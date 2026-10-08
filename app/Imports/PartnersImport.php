<?php

namespace App\Imports;

use App\Models\Church;
use App\Models\Partner;
use App\Models\PartnershipEntry;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PartnersImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    public int $imported = 0;
    public array $skipped = [];

    private bool $booted = false;
    private int $line = 1;          // heading row is row 1
    private $user;
    private $byId;
    private $byName;
    private $partners;
    private $entries;

    public function chunkSize(): int
    {
        return 200;
    }

    private static function norm(?string $s): string
    {
        return mb_strtolower(preg_replace('/\s+/', ' ', trim((string) $s)));
    }

    /** Runs once, on the first chunk. */
    private function boot(): void
    {
        $this->booted = true;
        $this->user = Auth::user();
        $ids = $this->user->visibleChurchIds();

        $churches = Church::with('groupChurch')
            ->when($ids !== null, fn ($q) => $q->whereIn('id', $ids))->get();

        $this->byId = $churches->keyBy('id');
        $this->byName = $churches->groupBy(fn ($c) => self::norm($c->name));

        $churchIds = $churches->pluck('id');
        $this->partners = Partner::whereIn('church_id', $churchIds)->get()
            ->keyBy(fn ($p) => $p->church_id.'|'.self::norm($p->first_name).'|'.self::norm($p->last_name));
        $this->entries = PartnershipEntry::whereIn('church_id', $churchIds)->get()->keyBy('partner_id');
    }

    public function collection(Collection $rows)
    {
        if (! $this->booted) $this->boot();

        $now = now();
        $audit = [];

        DB::transaction(function () use ($rows, $now, &$audit) {
            Partner::withoutEvents(function () use ($rows, $now, &$audit) {
                foreach ($rows as $r) {
                    $this->line++;

                    $first = trim((string) ($r['first_name'] ?? ''));
                    if ($first === '') continue;   // blank / formatted-only rows

                    $church = null;
                    if (filled($r['church_id'] ?? null)) {
                        $church = $this->byId[(int) $r['church_id']] ?? null;
                    } else {
                        $m = $this->byName[self::norm($r['church_name'] ?? '')] ?? collect();
                        if ($m->count() > 1 && filled($r['group_name'] ?? null)) {
                            $m = $m->filter(fn ($c) => self::norm($c->groupChurch?->name) === self::norm($r['group_name']));
                        }
                        $church = $m->count() === 1 ? $m->first() : null;
                    }
                    if (! $church) {
                        $this->skipped[] = "Row {$this->line}: church '".($r['church_name'] ?? $r['church_id'] ?? '')."' not found or not allowed";
                        continue;
                    }

                    $spouse = preg_replace('/\s+/', ' ', trim(
                        ($r['spouse_title'] ?? '').' '.($r['spouse_first_name'] ?? '').' '.($r['spouse_last_name'] ?? '')
                    ));

                    $data = [
                        'title' => $r['title'] ?? null,
                        'delegate_category' => $r['delegate_category'] ?? null,
                        'kingschat_username' => $r['kingschat_username'] ?? null,
                        'phone' => $r['phone'] ?? null,
                        'email' => $r['email'] ?? null,
                        'spouse_title' => $r['spouse_title'] ?? null,
                        'spouse_first_name' => $r['spouse_first_name'] ?? null,
                        'spouse_last_name' => $r['spouse_last_name'] ?? null,
                        'spouse_delegate_category' => $r['spouse_delegate_category'] ?? null,
                        'spouse_kingschat' => $r['spouse_kingschat'] ?? null,
                        'spouse_phone' => $r['spouse_phone'] ?? null,
                        'spouse_email' => $r['spouse_email'] ?? null,
                        'spouse_name' => $spouse,
                    ];

                    $key = $church->id.'|'.self::norm($first).'|'.self::norm($r['last_name'] ?? '');
                    if (isset($this->partners[$key])) {
                        $partner = $this->partners[$key];
                        $partner->update($data);
                    } else {
                        $partner = Partner::create($data + [
                            'church_id' => $church->id,
                            'first_name' => $first,
                            'last_name' => $r['last_name'] ?? null,
                        ]);
                        $this->partners[$key] = $partner;
                    }
                    $this->imported++;

                    $amounts = [];
                    foreach (PartnershipEntry::ARM_KEYS as $k) {
                        $v = (float) str_replace([',', ' '], '', (string) ($r[$k] ?? 0));
                        if ($v > 0) $amounts[$k] = $v;
                    }
                    if (! $amounts) continue;

                    $entry = $this->entries[$partner->id] ?? null;
                    if (! $entry) {
                        $entry = new PartnershipEntry([
                            'partner_id' => $partner->id,
                            'church_id' => $church->id,
                            'created_by' => $this->user->id,
                        ]);
                        foreach (PartnershipEntry::ARM_KEYS as $k) $entry->{$k} = 0;
                    }

                    $changes = [];
                    foreach ($amounts as $k => $v) {
                        $changes[$k] = ['before' => (float) $entry->{$k}, 'after' => $v];
                        $entry->{$k} = $v;
                    }
                    $entry->total_espees = collect(PartnershipEntry::ARM_KEYS)->sum(fn ($k) => (float) $entry->{$k});
                    $entry->recorded_at = $now;
                    $entry->save();
                    $this->entries[$partner->id] = $entry;

                    $audit[] = [
                        'actor_id' => $this->user->id,
                        'actor_email' => $this->user->email,
                        'church_id' => $church->id,
                        'action' => 'giving.imported',
                        'entity_type' => PartnershipEntry::class,
                        'entity_id' => $entry->id,
                        'details' => json_encode([
                            'partner_id' => $partner->id,
                            'partner' => trim(($partner->first_name ?? '').' '.($partner->last_name ?? '')),
                            'changes' => $changes,
                        ]),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            });

            if ($audit) DB::table('audit_logs')->insert($audit);
        });
    }
}