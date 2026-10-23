<div class="container">
    <div class="card" style="max-width:680px; margin:auto; text-align:center;">
        <h1 style="color:var(--success);">Đặt hàng thành công</h1>
        <p>Cảm ơn <strong><?php echo htmlspecialchars($buyerName, ENT_QUOTES, 'UTF-8'); ?></strong>. TechZone sẽ liên hệ qua số điện thoại <?php echo htmlspecialchars($phone, ENT_QUOTES, 'UTF-8'); ?> để xác nhận đơn hàng.</p>
        <p>Mã đơn hàng: <strong><?php echo htmlspecialchars($orderCode, ENT_QUOTES, 'UTF-8'); ?></strong></p>
        <p>Phương thức thanh toán:
            <strong><?php echo $paymentMethod === 'bank_transfer' ? 'Chuyển khoản ngân hàng' : 'Thanh toán khi nhận hàng (COD)'; ?></strong>
        </p>
        <?php if ($voucherCode !== ''): ?>
            <p>Voucher đã dùng: <strong><?php echo htmlspecialchars($voucherCode, ENT_QUOTES, 'UTF-8'); ?></strong>
                - giảm <?php echo number_format($discount, 0, ',', '.'); ?>đ
            </p>
        <?php endif; ?>
        <?php if ($emailSent): ?>
            <p>Email xác nhận đã được gửi đến <?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>.</p>
        <?php else: ?>
            <p style="color:#b45309;">Đơn hàng đã được lưu, nhưng email chưa gửi được. Vui lòng liên hệ cửa hàng để được hỗ trợ.</p>
        <?php endif; ?>
        <a class="btn" href="index.php?act=home">Về trang chủ</a>
    </div>
</div>
