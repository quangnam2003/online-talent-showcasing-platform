<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VideoSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $videos = [
            // [id, user_id, category_id, title, status, privacy, views, days_ago]
            [1,  2, 1, 'Cover guitar: Nhạc phim Mắt Biếc',              'approved', 'public',  850, 20],
            [2,  2, 1, 'Sáng tác mới: Hà Nội ngày trở gió',             'approved', 'public',  430, 6],
            [3,  3, 4, 'Xây REST API Laravel trong 30 phút',            'approved', 'public',  620, 15],
            [4,  3, 4, 'Mẹo debug PHP nhanh gấp đôi',                   'approved', 'public',  210, 4],
            [5,  4, 2, 'Vũ đạo hiện đại trên nền nhạc EDM',             'approved', 'public',  540, 12],
            [6,  5, 6, 'Bộ ảnh đường phố Hà Nội về đêm',                'approved', 'public',  380, 10],
            [7,  4, 5, 'Tấu hài: Chuyện sinh viên IT đi thực tập',      'approved', 'public',  720, 18],
            [8,  5, 3, 'Vẽ chân dung bằng chì trong 10 phút',           'approved', 'public',  150, 3],
            [9,  2, 1, 'Fingerstyle: Túy Âm',                           'approved', 'public',   95, 2],
            [10, 4, 2, 'Bản nháp vũ đạo (riêng tư)',                    'approved', 'private',  12, 5],
            [11, 3, 4, 'Clean code với Laravel Service Pattern',        'pending',  'public',    0, 1],
            [12, 5, 6, 'Ảnh chụp concert (nhạc nền vi phạm bản quyền)', 'rejected', 'public',    0, 7],
        ];

        $rows = [];
        foreach ($videos as [$id, $userId, $categoryId, $title, $status, $privacy, $views, $daysAgo]) {
            $rows[] = [
                'id'             => $id,
                'user_id'        => $userId,
                'category_id'    => $categoryId,
                'title'          => $title,
                'description'    => 'Video demo phục vụ dữ liệu mẫu của TalentStage.',
                'file_path'      => sprintf('videos/demo-%02d.mp4', $id),
                'thumbnail'      => sprintf('thumbnails/demo-%02d.jpg', $id),
                'privacy'        => $privacy,
                'allow_comments' => true,
                'status'         => $status,
                'views'          => $views,
                'created_at'     => $now->copy()->subDays($daysAgo),
                'updated_at'     => $now->copy()->subDays($daysAgo),
            ];
        }

        DB::table('videos')->insert($rows);
    }
}
