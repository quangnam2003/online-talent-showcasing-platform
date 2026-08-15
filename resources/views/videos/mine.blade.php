@extends('layouts.app')

@section('title', 'Video của tôi — TalentStage')

@section('screen-title', 'Video của tôi')
@section('screen-sub', 'Theo dõi trạng thái duyệt, chỉnh sửa hoặc xóa video bạn đã đăng.')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center">
    <span class="meta">{{ $videos->count() }} video</span>
    <a class="btn btn-primary btn-sm" href="{{ route('videos.create') }}"><x-icon name="upload" size="14" /> Đăng video mới</a>
</div>

<table class="table" style="font-size: 13px">
    <thead>
        <tr><th>Video</th><th>Thể loại</th><th>Quyền xem</th><th>Lượt xem</th><th>♡</th><th>Trạng thái</th><th>Gửi lúc</th><th></th></tr>
    </thead>
    <tbody>
        @forelse ($videos as $video)
            @php
                $t = ['pending' => 'Chờ duyệt', 'approved' => 'Đã duyệt', 'rejected' => 'Từ chối'][$video->status];
            @endphp
            <tr>
                <td><a href="{{ route('videos.show', $video) }}">{{ \Illuminate\Support\Str::limit($video->title, 40) }}</a></td>
                <td><span style="color: {{ $video->category->colorVar() }}; font-weight: 600; font-size: 12px">{{ $video->category->name }}</span></td>
                <td class="muted-i">{{ $video->privacy === 'public' ? 'Công khai' : 'Riêng tư' }}</td>
                <td class="num">{{ number_format($video->views) }}</td>
                <td class="num">{{ number_format($video->likes_count) }}</td>
                <td><span class="tag tag-status" data-status="{{ $video->status }}">{{ $t }}</span></td>
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
