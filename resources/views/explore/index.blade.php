@extends('layouts.app')

@section('title', 'Khám phá — TalentStage')

@section('screen-title', 'Tìm kiếm')
@section('screen-sub', 'Tìm video theo tên, lọc theo thể loại và sắp xếp theo mức độ thịnh hành.')

@section('content')
{{-- ── Thanh loc ── --}}
<form method="GET" action="{{ route('explore') }}" style="display: flex; gap: var(--space-2); flex-wrap: wrap; align-items: center">
    <input class="input" type="search" name="q" value="{{ $q }}" placeholder="Tìm theo tiêu đề, mô tả…" style="flex: 1; min-width: 220px; max-width: 420px">
    @if ($categorySlug)
        <input type="hidden" name="category" value="{{ $categorySlug }}">
    @endif
    <select class="input" name="sort" style="width: auto" onchange="this.form.submit()">
        <option value="trending" @selected($sort === 'trending')>Thịnh hành</option>
        <option value="new" @selected($sort === 'new')>Mới nhất</option>
        <option value="views" @selected($sort === 'views')>Nhiều lượt xem</option>
        <option value="rating" @selected($sort === 'rating')>Đánh giá cao</option>
    </select>
    <button class="btn btn-primary btn-sm"><x-icon name="search" size="14" /> Tìm</button>
    @if ($q || $categorySlug)
        <a class="btn btn-ghost btn-sm" href="{{ route('explore') }}">Xóa lọc</a>
    @endif
</form>

{{-- ── Chip the loai (moi the loai mot sac — giu nguyen q & sort khi doi) ── --}}
<div style="display: flex; gap: 6px; flex-wrap: wrap; align-items: center">
    <span class="label-up" style="margin-right: var(--space-1)">Thể loại</span>
    <a class="cat-chip {{ $categorySlug ? '' : 'active' }}" style="--cat: var(--color-neutral-700)"
       href="{{ route('explore', array_filter(['q' => $q, 'sort' => $sort])) }}">Tất cả</a>
    @foreach ($categories as $cat)
        <a class="cat-chip {{ $categorySlug === $cat->slug ? 'active' : '' }}" style="--cat: {{ $cat->colorVar() }}"
           href="{{ route('explore', array_filter(['q' => $q, 'sort' => $sort, 'category' => $cat->slug])) }}">{{ $cat->name }}</a>
    @endforeach
</div>

<div style="display: flex; align-items: baseline; justify-content: space-between">
    <span class="meta num">{{ $videos->total() }} video</span>
    @if ($q)
        <span class="muted-i">Kết quả cho «{{ $q }}»</span>
    @endif
</div>

{{-- ── Luoi ket qua ── --}}
<div class="grid-4">
    @forelse ($videos as $video)
        <a class="card video-card" href="{{ route('videos.show', $video) }}" style="--cat: {{ $video->category->colorVar() }}">
            <div class="video-thumb">@include('partials.thumb', ['video' => $video])</div>
            <div class="video-card-body">
                <span class="video-card-cat">{{ $video->category->name }}</span>
                <span class="video-card-title">{{ $video->title }}</span>
                <span class="meta">{{ $video->user->name }} · {{ number_format($video->views) }} lượt xem · ★ {{ number_format($video->avg_rating, 1) }}</span>
            </div>
        </a>
    @empty
        <div class="card" style="grid-column: 1 / -1; align-items: center; padding: var(--space-6)">
            <span class="muted-i">Không tìm thấy video nào phù hợp.</span>
        </div>
    @endforelse
</div>

@include('partials.pager', ['p' => $videos])
@endsection
