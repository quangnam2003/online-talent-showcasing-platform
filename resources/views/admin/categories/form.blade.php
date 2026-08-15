@extends('layouts.app')

@section('title', ($category ? 'Sửa' : 'Thêm').' danh mục — TalentStage Admin')

@section('screen-kicker')<a href="{{ route('admin.dashboard') }}">Quản trị</a><span class="sep">/</span><a href="{{ route('admin.categories.index') }}">Danh mục</a><span class="sep">/</span><span>{{ $category ? 'Sửa' : 'Thêm mới' }}</span>@endsection
@section('screen-title', $category ? 'Sửa danh mục' : 'Thêm danh mục')
@section('screen-sub', $category ? $category->name : 'Danh mục mới cho video')

@section('content')
<form method="POST"
      action="{{ $category ? route('admin.categories.update', $category) : route('admin.categories.store') }}"
      style="display: flex; flex-direction: column; gap: var(--space-4); max-width: 480px">
    @csrf
    @if ($category) @method('PUT') @endif

    <label class="field">
        <span class="label-up">Tên danh mục</span>
        <input class="input @error('name') is-invalid @enderror" name="name"
               value="{{ old('name', $category?->name) }}" placeholder="Âm nhạc" required autofocus>
        @error('name') <span class="err-msg">{{ $message }}</span> @enderror
        <span class="muted-i">Slug tự sinh từ tên (vd: «Âm nhạc» → am-nhac).</span>
    </label>

    <div style="display: flex; gap: var(--space-3)">
        <button type="submit" class="btn btn-primary btn-sm">{{ $category ? 'Lưu thay đổi' : 'Thêm danh mục' }}</button>
        <a class="btn btn-ghost btn-sm" href="{{ route('admin.categories.index') }}">Hủy</a>
    </div>
</form>
@endsection
