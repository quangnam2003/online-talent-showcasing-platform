@extends('layouts.app')

@section('title', 'Đăng video — TalentStage')

@section('screen-kicker', 'FR2 · Content')
@section('screen-title', 'Đăng & theo dõi duyệt')
@section('screen-sub', 'Upload video, submit for approval, track status')

@section('content')
<div class="upload-grid" style="display: grid; grid-template-columns: 1.3fr 1fr; gap: var(--space-8); align-items: start">

    {{-- ── Form upload (trai — mockup Upload) ── --}}
    <form method="POST" action="{{ route('videos.store') }}" enctype="multipart/form-data"
          style="display: flex; flex-direction: column; gap: var(--space-4)">
        @csrf

        <label class="plate hatch" style="border-style: dashed; min-height: 190px; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: var(--space-2); cursor: pointer">
            <span class="slot-note" id="fileNote">[ chọn tệp video · mp4 / mov / webm, tối đa 100 MB ]</span>
            <span class="btn btn-secondary btn-sm">Chọn tệp · Browse</span>
            <input type="file" name="video" accept="video/mp4,video/quicktime,video/webm" required style="display: none"
                   onchange="document.getElementById('fileNote').textContent = this.files[0] ? this.files[0].name : '[ chọn tệp video ]'">
        </label>
        @error('video') <span class="err-msg">{{ $message }}</span> @enderror

        <div class="grid-2" style="gap: var(--space-4)">
            <label class="field" style="grid-column: 1 / -1">
                <span class="label-up">Tiêu đề · Title</span>
                <input class="input @error('title') is-invalid @enderror" name="title" value="{{ old('title') }}"
                       placeholder="Đêm nhạc mộc — bản thu phòng khách" required>
                @error('title') <span class="err-msg">{{ $message }}</span> @enderror
            </label>

            <label class="field">
                <span class="label-up">Thể loại · Category</span>
                <select class="input @error('category_id') is-invalid @enderror" name="category_id" required>
                    <option value="">— Chọn thể loại —</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" @selected(old('category_id') == $cat->id)>{{ $cat->name }}</option>
                    @endforeach
                </select>
                @error('category_id') <span class="err-msg">{{ $message }}</span> @enderror
            </label>

            <label class="field">
                <span class="label-up">Quyền xem · Visibility (FR8)</span>
                <select class="input" name="privacy">
                    <option value="public" @selected(old('privacy', 'public') === 'public')>Công khai</option>
                    <option value="private" @selected(old('privacy') === 'private')>Riêng tư — chỉ mình tôi</option>
                </select>
            </label>

            <label class="field" style="grid-column: 1 / -1">
                <span class="label-up">Mô tả · Description</span>
                <textarea class="input" name="description" rows="4" placeholder="Kể ngắn gọn về tiết mục…">{{ old('description') }}</textarea>
            </label>

            <label class="field">
                <span class="label-up">Thumbnail (ảnh, tùy chọn)</span>
                <input class="input" type="file" name="thumbnail" accept="image/*" style="padding: 6px">
                @error('thumbnail') <span class="err-msg">{{ $message }}</span> @enderror
            </label>

            <label class="radio" style="align-self: end; padding-bottom: 8px">
                <input type="checkbox" name="allow_comments" value="1" @checked(old('allow_comments', true))>
                <span class="dot" style="border-radius: 3px"></span>
                Cho phép bình luận (FR8)
            </label>
        </div>

        <div style="display: flex; gap: var(--space-2); align-items: center">
            <button type="submit" class="btn btn-primary btn-sm">Gửi duyệt · Submit for approval</button>
            <span class="muted-i">Video sẽ hiển thị công khai sau khi admin duyệt.</span>
        </div>
    </form>

    {{-- ── Trang thai duyet (phai — mockup Upload) ── --}}
    <div style="display: flex; flex-direction: column; gap: var(--space-3)">
        <h3 style="font-size: 19px; margin: 0">Trạng thái duyệt · Approval status</h3>
        <table class="table" style="font-size: 12.5px">
            <thead><tr><th>Video</th><th>Gửi lúc</th><th>Trạng thái</th></tr></thead>
            <tbody>
                @forelse ($myUploads as $up)
                    @php
                        $c = ['pending' => 'var(--color-neutral-500)', 'approved' => 'var(--color-accent-700)', 'rejected' => 'var(--color-neutral-800)'][$up->status];
                        $t = ['pending' => 'Chờ duyệt', 'approved' => 'Đã duyệt', 'rejected' => 'Từ chối'][$up->status];
                    @endphp
                    <tr>
                        <td><a href="{{ route('videos.show', $up) }}">{{ \Illuminate\Support\Str::limit($up->title, 32) }}</a></td>
                        <td class="num" style="color: var(--color-neutral-600)">{{ $up->created_at->format('d/m H:i') }}</td>
                        <td><span class="tag" style="font-size: 10px; border: 1px solid {{ $c }}; color: {{ $c }}">{{ $t }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="muted-i">Bạn chưa đăng video nào.</td></tr>
                @endforelse
            </tbody>
        </table>
        <a class="btn btn-ghost btn-sm" style="align-self: flex-start; padding-left: 0" href="{{ route('videos.mine') }}">
            Xem tất cả video của tôi →
        </a>
    </div>
</div>
@endsection

@push('scripts')
<style>
    @media (max-width: 1080px) { .upload-grid { grid-template-columns: 1fr !important; } }
</style>
@endpush
