@extends('layouts.app')

@section('title', 'Khám phá — TalentStage')

@section('screen-kicker', 'FR3 · Talent discovery')
@section('screen-title', 'Tìm kiếm & lọc')
@section('screen-sub', 'Explore — search, filter by category, sort & trending')

@section('content')
{{-- ── Thanh loc ── --}}
<form method="GET" action="{{ route('explore') }}" style="display: flex; gap: var(--space-2); flex-wrap: wrap; align-items: center">
    <input class="input" type="search" name="q" value="{{ $q }}" placeholder="Tìm theo tiêu đề, mô tả…" style="flex: 1; min-width: 220px; max-width: 420px">
    <select class="input" name="category" style="width: auto" onchange="this.form.submit()">
        <option value="">Mọi thể loại</option>
        @foreach ($categories as $cat)
            <option value="{{ $cat->slug }}" @selected($categorySlug === $cat->slug)>{{ $cat->name }}</option>
        @endforeach
    </select>
    <select class="input" name="sort" style="width: auto" onchange="this.form.submit()">
        <option value="trending" @selected($sort === 'trending')>Thịnh hành · Trending</option>
        <option value="new" @selected($sort === 'new')>Mới nhất · Newest</option>
        <option value="views" @selected($sort === 'views')>Lượt xem · Most viewed</option>
        <option value="rating" @selected($sort === 'rating')>Điểm đánh giá · Top rated</option>
    </select>
    <button class="btn btn-primary btn-sm">Tìm · Search</button>
    @if ($q || $categorySlug)
        <a class="btn btn-ghost btn-sm" href="{{ route('explore') }}">Xóa lọc</a>
    @endif
</form>

<div style="display: flex; align-items: baseline; justify-content: space-between">
    <span class="meta num">{{ $videos->total() }} video</span>
    @if ($q)
        <span class="muted-i">Kết quả cho «{{ $q }}»</span>
    @endif
</div>

{{-- ── Luoi ket qua ── --}}
<div class="grid-4">
    @forelse ($videos as $video)
        <a class="card video-card" href="{{ route('videos.show', $video) }}">
            <div class="video-thumb hatch-mid">
                @if ($video->thumbnail && file_exists(public_path('storage/'.$video->thumbnail)))
                    <img src="{{ asset('storage/'.$video->thumbnail) }}" alt="{{ $video->title }}">
                @else
                    <span class="slot-note">[ thumbnail ]</span>
                @endif
            </div>
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
