<?php

namespace App\Http\Controllers;

use App\Models\Contest;
use App\Models\ContestEntry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ContestEntryController extends Controller
{
    // FR7: nop bai du thi — 1 bai / creator / cuoc thi, video phai da duyet
    public function store(Request $request, Contest $contest): RedirectResponse
    {
        $me = auth()->user();

        abort_unless($me->isCreator(), 403, 'Chỉ creator mới được nộp bài dự thi.');

        if (! $contest->isAcceptingSubmissions()) {
            return back()->with('error', 'Cuộc thi không trong giai đoạn nhận bài.');
        }

        if ($contest->entries()->where('user_id', $me->id)->exists()) {
            return back()->with('error', 'Bạn đã nộp bài cho cuộc thi này rồi.');
        }

        $data = $request->validate(['video_id' => ['required', 'exists:videos,id']]);

        $video = $me->videos()->visible()->find($data['video_id']);
        if (! $video) {
            return back()->with('error', 'Video phải là của bạn, đã được duyệt và ở chế độ công khai.');
        }

        if ($contest->entries()->where('video_id', $video->id)->exists()) {
            return back()->with('error', 'Video này đã được nộp cho cuộc thi.');
        }

        ContestEntry::create([
            'contest_id' => $contest->id,
            'video_id' => $video->id,
            'user_id' => $me->id,
        ]);

        return back()->with('success', 'Đã nộp "'.$video->title.'" dự thi "'.$contest->title.'".');
    }
}
