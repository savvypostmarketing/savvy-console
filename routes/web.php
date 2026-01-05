<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\JobPositionController;
use App\Http\Controllers\Admin\LeadController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\PortfolioController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\TestimonialController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VisitorAnalyticsController;
use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Redirect root to login or dashboard
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('login');
});

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// Admin Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Leads Management
    Route::middleware('permission:view-leads')->group(function () {
        Route::get('/leads', [LeadController::class, 'index'])->name('leads.index');
        Route::get('/leads/export', [LeadController::class, 'export'])->name('leads.export');
        Route::get('/leads/{lead}', [LeadController::class, 'show'])->name('leads.show');
    });

    Route::middleware('permission:edit-leads')->group(function () {
        Route::patch('/leads/{lead}/status', [LeadController::class, 'updateStatus'])->name('leads.status');
        Route::patch('/leads/{lead}/notes', [LeadController::class, 'updateNotes'])->name('leads.notes');
        Route::patch('/leads/{lead}/spam', [LeadController::class, 'toggleSpam'])->name('leads.spam');
    });

    Route::middleware('permission:delete-leads')->group(function () {
        Route::delete('/leads/{lead}', [LeadController::class, 'destroy'])->name('leads.destroy');
    });

    // Users Management
    Route::middleware('permission:view-users')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
    });

    Route::middleware('permission:create-users')->group(function () {
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
    });

    Route::middleware('permission:edit-users')->group(function () {
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    });

    Route::middleware('permission:delete-users')->group(function () {
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });

    // Roles Management
    Route::middleware('permission:view-roles')->group(function () {
        Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
    });

    Route::middleware('permission:create-roles')->group(function () {
        Route::get('/roles/create', [RoleController::class, 'create'])->name('roles.create');
        Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
    });

    Route::middleware('permission:edit-roles')->group(function () {
        Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
        Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
    });

    Route::middleware('permission:delete-roles')->group(function () {
        Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
    });

    // Permissions Management (Super Admin only)
    Route::middleware('role:super-admin')->group(function () {
        Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index');
        Route::get('/permissions/create', [PermissionController::class, 'create'])->name('permissions.create');
        Route::post('/permissions', [PermissionController::class, 'store'])->name('permissions.store');
        Route::get('/permissions/{permission}/edit', [PermissionController::class, 'edit'])->name('permissions.edit');
        Route::put('/permissions/{permission}', [PermissionController::class, 'update'])->name('permissions.update');
        Route::delete('/permissions/{permission}', [PermissionController::class, 'destroy'])->name('permissions.destroy');
    });

    // Portfolio Management
    Route::get('/portfolio', [PortfolioController::class, 'index'])
        ->middleware('permission:view-portfolio')
        ->name('portfolio.index');

    Route::get('/portfolio/create', [PortfolioController::class, 'create'])
        ->middleware('permission:create-portfolio')
        ->name('portfolio.create');

    Route::post('/portfolio', [PortfolioController::class, 'store'])
        ->middleware('permission:create-portfolio')
        ->name('portfolio.store');

    Route::get('/portfolio/{portfolio}', [PortfolioController::class, 'show'])
        ->middleware('permission:view-portfolio')
        ->name('portfolio.show');

    Route::get('/portfolio/{portfolio}/edit', [PortfolioController::class, 'edit'])
        ->middleware('permission:edit-portfolio')
        ->name('portfolio.edit');

    Route::put('/portfolio/{portfolio}', [PortfolioController::class, 'update'])
        ->middleware('permission:edit-portfolio')
        ->name('portfolio.update');

    Route::patch('/portfolio/{portfolio}/toggle-published', [PortfolioController::class, 'togglePublished'])
        ->middleware('permission:edit-portfolio')
        ->name('portfolio.toggle-published');

    Route::patch('/portfolio/{portfolio}/toggle-featured', [PortfolioController::class, 'toggleFeatured'])
        ->middleware('permission:edit-portfolio')
        ->name('portfolio.toggle-featured');

    Route::post('/portfolio/{portfolio}/gallery', [PortfolioController::class, 'uploadGallery'])
        ->middleware('permission:edit-portfolio')
        ->name('portfolio.upload-gallery');

    Route::delete('/portfolio/{portfolio}/gallery/{imageId}', [PortfolioController::class, 'deleteGalleryImage'])
        ->middleware('permission:edit-portfolio')
        ->name('portfolio.delete-gallery-image');

    Route::patch('/portfolio/{portfolio}/gallery/reorder', [PortfolioController::class, 'reorderGallery'])
        ->middleware('permission:edit-portfolio')
        ->name('portfolio.reorder-gallery');

    Route::delete('/portfolio/{portfolio}', [PortfolioController::class, 'destroy'])
        ->middleware('permission:delete-portfolio')
        ->name('portfolio.destroy');

    // Settings Management (Super Admin only)
    Route::middleware('role:super-admin')->prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::post('/', [SettingsController::class, 'update'])->name('update');
        Route::get('/api', [SettingsController::class, 'api'])->name('api');
        Route::post('/api', [SettingsController::class, 'updateApi'])->name('api.update');
        Route::get('/email', [SettingsController::class, 'email'])->name('email');
        Route::post('/email', [SettingsController::class, 'updateEmail'])->name('email.update');
        Route::post('/email/test', [SettingsController::class, 'testEmail'])->name('email.test');
    });

    // Testimonials Management
    Route::middleware('permission:view-testimonials')->group(function () {
        Route::get('/testimonials', [TestimonialController::class, 'index'])->name('testimonials.index');
    });

    Route::middleware('permission:create-testimonials')->group(function () {
        Route::get('/testimonials/create', [TestimonialController::class, 'create'])->name('testimonials.create');
        Route::post('/testimonials', [TestimonialController::class, 'store'])->name('testimonials.store');
    });

    Route::middleware('permission:edit-testimonials')->group(function () {
        Route::get('/testimonials/{testimonial}/edit', [TestimonialController::class, 'edit'])->name('testimonials.edit');
        Route::put('/testimonials/{testimonial}', [TestimonialController::class, 'update'])->name('testimonials.update');
        Route::patch('/testimonials/{testimonial}/toggle-published', [TestimonialController::class, 'togglePublished'])->name('testimonials.toggle-published');
        Route::patch('/testimonials/{testimonial}/toggle-featured', [TestimonialController::class, 'toggleFeatured'])->name('testimonials.toggle-featured');
        Route::patch('/testimonials/update-order', [TestimonialController::class, 'updateOrder'])->name('testimonials.update-order');
    });

    Route::middleware('permission:delete-testimonials')->group(function () {
        Route::delete('/testimonials/{testimonial}', [TestimonialController::class, 'destroy'])->name('testimonials.destroy');
    });

    // Job Positions Management (requires manage-settings permission)
    Route::middleware('permission:manage-settings')->prefix('job-positions')->name('job-positions.')->group(function () {
        Route::get('/', [JobPositionController::class, 'index'])->name('index');
        Route::get('/create', [JobPositionController::class, 'create'])->name('create');
        Route::post('/', [JobPositionController::class, 'store'])->name('store');
        Route::get('/{jobPosition}/edit', [JobPositionController::class, 'edit'])->name('edit');
        Route::put('/{jobPosition}', [JobPositionController::class, 'update'])->name('update');
        Route::delete('/{jobPosition}', [JobPositionController::class, 'destroy'])->name('destroy');
        Route::patch('/{jobPosition}/toggle-active', [JobPositionController::class, 'toggleActive'])->name('toggle-active');
        Route::patch('/{jobPosition}/toggle-featured', [JobPositionController::class, 'toggleFeatured'])->name('toggle-featured');
        Route::patch('/update-order', [JobPositionController::class, 'updateOrder'])->name('update-order');
    });

    // Visitor Analytics (Super Admin only)
    Route::middleware('role:super-admin')->prefix('analytics')->name('analytics.')->group(function () {
        Route::get('/', [VisitorAnalyticsController::class, 'index'])->name('index');
        Route::get('/sessions/{session}', [VisitorAnalyticsController::class, 'showSession'])->name('session');
        Route::get('/live', [VisitorAnalyticsController::class, 'liveSessions'])->name('live');
    });
});

// API endpoint for current user (used by React)
Route::middleware('auth')->get('/api/me', [AuthController::class, 'me']);
