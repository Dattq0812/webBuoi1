<?php
$customers = $customers ?? [];
?>
<div class="container">
    <div class="page-heading">
        <div><h1>Khách hàng thân thiết</h1><p class="subtitle">Hạng và ưu đãi được tính theo tổng chi tiêu hợp lệ, không gồm đơn huỷ hoặc hoàn trả.</p></div>
    </div>
    <div style="overflow-x:auto;">
        <table>
            <thead><tr><th>Khách hàng</th><th>Liên hệ</th><th>Số đơn</th><th>Tổng chi tiêu</th><th>Hạng</th><th>Ưu đãi</th></tr></thead>
            <tbody>
            <?php if (!$customers): ?>
                <tr><td colspan="6" class="empty">Chưa có khách hàng.</td></tr>
            <?php else: foreach ($customers as $customer): ?>
                <?php $tier = $customer['tier']; ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($customer['ho_ten'], ENT_QUOTES, 'UTF-8'); ?></strong></td>
                    <td><?php echo htmlspecialchars($customer['email'] ?: ($customer['sdt'] ?: 'Chưa cập nhật'), ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo (int) $customer['completed_orders']; ?></td>
                    <td><?php echo number_format((float) $customer['total_spending'], 0, ',', '.'); ?>đ</td>
                    <td><span class="loyalty-badge loyalty-<?php echo htmlspecialchars($tier['code'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($tier['label'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                    <td><strong><?php echo (int) $tier['discount']; ?>%</strong></td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
