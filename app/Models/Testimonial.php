<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Testimonial extends Model
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
        'name',
        'role',
        'role_es',
        'company',
        'company_es',
        'avatar',
        'quote',
        'quote_es',
        'rating',
        'project_title',
        'project_title_es',
        'project_screenshot',
        'source',
        'services',
        'is_featured',
        'is_published',
        'sort_order',
        'date_label',
        'extra_info',
    ];

    protected $casts = [
        'services' => 'array',
        'is_featured' => 'boolean',
        'is_published' => 'boolean',
        'rating' => 'integer',
        'sort_order' => 'integer',
    ];

    /**
     * Scope for published testimonials.
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    /**
     * Scope for featured testimonials.
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
     * Scope by source.
     */
    public function scopeBySource($query, string $source)
    {
        return $query->where('source', $source);
    }

    /**
     * Scope by service.
     */
    public function scopeForService($query, string $service)
    {
        return $query->whereJsonContains('services', $service);
    }

    /**
     * Get localized role.
     */
    public function getLocalizedRoleAttribute(): ?string
    {
        $locale = app()->getLocale();
        return $locale === 'es' && $this->role_es ? $this->role_es : $this->role;
    }

    /**
     * Get localized company.
     */
    public function getLocalizedCompanyAttribute(): ?string
    {
        $locale = app()->getLocale();
        return $locale === 'es' && $this->company_es ? $this->company_es : $this->company;
    }

    /**
     * Get localized quote.
     */
    public function getLocalizedQuoteAttribute(): string
    {
        $locale = app()->getLocale();
        return $locale === 'es' && $this->quote_es ? $this->quote_es : $this->quote;
    }

    /**
     * Get localized project title.
     */
    public function getLocalizedProjectTitleAttribute(): ?string
    {
        $locale = app()->getLocale();
        return $locale === 'es' && $this->project_title_es ? $this->project_title_es : $this->project_title;
    }
}
