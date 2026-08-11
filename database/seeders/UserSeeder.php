<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::table('users')->insert([
            [
                'id' => 1,
                'name' => 'Quản trị viên',
                'email' => 'admin@talentstage.test',
                'password' => Hash::make('Admin@123'),
                'role' => 'admin',
                'bio' => 'Tài khoản quản trị hệ thống TalentStage.',
                'location' => 'Hà Nội',
                'is_active' => true,
                'created_at' => $now->copy()->subDays(60),
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'name' => 'Nam Nguyễn',
                'email' => 'creator1@talentstage.test',
                'password' => Hash::make('Creator@123'),
                'role' => 'creator',
                'bio' => 'Guitarist tự học, thích cover nhạc phim và fingerstyle.',
                'location' => 'Hà Nội',
                'achievements' => 'Giải Nhì cuộc thi Acoustic sinh viên 2025',
                'is_active' => true,
                'created_at' => $now->copy()->subDays(45),
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'name' => 'Linh Trần',
                'email' => 'creator2@talentstage.test',
                'password' => Hash::make('Creator@123'),
                'role' => 'creator',
                'bio' => 'Backend developer, làm video chia sẻ kiến thức Laravel.',
                'location' => 'TP. Hồ Chí Minh',
                'achievements' => 'Chứng chỉ AWS Cloud Practitioner',
                'is_active' => true,
                'created_at' => $now->copy()->subDays(40),
                'updated_at' => $now,
            ],
            [
                'id' => 4,
                'name' => 'Huy Phạm',
                'email' => 'creator3@talentstage.test',
                'password' => Hash::make('Creator@123'),
                'role' => 'creator',
                'bio' => 'Dancer kiêm cây hài của nhóm, thích thử thách vũ đạo mới.',
                'location' => 'Đà Nẵng',
                'achievements' => null,
                'is_active' => true,
                'created_at' => $now->copy()->subDays(35),
                'updated_at' => $now,
            ],
            [
                'id' => 5,
                'name' => 'Mai Lê',
                'email' => 'creator4@talentstage.test',
                'password' => Hash::make('Creator@123'),
                'role' => 'creator',
                'bio' => 'Nhiếp ảnh đường phố và vẽ chì. Kể chuyện bằng hình ảnh.',
                'location' => 'Hà Nội',
                'achievements' => 'Triển lãm ảnh nhóm "Phố" 2024',
                'is_active' => true,
                'created_at' => $now->copy()->subDays(30),
                'updated_at' => $now,
            ],
            [
                'id' => 6,
                'name' => 'Sơn Đặng',
                'email' => 'mentor1@talentstage.test',
                'password' => Hash::make('Mentor@123'),
                'role' => 'mentor',
                'bio' => 'Giảng viên guitar 10 năm kinh nghiệm, cố vấn âm nhạc.',
                'location' => 'Hà Nội',
                'achievements' => 'Sáng lập lớp nhạc SolFa, giám khảo nhiều cuộc thi sinh viên',
                'is_active' => true,
                'created_at' => $now->copy()->subDays(50),
                'updated_at' => $now,
            ],
            [
                'id' => 7,
                'name' => 'Hạnh Vũ',
                'email' => 'mentor2@talentstage.test',
                'password' => Hash::make('Mentor@123'),
                'role' => 'mentor',
                'bio' => 'Engineering manager, mentor định hướng nghề cho dev trẻ.',
                'location' => 'TP. Hồ Chí Minh',
                'achievements' => '10+ năm phát triển phần mềm, speaker tại Vietnam Web Summit',
                'is_active' => true,
                'created_at' => $now->copy()->subDays(48),
                'updated_at' => $now,
            ],
        ]);
    }
}
