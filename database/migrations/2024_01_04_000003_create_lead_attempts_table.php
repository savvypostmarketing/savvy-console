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
        Schema::create('lead_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->nullable()->constrained()->onDelete('set null');

            // Request info
            $table->string('ip_address', 45);
            $table->string('user_agent')->nullable();
            $table->string('session_id')->nullable();
            $table->string('fingerprint')->nullable();

            // Attempt details
            $table->string('action'); // e.g., 'create', 'update_step', 'complete'
            $table->string('step_id')->nullable();
            $table->json('request_data')->nullable();

            // Spam indicators
            $table->boolean('is_spam')->default(false);
            $table->float('spam_score')->default(0);
            $table->json('spam_reasons')->nullable();
            $table->string('honeypot_value')->nullable();
            $table->integer('form_fill_time_ms')->nullable(); // Time to fill form (bots are fast)

            // Rate limiting
            $table->boolean('rate_limited')->default(false);

            // Response
            $table->integer('response_code')->nullable();
            $table->boolean('success')->default(false);
            $table->string('error_message')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('ip_address');
            $table->index(['ip_address', 'created_at']);
            $table->index('session_id');
            $table->index('is_spam');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_attempts');
    }
};
