<?php $cartTotal = $cartTotal ?? 0; ?>

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
            <input type="text" name="buyer_name" required value="<?php echo htmlspecialchars($_POST['buyer_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"><br><br>
            <label>Số điện thoại</label>
            <input type="tel" name="phone" pattern="[0-9+ ()-]{8,20}" required value="<?php echo htmlspecialchars($_POST['phone'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"><br><br>
            <label>Email</label>
            <input type="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"><br><br>
            <label>Địa chỉ giao hàng</label>
            <textarea name="address" rows="4" required><?php echo htmlspecialchars($_POST['address'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea><br><br>
            <label>Ghi chú (không bắt buộc)</label>
            <textarea name="note" rows="3"><?php echo htmlspecialchars($_POST['note'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea><br><br>
            <div style="padding:15px; background:#eff6ff; border-radius:8px; margin-bottom:18px;">
                Tổng thanh toán dự kiến: <strong style="color:var(--primary); font-size:20px;"><?php echo number_format($cartTotal, 0, ',', '.'); ?>đ</strong>
            </div>
            <button type="submit">Xác nhận đặt hàng</button>
        </form>
    </div>
</div>
