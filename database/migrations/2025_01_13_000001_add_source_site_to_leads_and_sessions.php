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
        // Add source_site to leads table
        Schema::table('leads', function (Blueprint $table) {
            $table->string('source_site', 50)->default('savvypostmarketing')->after('locale');
            $table->index('source_site');
        });

        // Add source_site to visitor_sessions table
        Schema::table('visitor_sessions', function (Blueprint $table) {
            $table->string('source_site', 50)->default('savvypostmarketing')->after('locale');
            $table->index('source_site');
        });

        // Add source_site to lead_attempts table for tracking
        Schema::table('lead_attempts', function (Blueprint $table) {
            $table->string('source_site', 50)->nullable()->after('action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropIndex(['source_site']);
            $table->dropColumn('source_site');
        });

        Schema::table('visitor_sessions', function (Blueprint $table) {
            $table->dropIndex(['source_site']);
            $table->dropColumn('source_site');
        });

        Schema::table('lead_attempts', function (Blueprint $table) {
            $table->dropColumn('source_site');
        });
    }
};
