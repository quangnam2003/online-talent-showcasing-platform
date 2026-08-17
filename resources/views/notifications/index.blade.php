@extends('layouts.app')

@section('title', 'Thông báo — TalentStage')

@section('screen-title', 'Thông báo')
@section('screen-sub', 'Kết quả duyệt tiết mục, người theo dõi mới và cập nhật từ cộng đồng. Mở trang này sẽ đánh dấu tất cả là đã đọc.')

@section('content')
<div style="display: flex; flex-direction: column; gap: var(--space-2); max-width: 720px">
    @forelse ($notifications as $n)
        @php
            // moi loai thong bao mot icon + mau rieng
            [$icon, $tone] = [
                'approved' => ['check', 'ok'],
                'rejected' => ['x', 'bad'],
                'follower' => ['user-plus', 'accent'],
                'submitted' => ['upload', 'accent'],
                'comment' => ['message', 'accent'],
                'reply' => ['message', 'accent'],
                'like' => ['heart', 'accent'],
                'rate' => ['star', 'accent'],
                'comment_reaction' => ['sparkles', 'accent'],
                'vote' => ['trophy', 'accent'],
            ][$n->data['kind'] ?? ''] ?? ['bell', 'muted'];
        @endphp
        <a class="card noti {{ $n->read_at === null ? 'is-unread' : '' }}" href="{{ url($n->data['url'] ?? '/') }}"
           style="flex-direction: row; gap: var(--space-3); padding: var(--space-3); align-items: center">
            <span class="noti-ico noti-{{ $tone }}"><x-icon :name="$icon" size="16" /></span>
            <span style="flex: 1; font-size: 13.5px; line-height: 1.5">{{ $n->data['message'] ?? '' }}</span>
            <span class="meta" style="flex: none">{{ $n->created_at->diffForHumans() }}</span>
        </a>
    @empty
        <div class="card" style="align-items: center; padding: var(--space-8); gap: var(--space-2)">
            <x-icon name="bell" size="28" style="color: var(--color-neutral-400)" />
            <span class="muted-i">Chưa có thông báo nào.</span>
        </div>
    @endforelse

    @include('partials.pager', ['p' => $notifications])
</div>
@endsection
