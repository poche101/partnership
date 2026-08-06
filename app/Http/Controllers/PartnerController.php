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

        $data = $this->validatePartnerData($request);

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
        $data['spouse_name'] = $this->buildSpouseName($data);

        Partner::create($data);

        return back()->with('success', 'Partner added.');
    }

    public function update(Request $request, Partner $partner)
    {
        $user = Auth::user();
        $this->authorizePartnerAccess($user, $partner);

        $data = $this->validatePartnerData($request);

        $churchId = $user->isChurchAdmin() ? $user->church_id : ($data['church_id'] ?? $partner->church_id);
        if (! $churchId) {
            return back()->withErrors(['church_id' => 'Select a church.'])->withInput();
        }
        if ($user->isGroupAdmin() && ! Church::where('id', $churchId)->where('group_church_id', $user->group_church_id)->exists()) {
            abort(403);
        }

        unset($data['church_id']);
        $data['church_id'] = $churchId;

        // Keep spouse_name in sync — including clearing it back out if the
        // spouse fields were emptied on this edit.
        $data['spouse_name'] = $this->buildSpouseName($data);

        $partner->update($data);

        return back()->with('success', 'Partner updated.');
    }

    public function destroy(Partner $partner)
    {
        $user = Auth::user();
        $this->authorizePartnerAccess($user, $partner);

        $partner->delete();

        return back()->with('success', 'Partner deleted.');
    }

    public function export()
    {
        return Excel::download(new PartnersExport, 'partners-'.now()->format('Y-m-d').'.xlsx');
    }

    /**
     * Shared validation rules for the create and edit partner forms.
     */
    private function validatePartnerData(Request $request): array
    {
        return $request->validate([
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
    }

    /**
     * Builds the flat spouse_name string (used by the Givings statement/table)
     * from the detailed spouse fields. Returns '' when no spouse fields are set,
     * so updates correctly clear spouse_name if the spouse details are removed.
     */
    private function buildSpouseName(array $data): string
    {
        $spouseName = trim(($data['spouse_title'] ?? '').' '.($data['spouse_first_name'] ?? '').' '.($data['spouse_last_name'] ?? ''));

        return preg_replace('/\s+/', ' ', $spouseName);
    }

    /**
     * Ensures the authenticated user is allowed to modify the given partner,
     * i.e. the partner's church is within the user's visible scope.
     */
    private function authorizePartnerAccess($user, Partner $partner): void
    {
        $churchIds = $user->visibleChurchIds();
        if ($churchIds !== null && ! in_array($partner->church_id, $churchIds, true)) {
            abort(403);
        }
    }
}