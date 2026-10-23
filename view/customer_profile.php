<?php
$customerLoyalty = $customerLoyalty ?? [];
$customer = $customerLoyalty['customer'] ?? [];
$tier = $customerLoyalty['tier'] ?? ['label' => 'Standard', 'discount' => 0];
$totalSpending = (float) ($customerLoyalty['total_spending'] ?? 0);
$tiers = [
    ['label' => 'Silver', 'threshold' => 50000000, 'discount' => 3],
    ['label' => 'Golden', 'threshold' => 150000000, 'discount' => 5],
    ['label' => 'Platinum', 'threshold' => 500000000, 'discount' => 10],
    ['label' => 'Diamond', 'threshold' => 1000000000, 'discount' => 15],
];
$nextTier = null;
foreach ($tiers as $candidate) {
    if ($totalSpending <= $candidate['threshold']) {
        $nextTier = $candidate;
        break;
    }
}
?>
<div class="container">
    <div class="page-heading">
        <div>
            <h1>Thông tin tài khoản</h1>
            <p class="subtitle">Theo dõi hạng thành viên và quyền lợi mua sắm của bạn.</p>
        </div>
        <a class="btn" href="index.php?act=customer_orders">Đơn hàng của tôi</a>
    </div>
    <div class="customer-profile-grid">
        <section class="card">
            <h2>Thông tin cá nhân</h2>
            <p><strong>Họ tên:</strong> <?php echo htmlspecialchars($customer['ho_ten'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($customer['email'] ?? 'Chưa cập nhật', ENT_QUOTES, 'UTF-8'); ?></p>
            <p><strong>Số điện thoại:</strong> <?php echo htmlspecialchars($customer['sdt'] ?? 'Chưa cập nhật', ENT_QUOTES, 'UTF-8'); ?></p>
            <p><strong>Số đơn hợp lệ:</strong> <?php echo (int) ($customerLoyalty['completed_orders'] ?? 0); ?></p>
        </section>
        <section class="card loyalty-card">
            <span class="loyalty-badge loyalty-<?php echo htmlspecialchars($tier['code'], ENT_QUOTES, 'UTF-8'); ?>">
                <?php echo htmlspecialchars($tier['label'], ENT_QUOTES, 'UTF-8'); ?>
            </span>
            <h2>Hạng <?php echo htmlspecialchars($tier['label'], ENT_QUOTES, 'UTF-8'); ?></h2>
            <p>Ưu đãi thành viên: <strong><?php echo (int) $tier['discount']; ?>% trên tổng bill</strong></p>
            <p class="loyalty-total">Tổng chi tiêu: <strong><?php echo number_format($totalSpending, 0, ',', '.'); ?>đ</strong></p>
            <?php if ($nextTier): ?>
                <p class="subtitle">Chi tiêu thêm <?php echo number_format(max(0, $nextTier['threshold'] - $totalSpending), 0, ',', '.'); ?>đ
                    để đạt hạng <?php echo htmlspecialchars($nextTier['label'], ENT_QUOTES, 'UTF-8'); ?> (<?php echo (int) $nextTier['discount']; ?>%).</p>
            <?php else: ?>
                <p class="subtitle">Bạn đang ở hạng thành viên cao nhất.</p>
            <?php endif; ?>
        </section>
    </div>
    <section class="card" style="margin-top:18px;">
        <h2>Quyền lợi theo hạng</h2>
        <div class="loyalty-tier-list">
            <?php foreach ($tiers as $item): ?>
                <div><strong><?php echo $item['label']; ?></strong><span>Trên <?php echo number_format($item['threshold'], 0, ',', '.'); ?>đ</span><b><?php echo $item['discount']; ?>%</b></div>
            <?php endforeach; ?>
        </div>
    </section>
</div>
