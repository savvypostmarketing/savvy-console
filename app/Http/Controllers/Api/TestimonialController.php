<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TestimonialController extends Controller
{
    /**
     * Get published testimonials.
     */
    public function index(Request $request): JsonResponse
    {
        $locale = $request->get('locale', 'en');
        $service = $request->get('service');
        $featured = $request->boolean('featured', false);
        $limit = $request->integer('limit', 10);

        $query = Testimonial::published()->ordered();

        if ($featured) {
            $query->featured();
        }

        if ($service) {
            $query->forService($service);
        }

        $testimonials = $query->limit($limit)->get();

        $data = $testimonials->map(function ($testimonial) use ($locale) {
            return [
                'id' => $testimonial->id,
                'name' => $testimonial->name,
                'role' => $locale === 'es' && $testimonial->role_es
                    ? $testimonial->role_es
                    : $testimonial->role,
                'company' => $locale === 'es' && $testimonial->company_es
                    ? $testimonial->company_es
                    : $testimonial->company,
                'avatar' => $testimonial->avatar,
                'quote' => $locale === 'es' && $testimonial->quote_es
                    ? $testimonial->quote_es
                    : $testimonial->quote,
                'rating' => $testimonial->rating,
                'project_title' => $locale === 'es' && $testimonial->project_title_es
                    ? $testimonial->project_title_es
                    : $testimonial->project_title,
                'project_screenshot' => $testimonial->project_screenshot,
                'source' => $testimonial->source,
                'services' => $testimonial->services,
                'is_featured' => $testimonial->is_featured,
                'date_label' => $testimonial->date_label,
                'extra_info' => $testimonial->extra_info,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'total' => $testimonials->count(),
        ]);
    }

    /**
     * Get featured testimonials for homepage.
     */
    public function featured(Request $request): JsonResponse
    {
        $locale = $request->get('locale', 'en');
        $limit = $request->integer('limit', 5);

        $testimonials = Testimonial::published()
            ->featured()
            ->ordered()
            ->limit($limit)
            ->get();

        $data = $testimonials->map(function ($testimonial) use ($locale) {
            return [
                'id' => $testimonial->id,
                'name' => $testimonial->name,
                'role' => $locale === 'es' && $testimonial->role_es
                    ? $testimonial->role_es
                    : $testimonial->role,
                'company' => $locale === 'es' && $testimonial->company_es
                    ? $testimonial->company_es
                    : $testimonial->company,
                'avatar' => $testimonial->avatar,
                'quote' => $locale === 'es' && $testimonial->quote_es
                    ? $testimonial->quote_es
                    : $testimonial->quote,
                'rating' => $testimonial->rating,
                'source' => $testimonial->source,
                'date_label' => $testimonial->date_label,
                'extra_info' => $testimonial->extra_info,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Get testimonials by service.
     */
    public function byService(Request $request, string $service): JsonResponse
    {
        $locale = $request->get('locale', 'en');
        $limit = $request->integer('limit', 5);

        $testimonials = Testimonial::published()
            ->forService($service)
            ->ordered()
            ->limit($limit)
            ->get();

        $data = $testimonials->map(function ($testimonial) use ($locale) {
            return [
                'id' => $testimonial->id,
                'name' => $testimonial->name,
                'role' => $locale === 'es' && $testimonial->role_es
                    ? $testimonial->role_es
                    : $testimonial->role,
                'company' => $locale === 'es' && $testimonial->company_es
                    ? $testimonial->company_es
                    : $testimonial->company,
                'avatar' => $testimonial->avatar,
                'quote' => $locale === 'es' && $testimonial->quote_es
                    ? $testimonial->quote_es
                    : $testimonial->quote,
                'rating' => $testimonial->rating,
                'project_title' => $locale === 'es' && $testimonial->project_title_es
                    ? $testimonial->project_title_es
                    : $testimonial->project_title,
                'project_screenshot' => $testimonial->project_screenshot,
                'source' => $testimonial->source,
                'date_label' => $testimonial->date_label,
                'extra_info' => $testimonial->extra_info,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'service' => $service,
        ]);
    }
}
