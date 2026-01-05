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
        Schema::create('page_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visitor_session_id')->constrained()->onDelete('cascade');

            // Page info
            $table->string('url')->index();
            $table->string('path')->index();
            $table->string('page_title')->nullable();
            $table->string('page_type', 50)->nullable(); // home, service, portfolio, blog, contact, etc.

            // Query parameters (excluding UTM which are on session)
            $table->json('query_params')->nullable();
            $table->string('hash')->nullable(); // URL fragment

            // Previous page
            $table->string('previous_url')->nullable();
            $table->string('previous_path')->nullable();

            // Engagement metrics
            $table->integer('time_on_page_seconds')->default(0);
            $table->integer('engaged_time_seconds')->default(0);
            $table->decimal('scroll_depth', 5, 2)->default(0); // Percentage 0-100
            $table->decimal('scroll_depth_max', 5, 2)->default(0);
            $table->integer('scroll_events')->default(0);
            $table->integer('click_events')->default(0);
            $table->integer('mouse_movements')->default(0);
            $table->integer('key_presses')->default(0);

            // Content interaction
            $table->boolean('read_content')->default(false); // Spent enough time + scrolled
            $table->boolean('interacted')->default(false); // Any click/interaction
            $table->boolean('bounced')->default(false); // Left without interaction

            // Viewport
            $table->integer('viewport_width')->nullable();
            $table->integer('viewport_height')->nullable();
            $table->integer('document_height')->nullable();

            // Exit info
            $table->string('exit_url')->nullable();
            $table->boolean('is_exit_page')->default(false);

            // Timing
            $table->integer('load_time_ms')->nullable(); // Page load time
            $table->integer('dom_ready_ms')->nullable();
            $table->integer('first_contentful_paint_ms')->nullable();

            $table->timestamp('entered_at');
            $table->timestamp('exited_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('page_type');
            $table->index('entered_at');
            $table->index(['visitor_session_id', 'entered_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_views');
    }
};
