<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InteractionSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // ----- Reactions: like + cham sao, moi cap (user, video) 1 dong -----
        $reactions = [
            // [user_id, video_id, liked, stars]
            [3, 1, true,  5], [4, 1, true, 4], [5, 1, true, 5], [6, 1, true, 5], [7, 1, false, 4],
            [2, 3, true,  4], [4, 3, true, 5], [6, 3, false, null],
            [3, 5, true,  4], [5, 5, true, null], [2, 5, false, 3], [7, 5, true, 4],
            [2, 6, true,  5], [3, 6, true, 4],
            [2, 7, true,  4], [5, 7, true, 5], [6, 7, true, null],
            [3, 8, true,  5],
            [4, 9, true,  4], [3, 9, true, 5],
        ];

        $rows = [];
        foreach ($reactions as [$userId, $videoId, $liked, $stars]) {
            $rows[] = [
                'user_id'    => $userId,
                'video_id'   => $videoId,
                'liked'      => $liked,
                'stars'      => $stars,
                'created_at' => $now->copy()->subDays(rand(1, 14)),
                'updated_at' => $now,
            ];
        }
        DB::table('reactions')->insert($rows);

        // ----- Comments: co tra loi 1 cap qua parent_id -----
        DB::table('comments')->insert([
            ['id' => 1,  'user_id' => 3, 'video_id' => 1, 'parent_id' => null, 'content' => 'Tone giọng ấm quá, phần intro xử lý mượt!',                'created_at' => $now->copy()->subDays(19), 'updated_at' => $now],
            ['id' => 2,  'user_id' => 4, 'video_id' => 1, 'parent_id' => null, 'content' => 'Đoạn điệp khúc hơi nhanh, thử chậm lại 5% xem sao?',        'created_at' => $now->copy()->subDays(18), 'updated_at' => $now],
            ['id' => 3,  'user_id' => 2, 'video_id' => 1, 'parent_id' => 2,    'content' => 'Cảm ơn góp ý, mình sẽ thu lại bản mới!',                    'created_at' => $now->copy()->subDays(18), 'updated_at' => $now],
            ['id' => 4,  'user_id' => 6, 'video_id' => 1, 'parent_id' => null, 'content' => 'Kỹ thuật tốt. Chú ý nhấn nhịp ở phách 2 nhé.',              'created_at' => $now->copy()->subDays(17), 'updated_at' => $now],
            ['id' => 5,  'user_id' => 2, 'video_id' => 3, 'parent_id' => null, 'content' => 'Phần route model binding giải thích dễ hiểu lắm.',          'created_at' => $now->copy()->subDays(14), 'updated_at' => $now],
            ['id' => 6,  'user_id' => 4, 'video_id' => 3, 'parent_id' => null, 'content' => 'Video sau làm về queue được không bạn?',                    'created_at' => $now->copy()->subDays(13), 'updated_at' => $now],
            ['id' => 7,  'user_id' => 3, 'video_id' => 3, 'parent_id' => 6,    'content' => 'Ok mình note lại chủ đề queue nhé.',                        'created_at' => $now->copy()->subDays(13), 'updated_at' => $now],
            ['id' => 8,  'user_id' => 5, 'video_id' => 7, 'parent_id' => null, 'content' => 'Cười xỉu đoạn phỏng vấn =))',                               'created_at' => $now->copy()->subDays(16), 'updated_at' => $now],
            ['id' => 9,  'user_id' => 3, 'video_id' => 6, 'parent_id' => null, 'content' => 'Màu film đẹp, bạn chỉnh bằng app gì?',                      'created_at' => $now->copy()->subDays(9),  'updated_at' => $now],
            ['id' => 10, 'user_id' => 5, 'video_id' => 6, 'parent_id' => 9,    'content' => 'Mình dùng Lightroom, preset tự tạo.',                       'created_at' => $now->copy()->subDays(9),  'updated_at' => $now],
            ['id' => 11, 'user_id' => 6, 'video_id' => 5, 'parent_id' => null, 'content' => 'Chuyển động sạch. Thử thêm biến tấu ở 8 nhịp cuối.',        'created_at' => $now->copy()->subDays(11), 'updated_at' => $now],
            ['id' => 12, 'user_id' => 7, 'video_id' => 8, 'parent_id' => null, 'content' => 'Tỉ lệ khuôn mặt chuẩn, thích cách đánh bóng.',              'created_at' => $now->copy()->subDays(2),  'updated_at' => $now],
        ]);

        // ----- Follows: quan he theo doi (tu tham chieu users) -----
        $follows = [
            // [follower_id, following_id]
            [3, 2], [4, 2], [5, 2], [6, 2], [7, 2],
            [2, 3], [4, 3],
            [2, 6], [3, 6], [4, 6],
            [5, 7],
            [2, 5],
        ];

        $rows = [];
        foreach ($follows as [$followerId, $followingId]) {
            $rows[] = [
                'follower_id'  => $followerId,
                'following_id' => $followingId,
                'created_at'   => $now->copy()->subDays(rand(5, 30)),
                'updated_at'   => $now,
            ];
        }
        DB::table('follows')->insert($rows);
    }
}
