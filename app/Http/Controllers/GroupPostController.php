<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\GroupPost;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class GroupPostController extends Controller
{
    // FR5: dang bai trong bang thao luan — chi thanh vien
    public function store(Request $request, Group $group): RedirectResponse
    {
        abort_unless($group->hasMember(auth()->user()), 403, 'Chỉ thành viên mới được đăng bài trong nhóm.');

        $data = $request->validate([
            'content' => ['required', 'string', 'max:2000'],
        ], [
            'content.required' => 'Vui lòng nhập nội dung bài đăng.',
        ]);

        $group->posts()->create([
            'user_id' => auth()->id(),
            'content' => $data['content'],
        ]);

        return redirect()->route('groups.show', $group)->with('success', 'Đã đăng bài trong nhóm.');
    }

    // FR5: xoa bai dang — tac gia, chu nhom, hoac admin (don spam trong nhom)
    public function destroy(Group $group, GroupPost $post): RedirectResponse
    {
        abort_unless($post->group_id === $group->id, 404);

        $me = auth()->user();
        $canDelete = $me->id === $post->user_id      // tac gia bai dang
            || $me->id === $group->owner_id          // chu nhom don bang thao luan cua minh
            || $me->isAdmin();                       // admin kiem duyet

        abort_unless($canDelete, 403, 'Bạn không có quyền xóa bài đăng này.');

        $post->delete();

        return back()->with('success', 'Đã xóa bài đăng.');
    }
}
