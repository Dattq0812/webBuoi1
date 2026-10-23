<?php

function sendOrderConfirmationEmail(array $order): void
{
    $autoload = __DIR__ . '/../vendor/autoload.php';
    if (!is_file($autoload)) {
        throw new RuntimeException('Chưa cài PHPMailer. Hãy chạy composer install trong thư mục dự án.');
    }
    require_once $autoload;

    loadEnvironment();

    $config = static function (string $key, string $default = ''): string {
        return (string) ($_ENV[$key] ?? $default);
    };
    $required = ['MAIL_HOST', 'MAIL_USERNAME', 'MAIL_PASSWORD', 'MAIL_FROM'];
    foreach ($required as $key) {
        if ($config($key) === '') {
            throw new RuntimeException("Thiếu cấu hình SMTP {$key}.");
        }
    }

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = $config('MAIL_HOST');
    $mail->SMTPAuth = true;
    $mail->Username = $config('MAIL_USERNAME');
    $mail->Password = $config('MAIL_PASSWORD');
    $mail->SMTPSecure = $config('MAIL_ENCRYPTION', PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS);
    $mail->Port = (int) $config('MAIL_PORT', '587');
    $mail->CharSet = 'UTF-8';
    $mail->setFrom($config('MAIL_FROM'), $config('MAIL_FROM_NAME', 'TechZone'));
    $mail->addAddress($order['email'], $order['ten_khachhang']);
    $mail->isHTML(true);
    $mail->Subject = 'TechZone - Xác nhận đơn hàng ' . $order['ma_donhang'];
    $itemsHtml = '<table border="1" cellpadding="8" cellspacing="0"><tr><th>Sản phẩm</th><th>Đơn giá</th><th>Số lượng</th><th>Thành tiền</th></tr>';
    $itemsText = '';
    $attachedImages = [];
    foreach ($order['items'] as $item) {
        $imageName = basename((string) ($item['image'] ?? ''));
        $imagePath = __DIR__ . '/../view/images/' . $imageName;
        if ($imageName !== '' && !isset($attachedImages[$imageName]) && is_file($imagePath)) {
            $mail->addAttachment($imagePath, $imageName);
            $attachedImages[$imageName] = true;
        }
        $itemsHtml .= '<tr><td>' . htmlspecialchars($item['Name'], ENT_QUOTES, 'UTF-8') . '</td>'
            . '<td>' . number_format($item['Price'], 0, ',', '.') . 'đ</td>'
            . '<td>' . (int) $item['quantity'] . '</td>'
            . '<td>' . number_format($item['subtotal'], 0, ',', '.') . 'đ</td></tr>';
        $itemsText .= $item['Name'] . ' x' . (int) $item['quantity'] . ' - '
            . number_format($item['subtotal'], 0, ',', '.') . "đ\n";
    }
    $itemsHtml .= '</table>';
    $attachmentMessage = empty($attachedImages)
        ? ''
        : '<p>Hình ảnh sản phẩm được đính kèm trong email này.</p>';
    $mail->Body = '<h2>Đặt hàng thành công</h2>'
        . '<p>Xin chào ' . htmlspecialchars($order['ten_khachhang'], ENT_QUOTES, 'UTF-8') . ',</p>'
        . '<p>Đơn hàng <strong>' . htmlspecialchars($order['ma_donhang'], ENT_QUOTES, 'UTF-8') . '</strong> đã được tiếp nhận.</p>'
        . '<p>Thanh toán: <strong>' . htmlspecialchars($order['phuong_thuc_thanh_toan'], ENT_QUOTES, 'UTF-8') . '</strong></p>'
        . ($order['ma_voucher'] !== '' ? '<p>Voucher: ' . htmlspecialchars($order['ma_voucher'], ENT_QUOTES, 'UTF-8') . ' - giảm ' . number_format($order['tien_giam'], 0, ',', '.') . 'đ</p>' : '')
        . '<h3>Sản phẩm đã đặt</h3>' . $itemsHtml
        . $attachmentMessage
        . '<p>Tổng tiền dự kiến: <strong>' . number_format($order['total'], 0, ',', '.') . 'đ</strong></p>'
        . '<p>Địa chỉ giao hàng: ' . nl2br(htmlspecialchars($order['dia_chi'], ENT_QUOTES, 'UTF-8')) . '</p>';
    $mail->AltBody = 'Đơn hàng ' . $order['ma_donhang'] . " đã được tiếp nhận.\n\n"
        . "Sản phẩm:\n" . $itemsText . "\nTổng tiền: "
        . number_format($order['total'], 0, ',', '.') . 'đ.';
    $mail->send();
}

function sendOrderCancellationEmail(array $order): void
{
    $autoload = __DIR__ . '/../vendor/autoload.php';
    if (!is_file($autoload)) {
        throw new RuntimeException('Chưa cài PHPMailer.');
    }
    require_once $autoload;
    loadEnvironment();
    $config = static function (string $key, string $default = ''): string {
        return (string) ($_ENV[$key] ?? $default);
    };
    foreach (['MAIL_HOST', 'MAIL_USERNAME', 'MAIL_PASSWORD', 'MAIL_FROM'] as $key) {
        if ($config($key) === '') {
            throw new RuntimeException("Thiếu cấu hình SMTP {$key}.");
        }
    }
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = $config('MAIL_HOST');
    $mail->SMTPAuth = true;
    $mail->Username = $config('MAIL_USERNAME');
    $mail->Password = $config('MAIL_PASSWORD');
    $mail->SMTPSecure = $config('MAIL_ENCRYPTION', PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS);
    $mail->Port = (int) $config('MAIL_PORT', '587');
    $mail->CharSet = 'UTF-8';
    $mail->setFrom($config('MAIL_FROM'), $config('MAIL_FROM_NAME', 'TechZone'));
    $mail->addAddress($order['email'], $order['ten_khachhang']);
    $mail->isHTML(true);
    $mail->Subject = 'TechZone - Đơn hàng ' . $order['ma_donhang'] . ' đã bị hủy';
    $mail->Body = '<h2>Thông báo hủy đơn hàng</h2>'
        . '<p>Đơn hàng <strong>' . htmlspecialchars($order['ma_donhang'], ENT_QUOTES, 'UTF-8') . '</strong> đã được hủy.</p>'
        . '<p>Lý do: ' . nl2br(htmlspecialchars($order['ly_do_huy'], ENT_QUOTES, 'UTF-8')) . '</p>'
        . '<h3>Sản phẩm trong đơn</h3><ul>';
    foreach ($order['items'] as $item) {
        $mail->Body .= '<li>' . htmlspecialchars($item['Name'], ENT_QUOTES, 'UTF-8')
            . ' x' . (int) $item['quantity'] . '</li>';
    }
    $mail->Body .= '</ul>';
    $mail->AltBody = 'Đơn hàng ' . $order['ma_donhang'] . ' đã bị hủy. Lý do: ' . $order['ly_do_huy'];
    $mail->send();
}

function sendPasswordResetEmail(array $customer, string $token): void
{
    require_once __DIR__ . '/../vendor/autoload.php';
    loadEnvironment();
    $config = static function (string $key, string $default = ''): string {
        return (string) ($_ENV[$key] ?? $default);
    };
    foreach (['MAIL_HOST', 'MAIL_USERNAME', 'MAIL_PASSWORD', 'MAIL_FROM'] as $key) {
        if ($config($key) === '') {
            throw new RuntimeException("Thiếu cấu hình SMTP {$key}.");
        }
    }
    $link = 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php')
        . '/index.php?act=reset_password&token=' . rawurlencode($token);
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = $config('MAIL_HOST');
    $mail->SMTPAuth = true;
    $mail->Username = $config('MAIL_USERNAME');
    $mail->Password = $config('MAIL_PASSWORD');
    $mail->SMTPSecure = $config('MAIL_ENCRYPTION', PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS);
    $mail->Port = (int) $config('MAIL_PORT', '587');
    $mail->CharSet = 'UTF-8';
    $mail->setFrom($config('MAIL_FROM'), $config('MAIL_FROM_NAME', 'TechZone'));
    $mail->addAddress($customer['email'], $customer['ho_ten']);
    $mail->isHTML(true);
    $mail->Subject = 'TechZone - Đặt lại mật khẩu';
    $mail->Body = '<p>Xin chào ' . htmlspecialchars($customer['ho_ten'], ENT_QUOTES, 'UTF-8') . ',</p>'
        . '<p>Nhấn vào liên kết sau để đặt lại mật khẩu (có hiệu lực trong 60 phút):</p>'
        . '<p><a href="' . htmlspecialchars($link, ENT_QUOTES, 'UTF-8') . '">Đặt lại mật khẩu</a></p>';
    $mail->AltBody = "Đặt lại mật khẩu tại: {$link}";
    $mail->send();
}

function sendCustomerCancellationToShop(array $order): void
{
    require_once __DIR__ . '/../vendor/autoload.php';
    loadEnvironment();
    $config = static function (string $key, string $default = ''): string {
        return (string) ($_ENV[$key] ?? $default);
    };
    foreach (['MAIL_HOST', 'MAIL_USERNAME', 'MAIL_PASSWORD', 'MAIL_FROM'] as $key) {
        if ($config($key) === '') {
            throw new RuntimeException("Thiếu cấu hình SMTP {$key}.");
        }
    }
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = $config('MAIL_HOST');
    $mail->SMTPAuth = true;
    $mail->Username = $config('MAIL_USERNAME');
    $mail->Password = $config('MAIL_PASSWORD');
    $mail->SMTPSecure = $config('MAIL_ENCRYPTION', PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS);
    $mail->Port = (int) $config('MAIL_PORT', '587');
    $mail->CharSet = 'UTF-8';
    $mail->setFrom($config('MAIL_FROM'), $config('MAIL_FROM_NAME', 'TechZone'));
    $mail->addAddress($config('MAIL_USERNAME'));
    $mail->isHTML(true);
    $mail->Subject = 'TechZone - Khách yêu cầu huỷ đơn ' . $order['ma_donhang'];
    $mail->Body = '<h2>Khách hàng yêu cầu huỷ đơn</h2>'
        . '<p>Mã đơn: <strong>' . htmlspecialchars($order['ma_donhang'], ENT_QUOTES, 'UTF-8') . '</strong></p>'
        . '<p>Khách hàng: ' . htmlspecialchars($order['ten_khachhang'], ENT_QUOTES, 'UTF-8')
        . ' - ' . htmlspecialchars($order['sdt'], ENT_QUOTES, 'UTF-8') . '</p>'
        . '<p>Email: ' . htmlspecialchars($order['email'], ENT_QUOTES, 'UTF-8') . '</p>'
        . '<p>Lý do: ' . nl2br(htmlspecialchars($order['ly_do_huy'], ENT_QUOTES, 'UTF-8')) . '</p>';
    $mail->AltBody = 'Khách yêu cầu huỷ đơn ' . $order['ma_donhang']
        . '. Lý do: ' . $order['ly_do_huy'];
    $mail->send();
}
