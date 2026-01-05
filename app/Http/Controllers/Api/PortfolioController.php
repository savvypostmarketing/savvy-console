<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Portfolio;
use App\Models\PortfolioService;
use App\Models\PortfolioIndustry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PortfolioController extends Controller
{
    /**
     * Get all published portfolios with optional filtering.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Portfolio::with(['industry', 'services', 'stats'])
            ->published()
            ->ordered();

        // Filter by service
        if ($request->has('service') && $request->service) {
            $query->whereHas('services', function ($q) use ($request) {
                $q->where('slug', $request->service);
            });
        }

        // Filter by industry
        if ($request->has('industry') && $request->industry) {
            $query->whereHas('industry', function ($q) use ($request) {
                $q->where('slug', $request->industry);
            });
        }

        // Filter featured only
        if ($request->boolean('featured')) {
            $query->featured();
        }

        $portfolios = $query->get();

        return response()->json([
            'success' => true,
            'data' => $portfolios->map(fn ($portfolio) => $this->transformPortfolioForList($portfolio)),
        ]);
    }

    /**
     * Get a single portfolio by slug with all related data.
     */
    public function show(string $slug): JsonResponse
    {
        $portfolio = Portfolio::with([
            'industry',
            'services',
            'stats',
            'gallery',
            'features',
            'results',
            'videoFeatures',
        ])
            ->published()
            ->where('slug', $slug)
            ->first();

        if (!$portfolio) {
            return response()->json([
                'success' => false,
                'message' => 'Portfolio not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $this->transformPortfolioForDetail($portfolio),
        ]);
    }

    /**
     * Get all services for filtering.
     */
    public function services(): JsonResponse
    {
        $services = PortfolioService::orderBy('sort_order')->get();

        return response()->json([
            'success' => true,
            'data' => $services->map(fn ($service) => [
                'slug' => $service->slug,
                'name' => $service->name,
                'name_es' => $service->name_es,
            ]),
        ]);
    }

    /**
     * Get all industries for filtering.
     */
    public function industries(): JsonResponse
    {
        $industries = PortfolioIndustry::orderBy('sort_order')->get();

        return response()->json([
            'success' => true,
            'data' => $industries->map(fn ($industry) => [
                'slug' => $industry->slug,
                'name' => $industry->name,
                'name_es' => $industry->name_es,
            ]),
        ]);
    }

    /**
     * Transform portfolio for list view (minimal data).
     */
    private function transformPortfolioForList(Portfolio $portfolio): array
    {
        return [
            'id' => $portfolio->uuid,
            'slug' => $portfolio->slug,
            'title' => $portfolio->title,
            'title_es' => $portfolio->title_es,
            'industry' => $portfolio->industry ? [
                'slug' => $portfolio->industry->slug,
                'name' => $portfolio->industry->name,
                'name_es' => $portfolio->industry->name_es,
            ] : null,
            'services' => $portfolio->services->map(fn ($service) => [
                'slug' => $service->slug,
                'name' => $service->name,
                'name_es' => $service->name_es,
            ])->toArray(),
            'description' => $portfolio->description,
            'description_es' => $portfolio->description_es,
            'featuredImage' => $portfolio->featured_image ? url('storage/' . $portfolio->featured_image) : null,
            'stats' => $portfolio->stats->map(fn ($stat) => [
                'label' => $stat->label,
                'label_es' => $stat->label_es,
                'value' => $stat->value,
            ])->toArray(),
            'is_featured' => $portfolio->is_featured,
        ];
    }

    /**
     * Transform portfolio for detail view (full data).
     */
    private function transformPortfolioForDetail(Portfolio $portfolio): array
    {
        return [
            'id' => $portfolio->uuid,
            'slug' => $portfolio->slug,
            'title' => $portfolio->title,
            'title_es' => $portfolio->title_es,
            'industry' => $portfolio->industry ? [
                'slug' => $portfolio->industry->slug,
                'name' => $portfolio->industry->name,
                'name_es' => $portfolio->industry->name_es,
            ] : null,
            'services' => $portfolio->services->map(fn ($service) => [
                'slug' => $service->slug,
                'name' => $service->name,
                'name_es' => $service->name_es,
            ])->toArray(),
            'description' => $portfolio->description,
            'description_es' => $portfolio->description_es,
            'challenge' => $portfolio->challenge,
            'challenge_es' => $portfolio->challenge_es,
            'solution' => $portfolio->solution,
            'solution_es' => $portfolio->solution_es,
            'featuredImage' => $portfolio->featured_image ? url('storage/' . $portfolio->featured_image) : null,
            'websiteUrl' => $portfolio->website_url,
            'gallery' => $portfolio->gallery->map(fn ($item) => [
                'image' => $item->image_path ? url('storage/' . $item->image_path) : null,
                'caption' => $item->caption,
                'caption_es' => $item->caption_es,
            ])->toArray(),
            'stats' => $portfolio->stats->map(fn ($stat) => [
                'label' => $stat->label,
                'label_es' => $stat->label_es,
                'value' => $stat->value,
            ])->toArray(),
            'features' => $portfolio->features->map(fn ($feature) => [
                'number' => $feature->number,
                'title' => $feature->title,
                'title_es' => $feature->title_es,
                'description' => $feature->description,
                'description_es' => $feature->description_es,
                'icon' => $feature->icon,
            ])->toArray(),
            'results' => $portfolio->results->map(fn ($result) => [
                'result' => $result->result,
                'result_es' => $result->result_es,
            ])->toArray(),
            'videoFeatures' => $portfolio->videoFeatures->map(fn ($vf) => [
                'title' => $vf->title,
                'title_es' => $vf->title_es,
            ])->toArray(),
            'videoUrl' => $portfolio->video_url,
            'videoThumbnail' => $portfolio->video_thumbnail ? url('storage/' . $portfolio->video_thumbnail) : null,
            'videoIntroText' => $portfolio->video_intro_text,
            'videoIntroText_es' => $portfolio->video_intro_text_es,
            'testimonial' => $portfolio->testimonial_quote ? [
                'quote' => $portfolio->testimonial_quote,
                'quote_es' => $portfolio->testimonial_quote_es,
                'author' => $portfolio->testimonial_author,
                'role' => $portfolio->testimonial_role,
                'role_es' => $portfolio->testimonial_role_es,
                'avatar' => $portfolio->testimonial_avatar ? url('storage/' . $portfolio->testimonial_avatar) : null,
            ] : null,
            'meta' => [
                'title' => $portfolio->meta_title,
                'title_es' => $portfolio->meta_title_es,
                'description' => $portfolio->meta_description,
                'description_es' => $portfolio->meta_description_es,
            ],
            'createdAt' => $portfolio->created_at?->toIso8601String(),
            'publishedAt' => $portfolio->published_at?->toIso8601String(),
        ];
    }
}
