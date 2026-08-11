<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CategorySeeder::class,
            VideoSeeder::class,
            InteractionSeeder::class,
            CommunitySeeder::class,
            ContestSeeder::class,
        ]);

        // Dong bo cac cot counter cache theo du lieu vua seed
        $this->syncCounters();
    }

    private function syncCounters(): void
    {
        DB::statement('UPDATE videos v SET likes_count = (SELECT COUNT(*) FROM reactions r WHERE r.video_id = v.id AND r.liked = 1)');
        DB::statement('UPDATE videos v SET comments_count = (SELECT COUNT(*) FROM comments c WHERE c.video_id = v.id)');
        DB::statement('UPDATE videos v SET avg_rating = COALESCE((SELECT ROUND(AVG(r.stars), 2) FROM reactions r WHERE r.video_id = v.id AND r.stars IS NOT NULL), 0)');
        DB::statement('UPDATE users u SET followers_count = (SELECT COUNT(*) FROM follows f WHERE f.following_id = u.id)');
        DB::statement('UPDATE contest_entries e SET votes_count = (SELECT COUNT(*) FROM votes vt WHERE vt.entry_id = e.id)');

        // Trending seed ban dau: views*1 + likes*3 + comments*5
        // (Trong app that, scheduled command se tinh lai theo cua so 7 ngay)
        DB::statement('UPDATE videos v SET trending_score = (v.views * 1) + (v.likes_count * 3) + (v.comments_count * 5)');
    }
}
