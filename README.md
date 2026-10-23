# TechZone

Website bán thiết bị điện tử và công nghệ cao, xây dựng bằng PHP thuần, MySQL,
PHPMailer và Dotenv. Ứng dụng có hai nhóm người dùng:

- **Quản lý:** quản lý danh mục, sản phẩm, tồn kho và trạng thái đơn hàng.
- **Khách hàng:** xem sản phẩm, đăng ký/đăng nhập, mua hàng, theo dõi và huỷ
  đơn trước khi đơn được xác nhận.

## Chức năng chính

### Khách hàng

- Xem danh sách và chi tiết sản phẩm.
- Thêm, cập nhật số lượng và xoá sản phẩm trong giỏ hàng.
- Đăng ký bằng email hoặc số điện thoại.
- Đăng nhập và đăng xuất.
- Quên mật khẩu bằng email, liên kết đặt lại có thời hạn 60 phút.
- Checkout với thông tin người nhận, phương thức thanh toán và voucher.
- Xem lịch sử đơn hàng và trạng thái được đồng bộ từ quản lý.
- Huỷ đơn khi trạng thái còn **Chờ xác nhận**, nhập lý do huỷ.
- Nhận email xác nhận đơn hàng hoặc thông báo liên quan.

### Quản lý

- Đăng nhập bằng thông tin trong `.env`.
- Thêm, sửa, xoá danh mục.
- Thêm, sửa, xoá sản phẩm và tải ảnh sản phẩm.
- Tự động đề xuất mã sản phẩm tiếp theo.
- Kiểm tra dữ liệu trùng bằng AJAX và kiểm tra lại ở server.
- Xem đơn hàng và chuyển trạng thái một chiều:

  `Chờ xác nhận -> Đã xác nhận -> Đang giao -> Đã giao -> Hoàn trả hàng`

- Huỷ đơn trước khi chuyển sang đang giao, nhập lý do và hoàn tồn kho.
- Nhận thông báo email khi khách hàng yêu cầu huỷ đơn.

## Yêu cầu môi trường

- Windows với XAMPP.
- PHP 8.2 trở lên.
- Apache và MySQL.
- PDO MySQL.
- Extension PHP `zip`.
- Composer hoặc file `composer.phar` đi kèm dự án.

## Cài đặt

1. Đặt dự án vào thư mục htdocs, ví dụ:

   `D:\XAMPP\htdocs\webBuoi1`

2. Tạo database MySQL tên `production`.

3. Tạo các bảng nghiệp vụ sản phẩm, danh mục và đơn hàng theo schema của dự án.
   Sau đó chạy [database/customer_auth.sql](database/customer_auth.sql) một lần
   để tạo tài khoản khách hàng và liên kết đơn hàng với khách hàng.

4. Cài dependency:

   ```powershell
   D:\xampp\php\php.exe composer.phar install
   ```

5. Sao chép `.env.example` thành `.env`, sau đó cập nhật thông tin SMTP và tài
   khoản quản lý. Không commit `.env` vì file này chứa thông tin nhạy cảm.

6. Bật Apache và MySQL trong XAMPP rồi truy cập:

   `http://localhost/webBuoi1/`

## Cấu hình `.env`

```dotenv
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls
MAIL_USERNAME=your-gmail-address@gmail.com
MAIL_PASSWORD="your-google-app-password"
MAIL_FROM=your-gmail-address@gmail.com
MAIL_FROM_NAME=TechZone
ADMIN_USERNAME=admin
ADMIN_PASSWORD=change-this-password
```

Gmail cần sử dụng **App Password**, không sử dụng mật khẩu Gmail thông thường.
Xem thêm [README_MAIL_SETUP.txt](README_MAIL_SETUP.txt).

## Cấu trúc thư mục

```text
controllers/   Điều hướng request và xử lý nghiệp vụ
models/        Truy vấn database và các model
services/      Xác thực, email và session khách hàng
view/          Giao diện HTML/PHP
database/      Script khởi tạo và migration database
vendor/        Dependency Composer
index.php      Entry point của ứng dụng
```

## Một số route

| Route | Mục đích |
|---|---|
| `index.php?act=home` | Trang chủ |
| `index.php?act=login` | Đăng nhập |
| `index.php?act=register` | Đăng ký khách hàng |
| `index.php?act=cart` | Giỏ hàng |
| `index.php?act=checkout` | Thanh toán |
| `index.php?act=customer_orders` | Lịch sử đơn hàng khách hàng |
| `index.php?act=admin` | Khu vực quản lý |
| `index.php?act=orders` | Quản lý đơn hàng |

## Kiểm tra nhanh

Kiểm tra cú pháp các file PHP:

```powershell
D:\xampp\php\php.exe -l index.php
D:\xampp\php\php.exe -l controllers\index.php
D:\xampp\php\php.exe -l models\donhang.php
```

Các luồng cần kiểm tra trên trình duyệt:

1. Đăng ký và đăng nhập bằng email.
2. Đăng nhập bằng số điện thoại.
3. Gửi yêu cầu quên mật khẩu và đặt lại mật khẩu.
4. Đặt hàng, kiểm tra trừ tồn kho và email xác nhận.
5. Kiểm tra lịch sử đơn hàng của khách.
6. Huỷ đơn khi còn chờ xác nhận và kiểm tra hoàn tồn kho.
7. Đổi trạng thái đơn ở quản lý và kiểm tra trạng thái phía khách.

## Lưu ý bảo mật

- Đổi `ADMIN_PASSWORD` trước khi triển khai.
- Không đưa `.env`, mật khẩu SMTP hoặc App Password vào Git.
- Các thao tác thay đổi dữ liệu nên được bổ sung CSRF token trước khi triển khai
  production.
- File migration `database/customer_auth.sql` chỉ chạy một lần trên cùng database.
