@extends('layouts.app')

@section('title', 'Bảng tin — TalentStage')

@section('screen-title', 'Bảng tin')
@section('screen-sub', 'Video mới nhất từ những creator bạn đang theo dõi.')

@section('content')
<div class="feed-grid" style="display: grid; grid-template-columns: 1.7fr 1fr; gap: var(--space-6); align-items: start">

    {{-- ── Dong thoi gian ── --}}
    <div style="display: flex; flex-direction: column; gap: var(--space-4)">
        @forelse ($videos as $video)
            <div class="card" style="padding: var(--space-4); gap: var(--space-3)">
                <div style="display: flex; align-items: center; gap: var(--space-2)">
                    <span class="avatar">
                        @if ($video->user->avatar)
                            <img src="{{ asset('storage/'.$video->user->avatar) }}" alt="">
                        @else
                            {{ mb_substr($video->user->name, 0, 1) }}
                        @endif
                    </span>
                    <a href="{{ route('users.show', $video->user) }}" style="font-size: 12.5px">{{ $video->user->name }}</a>
                    <span class="muted-i">đăng video mới</span>
                    <span class="meta" style="margin-left: auto">{{ $video->created_at->diffForHumans() }}</span>
                </div>
                <a href="{{ route('videos.show', $video) }}" style="display: flex; gap: var(--space-3); text-decoration: none; color: inherit" class="feed-item">
                    <div class="hatch-mid" style="width: 200px; height: 118px; flex: 0 0 200px; border: 1px solid var(--color-divider); overflow: hidden">
                        @if ($video->thumbnail && file_exists(public_path('storage/'.$video->thumbnail)))
                            <img src="{{ asset('storage/'.$video->thumbnail) }}" style="width: 100%; height: 100%; object-fit: cover" alt="">
                        @endif
                    </div>
                    <div style="display: flex; flex-direction: column; gap: var(--space-1); min-width: 0">
                        <span style="font-family: var(--font-heading); font-weight: 600; font-size: 18px">{{ $video->title }}</span>
                        @if ($video->description)
                            <span style="font-size: 12.5px; color: var(--color-neutral-700); line-height: 1.55">{{ \Illuminate\Support\Str::limit($video->description, 120) }}</span>
                        @endif
                        <span class="meta"><span style="color: {{ $video->category->colorVar() }}; font-weight: 600">{{ $video->category->name }}</span> · {{ number_format($video->views) }} lượt xem · ♡ {{ number_format($video->likes_count) }}</span>
                    </div>
                </a>
            </div>
        @empty
            <div class="card" style="align-items: center; padding: var(--space-8); gap: var(--space-2)">
                <span class="muted-i">Bảng tin trống — bạn chưa theo dõi ai, hoặc người bạn theo dõi chưa đăng video.</span>
                <a class="btn btn-primary btn-sm" href="{{ route('explore') }}"><x-icon name="compass" size="14" /> Khám phá creator để theo dõi</a>
            </div>
        @endforelse

        @include('partials.pager', ['p' => $videos])
    </div>

    {{-- ── Goi y theo doi ── --}}
    <div class="card" style="padding: var(--space-4); gap: var(--space-2)">
        <div class="card-kicker">Gợi ý theo dõi</div>
        @forelse ($suggested as $user)
            <div style="display: flex; align-items: center; gap: var(--space-2); padding-bottom: var(--space-1); border-bottom: 1px solid var(--color-divider)">
                <span class="avatar" style="width: 30px; height: 30px">
                    @if ($user->avatar)
                        <img src="{{ asset('storage/'.$user->avatar) }}" alt="">
                    @else
                        {{ mb_substr($user->name, 0, 1) }}
                    @endif
                </span>
                <span style="display: flex; flex-direction: column; flex: 1; min-width: 0">
                    <a href="{{ route('users.show', $user) }}" style="font-size: 12.5px">{{ $user->name }}</a>
                    <span class="meta">{{ number_format($user->followers_count) }} người theo dõi</span>
                </span>
                <form method="POST" action="{{ route('follows.toggle', $user) }}">
                    @csrf
                    <button class="btn btn-ghost btn-xs"><x-icon name="user-plus" size="13" /> Theo dõi</button>
                </form>
            </div>
        @empty
            <span class="muted-i">Bạn đã theo dõi tất cả creator rồi!</span>
        @endforelse
    </div>
</div>
@endsection

@push('scripts')
<style>
    @media (max-width: 1080px) {
        .feed-grid { grid-template-columns: 1fr !important; }
        .feed-item { flex-direction: column; }
        .feed-item > div:first-child { width: 100% !important; flex: none !important; }
    }
</style>
@endpush
