<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Church;
use App\Models\Partner;
use App\Models\PartnershipEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UploadController extends Controller
{
    /**
     * Group names the upload page lists that are saved under a slightly
     * different name in the app. Keys/values are in normalised form.
     */
    private const GROUP_ALIASES = [
        'ikoyi group 1' => 'ikoyi sub group 1',
        'ikoyi group 2' => 'ikoyi sub group 2',
    ];

    public function index()
    {
        $user = Auth::user();
        $ids = $user->visibleChurchIds();

        // Every role gets its (scoped) churches WITH their group church, because
        // the page builds the group dropdown from them.
        $churches = Church::with('groupChurch')
            ->when($ids !== null, fn ($q) => $q->whereIn('id', $ids))
            ->orderBy('name')
            ->get();

        return view('upload.index', ['churches' => $churches]);
    }

    public function import(Request $request)
    {
        set_time_limit(300);
        ini_set('memory_limit', '512M');

        $user = Auth::user();

        $data = $request->validate([
            'group_church_id' => ['nullable', 'integer'],
            'group_name' => ['nullable', 'string'],
            'church_id' => ['nullable', 'integer'],
            'rows' => ['required', 'array', 'min:1'],
            'rows.*.partner' => ['required', 'array'],
            'rows.*.partner.first_name' => ['required', 'string'],
            'rows.*.giving' => ['nullable', 'array'],
        ]);

        $data['rows'] = $request->input('rows');

        $norm = function ($s) {
            $s = mb_strtolower(preg_replace('/[\s\-]+/', ' ', trim((string) $s)));
            return self::GROUP_ALIASES[$s] ?? $s;
        };

        // Only churches this user may import into (zone admin: all).
        $ids = $user->visibleChurchIds();
        $churches = Church::with('groupChurch')
            ->when($ids !== null, fn ($q) => $q->whereIn('id', $ids))
            ->get();

        $byId = $churches->keyBy('id');
        $byName = $churches->groupBy(fn ($c) => $norm($c->name));

        // A church admin always imports into their own church.
        $forcedChurch = $user->isChurchAdmin() ? ($byId[$user->church_id] ?? null) : null;
        if ($user->isChurchAdmin() && ! $forcedChurch) {
            abort(403);
        }

        // Optional church to use for rows that have no church_name.
        $fallbackChurch = ! empty($data['church_id']) ? ($byId[(int) $data['church_id']] ?? null) : null;
        $pickedGroupId = $data['group_church_id'] ?? null;

        $partnerFields = [
            'title', 'first_name', 'last_name', 'delegate_category', 'kingschat_username', 'phone', 'email',
            'spouse_title', 'spouse_first_name', 'spouse_last_name', 'spouse_delegate_category',
            'spouse_kingschat', 'spouse_phone', 'spouse_email',
        ];

        $okPartners = 0;
        $okEntries = 0;
        $skipped = [];
        $now = now();

        DB::transaction(function () use (
            $data, $user, $norm, $byName, $forcedChurch, $fallbackChurch, $pickedGroupId,
            $partnerFields, $now, &$okPartners, &$okEntries, &$skipped
        ) {
            foreach ($data['rows'] as $i => $row) {
                $line = $i + 2; // row 1 of the sheet is the header
                $p = $row['partner'];

                $first = trim((string) ($p['first_name'] ?? ''));
                if ($first === '') {
                    continue;
                }

                // 1) Work out which church this row belongs to.
                $church = $forcedChurch;
                $churchName = trim((string) ($p['church_name'] ?? ''));

                if (! $church && $churchName !== '') {
                    $matches = $byName[$norm($churchName)] ?? collect();

                    $rowGroup = $norm($p['group_name'] ?? '');
                    if ($rowGroup !== '') {
                        $matches = $matches->filter(fn ($c) => $norm($c->groupChurch?->name) === $rowGroup);
                    } elseif ($pickedGroupId) {
                        $matches = $matches->filter(fn ($c) => (int) $c->group_church_id === (int) $pickedGroupId);
                    }

                    $church = $matches->count() === 1 ? $matches->first() : null;
                } elseif (! $church) {
                    $church = $fallbackChurch;
                }

                if (! $church) {
                    $skipped[] = $churchName === ''
                        ? "Row $line: no church_name"
                        : "Row $line: church '$churchName' not found (check the group)";
                    continue;
                }

                // 2) Create or update the partner.
                $partnerData = array_intersect_key($p, array_flip($partnerFields));
                $partnerData['spouse_name'] = preg_replace('/\s+/', ' ', trim(
                    ($p['spouse_title'] ?? '').' '.($p['spouse_first_name'] ?? '').' '.($p['spouse_last_name'] ?? '')
                ));

                $last = trim((string) ($p['last_name'] ?? ''));
                $canMatch = $last !== '' || ! empty($p['phone']) || ! empty($p['email']);

                if ($canMatch) {
                    // Re-uploading the same file updates people instead of duplicating them.
                    $partner = Partner::updateOrCreate(
                        ['church_id' => $church->id, 'first_name' => $first, 'last_name' => $last !== '' ? $last : null],
                        $partnerData
                    );
                } else {
                    // First name only, nothing to tell two people apart: always a new partner.
                    $partner = Partner::create($partnerData + ['church_id' => $church->id]);
                }
                $okPartners++;

                // 3) Giving: each arm in the sheet sets that arm's value.
                $giving = $row['giving'] ?? [];
                $amounts = [];
                foreach (PartnershipEntry::ARM_KEYS as $key) {
                    $amount = (float) ($giving[$key] ?? 0);
                    if ($amount > 0) {
                        $amounts[$key] = $amount;
                    }
                }
                if (! $amounts) {
                    continue;
                }

                $entry = PartnershipEntry::firstOrNew(['partner_id' => $partner->id], [
                    'church_id' => $church->id,
                    'created_by' => $user->id,
                ]);
                if (! $entry->exists) {
                    foreach (PartnershipEntry::ARM_KEYS as $key) {
                        $entry->{$key} = 0;
                    }
                }

                $changes = [];
                foreach ($amounts as $key => $amount) {
                    $changes[$key] = ['before' => (float) $entry->{$key}, 'after' => $amount];
                    $entry->{$key} = $amount;
                }

                // These two were missing before, so imported givings showed 0.00 totals.
                $entry->total_espees = collect(PartnershipEntry::ARM_KEYS)->sum(fn ($k) => (float) $entry->{$k});
                $entry->recorded_at = $now;
                $entry->save();
                $okEntries++;

                AuditLog::create([
                    'actor_id' => $user->id,
                    'actor_email' => $user->email,
                    'church_id' => $church->id,
                    'action' => 'giving.imported',
                    'entity_type' => PartnershipEntry::class,
                    'entity_id' => $entry->id,
                    'details' => [
                        'partner_id' => $partner->id,
                        'partner' => $partner->fullName(),
                        'changes' => $changes,
                    ],
                ]);
            }
        });

        return response()->json([
            'partners' => $okPartners,
            'entries' => $okEntries,
            'skipped' => $skipped,
        ]);
    }
}