<?php

require_once __DIR__ . "/../controller.php";
require_once __DIR__ . "/../../services/mailer.php";
require_once __DIR__ . "/../../services/auth.php";
require_once __DIR__ . "/../../services/customer_auth.php";

switch ($act)
{
        case 'customer_orders':
            requireCustomer();
            $orders = (new controller())->getCustomerOrders(customerId());
            include __DIR__ . "/../../view/customer_orders.php";
            break;
        case 'customer_profile':
            requireCustomer();
            $customerLoyalty = (new controller())->getCustomerLoyalty(customerId());
            include __DIR__ . "/../../view/customer_profile.php";
            break;
        case 'cancel_customer_order':
            requireCustomer();
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            $reason = trim($_POST['ly_do_huy'] ?? '');
            if (!$id || $reason === '' || mb_strlen($reason) > 1000) {
                echo "<script>alert('Vui lÃ²ng nháº­p lÃ½ do há»§y há»£p lá»‡.'); window.location.href='index.php?act=customer_orders';</script>";
                exit;
            }
            try {
                $cancelledOrder = (new controller())->cancelCustomerOrder($id, $reason, customerId());
                sendCustomerCancellationToShop($cancelledOrder);
                header('Location: index.php?act=customer_orders');
                exit;
            } catch (Throwable $e) {
                echo "<h3 style='color:red'>KhÃ´ng thá»ƒ há»§y Ä‘Æ¡n hÃ ng: "
                    . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</h3>";
                echo "<a href='index.php?act=customer_orders'>Quay láº¡i lá»‹ch sá»­ Ä‘Æ¡n hÃ ng</a>";
                exit;
            }
}
?>

