<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Partner;
use App\Models\PartnershipEntry;
use App\Support\Arms;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GivingStatementWriter
{
    /**
     * Builds a partnership giving statement letter for a partner over an
     * optional date range. Returns ['content' => string, 'total' => float].
     */
    public function write(Partner $partner, ?string $start, ?string $end): array
    {
        $isAllTime = ! $start && ! $end;

        $armTotals = $isAllTime
            ? $this->allTimeTotals($partner)
            : $this->periodTotals($partner, $start, $end);

        $total = $armTotals->sum();

        $name = $partner->fullName();
        if ($partner->spouse_name) {
            $name .= ' & '.$partner->spouse_name;
        }

        $breakdown = collect(Arms::all())
            ->filter(fn ($a) => ($armTotals[$a['key']] ?? 0) > 0)
            ->map(fn ($a) => sprintf('- %s: %s ESPEES', $a['label'], number_format($armTotals[$a['key']], 2)))
            ->implode("\n");
        $period = $isAllTime ? 'All time' : sprintf('%s to %s', $start ?: '—', $end ?: '—');

        $content = "PARTNERSHIP GIVING STATEMENT\n\n"
            ."Dear {$name},\n\n"
            ."We acknowledge with deep gratitude your partnership with the ministry for the period {$period}.\n\n"
            .'Church: '.($partner->church?->name ?? '—')."\n"
            .'Group: '.($partner->church?->groupChurch?->name ?? '—')."\n\n"
            ."GIVING BREAKDOWN:\n".($breakdown ?: 'No givings recorded for this period.')."\n\n"
            .'TOTAL: '.number_format($total, 2)." ESPEES\n\n"
            ."Thank you for your continued faithfulness.\n\n"
            .'Issued by Zone Administration.';

        $key = config('services.anthropic.key');
        if ($key && $total > 0) {
            try {
                $response = Http::withHeaders([
                    'x-api-key' => $key,
                    'anthropic-version' => '2023-06-01',
                    'content-type' => 'application/json',
                ])->timeout(30)->post('https://api.anthropic.com/v1/messages', [
                    'model' => config('services.anthropic.model'),
                    'max_tokens' => 1024,
                    'system' => 'You write warm, formal partnership giving acknowledgement statements for a Christian ministry. If the partner name includes a spouse (joined by "&"), address the greeting to both. Keep the factual numbers exactly as given. No emojis.',
                    'messages' => [['role' => 'user', 'content' =>
                        "Write a 200-word partnership statement letter using these facts:\n\n"
                        ."Partner: {$name}\nChurch: ".($partner->church?->name ?? '—')."\nGroup: ".($partner->church?->groupChurch?->name ?? '—')."\nPeriod: {$period}\nTotal: ".number_format($total, 2)." ESPEES\n\nBreakdown:\n{$breakdown}\n\n"
                        .'Format as a formal letter with greeting, body, breakdown table-like list, signature line for Zone Administration.',
                    ]],
                ]);

                if ($response->successful()) {
                    $aiText = $response->json('content.0.text');
                    if ($aiText) {
                        $content = $aiText;
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('AI statement generation failed, using templated statement: '.$e->getMessage());
            }
        }

        return ['content' => $content, 'total' => $total];
    }

    /**
     * All-time totals come straight from the partner's single running
     * PartnershipEntry row — accurate for an unbounded period.
     */
    protected function allTimeTotals(Partner $partner): \Illuminate\Support\Collection
    {
        $entry = PartnershipEntry::where('partner_id', $partner->id)->first();

        return collect(PartnershipEntry::ARM_KEYS)
            ->mapWithKeys(fn ($k) => [$k => $entry ? (float) $entry->{$k} : 0.0]);
    }

    /**
     * Period totals can't be read off PartnershipEntry (it only holds a
     * cumulative running total per partner, not per-date history). Instead,
     * sum the "added" amounts from each giving.recorded audit log entry
     * whose timestamp falls within the requested range.
     */
    protected function periodTotals(Partner $partner, ?string $start, ?string $end): \Illuminate\Support\Collection
    {
        $armTotals = collect(PartnershipEntry::ARM_KEYS)->mapWithKeys(fn ($k) => [$k => 0.0]);

        $entry = PartnershipEntry::where('partner_id', $partner->id)->first();
        if (! $entry) {
            return $armTotals;
        }

        $query = AuditLog::query()
            ->where('entity_type', PartnershipEntry::class)
            ->where('entity_id', $entry->id)
            ->where('action', 'giving.recorded');

        if ($start) {
            $query->where('created_at', '>=', Carbon::parse($start)->startOfDay());
        }
        if ($end) {
            $query->where('created_at', '<=', Carbon::parse($end)->endOfDay());
        }

        $logs = $query->get();

        foreach ($logs as $log) {
            $changes = $log->details['changes'] ?? [];
            foreach ($changes as $armKey => $change) {
                if (! $armTotals->has($armKey)) {
                    continue;
                }
                $armTotals[$armKey] += (float) ($change['added'] ?? 0);
            }
        }

        return $armTotals;
    }
}