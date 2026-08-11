<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // restrictOnDelete: khong cho xoa category dang duoc video su dung
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('file_path');
            $table->string('thumbnail')->nullable();
            $table->enum('privacy', ['public', 'private'])->default('public');   // FR8
            $table->boolean('allow_comments')->default(true);                    // FR8
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending'); // FR8 - admin duyet
            $table->unsignedBigInteger('views')->default(0);
            $table->unsignedInteger('likes_count')->default(0);      // counter cache
            $table->unsignedInteger('comments_count')->default(0);   // counter cache
            $table->decimal('avg_rating', 3, 2)->default(0);         // counter cache
            $table->float('trending_score')->default(0);             // FR3
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'privacy', 'category_id']); // query nong cua trang Explore
            $table->index('trending_score');                     // trang Trending
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('videos');
    }
};
