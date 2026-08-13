<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Video;
use Illuminate\View\View;

class FeedController extends Controller
{
    // FR4: bang tin tu nhung nguoi minh theo doi
    public function index(): View
    {
        $me = auth()->user();
        $followingIds = $me->following()->pluck('users.id');

        $videos = Video::visible()
            ->with(['user', 'category'])
            ->whereIn('user_id', $followingIds)
            ->latest()
            ->paginate(8);

        // Goi y theo doi: creator dong nguoi theo doi nhat ma minh chua follow
        $suggested = User::where('role', 'creator')
            ->whereKeyNot($me->id)
            ->whereNotIn('id', $followingIds)
            ->orderByDesc('followers_count')
            ->take(4)
            ->get();

        return view('feed.index', compact('videos', 'suggested', 'followingIds'));
    }
}
