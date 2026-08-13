<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\NewFollower;
use Illuminate\Http\RedirectResponse;

class FollowController extends Controller
{
    // FR4: theo doi / bo theo doi
    public function toggle(User $user): RedirectResponse
    {
        $me = auth()->user();

        if ($me->id === $user->id) {
            return back()->with('error', 'Bạn không thể tự theo dõi chính mình.');
        }

        if ($me->isFollowing($user)) {
            $me->following()->detach($user->id);
            $message = 'Đã bỏ theo dõi '.$user->name.'.';
        } else {
            $me->following()->attach($user->id);
            $user->notify(new NewFollower($me));
            $message = 'Đang theo dõi '.$user->name.'.';
        }

        // Counter cache followers_count (forceFill vi khong nam trong $fillable)
        $user->forceFill(['followers_count' => $user->followers()->count()])->save();

        return back()->with('success', $message);
    }
}
