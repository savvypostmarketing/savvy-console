<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class TestimonialController extends Controller
{
    /**
     * Display a listing of testimonials.
     */
    public function index(Request $request): Response
    {
        $query = Testimonial::query()->latest();

        // Filter by source
        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'published') {
                $query->where('is_published', true);
            } elseif ($request->status === 'draft') {
                $query->where('is_published', false);
            }
        }

        // Filter by featured
        if ($request->boolean('featured')) {
            $query->where('is_featured', true);
        }

        // Search by name or quote
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('quote', 'ilike', "%{$search}%")
                    ->orWhere('company', 'ilike', "%{$search}%");
            });
        }

        $testimonials = $query->paginate(20)->through(fn ($testimonial) => [
            'id' => $testimonial->id,
            'uuid' => $testimonial->uuid,
            'name' => $testimonial->name,
            'role' => $testimonial->role,
            'company' => $testimonial->company,
            'avatar' => $testimonial->avatar,
            'quote' => \Illuminate\Support\Str::limit($testimonial->quote, 100),
            'rating' => $testimonial->rating,
            'source' => $testimonial->source,
            'services' => $testimonial->services,
            'is_featured' => $testimonial->is_featured,
            'is_published' => $testimonial->is_published,
            'created_at' => $testimonial->created_at->format('Y-m-d H:i:s'),
        ]);

        $stats = [
            'total' => Testimonial::count(),
            'published' => Testimonial::where('is_published', true)->count(),
            'draft' => Testimonial::where('is_published', false)->count(),
            'featured' => Testimonial::where('is_featured', true)->count(),
        ];

        return Inertia::render('Admin/Testimonials/Index', [
            'testimonials' => $testimonials,
            'stats' => $stats,
            'filters' => $request->only(['source', 'status', 'featured', 'search']),
            'sources' => ['website', 'google', 'portfolio'],
        ]);
    }

    /**
     * Show the form for creating a new testimonial.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Testimonials/Create', [
            'sources' => ['website', 'google', 'portfolio'],
            'serviceOptions' => [
                'website' => 'Website Development',
                'branding' => 'Branding',
                'digital-marketing' => 'Digital Marketing',
                'app-development' => 'App Development',
                'seo' => 'SEO',
                'e-commerce' => 'E-Commerce',
            ],
        ]);
    }

    /**
     * Store a newly created testimonial.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'nullable|string|max:255',
            'role_es' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'company_es' => 'nullable|string|max:255',
            'avatar' => 'nullable|image|max:2048',
            'quote' => 'required|string',
            'quote_es' => 'nullable|string',
            'rating' => 'required|integer|min:1|max:5',
            'project_title' => 'nullable|string|max:255',
            'project_title_es' => 'nullable|string|max:255',
            'project_screenshot' => 'nullable|image|max:5120',
            'source' => 'required|string|in:website,google,portfolio',
            'services' => 'nullable|array',
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
            'sort_order' => 'nullable|integer',
            'date_label' => 'nullable|string|max:100',
            'extra_info' => 'nullable|string|max:255',
        ]);

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            $validated['avatar'] = $request->file('avatar')->store('testimonials/avatars', 'public');
        }

        // Handle screenshot upload
        if ($request->hasFile('project_screenshot')) {
            $validated['project_screenshot'] = $request->file('project_screenshot')->store('testimonials/screenshots', 'public');
        }

        Testimonial::create($validated);

        return redirect()->route('admin.testimonials.index')
            ->with('success', 'Testimonial created successfully.');
    }

    /**
     * Show the form for editing the specified testimonial.
     */
    public function edit(Testimonial $testimonial): Response
    {
        return Inertia::render('Admin/Testimonials/Edit', [
            'testimonial' => [
                'id' => $testimonial->id,
                'uuid' => $testimonial->uuid,
                'name' => $testimonial->name,
                'role' => $testimonial->role,
                'role_es' => $testimonial->role_es,
                'company' => $testimonial->company,
                'company_es' => $testimonial->company_es,
                'avatar' => $testimonial->avatar,
                'quote' => $testimonial->quote,
                'quote_es' => $testimonial->quote_es,
                'rating' => $testimonial->rating,
                'project_title' => $testimonial->project_title,
                'project_title_es' => $testimonial->project_title_es,
                'project_screenshot' => $testimonial->project_screenshot,
                'source' => $testimonial->source,
                'services' => $testimonial->services,
                'is_featured' => $testimonial->is_featured,
                'is_published' => $testimonial->is_published,
                'sort_order' => $testimonial->sort_order,
                'date_label' => $testimonial->date_label,
                'extra_info' => $testimonial->extra_info,
            ],
            'sources' => ['website', 'google', 'portfolio'],
            'serviceOptions' => [
                'website' => 'Website Development',
                'branding' => 'Branding',
                'digital-marketing' => 'Digital Marketing',
                'app-development' => 'App Development',
                'seo' => 'SEO',
                'e-commerce' => 'E-Commerce',
            ],
        ]);
    }

    /**
     * Update the specified testimonial.
     */
    public function update(Request $request, Testimonial $testimonial)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'nullable|string|max:255',
            'role_es' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'company_es' => 'nullable|string|max:255',
            'avatar' => 'nullable|image|max:2048',
            'quote' => 'required|string',
            'quote_es' => 'nullable|string',
            'rating' => 'required|integer|min:1|max:5',
            'project_title' => 'nullable|string|max:255',
            'project_title_es' => 'nullable|string|max:255',
            'project_screenshot' => 'nullable|image|max:5120',
            'source' => 'required|string|in:website,google,portfolio',
            'services' => 'nullable|array',
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
            'sort_order' => 'nullable|integer',
            'date_label' => 'nullable|string|max:100',
            'extra_info' => 'nullable|string|max:255',
        ]);

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            // Delete old avatar
            if ($testimonial->avatar) {
                Storage::disk('public')->delete($testimonial->avatar);
            }
            $validated['avatar'] = $request->file('avatar')->store('testimonials/avatars', 'public');
        }

        // Handle screenshot upload
        if ($request->hasFile('project_screenshot')) {
            if ($testimonial->project_screenshot) {
                Storage::disk('public')->delete($testimonial->project_screenshot);
            }
            $validated['project_screenshot'] = $request->file('project_screenshot')->store('testimonials/screenshots', 'public');
        }

        $testimonial->update($validated);

        return redirect()->route('admin.testimonials.index')
            ->with('success', 'Testimonial updated successfully.');
    }

    /**
     * Remove the specified testimonial.
     */
    public function destroy(Testimonial $testimonial)
    {
        // Delete associated files
        if ($testimonial->avatar) {
            Storage::disk('public')->delete($testimonial->avatar);
        }
        if ($testimonial->project_screenshot) {
            Storage::disk('public')->delete($testimonial->project_screenshot);
        }

        $testimonial->delete();

        return redirect()->route('admin.testimonials.index')
            ->with('success', 'Testimonial deleted successfully.');
    }

    /**
     * Toggle published status.
     */
    public function togglePublished(Testimonial $testimonial)
    {
        $testimonial->update(['is_published' => !$testimonial->is_published]);

        return back()->with('success', $testimonial->is_published
            ? 'Testimonial published successfully.'
            : 'Testimonial unpublished successfully.');
    }

    /**
     * Toggle featured status.
     */
    public function toggleFeatured(Testimonial $testimonial)
    {
        $testimonial->update(['is_featured' => !$testimonial->is_featured]);

        return back()->with('success', $testimonial->is_featured
            ? 'Testimonial marked as featured.'
            : 'Testimonial unmarked as featured.');
    }
}
