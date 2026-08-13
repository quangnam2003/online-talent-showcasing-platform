<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    // FR1: xem trang ca nhan
    public function show(User $user): View
    {
        $me = auth()->user();
        $ownProfile = $me && ($me->id === $user->id || $me->isAdmin());

        // Nguoi ngoai chi thay video da duyet + cong khai; chinh chu thay tat ca
        $videos = $user->videos()
            ->with('category')
            ->when(! $ownProfile, fn ($q) => $q->visible())
            ->latest()
            ->get();

        $stats = [
            ['n' => $videos->count(), 'k' => 'Video'],
            ['n' => number_format($user->followers_count), 'k' => 'Người theo dõi'],
            ['n' => number_format((float) $user->videos()->visible()->avg('avg_rating'), 1), 'k' => 'Điểm trung bình'],
            ['n' => $user->contestEntries()->count(), 'k' => 'Cuộc thi'],
        ];

        $isFollowing = $me ? $me->isFollowing($user) : false;

        return view('profile.show', compact('user', 'videos', 'stats', 'isFollowing', 'ownProfile'));
    }

    // FR1: sua ho so
    public function edit(): View
    {
        return view('profile.edit', ['user' => auth()->user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'location' => ['nullable', 'string', 'max:255'],
            'achievements' => ['nullable', 'string', 'max:2000'],
            'avatar' => ['nullable', 'image', 'max:4096'],
        ], [
            'name.required' => 'Vui lòng nhập họ tên.',
            'avatar.image' => 'Avatar phải là file ảnh.',
            'avatar.max' => 'Avatar tối đa 4MB.',
        ]);

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        } else {
            unset($data['avatar']);
        }

        $user->update($data);

        return redirect()->route('users.show', $user)
            ->with('success', 'Đã cập nhật hồ sơ.');
    }
}
