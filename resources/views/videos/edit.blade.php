@extends('layouts.app')

@section('title', 'Sửa video — TalentStage')

@php $backUrl = auth()->user()->isAdmin() ? route('admin.videos.index') : route('videos.mine'); $backLabel = auth()->user()->isAdmin() ? 'Quản lý video' : 'Tiết mục của tôi'; @endphp
@section('screen-kicker')<a href="{{ $backUrl }}">{{ $backLabel }}</a><span class="sep">/</span><span>{{ \Illuminate\Support\Str::limit($video->title, 40) }}</span>@endsection
@section('screen-title', 'Sửa video')

@section('content')
<form method="POST" action="{{ route('videos.update', $video) }}" enctype="multipart/form-data"
      style="display: flex; flex-direction: column; gap: var(--space-4); max-width: 640px">
    @csrf
    @method('PUT')

    <label class="field">
        <span class="label-up">Tiêu đề</span>
        <input class="input @error('title') is-invalid @enderror" name="title" value="{{ old('title', $video->title) }}" required>
        @error('title') <span class="err-msg">{{ $message }}</span> @enderror
    </label>

    <div class="grid-2" style="gap: var(--space-4)">
        <label class="field">
            <span class="label-up">Thể loại</span>
            <select class="input" name="category_id" required>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}" @selected(old('category_id', $video->category_id) == $cat->id)>{{ $cat->name }}</option>
                @endforeach
            </select>
        </label>
        <label class="field">
            <span class="label-up">Quyền xem</span>
            @if ($inActiveContest)
                {{-- A2: dang du thi — khoa doi che do xem (select disabled khong submit → gui hidden) --}}
                <input type="hidden" name="privacy" value="{{ $video->privacy }}">
                <select class="input" disabled title="Video đang dự thi — không thể đổi chế độ xem">
                    <option>{{ $video->privacy === 'public' ? 'Công khai' : 'Riêng tư — chỉ mình tôi' }}</option>
                </select>
                <span class="field-hint"><x-icon name="trophy" size="12" /> Video đang là bài dự thi — đổi chế độ xem và sửa nội dung bị khóa cho tới khi cuộc thi kết thúc.</span>
            @else
                <select class="input" name="privacy">
                    <option value="public" @selected(old('privacy', $video->privacy) === 'public')>Công khai</option>
                    <option value="private" @selected(old('privacy', $video->privacy) === 'private')>Riêng tư — chỉ mình tôi</option>
                </select>
            @endif
        </label>
    </div>

    <label class="field">
        <span class="label-up">Mô tả</span>
        <textarea class="input" name="description" rows="4">{{ old('description', $video->description) }}</textarea>
    </label>

    {{-- Anh bia: hien anh hien tai (neu co), chon tep moi de thay, hoac tich "Xoa anh bia" — JS: tsThumbField --}}
    @php $hasThumb = $video->thumbnail && file_exists(public_path('storage/'.$video->thumbnail)); @endphp
    <div class="field" data-thumb-field>
        <span class="label-up">Ảnh bìa (tùy chọn)</span>
        <div style="display: flex; align-items: center; gap: var(--space-3); flex-wrap: wrap">
            <span class="hatch-mid thumb-sm" style="width: 128px; height: 76px; flex: none; display: block; --cat: {{ $video->category->colorVar() }}">
                <img data-thumb-preview src="{{ $hasThumb ? asset('storage/'.$video->thumbnail) : '' }}" alt="" @unless ($hasThumb) hidden @endunless>
            </span>
            <div style="display: flex; flex-direction: column; gap: var(--space-2); flex: 1; min-width: 220px">
                <span class="meta" data-thumb-status
                      data-msg-current="Ảnh bìa hiện tại — chọn tệp khác để thay."
                      data-msg-none="Chưa có ảnh bìa — chọn ảnh để thêm (JPG, PNG, WEBP, tối đa 4 MB)."
                      data-msg-new="Ảnh mới sẽ thay ảnh bìa hiện tại khi bạn lưu."
                      data-msg-remove="Ảnh bìa sẽ bị xoá khi bạn lưu.">{{ $hasThumb ? 'Ảnh bìa hiện tại — chọn tệp khác để thay.' : 'Chưa có ảnh bìa — chọn ảnh để thêm (JPG, PNG, WEBP, tối đa 4 MB).' }}</span>
                <input class="input @error('thumbnail') is-invalid @enderror" type="file" name="thumbnail" accept="image/*" style="padding: 6px" data-thumb-input>
                @if ($hasThumb)
                    <label class="radio" style="font-size: 13px">
                        <input type="checkbox" name="remove_thumbnail" value="1" data-thumb-remove @checked(old('remove_thumbnail'))>
                        <span class="dot" style="border-radius: 3px"></span>
                        Xoá ảnh bìa hiện tại
                    </label>
                @endif
            </div>
        </div>
        @error('thumbnail') <span class="err-msg">{{ $message }}</span> @enderror
    </div>

    <label class="radio">
        <input type="checkbox" name="allow_comments" value="1" @checked(old('allow_comments', $video->allow_comments))>
        <span class="dot" style="border-radius: 3px"></span>
        Cho phép bình luận
    </label>

    <div style="display: flex; gap: var(--space-3); align-items: center">
        <button type="submit" class="btn btn-primary btn-sm"><x-icon name="check" size="14" /> Lưu thay đổi</button>
        <a class="btn btn-ghost btn-sm" href="{{ $backUrl }}">Hủy</a>
        <span class="muted-i">File video không thể thay đổi — muốn đổi hãy đăng video mới.</span>
    </div>
</form>
@endsection
