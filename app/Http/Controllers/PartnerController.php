<?php

namespace App\Http\Controllers;

use App\Exports\PartnersExport;
use App\Models\Church;
use App\Models\Partner;
use App\Services\SemanticPartnerSearch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

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
                        ->orWhere('kingschat_username', 'like', "%{$q}%")
                        ->orWhere('spouse_first_name', 'like', "%{$q}%")
                        ->orWhere('spouse_last_name', 'like', "%{$q}%")
                        ->orWhere('spouse_name', 'like', "%{$q}%")
                        ->orWhere('spouse_email', 'like', "%{$q}%")
                        ->orWhere('spouse_kingschat', 'like', "%{$q}%");
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
            'spouse_last_name' => ['nullable', 'string', 'max:255'],
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

        // Keep spouse_name (used by the Givings statement/table) in sync with
        // the detailed spouse fields collected here, so a partner created via
        // this form shows their spouse consistently everywhere.
        $spouseName = trim(($data['spouse_title'] ?? '').' '.($data['spouse_first_name'] ?? '').' '.($data['spouse_last_name'] ?? ''));
        $spouseName = preg_replace('/\s+/', ' ', $spouseName);
        if ($spouseName !== '') {
            $data['spouse_name'] = $spouseName;
        }

        Partner::create($data);

        return back()->with('success', 'Partner added.');
    }

    public function export()
    {
        return Excel::download(new PartnersExport, 'partners-'.now()->format('Y-m-d').'.xlsx');
    }
}