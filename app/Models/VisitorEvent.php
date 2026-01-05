<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitorEvent extends Model
{
    protected $fillable = [
        'visitor_session_id',
        'page_view_id',
        'event_type',
        'event_category',
        'event_action',
        'event_label',
        'element_type',
        'element_id',
        'element_class',
        'element_text',
        'element_href',
        'click_x',
        'click_y',
        'scroll_position',
        'viewport_section',
        'data',
        'intent_points',
        'is_conversion_event',
        'is_engagement_event',
        'time_since_page_load_ms',
        'time_since_session_start_ms',
        'occurred_at',
    ];

    protected $casts = [
        'data' => 'array',
        'is_conversion_event' => 'boolean',
        'is_engagement_event' => 'boolean',
        'scroll_position' => 'decimal:2',
        'occurred_at' => 'datetime',
    ];

    // Event types constants
    public const TYPE_PAGE_VIEW = 'page_view';
    public const TYPE_CLICK = 'click';
    public const TYPE_SCROLL = 'scroll';
    public const TYPE_FORM_START = 'form_start';
    public const TYPE_FORM_FIELD = 'form_field';
    public const TYPE_FORM_SUBMIT = 'form_submit';
    public const TYPE_FORM_ERROR = 'form_error';
    public const TYPE_VIDEO_PLAY = 'video_play';
    public const TYPE_VIDEO_PROGRESS = 'video_progress';
    public const TYPE_VIDEO_COMPLETE = 'video_complete';
    public const TYPE_CTA_CLICK = 'cta_click';
    public const TYPE_OUTBOUND_LINK = 'outbound_link';
    public const TYPE_DOWNLOAD = 'download';
    public const TYPE_COPY = 'copy';
    public const TYPE_SHARE = 'share';
    public const TYPE_VISIBILITY_CHANGE = 'visibility_change';
    public const TYPE_SESSION_END = 'session_end';

    // Event categories
    public const CATEGORY_NAVIGATION = 'navigation';
    public const CATEGORY_ENGAGEMENT = 'engagement';
    public const CATEGORY_CONVERSION = 'conversion';
    public const CATEGORY_VIDEO = 'video';
    public const CATEGORY_FORM = 'form';
    public const CATEGORY_SOCIAL = 'social';

    // Relationships
    public function session(): BelongsTo
    {
        return $this->belongsTo(VisitorSession::class, 'visitor_session_id');
    }

    public function pageView(): BelongsTo
    {
        return $this->belongsTo(PageView::class);
    }

    // Scopes
    public function scopeOfType($query, string $type)
    {
        return $query->where('event_type', $type);
    }

    public function scopeConversion($query)
    {
        return $query->where('is_conversion_event', true);
    }

    public function scopeEngagement($query)
    {
        return $query->where('is_engagement_event', true);
    }

    public function scopeInCategory($query, string $category)
    {
        return $query->where('event_category', $category);
    }

    // Static methods for intent points
    public static function getIntentPoints(string $eventType, array $data = []): int
    {
        $points = [
            self::TYPE_PAGE_VIEW => 1,
            self::TYPE_CLICK => 2,
            self::TYPE_SCROLL => 1,
            self::TYPE_FORM_START => 15,
            self::TYPE_FORM_FIELD => 3,
            self::TYPE_FORM_SUBMIT => 50,
            self::TYPE_VIDEO_PLAY => 5,
            self::TYPE_VIDEO_PROGRESS => 3,
            self::TYPE_VIDEO_COMPLETE => 10,
            self::TYPE_CTA_CLICK => 20,
            self::TYPE_OUTBOUND_LINK => 2,
            self::TYPE_DOWNLOAD => 10,
            self::TYPE_SHARE => 8,
        ];

        $basePoints = $points[$eventType] ?? 0;

        // Bonus points for specific actions
        if ($eventType === self::TYPE_PAGE_VIEW) {
            $pageType = $data['page_type'] ?? 'other';
            $pageBonus = [
                'pricing' => 10,
                'contact' => 8,
                'services' => 5,
                'portfolio' => 4,
            ];
            $basePoints += $pageBonus[$pageType] ?? 0;
        }

        return $basePoints;
    }

    public static function isConversionEvent(string $eventType): bool
    {
        return in_array($eventType, [
            self::TYPE_FORM_SUBMIT,
            self::TYPE_CTA_CLICK,
        ]);
    }

    public static function isEngagementEvent(string $eventType): bool
    {
        return in_array($eventType, [
            self::TYPE_CLICK,
            self::TYPE_SCROLL,
            self::TYPE_VIDEO_PLAY,
            self::TYPE_VIDEO_COMPLETE,
            self::TYPE_FORM_START,
            self::TYPE_FORM_FIELD,
            self::TYPE_DOWNLOAD,
            self::TYPE_SHARE,
        ]);
    }
}
