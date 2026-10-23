# TechZone

TechZone là website bán thiết bị điện tử và công nghệ, xây dựng bằng PHP thuần,
MySQL, PDO, PHPMailer và PHP dotenv. Ứng dụng sử dụng một entry point duy nhất
(`index.php`) để điều hướng giữa khu vực mua sắm của khách hàng và khu vực quản
trị của quản lý.

## Chức năng

### Khách hàng

- Xem danh sách sản phẩm, chi tiết sản phẩm và sản phẩm còn hàng.
- Tìm kiếm sản phẩm theo tên hoặc mô tả; gợi ý được tải tự động theo từng ký
  tự nhập.
- Khách hàng thân thiết được xếp hạng theo tổng chi tiêu hợp lệ: Silver trên
  50.000.000đ (giảm 3%), Golden trên 150.000.000đ (giảm 5%), Platinum trên
  500.000.000đ (giảm 10%) và Diamond trên 1.000.000.000đ (giảm 15%).
  Ưu đãi được tự động áp dụng trên tổng bill khi đặt hàng.
- Khách hàng xem hạng, tổng chi tiêu và quyền lợi trong hồ sơ; quản lý xem
  danh sách khách hàng, số đơn, tổng chi tiêu, hạng và mức ưu đãi.
- Trang chủ ưu tiên đề xuất sản phẩm bán chạy theo số lượng đã bán và sản phẩm
  mới lên kệ theo ngày nhập.
- Thêm sản phẩm vào giỏ hàng, cập nhật số lượng và xoá sản phẩm.
- Đăng ký bằng email hoặc số điện thoại.
- Đăng nhập bằng email hoặc số điện thoại.
- Đăng xuất và quản lý phiên đăng nhập.
- Quên mật khẩu bằng email, liên kết đặt lại có hiệu lực 60 phút.
- Thanh toán với thông tin người nhận, ghi chú và phương thức:
  - Thanh toán khi nhận hàng (`cod`).
  - Chuyển khoản ngân hàng (`bank_transfer`).
- Áp dụng voucher:
  - `TECH10`: giảm 10%.
  - `TECH50K`: giảm 50.000 đồng.
- Xem lịch sử đơn hàng và trạng thái đơn hàng.
- Huỷ đơn khi đơn còn ở trạng thái chờ xác nhận, kèm lý do.
- Nhận email xác nhận đơn hàng và gửi thông báo huỷ đơn cho cửa hàng.
- Email xác nhận đơn hàng có đính kèm các hình ảnh sản phẩm nếu file ảnh tồn tại.

### Quản lý

- Đăng nhập bằng tài khoản được cấu hình trong `.env`.
- Xem khu vực quản trị và danh sách đơn hàng.
- Xem thống kê doanh thu và số đơn theo tuần, tháng và năm hiện tại; đơn đã huỷ
  không được tính.
- Xem biểu đồ doanh thu theo 4 quý và biểu đồ số lượng nhập/xuất sản phẩm; dữ
  liệu tồn kho được ghi nhận lại sau mỗi lần thêm, sửa, đặt hàng, huỷ hoặc trả
  hàng.
- Chuyển trạng thái đơn theo đúng thứ tự:

  `Chờ xác nhận -> Đã xác nhận -> Đang giao -> Đã giao -> Hoàn trả`

- Huỷ đơn và nhập lý do huỷ; hệ thống xử lý hoàn tồn kho theo nghiệp vụ.
- Nhận email khi khách hàng yêu cầu huỷ đơn.
- Thêm, sửa và xoá danh mục.
- Thêm, sửa và xoá sản phẩm.
- Không cho xoá sản phẩm đang thuộc bất kỳ đơn chưa huỷ nào; chỉ cho xoá sản
  phẩm khi tất cả đơn liên quan đã ở trạng thái `Đã huỷ`. Danh mục chứa sản
  phẩm như vậy cũng không được xoá.
- Khi đơn bị huỷ hoặc chuyển sang trạng thái `Hoàn trả hàng`, số lượng sản phẩm
  được cộng lại vào tồn kho trong cùng transaction.
- Quản lý tên, giá, số lượng tồn kho, mô tả, khuyến mãi, danh mục và hình ảnh
  sản phẩm.
- Tự động đề xuất mã sản phẩm tiếp theo.
- Kiểm tra dữ liệu trùng bằng request AJAX và kiểm tra lại ở server.

## Yêu cầu môi trường

- Windows với XAMPP.
- PHP 8.2 trở lên.
- Apache và MySQL.
- PHP extension `pdo_mysql`, `mbstring` và `zip`.
- Composer hoặc `composer.phar` có sẵn trong thư mục dự án.

## Cài đặt

1. Đặt dự án trong thư mục `htdocs`, ví dụ:

   ```text
   D:\XAMPP\htdocs\webBuoi1
   ```

2. Tạo database MySQL tên `production`.

3. Tạo các bảng mà các model sử dụng:

   - `danhmuc`: danh mục sản phẩm.
   - `sanpham`: sản phẩm, giá, tồn kho, mô tả, khuyến mãi, hình ảnh và danh mục.
   - `donhang`: thông tin đơn hàng, trạng thái, thanh toán và khách hàng.
   - `chitietdonhang`: các sản phẩm trong đơn hàng.
   - `khachhang`: tài khoản khách hàng và thông tin đặt lại mật khẩu.

   Repository hiện không chứa file migration/schema SQL. Vì vậy cần dùng schema
   MySQL tương ứng với các model trong thư mục `models/` hoặc import schema
   riêng của dự án trước khi chạy ứng dụng.

4. Cài dependency:

   ```powershell
   D:\XAMPP\php\php.exe composer.phar install
   ```

   Dependency chính:

   - `phpmailer/phpmailer`
   - `vlucas/phpdotenv`

5. Tạo file `.env` từ [.env.example](.env.example):

   ```powershell
   Copy-Item .env.example .env
   ```

6. Cập nhật thông tin SMTP và tài khoản quản lý trong `.env`. Không commit file
   `.env` vì file có thể chứa mật khẩu SMTP và thông tin đăng nhập.

7. Bật Apache và MySQL trong XAMPP, sau đó truy cập:

   ```text
   http://localhost/webBuoi1/
   ```

## Cấu hình `.env`

```dotenv
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls
MAIL_USERNAME=your-gmail-address@gmail.com
MAIL_PASSWORD=your-google-app-password
MAIL_FROM=your-gmail-address@gmail.com
MAIL_FROM_NAME=TechZone
ADMIN_USERNAME=admin
ADMIN_PASSWORD=change-this-password
```

Với Gmail, `MAIL_PASSWORD` phải là **Google App Password**, không phải mật khẩu
Gmail thông thường. Xem thêm [README_MAIL_SETUP.txt](README_MAIL_SETUP.txt).

## Luồng xác thực và mật khẩu

### Khách hàng

Mật khẩu khách hàng không được lưu dạng nguyên văn. Khi đăng ký hoặc đặt lại
mật khẩu, controller sử dụng:

```php
password_hash($password, PASSWORD_DEFAULT)
```

Khi đăng nhập, mật khẩu người dùng nhập được kiểm tra bằng:

```php
password_verify($password, $customer['mat_khau'])
```

Hash được lưu trong cột `khachhang.mat_khau`. Token đặt lại mật khẩu được tạo
bằng `random_bytes(32)`, gửi cho khách hàng dưới dạng token gốc qua email, còn
database chỉ lưu `hash('sha256', $token)` và thời hạn 60 phút.

### Quản lý

Tài khoản quản lý lấy từ `ADMIN_USERNAME` và `ADMIN_PASSWORD` trong `.env`.
Hiện tại `ADMIN_PASSWORD` được so sánh trực tiếp với mật khẩu gửi lên bằng
`hash_equals()`; đây là so sánh an toàn về thời gian nhưng **không phải cơ chế
hash mật khẩu**. Cần bảo vệ file `.env` và đổi mật khẩu mặc định trước khi triển
khai.

`type="password"` trong form chỉ che ký tự trên giao diện. Muốn bảo vệ mật khẩu
trên đường truyền, ứng dụng phải được chạy qua HTTPS.

## Kiến trúc thư mục

```text
index.php                 Entry point, session và nạp cấu hình môi trường
controllers/index.php     Dispatcher, phân phối act tới route controller tương ứng
controllers/routes/        Controller theo từng nhóm: auth, customer, order, admin,
                           product, catalog, cart và search
controllers/controller.php Nghiệp vụ sản phẩm, danh mục, giỏ hàng và đơn hàng
models/                   Truy vấn database và các model dữ liệu
services/auth.php         Nạp .env và kiểm tra quyền quản lý
services/customer_auth.php Phiên đăng nhập và quyền khách hàng
services/mailer.php       Gửi email SMTP bằng PHPMailer
view/                     Giao diện PHP/HTML
view/images/              Hình ảnh sản phẩm và banner
vendor/                   Dependency Composer
.env.example              Mẫu cấu hình môi trường
```

## Các route chính

| Route | Quyền | Mục đích |
|---|---|---|
| `index.php?act=home` | Công khai | Trang chủ và danh sách sản phẩm |
| `index.php?act=chitietsp&idsp=...` | Công khai | Chi tiết sản phẩm |
| `index.php?act=search&q=...` | Công khai | Tìm kiếm sản phẩm |
| `index.php?act=customer_profile` | Khách hàng | Hồ sơ và hạng thành viên |
| `index.php?act=customers` | Quản lý | Danh sách khách hàng và hạng |
| `index.php?act=register` | Công khai | Đăng ký khách hàng |
| `index.php?act=login` | Công khai | Đăng nhập quản lý/khách hàng |
| `index.php?act=logout` | Đã đăng nhập | Đăng xuất |
| `index.php?act=forgot_password` | Công khai | Yêu cầu đặt lại mật khẩu |
| `index.php?act=reset_password&token=...` | Công khai | Đặt lại mật khẩu |
| `index.php?act=addcart` | Công khai | Thêm sản phẩm vào giỏ |
| `index.php?act=cart` | Công khai | Xem giỏ hàng |
| `index.php?act=checkout` | Khách hàng | Mở trang thanh toán |
| `index.php?act=placeorder` | Khách hàng | Tạo đơn hàng |
| `index.php?act=customer_orders` | Khách hàng | Xem đơn hàng của mình |
| `index.php?act=order_detail&id=...` | Khách hàng/Quản lý | Xem chi tiết một đơn hàng |
| `index.php?act=cancel_customer_order` | Khách hàng | Yêu cầu huỷ đơn |
| `index.php?act=admin` | Quản lý | Trang quản trị |
| `index.php?act=orders` | Quản lý | Quản lý đơn hàng |
| `index.php?act=update_order_status` | Quản lý | Cập nhật trạng thái đơn |
| `index.php?act=cancel_order` | Quản lý | Huỷ đơn hàng |
| `index.php?act=hienthiDM` | Quản lý | Quản lý danh mục |
| `index.php?act=hienthiSP` | Quản lý | Quản lý sản phẩm |
| `index.php?act=check_duplicate` | Công khai/AJAX | Kiểm tra dữ liệu trùng |

Các thao tác thêm, sửa, xoá và đặt hàng sử dụng `POST` khi phù hợp; tham số
định danh được kiểm tra kiểu dữ liệu trước khi xử lý.

## Kiểm tra nhanh

Kiểm tra cú pháp các file PHP quan trọng:

```powershell
D:\XAMPP\php\php.exe -l index.php
D:\XAMPP\php\php.exe -l controllers\index.php
D:\XAMPP\php\php.exe -l controllers\controller.php
D:\XAMPP\php\php.exe -l models\khachhang.php
D:\XAMPP\php\php.exe -l services\mailer.php
```

Kiểm tra trên trình duyệt:

1. Xem danh sách và chi tiết sản phẩm.
2. Đăng ký bằng email và bằng số điện thoại.
3. Đăng nhập khách hàng và quản lý.
4. Gửi yêu cầu quên mật khẩu, nhận email và đặt lại mật khẩu.
5. Thêm sản phẩm vào giỏ, cập nhật số lượng và xoá sản phẩm.
6. Đặt hàng với từng phương thức thanh toán và voucher.
7. Kiểm tra tồn kho sau khi đặt hoặc huỷ đơn.
8. Kiểm tra lịch sử đơn hàng và quyền huỷ đơn của khách hàng.
9. Đổi trạng thái đơn hàng theo đúng thứ tự trong khu vực quản lý.
10. Thêm, sửa, xoá danh mục và sản phẩm; kiểm tra upload hình ảnh.

## Lưu ý bảo mật

- Đổi `ADMIN_PASSWORD` trước khi sử dụng thực tế.
- Không đưa `.env`, mật khẩu SMTP hoặc Google App Password vào Git.
- Chạy production qua HTTPS.
- Bổ sung CSRF token cho các form thay đổi dữ liệu trước khi triển khai
  production.
- Giới hạn và kiểm tra MIME/type, kích thước và phần mở rộng file upload trước
  khi cho phép người dùng quản lý tải ảnh lên.
- Không bật `display_errors` trong production; thiết lập logging thay thế.
- Đảm bảo thư mục `view/images/` tồn tại và có quyền ghi khi thêm/sửa sản phẩm.
