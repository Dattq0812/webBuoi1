# Project Artifact: TechZone

## 1. Thông tin dự án

| Thuộc tính | Giá trị |
|---|---|
| Tên | TechZone |
| Loại | Website thương mại điện tử |
| Nền tảng | PHP 8.2, MySQL, Apache |
| Thư viện email | PHPMailer |
| Quản lý cấu hình | `vlucas/phpdotenv` |
| Entry point | `index.php` |

## 2. Phạm vi chức năng

### Catalog

- Danh mục và sản phẩm có CRUD ở khu vực quản lý.
- Khách hàng xem danh sách, chi tiết và giá khuyến mãi.
- Ảnh sản phẩm được lưu trong `view/images/`.

### Tài khoản

- Quản lý sử dụng tài khoản cấu hình trong `.env`.
- Khách hàng lưu trong bảng `khachhang`.
- Email và số điện thoại là thông tin định danh duy nhất.
- Mật khẩu được lưu dưới dạng hash.
- Token đặt lại mật khẩu được lưu dưới dạng SHA-256 và có thời hạn.

### Đơn hàng

- Đơn hàng lưu thông tin người mua, thanh toán, voucher và tổng tiền.
- Chi tiết đơn lưu snapshot tên, đơn giá và số lượng sản phẩm.
- Đơn hàng liên kết tài khoản qua `donhang.customer_id`.
- Tồn kho được trừ khi đặt hàng và hoàn lại khi huỷ hợp lệ.
- Trạng thái được cập nhật một chiều bởi quản lý.
- Khách chỉ được huỷ đơn của chính mình khi đơn còn `cho_xac_nhan`.

## 3. Luồng dữ liệu chính

```text
Khách hàng
  -> đăng nhập/đăng ký
  -> sản phẩm
  -> giỏ hàng
  -> checkout
  -> donhang + chitiet_donhang
  -> trừ tồn kho
  -> email xác nhận

Quản lý
  -> xem đơn hàng
  -> chuyển trạng thái
  -> huỷ đơn hợp lệ
  -> hoàn tồn kho
  -> email thông báo
```

## 4. Thành phần quan trọng

| Thành phần | Trách nhiệm |
|---|---|
| `controllers/index.php` | Router và xử lý request |
| `controllers/controller.php` | Facade gọi model |
| `models/khachhang.php` | Tài khoản và token reset |
| `models/donhang.php` | Tạo, đọc, cập nhật, huỷ đơn |
| `services/customer_auth.php` | Session và quyền khách hàng |
| `services/auth.php` | Nạp `.env` và quyền quản lý |
| `services/mailer.php` | SMTP và email nghiệp vụ |
| `view/customer_orders.php` | Lịch sử đơn hàng khách |
| `database/customer_auth.sql` | Migration tài khoản khách |

## 5. Trạng thái triển khai

- Đã hoàn tất chức năng catalog, giỏ hàng, checkout và email.
- Đã hoàn tất đăng ký, đăng nhập và khôi phục mật khẩu khách hàng.
- Đã hoàn tất lịch sử đơn hàng và đồng bộ trạng thái.
- Đã hoàn tất huỷ đơn phía khách kèm lý do và email về shop.
- Đã kiểm tra syntax các file PHP liên quan.
- Cần chạy migration database trước khi sử dụng tài khoản khách trên database
  hiện tại.

## 6. Rủi ro và công việc đề xuất

- Bổ sung CSRF token cho tất cả POST request.
- Làm migration SQL idempotent để có thể chạy lại an toàn.
- Chuyển voucher hard-code sang bảng database.
- Bổ sung cơ chế chống trùng mã sản phẩm khi có nhiều người thêm cùng lúc.
- Tắt `display_errors` trong môi trường production.
