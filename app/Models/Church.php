<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Church extends Model
{
    protected $fillable = ['group_church_id', 'name', 'category'];

    public function groupChurch()
    {
        return $this->belongsTo(GroupChurch::class);
    }

    public function partners()
    {
        return $this->hasMany(Partner::class);
    }

    public function partnershipEntries()
    {
        return $this->hasMany(PartnershipEntry::class);
    }
}
