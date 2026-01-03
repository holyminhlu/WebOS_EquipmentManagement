# Hướng dẫn cài đặt PHPMailer để gửi email

## Bước 1: Download PHPMailer

1. Truy cập: https://github.com/PHPMailer/PHPMailer/releases
2. Download file `PHPMailer-6.x.x.zip` (phiên bản mới nhất)
3. Giải nén file

## Bước 2: Copy vào project

Tạo thư mục và copy các file:
```
D:\xampp\htdocs\WebOS_EquipmentManagement\vendor\phpmailer\
    ├── src\
    │   ├── Exception.php
    │   ├── PHPMailer.php
    │   ├── SMTP.php
    │   └── ...các file khác
```

## Bước 3: Lấy App Password từ Gmail

1. Đăng nhập Gmail: https://myaccount.google.com/
2. Vào **Security** > **2-Step Verification** (bật nếu chưa có)
3. Cuối trang chọn **App passwords**
4. Chọn:
   - App: **Mail**
   - Device: **Other (Custom name)** → nhập "WebOS Equipment"
5. Click **Generate** → Copy mật khẩu 16 ký tự

## Bước 4: Cấu hình trong code

Mở file: `includes/email_phpmailer.php`

Sửa dòng 23:
```php
define('SMTP_PASSWORD', 'xxxx xxxx xxxx xxxx'); // Dán App Password vào đây
```

## Bước 5: Sử dụng

Trong file `public/contact.php` sửa dòng 15:
```php
// require_once __DIR__ . '/../includes/email.php';
require_once __DIR__ . '/../includes/email_phpmailer.php';
```

## Test thử

1. Mở trang liên hệ: http://localhost/WebOS_EquipmentManagement/public/contact.php
2. Điền form và gửi
3. Kiểm tra email admin và email người gửi

## Troubleshooting

**Lỗi "Could not authenticate":**
- Kiểm tra App Password đã đúng chưa
- Kiểm tra 2-Step Verification đã bật chưa

**Lỗi "Connection timeout":**
- Kiểm tra kết nối internet
- Thử đổi port 587 → 465 và SMTPSecure → ssl

**Email vào Spam:**
- Bình thường, người nhận check spam folder
- Hoặc cấu hình SPF/DKIM cho domain (nâng cao)
