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

        // Mac dinh: cho duyet -> gui truoc duyet truoc; da duyet/tu choi -> moi nhat len dau
        $sort = in_array($request->query('sort'), ['newest', 'oldest'], true)
            ? $request->query('sort')
            : ($status === 'pending' ? 'oldest' : 'newest');
        // dung tham so `title` (khong dung `q`) de khong dung o tim kiem chung tren header
        $q = trim((string) $request->query('title'));
        $dir = $sort === 'newest' ? 'desc' : 'asc';

        return view('admin.videos', [
            'status' => $status,
            'sort' => $sort,
            'q' => $q,
            'videos' => Video::where('status', $status)
                ->with(['user', 'category'])
                ->when($q !== '', fn ($query) => $query->where('title', 'like', '%'.$q.'%'))
                ->orderBy('created_at', $dir)
                ->orderBy('id', $dir)
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
