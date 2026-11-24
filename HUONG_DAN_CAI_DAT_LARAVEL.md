# 🎙️ English Smart Class THCS (Laravel 10 + PHP 8.1 + MySQL)

Hệ thống quản lý học tập và tự động chấm điểm phát âm Tiếng Anh cho **Học sinh Cấp 2 (THCS - Khối 6, 7, 8, 9 - SGK Global Success)** được xây dựng hoàn chỉnh bằng **Laravel 10**, tích hợp **Google OAuth (Laravel Socialite)** và lưu trữ file ghi âm giọng đọc học sinh trên server.

---

## 1. Hướng dẫn Chạy trên Máy tính (Laragon - PHP 8.1)

### Bước 1: Bật MySQL trong Laragon & Tạo Database
1. Mở phần mềm **Laragon**, bấm nút **Start All** để bật MySQL.
2. Mở **Database (HeidiSQL / phpMyAdmin)** và tạo một cơ sở dữ liệu mới tên là:
   ```sql
   CREATE DATABASE tienganh_thcs CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
   *(Thông tin kết nối đã được cấu hình sẵn trong file `.env`: `DB_DATABASE=tienganh_thcs`, `DB_USERNAME=root`, `DB_PASSWORD=`)*

### Bước 2: Khởi tạo bảng dữ liệu & Dữ liệu mẫu THCS (Khối 6, 7, 8, 9)
Mở Terminal tại thư mục `d:\Gemini\tienganh` và chạy lệnh:
```bash
php artisan migrate --seed
```
Lệnh này sẽ tự động tạo đầy đủ 8 bảng cơ sở dữ liệu và nạp sẵn:
- **4 Lớp học THCS**: `Lớp 6A1`, `Lớp 7A2`, `Lớp 8B1`, `Lớp 9A1`
- **5 Tài khoản Học sinh mẫu**: `minhan6a1`, `baongoc6a1`, `hoangnam7a2`, `khanhlinh8b1`, `ducanh9a1` (mật khẩu chung: `123`)
- **Kho Từ vựng & Đoạn văn SGK Global Success** cho cả 4 khối 6, 7, 8, 9
- **Các bài tập đã phân quyền** và **Bài nộp mẫu**

### Bước 3: Khởi động Website
Chạy lệnh:
```bash
php artisan serve
```
Sau đó mở trình duyệt **Google Chrome** hoặc **Microsoft Edge** truy cập địa chỉ: **http://127.0.0.1:8000**

---

## 2. Cách Bật Đăng nhập Google Thật cho Giáo viên (Google OAuth 2.0)

Dự án đã tích hợp sẵn gói **Laravel Socialite (`laravel/socialite`)**:
- Khi chưa điền khóa Google trong `.env`, nút **"Đăng nhập Giáo viên bằng Tài khoản Google"** sẽ tự động vào chế độ Local Demo để bạn sử dụng ngay.
- Để bật **Đăng nhập Google thật** (khi chạy local hoặc upload lên Hosting):
  1. Truy cập [Google Cloud Console](https://console.cloud.google.com/apis/credentials) bằng tài khoản Gmail của bạn.
  2. Tạo **OAuth 2.0 Client ID** (loại *Web application*).
  3. Tại mục **Authorized redirect URIs**, thêm đường dẫn:
     - Chạy local: `http://127.0.0.1:8000/auth/google/callback`
     - Khi lên hosting thật: `https://ten-mien-cua-ban.com/auth/google/callback`
  4. Copy `Client ID` và `Client Secret` dán vào file `.env`:
     ```env
     GOOGLE_CLIENT_ID=your-client-id.apps.googleusercontent.com
     GOOGLE_CLIENT_SECRET=your-client-secret
     GOOGLE_REDIRECT_URI="${APP_URL}/auth/google/callback"
     ```
  5. Chạy `php artisan config:clear` là hoàn tất!

---

## 3. Lưu ý khi Upload lên Hosting (cPanel / DirectAdmin / VPS)
- Đảm bảo hosting chọn **PHP 8.1 trở lên** và cài đặt **HTTPS (SSL)** (trình duyệt yêu cầu kết nối HTTPS để học sinh bấm mở Microphone thu âm giọng đọc trên điện thoại/máy tính).
- Trỏ Document Root của tên miền vào thư mục `public/`.
- Import cơ sở dữ liệu hoặc chạy `php artisan migrate --seed` và `php artisan storage:link` để hiển thị/phát lại file ghi âm giọng đọc (`.webm`) của học sinh.
