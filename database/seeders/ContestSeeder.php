<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContestSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // ----- Contests: du 3 trang thai de demo — da ket thuc / dang binh chon / dang nhan bai.
        //       Phieu bau CHI thuoc cuoc thi da qua han nop (dang binh chon tro di) — cuoc thi
        //       dang nhan bai (id 3) khong co entry/phieu, tranh mau thuan "nhan bai ma da co phieu".
        DB::table('contests')->insert([
            [
                'id' => 1,
                'title' => 'Tài năng mùa hè TalentStage 2026',
                'description' => 'Sân chơi mở cho mọi thể loại tài năng. Bình chọn công khai từ cộng đồng.',
                'start_at'            => $now->copy()->subDays(40),
                'submission_deadline' => $now->copy()->subDays(25),
                'end_at'              => $now->copy()->subDays(10),
                'announced_at'        => $now->copy()->subDays(10), // da cong bo — scheduler khong gui lai thong bao ket qua sau khi fresh-seed
                'created_at' => $now->copy()->subDays(45),
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'title' => 'Sáng tạo không giới hạn mùa thu 2026',
                'description' => 'Nộp video sáng tạo nhất của bạn. Đã đóng nhận bài — đang trong giai đoạn bình chọn.',
                'start_at'            => $now->copy()->subDays(10),
                'submission_deadline' => $now->copy()->subDays(2),
                'end_at'              => $now->copy()->addDays(5),
                'created_at' => $now->copy()->subDays(12),
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'title' => 'Giọng hát vàng mùa đông 2026',
                'description' => 'Dành riêng cho các tiết mục âm nhạc. Đang mở nhận bài dự thi — nộp video đã duyệt của bạn ngay!',
                'start_at'            => $now->copy()->subDay(),
                'submission_deadline' => $now->copy()->addDays(6),
                'end_at'              => $now->copy()->addDays(13),
                'created_at' => $now->copy()->subDays(2),
                'updated_at' => $now,
            ],
        ]);

        // ----- Entries: chi video approved; unique (contest, video) va (contest, user) -----
        DB::table('contest_entries')->insert([
            ['id' => 1, 'contest_id' => 1, 'video_id' => 1, 'user_id' => 2, 'created_at' => $now->copy()->subDays(38), 'updated_at' => $now],
            ['id' => 2, 'contest_id' => 1, 'video_id' => 5, 'user_id' => 4, 'created_at' => $now->copy()->subDays(36), 'updated_at' => $now],
            ['id' => 3, 'contest_id' => 1, 'video_id' => 6, 'user_id' => 5, 'created_at' => $now->copy()->subDays(30), 'updated_at' => $now],
            ['id' => 4, 'contest_id' => 2, 'video_id' => 2, 'user_id' => 2, 'created_at' => $now->copy()->subDays(4),  'updated_at' => $now],
            ['id' => 5, 'contest_id' => 2, 'video_id' => 8, 'user_id' => 5, 'created_at' => $now->copy()->subDays(3),  'updated_at' => $now],
        ]);

        // ----- Votes: 1 phieu / user / cuoc thi (unique user_id+contest_id). Contest 1: entry 1 thang voi 4 phieu -----
        $entryContest = [1 => 1, 2 => 1, 3 => 1, 4 => 2, 5 => 2];
        $votes = [
            // [user_id, entry_id] — moi user chi xuat hien 1 lan trong moi cuoc thi
            [3, 1], [4, 1], [5, 1], [7, 1],
            [2, 2], [6, 2],
            [3, 4], [6, 4],
            [4, 5],
        ];

        $rows = [];
        foreach ($votes as [$userId, $entryId]) {
            $contestId = $entryContest[$entryId];
            $rows[] = [
                'user_id'    => $userId,
                'entry_id'   => $entryId,
                'contest_id' => $contestId,
                // phieu nam TRONG cua so binh chon cua tung cuoc thi:
                // contest 1: 25→10 ngay truoc; contest 2: tu 2 ngay truoc toi nay
                'created_at' => $contestId === 1
                    ? $now->copy()->subDays(rand(11, 24))
                    : $now->copy()->subHours(rand(1, 40)),
                'updated_at' => $now,
            ];
        }
        DB::table('votes')->insert($rows);
    }
}
