<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// FR7: "1 phieu / user / cuoc thi" truoc day chi kiem tra o tang ung dung (exists() roi create()),
// unique DB moi la (user_id, entry_id) nen 2 request song song vao 2 bai khac nhau cua cung cuoc thi
// van co the lot 2 phieu. Them cot contest_id + unique (user_id, contest_id) de DB tu chan.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('votes', function (Blueprint $table) {
            $table->foreignId('contest_id')->nullable()->after('entry_id')
                ->constrained('contests')->cascadeOnDelete();
        });

        // Backfill tu bai du thi (subquery tuong quan — chay duoc ca MySQL lan SQLite)
        DB::table('votes')->whereNull('contest_id')->update([
            'contest_id' => DB::raw('(select contest_id from contest_entries where contest_entries.id = votes.entry_id)'),
        ]);

        // Don phieu trung (cung user, cung cuoc thi) neu da lot tu truoc: giu phieu som nhat (id nho nhat)
        $dups = DB::table('votes')
            ->select('user_id', 'contest_id', DB::raw('MIN(id) as keep_id'), DB::raw('COUNT(*) as n'))
            ->groupBy('user_id', 'contest_id')
            ->having('n', '>', 1)
            ->get();
        $touchedEntries = [];
        foreach ($dups as $d) {
            $extra = DB::table('votes')
                ->where('user_id', $d->user_id)->where('contest_id', $d->contest_id)
                ->where('id', '<>', $d->keep_id);
            $touchedEntries = array_merge($touchedEntries, $extra->pluck('entry_id')->all());
            $extra->delete();
        }
        if ($touchedEntries) {
            DB::table('contest_entries')->whereIn('id', array_unique($touchedEntries))->update([
                'votes_count' => DB::raw('(select count(*) from votes where votes.entry_id = contest_entries.id)'),
            ]);
        }

        Schema::table('votes', function (Blueprint $table) {
            $table->foreignId('contest_id')->nullable(false)->change();
            $table->unique(['user_id', 'contest_id'], 'votes_user_contest_unique'); // 1 phieu / user / cuoc thi
        });
    }

    public function down(): void
    {
        Schema::table('votes', function (Blueprint $table) {
            $table->dropUnique('votes_user_contest_unique');
            $table->dropConstrainedForeignId('contest_id');
        });
    }
};
