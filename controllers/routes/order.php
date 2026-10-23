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
                echo "<script>alert('Mã đơn hàng không hợp lệ.'); window.history.back();</script>";
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
                echo '<div class="container"><div class="card empty">Không tìm thấy đơn hàng hoặc bạn không có quyền xem đơn này.</div></div>';
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
                echo "<script>alert('Thông tin trạng thái không hợp lệ.'); window.location.href='index.php?act=orders';</script>";
                exit;
            }
            $updated = (new controller())->updateOrderStatus($id, $currentStatus, $nextStatus);
            if (!$updated) {
                echo "<script>alert('Đơn hàng đã được cập nhật hoặc trạng thái không còn hợp lệ.'); window.location.href='index.php?act=orders';</script>";
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
                echo "<script>alert('Vui lòng nhập lý do hủy hợp lệ.'); window.location.href='index.php?act=orders';</script>";
                exit;
            }
            try {
                $cancelledOrder = (new controller())->cancelOrder($id, $reason);
                sendOrderCancellationEmail($cancelledOrder);
                header('Location: index.php?act=orders');
                exit;
            } catch (Throwable $e) {
                echo "<h3 style='color:red'>Không thể hủy đơn hàng: "
                    . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</h3>";
                echo "<a href='index.php?act=orders'>Quay lại danh sách đơn hàng</a>";
                exit;
            }
}
?>

