-- Chạy 1 lần với quyền root:  sudo mysql < scripts/setup-mysql.sql
-- Tạo database + user riêng cho ứng dụng (khớp .env)
CREATE DATABASE IF NOT EXISTS talentstage
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE USER IF NOT EXISTS 'talentstage'@'localhost' IDENTIFIED BY 'Talent@123';
GRANT ALL PRIVILEGES ON talentstage.* TO 'talentstage'@'localhost';
FLUSH PRIVILEGES;
