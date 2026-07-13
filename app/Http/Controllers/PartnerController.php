<?php

namespace App\Http\Controllers;

use App\Models\Church;
use App\Models\Partner;
use App\Services\SemanticPartnerSearch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PartnerController extends Controller
{
    const DELEGATE_CATEGORIES = ['Partner', 'Church Pastor/Director'];

    public function index(Request $request)
    {
        $user = Auth::user();
        $churchIds = $user->visibleChurchIds();

        $query = Partner::with('church.groupChurch')->latest();
        if ($churchIds !== null) {
            $query->whereIn('church_id', $churchIds);
        }

        $q = trim((string) $request->query('q', ''));
        $aiMode = $user->isZoneAdmin() && $request->boolean('ai');
        $partners = collect();

        if ($aiMode && $q !== '') {
            $ids = app(SemanticPartnerSearch::class)->search($q);
            $partners = Partner::with('church.groupChurch')->whereIn('id', $ids)->get()
                ->sortBy(fn ($p) => array_search($p->id, $ids))->values();
        } else {
            if ($q !== '') {
                $query->where(function ($w) use ($q) {
                    $w->where('first_name', 'like', "%{$q}%")
                        ->orWhere('last_name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%")
                        ->orWhere('kingschat_username', 'like', "%{$q}%");
                });
            }
            $partners = $query->get();
        }

        $churches = $churchIds === null ? Church::orderBy('name')->get(['id', 'name']) : Church::whereIn('id', $churchIds)->get(['id', 'name']);

        return view('partners.index', [
            'partners' => $partners,
            'churches' => $churches,
            'delegateCategories' => self::DELEGATE_CATEGORIES,
            'q' => $q,
            'aiMode' => $aiMode,
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:100'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'delegate_category' => ['nullable', 'string'],
            'kingschat_username' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'church_id' => ['nullable', 'exists:churches,id'],
            'spouse_title' => ['nullable', 'string', 'max:100'],
            'spouse_first_name' => ['nullable', 'string', 'max:255'],
            'spouse_delegate_category' => ['nullable', 'string'],
            'spouse_kingschat' => ['nullable', 'string', 'max:255'],
            'spouse_phone' => ['nullable', 'string', 'max:50'],
            'spouse_email' => ['nullable', 'email', 'max:255'],
        ]);

        $churchId = $user->isChurchAdmin() ? $user->church_id : ($data['church_id'] ?? null);
        if (! $churchId) {
            return back()->withErrors(['church_id' => 'Select a church.'])->withInput();
        }
        if ($user->isGroupAdmin() && ! Church::where('id', $churchId)->where('group_church_id', $user->group_church_id)->exists()) {
            abort(403);
        }

        unset($data['church_id']);
        $data['church_id'] = $churchId;

        Partner::create($data);

        return back()->with('success', 'Partner added.');
    }
}
