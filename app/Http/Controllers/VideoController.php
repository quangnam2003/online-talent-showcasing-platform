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
            ->with(['user', 'reactions', 'replies.user', 'replies.reactions'])
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

        // Tong so luot cham sao — hien canh diem trung binh "x,x / 5"
        $ratingsCount = $video->reactions()->whereNotNull('stars')->count();

        $isFollowing = $me ? $me->isFollowing($video->user) : false;

        $upNext = Video::visible()
            ->with(['user', 'category'])
            ->whereKeyNot($video->id)
            ->orderByDesc('trending_score')
            ->take(5)
            ->get();

        return view('videos.show', compact(
            'video', 'comments', 'mentorComment', 'myReaction', 'isFollowing', 'upNext', 'ratingsCount'
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
            'inActiveContest' => $this->inActiveContest($video),
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

        // A2/FR7: video dang du thi (cuoc thi chua ket thuc) — khoa nhung thay doi
        // lam bai thi bien mat khoi nguoi binh chon:
        //  - chuyen sang rieng tu → nguoi vote bam vao bai thi se gap 404
        //  - sua noi dung hien thi → video ve "cho duyet" va cung bi an
        if ($this->inActiveContest($video)) {
            if ($data['privacy'] === 'private' && $video->privacy !== 'private') {
                return back()->withInput()->with('error',
                    'Video "'.$video->title.'" đang là bài dự thi của một cuộc thi chưa kết thúc nên không thể chuyển sang riêng tư — người bình chọn sẽ không xem được bài thi. Bạn có thể đổi sau khi cuộc thi kết thúc.');
            }

            $contentChanged = ! auth()->user()->isAdmin()
                && $video->status === 'approved'
                && ($data['title'] !== $video->title
                    || (string) ($data['description'] ?? '') !== (string) $video->description
                    || (int) $data['category_id'] !== (int) $video->category_id
                    || $request->hasFile('thumbnail')
                    || $request->boolean('remove_thumbnail'));

            if ($contentChanged) {
                return back()->withInput()->with('error',
                    'Video "'.$video->title.'" đang dự thi nên không thể sửa nội dung hiển thị (tiêu đề, mô tả, thể loại, ảnh bìa) — nội dung sửa phải duyệt lại và bài thi sẽ tạm ẩn khỏi cuộc thi. Bạn có thể sửa sau khi cuộc thi kết thúc.');
            }
        }

        if ($request->hasFile('thumbnail')) {
            // thay anh bia: xoa tep cu, luu tep moi
            if ($video->thumbnail) {
                Storage::disk('public')->delete($video->thumbnail);
            }
            $data['thumbnail'] = $request->file('thumbnail')->store('thumbnails', 'public');
        } elseif ($request->boolean('remove_thumbnail') && $video->thumbnail) {
            // bo anh bia hien tai (khong chon tep moi)
            Storage::disk('public')->delete($video->thumbnail);
            $data['thumbnail'] = null;
        } else {
            unset($data['thumbnail']); // khong dong vao anh bia hien tai
        }

        $data['allow_comments'] = $request->boolean('allow_comments');

        $video->fill($data);

        // FR8: video da duyet ma bi sua noi dung hien thi (tieu de / mo ta / anh bia / the loai)
        // thi phai duyet lai — tranh chieu "dang noi dung sach de duoc duyet roi sua thanh vi pham".
        // Doi privacy / allow_comments khong thay doi noi dung nen giu nguyen trang thai;
        // admin sua truc tiep cung khong can duyet lai.
        $needsReview = $video->status === 'approved'
            && ! auth()->user()->isAdmin()
            && $video->isDirty(['title', 'description', 'thumbnail', 'category_id']);

        if ($needsReview) {
            $video->status = 'pending';
        }

        $video->save();

        if ($needsReview) {
            Notification::send(
                User::where('role', 'admin')->where('is_active', true)->get(),
                new VideoSubmitted($video, edited: true)
            );

            return redirect()->to($this->afterManageUrl())
                ->with('success', 'Đã cập nhật video "'.$video->title.'". Vì nội dung thay đổi, video chuyển về trạng thái chờ duyệt lại và sẽ hiển thị công khai sau khi được duyệt.');
        }

        return redirect()->to($this->afterManageUrl())->with('success', 'Đã cập nhật video.');
    }

    public function destroy(Video $video): RedirectResponse
    {
        $this->authorizeOwner($video);

        // Soft delete khong keo theo cascadeOnDelete cua contest_entries — neu xoa video
        // dang du thi, entry mo coi se lam trang cuoc thi loi 500. Chan xoa khi video
        // con nam trong cuoc thi chua ket thuc.
        if ($this->inActiveContest($video)) {
            return back()->with('error', 'Video "'.$video->title.'" đang là bài dự thi của một cuộc thi chưa kết thúc nên không thể xóa. Bạn có thể xóa sau khi cuộc thi kết thúc.');
        }

        $video->delete(); // soft delete

        return redirect()->to($this->afterManageUrl())->with('success', 'Đã xóa video "'.$video->title.'".');
    }

    // Trang quay ve sau khi sua/xoa: admin → danh sach quan tri (route /my-videos gan role:creator
    // nen admin vao se bi 403 du thao tac da thanh cong); creator → "Video cua toi"
    private function afterManageUrl(): string
    {
        return auth()->user()->isAdmin() ? route('admin.videos.index') : route('videos.mine');
    }

    // A2/FR7: video dang la bai du thi cua mot cuoc thi chua ket thuc?
    private function inActiveContest(Video $video): bool
    {
        return $video->contestEntries()
            ->whereHas('contest', fn ($q) => $q->where('end_at', '>', now()))
            ->exists();
    }

    private function authorizeOwner(Video $video): void
    {
        $me = auth()->user();
        abort_unless($me->id === $video->user_id || $me->isAdmin(), 403, 'Bạn không có quyền với video này.');
    }
}
