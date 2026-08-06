<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = ['actor_id', 'actor_email', 'church_id', 'action', 'entity_type', 'entity_id', 'details', 'created_at'];

    protected $casts = [
        'details' => 'array',
        'created_at' => 'datetime',
    ];
}