<div class="container">
    <section class="card" style="padding:0; overflow:hidden; background:linear-gradient(120deg,#0f172a,#1d4ed8); color:#fff;">
        <div style="padding:58px 52px; max-width:700px;">
            <p style="color:#bfdbfe; font-weight:700; letter-spacing:1px;">CÔNG NGHỆ CHO CUỘC SỐNG</p>
            <h1 style="font-size:42px; margin-bottom:14px;">Nâng tầm trải nghiệm số của bạn</h1>
            <p style="color:#dbeafe; font-size:17px;">Khám phá laptop, smartphone, thiết bị thông minh và phụ kiện chính hãng với mức giá tốt mỗi ngày.</p>
            <a class="btn" style="background:#fff; color:#1d4ed8; margin-top:12px;" href="index.php?act=hienthiSP">Xem sản phẩm</a>
        </div>
    </section>

    <?php if (isManager()): ?>
    <section style="margin-top:30px;">
        <div class="page-heading">
            <div><h2>Trung tâm quản lý</h2><p class="subtitle">Truy cập nhanh các chức năng vận hành cửa hàng.</p></div>
        </div>
        <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:18px;">
            <a class="card" href="index.php?act=hienthiDM" style="text-decoration:none;">
                <div style="font-size:30px;">▦</div><h2>Danh mục</h2><p class="subtitle">Thêm, sửa và xóa nhóm sản phẩm.</p>
            </a>
            <a class="card" href="index.php?act=hienthiSP" style="text-decoration:none;">
                <div style="font-size:30px;">▣</div><h2>Sản phẩm</h2><p class="subtitle">Quản lý kho và thông tin sản phẩm.</p>
            </a>
            <a class="card" href="#" style="text-decoration:none;">
                <div style="font-size:30px;">◷</div><h2>Đơn hàng</h2><p class="subtitle">Theo dõi và xử lý đơn hàng.</p>
            </a>
            <a class="card" href="#" style="text-decoration:none;">
                <div style="font-size:30px;">☎</div><h2>Hỗ trợ</h2><p class="subtitle">Kết nối với khách hàng nhanh chóng.</p>
            </a>
        </div>
    </section>
    <?php endif; ?>

    <section style="margin-top:38px;">
        <div class="page-heading">
            <div>
                <h2>Sản phẩm nổi bật</h2>
                <p class="subtitle">Khám phá các sản phẩm đang có tại TechZone.</p>
            </div>
            <a class="btn" href="index.php?act=hienthiSP">Xem tất cả</a>
        </div>
        <?php if (!empty($danhsach)): ?>
            <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(210px,1fr)); gap:18px;">
                <?php foreach ($danhsach as $product): ?>
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
        <?php else: ?>
            <div class="card empty">Hiện chưa có sản phẩm nào để trưng bày.</div>
        <?php endif; ?>
    </section>

    <section style="display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:18px; margin-top:30px;">
        <div class="card"><h2>Chính hãng</h2><p class="subtitle">Sản phẩm có nguồn gốc rõ ràng, bảo hành đầy đủ.</p></div>
        <div class="card"><h2>Giao hàng nhanh</h2><p class="subtitle">Đóng gói an toàn, giao tận nơi trên toàn quốc.</p></div>
        <div class="card"><h2>Hỗ trợ tận tâm</h2><p class="subtitle">Tư vấn lựa chọn thiết bị phù hợp nhu cầu.</p></div>
    </section>
</div>
