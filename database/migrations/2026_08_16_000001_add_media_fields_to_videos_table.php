<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * FR2: tiet muc co the la video hoac am thanh (mp3, m4a, wav…).
     * - mime_type: kieu tep thuc te (sniff noi dung) de trang xem chon <video>/<audio>
     * - duration : do dai (giay) do phia trinh duyet doc metadata khi upload — hien "3:24" tren the
     */
    public function up(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->string('mime_type', 100)->nullable()->after('file_path');
            $table->unsignedInteger('duration')->nullable()->after('mime_type');
        });
    }

    public function down(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->dropColumn(['mime_type', 'duration']);
        });
    }
};
