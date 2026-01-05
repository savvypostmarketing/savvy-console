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
        Schema::create('lead_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->onDelete('cascade');

            // Step info
            $table->string('step_id'); // e.g., 'name', 'email', 'services'
            $table->integer('step_number');
            $table->string('step_type'); // e.g., 'input', 'choice', 'discovery'

            // Data captured at this step
            $table->json('data')->nullable();

            // Timing
            $table->integer('time_spent_seconds')->nullable(); // Time spent on this step
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            // Tracking
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['lead_id', 'step_id']);
            $table->index('step_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_steps');
    }
};
