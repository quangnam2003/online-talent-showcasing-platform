@extends('layouts.app')

@section('title', 'Sơ đồ trang — TalentStage')

@section('screen-kicker', 'Sitemap · Toàn bộ màn hình')
@section('screen-title', 'Sơ đồ trang')
@section('screen-sub', 'Mỗi màn hình gắn với chức năng (FR) và vai trò được truy cập — nguồn: docs/SITEMAP.md')

@section('content')
@php
    // Dong bo voi docs/SITEMAP.md
    $sections = [
        ['01', 'Chung & Xác thực', 'FR1 · Access', [
            ['Trang chủ — Khám phá', '/', 'Tất cả'],
            ['Đăng ký', '/register', 'Khách'],
            ['Đăng nhập', '/login', 'Khách'],
            ['Sơ đồ trang (trang này)', '/sitemap', 'Tất cả'],
        ]],
        ['02', 'Hồ sơ cá nhân', 'FR1 · Profile', [
            ['Xem trang cá nhân', '/users/1', 'Tất cả'],
            ['Sửa hồ sơ', '/profile/edit', 'Đã đăng nhập'],
        ]],
        ['03', 'Video & Kiểm duyệt', 'FR2 + FR8 · Content', [
            ['Đăng video & theo dõi duyệt', '/videos/create', 'Creator'],
            ['Xem video', '/videos/1', 'Tất cả (public) / Chủ sở hữu (private)'],
            ['Video của tôi', '/my-videos', 'Creator'],
            ['Sửa video', '/videos/1/edit', 'Chủ sở hữu'],
        ]],
        ['04', 'Khám phá', 'FR3 · Discovery', [
            ['Tìm kiếm, lọc, trending', '/explore', 'Tất cả'],
        ]],
        ['05', 'Tương tác', 'FR4 · Engagement', [
            ['Bảng tin theo dõi', '/feed', 'Đã đăng nhập'],
            ['Reaction, bình luận, follow', '/videos/1', 'Trong trang video & hồ sơ'],
        ]],
        ['06', 'Nhóm & Thảo luận', 'FR5 · Groups', [
            ['Danh sách nhóm', '/groups', 'Tất cả'],
            ['Tạo nhóm', '/groups/create', 'Đã đăng nhập'],
            ['Trang nhóm — bảng thảo luận', '/groups/1', 'Thành viên nhóm'],
        ]],
        ['07', 'Nhắn tin', 'FR6 · Mentorship', [
            ['Hộp thư', '/messages', 'Creator, Mentor'],
            ['Hội thoại 1-1', '/messages/2', 'Creator, Mentor'],
        ]],
        ['08', 'Cuộc thi', 'FR7 · Contest', [
            ['Danh sách cuộc thi', '/contests', 'Tất cả'],
            ['Chi tiết + bảng xếp hạng', '/contests/1', 'Tất cả'],
        ]],
        ['09', 'Thông báo', 'System', [
            ['Danh sách thông báo', '/notifications', 'Đã đăng nhập'],
        ]],
        ['10', 'Khu quản trị', 'FR8 · Moderation', [
            ['Dashboard', '/admin', 'Admin'],
            ['Kiểm duyệt video', '/admin/videos', 'Admin'],
            ['Quản lý người dùng', '/admin/users', 'Admin'],
            ['Quản lý danh mục', '/admin/categories', 'Admin'],
            ['Quản lý cuộc thi', '/admin/contests', 'Admin'],
        ]],
    ];
@endphp

<div class="grid-2">
    @foreach ($sections as [$no, $title, $fr, $items])
        <div class="card" style="gap: var(--space-3); padding: var(--space-4)">
            <div style="display: flex; align-items: baseline; justify-content: space-between; gap: var(--space-2); border-bottom: 1px solid var(--color-divider); padding-bottom: var(--space-2)">
                <span style="display: flex; align-items: baseline; gap: var(--space-2)">
                    <span class="rank-num" style="font-size: 20px; width: auto">{{ $no }}</span>
                    <span class="card-title" style="font-size: 19px">{{ $title }}</span>
                </span>
                <span class="tag tag-outline" style="font-size: 9.5px">{{ $fr }}</span>
            </div>
            <div style="display: flex; flex-direction: column; gap: var(--space-2)">
                @foreach ($items as [$label, $path, $roles])
                    <div style="display: flex; justify-content: space-between; align-items: baseline; gap: var(--space-2)">
                        <a href="{{ url($path) }}" style="font-size: 13.5px">{{ $label }}</a>
                        <span class="meta" style="text-align: right; flex: none; max-width: 45%">{{ $roles }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</div>

<p class="muted-i" style="text-align: center">
    Toàn bộ màn hình đã hoạt động — trang cần quyền sẽ chuyển hướng đăng nhập hoặc báo 403 đúng vai trò.
    Chi tiết: <code style="font-size: 11px">docs/SITEMAP.md</code>.
</p>
@endsection
