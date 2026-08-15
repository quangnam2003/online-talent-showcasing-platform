@extends('layouts.app')

@section('title', 'Tạo nhóm — TalentStage')

@section('screen-kicker')<a href="{{ route('groups.index') }}">Nhóm</a><span class="sep">/</span><span>Tạo nhóm</span>@endsection
@section('screen-title', 'Tạo nhóm mới')
@section('screen-sub', 'Bạn sẽ là chủ nhóm và thành viên đầu tiên.')

@section('content')
<form method="POST" action="{{ route('groups.store') }}" style="display: flex; flex-direction: column; gap: var(--space-4); max-width: 560px">
    @csrf

    <label class="field">
        <span class="label-up">Tên nhóm</span>
        <input class="input @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}"
               placeholder="Acoustic Đà Nẵng" required autofocus>
        @error('name') <span class="err-msg">{{ $message }}</span> @enderror
    </label>

    <label class="field">
        <span class="label-up">Mô tả</span>
        <textarea class="input" name="description" rows="4" placeholder="Nhóm dành cho ai, thảo luận về điều gì…">{{ old('description') }}</textarea>
        @error('description') <span class="err-msg">{{ $message }}</span> @enderror
    </label>

    <div style="display: flex; gap: var(--space-3); align-items: center">
        <button type="submit" class="btn btn-primary btn-sm"><x-icon name="plus" size="14" /> Tạo nhóm</button>
        <a class="btn btn-ghost btn-sm" href="{{ route('groups.index') }}">Hủy</a>
    </div>
</form>
@endsection
