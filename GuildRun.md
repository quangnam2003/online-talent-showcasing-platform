# GuildRun — Hướng dẫn chạy project TalentStage từng bước

> Làm theo thứ tự từ trên xuống. Bước 1 chỉ cần làm **một lần duy nhất** trên mỗi máy.
>
> Quy ước trình bày trong mỗi bước:
> - **Lệnh:** khối lệnh gõ vào terminal (đứng tại thư mục gốc project)
> - **Kết quả mong đợi:** dấu hiệu cho biết bước đó đã chạy đúng
>
> **Có Docker?** Bỏ qua Bước 0→6, xem thẳng phần [Chạy bằng Docker Compose](#chạy-bằng-docker-compose-thay-cho-bước-06) ở cuối tài liệu.

---

## Bước 0 — Kiểm tra môi trường

Máy cần có: **PHP ≥ 8.2**, **Composer 2**, **MySQL 8**.

- **Lệnh:**

```bash
php -v          # PHP 8.3.x
composer -V     # Composer 2.x
mysql --version # mysql Ver 8.0.x
```

- **Kết quả mong đợi:** cả 3 lệnh in ra số phiên bản. Thiếu cái nào thì cài trước:

```bash
sudo apt install -y php8.3 php8.3-cli composer mysql-server
```

---

## Bước 1 — Cài PHP extensions + tạo database *(chỉ làm 1 lần, cần sudo)*

- **Lệnh:** chạy lần lượt 3 lệnh:

```bash
# 1a. Extensions PHP còn thiếu: pdo_mysql (kết nối MySQL), xml (Laravel cần), gd (xử lý ảnh)
sudo apt install -y php8.3-mysql php8.3-xml php8.3-gd

# 1b. Tạo database `talentstage` + user `talentstage` / mật khẩu `Talent@123` (khớp sẵn với .env)
sudo mysql < scripts/setup-mysql.sql

# 1c. Nâng giới hạn upload lên 100MB (yêu cầu FR2 - đăng video / bản thu âm) — với Docker thì KHÔNG cần: image đã kèm docker/php-uploads.ini
sudo sed -i 's/^upload_max_filesize.*/upload_max_filesize = 100M/; s/^post_max_size.*/post_max_size = 100M/' /etc/php/8.3/cli/php.ini
```

- **Kết quả mong đợi:** kiểm tra lại bằng:

```bash
php -m | grep -E 'pdo_mysql|xml|gd'        # phải in ra đủ 3 dòng
mysql -utalentstage -pTalent@123 -e "SHOW DATABASES;"   # thấy dòng `talentstage`
php -i | grep upload_max_filesize           # upload_max_filesize => 100M
```

---

## Bước 2 — Cài thư viện PHP (vendor/)

- **Lệnh:**

```bash
composer install
```

- **Kết quả mong đợi:** xuất hiện thư mục `vendor/`, chạy `php artisan --version` in ra `Laravel Framework 13.x`.

> **Lưu ý:** nếu composer báo thiếu `ext-xml` / `ext-dom` nghĩa là Bước 1a chưa chạy — quay lại Bước 1.

---

## Bước 3 — Cấu hình `.env`

Repo này **đã có sẵn** file `.env` trỏ đúng database ở Bước 1b, không cần sửa gì:

| Khóa | Giá trị |
|---|---|
| `DB_CONNECTION` | `mysql` |
| `DB_DATABASE` | `talentstage` |
| `DB_USERNAME` | `talentstage` |
| `DB_PASSWORD` | `Talent@123` |

- **Lệnh:** chỉ cần sinh khóa mã hóa (bắt buộc, 1 lần):

```bash
php artisan key:generate
```

- **Kết quả mong đợi:** in ra `Application key set successfully.` — mở `.env` thấy `APP_KEY=base64:...` có giá trị.

> **Lưu ý:** *nếu là máy khác clone repo về (không có `.env`):* chạy `cp .env.example .env` trước rồi mới `php artisan key:generate`.

---

## Bước 4 — Tạo bảng + đổ dữ liệu mẫu

- **Lệnh:**

```bash
php artisan migrate --seed
```

- **Kết quả mong đợi:** chạy xong không lỗi, in 17 migrations `DONE` (14 của dự án + 3 của Laravel). Lệnh này tạo:
  - **Các bảng nghiệp vụ**: users, categories, videos, reactions, comments, follows, groups, group_members, group_posts, messages, contests, contest_entries, votes, notifications (+ bảng hệ thống: sessions, cache, jobs…)
  - **7 tài khoản demo** (xem Bước 7) + danh mục, video, cuộc thi, tương tác mẫu

> **Lưu ý:** muốn xóa sạch làm lại từ đầu: `php artisan migrate:fresh --seed`

---

## Bước 5 — Nối thư mục lưu file upload

- **Lệnh:**

```bash
php artisan storage:link
```

- **Kết quả mong đợi:** in ra `The [public/storage] link has been connected` — avatar/video/thumbnail upload sau này sẽ truy cập được qua trình duyệt.

---

## Bước 6 — Chạy server

- **Lệnh:**

```bash
php artisan serve
```

- **Kết quả mong đợi:** in ra `Server running on [http://127.0.0.1:8000]`. Mở trình duyệt vào:

### http://localhost:8000

Để **dừng server**: nhấn `Ctrl + C`. Nếu cổng 8000 bận: `php artisan serve --port=8001`.

---

## Bước 7 — Đăng nhập thử bằng tài khoản demo

| Vai trò | Email | Mật khẩu | Tên hiển thị |
|---|---|---|---|
| **Admin** | `admin@talentstage.test` | `Admin@123` | Quản trị viên |
| Creator | `creator1@talentstage.test` | `Creator@123` | Nam Nguyễn |
| Creator | `creator2@talentstage.test` | `Creator@123` | Linh Trần |
| Creator | `creator3@talentstage.test` | `Creator@123` | Huy Phạm |
| Creator | `creator4@talentstage.test` | `Creator@123` | Mai Lê |
| Mentor | `mentor1@talentstage.test` | `Mentor@123` | Sơn Đặng |
| Mentor | `mentor2@talentstage.test` | `Mentor@123` | Hạnh Vũ |

---

## Bước 8 — Checklist xác nhận mọi thứ chạy đúng

Mở lần lượt và đối chiếu:

- [ ] `http://localhost:8000` — trang chủ: nền giấy ấm, chữ Public Sans, **sidebar trái** có logo + menu icon (Trang chủ, Tìm kiếm, Cuộc thi, Nhóm…), hero + "Đang thịnh hành" + lưới video (viền trên màu theo thể loại, nội dung fade-in so le khi vào trang)
- [ ] `http://localhost:8000/sitemap` — sơ đồ 10 nhóm màn hình (yêu cầu đề bài, có link "Sơ đồ trang" ở footer)
- [ ] `http://localhost:8000/login` — đăng nhập `admin@talentstage.test / Admin@123` → sidebar hiện thêm nhóm **Quản trị** (Tổng quan, Kiểm duyệt có badge số video chờ, Người dùng, Danh mục, Cuộc thi)
- [ ] Đăng xuất → đăng nhập `creator1@talentstage.test` → header hiện nút **"Đăng video"**, chuông thông báo và menu tài khoản (bấm avatar); sidebar có nhóm **Kênh của tôi**
- [ ] `http://localhost:8000/register` — tạo tài khoản mới, chọn vai trò Creator/Mentor → đăng ký xong tự đăng nhập

> **Toàn bộ chức năng đã mở** (59 routes): Explore, Profile, Upload/Duyệt, Nhóm, Tin nhắn, Cuộc thi + Vote, Bảng tin, Thông báo, khu Admin. Thử thêm:
> - [ ] Creator: đăng video hoặc **bản thu âm (mp3/m4a/wav)** → thẻ đính kèm hiện tên · loại · dung lượng · thời lượng → bấm Gửi duyệt thấy thanh tiến trình → "Đã gửi duyệt thành công" → **Admin nhận thông báo** (chuông + badge Kiểm duyệt) → Admin duyệt → creator nhận **thông báo**
> - [ ] Like / chấm sao / bình luận / trả lời trong trang video — số liệu cập nhật ngay
> - [ ] Nhắn tin creator ↔ mentor (badge số tin chưa đọc trên sidebar)
> - [ ] Nhóm: join → đăng bài (bảng thảo luận chỉ thành viên thấy)
> - [ ] Cuộc thi: nộp video đã duyệt khi "Đang nhận bài", vote khi "Đang bình chọn" (1 phiếu/người)
>
> Mọi thao tác đều **ghi thẳng vào MySQL `talentstage`** — muốn xóa hết dữ liệu thử nghiệm về bản mẫu: `php artisan migrate:fresh --seed`.

---

## Chạy bằng Docker Compose *(thay cho Bước 0→6)*

Chỉ cần **Docker ≥ 24** kèm plugin **docker compose** — không cần cài PHP/Composer/MySQL trên máy.

- **Lệnh:** kiểm tra:

```bash
docker -v            # Docker version 24.x trở lên
docker compose version
```

### D1 — Chuẩn bị `APP_KEY`

`docker-compose.yml` đọc biến `APP_KEY` từ file `.env` ở thư mục gốc. Repo này đã có sẵn `.env` chứa `APP_KEY=base64:...` nên **thường không cần làm gì**.

> **Lưu ý:** *nếu máy khác clone về không có `.env`:* tạo file `.env` chỉ cần đúng 1 dòng `APP_KEY=base64:...`. Sinh giá trị key bằng một trong hai cách:
> - Có PHP trên máy: `php artisan key:generate --show`
> - Chỉ có Docker: `docker run --rm quangnam2512/online-talent:latest php artisan key:generate --show`
>
> Thiếu APP_KEY container vẫn chạy nhưng in `WARNING: APP_KEY chưa được set` và web sẽ lỗi 500.

### D2 — Build image + khởi động

- **Lệnh:**

```bash
docker compose up -d --build
```

- **Kết quả mong đợi:** `docker ps` thấy 2 container: `online-talent-app` (cổng `8000→80`) và `online-talent-db` (MySQL 8, trạng thái `healthy`). Container app **tự chạy migrate** khi khởi động (biến `AUTO_MIGRATE=true` trong compose) — tạo đủ bảng, nhưng **chưa có dữ liệu**.

> **Giới hạn upload:** image đã cài `docker/php-uploads.ini` (`upload_max_filesize=100M`, `post_max_size=110M`) nên đăng video / bản thu âm tới 100 MB hoạt động ngay. Nếu bạn build image từ trước khi có file này, chạy lại `docker compose up -d --build` — kiểm tra bằng `docker exec online-talent-app php -i | grep upload_max_filesize`.

### D3 — Seed dữ liệu mẫu *(bắt buộc lần đầu)*

Migrate chạy tự động nhưng seeder thì **không** — phải chạy tay 1 lần sau khi container lên:

- **Lệnh:**

```bash
docker exec online-talent-app php artisan db:seed --force
```

- **Kết quả mong đợi:** in 6 seeder `DONE` (UserSeeder → ContestSeeder). Sau đó mở **http://localhost:8000** và đăng nhập bằng tài khoản demo ở Bước 7 (ví dụ `admin@talentstage.test / Admin@123`).

> **Vì sao phải có `--force`?** Container đặt `APP_ENV=production`, Laravel sẽ hỏi xác nhận trước các lệnh đụng dữ liệu; qua `docker exec` không có terminal tương tác nên câu hỏi bị tự trả lời "không" → in `APPLICATION IN PRODUCTION / Command cancelled`. Cờ `--force` bỏ qua bước hỏi đó. Áp dụng cho **mọi** lệnh `db:seed`, `migrate`, `migrate:fresh` chạy trong container.

> **Reset toàn bộ dữ liệu về bản mẫu** (xóa sạch, migrate + seed lại):
> ```bash
> docker exec online-talent-app php artisan migrate:fresh --seed --force
> ```
> Lưu ý `db:seed` đơn thuần sẽ **lỗi duplicate key** nếu bảng `users` đã có dữ liệu (seeder insert id cố định 1→7) — khi DB không còn trống, luôn dùng `migrate:fresh --seed --force`.

### D4 — Các lệnh Docker hay dùng

| Việc | Lệnh |
|---|---|
| Khởi động (đã build rồi) | `docker compose up -d` |
| Dừng (giữ nguyên dữ liệu) | `docker compose down` |
| Dừng + **xóa sạch DB và file upload** | `docker compose down -v` |
| Xem log app | `docker logs -f online-talent-app` |
| Chạy lệnh artisan bất kỳ | `docker exec online-talent-app php artisan <lệnh> --force` |
| Vào shell trong container | `docker exec -it online-talent-app sh` |
| Query MySQL trực tiếp | `docker exec -it online-talent-db mysql -utalentstage -p'Talent@123' talentstage` |
| Build lại sau khi sửa code | `docker compose up -d --build` |

> **Lưu ý:** dữ liệu MySQL nằm trong volume `db-data`, file upload trong volume `app-storage` — `docker compose down` bình thường **không mất dữ liệu**, chỉ `down -v` mới xóa. Sau `down -v`, lần `up` kế tiếp cần seed lại (D3).

### Lỗi thường gặp với Docker

| Lỗi | Nguyên nhân | Cách sửa |
|---|---|---|
| `APPLICATION IN PRODUCTION / Command cancelled` | Thiếu `--force` khi chạy artisan qua `docker exec` | Thêm `--force` vào cuối lệnh |
| Đăng nhập báo "Email hoặc mật khẩu không đúng" với tài khoản demo | Chưa seed dữ liệu (D3) — DB chỉ có bảng trống | `docker exec online-talent-app php artisan db:seed --force` (DB đã có dữ liệu thì dùng `migrate:fresh --seed --force`) |
| `db:seed` báo `Duplicate entry '1' for key 'users.PRIMARY'` | Bảng users đã có dữ liệu, seeder insert id cố định | `docker exec online-talent-app php artisan migrate:fresh --seed --force` |
| Trang lỗi 500 + log in `No application encryption key` | Thiếu `APP_KEY` trong `.env` | Làm D1 rồi `docker compose up -d` lại |
| Cổng 8000 bận (`port is already allocated`) | Cổng bị process khác chiếm | Sửa `ports` trong `docker-compose.yml` thành `"8001:80"` rồi `up -d` lại |
| Sửa code nhưng web không đổi | Image cũ + config/view đã cache | `docker compose up -d --build` (entrypoint tự cache lại) |
| Gửi tiết mục báo "Tệp không tải lên được" / lỗi 413 dù tệp < 100 MB | Image cũ chưa có `docker/php-uploads.ini` (PHP mặc định chỉ nhận 2 MB) | `docker compose up -d --build` rồi kiểm tra `docker exec online-talent-app php -i \| grep upload_max_filesize` → phải là `100M` |

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

## Lỗi thường gặp & cách xử lý

| Lỗi | Nguyên nhân | Cách sửa |
|---|---|---|
| `could not find driver` khi migrate | Thiếu `php8.3-mysql` | Chạy lại Bước 1a rồi mở terminal mới |
| `Access denied for user 'talentstage'@'localhost'` | Chưa tạo DB/user | Chạy lại Bước 1b: `sudo mysql < scripts/setup-mysql.sql` |
| Composer báo `ext-xml ... is missing` | Thiếu `php8.3-xml` | Bước 1a → chạy lại `composer install` |
| `No application encryption key` / trang lỗi 500 ngay | Chưa có APP_KEY | `php artisan key:generate` |
| `Vite manifest not found` | Không dùng Vite trong project này | Không bao giờ gặp nếu giữ nguyên layout; nếu gặp thì kiểm tra view có gọi `@vite` không (phải bỏ) |
| Ảnh avatar/thumbnail không hiện | Chưa link storage | `php artisan storage:link` |
| `Address already in use` khi serve | Cổng 8000 đang bận | `php artisan serve --port=8001` |
| Đăng tiết mục 100MB bị chặn ("Tệp không tải lên được") | php.ini chưa nâng giới hạn | Bước 1c, sau đó **tắt và bật lại** `php artisan serve` |
| Sửa view/route không thấy thay đổi | Cache cũ | `php artisan optimize:clear` rồi F5 |
| Lỗi 500 không rõ nguyên nhân | — | Xem 50 dòng cuối log: `tail -50 storage/logs/laravel.log` |

---

*Tài liệu liên quan: [docs/SITEMAP.md](docs/SITEMAP.md) (danh sách 23+ màn hình) · [docs/UI-DESIGN.md](docs/UI-DESIGN.md) (hệ thống thiết kế UI + danh sách trang).*
