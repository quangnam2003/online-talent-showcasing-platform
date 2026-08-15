# TalentStage — Hệ thống thiết kế UI "Classical"

Nguồn: artifact mockup `claude.ai/code/artifact/b4e21537-e823-414b-a2fa-bb9b026085fe` (10 màn hình).
Toàn bộ tokens/components đã port sang **`public/css/talentstage.css`**; fonts tự host trong **`public/fonts/`** (24 face, có subset tiếng Việt).

## 1. Ngôn ngữ thị giác

| Yếu tố | Giá trị |
|---|---|
| Nền / bề mặt | `#f3f2f2` (giấy) / `#eae9e9` |
| Mực chữ | `#201f1d` |
| Accent | `#b68235` (vàng đồng) — ramp 100–900 |
| Heading | **Cormorant Garamond** — display weight 400, UI weight 600 |
| Body | **Lora** 400 |
| Spacing | 4.6 / 9.2 / 13.8 / 18.4 / 27.6 / 36.8px (`--space-1…8`) |
| Nét đặc trưng | nhãn song ngữ "VN · EN", kicker uppercase tracked, số tabular, ghi chú nghiêng mờ, kẻ hairline, khung ảnh `.plate` (viền surface 6px + sepia), nền chéo `.hatch` cho placeholder |

## 2. Component chính (class trong talentstage.css)

- Nút: `.btn` + `-primary` (viền vàng) / `-secondary` (viền mảnh) / `-ghost`, size `-sm` `-xs`
- Form: `.field` + `.label-up` (nhãn uppercase 11px), `.input`, `.is-invalid` + `.err-msg`, `.seg` (segmented radio), `.radio`
- Thẻ: `.card` (`-kicker/-title/-body/-meta`), `.tag` (`-accent/-outline/-neutral/-muted`)
- Bảng: `.table`; hộp thoại `.dialog*`; flash: `.flash` / `.flash-error`
- Đặc thù app: `.video-card` (+`.video-thumb` 16:10), `.rank-row`+`.rank-num` (số vàng Cormorant), `.phase-strip`+`.phase.current` (máy trạng thái contest), `.bubble.me/.them` (chat), `.stat`, `.line-tabs`, `.auth-tabs`, `.avatar` (`-lg/-xl`), `.kicker`, `.meta`, `.muted-i`, `.slot-note`, `.grid-4/.grid-2`

## 3. Layout shell (`layouts/app.blade.php`)

- **Sidebar 268px** trái, sticky: brand Cormorant + tagline uppercase; nav **4–5 nhóm đánh số** (01 Truy cập & nội dung → 05 Quản trị), mỗi mục = nhãn VN + phụ đề EN nghiêng + chip FR; mục active nền `accent-100` + viền trái vàng. Chân sidebar: thẻ user (avatar/chữ cái đầu, tên, vai trò) + Đăng xuất, hoặc nút Đăng ký/Đăng nhập cho khách; link Sitemap.
- **Header** sticky: ô tìm kiếm (GET `/explore?q=`), 4 chip thể loại từ DB, nút "Đăng video · Upload" (chỉ Creator), chip user.
- **Tiêu đề màn hình** (pattern mọi trang): section `screen-kicker` ("FRx · Tên use case") / `screen-title` (Cormorant 400, 40px) / `screen-sub` (nghiêng, EN).
- Mobile ≤1080px: sidebar thành drawer (nút ☰), lưới 4→2→1 cột.

## 4. Map mockup → trang thật (dùng khi code FR)

| Mockup | Trang thật | Trạng thái | Ghi chú chuyển đổi |
|---|---|---|---|
| Discover | `/` (home) + `/explore` | Đã xong (home) | Hero plate + card nổi bật đè góc, trending 6 hàng rank vàng, lưới 4 cột; explore thêm ô lọc + sort |
| Auth | `/login`, `/register` | Đã xong | Tab Cormorant; mockup chọn "thể loại tài năng" → đổi thành chọn vai trò Creator/Mentor bằng `.seg` (DB yêu cầu role) |
| Watch | `/videos/{id}` | FR2/FR4 | 1.9fr/1fr; player trái, sao chấm điểm (glyph ★☆ vàng), bình luận dưới; cột phải: nhận xét mentor + "Xem tiếp" |
| Profile | `/users/{id}`, `/profile/edit` | FR1 | Avatar `.plate` 168px, tên Cormorant 34px, chip thể loại, 4 `.stat`; hành động cột phải; lưới video 4 cột |
| Upload | `/videos/create` + `/my-videos` | FR2 | Form trái (khung kéo-thả `.plate` dashed) + bảng "Trạng thái duyệt" phải; **giới hạn 100MB** (mockup ghi 500MB — theo php.ini thực tế); privacy chỉ 2 mức public/private (DB enum) |
| Moderation | `/admin/videos` | FR8 | Tab gạch chân (Chờ duyệt/Đã xử lý), hàng đợi card ngang thumbnail 128×76 + nút Duyệt/Từ chối; panel "Từ chối kèm lý do" cố định bên phải (không dùng modal). Phần "Reported content/Suspend" để dạng khóa user (`is_active`) |
| Contest | `/contests`, `/contests/{id}` | FR7 | `.phase-strip` 4 pha từ 3 mốc thời gian (pha hiện tại nền accent-100), leaderboard rank vàng, lưới bài dự thi + nút Bình chọn (1 phiếu/user) |
| Groups | `/groups`, `/groups/{id}` | FR5 | 300px danh sách nhóm trái + bảng thảo luận phải; form đăng bài inline; người ngoài chỉ thấy mô tả + nút Join |
| Mentorship | `/messages`, `/messages/{user}` | FR6 | 3 cột mockup → thu còn 2: threads trái (viền trái vàng khi active) + khung chat `.bubble` (me = accent-100, them = surface); phần sessions/feedback-mốc-thời-gian ngoài phạm vi DB |
| Feed | `/feed` | FR4 (tùy chọn) | Card hoạt động: avatar + "ai · làm gì · khi nào", thumbnail 200×118 + tiêu đề; cột phải "Gợi ý theo dõi" + nhắc cuộc thi |

## 5. Quy tắc khi dựng màn hình mới

1. Mọi trang `@extends('layouts.app')` và khai báo 3 section tiêu đề màn hình.
2. Nhãn hiển thị song ngữ "VN · EN" ở nút/heading chính; nội dung chạy thuần tiếng Việt.
3. Số liệu luôn `.num`/`.meta` (tabular); hạng mục xếp hạng dùng `.rank-num`.
4. Thumbnail/ảnh chưa có → `.hatch*` + `.slot-note "[ thumbnail ]"`, tuyệt đối không để ô trống trắng.
5. Trạng thái (duyệt, contest, tin chưa đọc) thể hiện bằng `.tag` viền màu chữ tương ứng, không dùng nền đặc.
6. Không thêm thư viện CSS/JS ngoài — toàn bộ là `talentstage.css` + vanilla JS tối thiểu.
