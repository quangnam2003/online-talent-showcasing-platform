@extends('layouts.app')

@section('title', $video->title.' — TalentStage')

@section('content')
@php $me = auth()->user(); @endphp

@if ($video->status !== 'approved')
    <div class="flash flash-error">
        <x-icon name="info" size="16" />
        <span>Tiết mục đang ở trạng thái <strong>{{ ['pending' => 'chờ duyệt', 'rejected' => 'bị từ chối'][$video->status] ?? $video->status }}</strong> — chỉ bạn và quản trị viên nhìn thấy trang này.@if ($video->status === 'pending') Quản trị viên đã được thông báo; bạn sẽ nhận kết quả qua chuông thông báo.@endif</span>
    </div>
@elseif ($video->privacy === 'private')
    <div class="flash">
        <x-icon name="lock" size="16" />
        <span>Tiết mục ở chế độ <strong>riêng tư</strong> — chỉ bạn và quản trị viên xem được.</span>
    </div>
@endif

<div class="watch-grid" style="display: grid; grid-template-columns: 1.9fr 1fr; gap: var(--space-6); align-items: start">

    {{-- ══ Cot trai: player + tuong tac + binh luan ══ --}}
    <div style="display: flex; flex-direction: column; gap: var(--space-4)">

        @php $hasFile = $video->file_path && file_exists(public_path('storage/'.$video->file_path)); @endphp
        @if ($hasFile && $video->isAudio())
            {{-- Tiet muc am thanh: cover mau the loai (hoac anh bia) + trinh phat audio --}}
            <div class="player player-audio" style="--cat: {{ $video->category->colorVar() }}">
                @if ($video->thumbnail && file_exists(public_path('storage/'.$video->thumbnail)))
                    <img class="player-audio-cover" src="{{ asset('storage/'.$video->thumbnail) }}" alt="">
                @else
                    <span class="player-audio-art" aria-hidden="true"><x-icon name="mic" size="72" /></span>
                @endif
                <span class="thumb-badge thumb-badge-audio player-audio-tag"><x-icon name="mic" size="11" /> Bản thu âm</span>
                <audio controls preload="metadata" src="{{ asset('storage/'.$video->file_path) }}" @if ($video->mime_type) type="{{ $video->mime_type }}" @endif>
                    Trình duyệt không hỗ trợ phát âm thanh.
                </audio>
            </div>
        @else
            <div class="player">
                @if ($hasFile)
                    <video controls preload="metadata"
                           @if ($video->thumbnail && file_exists(public_path('storage/'.$video->thumbnail))) poster="{{ asset('storage/'.$video->thumbnail) }}" @endif>
                        <source src="{{ asset('storage/'.$video->file_path) }}" @if ($video->mime_type) type="{{ $video->mime_type }}" @endif>
                        Trình duyệt không hỗ trợ phát video.
                    </video>
                @else
                    <div class="player-empty">
                        <x-icon name="video-off" size="36" />
                        <span>Tiết mục này hiện chưa có tệp phát</span>
                    </div>
                @endif
            </div>
        @endif

        <div style="display: flex; flex-direction: column; gap: var(--space-2)">
            <h1 style="font-size: 32px; font-weight: 700; margin: 0; line-height: 1.2">{{ $video->title }}</h1>

            {{-- hang: tac gia + follow + like/share --}}
            <div class="watch-actions" style="display: flex; align-items: center; gap: var(--space-4); padding-bottom: var(--space-3); border-bottom: 1px solid var(--color-divider); flex-wrap: wrap">
                <a href="{{ route('users.show', $video->user) }}" style="display: flex; align-items: center; gap: var(--space-2); text-decoration: none; color: inherit">
                    <span class="avatar avatar-lg">
                        @if ($video->user->avatar)
                            <img src="{{ asset('storage/'.$video->user->avatar) }}" alt="">
                        @else
                            {{ mb_substr($video->user->name, 0, 1) }}
                        @endif
                    </span>
                    <span style="display: flex; flex-direction: column">
                        <span style="font-size: 14px">{{ $video->user->name }}</span>
                        <span class="meta">{{ number_format($video->user->followers_count) }} người theo dõi</span>
                    </span>
                </a>

                @auth
                    @if ($me->id !== $video->user_id)
                        <form method="POST" action="{{ route('follows.toggle', $video->user) }}">
                            @csrf
                            <button class="btn btn-secondary btn-sm">@if ($isFollowing)<x-icon name="check" size="14" /> Đang theo dõi @else <x-icon name="user-plus" size="14" /> Theo dõi @endif</button>
                        </form>
                    @endif
                @endauth

                <div style="margin-left: auto; display: flex; gap: var(--space-2); align-items: center">
                    @auth
                        <form method="POST" action="{{ route('reactions.store', $video) }}">
                            @csrf
                            <input type="hidden" name="action" value="like">
                            <button class="btn btn-ghost btn-sm num btn-like" aria-pressed="{{ $myReaction?->liked ? 'true' : 'false' }}">
                                <x-icon name="heart" size="15" style="{{ $myReaction?->liked ? 'fill: currentColor' : '' }}" /> Thích <span class="num">{{ number_format($video->likes_count) }}</span>
                            </button>
                        </form>
                    @else
                        <span class="btn btn-ghost btn-sm num" style="cursor: default"><x-icon name="heart" size="15" /> Thích {{ number_format($video->likes_count) }}</span>
                    @endauth
                    <span class="meta" style="display: inline-flex; align-items: center; gap: 4px"><x-icon name="eye" size="14" /> <span class="num">{{ number_format($video->views) }}</span> lượt xem</span>
                    @if ($video->durationLabel())
                        <span class="meta" style="display: inline-flex; align-items: center; gap: 4px"><x-icon name="clock" size="14" /> <span class="num">{{ $video->durationLabel() }}</span></span>
                    @endif
                </div>
            </div>

            {{-- hang: cham sao --}}
            <div style="display: flex; align-items: center; gap: var(--space-3); flex-wrap: wrap">
                <span class="label-up" style="font-size: 11px">Chấm điểm</span>
                {{-- .stars-hover: re chuot xem truoc so sao se cham; .star[data-on] = sao dang sang --}}
                <div class="stars @auth stars-hover @endauth" role="group" aria-label="Chấm điểm">
                    @for ($n = 1; $n <= 5; $n++)
                        @auth
                            <form method="POST" action="{{ route('reactions.store', $video) }}">
                                @csrf
                                <input type="hidden" name="action" value="rate">
                                <input type="hidden" name="stars" value="{{ $n }}">
                                <button class="star" @if ($n <= ($myReaction?->stars ?? 0)) data-on @endif
                                        aria-label="Chấm {{ $n }} sao" title="{{ $n }} sao"></button>
                            </form>
                        @else
                            <span class="star" @if ($n <= round($video->avg_rating)) data-on @endif aria-hidden="true"></span>
                        @endauth
                    @endfor
                </div>
                <a class="cat-chip" style="--cat: {{ $video->category->colorVar() }}; margin-left: auto"
                   href="{{ url('/explore?category='.$video->category->slug) }}">{{ $video->category->name }}</a>
            </div>

            {{-- dong rieng duoi hang cham sao: diem trung binh + tong luot danh gia --}}
            @if ($ratingsCount > 0)
                <span class="meta">Trung bình <span class="num">{{ number_format($video->avg_rating, 1) }}</span> / 5 · <span class="num">{{ number_format($ratingsCount) }}</span> lượt đánh giá</span>
            @else
                <span class="meta">Chưa có lượt đánh giá</span>
            @endif

            @if ($video->description)
                <p style="margin: var(--space-2) 0 0; font-size: 17px; font-weight: 700; line-height: 1.6; text-align: justify; color: var(--color-neutral-800)">{{ $video->description }}</p>
            @endif
        </div>

        {{-- ── Binh luan ── --}}
        <div style="display: flex; flex-direction: column; gap: var(--space-3)">
            <h3 style="font-size: 19px; margin: 0" class="num">{{ number_format($video->comments_count) }} bình luận</h3>

            @if ($video->allow_comments)
                @auth
                    <form method="POST" action="{{ route('comments.store', $video) }}" style="display: flex; gap: var(--space-2)">
                        @csrf
                        <input class="input" name="content" placeholder="Viết bình luận…" required style="flex: 1">
                        <button class="btn btn-primary btn-sm">Gửi</button>
                    </form>
                @else
                    <span class="muted-i"><a href="{{ route('login') }}">Đăng nhập</a> để bình luận.</span>
                @endauth
            @else
                <span class="muted-i">Chủ video đã tắt bình luận.</span>
            @endif

            @foreach ($comments as $comment)
                <div style="display: flex; gap: var(--space-3); padding-bottom: var(--space-3); border-bottom: 1px solid var(--color-divider)">
                    <span class="avatar">
                        @if ($comment->user->avatar)
                            <img src="{{ asset('storage/'.$comment->user->avatar) }}" alt="">
                        @else
                            {{ mb_substr($comment->user->name, 0, 1) }}
                        @endif
                    </span>
                    <div style="display: flex; flex-direction: column; gap: var(--space-1); min-width: 0; flex: 1" data-reveal-scope>
                        <span style="font-size: 12.5px">
                            <a href="{{ route('users.show', $comment->user) }}">{{ $comment->user->name }}</a>
                            @if ($comment->user->isMentor()) <span class="tag tag-accent" style="font-size: 9px">Mentor</span> @endif
                            <span class="muted-i">· {{ $comment->created_at->diffForHumans() }}</span>
                        </span>
                        <span style="font-size: 13.5px; line-height: 1.55">{{ $comment->content }}</span>

                        <div style="display: flex; gap: var(--space-3); align-items: center">
                            @auth
                                @if ($video->allow_comments)
                                    <button type="button" class="btn btn-ghost btn-xs" style="padding-left: 0"
                                            aria-expanded="false" onclick="tsToggle(this, '.reply-form')">
                                        Trả lời
                                    </button>
                                @endif
                                @if ($me->id === $comment->user_id || $me->isAdmin() || $me->id === $video->user_id)
                                    <form method="POST" action="{{ route('comments.destroy', $comment) }}" onsubmit="return confirm('Xóa bình luận này?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-ghost btn-xs" style="color: var(--color-danger)">Xóa</button>
                                    </form>
                                @endif
                            @endauth
                        </div>

                        @auth
                            {{-- panel tra loi: .reveal mo/dong muot (grid-rows 0fr → 1fr) --}}
                            <form method="POST" action="{{ route('comments.store', $video) }}" class="reveal reply-form" style="margin-top: calc(var(--space-1) * -1)">
                                @csrf
                                <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                                <div class="reveal-inner" style="display: flex; gap: var(--space-2); padding-top: calc(4px + var(--space-1))">
                                    <input class="input" name="content" placeholder="Trả lời {{ $comment->user->name }}…" required style="flex: 1; font-size: 12.5px; min-height: 32px">
                                    <button class="btn btn-primary btn-xs">Gửi</button>
                                </div>
                            </form>
                        @endauth

                        {{-- Tra loi 1 cap --}}
                        @foreach ($comment->replies as $reply)
                            <div style="display: flex; gap: var(--space-2); padding: var(--space-2) 0 0 var(--space-3); border-left: 2px solid var(--color-divider)">
                                <span class="avatar" style="width: 24px; height: 24px; font-size: 11px">
                                    @if ($reply->user->avatar)
                                        <img src="{{ asset('storage/'.$reply->user->avatar) }}" alt="">
                                    @else
                                        {{ mb_substr($reply->user->name, 0, 1) }}
                                    @endif
                                </span>
                                <div style="display: flex; flex-direction: column; gap: 2px; min-width: 0">
                                    <span style="font-size: 12px">
                                        <a href="{{ route('users.show', $reply->user) }}">{{ $reply->user->name }}</a>
                                        <span class="muted-i">· {{ $reply->created_at->diffForHumans() }}</span>
                                    </span>
                                    <span style="font-size: 13px; line-height: 1.5">{{ $reply->content }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- ══ Cot phai: nhan xet mentor + xem tiep ══ --}}
    <div style="display: flex; flex-direction: column; gap: var(--space-3)">
        @if ($mentorComment)
            <div class="card" style="padding: var(--space-4); gap: var(--space-2)">
                <div class="card-kicker"><x-icon name="graduation" size="12" /> Nhận xét từ mentor</div>
                <p style="margin: 0; font-size: 13px; line-height: 1.6; text-align: justify">{{ $mentorComment->content }}</p>
                <span class="muted-i">{{ $mentorComment->user->name }} · {{ $mentorComment->created_at->diffForHumans() }}</span>
            </div>
        @endif

        @if ($me && ($me->id === $video->user_id || $me->isAdmin()))
            <div class="card" style="padding: var(--space-4); gap: var(--space-2)">
                <div class="card-kicker">Quản lý video</div>
                <div style="display: flex; gap: var(--space-2)">
                    <a class="btn btn-secondary btn-xs" href="{{ route('videos.edit', $video) }}">Sửa</a>
                    <form method="POST" action="{{ route('videos.destroy', $video) }}" onsubmit="return confirm('Xóa video này?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-ghost btn-xs" style="color: var(--color-danger)">Xóa video</button>
                    </form>
                </div>
            </div>
        @endif

        <div class="kicker" style="color: var(--color-neutral-500)">Xem tiếp</div>
        @forelse ($upNext as $next)
            <a class="card" href="{{ route('videos.show', $next) }}" style="flex-direction: row; gap: var(--space-3); padding: var(--space-2); align-items: center; text-decoration: none; color: inherit">
                <div class="hatch-mid thumb-sm" style="width: 96px; height: 60px; flex: 0 0 96px">@include('partials.thumb', ['video' => $next, 'compact' => true])</div>
                <span style="display: flex; flex-direction: column; gap: 2px; min-width: 0">
                    <span style="font-size: 13px; line-height: 1.3">{{ $next->title }}</span>
                    <span class="meta">{{ $next->user->name }} · {{ number_format($next->views) }} lượt xem</span>
                </span>
            </a>
        @empty
            <span class="muted-i">Chưa có video khác.</span>
        @endforelse
    </div>
</div>
@endsection

@push('scripts')
<style>
    @media (max-width: 1080px) { .watch-grid { grid-template-columns: 1fr !important; } }
</style>
@endpush
