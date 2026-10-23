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
                echo "<script>alert('Vui lòng nhập lý do hủy hợp lệ.'); window.location.href='index.php?act=customer_orders';</script>";
                exit;
            }
            try {
                $cancelledOrder = (new controller())->cancelCustomerOrder($id, $reason, customerId());
                sendCustomerCancellationToShop($cancelledOrder);
                header('Location: index.php?act=customer_orders');
                exit;
            } catch (Throwable $e) {
                echo "<h3 style='color:red'>Không thể hủy đơn hàng: "
                    . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</h3>";
                echo "<a href='index.php?act=customer_orders'>Quay lại lịch sử đơn hàng</a>";
                exit;
            }
        case 'complete_customer_order':
            requireCustomer();
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            $rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_INT);
            $comment = trim($_POST['comment'] ?? '');
            if (!$id || $rating === false || $rating < 1 || $rating > 5 || mb_strlen($comment) > 2000) {
                echo "<script>alert('Vui lòng chọn đánh giá từ 1 đến 5 sao và nhập cảm nhận tối đa 2000 ký tự.'); window.location.href='index.php?act=customer_orders';</script>";
                exit;
            }
            try {
                (new controller())->completeCustomerOrder($id, customerId(), $rating, $comment);
                header('Location: index.php?act=order_detail&id=' . $id);
                exit;
            } catch (Throwable $e) {
                echo "<script>alert(" . json_encode($e->getMessage(), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP)
                    . "); window.location.href='index.php?act=order_detail&id=" . (int) $id . "';</script>";
                exit;
            }
        case 'return_customer_order':
            requireCustomer();
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            $reason = trim($_POST['ly_do_hoan'] ?? '');
            if (!$id || $reason === '' || mb_strlen($reason) > 1000) {
                echo "<script>alert('Vui lòng nhập lý do hoàn trả hợp lệ.'); window.location.href='index.php?act=customer_orders';</script>";
                exit;
            }
            try {
                $returnedOrder = (new controller())->returnCustomerOrder($id, customerId(), $reason);
                sendCustomerReturnToShop($returnedOrder);
                header('Location: index.php?act=order_detail&id=' . $id);
                exit;
            } catch (Throwable $e) {
                echo "<script>alert(" . json_encode($e->getMessage(), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP)
                    . "); window.location.href='index.php?act=order_detail&id=" . (int) $id . "';</script>";
                exit;
            }
}
?>
