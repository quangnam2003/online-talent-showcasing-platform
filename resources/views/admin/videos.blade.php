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
        <div class="card mod-row" style="gap: 0; padding: var(--space-3)" data-reveal-scope>
            {{-- hang chinh: thumbnail · thong tin · hanh dong --}}
            <div style="display: flex; gap: var(--space-4); align-items: center">
                <a href="{{ route('videos.show', $video) }}" class="hatch-mid" style="width: 128px; height: 76px; flex: 0 0 128px; border: 1px solid var(--color-divider); overflow: hidden; display: block">
                    @if ($video->thumbnail && file_exists(public_path('storage/'.$video->thumbnail)))
                        <img src="{{ asset('storage/'.$video->thumbnail) }}" style="width: 100%; height: 100%; object-fit: cover" alt="" loading="lazy">
                    @endif
                </a>
                <div style="display: flex; flex-direction: column; gap: 2px; flex: 1; min-width: 0">
                    <a href="{{ route('videos.show', $video) }}" style="font-family: var(--font-heading); font-weight: 600; font-size: 17px; color: inherit">{{ $video->title }}</a>
                    <span class="meta">
                        {{ $video->user->name }} ·
                        <span style="color: {{ $video->category->colorVar() }}; font-weight: 600">{{ $video->category->name }}</span>
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
                        <button type="button" class="btn btn-ghost btn-xs" aria-expanded="false"
                                onclick="tsToggle(this, '.reject-panel')">
                            Từ chối…
                        </button>
                    </div>
                @else
                    <span class="tag tag-status" data-status="{{ $status }}" style="flex: none">
                        {{ ['approved' => 'Đã duyệt', 'rejected' => 'Đã từ chối'][$status] }}
                    </span>
                    @if ($status === 'rejected')
                        <form method="POST" action="{{ route('admin.videos.approve', $video) }}">
                            @csrf @method('PATCH')
                            <button class="btn btn-ghost btn-xs">Duyệt lại</button>
                        </form>
                    @endif
                @endif
            </div>

            {{-- Panel tu choi kem ly do (khong dung modal — dung mockup); .reveal mo/dong muot ngay duoi hang --}}
            @if ($status === 'pending')
                <form method="POST" action="{{ route('admin.videos.reject', $video) }}" class="reveal reject-panel">
                    @csrf @method('PATCH')
                    <div class="reveal-inner" style="display: flex; gap: var(--space-2); align-items: center; flex-wrap: wrap; margin-top: var(--space-3); padding-top: var(--space-3); border-top: 1px solid var(--color-divider)">
                        <span class="label-up">Lý do từ chối</span>
                        <select class="input" name="reason" style="font-size: 12px; min-height: 32px; width: auto; min-width: 220px">
                            <option value="Vi phạm bản quyền">Vi phạm bản quyền</option>
                            <option value="Nội dung không phù hợp">Nội dung không phù hợp</option>
                            <option value="Chất lượng không đạt">Chất lượng không đạt</option>
                            <option value="Sai thể loại">Sai thể loại</option>
                            <option value="">Không ghi lý do</option>
                        </select>
                        <button class="btn btn-secondary btn-xs">Gửi từ chối (creator sẽ nhận thông báo)</button>
                    </div>
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
