<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Video;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    // FR4: binh luan + tra loi 1 cap
    public function store(Request $request, Video $video): RedirectResponse
    {
        abort_unless($video->isViewableBy(auth()->user()), 404);

        if (! $video->allow_comments) {
            return back()->with('error', 'Video này đã tắt bình luận.');
        }

        $data = $request->validate([
            'content' => ['required', 'string', 'max:2000'],
            'parent_id' => ['nullable', 'exists:comments,id'],
        ], [
            'content.required' => 'Vui lòng nhập nội dung bình luận.',
        ]);

        // Tra loi chi 1 cap: parent phai la binh luan goc cua chinh video nay
        if (! empty($data['parent_id'])) {
            $parent = Comment::find($data['parent_id']);
            if (! $parent || $parent->video_id !== $video->id || $parent->parent_id !== null) {
                return back()->with('error', 'Bình luận gốc không hợp lệ.');
            }
        }

        Comment::create([
            'user_id' => auth()->id(),
            'video_id' => $video->id,
            'parent_id' => $data['parent_id'] ?? null,
            'content' => $data['content'],
        ]);

        $video->refreshCounters();

        return back()->with('success', 'Đã đăng bình luận.');
    }

    public function destroy(Comment $comment): RedirectResponse
    {
        $me = auth()->user();
        abort_unless(
            $me->id === $comment->user_id || $me->isAdmin() || $me->id === $comment->video->user_id,
            403
        );

        $video = $comment->video;
        $comment->delete(); // cascade xoa ca replies (FK cascadeOnDelete)
        $video->refreshCounters();

        return back()->with('success', 'Đã xóa bình luận.');
    }
}
