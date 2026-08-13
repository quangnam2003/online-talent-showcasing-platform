@extends('layouts.app')

@section('title', 'Quản lý cuộc thi — TalentStage Admin')

@section('screen-kicker', 'Admin · FR7 Contest')
@section('screen-title', 'Quản lý cuộc thi')
@section('screen-sub', '3 mốc thời gian điều khiển máy trạng thái: nộp bài → bình chọn → công bố')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center">
    <span class="meta">{{ $contests->count() }} cuộc thi</span>
    <a class="btn btn-primary btn-sm" href="{{ route('admin.contests.create') }}">+ Tạo cuộc thi</a>
</div>

<table class="table" style="font-size: 13px">
    <thead><tr><th>Cuộc thi</th><th>Mở nộp</th><th>Hạn nộp</th><th>Kết thúc</th><th>Bài dự thi</th><th>Trạng thái</th><th></th></tr></thead>
    <tbody>
        @foreach ($contests as $contest)
            <tr>
                <td><a href="{{ route('contests.show', $contest) }}">{{ $contest->title }}</a></td>
                <td class="num" style="color: var(--color-neutral-600)">{{ $contest->start_at->format('d/m/Y H:i') }}</td>
                <td class="num" style="color: var(--color-neutral-600)">{{ $contest->submission_deadline->format('d/m/Y H:i') }}</td>
                <td class="num" style="color: var(--color-neutral-600)">{{ $contest->end_at->format('d/m/Y H:i') }}</td>
                <td class="num">{{ $contest->entries_count }}</td>
                <td><span class="tag tag-outline" style="font-size: 9.5px">{{ $contest->statusLabel() }}</span></td>
                <td style="text-align: right">
                    <div style="display: inline-flex; gap: var(--space-1)">
                        <a class="btn btn-secondary btn-xs" href="{{ route('admin.contests.edit', $contest) }}">Sửa</a>
                        <form method="POST" action="{{ route('admin.contests.destroy', $contest) }}"
                              onsubmit="return confirm('Xóa cuộc thi \"{{ $contest->title }}\" (bao gồm bài dự thi + phiếu bầu)?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-ghost btn-xs" style="color: var(--color-danger)">Xóa</button>
                        </form>
                    </div>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
@endsection
