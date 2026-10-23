<?php

require_once __DIR__ . "/../controller.php";
require_once __DIR__ . "/../../services/mailer.php";
require_once __DIR__ . "/../../services/auth.php";
require_once __DIR__ . "/../../services/customer_auth.php";

switch ($act)
{
        case 'addcart':
            $id_sp = filter_input(INPUT_POST, 'id_sp', FILTER_VALIDATE_INT);
            $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
            if (!$id_sp || !$quantity || $quantity < 1) {
                echo "<script>alert('ThÃ´ng tin sáº£n pháº©m khÃ´ng há»£p lá»‡!'); window.history.back();</script>";
                exit;
            }
            $sp = new sanpham();
            $sp->setId($id_sp);
            $controller = new controller();
            $products = $controller->getOneProduct($sp);
            if (empty($products) || (int) $products[0]['Mount'] < 1) {
                echo "<script>alert('Sáº£n pháº©m khÃ´ng tá»“n táº¡i hoáº·c Ä‘Ã£ háº¿t hÃ ng!'); window.history.back();</script>";
                exit;
            }
            $stock = (int) $products[0]['Mount'];
            $current = (int) ($_SESSION['cart'][$id_sp] ?? 0);
            $_SESSION['cart'][$id_sp] = min($stock, $current + $quantity);
            echo "<script>window.location.href = 'index.php?act=cart';</script>";
            exit;

        case 'cart':
            $controller = new controller();
            $cart = $_SESSION['cart'] ?? [];
            $cartItems = $controller->getCartItems($cart);
            $cartTotal = 0;
            foreach ($cartItems as $item) {
                $cartTotal += $item['subtotal'];
                $_SESSION['cart'][$item['id_sp']] = $item['quantity'];
            }
            include __DIR__ . "/../../view/cart.php";
            break;

        case 'updatecart':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo "<script>window.location.href = 'index.php?act=cart';</script>";
                exit;
            }
            $quantities = $_POST['quantities'] ?? [];
            $controller = new controller();
            foreach ($quantities as $id => $quantity) {
                $id_sp = filter_var($id, FILTER_VALIDATE_INT);
                $quantity = filter_var($quantity, FILTER_VALIDATE_INT);
                if (!$id_sp || $quantity === false || $quantity < 0) {
                    continue;
                }
                $sp = new sanpham();
                $sp->setId($id_sp);
                $products = $controller->getOneProduct($sp);
                if (empty($products) || $quantity === 0) {
                    unset($_SESSION['cart'][$id_sp]);
                    continue;
                }
                $_SESSION['cart'][$id_sp] = min($quantity, (int) $products[0]['Mount']);
            }
            echo "<script>window.location.href = 'index.php?act=cart';</script>";
            exit;

        case 'removecart':
            $id_sp = filter_input(INPUT_GET, 'idsp', FILTER_VALIDATE_INT);
            if ($id_sp) {
                unset($_SESSION['cart'][$id_sp]);
            }
            echo "<script>window.location.href = 'index.php?act=cart';</script>";
            exit;

        case 'checkout':
            requireCustomer();
            $controller = new controller();
            $cartItems = $controller->getCartItems($_SESSION['cart'] ?? []);
            if (empty($cartItems)) {
                echo "<script>
                        alert('Giá» hÃ ng Ä‘ang trá»‘ng!');
                        window.location.href = 'index.php?act=home';
                      </script>";
                exit;
            }
            $cartTotal = 0;
            foreach ($cartItems as $item) {
                $cartTotal += $item['subtotal'];
            }
            $customerLoyalty = $controller->getCustomerLoyalty(customerId());
            $voucherOptions = [
                'TECH10' => ['type' => 'percent', 'value' => 10],
                'TECH50K' => ['type' => 'fixed', 'value' => 50000],
            ];
            include __DIR__ . "/../../view/checkout.php";
            break;

        case 'placeorder':
            requireCustomer();
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo "<script>window.location.href = 'index.php?act=cart';</script>";
                exit;
            }
            $buyerName = trim($_POST['buyer_name'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $address = trim($_POST['address'] ?? '');
            $paymentMethod = $_POST['payment_method'] ?? '';
            $voucherCode = strtoupper(trim($_POST['voucher_code'] ?? ''));
            $voucherOptions = [
                'TECH10' => ['type' => 'percent', 'value' => 10],
                'TECH50K' => ['type' => 'fixed', 'value' => 50000],
            ];
            if ($buyerName === '' || $address === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)
                || !preg_match('/^[0-9+ ()-]{8,20}$/', $phone)
                || !in_array($paymentMethod, ['cod', 'bank_transfer'], true)) {
                echo "<script>alert('Vui lÃ²ng nháº­p Ä‘Ãºng há» tÃªn, Ä‘á»‹a chá»‰, sá»‘ Ä‘iá»‡n thoáº¡i vÃ  email.'); window.history.back();</script>";
                exit;
            }
            $controller = new controller();
            $cartItems = $controller->getCartItems($_SESSION['cart'] ?? []);
            if (empty($cartItems)) {
                echo "<script>alert('Giá» hÃ ng Ä‘ang trá»‘ng!'); window.location.href = 'index.php?act=home';</script>";
                exit;
            }
            $cartTotal = 0;
            foreach ($cartItems as $item) {
                $cartTotal += $item['subtotal'];
            }
            $customerLoyalty = $controller->getCustomerLoyalty(customerId());
            if ($voucherCode !== '' && !isset($voucherOptions[$voucherCode])) {
                echo "<script>alert('MÃ£ voucher khÃ´ng há»£p lá»‡.'); window.history.back();</script>";
                exit;
            }
            $discount = 0;
            if ($voucherCode !== '') {
                $voucher = $voucherOptions[$voucherCode];
                $discount = $voucher['type'] === 'percent'
                    ? $cartTotal * $voucher['value'] / 100
                    : $voucher['value'];
                $discount = min($cartTotal, $discount);
            }
            $loyaltyDiscount = $cartTotal * (int) $customerLoyalty['tier']['discount'] / 100;
            $discount = min($cartTotal, $discount + $loyaltyDiscount);
            $order = [
                'customer_id' => customerId(),
                'ma_donhang' => 'DH' . date('YmdHis') . strtoupper(bin2hex(random_bytes(2))),
                'ten_khachhang' => $buyerName,
                'sdt' => $phone,
                'email' => $email,
                'dia_chi' => $address,
                'ghi_chu' => trim($_POST['note'] ?? ''),
                'phuong_thuc_thanh_toan' => $paymentMethod,
                'ma_voucher' => $voucherCode,
                'tien_giam' => $discount,
                'total' => $cartTotal - $discount,
                'items' => $cartItems,
            ];
            try {
                $controller->createOrder($order, $cartItems);
            } catch (Throwable $e) {
                echo "<h3 style='color:red'>KhÃ´ng thá»ƒ lÆ°u Ä‘Æ¡n hÃ ng: "
                    . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8')
                    . "</h3>";
                echo "<p>ÄÆ¡n hÃ ng chÆ°a Ä‘Æ°á»£c táº¡o vÃ  sá»‘ lÆ°á»£ng tá»“n kho chÆ°a thay Ä‘á»•i. "
                    . "Vui lÃ²ng quay láº¡i giá» hÃ ng Ä‘á»ƒ kiá»ƒm tra láº¡i.</p>";
                echo "<a href='index.php?act=cart'>Quay láº¡i giá» hÃ ng</a>";
                exit;
            }
            $emailSent = true;
            $emailError = '';
            try {
                sendOrderConfirmationEmail($order);
            } catch (Throwable $e) {
                $emailSent = false;
                $emailError = $e->getMessage();
            }
            $_SESSION['cart'] = [];
            $orderCode = $order['ma_donhang'];
            $paymentMethod = $order['phuong_thuc_thanh_toan'];
            $voucherCode = $order['ma_voucher'];
            $discount = $order['tien_giam'];
            include __DIR__ . "/../../view/order_success.php";
            break;
}
?>

