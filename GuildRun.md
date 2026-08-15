# 🚀 GuildRun — Hướng dẫn chạy project TalentStage từng bước

> Làm theo thứ tự từ trên xuống. Bước 1 chỉ cần làm **một lần duy nhất** trên mỗi máy.
> Ký hiệu: 💻 = lệnh gõ vào terminal (đứng tại thư mục gốc project), ✅ = kết quả mong đợi.

---

## Bước 0 — Kiểm tra môi trường

Máy cần có: **PHP ≥ 8.2**, **Composer 2**, **MySQL 8**.

💻 Kiểm tra:
```bash
php -v          # PHP 8.3.x
composer -V     # Composer 2.x
mysql --version # mysql Ver 8.0.x
```

✅ Cả 3 lệnh in ra số phiên bản là đạt. Thiếu cái nào thì cài trước:
```bash
sudo apt install -y php8.3 php8.3-cli composer mysql-server
```

---

## Bước 1 — Cài PHP extensions + tạo database *(chỉ làm 1 lần, cần sudo)*

💻 Chạy lần lượt 3 lệnh:

```bash
# 1a. Extensions PHP còn thiếu: pdo_mysql (kết nối MySQL), xml (Laravel cần), gd (xử lý ảnh)
sudo apt install -y php8.3-mysql php8.3-xml php8.3-gd

# 1b. Tạo database `talentstage` + user `talentstage` / mật khẩu `Talent@123` (khớp sẵn với .env)
sudo mysql < scripts/setup-mysql.sql

# 1c. Nâng giới hạn upload lên 100MB (yêu cầu FR2 - upload video)
sudo sed -i 's/^upload_max_filesize.*/upload_max_filesize = 100M/; s/^post_max_size.*/post_max_size = 100M/' /etc/php/8.3/cli/php.ini
```

✅ Kiểm tra lại:
```bash
php -m | grep -E 'pdo_mysql|xml|gd'        # phải in ra đủ 3 dòng
mysql -utalentstage -pTalent@123 -e "SHOW DATABASES;"   # thấy dòng `talentstage`
php -i | grep upload_max_filesize           # upload_max_filesize => 100M
```

---

## Bước 2 — Cài thư viện PHP (vendor/)

💻
```bash
composer install
```

✅ Xuất hiện thư mục `vendor/`, chạy `php artisan --version` in ra `Laravel Framework 13.x`.

> ⚠️ Nếu composer báo thiếu `ext-xml` / `ext-dom` nghĩa là Bước 1a chưa chạy — quay lại Bước 1.

---

## Bước 3 — Cấu hình `.env`

Repo này **đã có sẵn** file `.env` trỏ đúng database ở Bước 1b, không cần sửa gì:

| Khóa | Giá trị |
|---|---|
| `DB_CONNECTION` | `mysql` |
| `DB_DATABASE` | `talentstage` |
| `DB_USERNAME` | `talentstage` |
| `DB_PASSWORD` | `Talent@123` |

💻 Chỉ cần sinh khóa mã hóa (bắt buộc, 1 lần):
```bash
php artisan key:generate
```

✅ In ra `Application key set successfully.` — mở `.env` thấy `APP_KEY=base64:...` có giá trị.

> 📝 *Nếu là máy khác clone repo về (không có `.env`):* chạy `cp .env.example .env` trước rồi mới `php artisan key:generate`.

---

## Bước 4 — Tạo bảng + đổ dữ liệu mẫu

💻
```bash
php artisan migrate --seed
```

✅ Chạy xong không lỗi, in 17 migrations `DONE` (14 của dự án + 3 của Laravel). Lệnh này tạo:
- **Các bảng nghiệp vụ**: users, categories, videos, reactions, comments, follows, groups, group_members, group_posts, messages, contests, contest_entries, votes, notifications (+ bảng hệ thống: sessions, cache, jobs…)
- **7 tài khoản demo** (xem Bước 7) + danh mục, video, cuộc thi, tương tác mẫu

> 🔄 Muốn xóa sạch làm lại từ đầu: `php artisan migrate:fresh --seed`

---

## Bước 5 — Nối thư mục lưu file upload

💻
```bash
php artisan storage:link
```

✅ In ra `The [public/storage] link has been connected` — avatar/video/thumbnail upload sau này sẽ truy cập được qua trình duyệt.

---

## Bước 6 — Chạy server

💻
```bash
php artisan serve
```

✅ In ra `Server running on [http://127.0.0.1:8000]`. Mở trình duyệt vào:

### 👉 http://localhost:8000

Để **dừng server**: nhấn `Ctrl + C`. Nếu cổng 8000 bận: `php artisan serve --port=8001`.

---

## Bước 7 — Đăng nhập thử bằng tài khoản demo

| Vai trò | Email | Mật khẩu | Tên hiển thị |
|---|---|---|---|
| 👑 **Admin** | `admin@talentstage.test` | `Admin@123` | Quản trị viên |
| 🎤 Creator | `creator1@talentstage.test` | `Creator@123` | Nam Nguyễn |
| 🎤 Creator | `creator2@talentstage.test` | `Creator@123` | Linh Trần |
| 🎤 Creator | `creator3@talentstage.test` | `Creator@123` | Huy Phạm |
| 🎤 Creator | `creator4@talentstage.test` | `Creator@123` | Mai Lê |
| 🎓 Mentor | `mentor1@talentstage.test` | `Mentor@123` | Sơn Đặng |
| 🎓 Mentor | `mentor2@talentstage.test` | `Mentor@123` | Hạnh Vũ |

---

## Bước 8 — Checklist xác nhận mọi thứ chạy đúng

Mở lần lượt và đối chiếu:

- [ ] `http://localhost:8000` — trang chủ: nền giấy ấm, chữ Public Sans, **sidebar trái** có các nhóm nav đánh số 01→04, hero + "Đang thịnh hành" + lưới video (viền trên màu theo thể loại, nội dung fade-in so le khi vào trang)
- [ ] `http://localhost:8000/sitemap` — sơ đồ 10 nhóm màn hình (yêu cầu đề bài, có link ở chân sidebar + footer)
- [ ] `http://localhost:8000/login` — đăng nhập `admin@talentstage.test / Admin@123` → sidebar hiện thêm nhóm **05 · Quản trị**
- [ ] Đăng xuất → đăng nhập `creator1@talentstage.test` → header hiện nút **"Đăng video · Upload"**
- [ ] `http://localhost:8000/register` — tạo tài khoản mới, chọn vai trò Creator/Mentor → đăng ký xong tự đăng nhập

> ✅ **Toàn bộ chức năng đã mở** (59 routes): Explore, Profile, Upload/Duyệt, Nhóm, Tin nhắn, Cuộc thi + Vote, Bảng tin, Thông báo, khu Admin. Thử thêm:
> - [ ] Creator: đăng video → thấy "Chờ duyệt" → đăng nhập Admin duyệt → creator nhận **thông báo**
> - [ ] Like / chấm sao / bình luận / trả lời trong trang video — số liệu cập nhật ngay
> - [ ] Nhắn tin creator ↔ mentor (badge số tin chưa đọc trên sidebar)
> - [ ] Nhóm: join → đăng bài (bảng thảo luận chỉ thành viên thấy)
> - [ ] Cuộc thi: nộp video đã duyệt khi "Đang nhận bài", vote khi "Đang bình chọn" (1 phiếu/người)
>
> Mọi thao tác đều **ghi thẳng vào MySQL `talentstage`** — muốn xóa hết dữ liệu thử nghiệm về bản mẫu: `php artisan migrate:fresh --seed`.

---

## Các lệnh hay dùng khi phát triển

| Việc | Lệnh |
|---|---|
| Chạy server | `php artisan serve` |
| Reset toàn bộ dữ liệu về mẫu ban đầu | `php artisan migrate:fresh --seed` |
| Xem danh sách route đang mở | `php artisan route:list` |
| Xóa cache view/config khi sửa không thấy đổi | `php artisan optimize:clear` |
| Thử nhanh code với DB (REPL) | `php artisan tinker` |
| Nạp lại autoload khi thêm class mới báo not found | `composer dump-autoload` |

---

## 🩹 Lỗi thường gặp & cách xử lý

| Lỗi | Nguyên nhân | Cách sửa |
|---|---|---|
| `could not find driver` khi migrate | Thiếu `php8.3-mysql` | Chạy lại Bước 1a rồi mở terminal mới |
| `Access denied for user 'talentstage'@'localhost'` | Chưa tạo DB/user | Chạy lại Bước 1b: `sudo mysql < scripts/setup-mysql.sql` |
| Composer báo `ext-xml ... is missing` | Thiếu `php8.3-xml` | Bước 1a → chạy lại `composer install` |
| `No application encryption key` / trang lỗi 500 ngay | Chưa có APP_KEY | `php artisan key:generate` |
| `Vite manifest not found` | Không dùng Vite trong project này | Không bao giờ gặp nếu giữ nguyên layout; nếu gặp thì kiểm tra view có gọi `@vite` không (phải bỏ) |
| Ảnh avatar/thumbnail không hiện | Chưa link storage | `php artisan storage:link` |
| `Address already in use` khi serve | Cổng 8000 đang bận | `php artisan serve --port=8001` |
| Upload video 100MB bị chặn | php.ini chưa nâng giới hạn | Bước 1c, sau đó **tắt và bật lại** `php artisan serve` |
| Sửa view/route không thấy thay đổi | Cache cũ | `php artisan optimize:clear` rồi F5 |
| Lỗi 500 không rõ nguyên nhân | — | Xem 50 dòng cuối log: `tail -50 storage/logs/laravel.log` |

---

*Tài liệu liên quan: [docs/SITEMAP.md](docs/SITEMAP.md) (danh sách 23+ màn hình) · [docs/UI-DESIGN.md](docs/UI-DESIGN.md) (hệ thống thiết kế Classical + map mockup → trang thật).*
