<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\PostTag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PostController extends Controller
{
    /**
     * Display a listing of posts.
     */
    public function index(Request $request): Response
    {
        $query = Post::with(['category', 'author', 'tags'])
            ->orderBy('created_at', 'desc');

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'published') {
                $query->where('is_published', true);
            } elseif ($request->status === 'draft') {
                $query->where('is_published', false);
            } elseif ($request->status === 'featured') {
                $query->where('is_featured', true);
            }
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'ILIKE', "%{$search}%")
                    ->orWhere('title_es', 'ILIKE', "%{$search}%")
                    ->orWhere('excerpt', 'ILIKE', "%{$search}%");
            });
        }

        $posts = $query->paginate(15)->withQueryString();

        $categories = PostCategory::active()->ordered()->get();

        return Inertia::render('Admin/Posts/Index', [
            'posts' => $posts,
            'categories' => $categories,
            'filters' => $request->only(['category', 'status', 'search']),
        ]);
    }

    /**
     * Show the form for creating a new post.
     */
    public function create(): Response
    {
        $categories = PostCategory::active()->ordered()->get();
        $tags = PostTag::active()->orderBy('name')->get();

        return Inertia::render('Admin/Posts/Create', [
            'categories' => $categories,
            'tags' => $tags,
        ]);
    }

    /**
     * Store a newly created post.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'title_es' => ['nullable', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:posts,slug'],
            'category_id' => ['nullable', 'exists:post_categories,id'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'excerpt_es' => ['nullable', 'string', 'max:500'],
            'content' => ['nullable', 'array'],
            'content_es' => ['nullable', 'array'],
            'featured_image' => ['nullable', 'image', 'max:5120'],
            'featured_image_alt' => ['nullable', 'string', 'max:255'],
            'featured_image_alt_es' => ['nullable', 'string', 'max:255'],
            'reading_time_minutes' => ['nullable', 'integer', 'min:1'],
            'is_published' => ['boolean'],
            'is_featured' => ['boolean'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_title_es' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'meta_description_es' => ['nullable', 'string', 'max:500'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['exists:post_tags,id'],
        ]);

        DB::beginTransaction();

        try {
            // Generate slug if not provided
            if (empty($validated['slug'])) {
                $validated['slug'] = Str::slug($validated['title']);

                // Ensure unique slug
                $originalSlug = $validated['slug'];
                $counter = 1;
                while (Post::where('slug', $validated['slug'])->exists()) {
                    $validated['slug'] = $originalSlug . '-' . $counter++;
                }
            }

            // Handle featured image upload
            if ($request->hasFile('featured_image')) {
                $path = $request->file('featured_image')->store('posts/featured', 'public');
                $validated['featured_image'] = $path;
            }

            // Convert content arrays to JSON
            if (isset($validated['content'])) {
                $validated['content'] = json_encode($validated['content']);
            }
            if (isset($validated['content_es'])) {
                $validated['content_es'] = json_encode($validated['content_es']);
            }

            // Set author
            $validated['author_id'] = auth()->id();

            // Set published_at if publishing
            if ($validated['is_published'] ?? false) {
                $validated['published_at'] = now();
            }

            // Extract tags before creating post
            $tagIds = $validated['tags'] ?? [];
            unset($validated['tags']);

            $post = Post::create($validated);

            // Sync tags
            if (!empty($tagIds)) {
                $post->tags()->sync($tagIds);
            }

            DB::commit();

            return redirect()->route('admin.posts.index')
                ->with('success', 'Post created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Display the specified post.
     */
    public function show(Post $post): Response
    {
        $post->load(['category', 'author', 'tags']);

        return Inertia::render('Admin/Posts/Show', [
            'post' => $post,
        ]);
    }

    /**
     * Show the form for editing the specified post.
     */
    public function edit(Post $post): Response
    {
        $post->load(['category', 'tags']);

        $categories = PostCategory::active()->ordered()->get();
        $tags = PostTag::active()->orderBy('name')->get();

        // Parse content JSON
        $postData = $post->toArray();
        $postData['content'] = $post->content ? json_decode($post->content, true) : null;
        $postData['content_es'] = $post->content_es ? json_decode($post->content_es, true) : null;
        $postData['tag_ids'] = $post->tags->pluck('id')->toArray();
        $postData['featured_image_url'] = $post->featured_image
            ? url('storage/' . $post->featured_image)
            : null;

        return Inertia::render('Admin/Posts/Edit', [
            'post' => $postData,
            'categories' => $categories,
            'tags' => $tags,
        ]);
    }

    /**
     * Update the specified post.
     */
    public function update(Request $request, Post $post): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'title_es' => ['nullable', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:posts,slug,' . $post->id],
            'category_id' => ['nullable', 'exists:post_categories,id'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'excerpt_es' => ['nullable', 'string', 'max:500'],
            'content' => ['nullable', 'array'],
            'content_es' => ['nullable', 'array'],
            'featured_image' => ['nullable', 'image', 'max:5120'],
            'featured_image_alt' => ['nullable', 'string', 'max:255'],
            'featured_image_alt_es' => ['nullable', 'string', 'max:255'],
            'reading_time_minutes' => ['nullable', 'integer', 'min:1'],
            'is_published' => ['boolean'],
            'is_featured' => ['boolean'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_title_es' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'meta_description_es' => ['nullable', 'string', 'max:500'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['exists:post_tags,id'],
            'remove_featured_image' => ['nullable', 'boolean'],
        ]);

        DB::beginTransaction();

        try {
            // Handle featured image
            if ($request->hasFile('featured_image')) {
                // Delete old image
                if ($post->featured_image) {
                    Storage::disk('public')->delete($post->featured_image);
                }
                $validated['featured_image'] = $request->file('featured_image')
                    ->store('posts/featured', 'public');
            } elseif ($request->boolean('remove_featured_image')) {
                if ($post->featured_image) {
                    Storage::disk('public')->delete($post->featured_image);
                }
                $validated['featured_image'] = null;
            }

            // Convert content arrays to JSON
            if (isset($validated['content'])) {
                $validated['content'] = json_encode($validated['content']);
            }
            if (isset($validated['content_es'])) {
                $validated['content_es'] = json_encode($validated['content_es']);
            }

            // Set published_at if publishing for first time
            if (($validated['is_published'] ?? false) && !$post->published_at) {
                $validated['published_at'] = now();
            }

            // Extract tags before updating
            $tagIds = $validated['tags'] ?? [];
            unset($validated['tags']);
            unset($validated['remove_featured_image']);

            $post->update($validated);

            // Sync tags
            $post->tags()->sync($tagIds);

            DB::commit();

            return redirect()->route('admin.posts.index')
                ->with('success', 'Post updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Remove the specified post.
     */
    public function destroy(Post $post): RedirectResponse
    {
        DB::beginTransaction();

        try {
            // Delete featured image
            if ($post->featured_image) {
                Storage::disk('public')->delete($post->featured_image);
            }

            // Delete related media from content
            // Note: This is a simplified version. In production, you'd want to
            // parse the content JSON and delete all embedded images.

            $post->tags()->detach();
            $post->delete();

            DB::commit();

            return redirect()->route('admin.posts.index')
                ->with('success', 'Post deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Toggle published status.
     */
    public function togglePublished(Post $post): JsonResponse
    {
        $post->is_published = !$post->is_published;

        if ($post->is_published && !$post->published_at) {
            $post->published_at = now();
        }

        $post->save();

        return response()->json([
            'success' => true,
            'is_published' => $post->is_published,
        ]);
    }

    /**
     * Toggle featured status.
     */
    public function toggleFeatured(Post $post): JsonResponse
    {
        $post->is_featured = !$post->is_featured;
        $post->save();

        return response()->json([
            'success' => true,
            'is_featured' => $post->is_featured,
        ]);
    }

    /**
     * Upload image for Editor.js.
     */
    public function uploadImage(Request $request): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'image', 'max:10240'],
        ]);

        $file = $request->file('image');
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('posts/media', $filename, 'public');

        return response()->json([
            'success' => 1,
            'file' => [
                'url' => url('storage/' . $path),
            ],
        ]);
    }

    /**
     * Fetch link metadata for Editor.js.
     */
    public function fetchLink(Request $request): JsonResponse
    {
        $request->validate([
            'url' => ['required', 'url'],
        ]);

        $url = $request->input('url');

        try {
            $html = @file_get_contents($url);

            if (!$html) {
                throw new \Exception('Could not fetch URL');
            }

            preg_match('/<title>(.*?)<\/title>/i', $html, $titleMatches);
            $title = $titleMatches[1] ?? '';

            preg_match('/<meta[^>]*name=["\']description["\'][^>]*content=["\'](.*?)["\']/i', $html, $descMatches);
            $description = $descMatches[1] ?? '';

            preg_match('/<meta[^>]*property=["\']og:image["\'][^>]*content=["\'](.*?)["\']/i', $html, $imageMatches);
            $image = $imageMatches[1] ?? '';

            return response()->json([
                'success' => 1,
                'link' => $url,
                'meta' => [
                    'title' => html_entity_decode($title, ENT_QUOTES, 'UTF-8'),
                    'description' => html_entity_decode($description, ENT_QUOTES, 'UTF-8'),
                    'image' => ['url' => $image],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => 0,
                'message' => 'Failed to fetch link',
            ], 400);
        }
    }
}
