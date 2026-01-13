<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\NewLeadNotification;
use App\Models\Lead;
use App\Models\LeadStep;
use App\Models\LeadAttempt;
use App\Models\BlockedIp;
use App\Services\GeoLocationService;
use App\Services\SpamDetectionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class LeadController extends Controller
{
    protected SpamDetectionService $spamService;
    protected GeoLocationService $geoService;

    public function __construct(SpamDetectionService $spamService, GeoLocationService $geoService)
    {
        $this->spamService = $spamService;
        $this->geoService = $geoService;
    }

    /**
     * Start a new lead session
     */
    public function start(Request $request): JsonResponse
    {
        $ip = $request->ip();

        // Check if IP is blocked
        if (BlockedIp::isIpBlocked($ip)) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied',
            ], 403);
        }

        // Rate limiting: max 10 new leads per IP per hour
        $rateLimitKey = 'lead_start:' . $ip;
        if (RateLimiter::tooManyAttempts($rateLimitKey, 10)) {
            $this->logAttempt($request, null, 'create', null, true, false, 'Rate limited');
            return response()->json([
                'success' => false,
                'message' => 'Too many requests. Please try again later.',
                'retry_after' => RateLimiter::availableIn($rateLimitKey),
            ], 429);
        }

        RateLimiter::hit($rateLimitKey, 3600);

        try {
            // Determine source site from request or header
            $sourceSite = $request->input('source_site', Lead::SITE_POST_MARKETING);
            if (!in_array($sourceSite, array_keys(Lead::SITES))) {
                $sourceSite = Lead::SITE_POST_MARKETING;
            }

            // Get geolocation data
            $geoData = $this->geoService->lookup($ip);

            $lead = Lead::create([
                'ip_address' => $ip,
                'country' => $geoData['country'],
                'country_name' => $geoData['country_name'],
                'city' => $geoData['city'],
                'user_agent' => $request->userAgent(),
                'referrer' => $request->header('Referer'),
                'utm_source' => $request->input('utm_source'),
                'utm_medium' => $request->input('utm_medium'),
                'utm_campaign' => $request->input('utm_campaign'),
                'utm_term' => $request->input('utm_term'),
                'utm_content' => $request->input('utm_content'),
                'session_id' => $request->input('session_id'),
                'fingerprint' => $request->input('fingerprint'),
                'locale' => $request->input('locale', 'en'),
                'source_site' => $sourceSite,
                'status' => 'in_progress',
                'current_step' => 0,
            ]);

            $this->logAttempt($request, $lead->id, 'create', null, false, true);

            return response()->json([
                'success' => true,
                'data' => [
                    'lead_id' => $lead->uuid,
                    'session_id' => $lead->session_id,
                ],
            ], 201);
        } catch (\Exception $e) {
            $this->logAttempt($request, null, 'create', null, false, false, $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to start session',
            ], 500);
        }
    }

    /**
     * Update a step in the lead funnel
     */
    public function updateStep(Request $request, string $uuid): JsonResponse
    {
        $ip = $request->ip();

        // Check if IP is blocked
        if (BlockedIp::isIpBlocked($ip)) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied',
            ], 403);
        }

        // Rate limiting: max 60 updates per lead per minute
        $rateLimitKey = 'lead_update:' . $uuid;
        if (RateLimiter::tooManyAttempts($rateLimitKey, 60)) {
            return response()->json([
                'success' => false,
                'message' => 'Too many requests',
            ], 429);
        }

        RateLimiter::hit($rateLimitKey, 60);

        $request->validate([
            'step_id' => 'required|string|max:50',
            'step_number' => 'required|integer|min:0',
            'step_type' => 'required|string|max:50',
            'data' => 'nullable|array',
            'honeypot' => 'nullable|string',
            'form_fill_time_ms' => 'nullable|integer',
        ]);

        $lead = Lead::where('uuid', $uuid)->first();

        if (!$lead) {
            return response()->json([
                'success' => false,
                'message' => 'Lead not found',
            ], 404);
        }

        // Spam detection
        $spamResult = $this->spamService->detect($request, $lead);
        if ($spamResult['is_spam']) {
            $lead->update([
                'is_spam' => true,
                'spam_score' => $spamResult['score'],
            ]);

            $this->logAttempt($request, $lead->id, 'update_step', $request->input('step_id'), false, false, 'Spam detected', $spamResult);

            // Still return success to not reveal spam detection
            return response()->json([
                'success' => true,
                'data' => ['lead_id' => $lead->uuid],
            ]);
        }

        try {
            DB::transaction(function () use ($request, $lead) {
                // Update or create the step
                $step = LeadStep::updateOrCreate(
                    [
                        'lead_id' => $lead->id,
                        'step_id' => $request->input('step_id'),
                    ],
                    [
                        'step_number' => $request->input('step_number'),
                        'step_type' => $request->input('step_type'),
                        'data' => $request->input('data'),
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                        'completed_at' => now(),
                    ]
                );

                // Update lead with step data
                $this->updateLeadFromStep($lead, $request);

                // Update current step
                $lead->update([
                    'current_step' => max($lead->current_step, $request->input('step_number')),
                ]);
            });

            $this->logAttempt($request, $lead->id, 'update_step', $request->input('step_id'), false, true);

            return response()->json([
                'success' => true,
                'data' => [
                    'lead_id' => $lead->uuid,
                    'current_step' => $lead->fresh()->current_step,
                ],
            ]);
        } catch (\Exception $e) {
            $this->logAttempt($request, $lead->id, 'update_step', $request->input('step_id'), false, false, $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update step',
            ], 500);
        }
    }

    /**
     * Complete the lead
     */
    public function complete(Request $request, string $uuid): JsonResponse
    {
        $ip = $request->ip();

        if (BlockedIp::isIpBlocked($ip)) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied',
            ], 403);
        }

        $lead = Lead::where('uuid', $uuid)->first();

        if (!$lead) {
            return response()->json([
                'success' => false,
                'message' => 'Lead not found',
            ], 404);
        }

        // Final spam check
        $spamResult = $this->spamService->detect($request, $lead);
        if ($spamResult['is_spam']) {
            $lead->update([
                'is_spam' => true,
                'spam_score' => $spamResult['score'],
            ]);

            // Still mark as completed but flagged
        }

        try {
            $lead->markAsCompleted();

            // Send email notification if not spam
            if (!$lead->is_spam) {
                $this->sendLeadNotification($lead);
            }

            $this->logAttempt($request, $lead->id, 'complete', null, false, true);

            return response()->json([
                'success' => true,
                'message' => 'Lead completed successfully',
                'data' => [
                    'lead_id' => $lead->uuid,
                ],
            ]);
        } catch (\Exception $e) {
            $this->logAttempt($request, $lead->id, 'complete', null, false, false, $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to complete lead',
            ], 500);
        }
    }

    /**
     * Get lead status (for resuming)
     */
    public function status(Request $request, string $uuid): JsonResponse
    {
        $lead = Lead::where('uuid', $uuid)
            ->with('steps')
            ->first();

        if (!$lead) {
            return response()->json([
                'success' => false,
                'message' => 'Lead not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'lead_id' => $lead->uuid,
                'status' => $lead->status,
                'current_step' => $lead->current_step,
                'form_data' => [
                    'name' => $lead->name,
                    'email' => $lead->email,
                    'company' => $lead->company,
                    'has_website' => $lead->has_website,
                    'website_url' => $lead->website_url,
                    'industry' => $lead->industry,
                    'other_industry' => $lead->other_industry,
                    'services' => $lead->services,
                    'message' => $lead->message,
                ],
                'discovery_answers' => $lead->discovery_answers,
                'steps_completed' => $lead->steps->pluck('step_id')->toArray(),
            ],
        ]);
    }

    /**
     * Update lead data from step
     */
    protected function updateLeadFromStep(Lead $lead, Request $request): void
    {
        $stepId = $request->input('step_id');
        $data = $request->input('data', []);

        $updateData = [];

        // Map step data to lead fields
        switch ($stepId) {
            case 'name':
                $updateData['name'] = $data['value'] ?? null;
                break;
            case 'email':
                $updateData['email'] = $data['value'] ?? null;
                break;
            case 'company':
                $updateData['company'] = $data['value'] ?? null;
                break;
            case 'hasWebsite':
                $updateData['has_website'] = strtolower($data['value'] ?? '') === 'yes' || strtolower($data['value'] ?? '') === 'sí' ? 'yes' : 'no';
                $updateData['website_url'] = $data['website_url'] ?? null;
                break;
            case 'industry':
                $updateData['industry'] = $data['value'] ?? null;
                $updateData['other_industry'] = $data['other_value'] ?? null;
                break;
            case 'services':
                $updateData['services'] = $data['values'] ?? [];
                break;
            case 'message':
                $updateData['message'] = $data['value'] ?? null;
                break;
            case 'discovery':
                $updateData['discovery_answers'] = array_merge(
                    $lead->discovery_answers ?? [],
                    $data
                );
                break;
            case 'welcome':
                $updateData['terms_accepted'] = $data['terms_accepted'] ?? false;
                break;
        }

        if (!empty($updateData)) {
            $lead->update($updateData);
        }
    }

    /**
     * Log an attempt
     */
    protected function logAttempt(
        Request $request,
        ?int $leadId,
        string $action,
        ?string $stepId,
        bool $rateLimited,
        bool $success,
        ?string $errorMessage = null,
        ?array $spamData = null
    ): void {
        LeadAttempt::create([
            'lead_id' => $leadId,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'session_id' => $request->input('session_id'),
            'fingerprint' => $request->input('fingerprint'),
            'action' => $action,
            'source_site' => $request->input('source_site'),
            'step_id' => $stepId,
            'request_data' => $request->except(['honeypot', 'password']),
            'is_spam' => $spamData['is_spam'] ?? false,
            'spam_score' => $spamData['score'] ?? 0,
            'spam_reasons' => $spamData['reasons'] ?? null,
            'honeypot_value' => $request->input('honeypot'),
            'form_fill_time_ms' => $request->input('form_fill_time_ms'),
            'rate_limited' => $rateLimited,
            'response_code' => $success ? 200 : 500,
            'success' => $success,
            'error_message' => $errorMessage,
        ]);
    }

    /**
     * Send email notification for new lead
     */
    protected function sendLeadNotification(Lead $lead): void
    {
        $adminEmail = config('mail.admin_email');

        if (empty($adminEmail)) {
            Log::warning('Admin email not configured. Lead notification not sent.', [
                'lead_id' => $lead->uuid,
            ]);
            return;
        }

        try {
            Mail::to($adminEmail)->send(new NewLeadNotification($lead));

            Log::info('Lead notification email sent', [
                'lead_id' => $lead->uuid,
                'admin_email' => $adminEmail,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send lead notification email', [
                'lead_id' => $lead->uuid,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
