<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GivingAlert extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'entry_id', 'partner_id', 'church_id', 'arm_key', 'amount_espees',
        'threshold_espees', 'acknowledged', 'acknowledged_by', 'acknowledged_at', 'created_at',
    ];

    protected function casts(): array
    {
        return [
            'acknowledged' => 'boolean',
            'acknowledged_at' => 'datetime',
            'created_at' => 'datetime',
            'amount_espees' => 'decimal:2',
            'threshold_espees' => 'decimal:2',
        ];
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
