<?php

namespace App\Http\Controllers;

use App\Models\ContestEntry;
use App\Models\Vote;
use Illuminate\Http\RedirectResponse;

class VoteController extends Controller
{
    // FR7: binh chon — 1 phieu / user / cuoc thi, chi trong giai doan voting
    public function store(ContestEntry $entry): RedirectResponse
    {
        $me = auth()->user();
        $contest = $entry->contest;

        if (! $contest->isVotingOpen()) {
            return back()->with('error', 'Cuộc thi không trong giai đoạn bình chọn.');
        }

        if ($entry->user_id === $me->id) {
            return back()->with('error', 'Bạn không thể bình chọn cho bài của chính mình.');
        }

        $already = Vote::where('user_id', $me->id)
            ->whereHas('entry', fn ($q) => $q->where('contest_id', $contest->id))
            ->exists();
        if ($already) {
            return back()->with('error', 'Bạn đã dùng lượt bình chọn cho cuộc thi này.');
        }

        Vote::create(['user_id' => $me->id, 'entry_id' => $entry->id]);

        // Counter cache cho leaderboard (forceFill vi khong nam trong $fillable)
        $entry->forceFill(['votes_count' => $entry->votes()->count()])->save();

        return back()->with('success', 'Đã bình chọn cho "'.$entry->video->title.'".');
    }
}
