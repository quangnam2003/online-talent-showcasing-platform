@extends('layouts.app')

@section('screen-kicker', 'FR3 · Talent discovery')
@section('screen-title', 'Khám phá tài năng')
@section('screen-sub', 'Discover — browse by category, search, trending')

@section('content')
{{-- ── Hero + Trending (bo cuc 1.6fr / 1fr nhu mockup) ── --}}
<section style="display: grid; grid-template-columns: 1.6fr 1fr; gap: var(--space-6); align-items: stretch" class="grid-hero">
    <div class="plate hatch" style="position: relative; min-height: 300px; display: flex; flex-direction: column; justify-content: flex-end; padding: var(--space-6)">
        @if ($featured)
            @if ($featured->thumbnail && file_exists(public_path('storage/'.$featured->thumbnail)))
                <img src="{{ asset('storage/'.$featured->thumbnail) }}" alt=""
                     style="position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover">
            @else
                <span class="slot-note" style="position: absolute; top: var(--space-4); left: var(--space-4)">[ hero — ảnh sân khấu tài năng nổi bật ]</span>
            @endif
            <div style="position: relative; background: var(--color-bg); border: 1px solid var(--color-divider); padding: var(--space-4); max-width: 460px">
                <div class="kicker">Nổi bật · Featured</div>
                <h2 style="font-weight: 400; font-size: 30px; margin: var(--space-1) 0 var(--space-2)">{{ $featured->title }}</h2>
                <p style="margin: 0 0 var(--space-3); font-size: 13.5px; text-align: justify; color: var(--color-neutral-800)">
                    {{ \Illuminate\Support\Str::limit($featured->description ?? 'Tiết mục của '.$featured->user->name.' trong thể loại '.$featured->category->name.'.', 150) }}
                </p>
                <a class="btn btn-primary btn-sm" href="{{ url('/videos/'.$featured->id) }}">Xem ngay · Watch</a>
            </div>
        @else
            <span class="slot-note" style="position: absolute; top: var(--space-4); left: var(--space-4)">[ hero — ảnh sân khấu tài năng nổi bật ]</span>
            <div style="position: relative; background: var(--color-bg); border: 1px solid var(--color-divider); padding: var(--space-4); max-width: 460px">
                <div class="kicker">Sân khấu đang chờ · Stage awaits</div>
                <h2 style="font-weight: 400; font-size: 30px; margin: var(--space-1) 0 var(--space-2)">Tiết mục đầu tiên là của bạn</h2>
                <p style="margin: 0 0 var(--space-3); font-size: 13.5px; text-align: justify; color: var(--color-neutral-800)">
                    Chưa có video nào được duyệt. Đăng ký làm creator, tải video lên và trở thành gương mặt nổi bật đầu tiên của TalentStage.
                </p>
                @guest
                    <a class="btn btn-primary btn-sm" href="{{ route('register') }}">Tham gia ngay · Join</a>
                @else
                    <a class="btn btn-primary btn-sm" href="{{ url('/videos/create') }}">Đăng video · Upload</a>
                @endguest
            </div>
        @endif
    </div>

    <div class="card" style="gap: var(--space-3); padding: var(--space-4)">
        <div class="card-kicker">Đang thịnh hành · Trending talent</div>
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
        <h3 style="font-size: 20px; margin: 0">Video mới · Browse by category</h3>
        <a href="{{ url('/explore') }}" class="muted-i">Tìm kiếm &amp; lọc đầy đủ → Explore</a>
    </div>
    <div class="grid-4">
        @forelse ($videos as $video)
            <a class="card video-card" href="{{ url('/videos/'.$video->id) }}">
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
        <h3 style="font-size: 20px; margin: 0">Cuộc thi · Contest</h3>
        <div class="grid-2" style="grid-template-columns: repeat(3, 1fr)">
            @foreach ($contests as $contest)
                <a class="card" href="{{ url('/contests/'.$contest->id) }}" style="text-decoration: none; color: inherit; gap: var(--space-1)">
                    <div class="card-kicker">{{ $contest->statusLabel() }}</div>
                    <span class="card-title" style="font-size: 19px; font-weight: 400">{{ $contest->title }}</span>
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
