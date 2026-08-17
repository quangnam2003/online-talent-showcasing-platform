<?php

namespace App\Http\Controllers;

use App\Models\Reaction;
use App\Models\Video;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReactionController extends Controller
{
    // FR4: like (toggle) + cham sao — moi cap (user, video) 1 dong reaction
    public function store(Request $request, Video $video): RedirectResponse
    {
        abort_unless($video->isViewableBy(auth()->user()), 404);

        // Chu video khong tu like / tu cham sao — tranh tu keo avg_rating, likes_count,
        // trending_score lam lech muc "Đánh giá cao" / "Thịnh hành" o Explore
        if (auth()->id() === $video->user_id) {
            return back()->with('error', 'Bạn không thể tự thích hay chấm điểm video của chính mình.');
        }

        $data = $request->validate([
            'action' => ['required', 'in:like,rate'],
            'stars' => ['required_if:action,rate', 'nullable', 'integer', 'between:1,5'],
        ]);

        $reaction = Reaction::firstOrNew([
            'user_id' => auth()->id(),
            'video_id' => $video->id,
        ]);

        if ($data['action'] === 'like') {
            $reaction->liked = ! $reaction->liked;
        } else {
            $reaction->stars = (int) $data['stars'];
        }

        $reaction->save();
        $video->refreshCounters();

        return back();
    }
}
