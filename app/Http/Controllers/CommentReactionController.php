<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\CommentReaction;
use App\Notifications\CommentReacted;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CommentReactionController extends Controller
{
    // FR4: bay to cam xuc voi binh luan — bam lai cung loai = bo, loai khac = doi
    public function store(Request $request, Comment $comment): RedirectResponse
    {
        $me = auth()->user();

        // Chi tuong tac duoc khi xem duoc video chua binh luan
        $video = $comment->video()->withTrashed()->first();
        abort_unless($video && ! $video->trashed() && $video->isViewableBy($me), 404);

        $data = $request->validate([
            'type' => ['required', Rule::in(array_keys(CommentReaction::TYPES))],
        ]);

        $existing = CommentReaction::where('comment_id', $comment->id)
            ->where('user_id', $me->id)
            ->first();

        if ($existing && $existing->type === $data['type']) {
            $existing->delete(); // bo cam xuc

            return back();
        }

        CommentReaction::updateOrCreate(
            ['comment_id' => $comment->id, 'user_id' => $me->id],
            ['type' => $data['type']]
        );

        // Bao cho nguoi viet binh luan (tru khi tu react binh luan cua minh)
        if ($comment->user_id !== $me->id) {
            $comment->user->notify(new CommentReacted($me, $comment, $data['type']));
        }

        return back();
    }
}
