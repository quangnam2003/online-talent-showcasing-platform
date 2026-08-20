@extends('layouts.app')

@section('title', ($activeGroup?->name ?? 'Nhóm').' — TalentStage')

@section('screen-title', 'Nhóm')
@section('screen-sub', 'Tham gia nhóm theo sở thích, trao đổi và học hỏi cùng những người có chung đam mê.')

@section('content')
@php
    $me = auth()->user();
    $isCM = $me && ($me->isCreator() || $me->isMentor());
    $canManage = $canManage ?? false;
@endphp

<div class="groups-grid" style="display: grid; grid-template-columns: 300px 1fr; gap: var(--space-6); align-items: start">

    {{-- ── Cot trai: danh sach nhom ── --}}
    <div style="display: flex; flex-direction: column; gap: var(--space-2)">
        <form method="GET" action="{{ route('groups.index') }}">
            <input class="input" type="search" name="q" value="{{ $q }}" placeholder="Tìm nhóm…" style="font-size: 13px">
        </form>

        @forelse ($groups as $g)
            <a class="card" href="{{ route('groups.show', $g) }}"
               style="padding: var(--space-3); gap: var(--space-1); text-decoration: none; color: inherit; {{ $activeGroup?->id === $g->id ? 'border-left: 2px solid var(--color-accent); background: var(--color-accent-100)' : '' }}">
                <span style="display: flex; align-items: center; gap: 6px">
                    <span style="font-family: var(--font-heading); font-weight: 600; font-size: 16px; min-width: 0">{{ $g->name }}</span>
                    @if ($g->is_member ?? false)
                        <span title="Bạn là thành viên" style="display: inline-flex; color: var(--c-sport); flex: none"><x-icon name="check" size="14" /></span>
                    @endif
                </span>
                <span class="meta">
                    {{ number_format($g->members_count) }} thành viên{{ ($g->is_member ?? false) ? ' · Đã tham gia' : '' }}
                </span>
            </a>
        @empty
            <span class="muted-i">Chưa có nhóm nào{{ $q ? ' khớp «'.$q.'»' : '' }}.</span>
        @endforelse

        @if ($isCM)
            <a class="btn btn-secondary btn-sm" href="{{ route('groups.create') }}"><x-icon name="plus" size="14" /> Tạo nhóm mới</a>
        @elseif (! $me)
            <span class="muted-i"><a href="{{ route('login') }}">Đăng nhập</a> để tham gia hoặc tạo nhóm.</span>
        @endif
    </div>

    {{-- ── Cot phai: nhom dang chon ── --}}
    <div style="display: flex; flex-direction: column; gap: var(--space-4)" data-reveal-scope>
        @if (! $activeGroup)
            <div class="card" style="align-items: center; padding: var(--space-8)">
                <span class="muted-i">Chọn một nhóm bên trái để xem bảng thảo luận.</span>
            </div>
        @else
            <div style="display: flex; align-items: flex-end; justify-content: space-between; border-bottom: 1px solid var(--color-divider); padding-bottom: var(--space-3); gap: var(--space-2); flex-wrap: wrap">
                <div>
                    <h3 style="font-size: 26px; margin: 0">{{ $activeGroup->name }}</h3>
                    <span class="meta">
                        {{ number_format($activeGroup->members_count) }} thành viên
                        · Chủ nhóm: <a href="{{ route('users.show', $activeGroup->owner) }}">{{ $activeGroup->owner->name }}</a>
                    </span>
                </div>
                @auth
                    <div style="display: flex; gap: var(--space-2); align-items: center">
                        @if ($canManage)
                            <button type="button" class="btn btn-secondary btn-xs" aria-expanded="false"
                                    onclick="tsToggle(this, '.group-manage')"><x-icon name="settings" size="13" /> Quản lý nhóm</button>
                        @endif
                        @if (($isMember ?? false) && $activeGroup->owner_id !== $me->id)
                            <form method="POST" action="{{ route('groups.leave', $activeGroup) }}">
                                @csrf @method('DELETE')
                                <button class="btn btn-ghost btn-xs">Rời nhóm</button>
                            </form>
                        @elseif ($activeGroup->owner_id === $me->id)
                            <span class="tag tag-accent" style="font-size: 10px">Bạn là chủ nhóm</span>
                        @elseif ($isCM && ! ($isMember ?? false))
                            <form method="POST" action="{{ route('groups.join', $activeGroup) }}">
                                @csrf
                                <button class="btn btn-primary btn-sm"><x-icon name="user-plus" size="14" /> Tham gia nhóm</button>
                            </form>
                        @endif
                    </div>
                @endauth
            </div>

            {{-- Panel quan ly (chu nhom / admin): sua ten + mo ta, xoa nhom --}}
            @if ($canManage)
                <div class="reveal group-manage">
                    <div class="reveal-inner">
                        <div class="card" style="padding: var(--space-4); gap: var(--space-3)">
                            <div class="card-kicker"><x-icon name="settings" size="12" /> Quản lý nhóm</div>
                            <form method="POST" action="{{ route('groups.update', $activeGroup) }}" style="display: flex; flex-direction: column; gap: var(--space-3)">
                                @csrf @method('PUT')
                                <label class="field">
                                    <span class="label-up">Tên nhóm</span>
                                    <input class="input @error('name') is-invalid @enderror" name="name" value="{{ old('name', $activeGroup->name) }}" required maxlength="100">
                                    @error('name') <span class="err-msg">{{ $message }}</span> @enderror
                                </label>
                                <label class="field">
                                    <span class="label-up">Mô tả</span>
                                    <textarea class="input" name="description" rows="3" maxlength="1000">{{ old('description', $activeGroup->description) }}</textarea>
                                </label>
                                <button type="submit" class="btn btn-primary btn-sm" style="align-self: flex-start"><x-icon name="check" size="14" /> Lưu thay đổi</button>
                            </form>
                            <div style="display: flex; align-items: center; gap: var(--space-3); border-top: 1px solid var(--color-divider); padding-top: var(--space-3)">
                                <form method="POST" action="{{ route('groups.destroy', $activeGroup) }}"
                                      onsubmit="return confirm('Xóa nhóm \'{{ $activeGroup->name }}\'? Toàn bộ bài đăng và danh sách thành viên sẽ mất.')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-ghost btn-xs" style="color: var(--color-danger)"><x-icon name="trash" size="13" /> Xóa nhóm</button>
                                </form>
                                <span class="muted-i">Xóa nhóm sẽ xóa cả bài đăng và danh sách thành viên — không hoàn tác được.</span>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if ($activeGroup->description)
                <p style="margin: 0; font-size: 13.5px; line-height: 1.6; color: var(--color-neutral-800); white-space: pre-line">{{ $activeGroup->description }}</p>
            @endif

            {{-- Thanh vien (usecase "Manage group members") --}}
            @if (($isMember ?? false) || $canManage)
                <div class="card" style="padding: var(--space-4); gap: var(--space-2)">
                    <div class="card-kicker"><x-icon name="users" size="12" /> Thành viên ({{ number_format($activeGroup->members_count) }})</div>
                    <div style="display: flex; flex-wrap: wrap; gap: var(--space-2)">
                        @foreach ($members as $member)
                            <span style="display: inline-flex; align-items: center; gap: 8px; border: 1px solid var(--color-divider); border-radius: 999px; padding: 3px 10px 3px 4px">
                                <a href="{{ route('users.show', $member) }}" style="display: inline-flex; align-items: center; gap: 8px; text-decoration: none; color: inherit">
                                    <span class="avatar" style="width: 24px; height: 24px; font-size: 11px">
                                        @if ($member->avatar)
                                            <img src="{{ asset('storage/'.$member->avatar) }}" alt="">
                                        @else
                                            {{ mb_substr($member->name, 0, 1) }}
                                        @endif
                                    </span>
                                    <span style="font-size: 12.5px">{{ $member->name }}</span>
                                </a>
                                @if ($member->id === $activeGroup->owner_id)
                                    <span class="tag tag-accent" style="font-size: 9px; padding: 1px 6px">Chủ nhóm</span>
                                @elseif ($canManage)
                                    <form method="POST" action="{{ route('groups.members.remove', [$activeGroup, $member]) }}"
                                          onsubmit="return confirm('Xóa {{ $member->name }} khỏi nhóm?')" style="display: inline-flex">
                                        @csrf @method('DELETE')
                                        <button style="all: unset; cursor: pointer; display: inline-flex; color: var(--color-neutral-500)"
                                                title="Xóa khỏi nhóm" aria-label="Xóa {{ $member->name }} khỏi nhóm"
                                                onmouseover="this.style.color='var(--color-danger)'" onmouseout="this.style.color='var(--color-neutral-500)'">
                                            <x-icon name="x" size="13" />
                                        </button>
                                    </form>
                                @endif
                            </span>
                        @endforeach
                        @if ($activeGroup->members_count > $members->count())
                            <span class="muted-i" style="align-self: center">+ {{ $activeGroup->members_count - $members->count() }} người khác</span>
                        @endif
                    </div>
                </div>
            @endif

            @if ($isMember ?? false)
                {{-- Form dang bai: textarea de viet nhieu dong --}}
                <form method="POST" action="{{ route('groups.posts.store', $activeGroup) }}" style="display: flex; flex-direction: column; gap: var(--space-2)">
                    @csrf
                    <textarea class="input" name="content" rows="3" required maxlength="2000"
                              placeholder="Chia sẻ điều gì đó với nhóm… (Enter để xuống dòng)">{{ old('content') }}</textarea>
                    @error('content') <span class="err-msg">{{ $message }}</span> @enderror
                    <button class="btn btn-primary btn-sm" style="align-self: flex-end"><x-icon name="send" size="14" /> Đăng</button>
                </form>
            @endif

            @if (($isMember ?? false) || $canManage)
                @forelse ($posts as $post)
                    <div class="card" style="padding: var(--space-4); flex-direction: row; gap: var(--space-3)">
                        <span class="avatar" style="width: 36px; height: 36px">
                            @if ($post->user->avatar)
                                <img src="{{ asset('storage/'.$post->user->avatar) }}" alt="">
                            @else
                                {{ mb_substr($post->user->name, 0, 1) }}
                            @endif
                        </span>
                        <div style="display: flex; flex-direction: column; gap: var(--space-1); min-width: 0; flex: 1">
                            <span style="font-size: 12.5px; display: flex; align-items: baseline; gap: 6px">
                                <a href="{{ route('users.show', $post->user) }}">{{ $post->user->name }}</a>
                                <span class="muted-i">· {{ $post->created_at->diffForHumans() }}</span>
                                @php $canDeletePost = $me && ($me->id === $post->user_id || $canManage); @endphp
                                @if ($canDeletePost)
                                    <form method="POST" action="{{ route('groups.posts.destroy', [$activeGroup, $post]) }}"
                                          onsubmit="return confirm('Xóa bài đăng này?')" style="margin-left: auto">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-ghost btn-xs" style="color: var(--color-danger); padding: 2px 6px">Xóa</button>
                                    </form>
                                @endif
                            </span>
                            {{-- pre-line: giu xuong dong nguoi dung go trong textarea --}}
                            <span style="font-size: 13.5px; line-height: 1.6; white-space: pre-line">{{ $post->content }}</span>
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
