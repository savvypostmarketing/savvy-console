<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobPosition;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class JobPositionController extends Controller
{
    /**
     * Display a listing of job positions.
     */
    public function index(Request $request): Response
    {
        $query = JobPosition::query()->ordered();

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Filter by employment type
        if ($request->filled('employment_type')) {
            $query->where('employment_type', $request->employment_type);
        }

        // Filter by location type
        if ($request->filled('location_type')) {
            $query->where('location_type', $request->location_type);
        }

        // Search by title or department
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'ilike', "%{$search}%")
                    ->orWhere('title_es', 'ilike', "%{$search}%")
                    ->orWhere('department', 'ilike', "%{$search}%");
            });
        }

        $positions = $query->paginate(20)->through(fn ($position) => [
            'id' => $position->id,
            'title' => $position->title,
            'title_es' => $position->title_es,
            'department' => $position->department,
            'employment_type' => $position->employment_type,
            'employment_type_label' => $position->employment_type_label,
            'location_type' => $position->location_type,
            'location_type_label' => $position->location_type_label,
            'location' => $position->location,
            'linkedin_url' => $position->linkedin_url,
            'apply_url' => $position->apply_url,
            'is_active' => $position->is_active,
            'is_featured' => $position->is_featured,
            'sort_order' => $position->sort_order,
            'created_at' => $position->created_at->format('Y-m-d H:i:s'),
        ]);

        $stats = [
            'total' => JobPosition::count(),
            'active' => JobPosition::where('is_active', true)->count(),
            'inactive' => JobPosition::where('is_active', false)->count(),
            'featured' => JobPosition::where('is_featured', true)->count(),
        ];

        return Inertia::render('Admin/JobPositions/Index', [
            'positions' => $positions,
            'stats' => $stats,
            'filters' => $request->only(['status', 'employment_type', 'location_type', 'search']),
            'employmentTypes' => JobPosition::employmentTypes(),
            'locationTypes' => JobPosition::locationTypes(),
        ]);
    }

    /**
     * Show the form for creating a new job position.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/JobPositions/Create', [
            'employmentTypes' => JobPosition::employmentTypes(),
            'locationTypes' => JobPosition::locationTypes(),
        ]);
    }

    /**
     * Store a newly created job position.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'title_es' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'employment_type' => 'required|string|in:full-time,part-time,contract,internship',
            'location_type' => 'required|string|in:remote,hybrid,on-site',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'description_es' => 'nullable|string',
            'linkedin_url' => 'nullable|url|max:500',
            'apply_url' => 'nullable|url|max:500',
            'salary_range' => 'nullable|string|max:100',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        JobPosition::create($validated);

        return redirect()->route('admin.job-positions.index')
            ->with('success', 'Job position created successfully.');
    }

    /**
     * Show the form for editing the specified job position.
     */
    public function edit(JobPosition $jobPosition): Response
    {
        return Inertia::render('Admin/JobPositions/Edit', [
            'position' => [
                'id' => $jobPosition->id,
                'title' => $jobPosition->title,
                'title_es' => $jobPosition->title_es,
                'department' => $jobPosition->department,
                'employment_type' => $jobPosition->employment_type,
                'location_type' => $jobPosition->location_type,
                'location' => $jobPosition->location,
                'description' => $jobPosition->description,
                'description_es' => $jobPosition->description_es,
                'linkedin_url' => $jobPosition->linkedin_url,
                'apply_url' => $jobPosition->apply_url,
                'salary_range' => $jobPosition->salary_range,
                'is_active' => $jobPosition->is_active,
                'is_featured' => $jobPosition->is_featured,
                'sort_order' => $jobPosition->sort_order,
            ],
            'employmentTypes' => JobPosition::employmentTypes(),
            'locationTypes' => JobPosition::locationTypes(),
        ]);
    }

    /**
     * Update the specified job position.
     */
    public function update(Request $request, JobPosition $jobPosition)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'title_es' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'employment_type' => 'required|string|in:full-time,part-time,contract,internship',
            'location_type' => 'required|string|in:remote,hybrid,on-site',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'description_es' => 'nullable|string',
            'linkedin_url' => 'nullable|url|max:500',
            'apply_url' => 'nullable|url|max:500',
            'salary_range' => 'nullable|string|max:100',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $jobPosition->update($validated);

        return redirect()->route('admin.job-positions.index')
            ->with('success', 'Job position updated successfully.');
    }

    /**
     * Remove the specified job position.
     */
    public function destroy(JobPosition $jobPosition)
    {
        $jobPosition->delete();

        return redirect()->route('admin.job-positions.index')
            ->with('success', 'Job position deleted successfully.');
    }

    /**
     * Toggle active status.
     */
    public function toggleActive(JobPosition $jobPosition)
    {
        $jobPosition->update(['is_active' => !$jobPosition->is_active]);

        return back()->with('success', $jobPosition->is_active
            ? 'Job position activated.'
            : 'Job position deactivated.');
    }

    /**
     * Toggle featured status.
     */
    public function toggleFeatured(JobPosition $jobPosition)
    {
        $jobPosition->update(['is_featured' => !$jobPosition->is_featured]);

        return back()->with('success', $jobPosition->is_featured
            ? 'Job position marked as featured.'
            : 'Job position unmarked as featured.');
    }

    /**
     * Update sort order.
     */
    public function updateOrder(Request $request)
    {
        $validated = $request->validate([
            'positions' => 'required|array',
            'positions.*.id' => 'required|exists:job_positions,id',
            'positions.*.sort_order' => 'required|integer',
        ]);

        foreach ($validated['positions'] as $item) {
            JobPosition::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
        }

        return back()->with('success', 'Order updated successfully.');
    }
}
