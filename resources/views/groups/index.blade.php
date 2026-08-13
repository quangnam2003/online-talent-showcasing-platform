@extends('layouts.app')

@section('title', ($activeGroup?->name ?? 'Nhóm').' — TalentStage')

@section('screen-kicker', 'FR5 · Groups')
@section('screen-title', 'Nhóm & thảo luận')
@section('screen-sub', 'Groups — browse, join, post in members-only board')

@section('content')
@php $me = auth()->user(); @endphp

<div class="groups-grid" style="display: grid; grid-template-columns: 300px 1fr; gap: var(--space-6); align-items: start">

    {{-- ── Cot trai: danh sach nhom (mockup Groups) ── --}}
    <div style="display: flex; flex-direction: column; gap: var(--space-2)">
        <form method="GET" action="{{ route('groups.index') }}">
            <input class="input" type="search" name="q" value="{{ $q }}" placeholder="Tìm nhóm · Browse groups" style="font-size: 13px">
        </form>

        @forelse ($groups as $g)
            <a class="card" href="{{ route('groups.show', $g) }}"
               style="padding: var(--space-3); gap: var(--space-1); text-decoration: none; color: inherit; {{ $activeGroup?->id === $g->id ? 'border-left: 2px solid var(--color-accent); background: var(--color-accent-100)' : '' }}">
                <span style="font-family: var(--font-heading); font-weight: 600; font-size: 16px">{{ $g->name }}</span>
                <span class="meta">{{ number_format($g->members_count) }} thành viên</span>
            </a>
        @empty
            <span class="muted-i">Chưa có nhóm nào{{ $q ? ' khớp «'.$q.'»' : '' }}.</span>
        @endforelse

        @auth
            <a class="btn btn-secondary btn-sm" href="{{ route('groups.create') }}">+ Tạo nhóm · Create group</a>
        @else
            <span class="muted-i"><a href="{{ route('login') }}">Đăng nhập</a> để tham gia hoặc tạo nhóm.</span>
        @endauth
    </div>

    {{-- ── Cot phai: bang thao luan cua nhom dang chon ── --}}
    <div style="display: flex; flex-direction: column; gap: var(--space-4)">
        @if (! $activeGroup)
            <div class="card" style="align-items: center; padding: var(--space-8)">
                <span class="muted-i">Chọn một nhóm bên trái để xem bảng thảo luận.</span>
            </div>
        @else
            <div style="display: flex; align-items: flex-end; justify-content: space-between; border-bottom: 1px solid var(--color-divider); padding-bottom: var(--space-3); gap: var(--space-2); flex-wrap: wrap">
                <div>
                    <h3 style="font-weight: 400; font-size: 26px; margin: 0">{{ $activeGroup->name }}</h3>
                    <span class="meta">
                        {{ number_format($activeGroup->members_count) }} thành viên
                        · Chủ nhóm: <a href="{{ route('users.show', $activeGroup->owner) }}">{{ $activeGroup->owner->name }}</a>
                    </span>
                </div>
                @auth
                    <div style="display: flex; gap: var(--space-2)">
                        @if (($isMember ?? false))
                            @if ($activeGroup->owner_id !== $me->id)
                                <form method="POST" action="{{ route('groups.leave', $activeGroup) }}">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-ghost btn-xs">Rời nhóm</button>
                                </form>
                            @else
                                <span class="tag tag-accent" style="font-size: 10px">Bạn là chủ nhóm</span>
                            @endif
                        @else
                            <form method="POST" action="{{ route('groups.join', $activeGroup) }}">
                                @csrf
                                <button class="btn btn-primary btn-sm">Tham gia · Join</button>
                            </form>
                        @endif
                    </div>
                @endauth
            </div>

            @if ($activeGroup->description)
                <p style="margin: 0; font-size: 13.5px; line-height: 1.6; text-align: justify; color: var(--color-neutral-800)">{{ $activeGroup->description }}</p>
            @endif

            @if ($isMember ?? false)
                {{-- Form dang bai inline (members-only) --}}
                <form method="POST" action="{{ route('groups.posts.store', $activeGroup) }}" style="display: flex; gap: var(--space-2)">
                    @csrf
                    <input class="input" name="content" placeholder="Đăng bài trong nhóm… / Post in group discussion" required style="flex: 1">
                    <button class="btn btn-primary btn-sm">Đăng</button>
                </form>

                @forelse ($posts as $post)
                    <div class="card" style="padding: var(--space-4); flex-direction: row; gap: var(--space-3)">
                        <span class="avatar" style="width: 36px; height: 36px">
                            @if ($post->user->avatar)
                                <img src="{{ asset('storage/'.$post->user->avatar) }}" alt="">
                            @else
                                {{ mb_substr($post->user->name, 0, 1) }}
                            @endif
                        </span>
                        <div style="display: flex; flex-direction: column; gap: var(--space-1); min-width: 0">
                            <span style="font-size: 12.5px">
                                <a href="{{ route('users.show', $post->user) }}">{{ $post->user->name }}</a>
                                <span class="muted-i">· {{ $post->created_at->diffForHumans() }}</span>
                            </span>
                            <span style="font-size: 13.5px; line-height: 1.6; text-align: justify">{{ $post->content }}</span>
                        </div>
                    </div>
                @empty
                    <span class="muted-i">Nhóm chưa có bài đăng nào — hãy mở đầu cuộc thảo luận!</span>
                @endforelse
            @else
                {{-- Nguoi ngoai: chi thay mo ta + loi moi --}}
                <div class="card" style="align-items: center; padding: var(--space-6); gap: var(--space-2)">
                    <span class="muted-i">Bảng thảo luận chỉ dành cho thành viên nhóm.</span>
                    @guest
                        <a class="btn btn-primary btn-sm" href="{{ route('login') }}">Đăng nhập để tham gia</a>
                    @endguest
                </div>
            @endif
        @endif
    </div>
</div>
@endsection

@push('scripts')
<style>
    @media (max-width: 1080px) { .groups-grid { grid-template-columns: 1fr !important; } }
</style>
@endpush
