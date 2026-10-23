<?php
$bannerProducts = array_values(array_filter($danhsach ?? [], static function (array $product): bool {
    $imageName = basename((string) ($product['image'] ?? ''));
    return $imageName !== '' && is_file(__DIR__ . '/images/' . $imageName);
}));
?>

<div class="container">
    <section class="card home-banner">
        <div class="home-banner-content">
            <p style="color:#bfdbfe; font-weight:700; letter-spacing:1px;">CÔNG NGHỆ CHO CUỘC SỐNG</p>
            <h1 style="font-size:42px; margin-bottom:14px;">Nâng tầm trải nghiệm số của bạn</h1>
            <p style="color:#dbeafe; font-size:17px;">Khám phá laptop, smartphone, thiết bị thông minh và phụ kiện chính hãng với mức giá tốt mỗi ngày.</p>
            <a class="btn" style="background:#fff; color:#1d4ed8; margin-top:12px;" href="index.php?act=hienthiSP">Xem sản phẩm</a>
        </div>
        <?php if ($bannerProducts): ?>
            <div class="product-slider" aria-label="Sản phẩm nổi bật">
                <div class="product-slider-track">
                    <?php foreach ($bannerProducts as $index => $product): ?>
                        <?php $imageName = basename((string) $product['image']); ?>
                        <a class="product-slide<?php echo $index === 0 ? ' is-active' : ''; ?>"
                           href="index.php?act=chitietsp&amp;idsp=<?php echo (int) $product['id_sp']; ?>">
                            <img src="view/images/<?php echo rawurlencode($imageName); ?>"
                                 alt="<?php echo htmlspecialchars($product['Name'], ENT_QUOTES, 'UTF-8'); ?>">
                            <span><?php echo htmlspecialchars($product['Name'], ENT_QUOTES, 'UTF-8'); ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
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
            <a class="card" href="index.php?act=orders" style="text-decoration:none;">
                <div style="font-size:30px;">◷</div><h2>Đơn hàng</h2><p class="subtitle">Theo dõi và xử lý đơn hàng.</p>
            </a>
            <a class="card" href="#" style="text-decoration:none;">
                <div style="font-size:30px;">☎</div><h2>Hỗ trợ</h2><p class="subtitle">Kết nối với khách hàng nhanh chóng.</p>
            </a>
        </div>
    </section>
    <section style="margin-top:30px;">
        <div class="page-heading">
            <div>
                <h2>Thống kê doanh số</h2>
                <p class="subtitle">Doanh thu và số đơn, không bao gồm đơn đã huỷ.</p>
            </div>
        </div>
        <?php
        $revenueStatistics = $revenueStatistics ?? [];
        $salesPeriods = [
            'week' => ['label' => 'Tuần này', 'revenue' => $revenueStatistics['week_revenue'] ?? 0, 'orders' => $revenueStatistics['week_orders'] ?? 0],
            'month' => ['label' => 'Tháng này', 'revenue' => $revenueStatistics['month_revenue'] ?? 0, 'orders' => $revenueStatistics['month_orders'] ?? 0],
            'year' => ['label' => 'Năm nay', 'revenue' => $revenueStatistics['year_revenue'] ?? 0, 'orders' => $revenueStatistics['year_orders'] ?? 0],
        ];
        ?>
        <div style="display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:18px;">
            <?php foreach ($salesPeriods as $period): ?>
                <div class="card">
                    <h3><?php echo $period['label']; ?></h3>
                    <strong style="display:block; margin:12px 0 6px; color:var(--primary); font-size:24px;">
                        <?php echo number_format((float) $period['revenue'], 0, ',', '.'); ?>đ
                    </strong>
                    <p class="subtitle"><?php echo (int) $period['orders']; ?> đơn hàng</p>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <?php
    $renderProducts = static function (array $products, string $gridClass = ''): void {
        if (empty($products)) {
            echo '<div class="card empty">Hiện chưa có sản phẩm phù hợp.</div>';
            return;
        }
        $classAttribute = $gridClass !== ''
            ? ' class="' . htmlspecialchars($gridClass, ENT_QUOTES, 'UTF-8') . '"'
            : '';
        echo '<div' . $classAttribute . ' style="display:grid; grid-template-columns:repeat(auto-fit,minmax(210px,1fr)); gap:18px;">';
        foreach ($products as $product):
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
        <?php endforeach;
        echo '</div>';
    };
    ?>

    <section style="margin-top:38px;">
        <div class="page-heading">
            <div>
                <h2>Sản phẩm bán chạy</h2>
                <p class="subtitle">Những sản phẩm được khách hàng lựa chọn nhiều nhất.</p>
            </div>
        </div>
        <?php $renderProducts($sanPhamBanChay ?? [], 'recommendations-grid'); ?>
    </section>

    <section style="margin-top:38px;">
        <div class="page-heading">
            <div>
                <h2>Mới lên kệ</h2>
                <p class="subtitle">Sản phẩm mới nhập được ưu tiên giới thiệu.</p>
            </div>
        </div>
        <?php $renderProducts($sanPhamMoi ?? [], 'recommendations-grid'); ?>
    </section>

    <section style="margin-top:38px;">
        <div class="page-heading">
            <div>
                <h2>Sản phẩm nổi bật</h2>
                <p class="subtitle">Khám phá các sản phẩm đang có tại TechZone.</p>
            </div>
            <a class="btn" href="index.php?act=hienthiSP">Xem tất cả</a>
        </div>
        <?php $renderProducts($danhsach ?? []); ?>
    </section>

    <section style="display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:18px; margin-top:30px;">
        <div class="card"><h2>Chính hãng</h2><p class="subtitle">Sản phẩm có nguồn gốc rõ ràng, bảo hành đầy đủ.</p></div>
        <div class="card"><h2>Giao hàng nhanh</h2><p class="subtitle">Đóng gói an toàn, giao tận nơi trên toàn quốc.</p></div>
        <div class="card"><h2>Hỗ trợ tận tâm</h2><p class="subtitle">Tư vấn lựa chọn thiết bị phù hợp nhu cầu.</p></div>
    </section>

    <style>
        .home-banner {
            display: grid;
            grid-template-columns: minmax(0, 1.15fr) minmax(260px, .85fr);
            min-height: 330px;
            padding: 0;
            overflow: hidden;
            color: #fff;
            background: linear-gradient(120deg,#0f172a,#1d4ed8);
        }
        .home-banner-content {
            align-self: center;
            padding: 58px 52px;
        }
        .product-slider {
            position: relative;
            min-height: 330px;
            overflow: hidden;
            background: linear-gradient(145deg, #2563eb, #0f172a);
        }
        .product-slider-track {
            position: relative;
            width: 100%;
            height: 100%;
            min-height: 330px;
        }
        .product-slide {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 28px;
            opacity: 0;
            transform: translateX(80px);
            transition: opacity .8s ease, transform .8s ease;
            pointer-events: none;
            text-decoration: none;
        }
        .product-slide.is-active {
            opacity: 1;
            transform: translateX(0);
            pointer-events: auto;
        }
        .product-slide img {
            width: 100%;
            height: 255px;
            object-fit: contain;
            filter: drop-shadow(0 18px 18px #02061766);
        }
        .product-slide span {
            position: absolute;
            right: 24px;
            bottom: 18px;
            left: 24px;
            overflow: hidden;
            color: #fff;
            font-weight: 700;
            text-align: center;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        @media (max-width: 700px) {
            .home-banner {
                grid-template-columns: 1fr;
            }
            .home-banner-content {
                padding: 34px 26px 24px;
            }
            .home-banner-content h1 {
                font-size: 30px !important;
            }
            .product-slider,
            .product-slider-track {
                min-height: 250px;
            }
            .product-slide img {
                height: 190px;
            }
        }
        .recommendations-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
        }
        @media (max-width: 900px) {
            .recommendations-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
            }
        }
        @media (max-width: 650px) {
            .recommendations-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            }
        }
    </style>
</div>

<?php if (count($bannerProducts) > 1): ?>
<script>
    (() => {
        const slides = document.querySelectorAll('.product-slide');
        let current = 0;
        window.setInterval(() => {
            slides[current].classList.remove('is-active');
            current = (current + 1) % slides.length;
            slides[current].classList.add('is-active');
        }, 3200);
    })();
</script>
<?php endif; ?>
