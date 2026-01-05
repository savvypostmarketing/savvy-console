<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class VisitorSession extends Model
{
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
            if (empty($model->session_token)) {
                $model->session_token = Str::random(64);
            }
            if (empty($model->started_at)) {
                $model->started_at = now();
            }
            $model->last_activity_at = now();
        });
    }

    protected $fillable = [
        'visitor_id',
        'session_token',
        'lead_id',
        'ip_address',
        'user_agent',
        'device_type',
        'browser',
        'browser_version',
        'os',
        'os_version',
        'is_bot',
        'country',
        'country_name',
        'region',
        'city',
        'latitude',
        'longitude',
        'timezone',
        'referrer_url',
        'referrer_domain',
        'referrer_type',
        'landing_page',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_term',
        'utm_content',
        'page_views_count',
        'events_count',
        'total_time_seconds',
        'engaged_time_seconds',
        'scroll_depth_avg',
        'scroll_depth_max',
        'intent_score',
        'intent_level',
        'intent_signals',
        'visited_pricing',
        'visited_services',
        'visited_portfolio',
        'visited_contact',
        'started_form',
        'completed_form',
        'clicked_cta',
        'watched_video',
        'is_returning',
        'previous_sessions_count',
        'first_seen_at',
        'status',
        'started_at',
        'last_activity_at',
        'ended_at',
        'locale',
        'accept_language',
    ];

    protected $casts = [
        'intent_signals' => 'array',
        'is_bot' => 'boolean',
        'is_returning' => 'boolean',
        'visited_pricing' => 'boolean',
        'visited_services' => 'boolean',
        'visited_portfolio' => 'boolean',
        'visited_contact' => 'boolean',
        'started_form' => 'boolean',
        'completed_form' => 'boolean',
        'clicked_cta' => 'boolean',
        'watched_video' => 'boolean',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'intent_score' => 'decimal:2',
        'scroll_depth_avg' => 'decimal:2',
        'scroll_depth_max' => 'decimal:2',
        'started_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'ended_at' => 'datetime',
        'first_seen_at' => 'datetime',
    ];

    // Relationships
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function pageViews(): HasMany
    {
        return $this->hasMany(PageView::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(VisitorEvent::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeHot($query)
    {
        return $query->where('intent_level', 'hot');
    }

    public function scopeQualified($query)
    {
        return $query->where('intent_level', 'qualified');
    }

    public function scopeConverted($query)
    {
        return $query->where('completed_form', true);
    }

    public function scopeRecent($query, int $minutes = 30)
    {
        return $query->where('last_activity_at', '>=', now()->subMinutes($minutes));
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    // Methods
    public function updateActivity(): void
    {
        $this->update([
            'last_activity_at' => now(),
            'status' => 'active',
        ]);
    }

    public function markAsIdle(): void
    {
        $this->update(['status' => 'idle']);
    }

    public function endSession(): void
    {
        $totalTime = 0;
        if ($this->started_at) {
            $totalTime = (int) abs(now()->diffInSeconds($this->started_at));
        }

        $this->update([
            'status' => 'ended',
            'ended_at' => now(),
            'total_time_seconds' => $totalTime,
        ]);
    }

    public function incrementPageViews(): void
    {
        $this->increment('page_views_count');
    }

    public function incrementEvents(): void
    {
        $this->increment('events_count');
    }

    public function linkToLead(Lead $lead): void
    {
        $this->update(['lead_id' => $lead->id]);
    }

    public function updateIntentScore(float $score, string $level, array $signals = []): void
    {
        $this->update([
            'intent_score' => $score,
            'intent_level' => $level,
            'intent_signals' => $signals,
        ]);
    }

    public function markPageVisited(string $pageType): void
    {
        $mapping = [
            'pricing' => 'visited_pricing',
            'services' => 'visited_services',
            'portfolio' => 'visited_portfolio',
            'contact' => 'visited_contact',
        ];

        if (isset($mapping[$pageType])) {
            $this->update([$mapping[$pageType] => true]);
        }
    }

    public function markFormStarted(): void
    {
        $this->update(['started_form' => true]);
    }

    public function markFormCompleted(): void
    {
        $this->update(['completed_form' => true]);
    }

    public function markCtaClicked(): void
    {
        $this->update(['clicked_cta' => true]);
    }

    public function markVideoWatched(): void
    {
        $this->update(['watched_video' => true]);
    }

    // Accessors
    public function getDurationAttribute(): int
    {
        $endTime = $this->ended_at ?? now();
        return $this->started_at ? (int) abs($endTime->diffInSeconds($this->started_at)) : 0;
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active' &&
               $this->last_activity_at &&
               $this->last_activity_at->diffInMinutes(now()) < 30;
    }
}
