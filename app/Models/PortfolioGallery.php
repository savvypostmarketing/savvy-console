<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortfolioGallery extends Model
{
    use HasFactory;

    protected $table = 'portfolio_gallery';

    protected $fillable = [
        'portfolio_id',
        'image_path',
        'alt_text',
        'alt_text_es',
        'caption',
        'caption_es',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    /**
     * Get the portfolio that owns this image.
     */
    public function portfolio(): BelongsTo
    {
        return $this->belongsTo(Portfolio::class);
    }

    /**
     * Get localized alt text.
     */
    public function getLocalizedAltTextAttribute(): ?string
    {
        $locale = app()->getLocale();
        return $locale === 'es' && $this->alt_text_es ? $this->alt_text_es : $this->alt_text;
    }

    /**
     * Get localized caption.
     */
    public function getLocalizedCaptionAttribute(): ?string
    {
        $locale = app()->getLocale();
        return $locale === 'es' && $this->caption_es ? $this->caption_es : $this->caption;
    }
}
