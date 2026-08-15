# TalentStage — Hệ thống thiết kế UI

Nguồn gốc: artifact mockup `claude.ai/code/artifact/b4e21537-e823-414b-a2fa-bb9b026085fe` (bản Public Sans) — lấy **màu sắc, typography, khoảng cách, thành phần**. Phần khung trang (sidebar, header, footer, tiêu đề trang) đã được dựng lại theo hướng **website người dùng thật**: không còn nhóm đánh số 01–05, tag FR, nhãn song ngữ hay chú thích "[ thumbnail ]" của bản mockup.

Mã nguồn: tokens/components trong **`public/css/talentstage.css`**; tương tác nhỏ trong **`public/js/talentstage.js`** (drawer, menu tài khoản, flash, panel mở/đóng, dropzone); icon SVG inline qua component **`<x-icon name="…" />`** (`resources/views/components/icon.blade.php`, nét Lucide); fonts tự host trong `public/fonts/` (`public/css/fonts.css`).

## 1. Ngôn ngữ thị giác

| Yếu tố | Giá trị |
|---|---|
| Nền / bề mặt | `#fbfaf8` (giấy ấm) / `#eae9e9` |
| Mực chữ | `#201f1d` |
| Accent | `#b68235` (vàng đồng) — ramp 100–900; badge đếm dùng `accent-700` nền + chữ trắng |
| Màu thể loại | `--c-music #6b4bb8` · `--c-dance #c2410c` · `--c-visual #1d6fa5` · `--c-acting #a83252` · `--c-food #b07417` · `--c-sport #2f7d5e` — gán qua `Category::colorVar()` → `style="--cat: var(--c-…)"` |
| Trạng thái duyệt | duyệt = `--c-sport` · chờ = `--c-food` · từ chối = `--c-acting` — `.tag-status[data-status]` |
| Chữ | **Public Sans** (variable) cho toàn bộ; heading 600, wordmark 700; body 15px/1.55; `-webkit-font-smoothing: antialiased` |
| Cỡ chữ | tiêu đề trang 30px (mobile 26) · h2 28–32 · h3 20 · nav 13.5 · meta 11.5 · kicker 10px tracked uppercase (chỉ dùng cho nhãn thẻ, không dùng làm mã FR) |
| Spacing | 4.6 / 9.2 / 13.8 / 18.4 / 27.6 / 36.8px (`--space-1…8`) |
| Bo góc / bóng | 2 / 4 / 7px; ô tìm kiếm & chip: pill 999px; bóng `--shadow-sm/md/lg` chỉ khi hover / menu / dialog |
| Placeholder ảnh | **không** dùng chữ chú thích: thumbnail thiếu → `.thumb-ph` (vòng tròn tint thể loại + icon play); ô nhỏ `.hatch-mid` → gradient dịu + play mờ; hero/cover → `.ph-art` (gradient + icon mic mờ); avatar thiếu → chữ cái đầu trên nền `accent-100` |
| Ngôn ngữ | Toàn bộ nhãn UI **tiếng Việt** (thời gian tương đối cũng tiếng Việt — `Carbon::setLocale('vi')` trong `AppServiceProvider`); tên vai trò Creator/Mentor/Admin giữ nguyên như thuật ngữ sản phẩm |

## 2. Khung trang (`layouts/app.blade.php`)

- **Sidebar 250px** (sticky, drawer trên mobile): logo mark vuông bo góc gradient vàng + wordmark **TalentStage** + tagline "Sân khấu tài năng trực tuyến"; nav dạng **icon + nhãn**, chia nhóm nhỏ:
  - *(không nhãn)*: Trang chủ · Tìm kiếm · Bảng tin (đăng nhập) · Cuộc thi
  - **Cộng đồng**: Nhóm · Tin nhắn (creator/mentor, badge chưa đọc) · Thông báo (badge chưa đọc)
  - **Kênh của tôi** (creator): Đăng video · Video của tôi · Hồ sơ của tôi — user khác: **Tài khoản**: Hồ sơ của tôi
  - **Quản trị** (admin): Tổng quan · Kiểm duyệt (badge số video chờ) · Người dùng · Danh mục · Cuộc thi
  - Mục active: nền `accent-100`, viền trái vàng, chữ 600, icon vàng. Chân sidebar: thẻ user (avatar · tên · vai trò) + nút icon Đăng xuất; khách: Đăng nhập / Tạo tài khoản.
- **Header** sticky (nền mờ blur): nút ☰ (mobile), ô tìm kiếm pill có icon, 4 chip thể loại `.cat-chip`, bên phải: nút **Đăng video** (creator), chuông thông báo `.icon-btn` + `.badge-dot`, **menu tài khoản** `details.menu > .ts-user` (Hồ sơ, Sửa hồ sơ, Video của tôi, Tin nhắn, Khu quản trị, Đăng xuất); khách: Đăng nhập / Đăng ký.
- **Tiêu đề trang**: `screen-kicker` = breadcrumb (`.crumbs`, dùng block section để chứa link) → `screen-title` (30px/600) → `screen-sub` (mô tả 13.5px). Trang xem video và hồ sơ **không** dùng khối này — tiêu đề video / tên người dùng chính là h1 trong nội dung.
- **Footer**: logo nhỏ + tagline · liên kết Khám phá / Cuộc thi / Nhóm / Sơ đồ trang · © năm.

## 3. Chuyển động (motion)

Token chung: `--ease-out` (cubic-bezier .22 1 .36 1), `--ease-spring` (nảy nhẹ), `--dur-1…4` = 120 / 200 / 320 / 520ms.

- **Vào trang**: khối con của `.ts-content` fade-rise 10px so le 60ms; thẻ trong `.grid-4/.grid-2` so le thêm 40ms (`animation-fill-mode: backwards` để hover vẫn hoạt động).
- **Chuyển trang** (MPA View Transitions): sidebar/header giữ nguyên, nội dung cross-fade.
- **Hover/press**: nút, thẻ (`.video-card` nhấc −2px + ảnh zoom + play-icon phóng), nav (icon đổi màu, chữ trượt 2px), chip, logo mark xoay nhẹ, `.icon-btn`; **focus** ring vàng 3px; **tab gạch chân** trượt; **sao** xem trước khi rê; **menu tài khoản** scale-in; **panel** `.reveal`; **dropzone** đổi màu khi kéo tệp vào; **flash** đóng mờ; **drawer** + màn che.
- Tôn trọng `prefers-reduced-motion`.

## 4. Component chính (class trong talentstage.css)

- Nút: `.btn` + `-primary` / `-secondary` / `-ghost`, size `-sm` `-xs`; nút icon `.icon-btn`; badge `.badge` / `.badge-dot`
- Form: `.field` + `.label-up`, `.input`, `.is-invalid` + `.err-msg`, `.seg`, `.radio`, `.dropzone` (+ `-ico/-title/-hint/-name`, input `.visually-hidden`)
- Thẻ: `.card` (`-kicker/-title/-body/-meta`), `.tag` (`-accent/-outline/-neutral/-muted/-status`), `.cat-chip` (`.active`)
- Bảng `.table`; hộp thoại `.dialog*`; flash `.flash` / `.flash-error`; panel `.reveal` + `.reveal-inner`; menu `.menu` / `.menu-panel` / `.menu-item` / `.menu-head` / `.menu-sep`; breadcrumb `.crumbs` (+ `.sep`)
- Đặc thù app: `.video-card` (+`.video-thumb`, `.thumb-ph`, `.video-card-cat`), `.hero-plate/.hero-box/.ph-art/.ph-art-ico`, `.player/.player-empty`, `.rank-row`+`.rank-num`, `.phase-strip`+`.phase.current`, `.bubble.me/.them`, `.stat`, `.line-tabs`, `.auth-tabs`, `.stars/.star[data-on]`, `.avatar` (`-lg/-xl`), `.kicker`, `.meta`, `.muted-i`, `.grid-4/.grid-2`

## 5. Trang chính

| Trang | URL | Ghi chú |
|---|---|---|
| Trang chủ | `/` | Hero (`.ph-art` khi chưa có ảnh) + "Đang thịnh hành" + lưới video 4 cột + cuộc thi đang diễn ra |
| Tìm kiếm | `/explore` | Ô tìm + sắp xếp + dãy `.cat-chip` lọc thể loại (giữ `q`/`sort`) |
| Xem video | `/videos/{id}` | `.player` (empty-state icon), h1 tiêu đề, tác giả + Theo dõi, Thích, chấm sao, chip thể loại, bình luận + trả lời `.reveal`; cột phải: nhận xét mentor, quản lý, Xem tiếp |
| Hồ sơ | `/users/{id}`, `/profile/edit` | h1 tên + tag vai trò + nơi ở + thành tích + 4 `.stat`; hành động cột phải; lưới video |
| Đăng video | `/videos/create`, `/my-videos`, `/videos/{id}/edit` | `.dropzone` kéo thả + form; bảng trạng thái duyệt `.tag-status` |
| Bảng tin / Thông báo | `/feed`, `/notifications` | Card hoạt động; gợi ý theo dõi |
| Nhóm | `/groups`, `/groups/{id}`, `/groups/create` | Danh sách trái + bảng thảo luận phải |
| Tin nhắn | `/messages`, `/messages/{user}` | Threads + khung chat `.bubble` |
| Cuộc thi | `/contests`, `/contests/{id}` | Breadcrumb, `.phase-strip`, bảng xếp hạng, lưới bài dự thi + Bình chọn |
| Đăng nhập / Đăng ký | `/login`, `/register` | Panel giới thiệu `auth/_cover` + form; tab Đăng nhập / Đăng ký |
| Quản trị | `/admin/*` | Breadcrumb "Quản trị / …"; kiểm duyệt: tab gạch chân, hàng đợi, panel từ chối `.reveal` |
| Sơ đồ trang | `/sitemap` | Trang yêu cầu của đề bài — vẫn liệt kê nhóm chức năng kèm mã FR |

## 6. Quy tắc khi dựng màn hình mới

1. `@extends('layouts.app')`; khai báo `screen-title` + `screen-sub` (tiếng Việt, mô tả ngắn cho người dùng); trang con dùng `@section('screen-kicker')…@endsection` làm breadcrumb với link về trang cha.
2. **Không** đưa mã yêu cầu (FR1…), nhãn song ngữ "VN · EN" hay ghi chú thiết kế vào giao diện — chỉ tiếng Việt tự nhiên (trừ tên vai trò).
3. Nút hành động chính kèm icon `<x-icon>` 14–15px; nav/menu icon 16–18px.
4. Ảnh chưa có → `.thumb-ph` / `.hatch-mid` / `.ph-art`, không để ô trống trắng, không dùng chữ chú thích.
5. Số liệu `.num`/`.meta` (tabular); xếp hạng `.rank-num`; trạng thái `.tag-status[data-status]`; thể loại kèm màu (`--cat`, `.cat-chip`).
6. Ẩn/hiện có chuyển động: `.reveal` + `tsToggle()`; hover/focus mới phải kèm `transition` dùng token `--dur-*`/`--ease-*`.
7. Không thêm thư viện CSS/JS ngoài — toàn bộ là `talentstage.css` + `talentstage.js` vanilla.
