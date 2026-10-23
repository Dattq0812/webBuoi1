<?php
$orders = $orders ?? [];
$statusLabels = [
    'cho_xac_nhan' => 'Chờ xác nhận',
    'da_xac_nhan' => 'Đã xác nhận',
    'dang_giao' => 'Đang giao',
    'da_giao' => 'Đã giao',
    'yeu_cau_hoan_tra' => 'Chờ duyệt hoàn trả',
    'hoan_tat' => 'Đã hoàn tất',
    'da_huy' => 'Đã huỷ',
    'hoan_tra' => 'Hoàn trả hàng',
];
?>
<div class="container">
    <div class="page-heading">
        <div><h1>Lịch sử đơn hàng</h1><p class="subtitle">Trạng thái được cập nhật đồng bộ theo cửa hàng.</p></div>
    </div>
    <div style="overflow-x:auto;">
        <table>
            <thead><tr><th>Mã đơn</th><th>Sản phẩm</th><th>Tổng tiền</th><th>Ngày đặt</th><th>Trạng thái</th><th>Thao tác</th></tr></thead>
            <tbody>
            <?php if (!$orders): ?>
                <tr><td colspan="6" class="empty">Bạn chưa có đơn hàng nào.</td></tr>
            <?php else: foreach ($orders as $order): ?>
                <?php $status = $order['trang_thai'] ?? 'cho_xac_nhan'; ?>
                <tr>
                    <td><?php echo htmlspecialchars($order['ma_donhang'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($order['san_pham'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo number_format((float) $order['tong_tien'], 0, ',', '.'); ?>đ</td>
                    <td><?php echo htmlspecialchars($order['ngay_dat'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><strong><?php echo htmlspecialchars($statusLabels[$status] ?? 'Không xác định', ENT_QUOTES, 'UTF-8'); ?></strong>
                        <?php if ($status === 'da_huy' && !empty($order['ly_do_huy'])): ?><br><small>Lý do: <?php echo htmlspecialchars($order['ly_do_huy'], ENT_QUOTES, 'UTF-8'); ?></small><?php endif; ?>
                    </td>
                    <td>
                        <a class="btn btn-edit" href="index.php?act=order_detail&amp;id=<?php echo (int) $order['id']; ?>">
                            Xem chi tiết
                        </a>
                        <?php if ($status === 'da_giao'): ?>
                            <a class="btn btn-save" href="index.php?act=order_detail&amp;id=<?php echo (int) $order['id']; ?>">
                                Hoàn tất / Hoàn trả
                            </a>
                        <?php elseif ($status === 'cho_xac_nhan'): ?>
                            <button type="button" class="btn-delete" style="border:0; background:none;" onclick="document.getElementById('customer-cancel-<?php echo (int) $order['id']; ?>').hidden = false; this.hidden = true;">Huỷ đơn</button>
                            <form id="customer-cancel-<?php echo (int) $order['id']; ?>" method="post" action="index.php?act=cancel_customer_order" hidden>
                                <input type="hidden" name="id" value="<?php echo (int) $order['id']; ?>">
                                <textarea name="ly_do_huy" rows="2" maxlength="1000" required placeholder="Nhập lý do huỷ"></textarea>
                                <button type="submit" class="btn-delete" onclick="return confirm('Xác nhận huỷ đơn hàng?');">Gửi yêu cầu huỷ</button>
                            </form>
                        <?php else: ?><span class="subtitle">Không thể huỷ</span><?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
