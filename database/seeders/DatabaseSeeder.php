<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed roles and permissions first
        $this->call([
            RolesAndPermissionsSeeder::class,
            AdminUserSeeder::class,
            PortfolioServicesAndIndustriesSeeder::class,
            SettingsSeeder::class,
            TestimonialSeeder::class,
        ]);
    }
}
