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
