<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_es')->nullable();
            $table->string('slug')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Pivot table for posts <-> tags (many-to-many)
        Schema::create('post_post_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('posts')->onDelete('cascade');
            $table->foreignId('post_tag_id')->constrained('post_tags')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['post_id', 'post_tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_post_tag');
        Schema::dropIfExists('post_tags');
    }
};
