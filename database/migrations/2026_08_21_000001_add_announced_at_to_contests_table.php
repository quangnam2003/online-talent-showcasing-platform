<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * FR7 "Announce winner" (actor: Scheduler): moc da cong bo ket qua.
     * Lenh `contests:announce` (chay moi gio) tim contest qua end_at ma announced_at
     * con trong → gui thong bao ket qua roi dong dau announced_at (khong gui lai).
     */
    public function up(): void
    {
        Schema::table('contests', function (Blueprint $table) {
            $table->timestamp('announced_at')->nullable()->after('end_at');
        });
    }

    public function down(): void
    {
        Schema::table('contests', function (Blueprint $table) {
            $table->dropColumn('announced_at');
        });
    }
};
