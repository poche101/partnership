<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GivingStatement extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'partner_id', 'period_start', 'period_end', 'total_espees', 'content', 'generated_by', 'created_at',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'total_espees' => 'decimal:2',
            'created_at' => 'datetime',
        ];
    }

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }
}
