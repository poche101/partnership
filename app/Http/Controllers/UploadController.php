<?php

namespace App\Http\Controllers;

use App\Models\Church;
use App\Models\Partner;
use App\Models\PartnershipEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UploadController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $churches = $user->isChurchAdmin()
            ? collect()
            : ($user->visibleChurchIds() === null ? Church::orderBy('name')->get(['id', 'name']) : Church::whereIn('id', $user->visibleChurchIds())->get(['id', 'name']));

        return view('upload.index', ['churches' => $churches]);
    }

    public function import(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'church_id' => ['nullable', 'exists:churches,id'],
            'rows' => ['required', 'array', 'min:1'],
            'rows.*.partner' => ['required', 'array'],
            'rows.*.partner.first_name' => ['required', 'string'],
            'rows.*.giving' => ['nullable', 'array'],
        ]);

        $churchId = $user->isChurchAdmin() ? $user->church_id : $data['church_id'] ?? null;
        if (! $churchId) {
            return response()->json(['message' => 'Select a church first.'], 422);
        }
        if ($user->isGroupAdmin() && ! Church::where('id', $churchId)->where('group_church_id', $user->group_church_id)->exists()) {
            abort(403);
        }

        $partnerFields = [
            'title', 'first_name', 'last_name', 'delegate_category', 'kingschat_username', 'phone', 'email',
            'group_name', 'church_category', 'spouse_title', 'spouse_first_name', 'spouse_delegate_category',
            'spouse_kingschat', 'spouse_phone', 'spouse_email',
        ];

        $okPartners = 0;
        $okEntries = 0;

        foreach ($data['rows'] as $row) {
            $partnerData = array_intersect_key($row['partner'], array_flip($partnerFields));
            $partnerData['church_id'] = $churchId;

            if (empty($partnerData['first_name'])) {
                continue;
            }

            $partner = Partner::create($partnerData);
            $okPartners++;

            $giving = $row['giving'] ?? [];
            $hasGiving = false;
            $entryPayload = ['partner_id' => $partner->id, 'church_id' => $churchId, 'created_by' => $user->id];
            foreach (PartnershipEntry::ARM_KEYS as $key) {
                $amount = (float) ($giving[$key] ?? 0);
                $entryPayload[$key] = $amount;
                if ($amount > 0) {
                    $hasGiving = true;
                }
            }

            if ($hasGiving) {
                PartnershipEntry::create($entryPayload);
                $okEntries++;
            }
        }

        return response()->json(['partners' => $okPartners, 'entries' => $okEntries]);
    }
}
