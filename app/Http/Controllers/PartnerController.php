<?php

namespace App\Http\Controllers;

use App\Exports\PartnersExport;
use App\Http\Controllers\Concerns\DrillsDown;
use App\Models\Church;
use App\Models\Partner;
use App\Services\SemanticPartnerSearch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class PartnerController extends Controller
{
    use DrillsDown;

    const DELEGATE_CATEGORIES = ['Partner', 'Church Pastor/Director'];

    /** Page 1: one line per group church */
    public function index()
    {
        return $this->groupPage('partners');
    }

    /** Page 2: churches under a group */
    public function group(string $group)
    {
        return $this->churchPage('partners', $group);
    }

    /** Page 3: one church's partners */
    public function church(Request $request, Church $church)
    {
        $this->authorizeChurch($church);
        $user = Auth::user();

        $q = trim((string) $request->query('q', ''));
        $aiMode = $user->isZoneAdmin() && $request->boolean('ai');

        $query = Partner::with('church.groupChurch')
            ->where('church_id', $church->id)
            ->latest();

        if ($aiMode && $q !== '') {
            $ids = app(SemanticPartnerSearch::class)->search($q);
            $partners = Partner::with('church.groupChurch')
                ->where('church_id', $church->id)
                ->whereIn('id', $ids)->get()
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

        $churchIds = $user->visibleChurchIds();
        $churches = $churchIds === null
            ? Church::orderBy('name')->get(['id', 'name'])
            : Church::whereIn('id', $churchIds)->orderBy('name')->get(['id', 'name']);

        return view('partners.index', [
            'partners' => $partners,
            'churches' => $churches,
            'delegateCategories' => self::DELEGATE_CATEGORIES,
            'q' => $q,
            'aiMode' => $aiMode,
            'church' => $church,
            'groupChurch' => $church->groupChurch,
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
        // the detailed spouse fields collected here.
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

        // Keep spouse_name in sync, including clearing it if the spouse fields were emptied.
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
     * from the detailed spouse fields. Returns '' when no spouse fields are set.
     */
    private function buildSpouseName(array $data): string
    {
        $spouseName = trim(($data['spouse_title'] ?? '').' '.($data['spouse_first_name'] ?? '').' '.($data['spouse_last_name'] ?? ''));

        return preg_replace('/\s+/', ' ', $spouseName);
    }

    /**
     * Ensures the authenticated user may modify the given partner,
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