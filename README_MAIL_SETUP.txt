CAI DAT PHPMailer VA GMAIL SMTP

1. Cai dependency tai thu muc du an:
   D:\xampp\php\php.exe composer.phar install

2. Tao Google App Password (khong dung mat khau Gmail thong thuong).

3. Sao chep .env.example thanh .env va dien thong tin SMTP:
   MAIL_HOST=smtp.gmail.com
   MAIL_PORT=587
   MAIL_ENCRYPTION=tls
   MAIL_USERNAME=dia-chi-gmail-cua-ban
   MAIL_PASSWORD=google-app-password
   MAIL_FROM=dia-chi-gmail-cua-ban
   MAIL_FROM_NAME=TechZone

Ung dung tu dong nap file .env truoc khi gui mail.

Tai khoan quan ly:
   ADMIN_USERNAME=admin
   ADMIN_PASSWORD=change-this-password

Doi ADMIN_PASSWORD truoc khi su dung that. Khach mua hang khong can dang nhap.


Neu SMTP loi sau khi don hang da luu, don van duoc giu trong bang donhang va
trang thanh cong se hien thong bao email chua gui duoc.
