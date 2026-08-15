@extends('layouts.app')

@section('title', $user->name.' — TalentStage')

@section('content')
{{-- ── Dau trang ho so: chan dung 168px + ten/vai tro/gioi thieu + hanh dong ── --}}
<div class="profile-head" style="display: flex; gap: var(--space-6); align-items: flex-start; border-bottom: 1px solid var(--color-divider); padding-bottom: var(--space-6)">
    <div class="plate avatar-xl" style="flex: 0 0 168px">
        @if ($user->avatar)
            <img src="{{ asset('storage/'.$user->avatar) }}" alt="{{ $user->name }}">
        @else
            {{ mb_substr($user->name, 0, 1) }}
        @endif
    </div>

    <div style="display: flex; flex-direction: column; gap: var(--space-2); flex: 1; min-width: 0">
        <div style="display: flex; align-items: center; gap: var(--space-2); flex-wrap: wrap">
            <h1 style="font-size: 30px; margin: 0; line-height: 1.15">{{ $user->name }}</h1>
            <span class="tag tag-accent" style="font-size: 10.5px">{{ ucfirst($user->role) }}</span>
            @if (! $user->is_active)
                <span class="tag tag-muted" style="font-size: 10.5px">Tài khoản bị khóa</span>
            @endif
        </div>
        @if ($user->location)
            <span class="meta" style="display: inline-flex; align-items: center; gap: 4px"><x-icon name="globe" size="13" /> {{ $user->location }}</span>
        @endif
        @if ($user->bio)
            <p style="margin: 0; max-width: 620px; font-size: 13.5px; line-height: 1.6; text-align: justify">{{ $user->bio }}</p>
        @endif
        @if ($user->achievements)
            <p class="meta" style="margin: 0; max-width: 620px; display: flex; gap: 6px; align-items: flex-start"><x-icon name="award" size="14" style="color: var(--color-accent); margin-top: 1px" /> <span>{{ $user->achievements }}</span></p>
        @endif
        <div style="display: flex; gap: var(--space-8); padding-top: var(--space-2); flex-wrap: wrap">
            @foreach ($stats as $st)
                <div class="stat">
                    <span class="stat-n">{{ $st['n'] }}</span>
                    <span class="stat-k">{{ $st['k'] }}</span>
                </div>
            @endforeach
        </div>
    </div>

    <div style="display: flex; flex-direction: column; gap: var(--space-2)">
        @auth
            @if (auth()->id() !== $user->id)
                <form method="POST" action="{{ route('follows.toggle', $user) }}">
                    @csrf
                    <button type="submit" class="btn {{ $isFollowing ? 'btn-secondary' : 'btn-primary' }} btn-sm" style="width: 100%">
                        @if ($isFollowing)<x-icon name="check" size="14" /> Đang theo dõi @else <x-icon name="user-plus" size="14" /> Theo dõi @endif
                    </button>
                </form>
                @if (in_array($user->role, ['creator', 'mentor']) && in_array(auth()->user()->role, ['creator', 'mentor']))
                    <a class="btn btn-secondary btn-sm" href="{{ route('messages.show', $user) }}"><x-icon name="message" size="14" /> Nhắn tin</a>
                @endif
            @else
                <a class="btn btn-primary btn-sm" href="{{ route('profile.edit') }}"><x-icon name="pencil" size="14" /> Sửa hồ sơ</a>
                @if ($user->isCreator())
                    <a class="btn btn-secondary btn-sm" href="{{ route('videos.mine') }}">Tiết mục của tôi</a>
                @endif
            @endif
        @else
            <a class="btn btn-primary btn-sm" href="{{ route('login') }}">Đăng nhập để theo dõi</a>
        @endauth
    </div>
</div>

{{-- ── Luoi video ── --}}
<div style="display: flex; flex-direction: column; gap: var(--space-3)">
    <h3 style="font-size: 20px; margin: 0">
        Video của {{ $user->name }}
        @if ($ownProfile)
            <span class="muted-i" style="font-size: 12px">(bạn thấy cả video chưa duyệt và riêng tư)</span>
        @endif
    </h3>
    <div class="grid-4">
        @forelse ($videos as $video)
            <a class="card video-card" href="{{ route('videos.show', $video) }}" style="--cat: {{ $video->category->colorVar() }}">
                <div class="video-thumb">@include('partials.thumb', ['video' => $video])</div>
                <div class="video-card-body">
                    <span class="video-card-cat">{{ $video->category->name }}</span>
                    <span class="video-card-title">{{ $video->title }}</span>
                    <span class="meta">{{ number_format($video->views) }} lượt xem · ♡ {{ number_format($video->likes_count) }}</span>
                    @if ($ownProfile && ($video->status !== 'approved' || $video->privacy === 'private'))
                        <span class="tag tag-status" data-status="{{ $video->status }}" style="align-self: flex-start">
                            {{ ['pending' => 'Chờ duyệt', 'approved' => 'Đã duyệt', 'rejected' => 'Từ chối'][$video->status] }}{{ $video->privacy === 'private' ? ' · Riêng tư' : '' }}
                        </span>
                    @endif
                </div>
            </a>
        @empty
            <div class="card" style="grid-column: 1 / -1; align-items: center; padding: var(--space-6)">
                <span class="muted-i">Chưa có video nào.</span>
            </div>
        @endforelse
    </div>
</div>
@endsection

@push('scripts')
<style>
    @media (max-width: 1080px) { .profile-head { flex-direction: column; } }
</style>
@endpush
