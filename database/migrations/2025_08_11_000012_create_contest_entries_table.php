<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contest_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contest_id')->constrained()->cascadeOnDelete();
            $table->foreignId('video_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('votes_count')->default(0); // counter cache cho leaderboard
            $table->timestamps();

            $table->unique(['contest_id', 'video_id']); // 1 video chi du thi 1 lan / contest
            $table->unique(['contest_id', 'user_id']);  // 1 user chi nop 1 bai / contest
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contest_entries');
    }
};
