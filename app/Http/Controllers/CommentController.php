<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Video;
use App\Notifications\VideoInteraction;
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
        $parent = null;
        if (! empty($data['parent_id'])) {
            $parent = Comment::find($data['parent_id']);
            if (! $parent || $parent->video_id !== $video->id || $parent->parent_id !== null) {
                return back()->with('error', 'Bình luận gốc không hợp lệ.');
            }
        }

        $comment = Comment::create([
            'user_id' => auth()->id(),
            'video_id' => $video->id,
            'parent_id' => $parent?->id,
            'content' => $data['content'],
        ]);

        $video->refreshCounters();

        $me = auth()->user();

        // Tra loi → bao cho nguoi viet binh luan goc (tru khi tu tra loi chinh minh)
        $repliedTo = ($parent && $parent->user_id !== $me->id) ? $parent->user_id : null;
        if ($repliedTo) {
            $parent->user->notify(new VideoInteraction($me, $video, 'reply', null, $comment));
        }

        // Bao cho chu video co binh luan moi (tru khi chinh chu tu binh luan,
        // hoac chu video chinh la nguoi vua duoc bao "tra loi" o tren — tranh bao doi)
        if ($video->user_id !== $me->id && $video->user_id !== $repliedTo) {
            $video->user->notify(new VideoInteraction($me, $video, 'comment', null, $comment));
        }

        return back()->with('success', 'Đã đăng bình luận.');
    }

    // Chi chinh chu binh luan moi duoc sua noi dung
    public function update(Request $request, Comment $comment): RedirectResponse
    {
        abort_unless(auth()->id() === $comment->user_id, 403, 'Bạn chỉ có thể sửa bình luận của chính mình.');

        $data = $request->validate([
            'content' => ['required', 'string', 'max:2000'],
        ], [
            'content.required' => 'Vui lòng nhập nội dung bình luận.',
        ]);

        $comment->update(['content' => $data['content']]);

        return back()->with('success', 'Đã cập nhật bình luận.');
    }

    public function destroy(Comment $comment): RedirectResponse
    {
        $me = auth()->user();

        // Video co the da bi soft-delete — lay ca ban ghi da xoa de khong bi null
        $video = $comment->video()->withTrashed()->first();

        abort_unless(
            $me->id === $comment->user_id || $me->isAdmin() || ($video && $me->id === $video->user_id),
            403
        );

        $comment->delete(); // cascade xoa ca replies (FK cascadeOnDelete)
        $video?->refreshCounters();

        return back()->with('success', 'Đã xóa bình luận.');
    }
}
