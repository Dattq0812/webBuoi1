<?php
$product = $product ?? [];
$imageName = basename((string) ($product['image'] ?? ''));
$imageFile = __DIR__ . '/images/' . $imageName;
$originalPrice = (float) ($product['Price'] ?? 0);
$salePercent = max(0, min(100, (float) ($product['Sale'] ?? 0)));
$salePrice = $originalPrice * (1 - $salePercent / 100);
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
            <div class="detail-price">
                <?php if ($salePercent > 0): ?>
                    <span class="detail-price-old"><?php echo number_format($originalPrice, 0, ',', '.'); ?>đ</span>
                    <strong class="detail-price-new"><?php echo number_format($salePrice, 0, ',', '.'); ?>đ</strong>
                <?php else: ?>
                    <strong class="detail-price-new"><?php echo number_format($originalPrice, 0, ',', '.'); ?>đ</strong>
                <?php endif; ?>
            </div>
            <p><strong>Số lượng:</strong> <?php echo htmlspecialchars($product['Mount'], ENT_QUOTES, 'UTF-8'); ?></p>
            <p><strong>Giảm giá:</strong> <?php echo number_format($salePercent, 0); ?>%</p>
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
.detail-price { display:flex; flex-direction:column; gap:2px; margin:10px 0 16px; }
.detail-price-old { color:#94a3b8; font-size:17px; text-decoration:line-through; }
.detail-price-new { color:var(--primary); font-size:27px; font-weight:800; }
@media (max-width: 700px) {
    .card[style*="grid-template-columns"] { grid-template-columns: 1fr !important; }
}
</style>
