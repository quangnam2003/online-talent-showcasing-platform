@extends('layouts.app')

@section('title', 'Thông báo — TalentStage')

@section('screen-title', 'Thông báo')
@section('screen-sub', 'Kết quả duyệt video, người theo dõi mới và cập nhật từ cộng đồng. Mở trang này sẽ đánh dấu tất cả là đã đọc.')

@section('content')
<div style="display: flex; flex-direction: column; gap: var(--space-2); max-width: 720px">
    @forelse ($notifications as $n)
        @php
            $icon = ['approved' => '✓', 'rejected' => '✕', 'follower' => '＋'][$n->data['kind'] ?? ''] ?? '·';
        @endphp
        <a class="card" href="{{ url($n->data['url'] ?? '/') }}"
           style="flex-direction: row; gap: var(--space-3); padding: var(--space-3); align-items: baseline; text-decoration: none; color: inherit; {{ $n->read_at === null ? 'border-left: 2px solid var(--color-accent); background: var(--color-accent-100)' : '' }}">
            <span class="rank-num" style="font-size: 16px; width: 20px">{{ $icon }}</span>
            <span style="flex: 1; font-size: 13.5px; line-height: 1.5">{{ $n->data['message'] ?? '' }}</span>
            <span class="meta" style="flex: none">{{ $n->created_at->diffForHumans() }}</span>
        </a>
    @empty
        <div class="card" style="align-items: center; padding: var(--space-8)">
            <span class="muted-i">Chưa có thông báo nào.</span>
        </div>
    @endforelse

    @include('partials.pager', ['p' => $notifications])
</div>
@endsection
