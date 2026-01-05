<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\JobPositionController;
use App\Http\Controllers\Api\LeadController;
use App\Http\Controllers\Api\PortfolioController;
use App\Http\Controllers\Api\TestimonialController;
use App\Http\Controllers\Api\VisitorTrackingController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

// Health check
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toIso8601String(),
    ]);
});

// Lead routes
Route::prefix('leads')->group(function () {
    // Start a new lead session
    Route::post('/start', [LeadController::class, 'start'])
        ->middleware('throttle:30,1'); // 30 requests per minute

    // Update a step
    Route::post('/{uuid}/step', [LeadController::class, 'updateStep'])
        ->middleware('throttle:60,1'); // 60 requests per minute

    // Complete the lead
    Route::post('/{uuid}/complete', [LeadController::class, 'complete'])
        ->middleware('throttle:10,1'); // 10 requests per minute

    // Get lead status (for resuming)
    Route::get('/{uuid}/status', [LeadController::class, 'status'])
        ->middleware('throttle:30,1');
});

// Portfolio routes (public)
Route::prefix('portfolio')->group(function () {
    Route::get('/', [PortfolioController::class, 'index'])
        ->middleware('throttle:60,1');

    Route::get('/services', [PortfolioController::class, 'services'])
        ->middleware('throttle:60,1');

    Route::get('/industries', [PortfolioController::class, 'industries'])
        ->middleware('throttle:60,1');

    Route::get('/{slug}', [PortfolioController::class, 'show'])
        ->middleware('throttle:60,1');
});

// Testimonials routes (public)
Route::prefix('testimonials')->group(function () {
    Route::get('/', [TestimonialController::class, 'index'])
        ->middleware('throttle:60,1');

    Route::get('/featured', [TestimonialController::class, 'featured'])
        ->middleware('throttle:60,1');

    Route::get('/service/{service}', [TestimonialController::class, 'byService'])
        ->middleware('throttle:60,1');
});

// Visitor Tracking routes (Intent Proxy)
Route::prefix('tracking')->group(function () {
    // Initialize or resume session
    Route::post('/session', [VisitorTrackingController::class, 'initSession'])
        ->middleware('throttle:60,1');

    // Track page view
    Route::post('/pageview', [VisitorTrackingController::class, 'trackPageView'])
        ->middleware('throttle:120,1');

    // Track event
    Route::post('/event', [VisitorTrackingController::class, 'trackEvent'])
        ->middleware('throttle:300,1'); // Higher limit for events

    // Update engagement metrics
    Route::post('/engagement', [VisitorTrackingController::class, 'updateEngagement'])
        ->middleware('throttle:120,1');

    // Link session to lead
    Route::post('/link-lead', [VisitorTrackingController::class, 'linkLead'])
        ->middleware('throttle:30,1');

    // End session
    Route::post('/end-session', [VisitorTrackingController::class, 'endSession'])
        ->middleware('throttle:30,1');
});

// Job Positions routes (public)
Route::prefix('job-positions')->group(function () {
    Route::get('/', [JobPositionController::class, 'index'])
        ->middleware('throttle:60,1');
});
