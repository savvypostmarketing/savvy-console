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
        Schema::table('leads', function (Blueprint $table) {
            $table->string('country', 2)->nullable()->after('ip_address');
            $table->string('country_name', 100)->nullable()->after('country');
            $table->string('city', 100)->nullable()->after('country_name');
            $table->index('country');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropIndex(['country']);
            $table->dropColumn(['country', 'country_name', 'city']);
        });
    }
};
