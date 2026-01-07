<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Portfolio extends Model
{
    use HasFactory, SoftDeletes;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    protected $fillable = [
        'title',
        'title_es',
        'slug',
        'industry_id',
        'description',
        'description_es',
        'project_overview',
        'project_overview_es',
        'challenge',
        'challenge_es',
        'solution',
        'solution_es',
        'featured_image',
        'website_url',
        'testimonial_quote',
        'testimonial_quote_es',
        'testimonial_author',
        'testimonial_role',
        'testimonial_role_es',
        'testimonial_avatar',
        'video_url',
        'video_thumbnail',
        'video_intro_text',
        'video_intro_text_es',
        'is_published',
        'is_featured',
        'published_at',
        'sort_order',
        'meta_title',
        'meta_title_es',
        'meta_description',
        'meta_description_es',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'is_featured' => 'boolean',
        'sort_order' => 'integer',
        'published_at' => 'datetime',
    ];

    /**
     * Get the industry for this portfolio.
     */
    public function industry(): BelongsTo
    {
        return $this->belongsTo(PortfolioIndustry::class, 'industry_id');
    }

    /**
     * Get the services for this portfolio.
     */
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(PortfolioService::class, 'portfolio_portfolio_service')
            ->withTimestamps();
    }

    /**
     * Get the stats for this portfolio.
     */
    public function stats(): HasMany
    {
        return $this->hasMany(PortfolioStat::class)->orderBy('sort_order');
    }

    /**
     * Get the gallery images for this portfolio.
     */
    public function gallery(): HasMany
    {
        return $this->hasMany(PortfolioGallery::class)->orderBy('sort_order');
    }

    /**
     * Get the features for this portfolio.
     */
    public function features(): HasMany
    {
        return $this->hasMany(PortfolioFeature::class)->orderBy('sort_order');
    }

    /**
     * Get the results for this portfolio.
     */
    public function results(): HasMany
    {
        return $this->hasMany(PortfolioResult::class)->orderBy('sort_order');
    }

    /**
     * Get the video features for this portfolio.
     */
    public function videoFeatures(): HasMany
    {
        return $this->hasMany(PortfolioVideoFeature::class)->orderBy('sort_order');
    }

    /**
     * Scope for published portfolios.
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    /**
     * Scope for featured portfolios.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope for ordering.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('created_at', 'desc');
    }

    /**
     * Get localized title.
     */
    public function getLocalizedTitleAttribute(): string
    {
        $locale = app()->getLocale();
        return $locale === 'es' && $this->title_es ? $this->title_es : $this->title;
    }

    /**
     * Get localized description.
     */
    public function getLocalizedDescriptionAttribute(): ?string
    {
        $locale = app()->getLocale();
        return $locale === 'es' && $this->description_es ? $this->description_es : $this->description;
    }

    /**
     * Get localized challenge.
     */
    public function getLocalizedChallengeAttribute(): ?string
    {
        $locale = app()->getLocale();
        return $locale === 'es' && $this->challenge_es ? $this->challenge_es : $this->challenge;
    }

    /**
     * Get localized solution.
     */
    public function getLocalizedSolutionAttribute(): ?string
    {
        $locale = app()->getLocale();
        return $locale === 'es' && $this->solution_es ? $this->solution_es : $this->solution;
    }

    /**
     * Get localized testimonial quote.
     */
    public function getLocalizedTestimonialQuoteAttribute(): ?string
    {
        $locale = app()->getLocale();
        return $locale === 'es' && $this->testimonial_quote_es ? $this->testimonial_quote_es : $this->testimonial_quote;
    }

    /**
     * Get localized testimonial role.
     */
    public function getLocalizedTestimonialRoleAttribute(): ?string
    {
        $locale = app()->getLocale();
        return $locale === 'es' && $this->testimonial_role_es ? $this->testimonial_role_es : $this->testimonial_role;
    }

    /**
     * Get localized project overview.
     */
    public function getLocalizedProjectOverviewAttribute(): ?string
    {
        $locale = app()->getLocale();
        return $locale === 'es' && $this->project_overview_es ? $this->project_overview_es : $this->project_overview;
    }

    /**
     * Get localized video intro text.
     */
    public function getLocalizedVideoIntroTextAttribute(): ?string
    {
        $locale = app()->getLocale();
        return $locale === 'es' && $this->video_intro_text_es ? $this->video_intro_text_es : $this->video_intro_text;
    }

    /**
     * Get localized meta title.
     */
    public function getLocalizedMetaTitleAttribute(): ?string
    {
        $locale = app()->getLocale();
        return $locale === 'es' && $this->meta_title_es ? $this->meta_title_es : $this->meta_title;
    }

    /**
     * Get localized meta description.
     */
    public function getLocalizedMetaDescriptionAttribute(): ?string
    {
        $locale = app()->getLocale();
        return $locale === 'es' && $this->meta_description_es ? $this->meta_description_es : $this->meta_description;
    }
}
