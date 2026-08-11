<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Vote tro vao ENTRY (khong phai video) de phieu chi co gia tri trong pham vi cuoc thi
        Schema::create('votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('entry_id')->constrained('contest_entries')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'entry_id']); // 1 vote / user / bai du thi
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('votes');
    }
};
