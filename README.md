# TalentStage

Nền tảng web trưng bày tài năng trực tuyến — nơi creator đăng video, mentor kết nối và cộng đồng tương tác.

## Tính năng chính

- **Khám phá & bảng tin** — xem video thịnh hành, theo danh mục, và feed theo người theo dõi
- **Đăng & duyệt tiết mục** — creator đăng video (MP4/MOV/WEBM) hoặc bản thu âm (MP3/M4A/WAV/OGG/FLAC) tối đa 100MB, có thanh tiến trình và báo kết quả gửi; admin được thông báo và duyệt trước khi công khai
- **Tương tác** — like, chấm sao, bình luận / trả lời, theo dõi creator
- **Nhóm cộng đồng** — tham gia nhóm, đăng bài thảo luận (chỉ thành viên thấy)
- **Tin nhắn** — chat creator ↔ mentor, badge tin chưa đọc
- **Cuộc thi** — nộp bài, bình chọn (1 phiếu/người), theo dõi trạng thái cuộc thi
- **Thông báo** — tiết mục mới chờ duyệt (admin), kết quả duyệt (creator), người theo dõi mới
- **Quản trị** — dashboard admin: người dùng, danh mục, video chờ duyệt, cuộc thi

Giao diện web hoàn chỉnh (nền giấy ấm, typography Public Sans, sidebar icon + menu tài khoản, mỗi thể loại một màu, chuyển động mượt) — xem [docs/UI-DESIGN.md](docs/UI-DESIGN.md).

## Công nghệ

Laravel 13 · PHP ≥ 8.2 · MySQL 8 · Blade

## Hướng dẫn chạy

Xem chi tiết từng bước tại **[GuildRun.md](GuildRun.md)**.

Mở trình duyệt: [http://localhost:8000](http://localhost:8000)

## Tài liệu liên quan

- [GuildRun.md](GuildRun.md) — hướng dẫn cài đặt & chạy project
- [docs/SITEMAP.md](docs/SITEMAP.md) — sơ đồ màn hình
- [docs/UI-DESIGN.md](docs/UI-DESIGN.md) — hệ thống thiết kế UI (màu, chữ, khung trang, chuyển động)
