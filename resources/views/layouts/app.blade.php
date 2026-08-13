<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'TalentStage — Sân khấu tài năng trực tuyến')</title>
    <link rel="stylesheet" href="{{ asset('css/fonts.css') }}">
    <link rel="stylesheet" href="{{ asset('css/talentstage.css') }}">
</head>
<body>
<div class="ts-shell">

    {{-- ═══ SIDEBAR — 4 nhom nav danh so, giong mockup ═══ --}}
    <aside class="ts-sidebar">
        <div class="ts-brand">
            <a class="ts-brand-name" href="{{ route('home') }}">TalentStage</a>
            <span class="ts-brand-sub">Online Talent Showcasing</span>
        </div>

        @php
            $me = auth()->user();
            // [nhan VN, nhan EN, tag FR, url, pattern active, hien thi?]
            $navGroups = [
                '01 · Truy cập & nội dung' => array_values(array_filter([
                    !$me ? ['Đăng ký / Đăng nhập', 'Register & log in', 'FR1', route('login'), 'login|register'] : null,
                    $me ? ['Hồ sơ của tôi', 'My profile', 'FR1', url('/users/'.$me->id), 'users/'.$me->id] : null,
                    $me?->isCreator() ? ['Đăng & duyệt', 'Upload & approval', 'FR2', url('/videos/create'), 'videos/create'] : null,
                    $me?->isCreator() ? ['Video của tôi', 'My videos', 'FR2', url('/my-videos'), 'my-videos'] : null,
                ])),
                '02 · Khám phá & tương tác' => array_values(array_filter([
                    ['Khám phá', 'Discover', 'FR3', route('home'), null], // active khi la trang chu
                    ['Tìm kiếm & lọc', 'Explore', 'FR3', url('/explore'), 'explore'],
                    $me ? ['Bảng tin', 'Personalised feed', 'FR4', url('/feed'), 'feed'] : null,
                    $me ? ['Thông báo', 'Notifications', 'SYS', url('/notifications'), 'notifications', $me->unreadNotifications()->count()] : null,
                ])),
                '03 · Nhóm & cố vấn' => array_values(array_filter([
                    ['Nhóm', 'Groups', 'FR5', url('/groups'), 'groups*'],
                    ($me?->isCreator() || $me?->isMentor()) ? ['Tin nhắn', 'Mentorship', 'FR6', url('/messages'), 'messages*', $me->unreadMessagesCount()] : null,
                ])),
                '04 · Cuộc thi' => [
                    ['Cuộc thi', 'Contest', 'FR7', url('/contests'), 'contests*'],
                ],
            ];
            if ($me?->isAdmin()) {
                $navGroups['05 · Quản trị'] = [
                    ['Kiểm duyệt', 'Moderation', 'FR8', url('/admin/videos'), 'admin/videos*'],
                    ['Dashboard', 'Overview', 'AD', url('/admin'), 'admin'],
                    ['Người dùng', 'Users', 'AD', url('/admin/users'), 'admin/users*'],
                    ['Danh mục', 'Categories', 'AD', url('/admin/categories'), 'admin/categories*'],
                    ['Quản lý cuộc thi', 'Contests', 'AD', url('/admin/contests'), 'admin/contests*'],
                ];
            }
        @endphp

        <nav class="ts-nav">
            @foreach ($navGroups as $label => $items)
                @if (count($items))
                    <div class="ts-nav-group">
                        <div class="ts-nav-label">{{ $label }}</div>
                        <div class="ts-nav-items">
                            @foreach ($items as $item)
                                @php
                                    [$vi, $en, $fr, $url, $pattern] = $item;
                                    $badge = $item[5] ?? 0;
                                    $active = $pattern === null
                                        ? request()->is('/')
                                        : request()->is(...explode('|', $pattern));
                                @endphp
                                <a class="ts-nav-item {{ $active ? 'active' : '' }}" href="{{ $url }}">
                                    <span>
                                        <span class="ts-nav-vi">{{ $vi }}</span>
                                        <span class="ts-nav-en">{{ $en }}</span>
                                    </span>
                                    @if ($badge > 0)
                                        <span class="tag tag-accent num">{{ $badge }}</span>
                                    @else
                                        <span class="tag tag-outline">{{ $fr }}</span>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach
        </nav>

        <div class="ts-side-foot">
            @auth
                <div style="display: flex; align-items: center; gap: var(--space-2)">
                    <span class="avatar">
                        @if ($me->avatar)
                            <img src="{{ asset('storage/'.$me->avatar) }}" alt="{{ $me->name }}">
                        @else
                            {{ mb_substr($me->name, 0, 1) }}
                        @endif
                    </span>
                    <span style="display: flex; flex-direction: column; min-width: 0">
                        <span style="font-size: 12.5px">{{ $me->name }}</span>
                        <span class="muted-i" style="font-size: 10px">{{ ucfirst($me->role) }}</span>
                    </span>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-ghost btn-xs" style="padding-left: 0">Đăng xuất · Log out</button>
                </form>
            @else
                <a class="btn btn-primary btn-sm" href="{{ route('register') }}">Đăng ký · Register</a>
                <a class="btn btn-secondary btn-sm" href="{{ route('login') }}">Đăng nhập · Log in</a>
            @endauth
            <a href="{{ route('sitemap') }}" class="muted-i" style="font-size: 10.5px">Sơ đồ trang · Sitemap</a>
        </div>
    </aside>

    {{-- ═══ MAIN ═══ --}}
    <main class="ts-main">

        <header class="ts-header">
            <button class="ts-menu-btn" type="button" onclick="document.body.classList.toggle('nav-open')" aria-label="Mở menu">☰</button>

            <form class="search" method="GET" action="{{ url('/explore') }}">
                <input class="input" type="search" name="q" value="{{ request('q') }}"
                       placeholder="Tìm tài năng, thể loại, creator… / Search talent">
            </form>

            <div class="ts-header-tags">
                @foreach ($navCategories ?? [] as $cat)
                    <a class="tag tag-outline" style="font-size: 10.5px"
                       href="{{ url('/explore?category='.$cat->slug) }}">{{ $cat->name }}</a>
                @endforeach
            </div>

            <div class="ts-header-right">
                @if ($me?->isCreator())
                    <a class="btn btn-primary btn-sm" href="{{ url('/videos/create') }}">Đăng video · Upload</a>
                @endif
                @auth
                    <a href="{{ url('/users/'.$me->id) }}" style="display: flex; align-items: center; gap: var(--space-2); text-decoration: none; color: inherit">
                        <span class="avatar">
                            @if ($me->avatar)
                                <img src="{{ asset('storage/'.$me->avatar) }}" alt="{{ $me->name }}">
                            @else
                                {{ mb_substr($me->name, 0, 1) }}
                            @endif
                        </span>
                        <span style="display: flex; flex-direction: column">
                            <span style="font-size: 12.5px">{{ $me->name }}</span>
                            <span class="muted-i" style="font-size: 10px">{{ ucfirst($me->role) }}</span>
                        </span>
                    </a>
                @else
                    <a class="btn btn-ghost btn-sm" href="{{ route('login') }}">Đăng nhập</a>
                @endauth
            </div>
        </header>

        <div class="ts-content">

            {{-- flash --}}
            @if (session('success'))
                <div class="flash">
                    <span>{{ session('success') }}</span>
                    <button type="button" onclick="this.closest('.flash').remove()" aria-label="Đóng">×</button>
                </div>
            @endif
            @if (session('error'))
                <div class="flash flash-error">
                    <span>{{ session('error') }}</span>
                    <button type="button" onclick="this.closest('.flash').remove()" aria-label="Đóng">×</button>
                </div>
            @endif

            {{-- tieu de man hinh: kicker / ten / phu de (pattern cua mockup) --}}
            @hasSection('screen-title')
                <div class="screen-head">
                    <div class="screen-head-txt">
                        <span class="kicker">@yield('screen-kicker')</span>
                        <h1 class="screen-title">@yield('screen-title')</h1>
                        <span class="screen-sub">@yield('screen-sub')</span>
                    </div>
                    @hasSection('screen-side')
                        <div class="screen-cases">@yield('screen-side')</div>
                    @endif
                </div>
            @endif

            @yield('content')
        </div>

        <footer class="ts-footer">
            <span style="font-family: var(--font-heading); font-size: 16px">TalentStage</span>
            <span class="muted-i">Nền tảng trình diễn tài năng trực tuyến — Đồ án 2026</span>
            <a href="{{ route('sitemap') }}" style="font-size: 12.5px; margin-left: auto">Sơ đồ trang · Sitemap</a>
            <a href="{{ url('/explore') }}" style="font-size: 12.5px">Khám phá</a>
            <a href="{{ url('/contests') }}" style="font-size: 12.5px">Cuộc thi</a>
        </footer>
    </main>
</div>

{{-- dong drawer khi bam ra ngoai (mobile) --}}
<script>
document.addEventListener('click', function (e) {
    if (document.body.classList.contains('nav-open')
        && !e.target.closest('.ts-sidebar') && !e.target.closest('.ts-menu-btn')) {
        document.body.classList.remove('nav-open');
    }
});
</script>
@stack('scripts')
</body>
</html>
