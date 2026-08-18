@extends('layouts.app')

@section('title', 'Người dùng — TalentStage Admin')

@section('screen-kicker')<a href="{{ route('admin.dashboard') }}">Quản trị</a><span class="sep">/</span><span>Người dùng</span>@endsection
@section('screen-title', 'Người dùng')
@section('screen-sub', 'Tìm kiếm, khóa hoặc mở khóa tài khoản — tài khoản bị khóa không thể đăng nhập.')

@section('content')
<form method="GET" action="{{ route('admin.users.index') }}" style="display: flex; gap: var(--space-2); max-width: 420px">
    <input class="input" type="search" name="q" value="{{ $q }}" placeholder="Tìm theo tên hoặc email…">
    <button class="btn btn-secondary btn-sm">Tìm</button>
</form>

<div class="table-wrap">
<table class="table" style="font-size: 13px">
    <thead>
        <tr><th>#</th><th>Người dùng</th><th>Email</th><th>Vai trò</th><th>Video</th><th>Người theo dõi</th><th>Trạng thái</th><th></th></tr>
    </thead>
    <tbody>
        @foreach ($users as $user)
            <tr style="{{ $user->is_active ? '' : 'opacity: .55' }}">
                <td class="num">{{ $user->id }}</td>
                <td>
                    <a href="{{ route('users.show', $user) }}" style="display: inline-flex; align-items: center; gap: var(--space-2)">
                        <span class="avatar" style="width: 24px; height: 24px; font-size: 11px">
                            @if ($user->avatar)
                                <img src="{{ asset('storage/'.$user->avatar) }}" alt="">
                            @else
                                {{ mb_substr($user->name, 0, 1) }}
                            @endif
                        </span>
                        {{ $user->name }}
                    </a>
                </td>
                <td style="color: var(--color-neutral-700)">{{ $user->email }}</td>
                <td><span class="tag {{ $user->isAdmin() ? 'tag-accent' : ($user->isMentor() ? 'tag-outline' : 'tag-muted') }}" style="font-size: 9.5px">{{ ucfirst($user->role) }}</span></td>
                <td class="num">{{ $user->videos_count }}</td>
                <td class="num">{{ number_format($user->followers_count) }}</td>
                <td>
                    <span class="tag {{ $user->is_active ? 'tag-ok' : 'tag-bad' }}">
                        {{ $user->is_active ? 'Hoạt động' : 'Bị khóa' }}
                    </span>
                </td>
                <td style="text-align: right">
                    @if ($user->id !== auth()->id())
                        <form method="POST" action="{{ route('admin.users.toggleActive', $user) }}"
                              onsubmit="return confirm('{{ $user->is_active ? 'Khóa' : 'Mở khóa' }} tài khoản {{ $user->name }}?')">
                            @csrf @method('PATCH')
                            <button class="btn {{ $user->is_active ? 'btn-ghost' : 'btn-secondary' }} btn-xs" style="{{ $user->is_active ? 'color: var(--color-danger)' : '' }}">
                                {{ $user->is_active ? 'Khóa' : 'Mở khóa' }}
                            </button>
                        </form>
                    @else
                        <span class="muted-i" style="font-size: 10.5px">bạn</span>
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
</div>

@include('partials.pager', ['p' => $users])
@endsection
