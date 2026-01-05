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
        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // Author info
            $table->string('name');
            $table->string('role')->nullable();
            $table->string('role_es')->nullable();
            $table->string('company')->nullable();
            $table->string('company_es')->nullable();
            $table->string('avatar')->nullable();

            // Testimonial content
            $table->text('quote');
            $table->text('quote_es')->nullable();
            $table->integer('rating')->default(5);

            // Associated project (optional)
            $table->string('project_title')->nullable();
            $table->string('project_title_es')->nullable();
            $table->string('project_screenshot')->nullable();

            // Categorization
            $table->string('source')->default('website'); // website, google, portfolio
            $table->json('services')->nullable(); // Associated services

            // Display settings
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_published')->default(true);
            $table->integer('sort_order')->default(0);

            // Meta
            $table->string('date_label')->nullable(); // "a year ago", "2 months ago"
            $table->string('extra_info')->nullable(); // "Local Guide·14 reviews·31 photos"

            $table->timestamps();
            $table->softDeletes();

            $table->index('source');
            $table->index('is_featured');
            $table->index('is_published');
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('testimonials');
    }
};
