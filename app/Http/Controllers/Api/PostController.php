<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\PostLike;
use App\Models\PostTag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * Get all published posts with optional filtering.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Post::with(['category', 'tags', 'author'])
            ->published()
            ->ordered();

        // Filter by category
        if ($request->has('category') && $request->category) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        // Filter by tag
        if ($request->has('tag') && $request->tag) {
            $query->whereHas('tags', function ($q) use ($request) {
                $q->where('slug', $request->tag);
            });
        }

        // Filter featured only
        if ($request->boolean('featured')) {
            $query->featured();
        }

        // Limit results
        if ($request->has('limit') && $request->limit > 0) {
            $query->limit((int) $request->limit);
        }

        $posts = $query->get();

        return response()->json([
            'success' => true,
            'data' => $posts->map(fn ($post) => $this->transformPostForList($post)),
        ]);
    }

    /**
     * Get popular posts (most viewed/liked).
     */
    public function popular(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 5);

        $posts = Post::with(['category', 'tags', 'author'])
            ->published()
            ->popular()
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $posts->map(fn ($post) => $this->transformPostForList($post)),
        ]);
    }

    /**
     * Get a single post by slug with all related data.
     */
    public function show(Request $request, string $slug): JsonResponse
    {
        $post = Post::with(['category', 'tags', 'author'])
            ->published()
            ->where('slug', $slug)
            ->first();

        if (!$post) {
            return response()->json([
                'success' => false,
                'message' => 'Post not found',
            ], 404);
        }

        // Increment view count
        $post->incrementViews();

        // Check if current user has liked this post
        $hasLiked = PostLike::where('post_id', $post->id)
            ->where('ip_address', $request->ip())
            ->exists();

        return response()->json([
            'success' => true,
            'data' => $this->transformPostForDetail($post, $hasLiked),
        ]);
    }

    /**
     * Get related posts (same category).
     */
    public function related(Request $request, string $slug): JsonResponse
    {
        $post = Post::published()->where('slug', $slug)->first();

        if (!$post) {
            return response()->json([
                'success' => false,
                'message' => 'Post not found',
            ], 404);
        }

        $limit = $request->input('limit', 3);

        $relatedPosts = Post::with(['category', 'tags', 'author'])
            ->published()
            ->where('id', '!=', $post->id)
            ->where('category_id', $post->category_id)
            ->ordered()
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $relatedPosts->map(fn ($p) => $this->transformPostForList($p)),
        ]);
    }

    /**
     * Toggle like on a post.
     */
    public function like(Request $request, string $slug): JsonResponse
    {
        $post = Post::published()->where('slug', $slug)->first();

        if (!$post) {
            return response()->json([
                'success' => false,
                'message' => 'Post not found',
            ], 404);
        }

        $ip = $request->ip();
        $existingLike = PostLike::where('post_id', $post->id)
            ->where('ip_address', $ip)
            ->first();

        if ($existingLike) {
            // Unlike
            $existingLike->delete();
            $post->decrement('likes_count');
            $liked = false;
        } else {
            // Like
            PostLike::create([
                'post_id' => $post->id,
                'ip_address' => $ip,
                'session_id' => $request->input('session_id'),
                'user_id' => auth()->id(),
            ]);
            $post->increment('likes_count');
            $liked = true;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'liked' => $liked,
                'likesCount' => $post->fresh()->likes_count,
            ],
        ]);
    }

    /**
     * Get all categories.
     */
    public function categories(): JsonResponse
    {
        $categories = PostCategory::active()->ordered()->get();

        return response()->json([
            'success' => true,
            'data' => $categories->map(fn ($category) => [
                'slug' => $category->slug,
                'name' => $category->name,
                'name_es' => $category->name_es,
                'description' => $category->description,
                'description_es' => $category->description_es,
                'icon' => $category->icon,
                'color' => $category->color,
            ]),
        ]);
    }

    /**
     * Get all tags.
     */
    public function tags(): JsonResponse
    {
        $tags = PostTag::active()->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $tags->map(fn ($tag) => [
                'slug' => $tag->slug,
                'name' => $tag->name,
                'name_es' => $tag->name_es,
            ]),
        ]);
    }

    /**
     * Transform post for list view (minimal data).
     */
    private function transformPostForList(Post $post): array
    {
        return [
            'id' => $post->uuid,
            'slug' => $post->slug,
            'title' => $post->title,
            'title_es' => $post->title_es,
            'excerpt' => $post->excerpt,
            'excerpt_es' => $post->excerpt_es,
            'featuredImage' => $post->featured_image ? url('storage/' . $post->featured_image) : null,
            'featuredImageAlt' => $post->featured_image_alt,
            'featuredImageAlt_es' => $post->featured_image_alt_es,
            'category' => $post->category ? [
                'slug' => $post->category->slug,
                'name' => $post->category->name,
                'name_es' => $post->category->name_es,
                'color' => $post->category->color,
            ] : null,
            'tags' => $post->tags->map(fn ($tag) => [
                'slug' => $tag->slug,
                'name' => $tag->name,
                'name_es' => $tag->name_es,
            ])->toArray(),
            'author' => $post->author ? [
                'name' => $post->author->name,
                'avatar' => $post->author->avatar ? url('storage/' . $post->author->avatar) : null,
            ] : null,
            'readingTime' => $post->reading_time_minutes,
            'viewsCount' => $post->views_count,
            'likesCount' => $post->likes_count,
            'isFeatured' => $post->is_featured,
            'publishedAt' => $post->published_at?->toIso8601String(),
        ];
    }

    /**
     * Transform post for detail view (full data).
     */
    private function transformPostForDetail(Post $post, bool $hasLiked = false): array
    {
        return [
            'id' => $post->uuid,
            'slug' => $post->slug,
            'title' => $post->title,
            'title_es' => $post->title_es,
            'excerpt' => $post->excerpt,
            'excerpt_es' => $post->excerpt_es,
            'content' => $post->content,
            'content_es' => $post->content_es,
            'featuredImage' => $post->featured_image ? url('storage/' . $post->featured_image) : null,
            'featuredImageAlt' => $post->featured_image_alt,
            'featuredImageAlt_es' => $post->featured_image_alt_es,
            'category' => $post->category ? [
                'slug' => $post->category->slug,
                'name' => $post->category->name,
                'name_es' => $post->category->name_es,
                'color' => $post->category->color,
            ] : null,
            'tags' => $post->tags->map(fn ($tag) => [
                'slug' => $tag->slug,
                'name' => $tag->name,
                'name_es' => $tag->name_es,
            ])->toArray(),
            'author' => $post->author ? [
                'name' => $post->author->name,
                'avatar' => $post->author->avatar ? url('storage/' . $post->author->avatar) : null,
            ] : null,
            'readingTime' => $post->reading_time_minutes,
            'viewsCount' => $post->views_count,
            'likesCount' => $post->likes_count,
            'hasLiked' => $hasLiked,
            'isFeatured' => $post->is_featured,
            'meta' => [
                'title' => $post->meta_title,
                'title_es' => $post->meta_title_es,
                'description' => $post->meta_description,
                'description_es' => $post->meta_description_es,
            ],
            'publishedAt' => $post->published_at?->toIso8601String(),
            'createdAt' => $post->created_at?->toIso8601String(),
        ];
    }
}
