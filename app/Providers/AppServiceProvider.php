<?php

namespace App\Providers;

use App\Models\Church;
use App\Models\GroupChurch;
use App\Models\Partner;
use App\Models\PartnershipEntry;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Zone admin bypasses every other check.
        Gate::before(function (User $user, string $ability) {
            return $user->role === 'zone_admin' ? true : null;
        });

        Gate::define('manage-group-churches', fn (User $user) => false);

        Gate::define('manage-churches', function (User $user, ?GroupChurch $group = null) {
            if ($user->role !== 'group_admin') {
                return false;
            }

            return $group === null || $group->id === $user->group_church_id;
        });

        Gate::define('view-church', function (User $user, Church $church) {
            return match ($user->role) {
                'group_admin' => $church->group_church_id === $user->group_church_id,
                'church_admin' => $church->id === $user->church_id,
                default => false,
            };
        });

        Gate::define('manage-church-scope', function (User $user, ?Church $church = null) {
            if ($user->role === 'group_admin') {
                return $church === null || $church->group_church_id === $user->group_church_id;
            }
            if ($user->role === 'church_admin') {
                return $church === null || $church->id === $user->church_id;
            }

            return false;
        });

        Gate::define('manage-partner', function (User $user, Partner $partner) {
            return self::userCanAccessChurch($user, $partner->church_id);
        });

        Gate::define('manage-giving', function (User $user, PartnershipEntry $entry) {
            return self::userCanAccessChurch($user, $entry->church_id);
        });

        Gate::define('view-audit', fn (User $user) => false);
        Gate::define('manage-arms', fn (User $user) => false);
        Gate::define('view-search', fn (User $user) => false);
        Gate::define('manage-statements', fn (User $user) => false);
        Gate::define('manage-thresholds', fn (User $user) => false);
    }

    /**
     * Helper to verify if a user has access rights to a specific church.
     */
    protected static function userCanAccessChurch(User $user, ?int $churchId): bool
    {
        if (! $churchId) {
            return false;
        }

        if ($user->role === 'church_admin') {
            return $user->church_id === $churchId;
        }

        if ($user->role === 'group_admin') {
            return Church::where('id', $churchId)
                ->where('group_church_id', $user->group_church_id)
                ->exists();
        }

        return false;
    }
}