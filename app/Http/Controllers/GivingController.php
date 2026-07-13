<?php

namespace App\Http\Controllers;

use App\Models\Church;
use App\Models\Partner;
use App\Models\PartnershipEntry;
use App\Support\Arms;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GivingController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $churchIds = $user->visibleChurchIds();

        $query = PartnershipEntry::with(['partner', 'church.groupChurch'])->latest('recorded_at')->limit(500);
        if ($churchIds !== null) {
            $query->whereIn('church_id', $churchIds);
        }
        $entries = $query->get();

        $armFilter = $request->query('arm', 'all');
        $arms = Arms::enabled();

        if ($armFilter !== 'all' && in_array($armFilter, PartnershipEntry::ARM_KEYS, true)) {
            $view = $entries->filter(fn ($e) => (float) $e->{$armFilter} > 0)->map(fn ($e) => [
                'entry' => $e, 'amount' => (float) $e->{$armFilter},
            ]);
            $armLabel = Arms::label($armFilter);
        } else {
            $armFilter = 'all';
            $view = $entries->map(fn ($e) => ['entry' => $e, 'amount' => (float) $e->total_espees]);
            $armLabel = 'All Arms';
        }

        $partnersQuery = Partner::query();
        if ($churchIds !== null) {
            $partnersQuery->whereIn('church_id', $churchIds);
        }

        return view('givings.index', [
            'view' => $view,
            'armFilter' => $armFilter,
            'armLabel' => $armLabel,
            'arms' => $arms,
            'partners' => $partnersQuery->orderBy('first_name')->get(),
            'totalShown' => $view->sum('amount'),
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'partner_id' => ['required', 'exists:partners,id'],
            'note' => ['nullable', 'string'],
            ...collect(PartnershipEntry::ARM_KEYS)->mapWithKeys(fn ($k) => [$k => ['nullable', 'numeric', 'min:0']])->all(),
        ]);

        $partner = Partner::findOrFail($data['partner_id']);
        if ($user->isChurchAdmin() && $partner->church_id !== $user->church_id) {
            abort(403);
        }
        if ($user->isGroupAdmin() && ! Church::where('id', $partner->church_id)->where('group_church_id', $user->group_church_id)->exists()) {
            abort(403);
        }

        $payload = ['partner_id' => $partner->id, 'church_id' => $partner->church_id, 'note' => $data['note'] ?? null, 'created_by' => $user->id];
        foreach (PartnershipEntry::ARM_KEYS as $key) {
            $payload[$key] = (float) ($data[$key] ?? 0);
        }

        PartnershipEntry::create($payload);

        return back()->with('success', 'Giving recorded.');
    }
}
