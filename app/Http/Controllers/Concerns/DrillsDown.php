<?php

// app/Http/Controllers/Concerns/DrillsDown.php
namespace App\Http\Controllers\Concerns;

use App\Models\Church;
use App\Models\Partner;
use App\Models\PartnershipEntry;
use Illuminate\Support\Facades\Auth;

trait DrillsDown
{
    private function visibleChurches()
    {
        $ids = Auth::user()->visibleChurchIds();
        return Church::with('groupChurch')->when($ids !== null, fn ($q) => $q->whereIn('id', $ids));
    }

    private function stats(string $section): array
    {
        $partners = Partner::selectRaw('church_id, count(*) c')->groupBy('church_id')->pluck('c', 'church_id');
        $totals = $section === 'givings'
            ? PartnershipEntry::selectRaw('church_id, sum(total_espees) t')->groupBy('church_id')->pluck('t', 'church_id')
            : collect();
        return [$partners, $totals];
    }

    /** Page 1: one line per group church */
    protected function groupPage(string $section)
    {
        [$p, $t] = $this->stats($section);

        $items = $this->visibleChurches()->get()
            ->groupBy(fn ($c) => $c->group_church_id ?? 'none')
            ->map(fn ($cs, $gid) => [
                'name'     => $cs->first()->groupChurch?->name ?? 'No group church',
                'url'      => route("$section.group", $gid),
                'meta'     => $cs->count().' '.str('church')->plural($cs->count())
                              .' · '.$cs->sum(fn ($c) => $p[$c->id] ?? 0).' partners',
                'total'    => $section === 'givings' ? $cs->sum(fn ($c) => $t[$c->id] ?? 0) : null,
            ])
            ->sortBy('name')->values();

        return view('drilldown', [
            'heading' => ucfirst($section), 'subtitle' => 'Select a group church.',
            'crumbs' => [], 'items' => $items,
        ]);
    }

    /** Page 2: churches under one group */
    protected function churchPage(string $section, string $group)
    {
        [$p, $t] = $this->stats($section);

        $churches = $this->visibleChurches()
            ->when($group === 'none',
                fn ($q) => $q->whereNull('group_church_id'),
                fn ($q) => $q->where('group_church_id', $group))
            ->orderBy('name')->get();
        abort_if($churches->isEmpty(), 404);

        $groupName = $churches->first()->groupChurch?->name ?? 'No group church';

        return view('drilldown', [
            'heading'  => $groupName,
            'subtitle' => 'Select a church.',
            'crumbs'   => [['label' => ucfirst($section), 'url' => route("$section.index")]],
            'items'    => $churches->map(fn ($c) => [
                'name'  => $c->name,
                'url'   => route("$section.church", $c),
                'meta'  => ($p[$c->id] ?? 0).' partners',
                'total' => $section === 'givings' ? ($t[$c->id] ?? 0) : null,
            ]),
        ]);
    }

    protected function authorizeChurch(Church $church): void
    {
        $ids = Auth::user()->visibleChurchIds();
        if ($ids !== null && ! in_array($church->id, $ids, true)) abort(403);
    }
}