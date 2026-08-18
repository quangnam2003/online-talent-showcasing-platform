@extends('layouts.app')

@section('title', 'Kiểm duyệt video — TalentStage Admin')

@section('screen-kicker')<a href="{{ route('admin.dashboard') }}">Quản trị</a><span class="sep">/</span><span>Kiểm duyệt</span>@endsection
@section('screen-title', 'Kiểm duyệt video')
@section('screen-sub', 'Xem video mới gửi, phê duyệt hoặc từ chối kèm lý do — creator sẽ nhận thông báo.')

@section('content')
{{-- ── Tab gach chan theo trang thai (mockup Moderation) ── --}}
{{-- [data-live]: vung duoc JS thay moi khi go tim/doi sap xep (xem tsModFilter trong talentstage.js) --}}
<div class="line-tabs" id="mod-tabs" data-live>
    @foreach (['pending' => 'Chờ duyệt', 'approved' => 'Đã duyệt', 'rejected' => 'Từ chối'] as $s => $label)
        <a class="line-tab {{ $status === $s ? 'active' : '' }}" href="{{ route('admin.videos.index', array_filter(['status' => $s, 'title' => $q])) }}">
            {{ $label }} <span class="num">({{ $counts[$s] ?? 0 }})</span>
        </a>
    @endforeach
    <span class="meta" style="margin-left: auto">{{ $videos->total() }} mục</span>
</div>

{{-- ── Tim theo tieu de + sap xep: tu loc khi go (khong can Enter), xoa chu la ve danh sach day du ── --}}
<form method="GET" action="{{ route('admin.videos.index') }}" id="mod-filter" role="search"
      style="display: flex; gap: var(--space-2); align-items: center; flex-wrap: wrap; margin-bottom: var(--space-4)">
    <input type="hidden" name="status" value="{{ $status }}">
    <input class="input" type="search" name="title" value="{{ $q }}" placeholder="Tìm theo tiêu đề…" autocomplete="off" style="max-width: 320px" aria-label="Tìm theo tiêu đề">
    <select class="input" name="sort" style="width: auto" aria-label="Sắp xếp">
        <option value="newest" {{ $sort === 'newest' ? 'selected' : '' }}>Mới nhất → cũ nhất</option>
        <option value="oldest" {{ $sort === 'oldest' ? 'selected' : '' }}>Cũ nhất → mới nhất</option>
    </select>
    <button type="button" class="btn btn-ghost btn-xs" data-clear {{ $q === '' ? 'hidden' : '' }}>Xoá lọc</button>
    <noscript><button class="btn btn-secondary btn-xs"><x-icon name="search" size="13" /> Tìm</button></noscript>
</form>

{{-- ── Hang doi: card ngang, duyet nhanh ── --}}
<div style="display: flex; flex-direction: column; gap: var(--space-3)" id="mod-list" data-live>
    @forelse ($videos as $video)
        <div class="card mod-row" style="gap: 0; padding: var(--space-3)" data-reveal-scope>
            {{-- hang chinh: thumbnail · thong tin · hanh dong --}}
            <div style="display: flex; gap: var(--space-4); align-items: center">
                <a href="{{ route('videos.show', $video) }}" class="hatch-mid thumb-sm" style="width: 128px; height: 76px; flex: 0 0 128px; display: block; --cat: {{ $video->category->colorVar() }}">@include('partials.thumb', ['video' => $video, 'compact' => true])</a>
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
                            <button class="btn btn-primary btn-xs" style="width: 100%"><x-icon name="check" size="13" /> Duyệt</button>
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
            <span class="muted-i">{{ $q !== '' ? 'Không tìm thấy video nào có tiêu đề chứa "'.$q.'".' : 'Không có video nào ở trạng thái này.' }}</span>
        </div>
    @endforelse

    @include('partials.pager', ['p' => $videos])
</div>
@endsection
