<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PageView;
use App\Models\VisitorEvent;
use App\Models\VisitorSession;
use App\Services\IntentScoringService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class VisitorAnalyticsController extends Controller
{
    /**
     * Display the analytics dashboard.
     */
    public function index(Request $request): Response
    {
        $period = $request->get('period', '7d');
        $startDate = $this->getStartDate($period);

        // Overview stats
        $stats = $this->getOverviewStats($startDate);

        // Recent sessions with high intent
        $hotSessions = VisitorSession::where('intent_level', 'hot')
            ->orWhere('intent_level', 'qualified')
            ->where('created_at', '>=', $startDate)
            ->orderBy('intent_score', 'desc')
            ->limit(10)
            ->get()
            ->map(fn ($session) => $this->formatSession($session));

        // Active sessions (last 30 minutes)
        $activeSessions = VisitorSession::recent(30)
            ->orderBy('last_activity_at', 'desc')
            ->limit(20)
            ->get()
            ->map(fn ($session) => $this->formatSession($session));

        // Traffic sources breakdown
        $trafficSources = VisitorSession::where('created_at', '>=', $startDate)
            ->select('referrer_type', DB::raw('count(*) as count'))
            ->groupBy('referrer_type')
            ->orderBy('count', 'desc')
            ->get();

        // Intent distribution
        $intentDistribution = VisitorSession::where('created_at', '>=', $startDate)
            ->select('intent_level', DB::raw('count(*) as count'))
            ->groupBy('intent_level')
            ->orderBy('count', 'desc')
            ->get();

        // Top pages
        $topPages = PageView::where('created_at', '>=', $startDate)
            ->select('path', 'page_type', DB::raw('count(*) as views'), DB::raw('avg(time_on_page_seconds) as avg_time'))
            ->groupBy('path', 'page_type')
            ->orderBy('views', 'desc')
            ->limit(10)
            ->get();

        // Daily visitors chart data
        $dailyVisitors = $this->getDailyVisitors($startDate);

        return Inertia::render('Admin/Analytics/Index', [
            'stats' => $stats,
            'hotSessions' => $hotSessions,
            'activeSessions' => $activeSessions,
            'trafficSources' => $trafficSources,
            'intentDistribution' => $intentDistribution,
            'topPages' => $topPages,
            'dailyVisitors' => $dailyVisitors,
            'period' => $period,
            'periods' => [
                '24h' => 'Last 24 Hours',
                '7d' => 'Last 7 Days',
                '30d' => 'Last 30 Days',
                '90d' => 'Last 90 Days',
            ],
        ]);
    }

    /**
     * Display session details.
     */
    public function showSession(VisitorSession $session): Response
    {
        $session->load(['lead', 'pageViews', 'events']);

        // Calculate intent score breakdown
        $intentService = new IntentScoringService();
        $intentBreakdown = $intentService->calculateScore($session);

        return Inertia::render('Admin/Analytics/Session', [
            'session' => $this->formatSession($session, true),
            'pageViews' => $session->pageViews->map(fn ($pv) => [
                'id' => $pv->id,
                'path' => $pv->path,
                'page_type' => $pv->page_type,
                'page_title' => $pv->page_title,
                'time_on_page' => $pv->time_on_page_seconds,
                'scroll_depth' => $pv->scroll_depth_max,
                'entered_at' => $pv->entered_at->format('H:i:s'),
                'exited_at' => $pv->exited_at?->format('H:i:s'),
                'interacted' => $pv->interacted,
                'bounced' => $pv->bounced,
            ]),
            'events' => $session->events()
                ->orderBy('occurred_at', 'desc')
                ->limit(100)
                ->get()
                ->map(fn ($event) => [
                    'id' => $event->id,
                    'type' => $event->event_type,
                    'category' => $event->event_category,
                    'action' => $event->event_action,
                    'label' => $event->event_label,
                    'element_text' => $event->element_text,
                    'intent_points' => $event->intent_points,
                    'occurred_at' => $event->occurred_at->format('H:i:s'),
                ]),
            'intentBreakdown' => $intentBreakdown,
        ]);
    }

    /**
     * Get live sessions for real-time dashboard.
     */
    public function liveSessions(Request $request)
    {
        $sessions = VisitorSession::recent(5) // Last 5 minutes
            ->orderBy('last_activity_at', 'desc')
            ->limit(50)
            ->get()
            ->map(fn ($session) => $this->formatSession($session));

        return response()->json([
            'sessions' => $sessions,
            'count' => $sessions->count(),
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Get overview statistics.
     */
    private function getOverviewStats(\DateTime $startDate): array
    {
        $totalSessions = VisitorSession::where('created_at', '>=', $startDate)->count();
        $uniqueVisitors = VisitorSession::where('created_at', '>=', $startDate)
            ->distinct('visitor_id')
            ->count('visitor_id');
        $totalPageViews = PageView::where('created_at', '>=', $startDate)->count();
        $avgSessionDuration = VisitorSession::where('created_at', '>=', $startDate)
            ->where('status', 'ended')
            ->avg('total_time_seconds') ?? 0;
        $avgPagesPerSession = $totalSessions > 0 ? $totalPageViews / $totalSessions : 0;
        $bounceRate = $this->calculateBounceRate($startDate);
        $conversionRate = $this->calculateConversionRate($startDate);

        // Intent metrics
        $hotLeads = VisitorSession::where('created_at', '>=', $startDate)
            ->where('intent_level', 'hot')
            ->count();
        $qualifiedLeads = VisitorSession::where('created_at', '>=', $startDate)
            ->where('intent_level', 'qualified')
            ->count();
        $avgIntentScore = VisitorSession::where('created_at', '>=', $startDate)
            ->avg('intent_score') ?? 0;

        // Returning visitors
        $returningVisitors = VisitorSession::where('created_at', '>=', $startDate)
            ->where('is_returning', true)
            ->count();
        $returningRate = $totalSessions > 0 ? ($returningVisitors / $totalSessions) * 100 : 0;

        return [
            'total_sessions' => $totalSessions,
            'unique_visitors' => $uniqueVisitors,
            'total_page_views' => $totalPageViews,
            'avg_session_duration' => round($avgSessionDuration),
            'avg_pages_per_session' => round($avgPagesPerSession, 1),
            'bounce_rate' => round($bounceRate, 1),
            'conversion_rate' => round($conversionRate, 1),
            'hot_leads' => $hotLeads,
            'qualified_leads' => $qualifiedLeads,
            'avg_intent_score' => round($avgIntentScore, 1),
            'returning_rate' => round($returningRate, 1),
        ];
    }

    /**
     * Calculate bounce rate.
     */
    private function calculateBounceRate(\DateTime $startDate): float
    {
        $totalSessions = VisitorSession::where('created_at', '>=', $startDate)->count();
        if ($totalSessions === 0) return 0;

        $bouncedSessions = VisitorSession::where('created_at', '>=', $startDate)
            ->where('page_views_count', 1)
            ->where('total_time_seconds', '<', 10)
            ->count();

        return ($bouncedSessions / $totalSessions) * 100;
    }

    /**
     * Calculate conversion rate.
     */
    private function calculateConversionRate(\DateTime $startDate): float
    {
        $totalSessions = VisitorSession::where('created_at', '>=', $startDate)->count();
        if ($totalSessions === 0) return 0;

        $conversions = VisitorSession::where('created_at', '>=', $startDate)
            ->where('completed_form', true)
            ->count();

        return ($conversions / $totalSessions) * 100;
    }

    /**
     * Get daily visitors data for chart.
     */
    private function getDailyVisitors(\DateTime $startDate): array
    {
        return VisitorSession::where('created_at', '>=', $startDate)
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('count(*) as sessions'),
                DB::raw('count(distinct visitor_id) as visitors'),
                DB::raw('avg(intent_score) as avg_intent')
            )
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get()
            ->map(fn ($day) => [
                'date' => $day->date,
                'sessions' => $day->sessions,
                'visitors' => $day->visitors,
                'avg_intent' => round($day->avg_intent ?? 0, 1),
            ])
            ->toArray();
    }

    /**
     * Format session for API response.
     */
    private function formatSession(VisitorSession $session, bool $detailed = false): array
    {
        $data = [
            'id' => $session->id,
            'uuid' => $session->uuid,
            'visitor_id' => substr($session->visitor_id, 0, 12) . '...',
            'intent_score' => $session->intent_score,
            'intent_level' => $session->intent_level,
            'intent_color' => IntentScoringService::getLevelColor($session->intent_level),
            'status' => $session->status,
            'page_views' => $session->page_views_count,
            'events' => $session->events_count,
            'duration' => $session->total_time_seconds,
            'duration_formatted' => $this->formatDuration($session->total_time_seconds),
            'device' => $session->device_type,
            'browser' => $session->browser,
            'country' => $session->country_name ?? $session->country,
            'city' => $session->city,
            'referrer_type' => $session->referrer_type,
            'landing_page' => $session->landing_page,
            'is_returning' => $session->is_returning,
            'has_lead' => $session->lead_id !== null,
            'started_form' => $session->started_form,
            'completed_form' => $session->completed_form,
            'started_at' => $session->started_at?->format('Y-m-d H:i:s'),
            'last_activity' => $session->last_activity_at?->diffForHumans(),
        ];

        if ($detailed) {
            $data['ip_address'] = $session->ip_address;
            $data['user_agent'] = $session->user_agent;
            $data['utm_source'] = $session->utm_source;
            $data['utm_medium'] = $session->utm_medium;
            $data['utm_campaign'] = $session->utm_campaign;
            $data['referrer_url'] = $session->referrer_url;
            $data['scroll_depth_max'] = $session->scroll_depth_max;
            $data['visited_pricing'] = $session->visited_pricing;
            $data['visited_services'] = $session->visited_services;
            $data['visited_portfolio'] = $session->visited_portfolio;
            $data['visited_contact'] = $session->visited_contact;
            $data['clicked_cta'] = $session->clicked_cta;
            $data['watched_video'] = $session->watched_video;
            $data['intent_signals'] = $session->intent_signals;
            $data['lead_id'] = $session->lead?->uuid;
        }

        return $data;
    }

    /**
     * Format duration in seconds to human readable.
     */
    private function formatDuration(int $seconds): string
    {
        if ($seconds < 60) {
            return $seconds . 's';
        }
        if ($seconds < 3600) {
            $minutes = floor($seconds / 60);
            $secs = $seconds % 60;
            return $minutes . 'm ' . $secs . 's';
        }
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        return $hours . 'h ' . $minutes . 'm';
    }

    /**
     * Get start date based on period.
     */
    private function getStartDate(string $period): \DateTime
    {
        return match ($period) {
            '24h' => now()->subDay(),
            '7d' => now()->subDays(7),
            '30d' => now()->subDays(30),
            '90d' => now()->subDays(90),
            default => now()->subDays(7),
        };
    }
}
