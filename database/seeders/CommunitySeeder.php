<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CommunitySeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // ----- Groups -----
        DB::table('groups')->insert([
            ['id' => 1, 'owner_id' => 2, 'name' => 'Guitar & Sáng tác',                'description' => 'Nơi anh em guitar luyện tập, chia sẻ bản phối và sáng tác mới.', 'created_at' => $now->copy()->subDays(28), 'updated_at' => $now],
            ['id' => 2, 'owner_id' => 3, 'name' => 'Lập trình viên sáng tạo nội dung', 'description' => 'Dev làm video kiến thức: cùng nhau lên kịch bản, review và collab.', 'created_at' => $now->copy()->subDays(22), 'updated_at' => $now],
            ['id' => 3, 'owner_id' => 5, 'name' => 'Ống kính đường phố',               'description' => 'Cộng đồng nhiếp ảnh đường phố: photowalk, chấm ảnh, chia preset.', 'created_at' => $now->copy()->subDays(15), 'updated_at' => $now],
        ]);

        // ----- Group members (unique group_id + user_id) -----
        $members = [
            [1, 2], [1, 4], [1, 5], [1, 6],
            [2, 3], [2, 2], [2, 7],
            [3, 5], [3, 3], [3, 6],
        ];

        $rows = [];
        foreach ($members as [$groupId, $userId]) {
            $rows[] = [
                'group_id'   => $groupId,
                'user_id'    => $userId,
                'created_at' => $now->copy()->subDays(rand(3, 20)),
                'updated_at' => $now,
            ];
        }
        DB::table('group_members')->insert($rows);

        // ----- Group posts (bang thao luan) -----
        DB::table('group_posts')->insert([
            ['group_id' => 1, 'user_id' => 2, 'content' => 'Tuần này mọi người luyện fingerstyle bài gì? Mình đang tập Túy Âm, khoe sau nhé.', 'created_at' => $now->copy()->subDays(6), 'updated_at' => $now],
            ['group_id' => 1, 'user_id' => 6, 'content' => 'Thầy chia sẻ giáo trình hợp âm nâng cao cho cả nhóm, ai cần thì nhắn thầy gửi.',   'created_at' => $now->copy()->subDays(5), 'updated_at' => $now],
            ['group_id' => 2, 'user_id' => 3, 'content' => 'Ai muốn collab series "Laravel căn bản trong 10 video" thì comment bên dưới nha.', 'created_at' => $now->copy()->subDays(4), 'updated_at' => $now],
            ['group_id' => 3, 'user_id' => 5, 'content' => 'Cuối tuần photowalk quanh hồ Gươm, 6h sáng cho kịp ánh vàng. Ai tham gia?',        'created_at' => $now->copy()->subDays(2), 'updated_at' => $now],
        ]);

        // ----- Messages (FR6 - nhan tin voi mentor, read_at null = chua doc) -----
        DB::table('messages')->insert([
            ['sender_id' => 2, 'receiver_id' => 6, 'content' => 'Em chào thầy, thầy góp ý giúp em bản cover mới với ạ?',                              'read_at' => $now->copy()->subDays(3), 'created_at' => $now->copy()->subDays(3), 'updated_at' => $now],
            ['sender_id' => 6, 'receiver_id' => 2, 'content' => 'Gửi link video cho thầy nhé.',                                                       'read_at' => $now->copy()->subDays(3), 'created_at' => $now->copy()->subDays(3), 'updated_at' => $now],
            ['sender_id' => 2, 'receiver_id' => 6, 'content' => 'Dạ video Fingerstyle Túy Âm ạ.',                                                     'read_at' => $now->copy()->subDays(2), 'created_at' => $now->copy()->subDays(2), 'updated_at' => $now],
            ['sender_id' => 6, 'receiver_id' => 2, 'content' => 'Nhịp tốt, chú ý dynamics đoạn giữa. Tối thầy gửi nhận xét chi tiết nhé.',            'read_at' => null,                     'created_at' => $now->copy()->subDays(1), 'updated_at' => $now],
            ['sender_id' => 3, 'receiver_id' => 7, 'content' => 'Chị ơi cho em hỏi lộ trình để làm mentor mảng coding trên nền tảng với ạ?',          'read_at' => null,                     'created_at' => $now->copy()->subDays(1), 'updated_at' => $now],
        ]);
    }
}
