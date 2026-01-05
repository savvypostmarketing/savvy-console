<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PageView extends Model
{
    protected $fillable = [
        'visitor_session_id',
        'url',
        'path',
        'page_title',
        'page_type',
        'query_params',
        'hash',
        'previous_url',
        'previous_path',
        'time_on_page_seconds',
        'engaged_time_seconds',
        'scroll_depth',
        'scroll_depth_max',
        'scroll_events',
        'click_events',
        'mouse_movements',
        'key_presses',
        'read_content',
        'interacted',
        'bounced',
        'viewport_width',
        'viewport_height',
        'document_height',
        'exit_url',
        'is_exit_page',
        'load_time_ms',
        'dom_ready_ms',
        'first_contentful_paint_ms',
        'entered_at',
        'exited_at',
    ];

    protected $casts = [
        'query_params' => 'array',
        'read_content' => 'boolean',
        'interacted' => 'boolean',
        'bounced' => 'boolean',
        'is_exit_page' => 'boolean',
        'scroll_depth' => 'decimal:2',
        'scroll_depth_max' => 'decimal:2',
        'entered_at' => 'datetime',
        'exited_at' => 'datetime',
    ];

    // Relationships
    public function session(): BelongsTo
    {
        return $this->belongsTo(VisitorSession::class, 'visitor_session_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(VisitorEvent::class);
    }

    // Scopes
    public function scopeOfType($query, string $type)
    {
        return $query->where('page_type', $type);
    }

    public function scopeWithInteraction($query)
    {
        return $query->where('interacted', true);
    }

    public function scopeBounced($query)
    {
        return $query->where('bounced', true);
    }

    // Methods
    public function updateEngagement(array $data): void
    {
        $this->update([
            'time_on_page_seconds' => $data['time_on_page_seconds'] ?? $this->time_on_page_seconds,
            'engaged_time_seconds' => $data['engaged_time_seconds'] ?? $this->engaged_time_seconds,
            'scroll_depth' => $data['scroll_depth'] ?? $this->scroll_depth,
            'scroll_depth_max' => max($this->scroll_depth_max, $data['scroll_depth'] ?? 0),
            'scroll_events' => ($this->scroll_events ?? 0) + ($data['scroll_events'] ?? 0),
            'click_events' => ($this->click_events ?? 0) + ($data['click_events'] ?? 0),
            'interacted' => $this->interacted || ($data['interacted'] ?? false),
        ]);

        // Mark as read if spent enough time and scrolled
        if ($this->time_on_page_seconds >= 30 && $this->scroll_depth_max >= 50) {
            $this->update(['read_content' => true]);
        }
    }

    public function markAsExit(string $exitUrl = null): void
    {
        $timeOnPage = 0;
        if ($this->entered_at) {
            // Use absolute diff and cast to int to avoid negative/float values
            $timeOnPage = (int) abs(now()->diffInSeconds($this->entered_at));
        }

        $this->update([
            'is_exit_page' => true,
            'exit_url' => $exitUrl,
            'exited_at' => now(),
            'time_on_page_seconds' => $timeOnPage,
        ]);

        // Mark as bounced if no interaction
        if (!$this->interacted && $this->time_on_page_seconds < 10) {
            $this->update(['bounced' => true]);
        }
    }

    // Accessors
    public function getDurationAttribute(): int
    {
        if ($this->exited_at && $this->entered_at) {
            return (int) abs($this->exited_at->diffInSeconds($this->entered_at));
        }
        return $this->entered_at ? (int) abs(now()->diffInSeconds($this->entered_at)) : 0;
    }

    // Static helpers
    public static function determinePageType(string $path): string
    {
        $path = strtolower(trim($path, '/'));

        if (empty($path) || $path === 'es') {
            return 'home';
        }

        $patterns = [
            'services' => ['services', 'servicios'],
            'portfolio' => ['portfolio', 'portafolio', 'work', 'projects'],
            'about' => ['about', 'about-us', 'nosotros'],
            'contact' => ['contact', 'contacto', 'get-started'],
            'blog' => ['blog', 'news', 'articles'],
            'pricing' => ['pricing', 'precios', 'plans'],
            'industries' => ['industries', 'industrias'],
            'privacy' => ['privacy', 'privacidad', 'terms'],
        ];

        foreach ($patterns as $type => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($path, $keyword)) {
                    return $type;
                }
            }
        }

        return 'other';
    }
}
