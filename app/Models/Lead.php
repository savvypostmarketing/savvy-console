<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Lead extends Model
{
    use HasFactory, SoftDeletes;

    // Source site constants
    public const SITE_POST_MARKETING = 'savvypostmarketing';
    public const SITE_TECH_INNOVATION = 'savvytechinnovation';

    public const SITES = [
        self::SITE_POST_MARKETING => 'Savvy Post Marketing',
        self::SITE_TECH_INNOVATION => 'Savvy Tech Innovation',
    ];

    protected $fillable = [
        'uuid',
        'name',
        'email',
        'company',
        'has_website',
        'website_url',
        'industry',
        'other_industry',
        'services',
        'message',
        'discovery_answers',
        'ip_address',
        'country',
        'country_name',
        'city',
        'user_agent',
        'referrer',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_term',
        'utm_content',
        'session_id',
        'fingerprint',
        'status',
        'current_step',
        'total_steps',
        'terms_accepted',
        'is_spam',
        'spam_score',
        'honeypot',
        'locale',
        'source_site',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'services' => 'array',
        'discovery_answers' => 'array',
        'terms_accepted' => 'boolean',
        'is_spam' => 'boolean',
        'spam_score' => 'float',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected $hidden = [
        'ip_address',
        'user_agent',
        'fingerprint',
        'honeypot',
        'spam_score',
        'is_spam',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($lead) {
            if (empty($lead->uuid)) {
                $lead->uuid = (string) Str::uuid();
            }
            if (empty($lead->started_at)) {
                $lead->started_at = now();
            }
        });
    }

    /**
     * Get the steps for this lead
     */
    public function steps(): HasMany
    {
        return $this->hasMany(LeadStep::class)->orderBy('step_number');
    }

    /**
     * Get the attempts for this lead
     */
    public function attempts(): HasMany
    {
        return $this->hasMany(LeadAttempt::class);
    }

    /**
     * Get the visitor sessions linked to this lead
     */
    public function visitorSessions(): HasMany
    {
        return $this->hasMany(VisitorSession::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get the most recent visitor session
     */
    public function latestVisitorSession(): HasOne
    {
        return $this->hasOne(VisitorSession::class)->latestOfMany();
    }

    /**
     * Mark the lead as completed
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    /**
     * Mark the lead as abandoned
     */
    public function markAsAbandoned(): void
    {
        $this->update([
            'status' => 'abandoned',
        ]);
    }

    /**
     * Check if lead is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Get the industry display name
     */
    public function getIndustryDisplayAttribute(): string
    {
        if ($this->industry === 'Other' || $this->industry === 'Otro') {
            return $this->other_industry ?? $this->industry;
        }
        return $this->industry ?? '';
    }

    /**
     * Get services as formatted string
     */
    public function getServicesDisplayAttribute(): string
    {
        if (empty($this->services)) {
            return '';
        }
        return implode(', ', $this->services);
    }

    /**
     * Scope for non-spam leads
     */
    public function scopeNotSpam($query)
    {
        return $query->where('is_spam', false);
    }

    /**
     * Scope for completed leads
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for in progress leads
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Scope for leads from a specific site
     */
    public function scopeFromSite($query, string $site)
    {
        return $query->where('source_site', $site);
    }

    /**
     * Get the site display name
     */
    public function getSiteDisplayAttribute(): string
    {
        return self::SITES[$this->source_site] ?? $this->source_site;
    }
}
