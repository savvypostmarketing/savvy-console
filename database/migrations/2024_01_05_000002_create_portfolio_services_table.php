<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('portfolio_services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_es')->nullable();
            $table->string('slug')->unique();
            $table->string('color')->default('primary'); // primary, secondary, success, warning, danger
            $table->text('description')->nullable();
            $table->text('description_es')->nullable();
            $table->string('icon')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portfolio_services');
    }
};
