<?php

namespace App\Http\Controllers;

use App\Models\Contest;
use App\Models\Vote;
use Illuminate\View\View;

class ContestController extends Controller
{
    // FR7: danh sach cuoc thi phan nhom theo trang thai (may trang thai 3 moc)
    public function index(): View
    {
        $contests = Contest::withCount('entries')->orderByDesc('start_at')->get();

        $groups = [
            'submission' => ['Đang nhận bài', $contests->where('status', 'submission')],
            'voting' => ['Đang bình chọn', $contests->where('status', 'voting')],
            'upcoming' => ['Sắp diễn ra', $contests->where('status', 'upcoming')],
            'ended' => ['Đã kết thúc', $contests->where('status', 'ended')],
        ];

        return view('contests.index', compact('groups'));
    }

    // FR7: chi tiet — phase strip, nop bai, binh chon, leaderboard
    public function show(Contest $contest): View
    {
        $me = auth()->user();

        // whereHas('video') loai entry ma video da bi soft-delete (du lieu cu truoc khi
        // chan xoa video dang du thi) — tranh $entry->video null lam view loi 500
        $entries = $contest->entries()
            ->whereHas('video')
            // an bai cua tai khoan bi khoa khoi luoi + bang xep hang
            ->whereHas('user', fn ($u) => $u->where('is_active', true))
            ->with(['video.category', 'user'])
            ->orderByDesc('votes_count')
            ->orderBy('created_at')
            ->get();

        // Da ket thuc: xac dinh quan quan (hoa phieu → dong hang; 0 phieu → khong co)
        $winnerIds = $contest->status === 'ended' ? $contest->winners()->pluck('id') : collect();

        $myEntry = $me ? $entries->firstWhere('user_id', $me->id) : null;

        // Video du dieu kien nop: cua toi, da duyet + cong khai, chua nop cuoc thi nay
        $eligibleVideos = ($me?->isCreator() && $contest->isAcceptingSubmissions() && ! $myEntry)
            ? $me->videos()->visible()
                ->whereNotIn('id', $contest->entries()->pluck('video_id'))
                ->latest()->get()
            : collect();

        // 1 phieu / user / cuoc thi
        $myVote = $me
            ? Vote::where('user_id', $me->id)->where('contest_id', $contest->id)->first()
            : null;

        return view('contests.show', [
            'contest' => $contest,
            'entries' => $entries,
            'leaderboard' => $entries->take(5),
            'myEntry' => $myEntry,
            'eligibleVideos' => $eligibleVideos,
            'votedEntryId' => $myVote?->entry_id,
            'winnerIds' => $winnerIds,
        ]);
    }
}
