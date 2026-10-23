<?php
$cartTotal = $cartTotal ?? 0;
$voucherOptions = $voucherOptions ?? [];
$customerLoyalty = $customerLoyalty ?? ['tier' => ['label' => 'Standard', 'discount' => 0], 'total_spending' => 0];
$loyaltyTier = $customerLoyalty['tier'];
?>

<div class="container">
    <div class="page-heading">
        <div>
            <h1>Thông tin nhận hàng</h1>
            <p class="subtitle">Vui lòng điền thông tin để TechZone liên hệ và giao hàng.</p>
        </div>
        <a class="btn" href="index.php?act=cart">Quay lại giỏ hàng</a>
    </div>
    <div class="card" style="max-width:760px;">
        <form method="post" action="index.php?act=placeorder">
            <label>Họ và tên</label>
            <input type="text" name="buyer_name" required value="<?php echo htmlspecialchars($_POST['buyer_name'] ?? ($_SESSION['customer_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"><br><br>
            <label>Số điện thoại</label>
            <input type="tel" name="phone" pattern="[0-9+ ()-]{8,20}" required value="<?php echo htmlspecialchars($_POST['phone'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"><br><br>
            <label>Email</label>
            <input type="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ($_SESSION['customer_email'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"><br><br>
            <label>Địa chỉ giao hàng</label>
            <textarea name="address" rows="4" required><?php echo htmlspecialchars($_POST['address'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea><br><br>
            <label>Ghi chú (không bắt buộc)</label>
            <textarea name="note" rows="3"><?php echo htmlspecialchars($_POST['note'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea><br><br>
            <label>Phương thức thanh toán</label>
            <select name="payment_method" required>
                <option value="cod" <?php echo ($_POST['payment_method'] ?? '') === 'cod' ? 'selected' : ''; ?>>Thanh toán khi nhận hàng (COD)</option>
                <option value="bank_transfer" <?php echo ($_POST['payment_method'] ?? '') === 'bank_transfer' ? 'selected' : ''; ?>>Chuyển khoản ngân hàng</option>
            </select><br><br>
            <label>Mã voucher</label>
            <input type="text" name="voucher_code" id="voucher-code" maxlength="30"
                   placeholder="Ví dụ: TECH10"
                   value="<?php echo htmlspecialchars($_POST['voucher_code'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            <small class="subtitle">TECH10 giảm 10%; TECH50K giảm 50.000đ.</small><br><br>
            <div class="card loyalty-summary" style="padding:15px; background:#fffbeb; border-color:#fcd34d;">
                <strong>Hạng thành viên: <?php echo htmlspecialchars($loyaltyTier['label'], ENT_QUOTES, 'UTF-8'); ?></strong>
                <span> - Ưu đãi thành viên <?php echo (int) $loyaltyTier['discount']; ?>% trên đơn này.</span>
                <small class="subtitle" style="display:block; margin-top:4px;">
                    Tổng chi tiêu hợp lệ: <?php echo number_format((float) $customerLoyalty['total_spending'], 0, ',', '.'); ?>đ
                </small>
            </div><br>
            <div class="card" style="padding:15px; background:#eff6ff;">
                <div>Tạm tính: <strong><?php echo number_format($cartTotal, 0, ',', '.'); ?>đ</strong></div>
                <div>Giảm voucher: <strong id="discount-preview">0đ</strong></div>
                <div style="margin-top:6px;">Tổng thanh toán: <strong id="total-preview" style="color:var(--primary); font-size:20px;"><?php echo number_format($cartTotal, 0, ',', '.'); ?>đ</strong></div>
            </div><br>
            <button type="submit">Xác nhận đặt hàng</button>
        </form>
    </div>
</div>
<script>
const voucherPrices = <?php echo json_encode($voucherOptions, JSON_UNESCAPED_UNICODE); ?>;
const subtotal = <?php echo json_encode((float) $cartTotal); ?>;
const voucherInput = document.getElementById('voucher-code');
const discountPreview = document.getElementById('discount-preview');
const totalPreview = document.getElementById('total-preview');
function formatPrice(value) {
    return new Intl.NumberFormat('vi-VN').format(value) + 'đ';
}
function previewVoucher() {
    const voucher = voucherPrices[voucherInput.value.trim().toUpperCase()];
    let discount = 0;
    if (voucher) {
        discount = voucher.type === 'percent' ? subtotal * voucher.value / 100 : voucher.value;
        discount = Math.min(subtotal, discount);
    }
    discount = Math.min(subtotal, discount + subtotal * <?php echo (int) $loyaltyTier['discount']; ?> / 100);
    discountPreview.textContent = formatPrice(discount);
    totalPreview.textContent = formatPrice(subtotal - discount);
}
voucherInput.addEventListener('input', previewVoucher);
previewVoucher();
</script>
