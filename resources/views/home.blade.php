@extends('layouts.app')

@section('screen-title', 'Khám phá tài năng')
@section('screen-sub', 'Video mới, tiết mục nổi bật và những gương mặt đang được cộng đồng yêu thích.')

@section('content')
{{-- ── Hero + Trending (1.6fr / 1fr) ── --}}
<section style="display: grid; grid-template-columns: 1.6fr 1fr; gap: var(--space-6); align-items: stretch" class="grid-hero">
    <div class="hero-plate ph-art" style="min-height: 320px; display: flex; flex-direction: column; justify-content: flex-end; padding: var(--space-6)">
        @if ($featured && $featured->thumbnail && file_exists(public_path('storage/'.$featured->thumbnail)))
            <img src="{{ asset('storage/'.$featured->thumbnail) }}" alt=""
                 style="position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover">
            <span style="position: absolute; inset: 0; background: linear-gradient(180deg, transparent 40%, rgba(32,31,29,.35))"></span>
        @else
            <x-icon name="mic" size="220" class="ph-art-ico" />
        @endif
        @if ($featured)
            <div class="hero-box">
                <div class="kicker"><x-icon name="sparkles" size="12" /> Nổi bật tuần này</div>
                <h2 style="font-size: 28px; margin: var(--space-1) 0 var(--space-2)">{{ $featured->title }}</h2>
                <p style="margin: 0 0 var(--space-3); font-size: 13.5px; line-height: 1.55; color: var(--color-neutral-800)">
                    {{ \Illuminate\Support\Str::limit($featured->description ?? 'Tiết mục của '.$featured->user->name.' trong thể loại '.$featured->category->name.'.', 150) }}
                </p>
                <div style="display: flex; align-items: center; gap: var(--space-3); flex-wrap: wrap">
                    <a class="btn btn-primary btn-sm" href="{{ url('/videos/'.$featured->id) }}"><x-icon name="play" size="14" /> Xem ngay</a>
                    <span class="meta">{{ $featured->user->name }} · {{ number_format($featured->views) }} lượt xem</span>
                </div>
            </div>
        @else
            <div class="hero-box">
                <div class="kicker">Sân khấu đang chờ</div>
                <h2 style="font-size: 28px; margin: var(--space-1) 0 var(--space-2)">Tiết mục đầu tiên là của bạn</h2>
                <p style="margin: 0 0 var(--space-3); font-size: 13.5px; line-height: 1.55; color: var(--color-neutral-800)">
                    Chưa có video nào được duyệt. Đăng ký làm creator, tải video lên và trở thành gương mặt nổi bật đầu tiên của TalentStage.
                </p>
                @guest
                    <a class="btn btn-primary btn-sm" href="{{ route('register') }}"><x-icon name="user-plus" size="14" /> Tham gia ngay</a>
                @else
                    <a class="btn btn-primary btn-sm" href="{{ url('/videos/create') }}"><x-icon name="upload" size="14" /> Đăng video</a>
                @endguest
            </div>
        @endif
    </div>

    <div class="card" style="gap: var(--space-3); padding: var(--space-4)">
        <div class="card-kicker">Đang thịnh hành</div>
        @forelse ($trending as $i => $video)
            <a class="rank-row" href="{{ url('/videos/'.$video->id) }}">
                <span class="rank-num">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                <span style="display: flex; flex-direction: column; gap: 1px; min-width: 0">
                    <span class="rank-title" style="font-size: 13.5px">{{ $video->title }}</span>
                    <span class="meta">{{ $video->user->name }} · {{ number_format($video->views) }} lượt xem</span>
                </span>
            </a>
        @empty
            <span class="muted-i">Chưa có dữ liệu thịnh hành.</span>
        @endforelse
    </div>
</section>

{{-- ── Luoi video moi ── --}}
<section style="display: flex; flex-direction: column; gap: var(--space-3)">
    <div style="display: flex; align-items: center; justify-content: space-between; gap: var(--space-4)">
        <h3 style="font-size: 20px; margin: 0">Video mới</h3>
        <a href="{{ url('/explore') }}" class="btn btn-ghost btn-sm">Xem tất cả <x-icon name="arrow-right" size="14" /></a>
    </div>
    <div class="grid-4">
        @forelse ($videos as $video)
            <a class="card video-card" href="{{ url('/videos/'.$video->id) }}" style="--cat: {{ $video->category->colorVar() }}">
                <div class="video-thumb">
                    @if ($video->thumbnail && file_exists(public_path('storage/'.$video->thumbnail)))
                        <img src="{{ asset('storage/'.$video->thumbnail) }}" alt="{{ $video->title }}" loading="lazy">
                    @else
                        <span class="thumb-ph" aria-hidden="true"></span>
                    @endif
                </div>
                <div class="video-card-body">
                    <span class="video-card-cat">{{ $video->category->name }}</span>
                    <span class="video-card-title">{{ $video->title }}</span>
                    <span class="meta">{{ $video->user->name }} · {{ number_format($video->views) }} lượt xem</span>
                </div>
            </a>
        @empty
            <div class="card" style="grid-column: 1 / -1; align-items: center; padding: var(--space-6)">
                <span class="muted-i">Chưa có video nào được duyệt — hãy là người đầu tiên!</span>
            </div>
        @endforelse
    </div>
</section>

{{-- ── Cuoc thi dang dien ra ── --}}
@if ($contests->isNotEmpty())
    <section style="display: flex; flex-direction: column; gap: var(--space-3)">
        <div style="display: flex; align-items: center; justify-content: space-between; gap: var(--space-4)">
            <h3 style="font-size: 20px; margin: 0">Cuộc thi đang diễn ra</h3>
            <a href="{{ url('/contests') }}" class="btn btn-ghost btn-sm">Tất cả cuộc thi <x-icon name="arrow-right" size="14" /></a>
        </div>
        <div class="grid-2" style="grid-template-columns: repeat(3, 1fr)">
            @foreach ($contests as $contest)
                <a class="card" href="{{ url('/contests/'.$contest->id) }}" style="gap: var(--space-1)">
                    <div class="card-kicker">{{ $contest->statusLabel() }}</div>
                    <span class="card-title" style="font-size: 19px">{{ $contest->title }}</span>
                    <span class="meta">
                        Nhận bài {{ $contest->start_at->format('d/m') }} — {{ $contest->submission_deadline->format('d/m') }}
                        · Công bố {{ $contest->end_at->format('d/m') }}
                    </span>
                </a>
            @endforeach
        </div>
    </section>
@endif
@endsection

@push('scripts')
<style>
    @media (max-width: 1080px) { .grid-hero { grid-template-columns: 1fr !important; } }
</style>
@endpush
