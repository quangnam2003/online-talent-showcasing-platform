{{--
    Mot binh luan (goc hoac tra loi) — dung chung cho ca hai cap.
    Bien: $comment, $video, $me, $isReply (bool)
    Moi binh luan la mot [data-reveal-scope] rieng de tsToggle mo dung panel Sua / Cam xuc / Tra loi cua no.
--}}
@php
    $canEdit = $me && $me->id === $comment->user_id;
    $canDelete = $me && ($me->id === $comment->user_id || $me->isAdmin() || $me->id === $video->user_id);
    $canReact = $me && $video->isViewableBy($me);
    $summary = $comment->reactionSummary();
    $mine = $comment->reactionOf($me);
    $types = \App\Models\CommentReaction::TYPES;
@endphp
<div id="comment-{{ $comment->id }}" style="display: flex; gap: {{ $isReply ? 'var(--space-2)' : 'var(--space-3)' }}; {{ $isReply ? 'padding: var(--space-2) 0 0 var(--space-3); border-left: 2px solid var(--color-divider)' : 'padding-bottom: var(--space-3); border-bottom: 1px solid var(--color-divider)' }}">
    <span class="avatar" @if ($isReply) style="width: 24px; height: 24px; font-size: 11px" @endif>
        @if ($comment->user->avatar)
            <img src="{{ asset('storage/'.$comment->user->avatar) }}" alt="">
        @else
            {{ mb_substr($comment->user->name, 0, 1) }}
        @endif
    </span>
    <div style="display: flex; flex-direction: column; gap: var(--space-1); min-width: 0; flex: 1" data-reveal-scope>
        <span style="font-size: {{ $isReply ? '12px' : '12.5px' }}">
            <a href="{{ route('users.show', $comment->user) }}">{{ $comment->user->name }}</a>
            @if ($comment->user->isMentor()) <span class="tag tag-accent" style="font-size: 9px">Mentor</span> @endif
            <span class="muted-i">· {{ $comment->created_at->diffForHumans() }}@if ($comment->isEdited()) · đã chỉnh sửa @endif</span>
        </span>
        <span style="font-size: {{ $isReply ? '13px' : '13.5px' }}; line-height: 1.55">{{ $comment->content }}</span>

        {{-- Tom tat cam xuc: chip emoji + so luong (ai cung thay) --}}
        @if ($summary)
            <div style="display: flex; gap: 4px; flex-wrap: wrap">
                @foreach ($summary as $type => $count)
                    <span class="tag {{ $mine === $type ? 'tag-accent' : 'tag-muted' }}" style="font-size: 11px; padding: 1px 7px" title="{{ $count }} người bày tỏ {{ $types[$type] ?? '' }}">{{ $types[$type] ?? '' }} <span class="num">{{ $count }}</span></span>
                @endforeach
            </div>
        @endif

        {{-- Hang nut hanh dong --}}
        @if ($me)
            <div style="display: flex; gap: var(--space-3); align-items: center; flex-wrap: wrap">
                @if ($canReact)
                    {{-- Re chuot vao .react-wrap → bang emoji noi len (CSS :hover/:focus-within); click de mo tren cam ung --}}
                    <div class="react-wrap">
                        <button type="button" class="btn btn-ghost btn-xs {{ $mine ? 'is-reacted' : '' }}" style="padding-left: 0" aria-expanded="false" aria-haspopup="true" onclick="tsToggle(this, '.react-picker')">
                            {{ $mine ? ($types[$mine] ?? '') : '' }} Cảm xúc
                        </button>
                        <div class="react-picker" role="group" aria-label="Chọn cảm xúc">
                            @foreach ($types as $type => $emoji)
                                <form method="POST" action="{{ route('comments.react', $comment) }}">
                                    @csrf
                                    <input type="hidden" name="type" value="{{ $type }}">
                                    <button class="react-emoji {{ $mine === $type ? 'is-on' : '' }}" title="{{ $mine === $type ? 'Bỏ cảm xúc' : ucfirst($type) }}" aria-pressed="{{ $mine === $type ? 'true' : 'false' }}" aria-label="{{ ucfirst($type) }}">{{ $emoji }}</button>
                                </form>
                            @endforeach
                        </div>
                    </div>
                @endif
                @if (! $isReply && $video->allow_comments)
                    <button type="button" class="btn btn-ghost btn-xs" aria-expanded="false" onclick="tsToggle(this, '.reply-form')">Trả lời</button>
                @endif
                @if ($canEdit)
                    <button type="button" class="btn btn-ghost btn-xs" aria-expanded="false" onclick="tsToggle(this, '.edit-form')">Sửa</button>
                @endif
                @if ($canDelete)
                    <form method="POST" action="{{ route('comments.destroy', $comment) }}" onsubmit="return confirm('Xóa bình luận này?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-ghost btn-xs" style="color: var(--color-danger)">Xóa</button>
                    </form>
                @endif
            </div>

            {{-- Form sua (chi chinh chu) --}}
            @if ($canEdit)
                <form method="POST" action="{{ route('comments.update', $comment) }}" class="reveal edit-form">
                    @csrf @method('PUT')
                    <div class="reveal-inner" style="display: flex; gap: var(--space-2); padding-top: calc(4px + var(--space-1))">
                        <input class="input" name="content" value="{{ $comment->content }}" required maxlength="2000" style="flex: 1; font-size: 12.5px; min-height: 32px">
                        <button class="btn btn-primary btn-xs">Lưu</button>
                    </div>
                </form>
            @endif

            {{-- Form tra loi (chi binh luan goc, 1 cap) --}}
            @if (! $isReply && $video->allow_comments)
                <form method="POST" action="{{ route('comments.store', $video) }}" class="reveal reply-form">
                    @csrf
                    <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                    <div class="reveal-inner" style="display: flex; gap: var(--space-2); padding-top: calc(4px + var(--space-1))">
                        <input class="input" name="content" placeholder="Trả lời {{ $comment->user->name }}…" required style="flex: 1; font-size: 12.5px; min-height: 32px">
                        <button class="btn btn-primary btn-xs">Gửi</button>
                    </div>
                </form>
            @endif
        @endif

        {{-- Tra loi 1 cap (dat SAU cac panel de tsToggle cua binh luan goc tim dung panel cua no) --}}
        @if (! $isReply)
            @foreach ($comment->replies as $reply)
                @include('videos._comment', ['comment' => $reply, 'isReply' => true])
            @endforeach
        @endif
    </div>
</div>
