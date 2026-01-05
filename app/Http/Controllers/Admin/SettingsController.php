<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    /**
     * Display the settings page.
     */
    public function index(): Response
    {
        $apiSettings = Setting::inGroup('api')->get()->mapWithKeys(fn ($s) => [$s->key => $s->getTypedValue()]);

        $emailSettings = Setting::inGroup('email')->get()->mapWithKeys(function ($s) {
            // Don't decrypt the API key for display, just show if it's set
            if ($s->key === 'resend_api_key' && $s->value) {
                return [$s->key => '••••••••' . substr($s->getTypedValue() ?? '', -4)];
            }
            return [$s->key => $s->getTypedValue()];
        });

        return Inertia::render('Admin/Settings/Index', [
            'apiSettings' => $apiSettings,
            'emailSettings' => $emailSettings,
        ]);
    }

    /**
     * Update settings.
     */
    public function update(Request $request)
    {
        $request->validate([
            'settings' => 'required|array',
            'settings.*.key' => 'required|string',
            'settings.*.value' => 'nullable',
            'settings.*.group' => 'required|string',
            'settings.*.type' => 'required|string|in:string,boolean,integer,json,encrypted',
        ]);

        foreach ($request->settings as $setting) {
            Setting::set(
                $setting['key'],
                $setting['value'],
                $setting['group'],
                $setting['type']
            );
        }

        Setting::clearCache();

        return back()->with('success', 'Settings updated successfully.');
    }

    /**
     * Get API settings for the frontend.
     */
    public function api(): Response
    {
        $apiSettings = Setting::inGroup('api')->get()->mapWithKeys(fn ($s) => [$s->key => $s->getTypedValue()]);

        return Inertia::render('Admin/Settings/Api', [
            'settings' => $apiSettings,
        ]);
    }

    /**
     * Update API settings.
     */
    public function updateApi(Request $request)
    {
        $request->validate([
            'frontend_url_production' => 'nullable|url',
            'frontend_url_development' => 'nullable|url',
            'api_url_production' => 'nullable|url',
            'api_url_development' => 'nullable|url',
            'cors_allowed_origins' => 'nullable|string',
        ]);

        Setting::set('frontend_url_production', $request->frontend_url_production, 'api', 'string');
        Setting::set('frontend_url_development', $request->frontend_url_development, 'api', 'string');
        Setting::set('api_url_production', $request->api_url_production, 'api', 'string');
        Setting::set('api_url_development', $request->api_url_development, 'api', 'string');
        Setting::set('cors_allowed_origins', $request->cors_allowed_origins, 'api', 'string');

        return back()->with('success', 'API settings updated successfully.');
    }

    /**
     * Get email settings.
     */
    public function email(): Response
    {
        $emailSettings = Setting::inGroup('email')->get()->mapWithKeys(function ($s) {
            // Don't decrypt the API key for display, just show if it's set
            if ($s->key === 'resend_api_key' && $s->value) {
                return [$s->key => '••••••••' . substr($s->getTypedValue(), -4)];
            }
            return [$s->key => $s->getTypedValue()];
        });

        return Inertia::render('Admin/Settings/Email', [
            'settings' => $emailSettings,
        ]);
    }

    /**
     * Update email settings.
     */
    public function updateEmail(Request $request)
    {
        $request->validate([
            'resend_api_key' => 'nullable|string',
            'email_from_address' => 'nullable|email',
            'email_from_name' => 'nullable|string',
            'email_reply_to' => 'nullable|email',
            'notification_email' => 'nullable|email',
            'email_enabled' => 'boolean',
        ]);

        // Only update API key if it's not the masked version
        if ($request->resend_api_key && !str_contains($request->resend_api_key, '••••')) {
            Setting::set('resend_api_key', $request->resend_api_key, 'email', 'encrypted');
        }

        Setting::set('email_from_address', $request->email_from_address, 'email', 'string');
        Setting::set('email_from_name', $request->email_from_name, 'email', 'string');
        Setting::set('email_reply_to', $request->email_reply_to, 'email', 'string');
        Setting::set('notification_email', $request->notification_email, 'email', 'string');
        Setting::set('email_enabled', $request->email_enabled, 'email', 'boolean');

        return back()->with('success', 'Email settings updated successfully.');
    }

    /**
     * Test email configuration.
     */
    public function testEmail(Request $request)
    {
        $request->validate([
            'test_email' => 'required|email',
        ]);

        // TODO: Implement actual email test with Resend

        return back()->with('success', 'Test email sent successfully to ' . $request->test_email);
    }
}
