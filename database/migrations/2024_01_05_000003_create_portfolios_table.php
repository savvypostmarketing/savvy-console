<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('portfolios', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('title');
            $table->string('title_es')->nullable();
            $table->string('slug')->unique();

            // Industry relationship
            $table->foreignId('industry_id')->constrained('portfolio_industries')->onDelete('restrict');

            // Main content
            $table->text('description')->nullable();
            $table->text('description_es')->nullable();
            $table->text('challenge')->nullable();
            $table->text('challenge_es')->nullable();
            $table->text('solution')->nullable();
            $table->text('solution_es')->nullable();

            // Images
            $table->string('featured_image')->nullable();

            // External link
            $table->string('website_url')->nullable();

            // Testimonial
            $table->text('testimonial_quote')->nullable();
            $table->text('testimonial_quote_es')->nullable();
            $table->string('testimonial_author')->nullable();
            $table->string('testimonial_role')->nullable();
            $table->string('testimonial_role_es')->nullable();
            $table->string('testimonial_avatar')->nullable();

            // Video section
            $table->string('video_url')->nullable();
            $table->string('video_thumbnail')->nullable();
            $table->text('video_intro_text')->nullable();
            $table->text('video_intro_text_es')->nullable();

            // Status
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->integer('sort_order')->default(0);

            // SEO
            $table->string('meta_title')->nullable();
            $table->string('meta_title_es')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_description_es')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portfolios');
    }
};
