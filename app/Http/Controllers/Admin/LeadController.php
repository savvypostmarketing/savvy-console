<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Services\IntentScoringService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LeadController extends Controller
{
    /**
     * Display a listing of leads
     */
    public function index(Request $request): Response
    {
        $query = Lead::with('steps')->latest();

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        // Search by name or email (using ILIKE for PostgreSQL case-insensitive)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('email', 'ilike', "%{$search}%")
                    ->orWhere('company', 'ilike', "%{$search}%");
            });
        }

        $leads = $query->paginate(20)->through(fn ($lead) => [
            'id' => $lead->id,
            'uuid' => $lead->uuid,
            'name' => $lead->name,
            'email' => $lead->email,
            'phone' => $lead->phone,
            'company' => $lead->company,
            'services' => $lead->services,
            'status' => $lead->status,
            'spam_score' => $lead->spam_score,
            'is_spam' => $lead->is_spam,
            'steps_count' => $lead->steps->count(),
            'utm_source' => $lead->utm_source,
            'utm_medium' => $lead->utm_medium,
            'utm_campaign' => $lead->utm_campaign,
            'created_at' => $lead->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $lead->updated_at->format('Y-m-d H:i:s'),
        ]);

        $stats = [
            'total' => Lead::count(),
            'new' => Lead::where('status', 'new')->count(),
            'contacted' => Lead::where('status', 'contacted')->count(),
            'qualified' => Lead::where('status', 'qualified')->count(),
            'converted' => Lead::where('status', 'converted')->count(),
            'spam' => Lead::where('is_spam', true)->count(),
        ];

        return Inertia::render('Admin/Leads/Index', [
            'leads' => $leads,
            'stats' => $stats,
            'filters' => $request->only(['status', 'from', 'to', 'search']),
        ]);
    }

    /**
     * Display the specified lead
     */
    public function show(Lead $lead): Response
    {
        $lead->load(['steps', 'attempts', 'visitorSessions.pageViews', 'visitorSessions.events']);

        // Get intent breakdown for the latest session
        $intentBreakdown = null;
        $latestSession = $lead->visitorSessions->first();
        if ($latestSession) {
            $intentService = new IntentScoringService();
            $intentBreakdown = $intentService->calculateScore($latestSession);
        }

        return Inertia::render('Admin/Leads/Show', [
            'lead' => [
                'id' => $lead->id,
                'uuid' => $lead->uuid,
                'name' => $lead->name,
                'email' => $lead->email,
                'company' => $lead->company,
                'has_website' => $lead->has_website,
                'website_url' => $lead->website_url,
                'industry' => $lead->industry,
                'other_industry' => $lead->other_industry,
                'services' => $lead->services,
                'message' => $lead->message,
                'discovery_answers' => $lead->discovery_answers,
                'terms_accepted' => $lead->terms_accepted,
                'status' => $lead->status,
                'spam_score' => $lead->spam_score,
                'is_spam' => $lead->is_spam,
                'locale' => $lead->locale,
                'ip_address' => $lead->ip_address,
                'user_agent' => $lead->user_agent,
                'referrer' => $lead->referrer,
                'landing_page' => $lead->landing_page,
                'utm_source' => $lead->utm_source,
                'utm_medium' => $lead->utm_medium,
                'utm_campaign' => $lead->utm_campaign,
                'utm_term' => $lead->utm_term,
                'utm_content' => $lead->utm_content,
                'notes' => $lead->notes,
                'started_at' => $lead->started_at?->format('Y-m-d H:i:s'),
                'completed_at' => $lead->completed_at?->format('Y-m-d H:i:s'),
                'created_at' => $lead->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $lead->updated_at->format('Y-m-d H:i:s'),
                'steps' => $lead->steps->map(fn ($step) => [
                    'id' => $step->id,
                    'step_id' => $step->step_id,  // 'name', 'email', 'services', etc.
                    'step_type' => $step->step_type,  // 'input', 'choice', 'discovery', etc.
                    'step_number' => $step->step_number,
                    'data' => $step->data,
                    'time_spent' => $step->time_spent_seconds ?? $step->calculateTimeSpent(),
                    'created_at' => $step->created_at->format('Y-m-d H:i:s'),
                ]),
                'attempts' => $lead->attempts->map(fn ($attempt) => [
                    'id' => $attempt->id,
                    'action' => $attempt->action,
                    'ip_address' => $attempt->ip_address,
                    'is_suspicious' => $attempt->is_suspicious,
                    'failure_reason' => $attempt->failure_reason,
                    'created_at' => $attempt->created_at->format('Y-m-d H:i:s'),
                ]),
            ],
            'visitorSessions' => $lead->visitorSessions->map(fn ($session) => [
                'id' => $session->id,
                'uuid' => $session->uuid,
                'intent_score' => $session->intent_score,
                'intent_level' => $session->intent_level,
                'status' => $session->status,
                'device_type' => $session->device_type,
                'browser' => $session->browser,
                'os' => $session->os,
                'country' => $session->country_name ?? $session->country,
                'city' => $session->city,
                'referrer_type' => $session->referrer_type,
                'landing_page' => $session->landing_page,
                'page_views_count' => $session->page_views_count,
                'events_count' => $session->events_count,
                'total_time_seconds' => $session->total_time_seconds,
                'scroll_depth_max' => $session->scroll_depth_max,
                'visited_pricing' => $session->visited_pricing,
                'visited_services' => $session->visited_services,
                'visited_portfolio' => $session->visited_portfolio,
                'visited_contact' => $session->visited_contact,
                'started_form' => $session->started_form,
                'completed_form' => $session->completed_form,
                'clicked_cta' => $session->clicked_cta,
                'watched_video' => $session->watched_video,
                'is_returning' => $session->is_returning,
                'started_at' => $session->started_at?->format('Y-m-d H:i:s'),
                'last_activity_at' => $session->last_activity_at?->diffForHumans(),
                'page_views' => $session->pageViews->map(fn ($pv) => [
                    'id' => $pv->id,
                    'path' => $pv->path,
                    'page_type' => $pv->page_type,
                    'time_on_page' => $pv->time_on_page_seconds,
                    'scroll_depth' => $pv->scroll_depth_max,
                    'entered_at' => $pv->entered_at?->format('H:i:s'),
                ]),
                'events' => $session->events()
                    ->orderBy('occurred_at', 'desc')
                    ->limit(50)
                    ->get()
                    ->map(fn ($event) => [
                        'id' => $event->id,
                        'type' => $event->event_type,
                        'category' => $event->event_category,
                        'label' => $event->event_label,
                        'element_text' => $event->element_text,
                        'intent_points' => $event->intent_points,
                        'occurred_at' => $event->occurred_at?->format('H:i:s'),
                    ]),
            ]),
            'intentBreakdown' => $intentBreakdown,
        ]);
    }

    /**
     * Update lead status
     */
    public function updateStatus(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:new,contacted,qualified,converted,lost'],
        ]);

        $lead->update(['status' => $validated['status']]);

        return back()->with('success', 'Lead status updated successfully.');
    }

    /**
     * Update lead notes
     */
    public function updateNotes(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $lead->update(['notes' => $validated['notes']]);

        return back()->with('success', 'Lead notes updated successfully.');
    }

    /**
     * Mark/unmark lead as spam
     */
    public function toggleSpam(Request $request, Lead $lead)
    {
        $lead->update(['is_spam' => !$lead->is_spam]);

        return back()->with('success', $lead->is_spam ? 'Lead marked as spam.' : 'Lead unmarked as spam.');
    }

    /**
     * Delete the specified lead
     */
    public function destroy(Lead $lead)
    {
        $lead->delete();

        return redirect()->route('admin.leads.index')
            ->with('success', 'Lead deleted successfully.');
    }

    /**
     * Export leads to CSV
     */
    public function export(Request $request)
    {
        $query = Lead::query();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $leads = $query->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="leads-' . now()->format('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($leads) {
            $file = fopen('php://output', 'w');

            // Header row
            fputcsv($file, [
                'ID', 'Name', 'Email', 'Phone', 'Company', 'Services',
                'Budget', 'Timeline', 'Status', 'UTM Source', 'UTM Medium',
                'UTM Campaign', 'Created At'
            ]);

            // Data rows
            foreach ($leads as $lead) {
                fputcsv($file, [
                    $lead->id,
                    $lead->name,
                    $lead->email,
                    $lead->phone,
                    $lead->company,
                    is_array($lead->services) ? implode(', ', $lead->services) : $lead->services,
                    $lead->budget,
                    $lead->timeline,
                    $lead->status,
                    $lead->utm_source,
                    $lead->utm_medium,
                    $lead->utm_campaign,
                    $lead->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
