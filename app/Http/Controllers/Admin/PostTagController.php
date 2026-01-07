<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PostTag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PostTagController extends Controller
{
    /**
     * Display a listing of tags.
     */
    public function index(): Response
    {
        $tags = PostTag::withCount('posts')
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/PostTags/Index', [
            'tags' => $tags,
        ]);
    }

    /**
     * Show the form for creating a new tag.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/PostTags/Create');
    }

    /**
     * Store a newly created tag.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'name_es' => ['nullable', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:post_tags,slug'],
            'is_active' => ['boolean'],
        ]);

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);

            // Ensure unique slug
            $originalSlug = $validated['slug'];
            $counter = 1;
            while (PostTag::where('slug', $validated['slug'])->exists()) {
                $validated['slug'] = $originalSlug . '-' . $counter++;
            }
        }

        PostTag::create($validated);

        return redirect()->route('admin.post-tags.index')
            ->with('success', 'Tag created successfully.');
    }

    /**
     * Show the form for editing the specified tag.
     */
    public function edit(PostTag $postTag): Response
    {
        return Inertia::render('Admin/PostTags/Edit', [
            'tag' => $postTag,
        ]);
    }

    /**
     * Update the specified tag.
     */
    public function update(Request $request, PostTag $postTag): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'name_es' => ['nullable', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:post_tags,slug,' . $postTag->id],
            'is_active' => ['boolean'],
        ]);

        $postTag->update($validated);

        return redirect()->route('admin.post-tags.index')
            ->with('success', 'Tag updated successfully.');
    }

    /**
     * Remove the specified tag.
     */
    public function destroy(PostTag $postTag): RedirectResponse
    {
        // Detach from all posts first
        $postTag->posts()->detach();
        $postTag->delete();

        return redirect()->route('admin.post-tags.index')
            ->with('success', 'Tag deleted successfully.');
    }

    /**
     * Toggle active status.
     */
    public function toggleActive(PostTag $postTag): JsonResponse
    {
        $postTag->is_active = !$postTag->is_active;
        $postTag->save();

        return response()->json([
            'success' => true,
            'is_active' => $postTag->is_active,
        ]);
    }
}
