<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    protected $fillable = [
        'church_id', 'title', 'first_name', 'last_name', 'delegate_category',
        'kingschat_username', 'phone', 'email', 'group_name', 'church_category',
        'spouse_title', 'spouse_first_name', 'spouse_delegate_category',
        'spouse_kingschat', 'spouse_phone', 'spouse_email',
    ];

    public function church()
    {
        return $this->belongsTo(Church::class);
    }

    public function entries()
    {
        return $this->hasMany(PartnershipEntry::class);
    }

    public function statements()
    {
        return $this->hasMany(GivingStatement::class);
    }

    public function fullName(): string
    {
        return trim(collect([$this->title, $this->first_name, $this->last_name])->filter()->implode(' '));
    }
}
