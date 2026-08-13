@extends('layouts.app')

@section('title', ($activeUser ? 'Chat với '.$activeUser->name : 'Tin nhắn').' — TalentStage')

@section('screen-kicker', 'FR6 · Mentorship')
@section('screen-title', 'Tin nhắn')
@section('screen-sub', 'Direct messages — creator ↔ mentor')

@section('content')
@php $me = auth()->user(); @endphp

<div class="msg-grid" style="display: grid; grid-template-columns: 280px 1fr; gap: var(--space-6); align-items: start">

    {{-- ── Cot trai: hoi thoai + bat dau moi (mockup Mentorship) ── --}}
    <div style="display: flex; flex-direction: column; gap: var(--space-2)">
        <div class="kicker" style="color: var(--color-neutral-500)">Hội thoại · Threads</div>

        @forelse ($threads as $t)
            <a class="card" href="{{ route('messages.show', $t->partner) }}"
               style="padding: var(--space-3); gap: 2px; text-decoration: none; color: inherit; border-left: 2px solid {{ $activeUser?->id === $t->partner->id ? 'var(--color-accent)' : 'transparent' }}; {{ $activeUser?->id === $t->partner->id ? 'background: var(--color-accent-100)' : '' }}">
                <span style="display: flex; justify-content: space-between; align-items: baseline; gap: var(--space-1)">
                    <span style="font-size: 13.5px">{{ $t->partner->name }}</span>
                    @if ($t->unread > 0)
                        <span class="tag tag-accent num" style="font-size: 9.5px">{{ $t->unread }}</span>
                    @endif
                </span>
                <span class="meta" style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap">
                    {{ $t->last->sender_id === $me->id ? 'Bạn: ' : '' }}{{ \Illuminate\Support\Str::limit($t->last->content, 34) }}
                </span>
            </a>
        @empty
            <span class="muted-i">Chưa có hội thoại nào.</span>
        @endforelse

        <div class="card" style="padding: var(--space-3); gap: var(--space-2); margin-top: var(--space-2)">
            <div class="card-kicker">Bắt đầu hội thoại · New</div>
            @foreach ($contacts as $contact)
                <a href="{{ route('messages.show', $contact) }}" style="display: flex; align-items: center; gap: var(--space-2); font-size: 12.5px">
                    <span class="avatar" style="width: 24px; height: 24px; font-size: 11px">
                        @if ($contact->avatar)
                            <img src="{{ asset('storage/'.$contact->avatar) }}" alt="">
                        @else
                            {{ mb_substr($contact->name, 0, 1) }}
                        @endif
                    </span>
                    {{ $contact->name }}
                    <span class="tag {{ $contact->isMentor() ? 'tag-accent' : 'tag-muted' }}" style="font-size: 9px; margin-left: auto">{{ ucfirst($contact->role) }}</span>
                </a>
            @endforeach
        </div>
    </div>

    {{-- ── Cot phai: khung chat ── --}}
    @if (! $activeUser)
        <div class="card" style="align-items: center; justify-content: center; min-height: 380px">
            <span class="muted-i">Chọn hội thoại bên trái, hoặc bắt đầu hội thoại mới.</span>
        </div>
    @else
        <div class="card" style="padding: var(--space-4); gap: var(--space-3); min-height: 480px; display: flex; flex-direction: column">
            <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--color-divider); padding-bottom: var(--space-2)">
                <a href="{{ route('users.show', $activeUser) }}" style="font-family: var(--font-heading); font-size: 20px; text-decoration: none; color: inherit">
                    {{ $activeUser->name }}
                    <span class="tag {{ $activeUser->isMentor() ? 'tag-accent' : 'tag-muted' }}" style="font-size: 9.5px">{{ ucfirst($activeUser->role) }}</span>
                </a>
                <a class="btn btn-ghost btn-xs" href="{{ route('users.show', $activeUser) }}">Xem hồ sơ</a>
            </div>

            <div id="chatScroll" style="display: flex; flex-direction: column; gap: var(--space-2); flex: 1; overflow-y: auto; max-height: 460px; padding-right: 4px">
                @forelse ($messages as $m)
                    <div class="bubble {{ $m->sender_id === $me->id ? 'me' : 'them' }}">
                        <div>{{ $m->content }}</div>
                        <div class="at">
                            {{ $m->created_at->format('d/m H:i') }}
                            @if ($m->sender_id === $me->id && $m->isRead()) · đã xem @endif
                        </div>
                    </div>
                @empty
                    <span class="muted-i" style="align-self: center; margin-top: var(--space-6)">
                        Chưa có tin nhắn — gửi lời chào đầu tiên tới {{ $activeUser->name }}!
                    </span>
                @endforelse
            </div>

            <form method="POST" action="{{ route('messages.store', $activeUser) }}" style="display: flex; gap: var(--space-2)">
                @csrf
                <input class="input" name="content" placeholder="Nhắn tin… / Message" required autofocus style="flex: 1" autocomplete="off">
                <button class="btn btn-primary btn-sm">Gửi</button>
            </form>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<style>
    @media (max-width: 1080px) { .msg-grid { grid-template-columns: 1fr !important; } }
</style>
<script>
    // Cuon xuong tin moi nhat
    const box = document.getElementById('chatScroll');
    if (box) box.scrollTop = box.scrollHeight;
</script>
@endpush
