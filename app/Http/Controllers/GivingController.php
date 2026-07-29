<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Church;
use App\Models\Partner;
use App\Models\PartnershipEntry;
use App\Support\Arms;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
            'include_spouse' => ['nullable', 'boolean'],
            'spouse_name' => ['nullable', 'required_if:include_spouse,1', 'string', 'max:255'],
            ...collect(PartnershipEntry::ARM_KEYS)->mapWithKeys(fn ($k) => [$k => ['nullable', 'numeric', 'min:0']])->all(),
        ]);

        $partner = Partner::findOrFail($data['partner_id']);
        if ($user->isChurchAdmin() && $partner->church_id !== $user->church_id) {
            abort(403);
        }
        if ($user->isGroupAdmin() && ! Church::where('id', $partner->church_id)->where('group_church_id', $user->group_church_id)->exists()) {
            abort(403);
        }

        // Update spouse name on the partner record based on the toggle
        if ($request->boolean('include_spouse') && ! empty($data['spouse_name'])) {
            $partner->spouse_name = $data['spouse_name'];
            $partner->save();
        } elseif ($request->has('include_spouse') && ! $request->boolean('include_spouse')) {
            $partner->spouse_name = null;
            $partner->save();
        }

        // Reject empty submissions early
        $submitted = collect(PartnershipEntry::ARM_KEYS)
            ->mapWithKeys(fn ($k) => [$k => (float) ($data[$k] ?? 0)])
            ->filter(fn ($v) => $v > 0);

        if ($submitted->isEmpty()) {
            return back()->with('error', 'Enter an amount for at least one arm.');
        }

        $entry = DB::transaction(function () use ($partner, $user, $data, $submitted) {
            // Lock the partner's single running record (create it if this is their first gift)
            $entry = PartnershipEntry::where('partner_id', $partner->id)
                ->lockForUpdate()
                ->first();

            if (! $entry) {
                $entry = new PartnershipEntry([
                    'partner_id' => $partner->id,
                    'church_id' => $partner->church_id,
                    'created_by' => $user->id,
                ]);
                foreach (PartnershipEntry::ARM_KEYS as $key) {
                    $entry->{$key} = 0;
                }
            }

            $changes = [];
            foreach ($submitted as $key => $increment) {
                $before = (float) $entry->{$key};
                $entry->{$key} = $before + $increment;
                $changes[$key] = ['before' => $before, 'added' => $increment, 'after' => $entry->{$key}];
            }

            $entry->total_espees = collect(PartnershipEntry::ARM_KEYS)->sum(fn ($k) => (float) $entry->{$k});
            $entry->note = $data['note'] ?? $entry->note;
            $entry->recorded_at = now();
            $entry->save();

            AuditLog::create([
                'actor_id' => $user->id,
                'actor_email' => $user->email,
                'church_id' => $partner->church_id,
                'action' => 'giving.recorded',
                'entity_type' => PartnershipEntry::class,
                'entity_id' => $entry->id,
                'details' => [
                    'partner_id' => $partner->id,
                    'partner' => $partner->fullName(),
                    'spouse_name' => $partner->spouse_name,
                    'church_id' => $partner->church_id,
                    'changes' => $changes,
                ],
            ]);

            return $entry;
        });

        return back()->with('success', 'Giving recorded.');
    }
}