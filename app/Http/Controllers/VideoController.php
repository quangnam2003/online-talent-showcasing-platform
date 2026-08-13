<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Video;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class VideoController extends Controller
{
    // FR2: form upload (kem bang trang thai duyet nhu mockup)
    public function create(): View
    {
        return view('videos.create', [
            'categories' => Category::orderBy('name')->get(),
            'myUploads' => auth()->user()->videos()->latest()->take(5)->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'description' => ['nullable', 'string', 'max:5000'],
            'privacy' => ['required', 'in:public,private'],
            'video' => ['required', 'file', 'mimetypes:video/mp4,video/quicktime,video/webm', 'max:102400'], // 100MB
            'thumbnail' => ['nullable', 'image', 'max:4096'],
        ], [
            'title.required' => 'Vui lòng nhập tiêu đề.',
            'category_id.required' => 'Vui lòng chọn thể loại.',
            'video.required' => 'Vui lòng chọn file video.',
            'video.mimetypes' => 'Định dạng phải là mp4 / mov / webm.',
            'video.max' => 'Video tối đa 100MB.',
            'thumbnail.max' => 'Thumbnail tối đa 4MB.',
        ]);

        $video = Video::create([
            'user_id' => auth()->id(),
            'category_id' => $data['category_id'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'privacy' => $data['privacy'],
            'allow_comments' => $request->boolean('allow_comments'),
            'status' => 'pending', // FR8: cho admin duyet
            'file_path' => $request->file('video')->store('videos', 'public'),
            'thumbnail' => $request->hasFile('thumbnail')
                ? $request->file('thumbnail')->store('thumbnails', 'public')
                : null,
        ]);

        return redirect()->route('videos.mine')
            ->with('success', 'Đã gửi video "'.$video->title.'" — chờ admin duyệt.');
    }

    // FR2 + FR4: trang xem video
    public function show(Video $video): View
    {
        $me = auth()->user();

        abort_unless($video->isViewableBy($me), 404);

        // Khong tinh luot xem cua chinh chu
        if (! $me || $me->id !== $video->user_id) {
            $video->increment('views');
            $video->refreshCounters();
        }

        $video->load(['user', 'category']);

        $comments = $video->rootComments()
            ->with(['user', 'replies.user'])
            ->latest()
            ->get();

        // Nhan xet noi bat tu mentor (mockup: card "Nhận xét từ mentor")
        $mentorComment = $video->comments()
            ->whereHas('user', fn ($q) => $q->where('role', 'mentor'))
            ->with('user')
            ->latest()
            ->first();

        $myReaction = $me
            ? $video->reactions()->where('user_id', $me->id)->first()
            : null;

        $isFollowing = $me ? $me->isFollowing($video->user) : false;

        $upNext = Video::visible()
            ->with('user')
            ->whereKeyNot($video->id)
            ->orderByDesc('trending_score')
            ->take(5)
            ->get();

        return view('videos.show', compact(
            'video', 'comments', 'mentorComment', 'myReaction', 'isFollowing', 'upNext'
        ));
    }

    // FR2: danh sach video cua toi + trang thai duyet
    public function mine(): View
    {
        return view('videos.mine', [
            'videos' => auth()->user()->videos()->with('category')->latest()->get(),
        ]);
    }

    public function edit(Video $video): View
    {
        $this->authorizeOwner($video);

        return view('videos.edit', [
            'video' => $video,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Video $video): RedirectResponse
    {
        $this->authorizeOwner($video);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'description' => ['nullable', 'string', 'max:5000'],
            'privacy' => ['required', 'in:public,private'],
            'thumbnail' => ['nullable', 'image', 'max:4096'],
        ]);

        if ($request->hasFile('thumbnail')) {
            if ($video->thumbnail) {
                Storage::disk('public')->delete($video->thumbnail);
            }
            $data['thumbnail'] = $request->file('thumbnail')->store('thumbnails', 'public');
        } else {
            unset($data['thumbnail']);
        }

        $data['allow_comments'] = $request->boolean('allow_comments');

        $video->update($data);

        return redirect()->route('videos.mine')->with('success', 'Đã cập nhật video.');
    }

    public function destroy(Video $video): RedirectResponse
    {
        $this->authorizeOwner($video);

        $video->delete(); // soft delete

        return redirect()->route('videos.mine')->with('success', 'Đã xóa video "'.$video->title.'".');
    }

    private function authorizeOwner(Video $video): void
    {
        $me = auth()->user();
        abort_unless($me->id === $video->user_id || $me->isAdmin(), 403, 'Bạn không có quyền với video này.');
    }
}
