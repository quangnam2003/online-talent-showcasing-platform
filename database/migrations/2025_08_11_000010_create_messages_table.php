<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tu tham chieu kep: sender va receiver deu tro ve users
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('receiver_id')->constrained('users')->cascadeOnDelete();
            $table->text('content');
            $table->timestamp('read_at')->nullable(); // null = chua doc
            $table->timestamps();

            $table->index(['receiver_id', 'read_at']);   // badge tin chua doc
            $table->index(['sender_id', 'receiver_id']); // truy van hoi thoai
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
