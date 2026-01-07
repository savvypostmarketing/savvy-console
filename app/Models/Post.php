<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Post extends Model
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
        'category_id',
        'author_id',
        'excerpt',
        'excerpt_es',
        'content',
        'content_es',
        'featured_image',
        'featured_image_alt',
        'featured_image_alt_es',
        'reading_time_minutes',
        'views_count',
        'likes_count',
        'is_featured',
        'is_published',
        'published_at',
        'sort_order',
        'meta_title',
        'meta_title_es',
        'meta_description',
        'meta_description_es',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_published' => 'boolean',
        'published_at' => 'datetime',
        'reading_time_minutes' => 'integer',
        'views_count' => 'integer',
        'likes_count' => 'integer',
        'sort_order' => 'integer',
    ];

    /**
     * Get the category for this post.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(PostCategory::class, 'category_id');
    }

    /**
     * Get the author for this post.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Get the tags for this post.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(PostTag::class, 'post_post_tag')
            ->withTimestamps();
    }

    /**
     * Get the likes for this post.
     */
    public function likes(): HasMany
    {
        return $this->hasMany(PostLike::class);
    }

    /**
     * Scope for published posts.
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    /**
     * Scope for featured posts.
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
        return $query->orderBy('sort_order')->orderBy('published_at', 'desc')->orderBy('created_at', 'desc');
    }

    /**
     * Scope for popular posts (by views and likes).
     */
    public function scopePopular($query)
    {
        return $query->orderByDesc('views_count')->orderByDesc('likes_count');
    }

    /**
     * Increment view count.
     */
    public function incrementViews(): void
    {
        $this->increment('views_count');
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
     * Get localized excerpt.
     */
    public function getLocalizedExcerptAttribute(): ?string
    {
        $locale = app()->getLocale();
        return $locale === 'es' && $this->excerpt_es ? $this->excerpt_es : $this->excerpt;
    }

    /**
     * Get localized content.
     */
    public function getLocalizedContentAttribute(): ?string
    {
        $locale = app()->getLocale();
        return $locale === 'es' && $this->content_es ? $this->content_es : $this->content;
    }

    /**
     * Get localized featured image alt.
     */
    public function getLocalizedFeaturedImageAltAttribute(): ?string
    {
        $locale = app()->getLocale();
        return $locale === 'es' && $this->featured_image_alt_es ? $this->featured_image_alt_es : $this->featured_image_alt;
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
