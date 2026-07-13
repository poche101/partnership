<?php

namespace App\Services;

use App\Models\Partner;
use App\Models\PartnershipEntry;
use App\Support\Arms;
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
        $query = PartnershipEntry::where('partner_id', $partner->id);
        if ($start) {
            $query->where('recorded_at', '>=', $start);
        }
        if ($end) {
            $query->where('recorded_at', '<=', $end);
        }
        $entries = $query->get();

        $armTotals = collect(PartnershipEntry::ARM_KEYS)
            ->mapWithKeys(fn ($k) => [$k => (float) $entries->sum($k)]);
        $total = (float) $entries->sum('total_espees');

        $name = $partner->fullName();
        $breakdown = collect(Arms::all())
            ->filter(fn ($a) => ($armTotals[$a['key']] ?? 0) > 0)
            ->map(fn ($a) => sprintf('- %s: %s ESPEES', $a['label'], number_format($armTotals[$a['key']], 2)))
            ->implode("\n");
        $period = ($start || $end) ? sprintf('%s to %s', $start ?: '—', $end ?: '—') : 'All time';

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
        if ($key && $entries->isNotEmpty()) {
            try {
                $response = Http::withHeaders([
                    'x-api-key' => $key,
                    'anthropic-version' => '2023-06-01',
                    'content-type' => 'application/json',
                ])->timeout(30)->post('https://api.anthropic.com/v1/messages', [
                    'model' => config('services.anthropic.model'),
                    'max_tokens' => 1024,
                    'system' => 'You write warm, formal partnership giving acknowledgement statements for a Christian ministry. Keep the factual numbers exactly as given. No emojis.',
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
}
