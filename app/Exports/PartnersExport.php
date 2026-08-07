<?php

namespace App\Exports;

use App\Models\Partner;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PartnersExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * Scoped the same way PartnerController@index scopes the table —
     * zone_admin sees everyone, group_admin/church_admin only their
     * own visibleChurchIds() — so exporting can't leak partners
     * outside what the logged-in user is already allowed to see.
     */
    public function collection()
    {
        $user = Auth::user();
        $churchIds = $user->visibleChurchIds();

        $query = Partner::with('church.groupChurch')->latest();

        if ($churchIds !== null) {
            $query->whereIn('church_id', $churchIds);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'Title',
            'First Name',
            'Last Name',
            'Delegate Category',
            'KingsChat',
            'Phone',
            'Email',
            'Church',
            'Group',
            'Spouse Title',
            'Spouse First Name',
            'Spouse Last Name',
            'Spouse Delegate Category',
            'Spouse KingsChat',
            'Spouse Phone',
            'Spouse Email',
        ];
    }

    public function map($partner): array
    {
        return [
            $partner->title,
            $partner->first_name,
            $partner->last_name,
            $partner->delegate_category,
            $partner->kingschat_username,
            $partner->phone,
            $partner->email,
            $partner->church?->name,
            $partner->church?->groupChurch?->name,
            $partner->spouse_title,
            $partner->spouse_first_name,
            $partner->spouse_last_name,
            $partner->spouse_delegate_category,
            $partner->spouse_kingschat,
            $partner->spouse_phone,
            $partner->spouse_email,
        ];
    }
}