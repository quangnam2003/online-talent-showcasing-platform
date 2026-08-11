<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'creator', 'mentor'])->default('creator')->after('password');
            $table->string('avatar')->nullable()->after('role');
            $table->text('bio')->nullable()->after('avatar');
            $table->string('location')->nullable()->after('bio');
            $table->text('achievements')->nullable()->after('location');
            $table->unsignedInteger('followers_count')->default(0)->after('achievements');
            $table->boolean('is_active')->default(true)->after('followers_count');
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn([
                'role', 'avatar', 'bio', 'location',
                'achievements', 'followers_count', 'is_active',
            ]);
        });
    }
};
