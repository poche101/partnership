<?php

namespace App\Exports;

use App\Support\Arms;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AuditLogsExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(private Builder $query)
    {
    }

    /**
     * One row per partnership arm changed within a giving.recorded or
     * giving.updated entry. Other audit actions (admin/church/group
     * creation, pastor-info edits, etc.) have no partner/arm/amount to
     * report and are excluded from this export entirely.
     */
    public function collection(): Collection
    {
        return $this->query->get()
            ->filter(fn ($log) => in_array($log->action, ['giving.recorded', 'giving.updated'], true))
            ->flatMap(fn ($log) => $this->lineItemsFor($log))
            ->values();
    }

    public function headings(): array
    {
        return ['Partner Name', 'Spouse Name', 'Partnership Arm', 'Amount', 'Date'];
    }

    public function map($item): array
    {
        return [
            $item['partner_name'],
            $item['spouse_name'],
            $item['arm'],
            $item['amount'],
            $item['date'],
        ];
    }

    private function lineItemsFor($log): array
    {
        $d = $log->details ?? [];

        if (empty($d['changes'])) {
            return [];
        }

        $partnerName = $this->partnerName($d);
        $spouseName = $this->spouseName($d);
        $date = $log->created_at->format('Y-m-d');
        $isRecorded = $log->action === 'giving.recorded';

        $items = [];
        foreach ($d['changes'] as $arm => $change) {
            $amount = ($isRecorded && array_key_exists('added', $change))
                ? (float) $change['added']
                : (float) $change['after'] - (float) $change['before'];

            $items[] = [
                'partner_name' => $partnerName,
                'spouse_name' => $spouseName,
                'arm' => Arms::label($arm),
                'amount' => number_format($amount, 2),
                'date' => $date,
            ];
        }

        return $items;
    }

    private function partnerName(array $d): string
    {
        if (! empty($d['partner_first_name'])) {
            return trim(($d['partner_title'] ?? '').' '.$d['partner_first_name'].' '.($d['partner_last_name'] ?? ''));
        }

        return $d['partner'] ?? 'Unknown partner';
    }

    private function spouseName(array $d): string
    {
        if (! empty($d['spouse_first_name'])) {
            return trim(($d['spouse_title'] ?? '').' '.$d['spouse_first_name'].' '.($d['spouse_last_name'] ?? ''));
        }

        return $d['spouse_name'] ?? '';
    }
}
