<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GivingAlertThreshold extends Model
{
    protected $fillable = ['arm_key', 'threshold_espees', 'enabled'];

    protected function casts(): array
    {
        return ['enabled' => 'boolean', 'threshold_espees' => 'decimal:2'];
    }
}
