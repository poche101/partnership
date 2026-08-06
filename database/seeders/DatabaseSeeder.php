<?php

namespace Database\Seeders;

use App\Models\PartnershipArm;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $arms = [
            ['key' => 'rhapsody', 'label' => 'Rhapsody of Realities', 'sort_order' => 1],
            ['key' => 'healing_school', 'label' => 'Healing School', 'sort_order' => 2],
            ['key' => 'loveworld_programs', 'label' => 'Loveworld Programs', 'sort_order' => 3],
            ['key' => 'loveworld_networks', 'label' => 'Loveworld Networks', 'sort_order' => 4],
            ['key' => 'inner_city', 'label' => 'Inner City Missions', 'sort_order' => 5],
            ['key' => 'ror_bible', 'label' => 'ROR Bible Sponsorship', 'sort_order' => 6],
            ['key' => 'blw_campus', 'label' => 'BLW Campus Ministry', 'sort_order' => 7],
            ['key' => 'new_media', 'label' => 'New Media Technologies', 'sort_order' => 8],
            ['key' => 'ltm', 'label' => 'LTM', 'sort_order' => 9],
            ['key' => 'loveworld_radio', 'label' => 'Loveworld Radio', 'sort_order' => 10],
            ['key' => 'lmam', 'label' => 'LMAM', 'sort_order' => 11],
            ['key' => 'crusade_grounds', 'label' => 'Loveworld Crusade Grounds', 'sort_order' => 12],
            ['key' => 'lca_rebuild', 'label' => 'LCA Rebuild', 'sort_order' => 13],
        ];

        foreach ($arms as $arm) {
            PartnershipArm::updateOrCreate(['key' => $arm['key']], $arm);
        }

        $email = env('SEED_SUPERADMIN_EMAIL', 'superadmin@partnership.app');
        $password = env('SEED_SUPERADMIN_PASSWORD', 'SuperAdmin#2026');

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Zone Super Admin',
                'password' => Hash::make($password),
                'role' => 'zone_admin',
            ]
        );

        $this->command?->info("Seeded zone super admin: {$email} / {$password}");

        // Groups, churches, pastors, and one login per church.
        $this->call(ChurchDataSeeder::class);
    }
}