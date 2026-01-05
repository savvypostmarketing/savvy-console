<?php

namespace App\Services;

use App\Models\VisitorSession;

class IntentScoringService
{
    // Weight multipliers for different signals
    private const WEIGHTS = [
        'page_views' => 1.0,
        'time_on_site' => 1.5,
        'engagement' => 2.0,
        'form_interaction' => 3.0,
        'conversion_signals' => 4.0,
        'return_visitor' => 1.5,
    ];

    // Intent level thresholds
    private const LEVELS = [
        'cold' => 0,
        'warm' => 20,
        'hot' => 50,
        'qualified' => 80,
    ];

    /**
     * Calculate the intent score for a visitor session.
     */
    public function calculateScore(VisitorSession $session): array
    {
        $signals = [];
        $totalScore = 0;

        // 1. Page Views Score (max 15 points)
        $pageViewsScore = $this->calculatePageViewsScore($session);
        $signals['page_views'] = $pageViewsScore;
        $totalScore += $pageViewsScore['score'] * self::WEIGHTS['page_views'];

        // 2. Time on Site Score (max 15 points)
        $timeScore = $this->calculateTimeScore($session);
        $signals['time_on_site'] = $timeScore;
        $totalScore += $timeScore['score'] * self::WEIGHTS['time_on_site'];

        // 3. Engagement Score (max 20 points)
        $engagementScore = $this->calculateEngagementScore($session);
        $signals['engagement'] = $engagementScore;
        $totalScore += $engagementScore['score'] * self::WEIGHTS['engagement'];

        // 4. Form Interaction Score (max 25 points)
        $formScore = $this->calculateFormScore($session);
        $signals['form_interaction'] = $formScore;
        $totalScore += $formScore['score'] * self::WEIGHTS['form_interaction'];

        // 5. Conversion Signals Score (max 15 points)
        $conversionScore = $this->calculateConversionSignalsScore($session);
        $signals['conversion_signals'] = $conversionScore;
        $totalScore += $conversionScore['score'] * self::WEIGHTS['conversion_signals'];

        // 6. Return Visitor Bonus (max 10 points)
        $returnScore = $this->calculateReturnVisitorScore($session);
        $signals['return_visitor'] = $returnScore;
        $totalScore += $returnScore['score'] * self::WEIGHTS['return_visitor'];

        // Normalize to 0-100
        $normalizedScore = min(100, $totalScore);
        $level = $this->determineLevel($normalizedScore);

        return [
            'score' => round($normalizedScore, 2),
            'level' => $level,
            'signals' => $signals,
            'calculated_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Update the intent score for a session.
     */
    public function updateSessionScore(VisitorSession $session): void
    {
        $result = $this->calculateScore($session);
        $session->updateIntentScore($result['score'], $result['level'], $result['signals']);
    }

    /**
     * Calculate score based on page views.
     */
    private function calculatePageViewsScore(VisitorSession $session): array
    {
        $pageViews = $session->page_views_count;
        $score = 0;
        $details = [];

        // Base score for page views (diminishing returns)
        if ($pageViews >= 1) $score += 2;
        if ($pageViews >= 2) $score += 2;
        if ($pageViews >= 3) $score += 2;
        if ($pageViews >= 5) $score += 3;
        if ($pageViews >= 8) $score += 3;
        if ($pageViews >= 12) $score += 3;

        $details['count'] = $pageViews;

        // Bonus for visiting key pages
        if ($session->visited_pricing) {
            $score += 3;
            $details['visited_pricing'] = true;
        }
        if ($session->visited_services) {
            $score += 2;
            $details['visited_services'] = true;
        }
        if ($session->visited_portfolio) {
            $score += 2;
            $details['visited_portfolio'] = true;
        }
        if ($session->visited_contact) {
            $score += 3;
            $details['visited_contact'] = true;
        }

        return [
            'score' => min(15, $score),
            'max' => 15,
            'details' => $details,
        ];
    }

    /**
     * Calculate score based on time on site.
     */
    private function calculateTimeScore(VisitorSession $session): array
    {
        $totalTime = $session->total_time_seconds;
        $engagedTime = $session->engaged_time_seconds;
        $score = 0;
        $details = [];

        // Score based on total time (diminishing returns)
        if ($totalTime >= 30) $score += 2;
        if ($totalTime >= 60) $score += 2;
        if ($totalTime >= 120) $score += 2;
        if ($totalTime >= 180) $score += 2;
        if ($totalTime >= 300) $score += 3;
        if ($totalTime >= 600) $score += 4;

        // Bonus for engaged time (active interaction)
        $engagementRatio = $totalTime > 0 ? ($engagedTime / $totalTime) : 0;
        if ($engagementRatio >= 0.5) $score += 2;
        if ($engagementRatio >= 0.7) $score += 2;

        $details['total_seconds'] = $totalTime;
        $details['engaged_seconds'] = $engagedTime;
        $details['engagement_ratio'] = round($engagementRatio, 2);

        return [
            'score' => min(15, $score),
            'max' => 15,
            'details' => $details,
        ];
    }

    /**
     * Calculate score based on engagement metrics.
     */
    private function calculateEngagementScore(VisitorSession $session): array
    {
        $score = 0;
        $details = [];

        // Scroll depth
        $scrollDepth = $session->scroll_depth_max;
        if ($scrollDepth >= 25) $score += 2;
        if ($scrollDepth >= 50) $score += 2;
        if ($scrollDepth >= 75) $score += 2;
        if ($scrollDepth >= 90) $score += 2;
        $details['scroll_depth'] = $scrollDepth;

        // Events count
        $eventsCount = $session->events_count;
        if ($eventsCount >= 5) $score += 2;
        if ($eventsCount >= 10) $score += 2;
        if ($eventsCount >= 20) $score += 2;
        if ($eventsCount >= 50) $score += 2;
        $details['events_count'] = $eventsCount;

        // Video watched
        if ($session->watched_video) {
            $score += 3;
            $details['watched_video'] = true;
        }

        // CTA clicked
        if ($session->clicked_cta) {
            $score += 3;
            $details['clicked_cta'] = true;
        }

        return [
            'score' => min(20, $score),
            'max' => 20,
            'details' => $details,
        ];
    }

    /**
     * Calculate score based on form interaction.
     */
    private function calculateFormScore(VisitorSession $session): array
    {
        $score = 0;
        $details = [];

        if ($session->started_form) {
            $score += 10;
            $details['started'] = true;
        }

        if ($session->completed_form) {
            $score += 15;
            $details['completed'] = true;
        }

        // Check lead progress if linked
        if ($session->lead_id) {
            $lead = $session->lead;
            if ($lead) {
                $progress = $lead->total_steps > 0
                    ? ($lead->current_step / $lead->total_steps) * 100
                    : 0;

                if ($progress >= 25) $score += 2;
                if ($progress >= 50) $score += 3;
                if ($progress >= 75) $score += 5;

                $details['lead_progress'] = round($progress, 0);
            }
        }

        return [
            'score' => min(25, $score),
            'max' => 25,
            'details' => $details,
        ];
    }

    /**
     * Calculate score based on conversion signals.
     */
    private function calculateConversionSignalsScore(VisitorSession $session): array
    {
        $score = 0;
        $details = [];

        // Traffic source quality
        $sourceScores = [
            'paid' => 4,
            'organic' => 3,
            'referral' => 3,
            'email' => 4,
            'social' => 2,
            'direct' => 2,
        ];

        $referrerType = $session->referrer_type ?? 'direct';
        $sourceScore = $sourceScores[$referrerType] ?? 1;
        $score += $sourceScore;
        $details['referrer_type'] = $referrerType;
        $details['source_score'] = $sourceScore;

        // UTM campaign presence indicates intentional marketing
        if ($session->utm_campaign) {
            $score += 3;
            $details['has_campaign'] = true;
        }

        // Device type (desktop users often have higher intent for B2B)
        if ($session->device_type === 'desktop') {
            $score += 2;
        }
        $details['device_type'] = $session->device_type;

        // Business hours bonus (for B2B)
        $hour = now()->setTimezone($session->timezone ?? 'UTC')->hour;
        if ($hour >= 9 && $hour <= 18) {
            $score += 2;
            $details['business_hours'] = true;
        }

        return [
            'score' => min(15, $score),
            'max' => 15,
            'details' => $details,
        ];
    }

    /**
     * Calculate score bonus for return visitors.
     */
    private function calculateReturnVisitorScore(VisitorSession $session): array
    {
        $score = 0;
        $details = [];

        if ($session->is_returning) {
            $previousSessions = $session->previous_sessions_count;

            $score += 3; // Base bonus for returning
            if ($previousSessions >= 2) $score += 2;
            if ($previousSessions >= 3) $score += 2;
            if ($previousSessions >= 5) $score += 3;

            $details['previous_sessions'] = $previousSessions;
            $details['is_returning'] = true;
        }

        return [
            'score' => min(10, $score),
            'max' => 10,
            'details' => $details,
        ];
    }

    /**
     * Determine intent level based on score.
     */
    private function determineLevel(float $score): string
    {
        if ($score >= self::LEVELS['qualified']) {
            return 'qualified';
        }
        if ($score >= self::LEVELS['hot']) {
            return 'hot';
        }
        if ($score >= self::LEVELS['warm']) {
            return 'warm';
        }
        return 'cold';
    }

    /**
     * Get intent level color for UI.
     */
    public static function getLevelColor(string $level): string
    {
        return match ($level) {
            'qualified' => '#22c55e', // green
            'hot' => '#ef4444',       // red
            'warm' => '#f97316',      // orange
            default => '#6b7280',     // gray
        };
    }

    /**
     * Get intent level label.
     */
    public static function getLevelLabel(string $level): string
    {
        return match ($level) {
            'qualified' => 'Qualified Lead',
            'hot' => 'Hot Lead',
            'warm' => 'Warm Lead',
            default => 'Cold Visitor',
        };
    }
}
