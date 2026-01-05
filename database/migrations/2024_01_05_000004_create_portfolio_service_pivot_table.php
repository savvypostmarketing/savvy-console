<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('portfolio_portfolio_service', function (Blueprint $table) {
            $table->id();
            $table->foreignId('portfolio_id')->constrained()->onDelete('cascade');
            $table->foreignId('portfolio_service_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['portfolio_id', 'portfolio_service_id'], 'portfolio_service_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portfolio_portfolio_service');
    }
};
