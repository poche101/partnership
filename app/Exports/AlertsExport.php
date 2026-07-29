<?php

namespace App\Exports;

use App\Models\GivingAlert;
use App\Support\Arms;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AlertsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    public function collection()
    {
        $user = Auth::user();
        $churchIds = $user->visibleChurchIds();

        $query = GivingAlert::with(['partner', 'church.groupChurch'])->latest('created_at')->limit(200);
        if ($churchIds !== null) {
            $query->whereIn('church_id', $churchIds);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return ['Date', 'Partner', 'Church', 'Arm', 'Amount (ESPEES)', 'Threshold (ESPEES)', 'Status'];
    }

    public function map($alert): array
    {
        return [
            $alert->created_at->format('M j, Y g:ia'),
            $alert->partner?->fullName() ?? '—',
            $alert->church?->name ?? '—',
            Arms::label($alert->arm_key),
            number_format((float) $alert->amount_espees, 2),
            number_format((float) $alert->threshold_espees, 2),
            $alert->acknowledged ? 'Acknowledged' : 'New',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
