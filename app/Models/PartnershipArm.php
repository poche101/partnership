<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnershipArm extends Model
{
    protected $fillable = ['key', 'label', 'enabled', 'sort_order'];

    protected function casts(): array
    {
        return ['enabled' => 'boolean'];
    }
}
