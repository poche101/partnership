<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupChurch extends Model
{
    protected $fillable = ['name'];

    public function churches()
    {
        return $this->hasMany(Church::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
