<?php

namespace App\Support;

use App\Models\PartnershipArm;

class Arms
{
    /** Default labels, keyed by column key on partnership_entries. Order matches the template. */
    public const DEFAULTS = [
        'rhapsody' => 'Rhapsody of Realities',
        'healing_school' => 'Healing School',
        'loveworld_programs' => 'Loveworld Programs',
        'loveworld_networks' => 'Loveworld Networks',
        'inner_city' => 'Inner City Missions',
        'ror_bible' => 'ROR Bible Sponsorship',
        'blw_campus' => 'BLW Campus Ministry',
        'new_media' => 'New Media Technologies',
        'ltm' => 'LTM',
        'loveworld_radio' => 'Loveworld Radio',
        'lmam' => 'LMAM',
        'crusade_grounds' => 'Loveworld Crusade Grounds',
        'lca_rebuild' => 'LCA Rebuild',
    ];

    /** Enabled arms from the DB, falling back to the static defaults. */
    public static function enabled(): array
    {
        $rows = PartnershipArm::where('enabled', true)->orderBy('sort_order')->get();

        if ($rows->isEmpty()) {
            return collect(self::DEFAULTS)->map(fn ($label, $key) => ['key' => $key, 'label' => $label])->values()->all();
        }

        return $rows->map(fn ($a) => ['key' => $a->key, 'label' => $a->label])->all();
    }

    public static function all(): array
    {
        $rows = PartnershipArm::orderBy('sort_order')->get();

        if ($rows->isEmpty()) {
            return collect(self::DEFAULTS)->map(fn ($label, $key) => ['key' => $key, 'label' => $label, 'enabled' => true])->values()->all();
        }

        return $rows->map(fn ($a) => ['key' => $a->key, 'label' => $a->label, 'enabled' => $a->enabled])->all();
    }

    public static function label(string $key): string
    {
        $arm = PartnershipArm::where('key', $key)->first();

        return $arm?->label ?? self::DEFAULTS[$key] ?? $key;
    }

    public static function format(float|int|string|null $n): string
    {
        return number_format((float) ($n ?? 0), 2);
    }
}
