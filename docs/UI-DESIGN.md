# TalentStage — Hệ thống thiết kế UI

Nguồn gốc: artifact mockup `claude.ai/code/artifact/b4e21537-e823-414b-a2fa-bb9b026085fe` (bản Public Sans) — lấy **màu sắc, typography, khoảng cách, thành phần**. Phần khung trang (sidebar, header, footer, tiêu đề trang) đã được dựng lại theo hướng **website người dùng thật**: không còn nhóm đánh số 01–05, tag FR, nhãn song ngữ hay chú thích "[ thumbnail ]" của bản mockup.

Mã nguồn: tokens/components trong **`public/css/talentstage.css`**; tương tác nhỏ trong **`public/js/talentstage.js`** (drawer, menu tài khoản, flash, panel mở/đóng, dropzone); icon SVG inline qua component **`<x-icon name="…" />`** (`resources/views/components/icon.blade.php`, nét Lucide); fonts tự host trong `public/fonts/` (`public/css/fonts.css`).

## 1. Ngôn ngữ thị giác

| Yếu tố | Giá trị |
|---|---|
| Nền / bề mặt | `#fbfaf8` (giấy ấm) / `#eae9e9` |
| Mực chữ | `#201f1d` |
| Accent | `#b68235` (vàng đồng) — ramp 100–900; badge đếm dùng `accent-700` nền + chữ trắng (trong sidebar tối: nền `--color-gold` + chữ ink) |
| Khung tối "sân khấu" | `--color-ink #241f18` / `--color-ink-2 #1c1813` cho **sidebar** và **hero / cover đăng nhập**; chữ trên nền ink: `--color-ink-text #f4efe6` (chính) · `--color-ink-muted #b6ad9c` (phụ) · `--color-ink-faint #857b68` (nhãn nhóm); điểm nhấn / mục active: `--color-gold #f0c780`, vạch active `--color-gold-2 #d8a353` |
| Nút — 3 cấp | `.btn-primary` = nút **đặc** (gradient accent 500→600, chữ trắng, bóng nhẹ) chỉ cho hành động chính của màn hình (Đăng tiết mục, Đăng ký, Xem ngay, Duyệt, Gửi…) · `.btn-secondary` = nền kem `accent-100` + viền vàng nhạt + chữ `accent-800` cho hành động phụ · `.btn-ghost` = chỉ chữ `accent-700` |
| Màu thể loại | `--c-music #6b4bb8` · `--c-dance #c2410c` · `--c-visual #1d6fa5` · `--c-acting #a83252` · `--c-food #b07417` · `--c-sport #2f7d5e` — gán qua `Category::colorVar()` → `style="--cat: var(--c-…)"` |
| Trạng thái duyệt | duyệt = `--c-sport` · chờ = `--c-food` · từ chối = `--c-acting` — `.tag-status[data-status]`: **nền mềm** 13% màu + chữ đậm cùng tông (không viền) |
| Chip thể loại | `.cat-chip` nền mềm 13% `--cat` + chữ đậm 78% `--cat`; hover/active nền 22%, active thêm viền trong |
| Chữ | **Public Sans** (variable) cho toàn bộ; heading 600, wordmark 700; body 15px/1.55; `-webkit-font-smoothing: antialiased` |
| Cỡ chữ | tiêu đề trang 30px (mobile 26) · h2 28–32 · h3 20 · nav 13.5 · meta 11.5 · kicker 10px tracked uppercase (chỉ dùng cho nhãn thẻ, không dùng làm mã FR) |
| Spacing | 4.6 / 9.2 / 13.8 / 18.4 / 27.6 / 36.8px (`--space-1…8`) |
| Bo góc / bóng | 2 / 4 / 7px; ô tìm kiếm & chip: pill 999px; bóng `--shadow-sm/md/lg` chỉ khi hover / menu / dialog |
| Placeholder ảnh | **không** dùng chữ chú thích: nền thumbnail thiếu (`.video-thumb`, `.hatch-mid`…) → **gradient 2 lớp theo màu thể loại** (`--cat` 30% sáng ở góc → 62% trộn ink ở dưới + quầng sáng góc phải trên); nút play `.thumb-ph` **trắng nổi có bóng**, icon màu `--cat` đậm; ô nhỏ `.hatch-mid` → play trắng mờ; player chưa có tệp `.player:has(.player-empty)` → gradient tối theo `--cat`; hero trang chủ & cover đăng nhập → nền ink + 2 quầng sáng vàng/tím ("ánh đèn sân khấu") + icon mic vàng mờ; avatar thiếu → chữ cái đầu trên nền `accent-100`. Luôn đặt `style="--cat: {{ $video->category->colorVar() }}"` cho ô thumbnail |
| Tương phản (WCAG) | Vàng 500 `#b68235` trên nền kem chỉ ~3.5:1 → **không dùng cho chữ nhỏ**: link/label/kicker dùng `accent-700`; chữ phụ (`.meta`, `.muted-i`, `.screen-sub`, `.label-up`, footer…) dùng `neutral-700` (không dùng 600); nhãn thể loại dưới thẻ video pha 85% `--cat` + đen. Vàng 500 chỉ dành cho nút đặc chữ trắng, icon, viền, sao |
| Ngôn ngữ | Toàn bộ nhãn UI **tiếng Việt**; `APP_LOCALE=vi` + `lang/vi/{validation,auth,pagination}.php` nên thông báo lỗi form cũng tiếng Việt (rule thiếu tự rơi về tiếng Anh của framework); thời gian tương đối tiếng Việt (`Carbon::setLocale('vi')`); tên vai trò Creator/Mentor/Admin giữ nguyên như thuật ngữ sản phẩm |

## 2. Khung trang (`layouts/app.blade.php`)

- **Sidebar 250px** (sticky; drawer trên mobile; desktop **thu gọn được** thành rail 72px chỉ icon bằng **cùng nút ☰** `.ts-menu-btn` ở đầu header (luôn hiển thị; JS `tsMenu()`: ≤1080px mở drawer, >1080px thu gọn/mở rộng; nút sáng nền `accent-100` khi đang thu gọn) — `html.nav-collapsed`, nhớ qua `localStorage['ts-nav']`, script trong `<head>` khôi phục trước khi vẽ để không nháy; khi thu gọn: ẩn chữ, badge thành chấm nhỏ trên icon, nhóm cách nhau bằng vạch, tooltip = `title` lấy từ `data-tip`) — **nền tối ink** (gradient `#262118 → #1c1813`, "khung sân khấu"): logo mark vuông bo góc gradient vàng sáng + wordmark **TalentStage** kem + tagline "Sân khấu tài năng trực tuyến"; nav dạng **icon + nhãn** chữ `ink-muted`, chia nhóm nhỏ (nhãn nhóm `ink-faint`):
  - *(không nhãn)*: Trang chủ · Tìm kiếm · Bảng tin (đăng nhập) · Cuộc thi
  - **Cộng đồng**: Nhóm · Tin nhắn (creator/mentor, badge chưa đọc) · Thông báo (badge chưa đọc)
  - **Kênh của tôi** (creator): Đăng tiết mục · Tiết mục của tôi · Hồ sơ của tôi — user khác: **Tài khoản**: Hồ sơ của tôi
  - **Quản trị** (admin): Tổng quan · Kiểm duyệt (badge số video chờ) · Người dùng · Danh mục · Cuộc thi
  - Mục active: nền vàng 14% mờ, viền trái `gold-2`, chữ + icon `gold` 600; hover: nền trắng 6%. Badge đếm nền `gold` chữ ink. Chân sidebar: thẻ user (avatar · tên · vai trò) + nút icon Đăng xuất; khách: Đăng nhập (nút đặc) / Tạo tài khoản (viền sáng, chữ kem).
- **Header** sticky (nền kem mờ blur — giữ sáng để tương phản với sidebar tối): nút ☰ (mobile), ô tìm kiếm pill có icon, 4 chip thể loại `.cat-chip`, bên phải: nút **Đăng tiết mục** (creator, nút đặc; ≤560px chỉ còn icon để nhường chỗ ô tìm), chuông thông báo `.icon-btn` + `.badge-dot`, **menu tài khoản** `details.menu > .ts-user` (Hồ sơ, Sửa hồ sơ, Tiết mục của tôi, Tin nhắn, Khu quản trị, Đăng xuất); khách: Đăng nhập / Đăng ký.
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
- Form: `.field` + `.label-up`, `.input`, `.is-invalid` + `.err-msg`, `.input-wrap` + `.input-check` (tích xanh / ✕ đỏ, dùng cho cặp mật khẩu `[data-password-pair]` — kiểm tra khớp ngay khi gõ, chặn submit), `.field-hint`, `.seg`, `.radio`, `.dropzone` (+ `-ico/-title/-hint/-name`, input `.visually-hidden`)
- Thẻ: `.card` (`-kicker/-title/-body/-meta`), `.tag` (`-accent/-outline/-neutral/-muted/-status`), `.cat-chip` (`.active`)
- Bảng `.table` **luôn bọc trong `.table-wrap`** (cuộn ngang gọn trong khung trên mobile, bóng mờ 2 mép; ô không ngắt chữ — cần xuống dòng thì thêm `td.wrap`); hộp thoại `.dialog*`; flash `.flash` / `.flash-error`; panel `.reveal` + `.reveal-inner`; menu `.menu` / `.menu-panel` / `.menu-item` / `.menu-head` / `.menu-sep`; breadcrumb `.crumbs` (+ `.sep`)
- Đặc thù app: `.video-card` (+`.video-thumb`, `.thumb-ph`, `.thumb-badge` âm thanh/thời lượng — partial `partials/thumb`, `.video-card-cat`), `.attach-card/.progress/.steps`, `.noti-ico`, `.hero-plate/.hero-box/.ph-art/.ph-art-ico`, `.player/.player-empty`, `.rank-row`+`.rank-num`, `.phase-strip`+`.phase.current`, `.bubble.me/.them`, `.stat`, `.line-tabs`, `.auth-tabs`, `.stars/.star[data-on]`, `.avatar` (`-lg/-xl`), `.kicker`, `.meta`, `.muted-i`, `.grid-4/.grid-2`

## 5. Trang chính

| Trang | URL | Ghi chú |
|---|---|---|
| Trang chủ | `/` | Hero (`.ph-art` khi chưa có ảnh) + "Đang thịnh hành" + lưới video 4 cột + cuộc thi đang diễn ra |
| Tìm kiếm | `/explore` | **Một ô tìm kiếm duy nhất (ở header)** — live search: gõ là kết quả cập nhật ngay (`[data-explore-root]`, controller trả partial `explore/_results` khi `$request->ajax()`); tìm theo tiêu đề, mô tả, **tên creator**, thể loại (`Video::scopeSearch`); hàng "Creator phù hợp"; chip thể loại + sắp xếp + phân trang cũng tải qua XHR, URL đồng bộ bằng `history.replaceState` |
| Xem video | `/videos/{id}` | `.player` (video) hoặc `.player-audio` (bản thu âm: cover màu thể loại + `<audio>`), h1 tiêu đề, tác giả + Theo dõi, Thích, lượt xem, thời lượng, chấm sao, chip thể loại, bình luận + trả lời `.reveal`; cột phải: nhận xét mentor, quản lý, Xem tiếp |
| Hồ sơ | `/users/{id}`, `/profile/edit` | h1 tên + tag vai trò + nơi ở + thành tích + 4 `.stat`; hành động cột phải; lưới video |
| Đăng tiết mục | `/videos/create`, `/my-videos`, `/videos/{id}/edit` | `.dropzone` kéo thả (video **hoặc âm thanh**) → `.attach-card` kiểu "Your work" (xem trước · tên · loại/dung lượng/thời lượng · nút gỡ); gửi bằng XHR: `.progress` + trạng thái, lỗi theo trường `[data-error-for]`, thành công → chuyển tới danh sách kèm flash; panel `.steps` "tiết mục đi đâu sau khi gửi"; bảng trạng thái duyệt `.tag-status` |
| Bảng tin / Thông báo | `/feed`, `/notifications` | Card hoạt động; gợi ý theo dõi |
| Nhóm | `/groups`, `/groups/{id}`, `/groups/create` | Danh sách trái + bảng thảo luận phải |
| Tin nhắn | `/messages`, `/messages/{user}` | Threads + khung chat `.bubble` |
| Cuộc thi | `/contests`, `/contests/{id}` | Breadcrumb, `.phase-strip`, bảng xếp hạng, lưới bài dự thi + Bình chọn |
| Đăng nhập / Đăng ký | `/login`, `/register` | **Không** dùng `screen-title`; một thẻ `.auth-wrap > .auth-card` căn giữa cả ngang lẫn dọc (max 1040px): nửa trái cover ink `auth/_cover` ("ánh đèn sân khấu"), nửa phải `.auth-panel` nền sáng, nội dung ≤400px, tab `.auth-tabs` làm tiêu đề + `.auth-lead`, input 42px, nút đặc full-width, `.auth-foot` link chuyển trang; <900px một cột, cover thu thành dải thương hiệu mỏng |
| Quản trị | `/admin/*` | Breadcrumb "Quản trị / …"; kiểm duyệt: tab gạch chân, hàng đợi, panel từ chối `.reveal` |
| Sơ đồ trang | `/sitemap` | Trang yêu cầu của đề bài — vẫn liệt kê nhóm chức năng kèm mã FR |

## 6. Quy tắc khi dựng màn hình mới

1. `@extends('layouts.app')`; khai báo `screen-title` + `screen-sub` (tiếng Việt, mô tả ngắn cho người dùng); trang con dùng `@section('screen-kicker')…@endsection` làm breadcrumb với link về trang cha.
2. **Không** đưa mã yêu cầu (FR1…), nhãn song ngữ "VN · EN" hay ghi chú thiết kế vào giao diện — chỉ tiếng Việt tự nhiên (trừ tên vai trò).
3. Nút hành động chính kèm icon `<x-icon>` 14–15px; nav/menu icon 16–18px.
4. Ảnh chưa có → `.thumb-ph` / `.hatch-mid` / `.ph-art`, không để ô trống trắng, không dùng chữ chú thích; nhớ đặt `--cat` theo thể loại để gradient đúng màu.
8. Mỗi màn hình chỉ **một** nút đặc `.btn-primary` cho hành động chính; hành động phụ dùng `.btn-secondary`, hành động nhẹ / huỷ dùng `.btn-ghost`. Bảng dữ liệu bọc `.table-wrap`.
5. Số liệu `.num`/`.meta` (tabular); xếp hạng `.rank-num`; trạng thái `.tag-status[data-status]`; thể loại kèm màu (`--cat`, `.cat-chip`).
6. Ẩn/hiện có chuyển động: `.reveal` + `tsToggle()`; hover/focus mới phải kèm `transition` dùng token `--dur-*`/`--ease-*`.
7. Không thêm thư viện CSS/JS ngoài — toàn bộ là `talentstage.css` + `talentstage.js` vanilla.
