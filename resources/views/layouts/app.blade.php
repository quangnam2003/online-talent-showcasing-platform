<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'TalentStage — Sân khấu tài năng trực tuyến')</title>
    {{-- preload 2 subset Public Sans hay dung nhat de tranh nhay chu khi vao trang --}}
    <link rel="preload" href="{{ asset('fonts/public-sans-vf-latin.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="{{ asset('fonts/public-sans-vf-vietnamese.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="{{ asset('css/fonts.css') }}">
    <link rel="stylesheet" href="{{ asset('css/talentstage.css') }}?v={{ @filemtime(public_path('css/talentstage.css')) ?: 1 }}">
    <script src="{{ asset('js/talentstage.js') }}?v={{ @filemtime(public_path('js/talentstage.js')) ?: 1 }}" defer></script>
    {{-- khoi phuc trang thai thu gon sidebar TRUOC khi ve trang (tranh nhay); logic bat/tat: tsToggleCollapse trong talentstage.js --}}
    <script>try{if(localStorage.getItem('ts-nav')==='collapsed')document.documentElement.classList.add('nav-collapsed')}catch(e){}</script>
</head>
<body>
@php
    $me = auth()->user();
    $isCM = $me && ($me->isCreator() || $me->isMentor());
    $unreadNoti = $me ? $me->unreadNotifications()->count() : 0;
    $unreadMsg = $isCM ? $me->unreadMessagesCount() : 0;
    $pendingCount = 0;
    if ($me?->isAdmin()) {
        try { $pendingCount = \App\Models\Video::where('status', 'pending')->count(); } catch (\Throwable $e) {}
    }

    // Nav sidebar: [nhan, icon, url, pattern active (null = trang chu), badge]
    $navGroups = [
        ['label' => null, 'items' => array_values(array_filter([
            ['Trang chủ', 'home', route('home'), null],
            ['Tìm kiếm', 'search', url('/explore'), 'explore'],
            $me ? ['Bảng tin', 'rss', url('/feed'), 'feed'] : null,
            ['Cuộc thi', 'trophy', url('/contests'), 'contests*'],
        ]))],
        ['label' => 'Cộng đồng', 'items' => array_values(array_filter([
            ['Nhóm', 'users', url('/groups'), 'groups*'],
            $isCM ? ['Tin nhắn', 'message', url('/messages'), 'messages*', $unreadMsg] : null,
            $me ? ['Thông báo', 'bell', url('/notifications'), 'notifications', $unreadNoti] : null,
        ]))],
    ];
    if ($me?->isCreator()) {
        $navGroups[] = ['label' => 'Kênh của tôi', 'items' => [
            ['Đăng tiết mục', 'upload', url('/videos/create'), 'videos/create'],
            ['Tiết mục của tôi', 'film', url('/my-videos'), 'my-videos|videos/*/edit'],
            ['Hồ sơ của tôi', 'user', url('/users/'.$me->id), 'users/'.$me->id.'|profile/edit'],
        ]];
    } elseif ($me) {
        $navGroups[] = ['label' => 'Tài khoản', 'items' => [
            ['Hồ sơ của tôi', 'user', url('/users/'.$me->id), 'users/'.$me->id.'|profile/edit'],
        ]];
    }
    if ($me?->isAdmin()) {
        $navGroups[] = ['label' => 'Quản trị', 'items' => [
            ['Tổng quan', 'dashboard', url('/admin'), 'admin'],
            ['Kiểm duyệt', 'list-checks', url('/admin/videos'), 'admin/videos*', $pendingCount],
            ['Người dùng', 'users', url('/admin/users'), 'admin/users*'],
            ['Danh mục', 'tag', url('/admin/categories'), 'admin/categories*'],
            ['Cuộc thi', 'trophy', url('/admin/contests'), 'admin/contests*'],
        ]];
    }
@endphp
<div class="ts-shell">
    {{-- toast thong bao gan thoi gian thuc (JS tsNotiPoll dien vao) --}}
    <div class="ts-toasts" id="ts-toasts" aria-live="polite" aria-atomic="false"></div>
    <div class="ts-scrim" aria-hidden="true"></div>

    {{-- ═══ SIDEBAR ═══ --}}
    <aside class="ts-sidebar" aria-label="Điều hướng chính">
        <a class="ts-logo" href="{{ route('home') }}" aria-label="TalentStage — Trang chủ">
            <span class="ts-logo-mark"><x-icon name="mic" size="18" /></span>
            <span class="ts-logo-txt">
                <span class="ts-logo-name">TalentStage</span>
                <span class="ts-logo-sub">Sân khấu tài năng trực tuyến</span>
            </span>
        </a>

        <nav class="ts-nav" id="ts-nav">
            @foreach ($navGroups as $group)
                @if (count($group['items']))
                    <div class="ts-nav-group">
                        @if ($group['label'])
                            <div class="ts-nav-label">{{ $group['label'] }}</div>
                        @endif
                        <div class="ts-nav-items">
                            @foreach ($group['items'] as $item)
                                @php
                                    [$label, $icon, $url, $pattern] = $item;
                                    $badge = $item[4] ?? 0;
                                    $active = $pattern === null
                                        ? request()->is('/')
                                        : request()->is(...explode('|', $pattern));
                                @endphp
                                <a class="ts-nav-item {{ $active ? 'active' : '' }}" href="{{ $url }}" data-tip="{{ $label }}" @if ($active) aria-current="page" @endif>
                                    <x-icon :name="$icon" size="18" />
                                    <span class="ts-nav-txt">{{ $label }}</span>
                                    @if ($label === 'Thông báo')
                                        <span class="badge" data-noti-badge="nav" @if ($badge <= 0) hidden @endif>{{ $badge > 99 ? '99+' : $badge }}</span>
                                    @elseif ($badge > 0)
                                        <span class="badge">{{ $badge > 99 ? '99+' : $badge }}</span>
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
                <a class="ts-me" href="{{ url('/users/'.$me->id) }}" title="{{ $me->name }} — xem hồ sơ">
                    <span class="avatar">
                        @if ($me->avatar)
                            <img src="{{ asset('storage/'.$me->avatar) }}" alt="{{ $me->name }}">
                        @else
                            {{ mb_substr($me->name, 0, 1) }}
                        @endif
                    </span>
                    <span class="ts-me-txt">
                        <span class="ts-me-name">{{ $me->name }}</span>
                        <span class="ts-me-role">{{ ucfirst($me->role) }}</span>
                    </span>
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="icon-btn" title="Đăng xuất" aria-label="Đăng xuất"><x-icon name="log-out" size="17" /></button>
                </form>
            @else
                <a class="btn btn-primary btn-sm" href="{{ route('login') }}" title="Đăng nhập"><x-icon name="log-in" size="15" /> Đăng nhập</a>
                <a class="btn btn-secondary btn-sm" href="{{ route('register') }}" title="Tạo tài khoản"><x-icon name="user-plus" size="15" /> Tạo tài khoản</a>
            @endauth
        </div>
    </aside>

    {{-- ═══ MAIN ═══ --}}
    <main class="ts-main">

        <header class="ts-header">
            {{-- MOT nut ☰ cho ca hai che do (JS tsMenu): man hinh nho → mo/dong drawer; desktop → thu gon sidebar
                 thanh rail icon / mo rong lai (html.nav-collapsed, nho bang localStorage) --}}
            <button class="ts-menu-btn" type="button" onclick="tsMenu()" aria-controls="ts-nav" aria-expanded="true"
                    title="Thu gọn thanh bên" aria-label="Thu gọn thanh bên"><x-icon name="menu" size="18" /></button>

            <form class="search" method="GET" action="{{ url('/explore') }}" role="search">
                <x-icon name="search" size="16" class="search-ico" />
                <input class="input" type="search" name="q" value="{{ request('q') }}"
                       placeholder="Tìm video, creator, thể loại…" aria-label="Tìm kiếm">
            </form>

            {{-- chip the loai: moi the loai mot sac (--cat), chip dang chon duoc danh dau --}}
            <div class="ts-header-tags">
                @foreach ($navCategories ?? [] as $cat)
                    <a class="cat-chip {{ request('category') === $cat->slug ? 'active' : '' }}"
                       style="--cat: {{ $cat->colorVar() }}"
                       href="{{ url('/explore?category='.$cat->slug) }}">{{ $cat->name }}</a>
                @endforeach
            </div>

            <div class="ts-header-right">
                @if ($me?->isCreator())
                    <a class="btn btn-primary btn-sm" href="{{ url('/videos/create') }}"><x-icon name="upload" size="15" /> Đăng tiết mục</a>
                @endif
                @auth
                    <a class="icon-btn" id="ts-bell" href="{{ url('/notifications') }}" aria-label="Thông báo{{ $unreadNoti ? " ($unreadNoti chưa đọc)" : '' }}" title="Thông báo"
                       data-noti-poll="{{ route('notifications.poll') }}" data-noti-read="{{ route('notifications.read') }}" data-noti-since="{{ now()->toIso8601String() }}">
                        <x-icon name="bell" size="18" />
                        <span class="badge badge-dot" data-noti-badge="dot" @if ($unreadNoti <= 0) hidden @endif>{{ $unreadNoti > 9 ? '9+' : $unreadNoti }}</span>
                    </a>
                    <details class="menu ts-user-menu">
                        <summary class="ts-user" aria-label="Menu tài khoản">
                            <span class="avatar">
                                @if ($me->avatar)
                                    <img src="{{ asset('storage/'.$me->avatar) }}" alt="{{ $me->name }}">
                                @else
                                    {{ mb_substr($me->name, 0, 1) }}
                                @endif
                            </span>
                            <span class="ts-user-name">
                                <span>{{ $me->name }}</span>
                                <span class="ts-me-role">{{ ucfirst($me->role) }}</span>
                            </span>
                            <x-icon name="chevron-down" size="14" class="chev" />
                        </summary>
                        <div class="menu-panel">
                            <div class="menu-head">
                                <strong>{{ $me->name }}</strong>
                                <span>{{ $me->email }}</span>
                            </div>
                            <a class="menu-item" href="{{ url('/users/'.$me->id) }}"><x-icon name="user" size="16" /> Hồ sơ của tôi</a>
                            <a class="menu-item" href="{{ url('/profile/edit') }}"><x-icon name="settings" size="16" /> Sửa hồ sơ</a>
                            @if ($me->isCreator())
                                <a class="menu-item" href="{{ url('/my-videos') }}"><x-icon name="film" size="16" /> Tiết mục của tôi</a>
                            @endif
                            @if ($isCM)
                                <a class="menu-item" href="{{ url('/messages') }}"><x-icon name="message" size="16" /> Tin nhắn @if ($unreadMsg)<span class="badge">{{ $unreadMsg }}</span>@endif</a>
                            @endif
                            @if ($me->isAdmin())
                                <a class="menu-item" href="{{ url('/admin') }}"><x-icon name="shield" size="16" /> Khu quản trị</a>
                            @endif
                            <div class="menu-sep"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="menu-item"><x-icon name="log-out" size="16" /> Đăng xuất</button>
                            </form>
                        </div>
                    </details>
                @else
                    <a class="btn btn-ghost btn-sm" href="{{ route('login') }}">Đăng nhập</a>
                    <a class="btn btn-primary btn-sm" href="{{ route('register') }}">Đăng ký</a>
                @endauth
            </div>
        </header>

        <div class="ts-content">

            {{-- flash --}}
            @if (session('success'))
                <div class="flash" role="status">
                    <x-icon name="check" size="16" />
                    <span>{{ session('success') }}</span>
                    <button type="button" onclick="tsDismiss(this)" aria-label="Đóng">×</button>
                </div>
            @endif
            @if (session('error'))
                <div class="flash flash-error" role="alert">
                    <x-icon name="info" size="16" />
                    <span>{{ session('error') }}</span>
                    <button type="button" onclick="tsDismiss(this)" aria-label="Đóng">×</button>
                </div>
            @elseif ($errors->any())
                {{-- form gui khong hop le (khi khong dung XHR): tom tat de nguoi dung biet vi sao trang tai lai --}}
                <div class="flash flash-error" role="alert">
                    <x-icon name="info" size="16" />
                    <span>Chưa thể gửi — vui lòng kiểm tra lại {{ $errors->count() }} mục được đánh dấu bên dưới.</span>
                    <button type="button" onclick="tsDismiss(this)" aria-label="Đóng">×</button>
                </div>
            @endif

            {{-- tieu de trang: breadcrumb (tuy chon) / tieu de / mo ta ngan / hanh dong ben phai (tuy chon) --}}
            @hasSection('screen-title')
                <div class="screen-head">
                    <div class="screen-head-txt">
                        @hasSection('screen-kicker')
                            <nav class="crumbs" aria-label="Breadcrumb">@yield('screen-kicker')</nav>
                        @endif
                        <h1 class="screen-title">@yield('screen-title')</h1>
                        @hasSection('screen-sub')
                            <p class="screen-sub">@yield('screen-sub')</p>
                        @endif
                    </div>
                    @hasSection('screen-side')
                        <div class="screen-side">@yield('screen-side')</div>
                    @endif
                </div>
            @endif

            @yield('content')
        </div>

        <footer class="ts-footer">
            <a class="ts-footer-brand" href="{{ route('home') }}">
                <span class="ts-logo-mark ts-logo-mark-sm"><x-icon name="mic" size="13" /></span>
                <span>TalentStage</span>
            </a>
            <span class="ts-footer-tag">Nền tảng trình diễn tài năng trực tuyến</span>
            <nav class="ts-footer-links" aria-label="Liên kết chân trang">
                <a href="{{ url('/explore') }}">Khám phá</a>
                <a href="{{ url('/contests') }}">Cuộc thi</a>
                <a href="{{ url('/groups') }}">Nhóm</a>
                <a href="{{ route('sitemap') }}">Sơ đồ trang</a>
            </nav>
            <span class="ts-footer-copy">© {{ date('Y') }} TalentStage</span>
        </footer>
    </main>
</div>

{{-- Hop thoai chao mung sau khi tao tai khoan thanh cong --}}
@if (session('welcome'))
    @php $w = session('welcome'); @endphp
    <div class="dialog-backdrop" data-dialog>
        <div class="dialog welcome" role="dialog" aria-modal="true" aria-labelledby="welcomeTitle">
            <span class="welcome-ico"><x-icon name="check" size="30" /></span>
            <h2 id="welcomeTitle" class="dialog-title" style="font-size: 24px; margin: 0">Tạo tài khoản thành công!</h2>
            <p class="dialog-body" style="margin: 0">
                Chào mừng <strong>{{ $w['name'] }}</strong> đến với TalentStage. Bạn đã được đăng nhập với vai trò
                <strong>{{ ucfirst($w['role']) }}</strong>{{ $w['role'] === 'creator' ? ' — hãy đăng tiết mục đầu tiên để bắt đầu.' : ' — hãy khám phá và kết nối với các creator.' }}
            </p>
            <div class="dialog-actions" style="justify-content: center; flex-wrap: wrap">
                @if ($w['role'] === 'creator')
                    <a class="btn btn-primary" href="{{ url('/videos/create') }}"><x-icon name="upload" size="15" /> Đăng tiết mục đầu tiên</a>
                @else
                    <a class="btn btn-primary" href="{{ url('/explore') }}"><x-icon name="compass" size="15" /> Khám phá tài năng</a>
                @endif
                <button type="button" class="btn btn-secondary" onclick="tsCloseDialog(this)">Để sau</button>
            </div>
        </div>
    </div>
@endif

@stack('scripts')
</body>
</html>
