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
        Schema::create('visitor_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visitor_session_id')->constrained()->onDelete('cascade');
            $table->foreignId('page_view_id')->nullable()->constrained()->nullOnDelete();

            // Event identification
            $table->string('event_type', 50)->index(); // click, scroll, form_start, form_complete, video_play, etc.
            $table->string('event_category', 50)->nullable(); // navigation, engagement, conversion, video, form
            $table->string('event_action', 100)->nullable(); // clicked_cta, scrolled_to_section, etc.
            $table->string('event_label')->nullable(); // Additional context

            // Element info
            $table->string('element_type', 50)->nullable(); // button, link, form, video, etc.
            $table->string('element_id')->nullable();
            $table->string('element_class')->nullable();
            $table->string('element_text')->nullable();
            $table->string('element_href')->nullable();

            // Position info
            $table->integer('click_x')->nullable();
            $table->integer('click_y')->nullable();
            $table->decimal('scroll_position', 5, 2)->nullable();
            $table->string('viewport_section', 50)->nullable(); // header, hero, services, cta, footer

            // Event data
            $table->json('data')->nullable(); // Additional event-specific data

            // Intent signals
            $table->integer('intent_points')->default(0); // Points this event contributes to intent score
            $table->boolean('is_conversion_event')->default(false);
            $table->boolean('is_engagement_event')->default(false);

            // Timing
            $table->integer('time_since_page_load_ms')->nullable();
            $table->integer('time_since_session_start_ms')->nullable();

            $table->timestamp('occurred_at');
            $table->timestamps();

            // Indexes
            $table->index('event_category');
            $table->index('occurred_at');
            $table->index(['visitor_session_id', 'event_type']);
            $table->index(['event_type', 'occurred_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitor_events');
    }
};
