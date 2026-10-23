<?php
$orders = $orders ?? [];
$statusLabels = [
    'cho_xac_nhan' => 'Chờ xác nhận',
    'da_xac_nhan' => 'Đã xác nhận',
    'dang_giao' => 'Đang giao',
    'da_giao' => 'Đã giao',
    'da_huy' => 'Đã huỷ',
    'hoan_tra' => 'Hoàn trả hàng',
];
?>
<div class="container">
    <div class="page-heading">
        <div><h1>Danh sách đơn hàng</h1><p class="subtitle">Theo dõi và cập nhật trạng thái đơn hàng.</p></div>
    </div>
    <div style="overflow-x:auto;">
        <table>
            <thead><tr><th>Mã đơn</th><th>Khách hàng</th><th>Sản phẩm</th><th>Tổng tiền</th><th>Ngày đặt</th><th>Trạng thái</th></tr></thead>
            <tbody>
            <?php if (!$orders): ?>
                <tr><td colspan="6" class="empty">Chưa có đơn hàng nào.</td></tr>
            <?php else: foreach ($orders as $order): ?>
                <tr>
                    <td><?php echo htmlspecialchars($order['ma_donhang'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($order['ten_khachhang'], ENT_QUOTES, 'UTF-8'); ?><br><small><?php echo htmlspecialchars($order['sdt'], ENT_QUOTES, 'UTF-8'); ?></small></td>
                    <td><?php echo htmlspecialchars($order['san_pham'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo number_format((float) $order['tong_tien'], 0, ',', '.'); ?>đ</td>
                    <td><?php echo htmlspecialchars($order['ngay_dat'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>
                        <form method="post" action="index.php?act=update_order_status">
                            <input type="hidden" name="id" value="<?php echo (int) $order['id']; ?>">
                            <select name="trang_thai" onchange="this.form.submit()">
                                <?php foreach ($statusLabels as $value => $label): ?>
                                    <option value="<?php echo $value; ?>" <?php echo ($order['trang_thai'] ?? 'cho_xac_nhan') === $value ? 'selected' : ''; ?>><?php echo $label; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
