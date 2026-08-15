# TalentStage — Hệ thống thiết kế UI

Nguồn: artifact mockup `claude.ai/code/artifact/b4e21537-e823-414b-a2fa-bb9b026085fe` (10 màn hình, bản **Public Sans**).
Toàn bộ tokens/components đã port sang **`public/css/talentstage.css`**; tương tác nhỏ (drawer, flash, panel mở/đóng) trong **`public/js/talentstage.js`**; fonts tự host trong **`public/fonts/`** (Public Sans variable + italic 400, có subset tiếng Việt — khai báo ở `public/css/fonts.css`).

## 1. Ngôn ngữ thị giác

| Yếu tố | Giá trị |
|---|---|
| Nền / bề mặt | `#fbfaf8` (giấy ấm) / `#eae9e9` |
| Mực chữ | `#201f1d` |
| Accent | `#b68235` (vàng đồng) — ramp 100–900 |
| Màu thể loại | `--c-music #6b4bb8` · `--c-dance #c2410c` · `--c-visual #1d6fa5` · `--c-acting #a83252` · `--c-food #b07417` · `--c-sport #2f7d5e` — gán qua `Category::colorVar()` → `style="--cat: var(--c-…)"` |
| Trạng thái duyệt | duyệt = `--c-sport` (xanh) · chờ = `--c-food` (vàng) · từ chối = `--c-acting` (đỏ) — class `.tag-status[data-status]` |
| Chữ | **Public Sans** cho cả heading lẫn body; heading weight 600, body 400 15px/1.55; `-webkit-font-smoothing: antialiased` |
| Cỡ chữ | h1 42 · screen-title 40 · h2 32 · h3 25 · h4 20; kicker 10px tracked 0.22em uppercase; nhãn form 11px 0.18em; meta 11.5px; nút 12–14px |
| Spacing | 4.6 / 9.2 / 13.8 / 18.4 / 27.6 / 36.8px (`--space-1…8`) |
| Bo góc / bóng | 2 / 4 / 7px; bóng mực nhạt `--shadow-sm/md/lg` (chỉ dùng khi hover/dialog) |
| Nét đặc trưng | nhãn song ngữ "VN · EN", kicker uppercase tracked, số tabular, ghi chú nghiêng mờ, kẻ hairline, khung ảnh `.plate` (viền surface 6px + sepia), nền chéo `.hatch` cho placeholder, thẻ video có **viền trên 3px màu thể loại** + thumbnail tint 12% |

## 2. Chuyển động (motion)

Một bộ token dùng chung: `--ease-out` (cubic-bezier .22 1 .36 1), `--ease-spring` (nảy nhẹ), `--dur-1…4` = 120 / 200 / 320 / 520ms.

- **Vào trang**: mỗi khối con của `.ts-content` fade-rise 10px, so le 60ms; thẻ trong `.grid-4/.grid-2` so le thêm 40ms. Dùng `animation-fill-mode: backwards` để sau khi chạy xong hover vẫn hoạt động.
- **Chuyển trang** (MPA View Transitions): `@view-transition { navigation: auto }` — sidebar/header giữ nguyên (`view-transition-name`), phần nội dung cross-fade 160/240ms. Trình duyệt cũ bỏ qua.
- **Hover/press**: nút đổi nền/viền 200ms, nhấn co 0.985; thẻ link nổi viền + bóng; `.video-card` nhấc −2px + bóng md + ảnh zoom 1.04; nav item chữ trượt 2px; chip thể loại nhấc −1px; hàng rank số trượt.
- **Focus**: `.input` viền accent + vòng 3px accent 16%; nhãn `.field` đổi màu theo focus-within.
- **Tab gạch chân** `.line-tab::after` scaleX 0→1 từ trái; **sao chấm điểm** `.stars-hover` xem trước khi rê chuột, phóng 1.22 (spring).
- **Panel mở/đóng** `.reveal` (grid-rows 0fr→1fr + opacity + visibility) — dùng `tsToggle(btn, selector)` trong phạm vi `[data-reveal-scope]`; **flash** đóng bằng `tsDismiss(btn)`; **drawer** mobile trượt + màn che `.ts-scrim`.
- Tôn trọng `prefers-reduced-motion: reduce` (tắt toàn bộ animation/transition/view-transition).

## 3. Component chính (class trong talentstage.css)

- Nút: `.btn` + `-primary` (viền vàng) / `-secondary` (viền mảnh) / `-ghost`, size `-sm` `-xs`; `.btn-like` (nảy khi bấm)
- Form: `.field` + `.label-up` (nhãn uppercase 11px), `.input`, `.is-invalid` + `.err-msg`, `.seg` (segmented radio), `.radio`
- Thẻ: `.card` (`-kicker/-title/-body/-meta`), `.tag` (`-accent/-outline/-neutral/-muted/-status`), `.cat-chip` (pill màu thể loại, `.active`)
- Bảng: `.table`; hộp thoại `.dialog*`; flash: `.flash` / `.flash-error`; panel: `.reveal` + `.reveal-inner`
- Đặc thù app: `.video-card` (+`.video-thumb` 16:10, `.video-card-cat` màu thể loại), `.hero-plate/.hero-box`, `.rank-row`+`.rank-num`, `.phase-strip`+`.phase.current`, `.bubble.me/.them`, `.stat`, `.line-tabs`, `.auth-tabs`, `.stars/.star[data-on]`, `.avatar` (`-lg/-xl`), `.kicker`, `.meta`, `.muted-i`, `.slot-note`, `.grid-4/.grid-2`

## 4. Layout shell (`layouts/app.blade.php`)

- **Sidebar 268px** trái, sticky: brand + tagline uppercase; nav **4–5 nhóm đánh số** (01 Truy cập & nội dung → 05 Quản trị), mỗi mục = nhãn VN + phụ đề EN nghiêng + chip FR; mục active nền `accent-100` + viền trái vàng. Chân sidebar: thẻ user (avatar/chữ cái đầu, tên, vai trò) + Đăng xuất, hoặc nút Đăng ký/Đăng nhập cho khách; link Sitemap.
- **Header** sticky: ô tìm kiếm (GET `/explore?q=`), 4 chip thể loại từ DB (`.cat-chip` màu riêng, đánh dấu chip đang lọc), nút "Đăng video · Upload" (chỉ Creator), chip user `.ts-user`.
- **Tiêu đề màn hình** (pattern mọi trang): section `screen-kicker` ("FRx · Tên use case") / `screen-title` (600, 40px) / `screen-sub` (nghiêng, EN).
- Mobile ≤1080px: sidebar thành drawer (nút ☰ → `tsToggleNav()`, màn che, Esc/bấm ngoài để đóng), lưới 4→2→1 cột.

## 5. Map mockup → trang thật (dùng khi code FR)

| Mockup | Trang thật | Trạng thái | Ghi chú chuyển đổi |
|---|---|---|---|
| Discover | `/` (home) + `/explore` | ✅ xong | Hero plate + card nổi bật đè góc, trending 6 hàng rank vàng, lưới 4 cột thẻ màu thể loại; explore: ô lọc + sort + dãy `.cat-chip` |
| Auth | `/login`, `/register` | ✅ xong | Tab heading 22px; mockup chọn "thể loại tài năng" → đổi thành chọn vai trò Creator/Mentor bằng `.seg` (DB yêu cầu role) |
| Watch | `/videos/{id}` | ✅ xong | 1.9fr/1fr; player trái, sao chấm điểm `.stars-hover` (xem trước khi rê), chip thể loại, bình luận + form trả lời `.reveal`; cột phải: nhận xét mentor + "Xem tiếp" |
| Profile | `/users/{id}`, `/profile/edit` | ✅ xong | Avatar `.plate` 168px, tên 34px, chip vai trò, 4 `.stat`; hành động cột phải; lưới video 4 cột + `.tag-status` cho video chưa duyệt |
| Upload | `/videos/create` + `/my-videos` | ✅ xong | Form trái (khung kéo-thả `.plate` dashed) + bảng "Trạng thái duyệt" phải với `.tag-status`; **giới hạn 100MB** (mockup ghi 500MB — theo php.ini thực tế); privacy chỉ 2 mức public/private (DB enum) |
| Moderation | `/admin/videos` | ✅ xong | Tab gạch chân trượt (Chờ duyệt/Đã duyệt/Từ chối), hàng đợi card ngang thumbnail 128×76 + nút Duyệt/Từ chối; panel "Từ chối kèm lý do" là `.reveal` mở ngay dưới hàng (không modal). Phần "Reported content/Suspend" để dạng khóa user (`is_active`) |
| Contest | `/contests`, `/contests/{id}` | ✅ xong | `.phase-strip` 4 pha từ 3 mốc thời gian (pha hiện tại nền accent-100), leaderboard rank vàng, lưới bài dự thi (viền trên màu thể loại) + nút Bình chọn (1 phiếu/user) |
| Groups | `/groups`, `/groups/{id}` | ✅ xong | 300px danh sách nhóm trái + bảng thảo luận phải; form đăng bài inline; người ngoài chỉ thấy mô tả + nút Join |
| Mentorship | `/messages`, `/messages/{user}` | ✅ xong | 3 cột mockup → thu còn 2: threads trái (viền trái vàng khi active) + khung chat `.bubble` (me = accent-100, them = surface); phần sessions/feedback-mốc-thời-gian ngoài phạm vi DB |
| Feed | `/feed` | ✅ xong | Card hoạt động: avatar + "ai · làm gì · khi nào", thumbnail 200×118 + tiêu đề (tên thể loại tô màu); cột phải "Gợi ý theo dõi" |

## 6. Quy tắc khi dựng màn hình mới

1. Mọi trang `@extends('layouts.app')` và khai báo 3 section tiêu đề màn hình.
2. Nhãn hiển thị song ngữ "VN · EN" ở nút/heading chính; nội dung chạy thuần tiếng Việt.
3. Số liệu luôn `.num`/`.meta` (tabular); hạng mục xếp hạng dùng `.rank-num`.
4. Thumbnail/ảnh chưa có → `.hatch*` + `.slot-note "[ thumbnail ]"`, tuyệt đối không để ô trống trắng.
5. Trạng thái (duyệt, contest, tin chưa đọc) thể hiện bằng `.tag` viền màu chữ tương ứng (`.tag-status[data-status]` cho duyệt), không dùng nền đặc.
6. Thể loại luôn đi kèm màu: thẻ video đặt `style="--cat: {{ $video->category->colorVar() }}"`, chip dùng `.cat-chip`.
7. Ẩn/hiện có chuyển động: dùng `.reveal` + `tsToggle()` thay vì `display: none` inline; hover/focus mới phải kèm `transition` dùng token `--dur-*`/`--ease-*`.
8. Không thêm thư viện CSS/JS ngoài — toàn bộ là `talentstage.css` + `talentstage.js` vanilla.
