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
    $mail->Body = '<h2>Đặt hàng thành công</h2>'
        . '<p>Xin chào ' . htmlspecialchars($order['ten_khachhang'], ENT_QUOTES, 'UTF-8') . ',</p>'
        . '<p>Đơn hàng <strong>' . htmlspecialchars($order['ma_donhang'], ENT_QUOTES, 'UTF-8') . '</strong> đã được tiếp nhận.</p>'
        . '<p>Tổng tiền dự kiến: <strong>' . number_format($order['total'], 0, ',', '.') . 'đ</strong></p>'
        . '<p>Địa chỉ giao hàng: ' . nl2br(htmlspecialchars($order['dia_chi'], ENT_QUOTES, 'UTF-8')) . '</p>';
    $mail->AltBody = 'Đơn hàng ' . $order['ma_donhang'] . ' đã được tiếp nhận. Tổng tiền: '
        . number_format($order['total'], 0, ',', '.') . 'đ.';
    $mail->send();
}
