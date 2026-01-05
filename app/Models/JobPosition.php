<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class JobPosition extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'title_es',
        'department',
        'employment_type',
        'location_type',
        'location',
        'description',
        'description_es',
        'linkedin_url',
        'apply_url',
        'salary_range',
        'is_active',
        'is_featured',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
    ];

    // Employment types
    public const TYPE_FULL_TIME = 'full-time';
    public const TYPE_PART_TIME = 'part-time';
    public const TYPE_CONTRACT = 'contract';
    public const TYPE_INTERNSHIP = 'internship';

    // Location types
    public const LOCATION_REMOTE = 'remote';
    public const LOCATION_HYBRID = 'hybrid';
    public const LOCATION_ON_SITE = 'on-site';

    /**
     * Get employment type options
     */
    public static function employmentTypes(): array
    {
        return [
            self::TYPE_FULL_TIME => 'Full Time',
            self::TYPE_PART_TIME => 'Part Time',
            self::TYPE_CONTRACT => 'Contract',
            self::TYPE_INTERNSHIP => 'Internship',
        ];
    }

    /**
     * Get location type options
     */
    public static function locationTypes(): array
    {
        return [
            self::LOCATION_REMOTE => 'Remote',
            self::LOCATION_HYBRID => 'Hybrid',
            self::LOCATION_ON_SITE => 'On-site',
        ];
    }

    /**
     * Scope to active positions
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to featured positions
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope ordered by sort_order
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('created_at', 'desc');
    }

    /**
     * Get the apply URL (LinkedIn or custom)
     */
    public function getApplyLinkAttribute(): ?string
    {
        return $this->linkedin_url ?: $this->apply_url;
    }

    /**
     * Get localized title
     */
    public function getLocalizedTitle(string $locale = 'en'): string
    {
        if ($locale === 'es' && $this->title_es) {
            return $this->title_es;
        }
        return $this->title;
    }

    /**
     * Get localized description
     */
    public function getLocalizedDescription(string $locale = 'en'): ?string
    {
        if ($locale === 'es' && $this->description_es) {
            return $this->description_es;
        }
        return $this->description;
    }

    /**
     * Get employment type label
     */
    public function getEmploymentTypeLabelAttribute(): string
    {
        return self::employmentTypes()[$this->employment_type] ?? $this->employment_type;
    }

    /**
     * Get location type label
     */
    public function getLocationTypeLabelAttribute(): string
    {
        return self::locationTypes()[$this->location_type] ?? $this->location_type;
    }
}
