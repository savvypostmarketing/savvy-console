<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortfolioFeature extends Model
{
    use HasFactory;

    protected $fillable = [
        'portfolio_id',
        'number',
        'title',
        'title_es',
        'description',
        'description_es',
        'icon',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    /**
     * Get the portfolio that owns this feature.
     */
    public function portfolio(): BelongsTo
    {
        return $this->belongsTo(Portfolio::class);
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
}
