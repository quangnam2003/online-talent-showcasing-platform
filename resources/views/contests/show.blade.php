@extends('layouts.app')

@section('title', $contest->title.' — TalentStage')

@section('screen-kicker')<a href="{{ route('contests.index') }}">Cuộc thi</a><span class="sep">/</span><span>{{ $contest->statusLabel() }}</span>@endsection
@section('screen-title', $contest->title)
@section('screen-sub', 'Nhận bài '.$contest->start_at->format('d/m/Y').' — '.$contest->submission_deadline->format('d/m/Y').' · Bình chọn đến '.$contest->end_at->format('d/m/Y'))

@section('content')
@php $me = auth()->user(); @endphp

<div class="contest-top" style="display: flex; gap: var(--space-6); align-items: stretch">

    {{-- ── The chinh: mo ta + phase strip + nop bai (mockup Contest) ── --}}
    <div class="card" style="flex: 1.6; padding: var(--space-6); gap: var(--space-3)">
        <div class="card-kicker">{{ $contest->statusLabel() }}</div>
        @if ($contest->description)
            <p style="margin: 0; font-size: 13.5px; line-height: 1.6; text-align: justify; max-width: 640px">{{ $contest->description }}</p>
        @endif
        <p class="muted-i" style="margin: 0">Mỗi creator gửi một video đã được duyệt. Mỗi tài khoản bình chọn một lần cho một bài dự thi.</p>

        {{-- May trang thai: 4 o = 4 GIAI DOAN (khong phai 4 moc) — o hien tai to sang,
             o da qua danh dau ✓, nen khi "Đang nhận bài" thi o "Nhận bài" sang chu khong
             phai "Đóng nộp bài" nhu ban cu --}}
        @php
            $phaseIdx = (int) array_search($contest->status, ['upcoming', 'submission', 'voting', 'ended']);
            $phases = [
                ['Sắp diễn ra', 'đến '.$contest->start_at->format('d/m H:i')],
                ['Nhận bài', $contest->start_at->format('d/m').' — '.$contest->submission_deadline->format('d/m H:i')],
                ['Bình chọn', $contest->submission_deadline->format('d/m').' — '.$contest->end_at->format('d/m H:i')],
                ['Công bố', 'từ '.$contest->end_at->format('d/m H:i')],
            ];
        @endphp
        <div class="phase-strip">
            @foreach ($phases as $i => [$k, $v])
                <div class="phase {{ $i === $phaseIdx ? 'current' : ($i < $phaseIdx ? 'done' : '') }}">
                    <div class="phase-k">@if ($i < $phaseIdx)<x-icon name="check" size="10" /> @endif{{ $k }}</div>
                    <div class="phase-v">{{ $v }}</div>
                </div>
            @endforeach
        </div>

        {{-- Nop bai --}}
        <div style="display: flex; gap: var(--space-2); align-items: center; flex-wrap: wrap">
            @if ($myEntry)
                <span class="tag tag-accent" style="font-size: 11px">Đã gửi bài ✓ — «{{ $myEntry->video->title }}»</span>
                @unless ($myEntry->video->isViewableBy(null))
                    <span class="muted-i">Bài của bạn đang tạm ẩn — video cần được duyệt và ở chế độ công khai thì mới nhận phiếu tiếp.</span>
                @endunless
            @elseif ($me?->isCreator() && $contest->isAcceptingSubmissions())
                @if ($eligibleVideos->isNotEmpty())
                    <form method="POST" action="{{ route('contests.entries.store', $contest) }}" style="display: flex; gap: var(--space-2); align-items: center; flex-wrap: wrap">
                        @csrf
                        <select class="input" name="video_id" required style="width: auto; min-width: 220px">
                            <option value="">— Chọn video đã duyệt của bạn —</option>
                            @foreach ($eligibleVideos as $v)
                                <option value="{{ $v->id }}">{{ $v->title }}</option>
                            @endforeach
                        </select>
                        <button class="btn btn-primary btn-sm"><x-icon name="send" size="14" /> Gửi bài dự thi</button>
                    </form>
                @else
                    <span class="muted-i">Bạn chưa có video đã duyệt + công khai để dự thi — <a href="{{ route('videos.create') }}">đăng video</a> trước nhé.</span>
                @endif
            @elseif ($contest->status === 'upcoming')
                <span class="muted-i">Cuộc thi chưa mở nhận bài.</span>
            @elseif (! $me)
                <span class="muted-i"><a href="{{ route('login') }}">Đăng nhập</a> để dự thi hoặc bình chọn.</span>
            @endif
        </div>
    </div>

    {{-- ── Leaderboard ── --}}
    <div class="card" style="flex: 1; padding: var(--space-4); gap: var(--space-2)">
        <div class="card-kicker">Bảng xếp hạng</div>
        @forelse ($leaderboard as $i => $entry)
            <div style="display: flex; align-items: baseline; gap: var(--space-3); padding-bottom: 6px; border-bottom: 1px solid var(--color-divider)">
                <span class="rank-num" style="font-size: 20px; width: 26px">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                @if ($entry->video->isViewableBy(null))
                    <a href="{{ route('videos.show', $entry->video) }}" style="flex: 1; font-size: 13px">{{ $entry->user->name }} — {{ \Illuminate\Support\Str::limit($entry->video->title, 28) }}</a>
                @else
                    <span style="flex: 1; font-size: 13px; color: var(--color-neutral-600)">{{ $entry->user->name }} — {{ \Illuminate\Support\Str::limit($entry->video->title, 28) }} <span class="muted-i">(tạm ẩn)</span></span>
                @endif
                <span class="num" style="font-size: 13px; color: var(--color-neutral-700)">{{ number_format($entry->votes_count) }}</span>
            </div>
        @empty
            <span class="muted-i">Chưa có bài dự thi.</span>
        @endforelse
    </div>
</div>

{{-- ── Luoi bai du thi + nut binh chon ── --}}
<section style="display: flex; flex-direction: column; gap: var(--space-3)">
    <h3 style="font-size: 20px; margin: 0">Bài dự thi <span class="meta num">({{ $entries->count() }})</span></h3>
    <div class="grid-4">
        @forelse ($entries as $entry)
            @php $entryLive = $entry->video->isViewableBy(null); /* A2: video con duoc xem cong khai? */ @endphp
            <div class="card video-card" style="cursor: default; --cat: {{ $entry->video->category->colorVar() }}{{ $entryLive ? '' : '; opacity: .7' }}">
                @if ($entryLive)
                    <a href="{{ route('videos.show', $entry->video) }}" class="video-thumb" style="display: flex">@include('partials.thumb', ['video' => $entry->video])</a>
                @else
                    <div class="video-thumb" style="display: flex">@include('partials.thumb', ['video' => $entry->video])</div>
                @endif
                <div class="video-card-body" style="gap: var(--space-2)">
                    <span class="video-card-title" style="font-size: 16px">{{ $entry->video->title }}</span>
                    <span class="meta">{{ $entry->user->name }} · <span class="num">{{ number_format($entry->votes_count) }}</span> phiếu</span>

                    @unless ($entryLive)
                        <span class="tag tag-status" data-status="pending" style="align-self: flex-start" title="Video của bài thi đang chờ duyệt lại hoặc không còn công khai">Tạm ẩn — chờ duyệt lại</span>
                    @endunless

                    @if ($contest->isVotingOpen())
                        @auth
                            @if ($votedEntryId === $entry->id)
                                <span class="tag tag-accent" style="font-size: 10.5px; align-self: flex-start">Đã bình chọn ✓</span>
                            @elseif (! $entryLive)
                                {{-- bai tam an: khong nhan phieu moi --}}
                            @elseif ($votedEntryId !== null)
                                <span class="muted-i" style="font-size: 11px">Đã dùng lượt bình chọn</span>
                            @elseif ($entry->user_id === $me->id)
                                <span class="muted-i" style="font-size: 11px">Bài của bạn</span>
                            @else
                                <form method="POST" action="{{ route('entries.vote', $entry) }}">
                                    @csrf
                                    <button class="btn btn-secondary btn-xs"><x-icon name="heart" size="13" /> Bình chọn</button>
                                </form>
                            @endif
                        @else
                            <span class="muted-i" style="font-size: 11px"><a href="{{ route('login') }}">Đăng nhập</a> để bình chọn</span>
                        @endauth
                    @elseif ($contest->status === 'ended' && $loop->first)
                        <span class="tag tag-accent" style="font-size: 10.5px; align-self: flex-start">🏆 Quán quân</span>
                    @endif
                </div>
            </div>
        @empty
            <div class="card" style="grid-column: 1 / -1; align-items: center; padding: var(--space-6)">
                <span class="muted-i">Chưa có bài dự thi nào.</span>
            </div>
        @endforelse
    </div>
    <span class="muted-i">Cuộc thi tự động chuyển giai đoạn: nhận bài → đóng nhận bài → bình chọn → công bố kết quả.</span>
</section>
@endsection

@push('scripts')
<style>
    @media (max-width: 1080px) { .contest-top { flex-direction: column; } }
</style>
@endpush
