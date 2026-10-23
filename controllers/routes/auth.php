<?php

require_once __DIR__ . "/../controller.php";
require_once __DIR__ . "/../../services/mailer.php";
require_once __DIR__ . "/../../services/auth.php";
require_once __DIR__ . "/../../services/customer_auth.php";

switch ($act)
{
        case 'login':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $username = trim($_POST['username'] ?? '');
                $password = (string) ($_POST['password'] ?? '');
                $configuredUsername = (string) ($_ENV['ADMIN_USERNAME'] ?? '');
                $configuredPassword = (string) ($_ENV['ADMIN_PASSWORD'] ?? '');
                if ($configuredUsername !== '' && $username === $configuredUsername
                    && $configuredPassword !== '' && hash_equals($configuredPassword, $password)) {
                    session_regenerate_id(true);
                    unset(
                        $_SESSION['customer_id'],
                        $_SESSION['customer_name'],
                        $_SESSION['customer_email'],
                        $_SESSION['customer_phone']
                    );
                    $_SESSION['user_role'] = 'manager';
                    header('Location: index.php?act=admin');
                    exit;
                }
                $customer = (new khachhang())->findByIdentifier($username);
                if ($customer && password_verify($password, $customer['mat_khau'])) {
                    customerLogin($customer);
                    header('Location: index.php?act=home');
                    exit;
                }
                $loginError = 'Email/số điện thoại hoặc mật khẩu không đúng.';
            }
            include __DIR__ . "/../../view/login.php";
            break;
        case 'register':
            $registerError = '';
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $name = trim($_POST['ho_ten'] ?? '');
                $phone = trim($_POST['sdt'] ?? '');
                $email = strtolower(trim($_POST['email'] ?? ''));
                $password = (string) ($_POST['password'] ?? '');
                $confirm = (string) ($_POST['password_confirmation'] ?? '');
                if ($name === '' || ($phone === '' && $email === '') || ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL))
                    || ($phone !== '' && !preg_match('/^[0-9+ ()-]{8,20}$/', $phone))
                    || strlen($password) < 8 || $password !== $confirm) {
                    $registerError = 'Vui lòng nhập đúng thông tin và mật khẩu tối thiểu 8 ký tự.';
                } else {
                    $customerModel = new khachhang();
                    if (($email !== '' && $customerModel->findByEmail($email))
                        || ($phone !== '' && $customerModel->findByIdentifier($phone))) {
                        $registerError = 'Email hoặc số điện thoại đã được đăng ký.';
                    } else {
                        try {
                            $customerModel->create($name, $phone, $email, password_hash($password, PASSWORD_DEFAULT));
                            $customer = $customerModel->findByIdentifier($email !== '' ? $email : $phone);
                            customerLogin($customer);
                            header('Location: index.php?act=home');
                            exit;
                        } catch (Throwable $e) {
                            $registerError = 'Không thể tạo tài khoản. Vui lòng kiểm tra lại dữ liệu.';
                        }
                    }
                }
            }
            include __DIR__ . "/../../view/register.php";
            break;
        case 'forgot_password':
            $forgotMessage = '';
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $email = strtolower(trim($_POST['email'] ?? ''));
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $customerModel = new khachhang();
                    $customer = $customerModel->findByEmail($email);
                    if ($customer) {
                        $token = bin2hex(random_bytes(32));
                        $customerModel->saveResetToken((int) $customer['id'], hash('sha256', $token), date('Y-m-d H:i:s', time() + 3600));
                        try {
                            sendPasswordResetEmail($customer, $token);
                        } catch (Throwable $e) {
                            $forgotMessage = 'Không thể gửi email lúc này. Vui lòng thử lại sau.';
                        }
                    }
                }
                if ($forgotMessage === '') {
                    $forgotMessage = 'Nếu email đã được đăng ký, hướng dẫn đặt lại mật khẩu đã được gửi.';
                }
            }
            include __DIR__ . "/../../view/forgot_password.php";
            break;
        case 'reset_password':
            $token = trim($_GET['token'] ?? $_POST['token'] ?? '');
            $resetError = '';
            if ($token === '' || !(new khachhang())->findByResetToken(hash('sha256', $token))) {
                $resetError = 'Liên kết đặt lại mật khẩu không hợp lệ hoặc đã hết hạn.';
            } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $password = (string) ($_POST['password'] ?? '');
                $confirm = (string) ($_POST['password_confirmation'] ?? '');
                if (strlen($password) < 8 || $password !== $confirm) {
                    $resetError = 'Mật khẩu phải có ít nhất 8 ký tự và khớp xác nhận.';
                } else {
                    $customer = (new khachhang())->findByResetToken(hash('sha256', $token));
                    (new khachhang())->updatePassword((int) $customer['id'], password_hash($password, PASSWORD_DEFAULT));
                    header('Location: index.php?act=login&reset=1');
                    exit;
                }
            }
            include __DIR__ . "/../../view/reset_password.php";
            break;
        case 'logout':
            unset(
                $_SESSION['user_role'],
                $_SESSION['customer_id'],
                $_SESSION['customer_name'],
                $_SESSION['customer_email'],
                $_SESSION['customer_phone']
            );
            header('Location: index.php?act=home');
            exit;
}
?>

