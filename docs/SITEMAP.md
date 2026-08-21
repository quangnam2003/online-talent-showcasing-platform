# TalentStage — Danh sách màn hình (Sitemap)

Bảng map **use case → màn hình → route** cho toàn hệ thống. Đây là kim chỉ nam khi code FE/BE,
và là nguồn nội dung cho trang `/sitemap` (yêu cầu bắt buộc: Sitemap phải gắn link trên trang chủ).

**Vai trò (actor):** Guest (chưa đăng nhập) · Creator · Mentor · Admin
(cột `users.role` trong DB: `admin | creator | mentor`)

---

## A. Chung & Xác thực (4 màn hình)

| # | Màn hình | Route | Ai truy cập | Use case / FR | Ghi chú |
|---|----------|-------|-------------|---------------|---------|
| 1 | Trang chủ | `/` | Tất cả | Landing | Video nổi bật/trending, contest đang diễn ra, **link tới /sitemap** |
| 2 | Đăng ký | `/register` | Guest | Register | Chọn vai trò Creator hoặc Mentor (không cho tự đăng ký Admin) |
| 3 | Đăng nhập | `/login` | Guest | Login | Chặn tài khoản `is_active = false` |
| 4 | Sitemap | `/sitemap` | Tất cả | Yêu cầu đề bài | Trang tĩnh liệt kê toàn bộ màn hình theo nhóm này |

## B. FR1 — Hồ sơ cá nhân (2 màn hình)

| # | Màn hình | Route | Ai truy cập | Use case / FR | Ghi chú |
|---|----------|-------|-------------|---------------|---------|
| 5 | Xem profile | `/users/{user}` | Tất cả | FR1 View profile | Avatar, bio, location, achievements, `followers_count`, lưới video public+approved của người đó; nút Follow (FR4) |
| 6 | Sửa profile | `/profile/edit` | Đã đăng nhập (chính chủ) | FR1 Edit profile | Upload avatar, sửa bio/location/achievements |

## C. FR2 + FR8 — Video & Kiểm duyệt (4 màn hình)

| # | Màn hình | Route | Ai truy cập | Use case / FR | Ghi chú |
|---|----------|-------|-------------|---------------|---------|
| 7 | Upload video | `/videos/create` | Creator | FR2 Upload | File ≤ 100MB, chọn category, `privacy` (public/private), `allow_comments`; sau khi nộp → `status = pending` |
| 8 | Xem video | `/videos/{video}` | Public+approved: tất cả · Private: chủ sở hữu (+ Admin) | FR2 Watch, FR4 | Player, tăng `views`, reaction, bình luận (ẩn khung nếu `allow_comments = false`), nút nộp contest |
| 9 | Video của tôi | `/my-videos` | Creator | FR2 Manage | Danh sách kèm trạng thái duyệt pending/approved/rejected, nút sửa/xóa (soft delete) |
| 10 | Sửa video | `/videos/{video}/edit` | Chủ sở hữu | FR2 + FR8 | Đổi title/description/category/privacy/allow_comments (không đổi file) |

## D. FR3 — Khám phá (1 màn hình)

| # | Màn hình | Route | Ai truy cập | Use case / FR | Ghi chú |
|---|----------|-------|-------------|---------------|---------|
| 11 | Explore | `/explore` | Tất cả | FR3 Search/Filter/Trending | Ô tìm kiếm theo tên/mô tả, lọc theo category, tab sắp xếp: Mới nhất / Xem nhiều / **Trending** (`trending_score`); chỉ hiện video public+approved |

## E. FR4 — Tương tác

Reaction, comment, follow **không có màn hình riêng** — chúng là hành động nằm trong màn hình #8 (video) và #5 (profile). DB đã có counter cache (`likes_count`, `comments_count`, `followers_count`).

| # | Màn hình | Route | Ai truy cập | Use case / FR | Ghi chú |
|---|----------|-------|-------------|---------------|---------|
| 12 | Feed theo dõi *(tùy chọn)* | `/feed` | Đã đăng nhập | FR4 Follow | Video mới từ những người mình follow — làm nếu còn thời gian |

## F. FR5 — Nhóm & Thảo luận (3 màn hình)

| # | Màn hình | Route | Ai truy cập | Use case / FR | Ghi chú |
|---|----------|-------|-------------|---------------|---------|
| 13 | Danh sách nhóm | `/groups` | Tất cả xem · join khi đã đăng nhập | FR5 | Tên, mô tả, số thành viên, nút Join/Leave |
| 14 | Tạo nhóm | `/groups/create` | Đã đăng nhập | FR5 | Người tạo thành `owner_id` + tự động là thành viên |
| 15 | Trang nhóm | `/groups/{group}` | Bảng thảo luận: **chỉ thành viên** | FR5 Members-only board | Form đăng bài inline; người ngoài chỉ thấy mô tả + nút Join |

## G. FR6 — Nhắn tin Creator ↔ Mentor (2 màn hình)

| # | Màn hình | Route | Ai truy cập | Use case / FR | Ghi chú |
|---|----------|-------|-------------|---------------|---------|
| 16 | Hộp thư | `/messages` | Creator, Mentor | FR6 | Danh sách hội thoại, badge chưa đọc (index `receiver_id + read_at` đã có sẵn) |
| 17 | Hội thoại | `/messages/{user}` | Creator, Mentor | FR6 | Chat 1-1, đánh dấu `read_at` khi mở |

> **Phạm vi giản lược (FR6):** không có bước "Request mentorship / Accept" — mentor mặc định chấp nhận mọi creator, hai vai trò đối diện nhắn được cho nhau ngay. Danh sách "Bắt đầu hội thoại mới" chỉ hiện người **đang hoạt động** và **chưa có hội thoại**; tài khoản bị khóa không nhận được tin. Khung chat hiển thị 50 tin gần nhất.

> **Phạm vi giản lược (FR5):** bảng thảo luận (discussion board) thay cho "group chat" thời gian thực của spec gốc. Admin không tạo/tham gia nhóm — chỉ có quyền kiểm duyệt (xem bảng thảo luận, sửa/xóa nhóm, xóa thành viên, xóa bài vi phạm). Chủ nhóm quản lý được nhóm của mình: sửa tên/mô tả, xóa nhóm, xem danh sách thành viên, xóa thành viên, xóa bài đăng; chủ nhóm nhận thông báo khi có người tham gia.

## H. FR7 — Cuộc thi (2 màn hình)

| # | Màn hình | Route | Ai truy cập | Use case / FR | Ghi chú |
|---|----------|-------|-------------|---------------|---------|
| 18 | Danh sách cuộc thi | `/contests` | Tất cả | FR7 | Phân nhóm theo trạng thái từ 3 mốc: Sắp diễn ra → Đang nhận bài → Đang bình chọn → Đã kết thúc |
| 19 | Chi tiết cuộc thi + Leaderboard | `/contests/{contest}` | Tất cả xem · nộp bài: Creator trong hạn · vote: đã đăng nhập, trong giai đoạn vote | FR7 Submit/Vote/Leaderboard | Nộp 1 video approved (unique per contest/user), vote 1 lần/contest, bảng xếp hạng theo `votes_count` |


> **Phạm vi & luật cuộc thi (FR7):** mỗi tài khoản một phiếu; ban tổ chức (admin) không bình chọn; hòa phiếu → đồng hạng quán quân; không có phiếu → không có quán quân; bài của tài khoản bị khóa bị ẩn khỏi cuộc thi. Creator rút bài được trước khi kết thúc; admin loại bài vi phạm (chủ bài được thông báo). Actor "Scheduler" = lệnh `contests:announce` chạy mỗi giờ — công bố kết quả, thông báo người thắng và thí sinh, đóng dấu `announced_at`; cuộc thi đã kết thúc không sửa được mốc thời gian. Thể lệ/giải thưởng là mô tả tự do; "ban giám khảo chuyên môn" của spec gốc là hướng phát triển.

## I. Thông báo (1 màn hình)

| # | Màn hình | Route | Ai truy cập | Use case / FR | Ghi chú |
|---|----------|-------|-------------|---------------|---------|
| 20 | Thông báo | `/notifications` | Đã đăng nhập | Notifications | Dropdown chuông trên navbar + trang danh sách; đánh dấu đã đọc |

## J. Khu quản trị — Admin (5 màn hình)

| # | Màn hình | Route | Ai truy cập | Use case / FR | Ghi chú |
|---|----------|-------|-------------|---------------|---------|
| 21 | Dashboard | `/admin` | Admin | Thống kê | Đếm user/video/contest, số video chờ duyệt |
| 22 | Duyệt video | `/admin/videos` | Admin | FR8 Moderate | Hàng đợi pending, xem trước, Approve/Reject; lọc theo trạng thái |
| 23 | Quản lý user | `/admin/users` | Admin | Quản trị | Khóa/mở (`is_active`), xem vai trò |
| 24 | Quản lý category | `/admin/categories` | Admin | FR3 nền tảng | CRUD danh mục (video bắt buộc có category — `restrictOnDelete`) |
| 25 | Quản lý contest | `/admin/contests` | Admin | FR7 | Tạo/sửa cuộc thi với 3 mốc: `start_at` < `submission_deadline` < `end_at` |

---

## Tổng kết

- **23 màn hình chính thức** + 2 tùy chọn (#12 Feed; #20 có thể chỉ làm dropdown nếu thiếu thời gian) — khớp ước lượng 18–22 ban đầu, phần vượt là do tách khu Admin thành các trang CRUD riêng cho rõ ràng.
- Mọi route dùng **quy ước Laravel resource** (`videos.create`, `videos.show`, …) để khớp `web.example.php` của gói models-auth khi lắp vào.
- Navbar theo vai trò (làm ở bước 3 — layout chung):
  - **Guest:** Trang chủ · Explore · Contests · Groups · Login · Register
  - **Creator:** + nút **Upload**, My Videos, Messages, chuông thông báo
  - **Mentor:** + Messages, chuông thông báo (không có Upload)
  - **Admin:** + menu **Quản trị** (Duyệt video, Users, Categories, Contests)
