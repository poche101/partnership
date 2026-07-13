<?php

namespace App\Http\Controllers;

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
        $series = [];
        if ($user->isZoneAdmin()) {
            $byDay = [];
            for ($i = 29; $i >= 0; $i--) {
                $d = now()->subDays($i)->toDateString();
                $byDay[$d] = ['total' => 0.0, 'partners' => 0];
            }
            foreach ($entries as $e) {
                $day = $e->recorded_at?->toDateString();
                if ($day && isset($byDay[$day])) {
                    $byDay[$day]['total'] += (float) $e->total_espees;
                    $byDay[$day]['partners'] += 1;
                }
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
