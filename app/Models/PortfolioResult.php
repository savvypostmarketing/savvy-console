<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortfolioResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'portfolio_id',
        'result',
        'result_es',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    /**
     * Get the portfolio that owns this result.
     */
    public function portfolio(): BelongsTo
    {
        return $this->belongsTo(Portfolio::class);
    }

    /**
     * Get localized result.
     */
    public function getLocalizedResultAttribute(): string
    {
        $locale = app()->getLocale();
        return $locale === 'es' && $this->result_es ? $this->result_es : $this->result;
    }
}
