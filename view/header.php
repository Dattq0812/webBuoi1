<!DOCTYPE html>
<html lang="vi">
<head>
    <?php
    $pageTitle = 'TechZone - Thiết bị công nghệ';
    $pageDescription = 'Khám phá sản phẩm công nghệ chính hãng tại TechZone.';
    if (($GLOBALS['act'] ?? ($_REQUEST['act'] ?? 'home')) === 'chitietsp') {
        require_once __DIR__ . '/../models/xl_database.php';
        require_once __DIR__ . '/../models/sanpham.php';
        $productId = filter_input(INPUT_GET, 'idsp', FILTER_VALIDATE_INT);
        if ($productId && $productId > 0) {
            $productQuery = new sanpham();
            $productQuery->setId($productId);
            $productResult = $productQuery->motsp($productQuery);
            if (!empty($productResult)) {
                $seoProduct = $productResult[0];
                $seoName = trim((string) ($seoProduct['Name'] ?? ''));
                $seoDescription = trim(strip_tags((string) ($seoProduct['Decribe'] ?? '')));
                $pageTitle = $seoName !== ''
                    ? $seoName . ' | TechZone'
                    : $pageTitle;
                $pageDescription = $seoDescription !== ''
                    ? $seoDescription
                    : 'Xem thông tin, giá bán và tình trạng sản phẩm tại TechZone.';
            }
        }
    }
    ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo htmlspecialchars($pageDescription, ENT_QUOTES, 'UTF-8'); ?>">
    <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --ink: #172033;
            --muted: #64748b;
            --surface: #ffffff;
            --background: #f4f7fb;
            --border: #e2e8f0;
            --danger: #dc2626;
            --success: #15803d;
        }
        * { box-sizing: border-box; }
        body {
            display: flex;
            min-height: 100vh;
            flex-direction: column;
            margin: 0;
            color: var(--ink);
            background: var(--background);
            font: 15px/1.5 Arial, sans-serif;
        }
        a { color: inherit; }
        .topbar { color: #dbeafe; background: #0f172a; font-size: 13px; }
        .topbar-inner, .nav-inner, .container { width: min(1180px, calc(100% - 32px)); margin: auto; }
        .topbar-inner { display: flex; justify-content: space-between; padding: 8px 0; }
        .navbar { position: sticky; top: 0; z-index: 10; background: var(--surface); border-bottom: 1px solid var(--border); box-shadow: 0 3px 14px #17203312; }
        .nav-inner { display: flex; align-items: center; gap: 28px; min-height: 68px; }
        .brand { color: var(--primary); font-size: 23px; font-weight: 800; text-decoration: none; white-space: nowrap; }
        .brand span { color: #0f172a; }
        .nav-links { display: flex; gap: 6px; margin: 0; padding: 0; list-style: none; }
        .nav-links a { display: block; padding: 10px 14px; border-radius: 8px; color: var(--muted); text-decoration: none; font-weight: 600; }
        .nav-links a:hover, .nav-links .active { color: var(--primary); background: #eff6ff; }
        .customer-account {
            display: flex;
            align-items: center;
            gap: 9px;
            margin-left: auto;
            padding: 7px 11px;
            color: #334155;
            background: #f8fafc;
            border: 1px solid var(--border);
            border-radius: 10px;
            text-decoration: none;
            line-height: 1.25;
        }
        .customer-account:hover { border-color: #93c5fd; background: #eff6ff; }
        .customer-avatar {
            display: grid;
            width: 31px;
            height: 31px;
            place-items: center;
            color: #fff;
            background: linear-gradient(135deg, var(--primary), #7c3aed);
            border-radius: 50%;
            font-weight: 800;
        }
        .customer-account-info { display: flex; flex-direction: column; }
        .customer-account-name { color: #0f172a; font-size: 13px; font-weight: 800; }
        .customer-account-contact { color: var(--muted); font-size: 11px; }
        .customer-profile-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 18px; }
        .loyalty-card { background: linear-gradient(135deg, #fff7ed, #fffbeb); border-color: #fcd34d; }
        .loyalty-badge { display: inline-block; padding: 4px 10px; border-radius: 999px; color: #fff; font-size: 12px; font-weight: 800; }
        .loyalty-standard { background: #64748b; }
        .loyalty-silver { background: #64748b; }
        .loyalty-golden { background: #d97706; }
        .loyalty-platinum { background: #475569; }
        .loyalty-diamond { background: #0891b2; }
        .loyalty-total { margin-top: 18px; color: var(--primary); font-size: 20px; }
        .loyalty-tier-list { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; }
        .loyalty-tier-list div { padding: 14px; background: #f8fafc; border: 1px solid var(--border); border-radius: 10px; }
        .loyalty-tier-list span { display: block; margin: 5px 0; color: var(--muted); font-size: 12px; }
        .loyalty-tier-list b { color: var(--primary); }
        @media (max-width: 800px) {
            .customer-profile-grid, .loyalty-tier-list { grid-template-columns: 1fr 1fr; }
        }
        @media (max-width: 520px) {
            .customer-profile-grid, .loyalty-tier-list { grid-template-columns: 1fr; }
        }
        main { flex: 1 0 auto; padding: 34px 0 60px; }
        .page-heading { display: flex; justify-content: space-between; align-items: end; gap: 16px; margin-bottom: 24px; }
        h1, h2, p { margin-top: 0; }
        h1 { margin-bottom: 6px; font-size: 30px; }
        .subtitle { color: var(--muted); margin-bottom: 0; }
        .card { padding: 24px; background: var(--surface); border: 1px solid var(--border); border-radius: 14px; box-shadow: 0 5px 18px #17203308; }
        .btn, button { display: inline-block; border: 0; border-radius: 8px; padding: 10px 16px; color: #fff; background: var(--primary); cursor: pointer; font-weight: 700; text-decoration: none; }
        .btn:hover, button:hover { background: var(--primary-dark); }
        input, select, textarea { width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font: inherit; }
        input:focus, select:focus, textarea:focus { outline: 2px solid #bfdbfe; border-color: var(--primary); }
        table { width: 100%; border-collapse: collapse; background: var(--surface); overflow: hidden; border: 1px solid var(--border); border-radius: 12px; }
        th, td { padding: 14px 16px; border-bottom: 1px solid var(--border); text-align: left; }
        th { color: #475569; background: #f8fafc; font-size: 13px; text-transform: uppercase; }
        tr:last-child td { border-bottom: 0; }
        .btn-delete { color: var(--danger); text-decoration: none; font-weight: 700; }
        .btn-edit, .btn-save { margin: 0 3px; padding: 7px 11px; font-size: 13px; }
        .btn-edit { color: var(--primary); background: #eff6ff; }
        .btn-save { color: var(--success); background: #f0fdf4; }
        .edit-input { width: min(280px, 100%); }
        .empty { color: var(--muted); text-align: center; }
        .product-row-selling { background: #f0fdf4; }
        .product-row-selling:hover { background: #dcfce7; }
        .product-row-out-of-stock { background: #fff1f2; }
        .product-row-out-of-stock:hover { background: #ffe4e6; }
        .stock-badge { display: inline-block; padding: 5px 9px; border-radius: 999px; font-size: 12px; font-weight: 800; white-space: nowrap; }
        .stock-badge-in { color: #166534; background: #bbf7d0; }
        .stock-badge-out { color: #991b1b; background: #fecdd3; }
        .today-sales { display: inline-block; min-width: 32px; padding: 5px 8px; color: #92400e; background: #fde68a; border-radius: 8px; text-align: center; }
        .product-card { display: block; overflow: hidden; color: inherit; background: var(--surface); border: 1px solid var(--border); border-radius: 14px; text-decoration: none; transition: transform .2s, box-shadow .2s; }
        .product-card:hover { transform: translateY(-4px); box-shadow: 0 10px 24px #17203318; }
        .product-image { display: flex; align-items: center; justify-content: center; aspect-ratio: 3 / 4; overflow: hidden; color: var(--muted); background: #f8fafc; }
        .product-image img { width: 100%; height: 100%; object-fit: cover; }
        .product-card-body { padding: 16px; }
        .product-card-body h3 { min-height: 44px; margin: 0 0 10px; font-size: 16px; }
        .product-card-body strong { display: block; color: var(--primary); font-size: 18px; }
        .product-card-out-of-stock { border: 2px solid #fb7185; background: #fff1f2; }
        .product-card-out-of-stock .product-image { background: #ffe4e6; }
        .product-stock-in, .product-stock-out, .product-today-sales { display: inline-block; margin-top: 8px; font-size: 12px; font-weight: 800; }
        .product-stock-in { color: #15803d; }
        .product-stock-out { padding: 4px 8px; color: #9f1239; background: #fecdd3; border-radius: 999px; }
        .product-today-sales { margin-left: 6px; color: #92400e; }
        .product-link { display: block; margin-top: 12px; color: var(--muted); font-size: 13px; font-weight: 700; }
        .site-footer { padding: 24px 0; color: #94a3b8; background: #0f172a; }
        .site-footer-inner { display: flex; width: min(1180px, calc(100% - 32px)); align-items: center; gap: 28px; margin: auto; }
        .site-map { flex: 0 0 280px; overflow: hidden; border: 1px solid #334155; border-radius: 10px; }
        .site-map iframe { display: block; width: 100%; height: 150px; border: 0; }
        .site-footer-info { flex: 1; text-align: center; line-height: 1.8; }
        .site-footer-info strong { color: #e2e8f0; }
        .site-footer-info a { color: #bfdbfe; }
        @media (max-width: 650px) {
            .site-footer-inner { flex-direction: column; align-items: stretch; gap: 16px; }
            .site-map { flex-basis: auto; width: 100%; }
            .site-footer-info { text-align: center; }
        }
        .contact-float {
            position: fixed;
            right: 18px;
            bottom: 18px;
            z-index: 20;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .contact-float a {
            display: inline-flex;
            min-width: 82px;
            align-items: center;
            justify-content: center;
            padding: 8px 12px;
            color: #fff;
            border-radius: 999px;
            box-shadow: 0 5px 14px #17203333;
            font-size: 12px;
            font-weight: 800;
            text-decoration: none;
        }
        .contact-zalo { background: #0068ff; }
        .contact-facebook { background: #1877f2; }
        .contact-float a:hover { transform: translateY(-2px); }
        .search-box { position: relative; flex: 1 1 220px; max-width: 330px; }
        .search-box form { display: flex; gap: 6px; }
        .search-box input { padding: 9px 11px; }
        .search-box button { padding: 9px 12px; }
        .search-suggestions {
            position: absolute;
            top: calc(100% + 6px);
            right: 0;
            left: 0;
            z-index: 30;
            display: none;
            overflow: hidden;
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 10px;
            box-shadow: 0 10px 24px #17203326;
        }
        .search-suggestion {
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 8px 10px;
            text-decoration: none;
        }
        .search-suggestion:hover { background: #eff6ff; }
        .search-suggestion img { width: 34px; height: 42px; object-fit: cover; border-radius: 5px; }
        .search-suggestion-info { min-width: 0; }
        .search-suggestion-name { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-weight: 700; }
        .search-suggestion-price { color: var(--primary); font-size: 12px; }
        @media (max-width: 700px) {
            .topbar-inner, .nav-inner { width: min(100% - 24px, 1180px); }
            .nav-inner { flex-wrap: wrap; gap: 8px; padding: 12px 0; }
            .nav-links { width: 100%; overflow-x: auto; }
            .search-box { order: 3; width: 100%; max-width: none; }
            .page-heading { align-items: start; flex-direction: column; }
            th, td { padding: 10px; }
        }
    </style>
</head>
<body>
    <div class="topbar">
        <div class="topbar-inner">
            <span>Miễn phí giao hàng cho đơn từ 500.000đ</span>
            <span>Hotline: 0914617291</span>
        </div>
    </div>
    <header class="navbar">
        <div class="nav-inner">
            <a class="brand" href="index.php?act=home">Tech<span>ZoneVN</span></a>
            <div class="search-box">
                <form method="get" action="index.php">
                    <input id="product-search" type="search" name="q" placeholder="Tìm sản phẩm..."
                           autocomplete="off" aria-label="Tìm kiếm sản phẩm">
                    <input type="hidden" name="act" value="search">
                    <button type="submit">Tìm</button>
                </form>
                <div id="search-suggestions" class="search-suggestions" role="listbox"></div>
            </div>
            <nav>
                <ul class="nav-links">
                    <li><a href="index.php?act=home">Trang chủ</a></li>
                    <?php if (isManager()): ?>
                        <li><a href="index.php?act=admin">Quản trị</a></li>
                        <li><a href="index.php?act=hienthiDM">Danh mục</a></li>
                        <li><a href="index.php?act=hienthiSP">Sản phẩm</a></li>
                        <li><a href="index.php?act=orders">Đơn hàng</a></li>
                        <li><a href="index.php?act=customers">Khách hàng</a></li>
                        <li><a href="index.php?act=review_management">Đánh giá</a></li>
                    <?php endif; ?>
                    <li><a href="index.php?act=cart">Giỏ hàng (<?php echo isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0; ?>)</a></li>
                    <?php if (isManager()): ?>
                        <li><a href="index.php?act=logout">Đăng xuất</a></li>
                    <?php elseif (isCustomer()): ?>
                        <li><a href="index.php?act=customer_profile">Hồ sơ</a></li>
                        <li><a href="index.php?act=customer_orders">Đơn của tôi</a></li>
                        <li><a href="index.php?act=logout">Đăng xuất</a></li>
                    <?php else: ?>
                        <li><a href="index.php?act=login">Đăng nhập / Đăng ký</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
            <?php if (isCustomer()): ?>
                <?php
                    $customerName = trim((string) ($_SESSION['customer_name'] ?? 'Khách hàng'));
                    $customerContact = trim((string) ($_SESSION['customer_email'] ?? ''));
                    if ($customerContact === '') {
                        $customerContact = trim((string) ($_SESSION['customer_phone'] ?? ''));
                    }
                    $customerInitial = function_exists('mb_substr')
                        ? mb_strtoupper(mb_substr($customerName, 0, 1, 'UTF-8'), 'UTF-8')
                        : strtoupper(substr($customerName, 0, 1));
                ?>
                <a class="customer-account" href="index.php?act=customer_profile" title="Xem hồ sơ và hạng thành viên">
                    <span class="customer-avatar"><?php echo htmlspecialchars($customerInitial, ENT_QUOTES, 'UTF-8'); ?></span>
                    <span class="customer-account-info">
                        <span class="customer-account-name"><?php echo htmlspecialchars($customerName, ENT_QUOTES, 'UTF-8'); ?></span>
                        <span class="customer-account-contact"><?php echo htmlspecialchars($customerContact ?: 'Tài khoản khách hàng', ENT_QUOTES, 'UTF-8'); ?></span>
                    </span>
                </a>
            <?php endif; ?>
        </div>
    </header>
    <div class="contact-float" aria-label="Liên hệ TechZone">
        <a class="contact-zalo" href="https://zalo.me/0942003937" target="_blank" rel="noopener noreferrer">Zalo</a>
        <a class="contact-facebook" href="https://www.facebook.com/attran.622460" target="_blank" rel="noopener noreferrer">Facebook</a>
    </div>
    <main>
    <script>
        (() => {
            const input = document.getElementById('product-search');
            const box = document.getElementById('search-suggestions');
            if (!input || !box) return;
            let timer;
            input.addEventListener('input', () => {
                window.clearTimeout(timer);
                const keyword = input.value.trim();
                if (keyword === '') {
                    box.replaceChildren();
                    box.style.display = 'none';
                    return;
                }
                timer = window.setTimeout(async () => {
                    try {
                        const response = await fetch('index.php?act=search_suggestions&q=' + encodeURIComponent(keyword));
                        const data = await response.json();
                        box.replaceChildren();
                        (data.products || []).forEach((product) => {
                            const link = document.createElement('a');
                            link.className = 'search-suggestion';
                            link.href = 'index.php?act=chitietsp&idsp=' + product.id;
                            if (product.image) {
                                const image = document.createElement('img');
                                image.src = 'view/images/' + encodeURIComponent(product.image);
                                image.alt = '';
                                link.appendChild(image);
                            }
                            const info = document.createElement('span');
                            info.className = 'search-suggestion-info';
                            info.innerHTML = '<span class="search-suggestion-name"></span><span class="search-suggestion-price"></span>';
                            info.querySelector('.search-suggestion-name').textContent = product.name;
                            info.querySelector('.search-suggestion-price').textContent = product.price;
                            link.appendChild(info);
                            box.appendChild(link);
                        });
                        box.style.display = data.products && data.products.length ? 'block' : 'none';
                    } catch (error) {
                        box.replaceChildren();
                        box.style.display = 'none';
                    }
                }, 220);
            });
            document.addEventListener('click', (event) => {
                if (!event.target.closest('.search-box')) box.style.display = 'none';
            });
        })();
    </script>
