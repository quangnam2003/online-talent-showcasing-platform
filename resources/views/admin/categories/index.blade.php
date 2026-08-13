@extends('layouts.app')

@section('title', 'Danh mục — TalentStage Admin')

@section('screen-kicker', 'Admin · Categories')
@section('screen-title', 'Quản lý danh mục')
@section('screen-sub', 'Thể loại tài năng — không thể xóa danh mục đang có video')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center">
    <span class="meta">{{ $categories->count() }} danh mục</span>
    <a class="btn btn-primary btn-sm" href="{{ route('admin.categories.create') }}">+ Thêm danh mục</a>
</div>

<table class="table" style="font-size: 13px; max-width: 720px">
    <thead><tr><th>Tên</th><th>Slug</th><th>Số video</th><th></th></tr></thead>
    <tbody>
        @foreach ($categories as $cat)
            <tr>
                <td>{{ $cat->name }}</td>
                <td style="color: var(--color-neutral-600)"><code style="font-size: 11px">{{ $cat->slug }}</code></td>
                <td class="num">{{ $cat->videos_count }}</td>
                <td style="text-align: right">
                    <div style="display: inline-flex; gap: var(--space-1)">
                        <a class="btn btn-secondary btn-xs" href="{{ route('admin.categories.edit', $cat) }}">Sửa</a>
                        <form method="POST" action="{{ route('admin.categories.destroy', $cat) }}"
                              onsubmit="return confirm('Xóa danh mục \"{{ $cat->name }}\"?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-ghost btn-xs" style="color: var(--color-danger)" {{ $cat->videos_count > 0 ? 'disabled title=Đang-có-video' : '' }}>Xóa</button>
                        </form>
                    </div>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
@endsection
