# 🎙️ English Smart Class THCS (Laravel 10 + PHP 8.1 + MySQL)

Website quản lý học tập và luyện đọc – tự động chấm điểm phát âm Tiếng Anh cho **Học sinh Cấp 2 (THCS - Khối 6, 7, 8, 9 - SGK Global Success)**, xây dựng trên **Laravel 10**, tích hợp **Google OAuth (Laravel Socialite)** và lưu trữ file ghi âm giọng đọc học sinh trên server.

## ✨ Tính năng chính

- **Đăng nhập tách biệt Giáo viên & Học sinh:**
  - **Giáo viên:** Đăng nhập bằng tài khoản Google (Laravel Socialite OAuth 2.0, hỗ trợ chế độ Local Demo khi chưa cấu hình key).
  - **Học sinh:** Đăng nhập bằng mã học sinh (`username`) & mật khẩu do giáo viên tạo và cấp.
- **Quản lý Lớp học & Tài khoản Học sinh (Khối 6, 7, 8, 9):** Tạo lớp theo khối, cấp tài khoản, copy nhanh thông tin đăng nhập, đăng nhập thử dưới góc nhìn học sinh.
- **Kho Từ vựng & Kho Đoạn văn THCS:** Soạn hàng loạt từ vựng (từ, IPA, từ loại, nghĩa, ví dụ), đoạn văn kèm bản dịch và từ trọng tâm, lọc theo khối 6–9.
- **Phân quyền Giao bài tập:** Giao bài Từ vựng hoặc Đoạn văn cho cả lớp hoặc chọn riêng từng học sinh, kèm hạn nộp, điểm đạt yêu cầu và lời dặn.
- **Phòng Luyện đọc AI & Ghi âm Tự động chấm điểm:**
  - Phát âm mẫu chuẩn Anh-Mỹ (`Text-to-Speech`) tốc độ thường / chậm, bấm vào từng từ để nghe phát âm.
  - Thu âm giọng đọc thật (`MediaRecorder`) lưu dưới dạng file âm thanh trên server (`storage/app/public/recordings/`) kèm chấm điểm tự động theo **Thang điểm 10 & %** (`Web Speech API`).
  - Tự động tô màu từng từ đúng/gần đúng/sai và gợi ý sửa lỗi phát âm đặc trưng THCS (âm đuôi `-s/-es`, `-ed`).
  - **Bảng Vàng Thi đua Lớp** tạo động lực luyện tập cho học sinh.

## ⚙️ Cài đặt khi Clone từ GitHub

```bash
# 1. Cài đặt các gói PHP
composer install

# 2. Tạo file cấu hình môi trường & sinh APP_KEY
cp .env.example .env
php artisan key:generate

# 3. Cấu hình database trong .env (mặc định DB_DATABASE=tienganh_thcs)
# Tạo database tienganh_thcs trong MySQL, sau đó chạy:
php artisan migrate --seed

# 4. Liên kết thư mục storage để phát lại file ghi âm
php artisan storage:link

# 5. Chạy server phát triển
php artisan serve
```

Mở trình duyệt Chrome hoặc Edge tại `http://127.0.0.1:8000`.

Xem hướng dẫn chi tiết về Google OAuth và triển khai hosting trong file [`HUONG_DAN_CAI_DAT_LARAVEL.md`](HUONG_DAN_CAI_DAT_LARAVEL.md).
