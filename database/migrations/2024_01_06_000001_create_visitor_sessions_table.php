<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('visitor_sessions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // Visitor identification
            $table->string('visitor_id', 64)->index(); // Browser fingerprint or cookie ID
            $table->string('session_token', 64)->unique();

            // Link to lead when converted
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();

            // Device & Browser info
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->string('device_type', 20)->nullable(); // desktop, mobile, tablet
            $table->string('browser', 50)->nullable();
            $table->string('browser_version', 20)->nullable();
            $table->string('os', 50)->nullable();
            $table->string('os_version', 20)->nullable();
            $table->boolean('is_bot')->default(false);

            // Location (from IP)
            $table->string('country', 2)->nullable();
            $table->string('country_name', 100)->nullable();
            $table->string('region', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('timezone', 50)->nullable();

            // Traffic source
            $table->string('referrer_url')->nullable();
            $table->string('referrer_domain')->nullable();
            $table->string('referrer_type', 30)->nullable(); // direct, organic, social, paid, referral, email
            $table->string('landing_page')->nullable();

            // UTM parameters
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->string('utm_term')->nullable();
            $table->string('utm_content')->nullable();

            // Session metrics
            $table->integer('page_views_count')->default(0);
            $table->integer('events_count')->default(0);
            $table->integer('total_time_seconds')->default(0);
            $table->integer('engaged_time_seconds')->default(0); // Time actively on page
            $table->decimal('scroll_depth_avg', 5, 2)->default(0);
            $table->decimal('scroll_depth_max', 5, 2)->default(0);

            // Intent scoring
            $table->decimal('intent_score', 5, 2)->default(0); // 0-100
            $table->string('intent_level', 20)->default('cold'); // cold, warm, hot, qualified
            $table->json('intent_signals')->nullable(); // Detailed scoring breakdown

            // Engagement flags
            $table->boolean('visited_pricing')->default(false);
            $table->boolean('visited_services')->default(false);
            $table->boolean('visited_portfolio')->default(false);
            $table->boolean('visited_contact')->default(false);
            $table->boolean('started_form')->default(false);
            $table->boolean('completed_form')->default(false);
            $table->boolean('clicked_cta')->default(false);
            $table->boolean('watched_video')->default(false);

            // Return visitor tracking
            $table->boolean('is_returning')->default(false);
            $table->integer('previous_sessions_count')->default(0);
            $table->timestamp('first_seen_at')->nullable();

            // Session status
            $table->enum('status', ['active', 'idle', 'ended'])->default('active');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamp('ended_at')->nullable();

            // Language preference
            $table->string('locale', 5)->nullable();
            $table->string('accept_language')->nullable();

            $table->timestamps();

            // Indexes (visitor_id already indexed via ->index() on column definition)
            $table->index('intent_score');
            $table->index('intent_level');
            $table->index('status');
            $table->index('created_at');
            $table->index(['ip_address', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitor_sessions');
    }
};
