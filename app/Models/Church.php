<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Church extends Model
{
    protected $fillable = [
        'name',
        'category',
        'group_church_id',
        'pastor_name',
        'pastor_email',
        'pastor_phone',
        'pastor_kingschat',
    ];

    public function groupChurch()
    {
        return $this->belongsTo(GroupChurch::class);
    }

    /** The church_admin user for this church, if one exists. */
    public function admin()
    {
        return $this->hasOne(User::class);
    }
}
