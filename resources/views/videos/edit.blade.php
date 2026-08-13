@extends('layouts.app')

@section('title', 'Sửa video — TalentStage')

@section('screen-kicker', 'FR2 · Content')
@section('screen-title', 'Sửa video')
@section('screen-sub', $video->title)

@section('content')
<form method="POST" action="{{ route('videos.update', $video) }}" enctype="multipart/form-data"
      style="display: flex; flex-direction: column; gap: var(--space-4); max-width: 640px">
    @csrf
    @method('PUT')

    <label class="field">
        <span class="label-up">Tiêu đề · Title</span>
        <input class="input @error('title') is-invalid @enderror" name="title" value="{{ old('title', $video->title) }}" required>
        @error('title') <span class="err-msg">{{ $message }}</span> @enderror
    </label>

    <div class="grid-2" style="gap: var(--space-4)">
        <label class="field">
            <span class="label-up">Thể loại · Category</span>
            <select class="input" name="category_id" required>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}" @selected(old('category_id', $video->category_id) == $cat->id)>{{ $cat->name }}</option>
                @endforeach
            </select>
        </label>
        <label class="field">
            <span class="label-up">Quyền xem · Visibility</span>
            <select class="input" name="privacy">
                <option value="public" @selected(old('privacy', $video->privacy) === 'public')>Công khai</option>
                <option value="private" @selected(old('privacy', $video->privacy) === 'private')>Riêng tư — chỉ mình tôi</option>
            </select>
        </label>
    </div>

    <label class="field">
        <span class="label-up">Mô tả · Description</span>
        <textarea class="input" name="description" rows="4">{{ old('description', $video->description) }}</textarea>
    </label>

    <label class="field">
        <span class="label-up">Đổi thumbnail (tùy chọn)</span>
        <input class="input" type="file" name="thumbnail" accept="image/*" style="padding: 6px">
        @error('thumbnail') <span class="err-msg">{{ $message }}</span> @enderror
    </label>

    <label class="radio">
        <input type="checkbox" name="allow_comments" value="1" @checked(old('allow_comments', $video->allow_comments))>
        <span class="dot" style="border-radius: 3px"></span>
        Cho phép bình luận
    </label>

    <div style="display: flex; gap: var(--space-3); align-items: center">
        <button type="submit" class="btn btn-primary btn-sm">Lưu thay đổi · Save</button>
        <a class="btn btn-ghost btn-sm" href="{{ route('videos.mine') }}">Hủy</a>
        <span class="muted-i">File video không thể thay đổi — muốn đổi hãy đăng video mới.</span>
    </div>
</form>
@endsection
