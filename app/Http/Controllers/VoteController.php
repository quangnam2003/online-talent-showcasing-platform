<?php

namespace App\Http\Controllers;

use App\Models\ContestEntry;
use App\Models\Vote;
use App\Notifications\NewVote;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;

class VoteController extends Controller
{
    // FR7: binh chon — 1 phieu / user / cuoc thi, chi trong giai doan voting
    public function store(ContestEntry $entry): RedirectResponse
    {
        $me = auth()->user();
        $contest = $entry->contest;

        // Ban to chuc dung ngoai cuoc binh chon
        if ($me->isAdmin()) {
            return back()->with('error', 'Ban tổ chức không tham gia bình chọn.');
        }

        // Chu bai bi khoa tai khoan → bai khong nhan phieu nua
        if (! $entry->user || ! $entry->user->is_active) {
            return back()->with('error', 'Bài dự thi này không còn hợp lệ (tài khoản chủ bài đã bị khóa).');
        }

        if (! $contest->isVotingOpen()) {
            return back()->with('error', 'Cuộc thi không trong giai đoạn bình chọn.');
        }

        // A2: bai thi phai con xem duoc cong khai — video ve "cho duyet" / bi tu choi /
        // chuyen rieng tu thi khong nhan phieu nua (nguoi vote bam vao se gap 404)
        if (! $entry->video || ! $entry->video->isViewableBy(null)) {
            return back()->with('error', 'Bài dự thi này đang tạm ẩn (video chờ duyệt lại hoặc không còn công khai) nên không thể bình chọn.');
        }

        if ($entry->user_id === $me->id) {
            return back()->with('error', 'Bạn không thể bình chọn cho bài của chính mình.');
        }

        $already = Vote::where('user_id', $me->id)->where('contest_id', $contest->id)->exists();
        if ($already) {
            return back()->with('error', 'Bạn đã dùng lượt bình chọn cho cuộc thi này.');
        }

        // Unique (user_id, contest_id) o DB la chot chan cuoi: 2 request song song (da tien trinh)
        // cung qua duoc exists() o tren thi chi 1 ban ghi duoc tao, ban con lai roi vao catch.
        try {
            Vote::create(['user_id' => $me->id, 'entry_id' => $entry->id, 'contest_id' => $contest->id]);
        } catch (UniqueConstraintViolationException) {
            return back()->with('error', 'Bạn đã dùng lượt bình chọn cho cuộc thi này.');
        }

        // Counter cache cho leaderboard (forceFill vi khong nam trong $fillable)
        $entry->forceFill(['votes_count' => $entry->votes()->count()])->save();

        // Bao cho chu bai du thi (nguoi vote khong the la chinh chu — da chan o tren)
        $entry->user->notify(new NewVote($me, $entry));

        return back()->with('success', 'Đã bình chọn cho "'.$entry->video->title.'".');
    }
}
