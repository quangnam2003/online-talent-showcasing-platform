@extends('layouts.app')

@section('title', 'Dashboard — TalentStage Admin')

@section('screen-kicker')<span>Quản trị</span><span class="sep">/</span><span>Tổng quan</span>@endsection
@section('screen-title', 'Tổng quan')
@section('screen-sub', 'Số liệu toàn hệ thống và những việc cần xử lý.')

@section('content')
{{-- ── Chi so tong ── --}}
<div style="display: flex; gap: var(--space-8); flex-wrap: wrap; border-bottom: 1px solid var(--color-divider); padding-bottom: var(--space-4)">
    @foreach ($stats as $st)
        <div class="stat">
            <span class="stat-n">{{ number_format($st['n']) }}</span>
            <span class="stat-k">{{ $st['k'] }}</span>
        </div>
    @endforeach
</div>

<div class="grid-2" style="align-items: start">
    {{-- Cho duyet moi nhat --}}
    <div class="card" style="padding: var(--space-4); gap: var(--space-2)">
        <div class="card-kicker">Video chờ duyệt mới nhất</div>
        @forelse ($pendingLatest as $video)
            <div style="display: flex; justify-content: space-between; gap: var(--space-2); align-items: baseline; padding-bottom: 6px; border-bottom: 1px solid var(--color-divider)">
                <a href="{{ route('videos.show', $video) }}" style="font-size: 13px">{{ \Illuminate\Support\Str::limit($video->title, 40) }}</a>
                <span class="meta">{{ $video->user->name }} · {{ $video->created_at->diffForHumans() }}</span>
            </div>
        @empty
            <span class="muted-i">Không có video chờ duyệt 🎉</span>
        @endforelse
        <a class="btn btn-primary btn-sm" style="align-self: flex-start" href="{{ route('admin.videos.index') }}">Vào hàng đợi kiểm duyệt <x-icon name="arrow-right" size="14" /></a>
    </div>

    {{-- Phan bo --}}
    <div style="display: flex; flex-direction: column; gap: var(--space-4)">
        <div class="card" style="padding: var(--space-4); gap: var(--space-2)">
            <div class="card-kicker">Người dùng theo vai trò</div>
            <table class="table" style="font-size: 13px">
                <tbody>
                    @foreach (['admin' => 'Admin', 'creator' => 'Creator', 'mentor' => 'Mentor'] as $role => $label)
                        <tr><td>{{ $label }}</td><td class="num" style="text-align: right">{{ number_format($roleCounts[$role] ?? 0) }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card" style="padding: var(--space-4); gap: var(--space-2)">
            <div class="card-kicker">Video theo trạng thái</div>
            <table class="table" style="font-size: 13px">
                <tbody>
                    @foreach (['pending' => 'Chờ duyệt', 'approved' => 'Đã duyệt', 'rejected' => 'Từ chối'] as $s => $label)
                        <tr><td>{{ $label }}</td><td class="num" style="text-align: right">{{ number_format($statusCounts[$s] ?? 0) }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
