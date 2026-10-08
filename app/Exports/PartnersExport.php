<?php

namespace App\Exports;

use App\Models\Partner;
use App\Models\PartnershipEntry;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class PartnersExport implements FromCollection, WithHeadings, WithMapping, WithColumnFormatting
{
    /** Giving record per partner, keyed by partner_id (filled in collection()). */
    private $entries;

    /**
     * Scoped the same way PartnerController scopes its pages —
     * zone_admin sees everyone, group_admin/church_admin only their
     * own visibleChurchIds() — so exporting can't leak partners
     * outside what the logged-in user is already allowed to see.
     */
    public function collection()
    {
        $user = Auth::user();
        $churchIds = $user->visibleChurchIds();

        $query = Partner::with('church.groupChurch')
            ->orderBy('church_id')
            ->orderBy('first_name');

        if ($churchIds !== null) {
            $query->whereIn('church_id', $churchIds);
        }

        $partners = $query->get();

        $this->entries = PartnershipEntry::whereIn('partner_id', $partners->pluck('id'))
            ->get()
            ->keyBy('partner_id');

        return $partners;
    }

    /**
     * Same headers, same order as the upload template, plus church_id
     * at the end so a re-upload matches the church exactly.
     */
    public function headings(): array
    {
        return array_merge([
            'title',
            'first_name',
            'last_name',
            'delegate_category',
            'kingschat_username',
            'phone',
            'email',
            'church_name',
            'church_category',
            'group_name',
            'spouse_title',
            'spouse_first_name',
            'spouse_last_name',
            'spouse_delegate_category',
            'spouse_kingschat',
            'spouse_phone',
            'spouse_email',
        ], PartnershipEntry::ARM_KEYS, ['church_id']);
    }

    public function map($partner): array
    {
        $entry = $this->entries[$partner->id] ?? null;

        $arms = collect(PartnershipEntry::ARM_KEYS)
            ->map(fn ($key) => $entry && (float) $entry->{$key} > 0 ? (float) $entry->{$key} : null)
            ->all();

        return array_merge([
            $partner->title,
            $partner->first_name,
            $partner->last_name,
            $partner->delegate_category,
            $partner->kingschat_username,
            $partner->phone,
            $partner->email,
            $partner->church?->name,
            $partner->church?->category,
            $partner->church?->groupChurch?->name,
            $partner->spouse_title,
            $partner->spouse_first_name,
            $partner->spouse_last_name,
            $partner->spouse_delegate_category,
            $partner->spouse_kingschat,
            $partner->spouse_phone,
            $partner->spouse_email,
        ], $arms, [$partner->church_id]);
    }

    /** Keep phone numbers as text so Excel doesn't drop the leading 0. */
    public function columnFormats(): array
    {
        return [
            'F' => NumberFormat::FORMAT_TEXT, // phone
            'P' => NumberFormat::FORMAT_TEXT, // spouse_phone
        ];
    }
}