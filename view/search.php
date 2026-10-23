<?php
$keyword = $keyword ?? '';
$searchResults = $searchResults ?? [];
?>
<div class="container">
    <div class="page-heading">
        <div>
            <h1>Tìm kiếm sản phẩm</h1>
            <p class="subtitle">
                <?php if ($keyword !== ''): ?>
                    Kết quả cho: <strong><?php echo htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8'); ?></strong>
                <?php else: ?>
                    Nhập từ khoá để tìm sản phẩm.
                <?php endif; ?>
            </p>
        </div>
    </div>
    <?php if ($keyword !== '' && $searchResults): ?>
        <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(210px,1fr)); gap:18px;">
            <?php foreach ($searchResults as $product): ?>
                <?php
                $imageName = basename((string) ($product['image'] ?? ''));
                $imageFile = __DIR__ . '/images/' . $imageName;
                ?>
                <a class="product-card" href="index.php?act=chitietsp&amp;idsp=<?php echo (int) $product['id_sp']; ?>">
                    <div class="product-image">
                        <?php if ($imageName !== '' && is_file($imageFile)): ?>
                            <img src="view/images/<?php echo rawurlencode($imageName); ?>"
                                 alt="<?php echo htmlspecialchars($product['Name'], ENT_QUOTES, 'UTF-8'); ?>">
                        <?php else: ?>
                            <span>Chưa có ảnh</span>
                        <?php endif; ?>
                    </div>
                    <div class="product-card-body">
                        <h3><?php echo htmlspecialchars($product['Name'], ENT_QUOTES, 'UTF-8'); ?></h3>
                        <strong><?php echo number_format((float) $product['Price'], 0, ',', '.'); ?>đ</strong>
                        <span class="product-link">Xem chi tiết →</span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php elseif ($keyword !== ''): ?>
        <div class="card empty">Không tìm thấy sản phẩm phù hợp.</div>
    <?php endif; ?>
</div>
