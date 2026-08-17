<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\User;
use App\Models\Video;
use App\Notifications\VideoSubmitted;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
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

    /**
     * FR2: nhan tiet muc (video hoac am thanh), luu o trang thai "cho duyet", bao cho admin.
     * Form gui bang XHR (co thanh tien trinh) → tra JSON; gui thuong → redirect kem flash.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'description' => ['nullable', 'string', 'max:5000'],
            'privacy' => ['required', 'in:public,private'],
            'video' => ['required', 'file', 'mimetypes:'.implode(',', Video::MEDIA_MIMES), 'max:102400'], // 100MB
            'thumbnail' => ['nullable', 'image', 'max:4096'],
            'duration' => ['nullable', 'integer', 'min:0', 'max:86400'],
        ], [
            'title.required' => 'Vui lòng nhập tiêu đề cho tiết mục.',
            'title.max' => 'Tiêu đề tối đa 255 ký tự.',
            'category_id.required' => 'Vui lòng chọn thể loại.',
            'category_id.exists' => 'Thể loại không hợp lệ.',
            'description.max' => 'Mô tả tối đa 5000 ký tự.',
            'video.required' => 'Bạn chưa chọn tệp video hoặc âm thanh.',
            'video.file' => 'Tệp không hợp lệ, vui lòng chọn lại.',
            'video.uploaded' => 'Tệp không tải lên được — thường do vượt quá 100 MB hoặc kết nối bị gián đoạn. Hãy thử lại với tệp nhỏ hơn.',
            'video.mimetypes' => 'Định dạng chưa hỗ trợ. Chấp nhận video MP4 / MOV / WEBM hoặc âm thanh MP3 / M4A / WAV / OGG / FLAC.',
            'video.max' => 'Tệp vượt quá giới hạn 100 MB.',
            'thumbnail.image' => 'Ảnh bìa phải là tệp hình ảnh (JPG, PNG, WEBP…).',
            'thumbnail.max' => 'Ảnh bìa tối đa 4 MB.',
        ]);

        $file = $request->file('video');

        $video = Video::create([
            'user_id' => auth()->id(),
            'category_id' => $data['category_id'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'privacy' => $data['privacy'],
            'allow_comments' => $request->boolean('allow_comments'),
            'status' => 'pending', // FR8: cho admin duyet
            'file_path' => $file->store('videos', 'public'),
            'mime_type' => $file->getMimeType(),
            'duration' => $data['duration'] ?? null,
            'thumbnail' => $request->hasFile('thumbnail')
                ? $request->file('thumbnail')->store('thumbnails', 'public')
                : null,
        ]);

        // Bao cho moi quan tri vien dang hoat dong: co muc moi trong hang doi kiem duyet
        $video->setRelation('user', auth()->user());
        Notification::send(User::where('role', 'admin')->where('is_active', true)->get(), new VideoSubmitted($video));

        $label = $video->isAudio() ? 'bản thu âm' : 'video';
        $message = 'Đã gửi '.$label.' "'.$video->title.'" tới quản trị viên để duyệt. Bạn sẽ nhận thông báo ngay khi có kết quả.';

        session()->flash('success', $message);

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => $message,
                'redirect' => route('videos.mine'),
                'video' => ['id' => $video->id, 'url' => route('videos.show', $video)],
            ]);
        }

        return redirect()->route('videos.mine');
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
        ], [
            'title.required' => 'Vui lòng nhập tiêu đề cho tiết mục.',
            'title.max' => 'Tiêu đề tối đa 255 ký tự.',
            'category_id.required' => 'Vui lòng chọn thể loại.',
            'category_id.exists' => 'Thể loại không hợp lệ.',
            'description.max' => 'Mô tả tối đa 5000 ký tự.',
            'thumbnail.image' => 'Ảnh bìa phải là tệp hình ảnh (JPG, PNG, WEBP…).',
            'thumbnail.max' => 'Ảnh bìa tối đa 4 MB.',
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

        // Soft delete khong keo theo cascadeOnDelete cua contest_entries — neu xoa video
        // dang du thi, entry mo coi se lam trang cuoc thi loi 500. Chan xoa khi video
        // con nam trong cuoc thi chua ket thuc.
        $inActiveContest = $video->contestEntries()
            ->whereHas('contest', fn ($q) => $q->where('end_at', '>', now()))
            ->exists();

        if ($inActiveContest) {
            return back()->with('error', 'Video "'.$video->title.'" đang là bài dự thi của một cuộc thi chưa kết thúc nên không thể xóa. Bạn có thể xóa sau khi cuộc thi kết thúc.');
        }

        $video->delete(); // soft delete

        return redirect()->route('videos.mine')->with('success', 'Đã xóa video "'.$video->title.'".');
    }

    private function authorizeOwner(Video $video): void
    {
        $me = auth()->user();
        abort_unless($me->id === $video->user_id || $me->isAdmin(), 403, 'Bạn không có quyền với video này.');
    }
}
