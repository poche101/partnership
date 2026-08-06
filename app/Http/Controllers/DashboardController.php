<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Church;
use App\Models\GroupChurch;
use App\Models\Partner;
use App\Models\PartnershipEntry;
use App\Support\Arms;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $user = Auth::user();
        $churchIds = $user->visibleChurchIds();

        $entriesQuery = PartnershipEntry::query();
        $partnersQuery = Partner::query();
        $churchesQuery = Church::query();

        if ($churchIds !== null) {
            $entriesQuery->whereIn('church_id', $churchIds);
            $partnersQuery->whereIn('church_id', $churchIds);
            $churchesQuery->whereIn('id', $churchIds);
        }
        if ($user->isGroupAdmin()) {
            $churchesQuery->where('group_church_id', $user->group_church_id);
        }

        $entries = $entriesQuery->get();
        $armKeys = collect(\App\Models\PartnershipEntry::ARM_KEYS);

        $armTotals = $armKeys->mapWithKeys(fn ($k) => [$k => (float) $entries->sum($k)])->all();
        $total = (float) $entries->sum('total_espees');

        $churches = $churchesQuery->get(['id', 'name', 'group_church_id']);
        $groupsCount = $user->isZoneAdmin() ? GroupChurch::count() : ($user->isGroupAdmin() ? 1 : 0);

        $churchTotals = $entries->groupBy('church_id')->map(fn ($rows) => (float) $rows->sum('total_espees'));
        $top = $churches->map(fn ($c) => [
            'id' => $c->id,
            'name' => $c->name,
            'total' => $churchTotals->get($c->id, 0),
        ])->sortByDesc('total')->take(6)->values();

        // 30-day trend (zone admin only, matches the original UI)
        //
        // IMPORTANT: this can NOT be built from PartnershipEntry. That table
        // holds one running-total row PER PARTNER, which gets overwritten
        // (amounts added, recorded_at reset to now()) on every gift — so
        // $entry->recorded_at only ever reflects a partner's most recent
        // gift, never their giving history. A partner who gave on day 1 and
        // again on day 20 would show 0 on day 1 and their *entire* total on
        // day 20, and would vanish from the trend completely once day 20
        // ages out of the 30-day window.
        //
        // AuditLog has the real per-gift history: one 'giving.recorded' row
        // per actual transaction, with a true timestamp and an 'added'
        // amount per arm (see GivingController@store). That's the correct
        // source for a day-by-day trend.
        $series = [];
        if ($user->isZoneAdmin()) {
            $byDay = [];
            for ($i = 29; $i >= 0; $i--) {
                $d = now()->subDays($i)->toDateString();
                $byDay[$d] = ['total' => 0.0, 'partners' => 0];
            }

            $logsQuery = AuditLog::where('action', 'giving.recorded')
                ->where('created_at', '>=', now()->subDays(29)->startOfDay());

            if ($churchIds !== null) {
                $logsQuery->whereIn('church_id', $churchIds);
            }

            $logs = $logsQuery->get(['church_id', 'created_at', 'details']);

            foreach ($logs as $log) {
                $day = $log->created_at?->toDateString();
                if (! $day || ! isset($byDay[$day])) {
                    continue;
                }

                $addedThisEvent = collect($log->details['changes'] ?? [])
                    ->sum(fn ($change) => (float) ($change['added'] ?? 0));

                $byDay[$day]['total'] += $addedThisEvent;
                $byDay[$day]['partners'] += 1;
            }

            foreach ($byDay as $date => $v) {
                $series[] = ['date' => substr($date, 5), 'total' => round($v['total'], 2), 'partners' => $v['partners']];
            }
        }

        return view('dashboard.index', [
            'total' => $total,
            'armTotals' => $armTotals,
            'arms' => Arms::enabled(),
            'countPartners' => $partnersQuery->count(),
            'countChurches' => $user->isZoneAdmin() ? Church::count() : $churches->count(),
            'countGroups' => $groupsCount,
            'top' => $top,
            'series' => $series,
        ]);
    }
}