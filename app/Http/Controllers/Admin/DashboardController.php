<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Show the dashboard
     */
    public function index(): Response
    {
        $stats = [
            'total_leads' => Lead::count(),
            'new_leads' => Lead::where('status', 'new')->count(),
            'contacted_leads' => Lead::where('status', 'contacted')->count(),
            'converted_leads' => Lead::where('status', 'converted')->count(),
            'total_users' => User::count(),
            'leads_by_site' => [
                'savvypostmarketing' => Lead::where('source_site', Lead::SITE_POST_MARKETING)->count(),
                'savvytechinnovation' => Lead::where('source_site', Lead::SITE_TECH_INNOVATION)->count(),
            ],
            'recent_leads' => Lead::with('steps')
                ->latest()
                ->take(5)
                ->get()
                ->map(fn ($lead) => [
                    'id' => $lead->id,
                    'uuid' => $lead->uuid,
                    'name' => $lead->name,
                    'email' => $lead->email,
                    'status' => $lead->status,
                    'services' => $lead->services,
                    'source_site' => $lead->source_site,
                    'site_display' => $lead->site_display,
                    'created_at' => $lead->created_at->diffForHumans(),
                ]),
        ];

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
        ]);
    }
}
