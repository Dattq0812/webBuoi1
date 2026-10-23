<?php

require_once __DIR__ . "/../controller.php";
require_once __DIR__ . "/../../services/mailer.php";
require_once __DIR__ . "/../../services/auth.php";
require_once __DIR__ . "/../../services/customer_auth.php";

switch ($act)
{
        case 'order_detail':
            $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
            if (!$id || $id <= 0) {
                echo "<script>alert('MÃ£ Ä‘Æ¡n hÃ ng khÃ´ng há»£p lá»‡.'); window.history.back();</script>";
                exit;
            }
            $orderController = new controller();
            if (isManager()) {
                $orderDetail = $orderController->getOrderDetail($id);
            } elseif (isCustomer()) {
                $orderDetail = $orderController->getOrderDetail($id, customerId());
            } else {
                header('Location: index.php?act=login');
                exit;
            }
            if (!$orderDetail) {
                http_response_code(404);
                echo '<div class="container"><div class="card empty">KhÃ´ng tÃ¬m tháº¥y Ä‘Æ¡n hÃ ng hoáº·c báº¡n khÃ´ng cÃ³ quyá»n xem Ä‘Æ¡n nÃ y.</div></div>';
                exit;
            }
            include __DIR__ . "/../../view/order_detail.php";
            break;
        case 'orders':
            requireManager();
            $orders = (new controller())->getOrders();
            include __DIR__ . "/../../view/orders.php";
            break;
        case 'update_order_status':
            requireManager();
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            $currentStatus = $_POST['trang_thai_hien_tai'] ?? '';
            $nextStatus = $_POST['trang_thai_tiep_theo'] ?? '';
            $statusFlow = [
                'cho_xac_nhan' => 'da_xac_nhan',
                'da_xac_nhan' => 'dang_giao',
                'dang_giao' => 'da_giao',
                'da_giao' => 'hoan_tra',
            ];
            if (!$id || !isset($statusFlow[$currentStatus]) || $statusFlow[$currentStatus] !== $nextStatus) {
                echo "<script>alert('ThÃ´ng tin tráº¡ng thÃ¡i khÃ´ng há»£p lá»‡.'); window.location.href='index.php?act=orders';</script>";
                exit;
            }
            $updated = (new controller())->updateOrderStatus($id, $currentStatus, $nextStatus);
            if (!$updated) {
                echo "<script>alert('ÄÆ¡n hÃ ng Ä‘Ã£ Ä‘Æ°á»£c cáº­p nháº­t hoáº·c tráº¡ng thÃ¡i khÃ´ng cÃ²n há»£p lá»‡.'); window.location.href='index.php?act=orders';</script>";
                exit;
            }
            $returnToDetail = ($_POST['return_to'] ?? '') === 'order_detail';
            header('Location: index.php?act=' . ($returnToDetail ? 'order_detail&id=' . $id : 'orders'));
            exit;
        case 'cancel_order':
            requireManager();
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            $reason = trim($_POST['ly_do_huy'] ?? '');
            if (!$id || $reason === '' || mb_strlen($reason) > 1000) {
                echo "<script>alert('Vui lÃ²ng nháº­p lÃ½ do há»§y há»£p lá»‡.'); window.location.href='index.php?act=orders';</script>";
                exit;
            }
            try {
                $cancelledOrder = (new controller())->cancelOrder($id, $reason);
                sendOrderCancellationEmail($cancelledOrder);
                header('Location: index.php?act=orders');
                exit;
            } catch (Throwable $e) {
                echo "<h3 style='color:red'>KhÃ´ng thá»ƒ há»§y Ä‘Æ¡n hÃ ng: "
                    . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</h3>";
                echo "<a href='index.php?act=orders'>Quay láº¡i danh sÃ¡ch Ä‘Æ¡n hÃ ng</a>";
                exit;
            }
}
?>

