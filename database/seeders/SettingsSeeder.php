<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // API Configuration
            [
                'key' => 'frontend_url_production',
                'value' => 'https://savvypostmarketing.com',
                'group' => 'api',
                'type' => 'string',
                'description' => 'Frontend URL for production environment',
                'is_public' => true,
            ],
            [
                'key' => 'frontend_url_development',
                'value' => 'http://localhost:3000',
                'group' => 'api',
                'type' => 'string',
                'description' => 'Frontend URL for development environment',
                'is_public' => true,
            ],
            [
                'key' => 'api_url_production',
                'value' => 'https://api.savvypostmarketing.com',
                'group' => 'api',
                'type' => 'string',
                'description' => 'API URL for production environment',
                'is_public' => true,
            ],
            [
                'key' => 'api_url_development',
                'value' => 'http://localhost:8000',
                'group' => 'api',
                'type' => 'string',
                'description' => 'API URL for development environment',
                'is_public' => true,
            ],
            [
                'key' => 'cors_allowed_origins',
                'value' => 'http://localhost:3000,https://savvypostmarketing.com',
                'group' => 'api',
                'type' => 'string',
                'description' => 'Comma-separated list of allowed CORS origins',
                'is_public' => false,
            ],

            // Email Configuration
            [
                'key' => 'email_enabled',
                'value' => '0',
                'group' => 'email',
                'type' => 'boolean',
                'description' => 'Enable or disable email sending',
                'is_public' => false,
            ],
            [
                'key' => 'resend_api_key',
                'value' => null,
                'group' => 'email',
                'type' => 'encrypted',
                'description' => 'Resend API key for email sending',
                'is_public' => false,
            ],
            [
                'key' => 'email_from_address',
                'value' => 'hello@savvypostmarketing.com',
                'group' => 'email',
                'type' => 'string',
                'description' => 'Default from email address',
                'is_public' => false,
            ],
            [
                'key' => 'email_from_name',
                'value' => 'Savvy Marketing',
                'group' => 'email',
                'type' => 'string',
                'description' => 'Default from name',
                'is_public' => false,
            ],
            [
                'key' => 'email_reply_to',
                'value' => 'hello@savvypostmarketing.com',
                'group' => 'email',
                'type' => 'string',
                'description' => 'Default reply-to email address',
                'is_public' => false,
            ],
            [
                'key' => 'notification_email',
                'value' => 'notifications@savvypostmarketing.com',
                'group' => 'email',
                'type' => 'string',
                'description' => 'Email address for system notifications',
                'is_public' => false,
            ],

            // General Settings
            [
                'key' => 'site_name',
                'value' => 'Savvy Marketing',
                'group' => 'general',
                'type' => 'string',
                'description' => 'Site name',
                'is_public' => true,
            ],
            [
                'key' => 'maintenance_mode',
                'value' => '0',
                'group' => 'general',
                'type' => 'boolean',
                'description' => 'Enable maintenance mode',
                'is_public' => true,
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }

        $this->command->info('Settings seeded successfully!');
        $this->command->info('Total settings: ' . Setting::count());
    }
}
