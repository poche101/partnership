<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'group_church_id', 'church_id',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public const ROLES = ['zone_admin', 'group_admin', 'church_admin'];

    public function groupChurch()
    {
        return $this->belongsTo(GroupChurch::class);
    }

    public function church()
    {
        return $this->belongsTo(Church::class);
    }

    public function isZoneAdmin(): bool
    {
        return $this->role === 'zone_admin';
    }

    public function isGroupAdmin(): bool
    {
        return $this->role === 'group_admin';
    }

    public function isChurchAdmin(): bool
    {
        return $this->role === 'church_admin';
    }

    public function roleLabel(): string
    {
        return match ($this->role) {
            'zone_admin' => 'Zone Admin',
            'group_admin' => 'Group Admin',
            'church_admin' => 'Church Admin',
            default => '—',
        };
    }

    /**
     * Church IDs this user is allowed to see/manage data for.
     * Zone admin: null (means "no restriction").
     */
    public function visibleChurchIds(): ?array
    {
        if ($this->isZoneAdmin()) {
            return null;
        }

        if ($this->isGroupAdmin()) {
            return Church::where('group_church_id', $this->group_church_id)->pluck('id')->all();
        }

        return $this->church_id ? [$this->church_id] : [];
    }
}
