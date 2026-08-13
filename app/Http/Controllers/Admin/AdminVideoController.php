<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Video;
use App\Notifications\VideoApproved;
use App\Notifications\VideoRejected;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminVideoController extends Controller
{
    // FR8: hang doi kiem duyet — tab theo trang thai nhu mockup
    public function index(Request $request): View
    {
        $status = in_array($request->query('status'), ['pending', 'approved', 'rejected'], true)
            ? $request->query('status')
            : 'pending';

        return view('admin.videos', [
            'status' => $status,
            'videos' => Video::where('status', $status)
                ->with(['user', 'category'])
                ->oldest() // gui truoc duyet truoc
                ->paginate(10)
                ->withQueryString(),
            'counts' => Video::selectRaw('status, count(*) as n')->groupBy('status')->pluck('n', 'status'),
        ]);
    }

    public function approve(Video $video): RedirectResponse
    {
        $video->update(['status' => 'approved']);
        $video->user->notify(new VideoApproved($video));

        return back()->with('success', 'Đã duyệt "'.$video->title.'".');
    }

    // Tu choi kem ly do (panel co dinh ben phai — khong dung modal, dung mockup)
    public function reject(Request $request, Video $video): RedirectResponse
    {
        $reason = trim((string) $request->input('reason'));

        $video->update(['status' => 'rejected']);
        $video->user->notify(new VideoRejected($video, $reason ?: null));

        return back()->with('success', 'Đã từ chối "'.$video->title.'"'.($reason ? ' kèm lý do.' : '.'));
    }
}
