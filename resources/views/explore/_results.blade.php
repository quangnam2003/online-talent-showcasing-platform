{{--
    Phan ket qua cua trang Tim kiem — duoc render lai qua XHR khi go vao o tim kiem
    o header (live search). Moi control loc/sap xep nam trong day de luon mang dung
    trang thai q/category/sort hien tai.
--}}

{{-- ── Thanh cong cu: so ket qua · sap xep · xoa loc ── --}}
<div style="display: flex; align-items: center; gap: var(--space-3); flex-wrap: wrap">
    <span class="meta num" role="status">
        {{ $videos->total() }} tiết mục{{ $q !== '' ? ' cho «'.$q.'»' : '' }}
    </span>
    <form method="GET" action="{{ route('explore') }}" style="margin-left: auto; display: flex; gap: var(--space-2); align-items: center">
        @if ($q !== '')<input type="hidden" name="q" value="{{ $q }}">@endif
        @if ($categorySlug)<input type="hidden" name="category" value="{{ $categorySlug }}">@endif
        <label class="meta" for="exploreSort" style="flex: none">Sắp xếp</label>
        <select class="input" id="exploreSort" name="sort" style="width: auto; min-height: 32px; font-size: 12.5px">
            <option value="trending" @selected($sort === 'trending')>Thịnh hành</option>
            <option value="new" @selected($sort === 'new')>Mới nhất</option>
            <option value="views" @selected($sort === 'views')>Nhiều lượt xem</option>
            <option value="rating" @selected($sort === 'rating')>Đánh giá cao</option>
        </select>
        <noscript><button class="btn btn-secondary btn-xs">Áp dụng</button></noscript>
    </form>
    @if ($q !== '' || $categorySlug)
        <a class="btn btn-ghost btn-xs" href="{{ route('explore') }}"><x-icon name="x" size="13" /> Xóa lọc</a>
    @endif
</div>

{{-- ── Chip the loai ── --}}
<div style="display: flex; gap: 6px; flex-wrap: wrap; align-items: center">
    <span class="label-up" style="margin-right: var(--space-1)">Thể loại</span>
    <a class="cat-chip {{ $categorySlug ? '' : 'active' }}" style="--cat: var(--color-neutral-700)"
       href="{{ route('explore', array_filter(['q' => $q ?: null, 'sort' => $sort])) }}">Tất cả</a>
    @foreach ($categories as $cat)
        <a class="cat-chip {{ $categorySlug === $cat->slug ? 'active' : '' }}" style="--cat: {{ $cat->colorVar() }}"
           href="{{ route('explore', array_filter(['q' => $q ?: null, 'sort' => $sort, 'category' => $cat->slug])) }}">{{ $cat->name }}</a>
    @endforeach
</div>

{{-- ── Creator co ten khop ── --}}
@if ($creators->isNotEmpty())
    <div style="display: flex; flex-direction: column; gap: var(--space-2)">
        <span class="label-up">Creator phù hợp</span>
        <div style="display: flex; gap: var(--space-2); flex-wrap: wrap">
            @foreach ($creators as $creator)
                <a class="card" href="{{ route('users.show', $creator) }}"
                   style="flex-direction: row; align-items: center; gap: var(--space-2); padding: var(--space-2) var(--space-3)">
                    <span class="avatar">
                        @if ($creator->avatar)
                            <img src="{{ asset('storage/'.$creator->avatar) }}" alt="{{ $creator->name }}">
                        @else
                            {{ mb_substr($creator->name, 0, 1) }}
                        @endif
                    </span>
                    <span style="display: flex; flex-direction: column; line-height: 1.25">
                        <span style="font-size: 13.5px; font-weight: 600">{{ $creator->name }}</span>
                        <span class="meta">{{ number_format($creator->followers_count) }} người theo dõi · {{ $creator->videos_count }} tiết mục</span>
                    </span>
                </a>
            @endforeach
        </div>
    </div>
@endif

{{-- ── Luoi ket qua ── --}}
<div class="grid-4">
    @forelse ($videos as $video)
        <a class="card video-card" href="{{ route('videos.show', $video) }}" style="--cat: {{ $video->category->colorVar() }}">
            <div class="video-thumb">@include('partials.thumb', ['video' => $video])</div>
            <div class="video-card-body">
                <span class="video-card-cat">{{ $video->category->name }}</span>
                <span class="video-card-title">{{ $video->title }}</span>
                <span class="meta">{{ $video->user->name }} · {{ number_format($video->views) }} lượt xem · ★ {{ number_format($video->avg_rating, 1) }}</span>
            </div>
        </a>
    @empty
        <div class="card" style="grid-column: 1 / -1; align-items: center; padding: var(--space-6); gap: var(--space-1)">
            <x-icon name="search" size="26" style="color: var(--color-neutral-400)" />
            <span class="muted-i">
                @if ($q !== '')
                    Không có kết quả cho «{{ $q }}» — thử từ khóa khác, ví dụ tên creator, tiêu đề hoặc thể loại.
                @else
                    Chưa có tiết mục nào trong mục này.
                @endif
            </span>
        </div>
    @endforelse
</div>

@include('partials.pager', ['p' => $videos])
