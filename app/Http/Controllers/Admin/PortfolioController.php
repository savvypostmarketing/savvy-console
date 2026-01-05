<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Portfolio;
use App\Models\PortfolioIndustry;
use App\Models\PortfolioService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PortfolioController extends Controller
{
    /**
     * Display a listing of portfolios
     */
    public function index(Request $request): Response
    {
        $query = Portfolio::with(['industry', 'services'])->latest();

        // Filter by industry
        if ($request->filled('industry')) {
            $query->where('industry_id', $request->industry);
        }

        // Filter by service
        if ($request->filled('service')) {
            $query->whereHas('services', function ($q) use ($request) {
                $q->where('portfolio_services.id', $request->service);
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'published') {
                $query->where('is_published', true);
            } elseif ($request->status === 'draft') {
                $query->where('is_published', false);
            }
        }

        // Search by title
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'ilike', "%{$search}%")
                    ->orWhere('title_es', 'ilike', "%{$search}%");
            });
        }

        $portfolios = $query->paginate(20)->through(fn ($portfolio) => [
            'id' => $portfolio->id,
            'title' => $portfolio->title,
            'title_es' => $portfolio->title_es,
            'slug' => $portfolio->slug,
            'featured_image' => $portfolio->featured_image,
            'industry' => $portfolio->industry ? [
                'id' => $portfolio->industry->id,
                'name' => $portfolio->industry->name,
                'name_es' => $portfolio->industry->name_es,
            ] : null,
            'services' => $portfolio->services->map(fn ($service) => [
                'id' => $service->id,
                'name' => $service->name,
                'name_es' => $service->name_es,
                'color' => $service->color,
            ]),
            'is_published' => $portfolio->is_published,
            'is_featured' => $portfolio->is_featured,
            'created_at' => $portfolio->created_at->format('Y-m-d H:i:s'),
        ]);

        $stats = [
            'total' => Portfolio::count(),
            'published' => Portfolio::where('is_published', true)->count(),
            'draft' => Portfolio::where('is_published', false)->count(),
            'featured' => Portfolio::where('is_featured', true)->count(),
        ];

        return Inertia::render('Admin/Portfolio/Index', [
            'portfolios' => $portfolios,
            'stats' => $stats,
            'filters' => $request->only(['industry', 'service', 'status', 'search']),
            'industries' => PortfolioIndustry::active()->ordered()->get(['id', 'name', 'name_es']),
            'services' => PortfolioService::active()->ordered()->get(['id', 'name', 'name_es']),
        ]);
    }

    /**
     * Show the form for creating a new portfolio
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Portfolio/Create', [
            'industries' => PortfolioIndustry::active()->ordered()->get(['id', 'name', 'name_es', 'slug', 'icon']),
            'services' => PortfolioService::active()->ordered()->get(['id', 'name', 'name_es', 'slug', 'color', 'icon']),
        ]);
    }

    /**
     * Store a newly created portfolio
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'title_es' => ['nullable', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:portfolios,slug'],
            'industry_id' => ['required', 'exists:portfolio_industries,id'],
            'services' => ['required', 'array', 'min:1'],
            'services.*' => ['exists:portfolio_services,id'],
            'description' => ['nullable', 'string'],
            'description_es' => ['nullable', 'string'],
            'challenge' => ['nullable', 'string'],
            'challenge_es' => ['nullable', 'string'],
            'solution' => ['nullable', 'string'],
            'solution_es' => ['nullable', 'string'],
            'featured_image' => ['nullable', 'image', 'max:5120'],
            'website_url' => ['nullable', 'url', 'max:255'],
            'testimonial_quote' => ['nullable', 'string'],
            'testimonial_quote_es' => ['nullable', 'string'],
            'testimonial_author' => ['nullable', 'string', 'max:255'],
            'testimonial_role' => ['nullable', 'string', 'max:255'],
            'testimonial_role_es' => ['nullable', 'string', 'max:255'],
            'testimonial_avatar' => ['nullable', 'image', 'max:2048'],
            'video_url' => ['nullable', 'url', 'max:255'],
            'video_thumbnail' => ['nullable', 'image', 'max:5120'],
            'is_published' => ['boolean'],
            'is_featured' => ['boolean'],
            'sort_order' => ['nullable', 'integer'],
            // Related data
            'stats' => ['nullable', 'array'],
            'stats.*.label' => ['required_with:stats', 'string', 'max:100'],
            'stats.*.label_es' => ['nullable', 'string', 'max:100'],
            'stats.*.value' => ['required_with:stats', 'string', 'max:50'],
            'gallery' => ['nullable', 'array'],
            'gallery.*' => ['image', 'max:5120'],
            'features' => ['nullable', 'array'],
            'features.*.number' => ['required_with:features', 'string', 'max:10'],
            'features.*.title' => ['required_with:features', 'string', 'max:255'],
            'features.*.title_es' => ['nullable', 'string', 'max:255'],
            'features.*.description' => ['nullable', 'string'],
            'features.*.description_es' => ['nullable', 'string'],
            'features.*.icon' => ['nullable', 'string', 'max:100'],
            'results' => ['nullable', 'array'],
            'results.*.result' => ['required_with:results', 'string'],
            'results.*.result_es' => ['nullable', 'string'],
            'video_features' => ['nullable', 'array'],
            'video_features.*.title' => ['required_with:video_features', 'string', 'max:255'],
            'video_features.*.title_es' => ['nullable', 'string', 'max:255'],
            'video_features.*.description' => ['nullable', 'string'],
            'video_features.*.description_es' => ['nullable', 'string'],
        ]);

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
            // Ensure unique slug
            $count = Portfolio::where('slug', 'like', $validated['slug'] . '%')->count();
            if ($count > 0) {
                $validated['slug'] .= '-' . ($count + 1);
            }
        }

        DB::beginTransaction();

        try {
            // Handle file uploads
            if ($request->hasFile('featured_image')) {
                $validated['featured_image'] = $request->file('featured_image')
                    ->store('portfolio/featured', 'public');
            }

            if ($request->hasFile('testimonial_avatar')) {
                $validated['testimonial_avatar'] = $request->file('testimonial_avatar')
                    ->store('portfolio/testimonials', 'public');
            }

            if ($request->hasFile('video_thumbnail')) {
                $validated['video_thumbnail'] = $request->file('video_thumbnail')
                    ->store('portfolio/video-thumbnails', 'public');
            }

            // Create portfolio
            $services = $validated['services'];
            unset($validated['services'], $validated['stats'], $validated['gallery'], $validated['features'], $validated['results'], $validated['video_features']);

            $portfolio = Portfolio::create($validated);

            // Attach services
            $portfolio->services()->attach($services);

            // Create stats
            if ($request->has('stats')) {
                foreach ($request->stats as $index => $stat) {
                    $portfolio->stats()->create([
                        'label' => $stat['label'],
                        'label_es' => $stat['label_es'] ?? null,
                        'value' => $stat['value'],
                        'sort_order' => $index,
                    ]);
                }
            }

            // Handle gallery uploads
            if ($request->hasFile('gallery')) {
                foreach ($request->file('gallery') as $index => $image) {
                    $path = $image->store('portfolio/gallery', 'public');
                    $portfolio->gallery()->create([
                        'image_path' => $path,
                        'sort_order' => $index,
                    ]);
                }
            }

            // Create features
            if ($request->has('features')) {
                foreach ($request->features as $index => $feature) {
                    $portfolio->features()->create([
                        'number' => $feature['number'],
                        'title' => $feature['title'],
                        'title_es' => $feature['title_es'] ?? null,
                        'description' => $feature['description'] ?? null,
                        'description_es' => $feature['description_es'] ?? null,
                        'icon' => $feature['icon'] ?? null,
                        'sort_order' => $index,
                    ]);
                }
            }

            // Create results
            if ($request->has('results')) {
                foreach ($request->results as $index => $result) {
                    $portfolio->results()->create([
                        'result' => $result['result'],
                        'result_es' => $result['result_es'] ?? null,
                        'sort_order' => $index,
                    ]);
                }
            }

            // Create video features
            if ($request->has('video_features')) {
                foreach ($request->video_features as $index => $videoFeature) {
                    $portfolio->videoFeatures()->create([
                        'title' => $videoFeature['title'],
                        'title_es' => $videoFeature['title_es'] ?? null,
                        'description' => $videoFeature['description'] ?? null,
                        'description_es' => $videoFeature['description_es'] ?? null,
                        'sort_order' => $index,
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('admin.portfolio.index')
                ->with('success', 'Portfolio created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Display the specified portfolio
     */
    public function show(Portfolio $portfolio): Response
    {
        $portfolio->load(['industry', 'services', 'stats', 'gallery', 'features', 'results', 'videoFeatures']);

        return Inertia::render('Admin/Portfolio/Show', [
            'portfolio' => $this->formatPortfolio($portfolio),
        ]);
    }

    /**
     * Show the form for editing the specified portfolio
     */
    public function edit(Portfolio $portfolio): Response
    {
        $portfolio->load(['industry', 'services', 'stats', 'gallery', 'features', 'results', 'videoFeatures']);

        return Inertia::render('Admin/Portfolio/Edit', [
            'portfolio' => $this->formatPortfolio($portfolio),
            'industries' => PortfolioIndustry::active()->ordered()->get(['id', 'name', 'name_es', 'slug', 'icon']),
            'services' => PortfolioService::active()->ordered()->get(['id', 'name', 'name_es', 'slug', 'color', 'icon']),
        ]);
    }

    /**
     * Update the specified portfolio
     */
    public function update(Request $request, Portfolio $portfolio)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'title_es' => ['nullable', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:portfolios,slug,' . $portfolio->id],
            'industry_id' => ['required', 'exists:portfolio_industries,id'],
            'services' => ['required', 'array', 'min:1'],
            'services.*' => ['exists:portfolio_services,id'],
            'description' => ['nullable', 'string'],
            'description_es' => ['nullable', 'string'],
            'challenge' => ['nullable', 'string'],
            'challenge_es' => ['nullable', 'string'],
            'solution' => ['nullable', 'string'],
            'solution_es' => ['nullable', 'string'],
            'featured_image' => ['nullable', 'image', 'max:5120'],
            'website_url' => ['nullable', 'url', 'max:255'],
            'testimonial_quote' => ['nullable', 'string'],
            'testimonial_quote_es' => ['nullable', 'string'],
            'testimonial_author' => ['nullable', 'string', 'max:255'],
            'testimonial_role' => ['nullable', 'string', 'max:255'],
            'testimonial_role_es' => ['nullable', 'string', 'max:255'],
            'testimonial_avatar' => ['nullable', 'image', 'max:2048'],
            'video_url' => ['nullable', 'url', 'max:255'],
            'video_thumbnail' => ['nullable', 'image', 'max:5120'],
            'is_published' => ['boolean'],
            'is_featured' => ['boolean'],
            'sort_order' => ['nullable', 'integer'],
            // Related data
            'stats' => ['nullable', 'array'],
            'stats.*.id' => ['nullable', 'integer'],
            'stats.*.label' => ['required_with:stats', 'string', 'max:100'],
            'stats.*.label_es' => ['nullable', 'string', 'max:100'],
            'stats.*.value' => ['required_with:stats', 'string', 'max:50'],
            'features' => ['nullable', 'array'],
            'features.*.id' => ['nullable', 'integer'],
            'features.*.number' => ['required_with:features', 'string', 'max:10'],
            'features.*.title' => ['required_with:features', 'string', 'max:255'],
            'features.*.title_es' => ['nullable', 'string', 'max:255'],
            'features.*.description' => ['nullable', 'string'],
            'features.*.description_es' => ['nullable', 'string'],
            'features.*.icon' => ['nullable', 'string', 'max:100'],
            'results' => ['nullable', 'array'],
            'results.*.id' => ['nullable', 'integer'],
            'results.*.result' => ['required_with:results', 'string'],
            'results.*.result_es' => ['nullable', 'string'],
            'video_features' => ['nullable', 'array'],
            'video_features.*.id' => ['nullable', 'integer'],
            'video_features.*.title' => ['required_with:video_features', 'string', 'max:255'],
            'video_features.*.title_es' => ['nullable', 'string', 'max:255'],
            'video_features.*.description' => ['nullable', 'string'],
            'video_features.*.description_es' => ['nullable', 'string'],
        ]);

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
            // Ensure unique slug
            $existing = Portfolio::where('slug', $validated['slug'])
                ->where('id', '!=', $portfolio->id)
                ->exists();
            if ($existing) {
                $validated['slug'] .= '-' . $portfolio->id;
            }
        }

        DB::beginTransaction();

        try {
            // Handle file uploads
            if ($request->hasFile('featured_image')) {
                // Delete old image
                if ($portfolio->featured_image) {
                    Storage::disk('public')->delete($portfolio->featured_image);
                }
                $validated['featured_image'] = $request->file('featured_image')
                    ->store('portfolio/featured', 'public');
            }

            if ($request->hasFile('testimonial_avatar')) {
                if ($portfolio->testimonial_avatar) {
                    Storage::disk('public')->delete($portfolio->testimonial_avatar);
                }
                $validated['testimonial_avatar'] = $request->file('testimonial_avatar')
                    ->store('portfolio/testimonials', 'public');
            }

            if ($request->hasFile('video_thumbnail')) {
                if ($portfolio->video_thumbnail) {
                    Storage::disk('public')->delete($portfolio->video_thumbnail);
                }
                $validated['video_thumbnail'] = $request->file('video_thumbnail')
                    ->store('portfolio/video-thumbnails', 'public');
            }

            // Update portfolio
            $services = $validated['services'];
            unset($validated['services'], $validated['stats'], $validated['features'], $validated['results'], $validated['video_features']);

            $portfolio->update($validated);

            // Sync services
            $portfolio->services()->sync($services);

            // Update stats
            $this->syncRelated($portfolio, 'stats', $request->stats ?? [], ['label', 'label_es', 'value']);

            // Update features
            $this->syncRelated($portfolio, 'features', $request->features ?? [], ['number', 'title', 'title_es', 'description', 'description_es', 'icon']);

            // Update results
            $this->syncRelated($portfolio, 'results', $request->results ?? [], ['result', 'result_es']);

            // Update video features
            $this->syncRelated($portfolio, 'videoFeatures', $request->video_features ?? [], ['title', 'title_es', 'description', 'description_es']);

            DB::commit();

            return redirect()->route('admin.portfolio.index')
                ->with('success', 'Portfolio updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Remove the specified portfolio
     */
    public function destroy(Portfolio $portfolio)
    {
        // Delete associated files
        if ($portfolio->featured_image) {
            Storage::disk('public')->delete($portfolio->featured_image);
        }
        if ($portfolio->testimonial_avatar) {
            Storage::disk('public')->delete($portfolio->testimonial_avatar);
        }
        if ($portfolio->video_thumbnail) {
            Storage::disk('public')->delete($portfolio->video_thumbnail);
        }

        // Delete gallery images
        foreach ($portfolio->gallery as $image) {
            Storage::disk('public')->delete($image->image_path);
        }

        $portfolio->delete();

        return redirect()->route('admin.portfolio.index')
            ->with('success', 'Portfolio deleted successfully.');
    }

    /**
     * Toggle published status
     */
    public function togglePublished(Portfolio $portfolio)
    {
        $portfolio->update(['is_published' => !$portfolio->is_published]);

        return back()->with('success', $portfolio->is_published
            ? 'Portfolio published successfully.'
            : 'Portfolio unpublished successfully.');
    }

    /**
     * Toggle featured status
     */
    public function toggleFeatured(Portfolio $portfolio)
    {
        $portfolio->update(['is_featured' => !$portfolio->is_featured]);

        return back()->with('success', $portfolio->is_featured
            ? 'Portfolio marked as featured.'
            : 'Portfolio unmarked as featured.');
    }

    /**
     * Upload gallery images
     */
    public function uploadGallery(Request $request, Portfolio $portfolio)
    {
        $request->validate([
            'images' => ['required', 'array'],
            'images.*' => ['image', 'max:5120'],
        ]);

        $uploaded = [];
        $currentCount = $portfolio->gallery()->count();

        foreach ($request->file('images') as $index => $image) {
            $path = $image->store('portfolio/gallery', 'public');
            $uploaded[] = $portfolio->gallery()->create([
                'image_path' => $path,
                'sort_order' => $currentCount + $index,
            ]);
        }

        return back()->with('success', count($uploaded) . ' image(s) uploaded successfully.');
    }

    /**
     * Delete gallery image
     */
    public function deleteGalleryImage(Portfolio $portfolio, int $imageId)
    {
        $image = $portfolio->gallery()->findOrFail($imageId);
        Storage::disk('public')->delete($image->image_path);
        $image->delete();

        return back()->with('success', 'Image deleted successfully.');
    }

    /**
     * Reorder gallery images
     */
    public function reorderGallery(Request $request, Portfolio $portfolio)
    {
        $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['integer', 'exists:portfolio_gallery,id'],
        ]);

        foreach ($request->order as $index => $id) {
            $portfolio->gallery()->where('id', $id)->update(['sort_order' => $index]);
        }

        return back()->with('success', 'Gallery order updated.');
    }

    /**
     * Format portfolio for response
     */
    private function formatPortfolio(Portfolio $portfolio): array
    {
        return [
            'id' => $portfolio->id,
            'title' => $portfolio->title,
            'title_es' => $portfolio->title_es,
            'slug' => $portfolio->slug,
            'industry_id' => $portfolio->industry_id,
            'industry' => $portfolio->industry ? [
                'id' => $portfolio->industry->id,
                'name' => $portfolio->industry->name,
                'name_es' => $portfolio->industry->name_es,
            ] : null,
            'services' => $portfolio->services->map(fn ($service) => [
                'id' => $service->id,
                'name' => $service->name,
                'name_es' => $service->name_es,
                'color' => $service->color,
            ]),
            'service_ids' => $portfolio->services->pluck('id')->toArray(),
            'description' => $portfolio->description,
            'description_es' => $portfolio->description_es,
            'challenge' => $portfolio->challenge,
            'challenge_es' => $portfolio->challenge_es,
            'solution' => $portfolio->solution,
            'solution_es' => $portfolio->solution_es,
            'featured_image' => $portfolio->featured_image,
            'website_url' => $portfolio->website_url,
            'testimonial_quote' => $portfolio->testimonial_quote,
            'testimonial_quote_es' => $portfolio->testimonial_quote_es,
            'testimonial_author' => $portfolio->testimonial_author,
            'testimonial_role' => $portfolio->testimonial_role,
            'testimonial_role_es' => $portfolio->testimonial_role_es,
            'testimonial_avatar' => $portfolio->testimonial_avatar,
            'video_url' => $portfolio->video_url,
            'video_thumbnail' => $portfolio->video_thumbnail,
            'is_published' => $portfolio->is_published,
            'is_featured' => $portfolio->is_featured,
            'sort_order' => $portfolio->sort_order,
            'stats' => $portfolio->stats->map(fn ($stat) => [
                'id' => $stat->id,
                'label' => $stat->label,
                'label_es' => $stat->label_es,
                'value' => $stat->value,
            ]),
            'gallery' => $portfolio->gallery->map(fn ($image) => [
                'id' => $image->id,
                'image_path' => $image->image_path,
                'alt_text' => $image->alt_text,
                'alt_text_es' => $image->alt_text_es,
                'caption' => $image->caption,
                'caption_es' => $image->caption_es,
            ]),
            'features' => $portfolio->features->map(fn ($feature) => [
                'id' => $feature->id,
                'number' => $feature->number,
                'title' => $feature->title,
                'title_es' => $feature->title_es,
                'description' => $feature->description,
                'description_es' => $feature->description_es,
                'icon' => $feature->icon,
            ]),
            'results' => $portfolio->results->map(fn ($result) => [
                'id' => $result->id,
                'result' => $result->result,
                'result_es' => $result->result_es,
            ]),
            'video_features' => $portfolio->videoFeatures->map(fn ($vf) => [
                'id' => $vf->id,
                'title' => $vf->title,
                'title_es' => $vf->title_es,
                'description' => $vf->description,
                'description_es' => $vf->description_es,
            ]),
            'created_at' => $portfolio->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $portfolio->updated_at->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Sync related models
     */
    private function syncRelated(Portfolio $portfolio, string $relation, array $items, array $fields): void
    {
        $existingIds = collect($items)->pluck('id')->filter()->toArray();

        // Delete removed items
        $portfolio->$relation()->whereNotIn('id', $existingIds)->delete();

        // Update or create items
        foreach ($items as $index => $item) {
            $data = array_merge(
                array_intersect_key($item, array_flip($fields)),
                ['sort_order' => $index]
            );

            if (!empty($item['id'])) {
                $portfolio->$relation()->where('id', $item['id'])->update($data);
            } else {
                $portfolio->$relation()->create($data);
            }
        }
    }
}
