<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PageView;
use App\Models\VisitorEvent;
use App\Models\VisitorSession;
use App\Services\IntentScoringService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Jenssegers\Agent\Agent;

class VisitorTrackingController extends Controller
{
    private IntentScoringService $intentService;

    public function __construct(IntentScoringService $intentService)
    {
        $this->intentService = $intentService;
    }

    /**
     * Initialize or resume a visitor session.
     */
    public function initSession(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'visitor_id' => 'required|string|max:64',
            'session_token' => 'nullable|string|max:64',
            'landing_page' => 'required|string|max:500',
            'referrer' => 'nullable|string|max:500',
            'utm_source' => 'nullable|string|max:100',
            'utm_medium' => 'nullable|string|max:100',
            'utm_campaign' => 'nullable|string|max:100',
            'utm_term' => 'nullable|string|max:100',
            'utm_content' => 'nullable|string|max:100',
            'viewport_width' => 'nullable|integer',
            'viewport_height' => 'nullable|integer',
            'locale' => 'nullable|string|max:5',
            'timezone' => 'nullable|string|max:50',
        ]);

        // Try to resume existing session
        if (!empty($validated['session_token'])) {
            $session = VisitorSession::where('session_token', $validated['session_token'])
                ->where('status', '!=', 'ended')
                ->where('last_activity_at', '>=', now()->subMinutes(30))
                ->first();

            if ($session) {
                $session->updateActivity();
                return response()->json([
                    'success' => true,
                    'session_token' => $session->session_token,
                    'session_id' => $session->uuid,
                    'resumed' => true,
                ]);
            }
        }

        // Parse user agent
        $agent = new Agent();
        $agent->setUserAgent($request->userAgent());

        // Check for returning visitor
        $previousSessions = VisitorSession::where('visitor_id', $validated['visitor_id'])
            ->where('status', 'ended')
            ->count();

        $firstSeen = VisitorSession::where('visitor_id', $validated['visitor_id'])
            ->min('created_at');

        // Parse referrer
        $referrerDomain = null;
        $referrerType = 'direct';
        if (!empty($validated['referrer'])) {
            $parsedReferrer = parse_url($validated['referrer']);
            $referrerDomain = $parsedReferrer['host'] ?? null;
            $referrerType = $this->determineReferrerType($referrerDomain, $validated);
        }

        // Create new session
        $session = VisitorSession::create([
            'visitor_id' => $validated['visitor_id'],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'device_type' => $this->getDeviceType($agent),
            'browser' => $agent->browser(),
            'browser_version' => $agent->version($agent->browser()),
            'os' => $agent->platform(),
            'os_version' => $agent->version($agent->platform()),
            'is_bot' => $agent->isRobot(),
            'referrer_url' => $validated['referrer'] ?? null,
            'referrer_domain' => $referrerDomain,
            'referrer_type' => $referrerType,
            'landing_page' => $validated['landing_page'],
            'utm_source' => $validated['utm_source'] ?? null,
            'utm_medium' => $validated['utm_medium'] ?? null,
            'utm_campaign' => $validated['utm_campaign'] ?? null,
            'utm_term' => $validated['utm_term'] ?? null,
            'utm_content' => $validated['utm_content'] ?? null,
            'is_returning' => $previousSessions > 0,
            'previous_sessions_count' => $previousSessions,
            'first_seen_at' => $firstSeen ?? now(),
            'locale' => $validated['locale'] ?? 'en',
            'timezone' => $validated['timezone'] ?? null,
            'accept_language' => $request->header('Accept-Language'),
        ]);

        return response()->json([
            'success' => true,
            'session_token' => $session->session_token,
            'session_id' => $session->uuid,
            'resumed' => false,
            'is_returning' => $session->is_returning,
        ]);
    }

    /**
     * Track a page view.
     */
    public function trackPageView(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'session_token' => 'required|string|max:64',
            'url' => 'required|string|max:500',
            'path' => 'required|string|max:500',
            'title' => 'nullable|string|max:255',
            'previous_url' => 'nullable|string|max:500',
            'previous_path' => 'nullable|string|max:500',
            'viewport_width' => 'nullable|integer',
            'viewport_height' => 'nullable|integer',
            'document_height' => 'nullable|integer',
            'load_time_ms' => 'nullable|integer',
            'dom_ready_ms' => 'nullable|integer',
            'fcp_ms' => 'nullable|integer',
        ]);

        $session = VisitorSession::where('session_token', $validated['session_token'])->first();

        if (!$session) {
            return response()->json(['success' => false, 'error' => 'Session not found'], 404);
        }

        // Mark previous page view as exited
        $previousPageView = $session->pageViews()
            ->whereNull('exited_at')
            ->latest('entered_at')
            ->first();

        if ($previousPageView) {
            $previousPageView->markAsExit($validated['url']);
        }

        // Determine page type
        $pageType = PageView::determinePageType($validated['path']);

        // Create new page view
        $pageView = PageView::create([
            'visitor_session_id' => $session->id,
            'url' => $validated['url'],
            'path' => $validated['path'],
            'page_title' => $validated['title'],
            'page_type' => $pageType,
            'previous_url' => $validated['previous_url'],
            'previous_path' => $validated['previous_path'],
            'viewport_width' => $validated['viewport_width'],
            'viewport_height' => $validated['viewport_height'],
            'document_height' => $validated['document_height'],
            'load_time_ms' => $validated['load_time_ms'],
            'dom_ready_ms' => $validated['dom_ready_ms'],
            'first_contentful_paint_ms' => $validated['fcp_ms'],
            'entered_at' => now(),
        ]);

        // Update session
        $session->incrementPageViews();
        $session->updateActivity();
        $session->markPageVisited($pageType);

        // Create page view event
        $this->createEvent($session, $pageView, VisitorEvent::TYPE_PAGE_VIEW, [
            'page_type' => $pageType,
        ]);

        // Update intent score
        $this->intentService->updateSessionScore($session);

        return response()->json([
            'success' => true,
            'page_view_id' => $pageView->id,
            'page_type' => $pageType,
        ]);
    }

    /**
     * Track an event.
     */
    public function trackEvent(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'session_token' => 'required|string|max:64',
            'page_view_id' => 'nullable|integer',
            'event_type' => 'required|string|max:50',
            'event_category' => 'nullable|string|max:50',
            'event_action' => 'nullable|string|max:100',
            'event_label' => 'nullable|string|max:255',
            'element_type' => 'nullable|string|max:50',
            'element_id' => 'nullable|string|max:100',
            'element_class' => 'nullable|string|max:255',
            'element_text' => 'nullable|string|max:255',
            'element_href' => 'nullable|string|max:500',
            'click_x' => 'nullable|integer',
            'click_y' => 'nullable|integer',
            'scroll_position' => 'nullable|numeric|min:0|max:100',
            'viewport_section' => 'nullable|string|max:50',
            'data' => 'nullable|array',
            'time_since_page_load_ms' => 'nullable|integer',
        ]);

        $session = VisitorSession::where('session_token', $validated['session_token'])->first();

        if (!$session) {
            return response()->json(['success' => false, 'error' => 'Session not found'], 404);
        }

        $pageView = null;
        if (!empty($validated['page_view_id'])) {
            $pageView = PageView::find($validated['page_view_id']);
        }

        $event = $this->createEvent($session, $pageView, $validated['event_type'], $validated);

        // Update session based on event type
        $this->handleEventSideEffects($session, $validated['event_type'], $validated);

        // Update intent score
        $this->intentService->updateSessionScore($session);

        return response()->json([
            'success' => true,
            'event_id' => $event->id,
            'intent_score' => $session->fresh()->intent_score,
        ]);
    }

    /**
     * Update page engagement metrics.
     */
    public function updateEngagement(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'session_token' => 'required|string|max:64',
            'page_view_id' => 'required|integer',
            'time_on_page_seconds' => 'required|integer|min:0',
            'engaged_time_seconds' => 'nullable|integer|min:0',
            'scroll_depth' => 'required|numeric|min:0|max:100',
            'scroll_events' => 'nullable|integer|min:0',
            'click_events' => 'nullable|integer|min:0',
        ]);

        $session = VisitorSession::where('session_token', $validated['session_token'])->first();

        if (!$session) {
            return response()->json(['success' => false, 'error' => 'Session not found'], 404);
        }

        $pageView = PageView::where('id', $validated['page_view_id'])
            ->where('visitor_session_id', $session->id)
            ->first();

        if (!$pageView) {
            return response()->json(['success' => false, 'error' => 'Page view not found'], 404);
        }

        $pageView->updateEngagement($validated);

        // Update session metrics
        $session->update([
            'total_time_seconds' => $session->pageViews()->sum('time_on_page_seconds'),
            'engaged_time_seconds' => $session->pageViews()->sum('engaged_time_seconds'),
            'scroll_depth_max' => max($session->scroll_depth_max, $validated['scroll_depth']),
        ]);

        $session->updateActivity();

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Link session to a lead.
     */
    public function linkLead(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'session_token' => 'required|string|max:64',
            'lead_id' => 'required|string', // UUID
        ]);

        $session = VisitorSession::where('session_token', $validated['session_token'])->first();

        if (!$session) {
            return response()->json(['success' => false, 'error' => 'Session not found'], 404);
        }

        $lead = \App\Models\Lead::where('uuid', $validated['lead_id'])->first();

        if (!$lead) {
            return response()->json(['success' => false, 'error' => 'Lead not found'], 404);
        }

        $session->linkToLead($lead);
        $session->markFormStarted();

        // Update intent score
        $this->intentService->updateSessionScore($session);

        return response()->json([
            'success' => true,
            'intent_score' => $session->fresh()->intent_score,
        ]);
    }

    /**
     * End a session.
     */
    public function endSession(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'session_token' => 'required|string|max:64',
        ]);

        $session = VisitorSession::where('session_token', $validated['session_token'])->first();

        if (!$session) {
            return response()->json(['success' => false, 'error' => 'Session not found'], 404);
        }

        // Mark last page view as exit
        $lastPageView = $session->pageViews()->whereNull('exited_at')->latest('entered_at')->first();
        if ($lastPageView) {
            $lastPageView->markAsExit();
        }

        $session->endSession();

        // Final intent score update
        $this->intentService->updateSessionScore($session);

        return response()->json([
            'success' => true,
            'final_intent_score' => $session->fresh()->intent_score,
        ]);
    }

    /**
     * Create an event record.
     */
    private function createEvent(VisitorSession $session, ?PageView $pageView, string $eventType, array $data): VisitorEvent
    {
        $intentPoints = VisitorEvent::getIntentPoints($eventType, $data);

        $event = VisitorEvent::create([
            'visitor_session_id' => $session->id,
            'page_view_id' => $pageView?->id,
            'event_type' => $eventType,
            'event_category' => $data['event_category'] ?? $this->getEventCategory($eventType),
            'event_action' => $data['event_action'] ?? null,
            'event_label' => $data['event_label'] ?? null,
            'element_type' => $data['element_type'] ?? null,
            'element_id' => $data['element_id'] ?? null,
            'element_class' => $data['element_class'] ?? null,
            'element_text' => isset($data['element_text']) ? Str::limit($data['element_text'], 250) : null,
            'element_href' => $data['element_href'] ?? null,
            'click_x' => $data['click_x'] ?? null,
            'click_y' => $data['click_y'] ?? null,
            'scroll_position' => $data['scroll_position'] ?? null,
            'viewport_section' => $data['viewport_section'] ?? null,
            'data' => $data['data'] ?? null,
            'intent_points' => $intentPoints,
            'is_conversion_event' => VisitorEvent::isConversionEvent($eventType),
            'is_engagement_event' => VisitorEvent::isEngagementEvent($eventType),
            'time_since_page_load_ms' => $data['time_since_page_load_ms'] ?? null,
            'time_since_session_start_ms' => $session->started_at
                ? (int) abs(now()->diffInMilliseconds($session->started_at))
                : null,
            'occurred_at' => now(),
        ]);

        $session->incrementEvents();

        return $event;
    }

    /**
     * Handle side effects of specific event types.
     */
    private function handleEventSideEffects(VisitorSession $session, string $eventType, array $data): void
    {
        switch ($eventType) {
            case VisitorEvent::TYPE_CTA_CLICK:
                $session->markCtaClicked();
                break;

            case VisitorEvent::TYPE_VIDEO_PLAY:
            case VisitorEvent::TYPE_VIDEO_COMPLETE:
                $session->markVideoWatched();
                break;

            case VisitorEvent::TYPE_FORM_START:
                $session->markFormStarted();
                break;

            case VisitorEvent::TYPE_FORM_SUBMIT:
                $session->markFormCompleted();
                break;
        }

        $session->updateActivity();
    }

    /**
     * Determine the device type.
     */
    private function getDeviceType(Agent $agent): string
    {
        if ($agent->isTablet()) {
            return 'tablet';
        }
        if ($agent->isMobile()) {
            return 'mobile';
        }
        return 'desktop';
    }

    /**
     * Determine referrer type.
     */
    private function determineReferrerType(?string $domain, array $validated): string
    {
        if (empty($domain)) {
            return 'direct';
        }

        // Check UTM medium first
        if (!empty($validated['utm_medium'])) {
            $medium = strtolower($validated['utm_medium']);
            if (in_array($medium, ['cpc', 'ppc', 'paid', 'paidsearch', 'paidsocial'])) {
                return 'paid';
            }
            if (in_array($medium, ['email', 'newsletter'])) {
                return 'email';
            }
        }

        // Check domain patterns
        $searchEngines = ['google', 'bing', 'yahoo', 'duckduckgo', 'baidu', 'yandex'];
        $socialNetworks = ['facebook', 'twitter', 'linkedin', 'instagram', 'pinterest', 'tiktok', 'youtube'];

        foreach ($searchEngines as $engine) {
            if (str_contains($domain, $engine)) {
                return !empty($validated['utm_source']) ? 'paid' : 'organic';
            }
        }

        foreach ($socialNetworks as $network) {
            if (str_contains($domain, $network)) {
                return 'social';
            }
        }

        return 'referral';
    }

    /**
     * Get event category from event type.
     */
    private function getEventCategory(string $eventType): string
    {
        $mapping = [
            VisitorEvent::TYPE_PAGE_VIEW => VisitorEvent::CATEGORY_NAVIGATION,
            VisitorEvent::TYPE_CLICK => VisitorEvent::CATEGORY_ENGAGEMENT,
            VisitorEvent::TYPE_SCROLL => VisitorEvent::CATEGORY_ENGAGEMENT,
            VisitorEvent::TYPE_FORM_START => VisitorEvent::CATEGORY_FORM,
            VisitorEvent::TYPE_FORM_FIELD => VisitorEvent::CATEGORY_FORM,
            VisitorEvent::TYPE_FORM_SUBMIT => VisitorEvent::CATEGORY_CONVERSION,
            VisitorEvent::TYPE_CTA_CLICK => VisitorEvent::CATEGORY_CONVERSION,
            VisitorEvent::TYPE_VIDEO_PLAY => VisitorEvent::CATEGORY_VIDEO,
            VisitorEvent::TYPE_VIDEO_PROGRESS => VisitorEvent::CATEGORY_VIDEO,
            VisitorEvent::TYPE_VIDEO_COMPLETE => VisitorEvent::CATEGORY_VIDEO,
            VisitorEvent::TYPE_SHARE => VisitorEvent::CATEGORY_SOCIAL,
        ];

        return $mapping[$eventType] ?? VisitorEvent::CATEGORY_ENGAGEMENT;
    }
}
