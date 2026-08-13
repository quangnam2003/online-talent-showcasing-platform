@extends('layouts.app')

@section('title', 'Kiểm duyệt video — TalentStage Admin')

@section('screen-kicker', 'FR8 · Moderation')
@section('screen-title', 'Kiểm duyệt')
@section('screen-sub', 'Admin — review, approve, reject with reason')

@section('content')
{{-- ── Tab gach chan theo trang thai (mockup Moderation) ── --}}
<div class="line-tabs">
    @foreach (['pending' => 'Chờ duyệt · Pending', 'approved' => 'Đã duyệt · Approved', 'rejected' => 'Từ chối · Rejected'] as $s => $label)
        <a class="line-tab {{ $status === $s ? 'active' : '' }}" href="{{ route('admin.videos.index', ['status' => $s]) }}">
            {{ $label }} <span class="num">({{ $counts[$s] ?? 0 }})</span>
        </a>
    @endforeach
    <span class="meta" style="margin-left: auto">{{ $videos->total() }} mục</span>
</div>

{{-- ── Hang doi: card ngang, duyet nhanh ── --}}
<div style="display: flex; flex-direction: column; gap: var(--space-3)">
    @forelse ($videos as $video)
        <div class="card mod-row" style="flex-direction: row; gap: var(--space-4); padding: var(--space-3); align-items: center">
            <a href="{{ route('videos.show', $video) }}" class="hatch-mid" style="width: 128px; height: 76px; flex: 0 0 128px; border: 1px solid var(--color-divider); overflow: hidden; display: block">
                @if ($video->thumbnail && file_exists(public_path('storage/'.$video->thumbnail)))
                    <img src="{{ asset('storage/'.$video->thumbnail) }}" style="width: 100%; height: 100%; object-fit: cover" alt="">
                @endif
            </a>
            <div style="display: flex; flex-direction: column; gap: 2px; flex: 1; min-width: 0">
                <a href="{{ route('videos.show', $video) }}" style="font-family: var(--font-heading); font-weight: 600; font-size: 17px; color: inherit">{{ $video->title }}</a>
                <span class="meta">
                    {{ $video->user->name }} · {{ $video->category->name }}
                    · {{ $video->privacy === 'public' ? 'Công khai' : 'Riêng tư' }}
                    · gửi {{ $video->created_at->diffForHumans() }}
                </span>
                @if ($video->description)
                    <span class="muted-i" style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap">{{ \Illuminate\Support\Str::limit($video->description, 90) }}</span>
                @endif
            </div>

            @if ($status === 'pending')
                <div style="display: flex; flex-direction: column; gap: var(--space-1)">
                    <form method="POST" action="{{ route('admin.videos.approve', $video) }}">
                        @csrf @method('PATCH')
                        <button class="btn btn-primary btn-xs" style="width: 100%">Duyệt · Approve</button>
                    </form>
                    <button type="button" class="btn btn-ghost btn-xs"
                            onclick="const f = this.closest('.mod-row').querySelector('.reject-panel'); f.style.display = f.style.display === 'none' ? 'flex' : 'none'">
                        Từ chối…
                    </button>
                </div>
            @else
                @php
                    $c = ['approved' => 'var(--color-accent-700)', 'rejected' => 'var(--color-neutral-800)'][$status];
                    $t = ['approved' => 'Đã duyệt', 'rejected' => 'Đã từ chối'][$status];
                @endphp
                <span class="tag" style="font-size: 10px; border: 1px solid {{ $c }}; color: {{ $c }}; flex: none">{{ $t }}</span>
                @if ($status === 'rejected')
                    <form method="POST" action="{{ route('admin.videos.approve', $video) }}">
                        @csrf @method('PATCH')
                        <button class="btn btn-ghost btn-xs">Duyệt lại</button>
                    </form>
                @endif
            @endif

            {{-- Panel tu choi kem ly do (khong dung modal — dung mockup) --}}
            @if ($status === 'pending')
                <form method="POST" action="{{ route('admin.videos.reject', $video) }}" class="reject-panel"
                      style="display: none; flex-direction: column; gap: var(--space-1); flex: 0 0 260px">
                    @csrf @method('PATCH')
                    <select class="input" name="reason" style="font-size: 12px; min-height: 32px">
                        <option value="Vi phạm bản quyền">Vi phạm bản quyền</option>
                        <option value="Nội dung không phù hợp">Nội dung không phù hợp</option>
                        <option value="Chất lượng không đạt">Chất lượng không đạt</option>
                        <option value="Sai thể loại">Sai thể loại</option>
                        <option value="">Không ghi lý do</option>
                    </select>
                    <button class="btn btn-secondary btn-xs">Gửi từ chối (creator sẽ nhận thông báo)</button>
                </form>
            @endif
        </div>
    @empty
        <div class="card" style="align-items: center; padding: var(--space-8)">
            <span class="muted-i">Không có video nào ở trạng thái này.</span>
        </div>
    @endforelse
</div>

@include('partials.pager', ['p' => $videos])
@endsection
