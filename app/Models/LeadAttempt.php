<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'ip_address',
        'user_agent',
        'session_id',
        'fingerprint',
        'action',
        'step_id',
        'request_data',
        'is_spam',
        'spam_score',
        'spam_reasons',
        'honeypot_value',
        'form_fill_time_ms',
        'rate_limited',
        'response_code',
        'success',
        'error_message',
    ];

    protected $casts = [
        'request_data' => 'array',
        'spam_reasons' => 'array',
        'is_spam' => 'boolean',
        'spam_score' => 'float',
        'rate_limited' => 'boolean',
        'success' => 'boolean',
    ];

    /**
     * Get the lead associated with this attempt
     */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    /**
     * Check if this attempt looks like spam
     */
    public function detectSpam(): array
    {
        $reasons = [];
        $score = 0;

        // Honeypot filled (bots fill hidden fields)
        if (!empty($this->honeypot_value)) {
            $reasons[] = 'honeypot_filled';
            $score += 100;
        }

        // Form filled too fast (under 3 seconds)
        if ($this->form_fill_time_ms && $this->form_fill_time_ms < 3000) {
            $reasons[] = 'filled_too_fast';
            $score += 50;
        }

        // Form filled suspiciously fast (under 1 second)
        if ($this->form_fill_time_ms && $this->form_fill_time_ms < 1000) {
            $score += 50; // Add more points
        }

        return [
            'is_spam' => $score >= 50,
            'score' => min($score, 100),
            'reasons' => $reasons,
        ];
    }

    /**
     * Scope for spam attempts
     */
    public function scopeSpam($query)
    {
        return $query->where('is_spam', true);
    }

    /**
     * Scope for successful attempts
     */
    public function scopeSuccessful($query)
    {
        return $query->where('success', true);
    }

    /**
     * Scope for rate limited attempts
     */
    public function scopeRateLimited($query)
    {
        return $query->where('rate_limited', true);
    }
}
