<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('posts')->onDelete('cascade');
            $table->string('ip_address', 45);
            $table->string('session_id')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->timestamps();

            // Prevent duplicate likes from same IP
            $table->unique(['post_id', 'ip_address']);
            $table->index('post_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_likes');
    }
};
