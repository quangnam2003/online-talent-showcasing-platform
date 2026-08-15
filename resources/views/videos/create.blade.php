@extends('layouts.app')

@section('title', 'Đăng video — TalentStage')

@section('screen-title', 'Đăng video')
@section('screen-sub', 'Tải video lên và gửi duyệt — video hiển thị công khai sau khi quản trị viên phê duyệt.')

@section('content')
<div class="upload-grid" style="display: grid; grid-template-columns: 1.3fr 1fr; gap: var(--space-8); align-items: start">

    {{-- ── Form upload (trai — mockup Upload) ── --}}
    <form method="POST" action="{{ route('videos.store') }}" enctype="multipart/form-data"
          style="display: flex; flex-direction: column; gap: var(--space-4)">
        @csrf

        <label class="dropzone">
            <span class="dropzone-ico"><x-icon name="upload" size="20" /></span>
            <span class="dropzone-title">Kéo thả video vào đây hoặc <u>chọn tệp</u></span>
            <span class="dropzone-hint">MP4, MOV hoặc WEBM · tối đa 100 MB</span>
            <span class="dropzone-name"></span>
            <input class="visually-hidden" type="file" name="video" accept="video/mp4,video/quicktime,video/webm" required>
        </label>
        @error('video') <span class="err-msg">{{ $message }}</span> @enderror

        <div class="grid-2" style="gap: var(--space-4)">
            <label class="field" style="grid-column: 1 / -1">
                <span class="label-up">Tiêu đề</span>
                <input class="input @error('title') is-invalid @enderror" name="title" value="{{ old('title') }}"
                       placeholder="Đêm nhạc mộc — bản thu phòng khách" required>
                @error('title') <span class="err-msg">{{ $message }}</span> @enderror
            </label>

            <label class="field">
                <span class="label-up">Thể loại</span>
                <select class="input @error('category_id') is-invalid @enderror" name="category_id" required>
                    <option value="">— Chọn thể loại —</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" @selected(old('category_id') == $cat->id)>{{ $cat->name }}</option>
                    @endforeach
                </select>
                @error('category_id') <span class="err-msg">{{ $message }}</span> @enderror
            </label>

            <label class="field">
                <span class="label-up">Quyền xem</span>
                <select class="input" name="privacy">
                    <option value="public" @selected(old('privacy', 'public') === 'public')>Công khai</option>
                    <option value="private" @selected(old('privacy') === 'private')>Riêng tư — chỉ mình tôi</option>
                </select>
            </label>

            <label class="field" style="grid-column: 1 / -1">
                <span class="label-up">Mô tả</span>
                <textarea class="input" name="description" rows="4" placeholder="Kể ngắn gọn về tiết mục…">{{ old('description') }}</textarea>
            </label>

            <label class="field">
                <span class="label-up">Ảnh bìa (tùy chọn)</span>
                <input class="input" type="file" name="thumbnail" accept="image/*" style="padding: 6px">
                @error('thumbnail') <span class="err-msg">{{ $message }}</span> @enderror
            </label>

            <label class="radio" style="align-self: end; padding-bottom: 8px">
                <input type="checkbox" name="allow_comments" value="1" @checked(old('allow_comments', true))>
                <span class="dot" style="border-radius: 3px"></span>
                Cho phép bình luận
            </label>
        </div>

        <div style="display: flex; gap: var(--space-2); align-items: center">
            <button type="submit" class="btn btn-primary btn-sm"><x-icon name="send" size="14" /> Gửi duyệt</button>
            <span class="muted-i">Video sẽ hiển thị công khai sau khi quản trị viên duyệt.</span>
        </div>
    </form>

    {{-- ── Trang thai duyet (phai — mockup Upload) ── --}}
    <div style="display: flex; flex-direction: column; gap: var(--space-3)">
        <h3 style="font-size: 19px; margin: 0">Trạng thái duyệt</h3>
        <table class="table" style="font-size: 12.5px">
            <thead><tr><th>Video</th><th>Gửi lúc</th><th>Trạng thái</th></tr></thead>
            <tbody>
                @forelse ($myUploads as $up)
                    @php
                        $t = ['pending' => 'Chờ duyệt', 'approved' => 'Đã duyệt', 'rejected' => 'Từ chối'][$up->status];
                    @endphp
                    <tr>
                        <td><a href="{{ route('videos.show', $up) }}">{{ \Illuminate\Support\Str::limit($up->title, 32) }}</a></td>
                        <td class="num" style="color: var(--color-neutral-600)">{{ $up->created_at->format('d/m H:i') }}</td>
                        <td><span class="tag tag-status" data-status="{{ $up->status }}">{{ $t }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="muted-i">Bạn chưa đăng video nào.</td></tr>
                @endforelse
            </tbody>
        </table>
        <a class="btn btn-ghost btn-sm" style="align-self: flex-start; padding-left: 0" href="{{ route('videos.mine') }}">
            Xem tất cả video của tôi <x-icon name="arrow-right" size="14" />
        </a>
    </div>
</div>
@endsection

@push('scripts')
<style>
    @media (max-width: 1080px) { .upload-grid { grid-template-columns: 1fr !important; } }
</style>
@endpush
