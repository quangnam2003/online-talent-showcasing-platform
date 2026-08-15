@extends('layouts.app')

@section('title', 'Cuộc thi — TalentStage')

@section('screen-kicker', 'FR7 · Contest')
@section('screen-title', 'Cuộc thi tài năng')
@section('screen-sub', 'Contest — entries, voting, leaderboard')

@section('content')
@foreach ($groups as [$label, $contests])
    @if ($contests->isNotEmpty())
        <section style="display: flex; flex-direction: column; gap: var(--space-3)">
            <h3 style="font-size: 20px; margin: 0">{{ $label }}</h3>
            <div class="grid-2">
                @foreach ($contests as $contest)
                    <a class="card" href="{{ route('contests.show', $contest) }}" style="padding: var(--space-4); gap: var(--space-2)">
                        <div style="display: flex; justify-content: space-between; align-items: baseline; gap: var(--space-2)">
                            <span class="card-kicker">{{ $contest->statusLabel() }}</span>
                            <span class="meta num">{{ $contest->entries_count }} bài dự thi</span>
                        </div>
                        <span style="font-family: var(--font-heading); font-weight: 600; font-size: 24px; line-height: 1.15; letter-spacing: -0.015em">{{ $contest->title }}</span>
                        @if ($contest->description)
                            <span style="font-size: 13px; color: var(--color-neutral-700); line-height: 1.55">{{ \Illuminate\Support\Str::limit($contest->description, 110) }}</span>
                        @endif
                        <span class="meta">
                            Nộp bài: {{ $contest->start_at->format('d/m/Y') }} — {{ $contest->submission_deadline->format('d/m/Y') }}
                            · Bình chọn tới {{ $contest->end_at->format('d/m/Y') }}
                        </span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif
@endforeach

@if (collect($groups)->every(fn ($g) => $g[1]->isEmpty()))
    <div class="card" style="align-items: center; padding: var(--space-8)">
        <span class="muted-i">Chưa có cuộc thi nào.</span>
    </div>
@endif
@endsection
