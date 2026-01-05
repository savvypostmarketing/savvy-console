<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\LeadAttempt;
use App\Models\BlockedIp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class SpamDetectionService
{
    protected int $spamThreshold = 50;

    /**
     * Detect if the request is spam
     */
    public function detect(Request $request, ?Lead $lead = null): array
    {
        $score = 0;
        $reasons = [];

        // 1. Honeypot check (bots fill hidden fields)
        $honeypot = $request->input('honeypot');
        if (!empty($honeypot)) {
            $score += 100;
            $reasons[] = 'honeypot_filled';
        }

        // 2. Form fill time check (bots are too fast)
        $fillTime = $request->input('form_fill_time_ms');
        if ($fillTime !== null) {
            if ($fillTime < 1000) { // Less than 1 second
                $score += 80;
                $reasons[] = 'filled_extremely_fast';
            } elseif ($fillTime < 3000) { // Less than 3 seconds
                $score += 40;
                $reasons[] = 'filled_very_fast';
            }
        }

        // 3. IP reputation check
        $ip = $request->ip();
        $ipScore = $this->checkIpReputation($ip);
        if ($ipScore > 0) {
            $score += $ipScore;
            $reasons[] = 'suspicious_ip';
        }

        // 4. Too many attempts from same IP in short time
        $recentAttempts = LeadAttempt::where('ip_address', $ip)
            ->where('created_at', '>', now()->subMinutes(10))
            ->count();

        if ($recentAttempts > 20) {
            $score += 60;
            $reasons[] = 'too_many_attempts';

            // Auto-block if excessive
            if ($recentAttempts > 50) {
                BlockedIp::blockIp($ip, 'Excessive attempts', 60);
            }
        }

        // 5. Email validation
        if ($lead && $lead->email) {
            $emailScore = $this->checkEmail($lead->email);
            if ($emailScore > 0) {
                $score += $emailScore;
                $reasons[] = 'suspicious_email';
            }
        }

        // 6. Content check (spam keywords)
        $contentScore = $this->checkContent($request->all());
        if ($contentScore > 0) {
            $score += $contentScore;
            $reasons[] = 'spam_content';
        }

        // 7. User agent check
        $userAgent = $request->userAgent();
        if ($this->isSuspiciousUserAgent($userAgent)) {
            $score += 30;
            $reasons[] = 'suspicious_user_agent';
        }

        // 8. Missing or suspicious headers
        if (!$request->header('Accept-Language')) {
            $score += 10;
            $reasons[] = 'missing_headers';
        }

        return [
            'is_spam' => $score >= $this->spamThreshold,
            'score' => min($score, 100),
            'reasons' => $reasons,
        ];
    }

    /**
     * Check IP reputation
     */
    protected function checkIpReputation(string $ip): int
    {
        // Check cache first
        $cacheKey = 'ip_reputation:' . $ip;
        $cached = Cache::get($cacheKey);

        if ($cached !== null) {
            return $cached;
        }

        $score = 0;

        // Check if IP has previous spam attempts
        $spamAttempts = LeadAttempt::where('ip_address', $ip)
            ->where('is_spam', true)
            ->count();

        if ($spamAttempts > 0) {
            $score += min($spamAttempts * 10, 50);
        }

        // Check if IP is in blocked list
        if (BlockedIp::isIpBlocked($ip)) {
            $score += 100;
        }

        // Cache for 1 hour
        Cache::put($cacheKey, $score, 3600);

        return $score;
    }

    /**
     * Check email for spam indicators
     */
    protected function checkEmail(string $email): int
    {
        $score = 0;

        // Disposable email domains
        $disposableDomains = [
            'tempmail.com', 'throwaway.com', 'guerrillamail.com',
            'mailinator.com', '10minutemail.com', 'temp-mail.org',
            'fakeinbox.com', 'trashmail.com', 'yopmail.com',
        ];

        $domain = strtolower(substr(strrchr($email, '@'), 1));

        if (in_array($domain, $disposableDomains)) {
            $score += 50;
        }

        // Check for suspicious patterns
        if (preg_match('/^[a-z]{1,2}\d{5,}@/', $email)) {
            $score += 30; // Random looking email like "ab12345@..."
        }

        return $score;
    }

    /**
     * Check content for spam keywords
     */
    protected function checkContent(array $data): int
    {
        $score = 0;

        $spamKeywords = [
            'viagra', 'cialis', 'casino', 'lottery', 'winner',
            'click here', 'free money', 'act now', 'limited time',
            'buy now', 'order now', 'special offer', 'amazing deal',
            'crypto', 'bitcoin', 'investment opportunity',
        ];

        $content = strtolower(json_encode($data));

        foreach ($spamKeywords as $keyword) {
            if (str_contains($content, $keyword)) {
                $score += 20;
            }
        }

        // Check for excessive URLs
        $urlCount = preg_match_all('/https?:\/\//', $content);
        if ($urlCount > 3) {
            $score += 20;
        }

        return min($score, 60);
    }

    /**
     * Check if user agent is suspicious
     */
    protected function isSuspiciousUserAgent(?string $userAgent): bool
    {
        if (empty($userAgent)) {
            return true;
        }

        $suspiciousPatterns = [
            'curl', 'wget', 'python', 'java/', 'perl',
            'bot', 'spider', 'crawler', 'scraper',
            'headless', 'phantom', 'selenium',
        ];

        $ua = strtolower($userAgent);

        foreach ($suspiciousPatterns as $pattern) {
            if (str_contains($ua, $pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Verify reCAPTCHA token
     */
    public function verifyRecaptcha(string $token): bool
    {
        $secretKey = config('services.recaptcha.secret_key');

        if (empty($secretKey)) {
            return true; // Skip if not configured
        }

        try {
            $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => $secretKey,
                'response' => $token,
            ]);

            $result = $response->json();

            return $result['success'] ?? false;
        } catch (\Exception $e) {
            // Log error but don't block
            return true;
        }
    }
}
