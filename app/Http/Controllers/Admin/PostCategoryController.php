<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PostCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PostCategoryController extends Controller
{
    /**
     * Display a listing of categories.
     */
    public function index(): Response
    {
        $categories = PostCategory::withCount('posts')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/PostCategories/Index', [
            'categories' => $categories,
        ]);
    }

    /**
     * Show the form for creating a new category.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/PostCategories/Create');
    }

    /**
     * Store a newly created category.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'name_es' => ['nullable', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:post_categories,slug'],
            'description' => ['nullable', 'string', 'max:1000'],
            'description_es' => ['nullable', 'string', 'max:1000'],
            'icon' => ['nullable', 'string', 'max:100'],
            'color' => ['nullable', 'string', 'max:20'],
            'is_active' => ['boolean'],
        ]);

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);

            // Ensure unique slug
            $originalSlug = $validated['slug'];
            $counter = 1;
            while (PostCategory::where('slug', $validated['slug'])->exists()) {
                $validated['slug'] = $originalSlug . '-' . $counter++;
            }
        }

        // Set sort order
        $validated['sort_order'] = PostCategory::max('sort_order') + 1;

        PostCategory::create($validated);

        return redirect()->route('admin.post-categories.index')
            ->with('success', 'Category created successfully.');
    }

    /**
     * Show the form for editing the specified category.
     */
    public function edit(PostCategory $postCategory): Response
    {
        return Inertia::render('Admin/PostCategories/Edit', [
            'category' => $postCategory,
        ]);
    }

    /**
     * Update the specified category.
     */
    public function update(Request $request, PostCategory $postCategory): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'name_es' => ['nullable', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:post_categories,slug,' . $postCategory->id],
            'description' => ['nullable', 'string', 'max:1000'],
            'description_es' => ['nullable', 'string', 'max:1000'],
            'icon' => ['nullable', 'string', 'max:100'],
            'color' => ['nullable', 'string', 'max:20'],
            'is_active' => ['boolean'],
        ]);

        $postCategory->update($validated);

        return redirect()->route('admin.post-categories.index')
            ->with('success', 'Category updated successfully.');
    }

    /**
     * Remove the specified category.
     */
    public function destroy(PostCategory $postCategory): RedirectResponse
    {
        // Check if category has posts
        if ($postCategory->posts()->count() > 0) {
            return redirect()->route('admin.post-categories.index')
                ->with('error', 'Cannot delete category with associated posts.');
        }

        $postCategory->delete();

        return redirect()->route('admin.post-categories.index')
            ->with('success', 'Category deleted successfully.');
    }

    /**
     * Toggle active status.
     */
    public function toggleActive(PostCategory $postCategory): JsonResponse
    {
        $postCategory->is_active = !$postCategory->is_active;
        $postCategory->save();

        return response()->json([
            'success' => true,
            'is_active' => $postCategory->is_active,
        ]);
    }

    /**
     * Update category order.
     */
    public function updateOrder(Request $request): JsonResponse
    {
        $request->validate([
            'categories' => ['required', 'array'],
            'categories.*.id' => ['required', 'exists:post_categories,id'],
            'categories.*.sort_order' => ['required', 'integer'],
        ]);

        foreach ($request->categories as $item) {
            PostCategory::where('id', $item['id'])
                ->update(['sort_order' => $item['sort_order']]);
        }

        return response()->json(['success' => true]);
    }
}
