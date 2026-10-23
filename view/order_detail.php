<?php
$order = $orderDetail;
$statusLabels = [
    'cho_xac_nhan' => 'Chờ xác nhận',
    'da_xac_nhan' => 'Đã xác nhận',
    'dang_giao' => 'Đang giao',
    'da_giao' => 'Đã giao',
    'da_huy' => 'Đã huỷ',
    'hoan_tra' => 'Hoàn trả hàng',
];
$paymentLabels = [
    'cod' => 'Thanh toán khi nhận hàng (COD)',
    'bank_transfer' => 'Chuyển khoản ngân hàng',
];
$nextStatuses = [
    'cho_xac_nhan' => 'da_xac_nhan',
    'da_xac_nhan' => 'dang_giao',
    'dang_giao' => 'da_giao',
    'da_giao' => 'hoan_tra',
];
$currentStatus = $order['trang_thai'] ?? 'cho_xac_nhan';
?>
<div class="container">
    <div class="page-heading">
        <div>
            <h1>Chi tiết đơn hàng</h1>
            <p class="subtitle">Mã đơn: <?php echo htmlspecialchars($order['ma_donhang'], ENT_QUOTES, 'UTF-8'); ?></p>
        </div>
        <a class="btn" href="index.php?act=<?php echo isManager() ? 'orders' : 'customer_orders'; ?>">Quay lại</a>
    </div>

    <div class="card" style="margin-bottom:20px;">
        <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:16px;">
            <div><strong>Khách hàng</strong><br><?php echo htmlspecialchars($order['ten_khachhang'], ENT_QUOTES, 'UTF-8'); ?></div>
            <div><strong>Số điện thoại</strong><br><?php echo htmlspecialchars($order['sdt'], ENT_QUOTES, 'UTF-8'); ?></div>
            <div><strong>Email</strong><br><?php echo htmlspecialchars($order['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?></div>
            <div><strong>Ngày đặt</strong><br><?php echo htmlspecialchars($order['ngay_dat'], ENT_QUOTES, 'UTF-8'); ?></div>
            <div>
                <strong>Trạng thái</strong><br>
                <?php echo htmlspecialchars($statusLabels[$currentStatus] ?? 'Không xác định', ENT_QUOTES, 'UTF-8'); ?>
                <?php if (isManager() && isset($nextStatuses[$currentStatus])): ?>
                    <form method="post" action="index.php?act=update_order_status" style="margin-top:10px;">
                        <input type="hidden" name="id" value="<?php echo (int) $order['id']; ?>">
                        <input type="hidden" name="trang_thai_hien_tai" value="<?php echo htmlspecialchars($currentStatus, ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="trang_thai_tiep_theo" value="<?php echo htmlspecialchars($nextStatuses[$currentStatus], ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="return_to" value="order_detail">
                        <button type="submit" class="btn-save"
                                onclick="return confirm('Xác nhận chuyển sang trạng thái <?php echo htmlspecialchars($statusLabels[$nextStatuses[$currentStatus]], ENT_QUOTES, 'UTF-8'); ?>?');">
                            Cập nhật: <?php echo htmlspecialchars($statusLabels[$nextStatuses[$currentStatus]], ENT_QUOTES, 'UTF-8'); ?>
                        </button>
                    </form>
                <?php elseif (isManager()): ?>
                    <br><small class="subtitle">Đã hoàn tất</small>
                <?php endif; ?>
            </div>
            <div><strong>Thanh toán</strong><br><?php echo htmlspecialchars($paymentLabels[$order['phuong_thuc_thanh_toan']] ?? $order['phuong_thuc_thanh_toan'], ENT_QUOTES, 'UTF-8'); ?></div>
            <div style="grid-column:1 / -1;"><strong>Địa chỉ giao hàng</strong><br><?php echo nl2br(htmlspecialchars($order['dia_chi'], ENT_QUOTES, 'UTF-8')); ?></div>
            <?php if (!empty($order['ghi_chu'])): ?>
                <div style="grid-column:1 / -1;"><strong>Ghi chú</strong><br><?php echo nl2br(htmlspecialchars($order['ghi_chu'], ENT_QUOTES, 'UTF-8')); ?></div>
            <?php endif; ?>
            <?php if (!empty($order['ly_do_huy'])): ?>
                <div style="grid-column:1 / -1;"><strong>Lý do huỷ</strong><br><?php echo nl2br(htmlspecialchars($order['ly_do_huy'], ENT_QUOTES, 'UTF-8')); ?></div>
            <?php endif; ?>
        </div>
    </div>

    <div style="overflow-x:auto;">
        <table>
            <thead><tr><th>Sản phẩm</th><th>Đơn giá</th><th>Số lượng</th><th>Thành tiền</th></tr></thead>
            <tbody>
            <?php foreach ($order['items'] as $item): ?>
                <tr>
                    <td>
                        <?php $imageName = basename((string) ($item['image'] ?? '')); ?>
                        <?php if ($imageName !== '' && is_file(__DIR__ . '/images/' . $imageName)): ?>
                            <img src="view/images/<?php echo rawurlencode($imageName); ?>" alt="" width="60" height="70" style="object-fit:cover; vertical-align:middle; margin-right:8px; border-radius:6px;">
                        <?php endif; ?>
                        <?php echo htmlspecialchars($item['ten_sanpham'], ENT_QUOTES, 'UTF-8'); ?>
                    </td>
                    <td><?php echo number_format((float) $item['don_gia'], 0, ',', '.'); ?>đ</td>
                    <td><?php echo (int) $item['so_luong']; ?></td>
                    <td><?php echo number_format((float) $item['thanh_tien'], 0, ',', '.'); ?>đ</td>
                </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr><td colspan="3" style="text-align:right;"><strong>Giảm giá</strong></td><td><?php echo number_format((float) $order['tien_giam'], 0, ',', '.'); ?>đ</td></tr>
                <tr><td colspan="3" style="text-align:right;"><strong>Tổng tiền</strong></td><td><strong><?php echo number_format((float) $order['tong_tien'], 0, ',', '.'); ?>đ</strong></td></tr>
            </tfoot>
        </table>
    </div>
</div>
