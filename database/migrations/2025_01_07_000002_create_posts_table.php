<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('title');
            $table->string('title_es')->nullable();
            $table->string('slug')->unique();

            // Category relationship
            $table->foreignId('category_id')->nullable()->constrained('post_categories')->onDelete('set null');

            // Author relationship
            $table->foreignId('author_id')->nullable()->constrained('users')->onDelete('set null');

            // Excerpt
            $table->text('excerpt')->nullable();
            $table->text('excerpt_es')->nullable();

            // Main content (HTML from WYSIWYG editor)
            $table->longText('content')->nullable();
            $table->longText('content_es')->nullable();

            // Featured image
            $table->string('featured_image')->nullable();
            $table->string('featured_image_alt')->nullable();
            $table->string('featured_image_alt_es')->nullable();

            // Reading time
            $table->integer('reading_time_minutes')->default(5);

            // Stats
            $table->unsignedBigInteger('views_count')->default(0);
            $table->unsignedBigInteger('likes_count')->default(0);

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

            // Indexes
            $table->index('is_published');
            $table->index('is_featured');
            $table->index('published_at');
            $table->index(['views_count', 'likes_count']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
