<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnershipEntry extends Model
{
    public const ARM_KEYS = [
        'rhapsody', 'healing_school', 'loveworld_programs', 'loveworld_networks',
        'inner_city', 'ror_bible', 'blw_campus', 'new_media', 'ltm',
        'loveworld_radio', 'lmam', 'crusade_grounds', 'lca_rebuild',
    ];

    protected $fillable = [
        'partner_id', 'church_id', 'note', 'recorded_at', 'created_by', 'updated_by',
        ...self::ARM_KEYS,
    ];

    protected function casts(): array
    {
        return [
            'recorded_at' => 'datetime',
            'total_espees' => 'decimal:2',
            ...array_fill_keys(self::ARM_KEYS, 'decimal:2'),
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (PartnershipEntry $entry) {
            $entry->total_espees = collect(self::ARM_KEYS)
                ->sum(fn ($key) => (float) ($entry->{$key} ?? 0));

            if (! $entry->recorded_at) {
                $entry->recorded_at = now();
            }
        });

        static::saved(function (PartnershipEntry $entry) {
            app(\App\Services\GivingAlertService::class)->checkThresholds($entry);

            if ($user = auth()->user()) {
                \App\Services\AuditLogger::log(
                    $user,
                    $entry->wasRecentlyCreated ? 'giving.created' : 'giving.updated',
                    'partnership_entry',
                    $entry->id,
                    ['partner_id' => $entry->partner_id, 'church_id' => $entry->church_id, 'total_espees' => (float) $entry->total_espees]
                );
            }
        });
    }

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    public function church()
    {
        return $this->belongsTo(Church::class);
    }
}
