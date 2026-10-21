<?php
$product = $product ?? [];
$imageName = basename((string) ($product['image'] ?? ''));
$imageFile = __DIR__ . '/images/' . $imageName;
?>

<div class="container">
    <div class="page-heading">
        <div>
            <h1>Chi tiết sản phẩm</h1>
            <p class="subtitle">Thông tin sản phẩm tại TechZone.</p>
        </div>
        <a class="btn" href="index.php?act=home">Quay lại trang chủ</a>
    </div>

    <div class="card" style="display:grid; grid-template-columns:minmax(250px,380px) 1fr; gap:34px; align-items:start;">
        <div class="product-image" style="border-radius:12px;">
            <?php if ($imageName !== '' && is_file($imageFile)): ?>
                <img src="view/images/<?php echo rawurlencode($imageName); ?>"
                     alt="<?php echo htmlspecialchars($product['Name'], ENT_QUOTES, 'UTF-8'); ?>">
            <?php else: ?>
                <span>Chưa có ảnh</span>
            <?php endif; ?>
        </div>
        <div>
            <p class="subtitle">Mã sản phẩm: #<?php echo (int) $product['id_sp']; ?></p>
            <h2><?php echo htmlspecialchars($product['Name'], ENT_QUOTES, 'UTF-8'); ?></h2>
            <p style="color:var(--primary); font-size:25px; font-weight:800;">
                <?php echo number_format((float) $product['Price'], 0, ',', '.'); ?>đ
            </p>
            <p><strong>Số lượng:</strong> <?php echo htmlspecialchars($product['Mount'], ENT_QUOTES, 'UTF-8'); ?></p>
            <p><strong>Giảm giá:</strong> <?php echo htmlspecialchars($product['Sale'], ENT_QUOTES, 'UTF-8'); ?>%</p>
            <h3>Mô tả sản phẩm</h3>
            <p class="subtitle"><?php echo nl2br(htmlspecialchars($product['Decribe'], ENT_QUOTES, 'UTF-8')); ?></p>
            <form method="post" action="index.php?act=addcart" style="display:flex; gap:10px; align-items:center; margin-top:22px;">
                <input type="hidden" name="id_sp" value="<?php echo (int) $product['id_sp']; ?>">
                <input type="number" name="quantity" min="1" max="<?php echo (int) $product['Mount']; ?>" value="1"
                       style="width:90px;" <?php echo (int) $product['Mount'] < 1 ? 'disabled' : ''; ?>>
                <button type="submit" <?php echo (int) $product['Mount'] < 1 ? 'disabled' : ''; ?>>
                    <?php echo (int) $product['Mount'] < 1 ? 'Hết hàng' : 'Thêm vào giỏ hàng'; ?>
                </button>
            </form>
        </div>
    </div>
</div>

<style>
@media (max-width: 700px) {
    .card[style*="grid-template-columns"] { grid-template-columns: 1fr !important; }
}
</style>
