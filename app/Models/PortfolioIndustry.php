<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PortfolioIndustry extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_es',
        'slug',
        'description',
        'description_es',
        'icon',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get the portfolios for this industry.
     */
    public function portfolios(): HasMany
    {
        return $this->hasMany(Portfolio::class, 'industry_id');
    }

    /**
     * Get the published portfolios for this industry.
     */
    public function publishedPortfolios(): HasMany
    {
        return $this->hasMany(Portfolio::class, 'industry_id')
            ->where('is_published', true);
    }

    /**
     * Scope for active industries.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for ordering.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Get localized name.
     */
    public function getLocalizedNameAttribute(): string
    {
        $locale = app()->getLocale();
        return $locale === 'es' && $this->name_es ? $this->name_es : $this->name;
    }

    /**
     * Get localized description.
     */
    public function getLocalizedDescriptionAttribute(): ?string
    {
        $locale = app()->getLocale();
        return $locale === 'es' && $this->description_es ? $this->description_es : $this->description;
    }
}
