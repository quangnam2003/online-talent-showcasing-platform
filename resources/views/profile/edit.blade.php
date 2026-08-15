@extends('layouts.app')

@section('title', 'Sửa hồ sơ — TalentStage')

@section('screen-kicker')<a href="{{ route('users.show', $user) }}">Hồ sơ của tôi</a><span class="sep">/</span><span>Chỉnh sửa</span>@endsection
@section('screen-title', 'Sửa hồ sơ')
@section('screen-sub', 'Cập nhật ảnh đại diện, giới thiệu, nơi ở và thành tích của bạn.')

@section('content')
<form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data"
      style="display: grid; grid-template-columns: 220px 1fr; gap: var(--space-8); max-width: 860px; align-items: start" class="profile-form">
    @csrf
    @method('PUT')

    <div style="display: flex; flex-direction: column; gap: var(--space-3)">
        <div class="plate avatar-xl" style="width: 100%; aspect-ratio: 1; height: auto">
            @if ($user->avatar)
                <img src="{{ asset('storage/'.$user->avatar) }}" alt="{{ $user->name }}">
            @else
                {{ mb_substr($user->name, 0, 1) }}
            @endif
        </div>
        <label class="field">
            <span class="label-up">Đổi avatar (ảnh ≤ 4MB)</span>
            <input class="input" type="file" name="avatar" accept="image/*" style="padding: 6px">
            @error('avatar') <span class="err-msg">{{ $message }}</span> @enderror
        </label>
    </div>

    <div style="display: flex; flex-direction: column; gap: var(--space-4)">
        <label class="field">
            <span class="label-up">Họ tên</span>
            <input class="input @error('name') is-invalid @enderror" name="name" value="{{ old('name', $user->name) }}" required>
            @error('name') <span class="err-msg">{{ $message }}</span> @enderror
        </label>

        <label class="field">
            <span class="label-up">Giới thiệu</span>
            <textarea class="input" name="bio" rows="4" placeholder="Kể ngắn gọn về bạn…">{{ old('bio', $user->bio) }}</textarea>
            @error('bio') <span class="err-msg">{{ $message }}</span> @enderror
        </label>

        <label class="field">
            <span class="label-up">Nơi ở</span>
            <input class="input" name="location" value="{{ old('location', $user->location) }}" placeholder="Hà Nội">
        </label>

        <label class="field">
            <span class="label-up">Thành tích</span>
            <textarea class="input" name="achievements" rows="3" placeholder="Giải thưởng, chứng chỉ…">{{ old('achievements', $user->achievements) }}</textarea>
        </label>

        <div style="display: flex; gap: var(--space-3); align-items: center">
            <button type="submit" class="btn btn-primary" style="font-size: 13px"><x-icon name="check" size="15" /> Lưu hồ sơ</button>
            <a class="btn btn-ghost btn-sm" href="{{ route('users.show', $user) }}">Hủy</a>
            <span class="muted-i">Email đăng nhập không thể thay đổi: {{ $user->email }}</span>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<style>
    @media (max-width: 1080px) { .profile-form { grid-template-columns: 1fr !important; } }
</style>
@endpush
