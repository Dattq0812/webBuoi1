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
    $itemsHtml = '<table role="presentation" cellpadding="8" cellspacing="0" '
        . 'style="width:100%; border-collapse:collapse; border:1px solid #dbe3ef;">'
        . '<tr style="background:#f8fafc;">'
        . '<th style="text-align:left; border-bottom:1px solid #dbe3ef;">Sản phẩm</th>'
        . '<th style="text-align:right; border-bottom:1px solid #dbe3ef;">Đơn giá</th>'
        . '<th style="text-align:center; border-bottom:1px solid #dbe3ef;">Số lượng</th>'
        . '<th style="text-align:right; border-bottom:1px solid #dbe3ef;">Thành tiền</th></tr>';
    $itemsText = '';
    $embeddedImages = [];
    foreach ($order['items'] as $item) {
        $imageName = basename((string) ($item['image'] ?? ''));
        $imagePath = __DIR__ . '/../view/images/' . $imageName;
        $imageHtml = '<span style="color:#64748b;">Không có ảnh</span>';
        if ($imageName !== '' && is_file($imagePath)) {
            if (!isset($embeddedImages[$imageName])) {
                $contentId = 'product-' . md5($imageName);
                $mail->addEmbeddedImage($imagePath, $contentId, $imageName);
                $embeddedImages[$imageName] = $contentId;
            }
            $imageHtml = '<img src="cid:' . $embeddedImages[$imageName] . '" '
                . 'alt="" width="64" height="64" style="display:block; width:64px; height:64px; object-fit:cover; border-radius:6px;">';
        }
        $itemsHtml .= '<tr>'
            . '<td style="border-bottom:1px solid #e2e8f0; vertical-align:middle;">'
            . '<div style="display:flex; align-items:center; gap:10px;">'
            . $imageHtml
            . '<span>' . htmlspecialchars($item['Name'], ENT_QUOTES, 'UTF-8') . '</span>'
            . '</div></td>'
            . '<td style="border-bottom:1px solid #e2e8f0; text-align:right; vertical-align:middle;">'
            . number_format($item['Price'], 0, ',', '.') . 'đ</td>'
            . '<td style="border-bottom:1px solid #e2e8f0; text-align:center; vertical-align:middle;">'
            . (int) $item['quantity'] . '</td>'
            . '<td style="border-bottom:1px solid #e2e8f0; text-align:right; vertical-align:middle;">'
            . number_format($item['subtotal'], 0, ',', '.') . 'đ</td></tr>';
        $itemsText .= $item['Name'] . ' x' . (int) $item['quantity'] . ' - '
            . number_format($item['subtotal'], 0, ',', '.') . "đ\n";
    }
    $itemsHtml .= '</table>';
    $imageMessage = empty($embeddedImages)
        ? ''
        : '<p>Hình ảnh sản phẩm được hiển thị ngay trong danh sách sản phẩm.</p>';
    $mail->Body = '<h2>Đặt hàng thành công</h2>'
        . '<p>Xin chào ' . htmlspecialchars($order['ten_khachhang'], ENT_QUOTES, 'UTF-8') . ',</p>'
        . '<p>Đơn hàng <strong>' . htmlspecialchars($order['ma_donhang'], ENT_QUOTES, 'UTF-8') . '</strong> đã được tiếp nhận.</p>'
        . '<p>Thanh toán: <strong>' . htmlspecialchars($order['phuong_thuc_thanh_toan'], ENT_QUOTES, 'UTF-8') . '</strong></p>'
        . ($order['ma_voucher'] !== '' ? '<p>Voucher: ' . htmlspecialchars($order['ma_voucher'], ENT_QUOTES, 'UTF-8') . ' - giảm ' . number_format($order['tien_giam'], 0, ',', '.') . 'đ</p>' : '')
        . '<h3>Sản phẩm đã đặt</h3>' . $itemsHtml
        . $imageMessage
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

function sendCustomerReturnToShop(array $order): void
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
    $mail->Subject = 'TechZone - Khách yêu cầu hoàn trả đơn ' . $order['ma_donhang'];
    $mail->Body = '<h2>Khách hàng yêu cầu hoàn trả đơn</h2>'
        . '<p>Mã đơn: <strong>' . htmlspecialchars($order['ma_donhang'], ENT_QUOTES, 'UTF-8') . '</strong></p>'
        . '<p>Khách hàng: ' . htmlspecialchars($order['ten_khachhang'], ENT_QUOTES, 'UTF-8')
        . ' - ' . htmlspecialchars($order['sdt'], ENT_QUOTES, 'UTF-8') . '</p>'
        . '<p>Email: ' . htmlspecialchars($order['email'], ENT_QUOTES, 'UTF-8') . '</p>'
        . '<p>Lý do: ' . nl2br(htmlspecialchars($order['ly_do_huy'], ENT_QUOTES, 'UTF-8')) . '</p>';
    $mail->AltBody = 'Khách yêu cầu hoàn trả đơn ' . $order['ma_donhang']
        . '. Lý do: ' . $order['ly_do_huy'];
    $mail->send();
}
