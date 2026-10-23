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
$nextStatuses = [
    'cho_xac_nhan' => 'da_xac_nhan',
    'da_xac_nhan' => 'dang_giao',
    'dang_giao' => 'da_giao',
    'da_giao' => 'hoan_tra',
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
                        <?php $currentStatus = $order['trang_thai'] ?? 'cho_xac_nhan'; ?>
                        <strong><?php echo $statusLabels[$currentStatus] ?? 'Không xác định'; ?></strong>
                        <?php if (isset($nextStatuses[$currentStatus])): ?>
                            <form method="post" action="index.php?act=update_order_status" style="margin-top:8px;">
                                <input type="hidden" name="id" value="<?php echo (int) $order['id']; ?>">
                                <input type="hidden" name="trang_thai_hien_tai" value="<?php echo $currentStatus; ?>">
                                <input type="hidden" name="trang_thai_tiep_theo" value="<?php echo $nextStatuses[$currentStatus]; ?>">
                                <button type="submit" class="btn-save"
                                        onclick="return confirm('Xác nhận chuyển sang trạng thái <?php echo htmlspecialchars($statusLabels[$nextStatuses[$currentStatus]], ENT_QUOTES, 'UTF-8'); ?>?');">
                                    Xác nhận: <?php echo $statusLabels[$nextStatuses[$currentStatus]]; ?>
                                </button>
                            </form>
                        <?php endif; ?>
                        <?php if (in_array($currentStatus, ['cho_xac_nhan', 'da_xac_nhan'], true)): ?>
                            <button type="button" class="btn-delete" style="border:0; background:none; margin-top:8px;"
                                    onclick="document.getElementById('cancel-<?php echo (int) $order['id']; ?>').hidden = false; this.hidden = true;">
                                Hủy đơn hàng
                            </button>
                            <form id="cancel-<?php echo (int) $order['id']; ?>" method="post"
                                  action="index.php?act=cancel_order" hidden style="margin-top:8px;">
                                <input type="hidden" name="id" value="<?php echo (int) $order['id']; ?>">
                                <textarea name="ly_do_huy" rows="2" maxlength="1000" required placeholder="Nhập lý do hủy"></textarea>
                                <button type="submit" class="btn-delete"
                                        onclick="return confirm('Xác nhận hủy đơn hàng và gửi thông báo cho khách?');">
                                    Xác nhận hủy
                                </button>
                            </form>
                        <?php elseif (!isset($nextStatuses[$currentStatus])): ?>
                            <small class="subtitle">Đã hoàn tất</small>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
