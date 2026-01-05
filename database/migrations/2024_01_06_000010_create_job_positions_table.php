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
        Schema::create('job_positions', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('title_es')->nullable();
            $table->string('department')->nullable();
            $table->string('employment_type')->default('full-time'); // full-time, part-time, contract, internship
            $table->string('location_type')->default('remote'); // remote, hybrid, on-site
            $table->string('location')->nullable(); // City/Country if applicable
            $table->text('description')->nullable();
            $table->text('description_es')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('apply_url')->nullable(); // Alternative to LinkedIn
            $table->string('salary_range')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('is_active');
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_positions');
    }
};
