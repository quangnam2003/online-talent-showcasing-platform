<?php

namespace App\Http\Controllers;

use App\Models\Group;
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
}
