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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // Basic contact info
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('company')->nullable();

            // Website info
            $table->enum('has_website', ['yes', 'no'])->nullable();
            $table->string('website_url')->nullable();

            // Business info
            $table->string('industry')->nullable();
            $table->string('other_industry')->nullable();
            $table->json('services')->nullable();

            // Additional info
            $table->text('message')->nullable();

            // Discovery form answers (JSON)
            $table->json('discovery_answers')->nullable();

            // Tracking
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->string('referrer')->nullable();

            // UTM parameters
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->string('utm_term')->nullable();
            $table->string('utm_content')->nullable();

            // Session tracking
            $table->string('session_id')->nullable()->index();
            $table->string('fingerprint')->nullable();

            // Status
            $table->enum('status', ['in_progress', 'completed', 'abandoned'])->default('in_progress');
            $table->integer('current_step')->default(0);
            $table->integer('total_steps')->default(0);
            $table->boolean('terms_accepted')->default(false);

            // Spam protection
            $table->boolean('is_spam')->default(false);
            $table->float('spam_score')->default(0);
            $table->string('honeypot')->nullable();

            // Locale
            $table->string('locale', 5)->default('en');

            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('email');
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
        Schema::dropIfExists('leads');
    }
};
