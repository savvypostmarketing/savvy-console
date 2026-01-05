<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create super admin user
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@savvy.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // Assign super-admin role
        $superAdmin->assignRole(Role::SUPER_ADMIN);

        $this->command->info('Super Admin user created:');
        $this->command->info('Email: admin@savvy.com');
        $this->command->info('Password: password');
        $this->command->warn('Please change this password immediately in production!');
    }
}
