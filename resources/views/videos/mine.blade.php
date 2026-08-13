@extends('layouts.app')

@section('title', 'Video của tôi — TalentStage')

@section('screen-kicker', 'FR2 · Content')
@section('screen-title', 'Video của tôi')
@section('screen-sub', 'My videos — trạng thái duyệt, sửa, xóa')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center">
    <span class="meta">{{ $videos->count() }} video</span>
    <a class="btn btn-primary btn-sm" href="{{ route('videos.create') }}">+ Đăng video mới</a>
</div>

<table class="table" style="font-size: 13px">
    <thead>
        <tr><th>Video</th><th>Thể loại</th><th>Quyền xem</th><th>Lượt xem</th><th>♡</th><th>Trạng thái</th><th>Gửi lúc</th><th></th></tr>
    </thead>
    <tbody>
        @forelse ($videos as $video)
            @php
                $c = ['pending' => 'var(--color-neutral-500)', 'approved' => 'var(--color-accent-700)', 'rejected' => 'var(--color-neutral-800)'][$video->status];
                $t = ['pending' => 'Chờ duyệt', 'approved' => 'Đã duyệt', 'rejected' => 'Từ chối'][$video->status];
            @endphp
            <tr>
                <td><a href="{{ route('videos.show', $video) }}">{{ \Illuminate\Support\Str::limit($video->title, 40) }}</a></td>
                <td>{{ $video->category->name }}</td>
                <td class="muted-i">{{ $video->privacy === 'public' ? 'Công khai' : 'Riêng tư' }}</td>
                <td class="num">{{ number_format($video->views) }}</td>
                <td class="num">{{ number_format($video->likes_count) }}</td>
                <td><span class="tag" style="font-size: 10px; border: 1px solid {{ $c }}; color: {{ $c }}">{{ $t }}</span></td>
                <td class="num" style="color: var(--color-neutral-600)">{{ $video->created_at->format('d/m H:i') }}</td>
                <td>
                    <div style="display: flex; gap: var(--space-1); justify-content: flex-end">
                        <a class="btn btn-secondary btn-xs" href="{{ route('videos.edit', $video) }}">Sửa</a>
                        <form method="POST" action="{{ route('videos.destroy', $video) }}" onsubmit="return confirm('Xóa video \"{{ $video->title }}\"?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-ghost btn-xs" style="color: var(--color-danger)">Xóa</button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="8" class="muted-i" style="text-align: center; padding: var(--space-6)">
                Bạn chưa có video nào — <a href="{{ route('videos.create') }}">đăng video đầu tiên</a>.
            </td></tr>
        @endforelse
    </tbody>
</table>
@endsection
