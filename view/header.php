<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechZone - Thiết bị công nghệ</title>
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
        body { margin: 0; color: var(--ink); background: var(--background); font: 15px/1.5 Arial, sans-serif; }
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
        main { padding: 34px 0 60px; }
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
        .product-card { display: block; overflow: hidden; color: inherit; background: var(--surface); border: 1px solid var(--border); border-radius: 14px; text-decoration: none; transition: transform .2s, box-shadow .2s; }
        .product-card:hover { transform: translateY(-4px); box-shadow: 0 10px 24px #17203318; }
        .product-image { display: flex; align-items: center; justify-content: center; aspect-ratio: 3 / 4; overflow: hidden; color: var(--muted); background: #f8fafc; }
        .product-image img { width: 100%; height: 100%; object-fit: cover; }
        .product-card-body { padding: 16px; }
        .product-card-body h3 { min-height: 44px; margin: 0 0 10px; font-size: 16px; }
        .product-card-body strong { display: block; color: var(--primary); font-size: 18px; }
        .product-link { display: block; margin-top: 12px; color: var(--muted); font-size: 13px; font-weight: 700; }
        .site-footer { padding: 24px 0; color: #94a3b8; background: #0f172a; text-align: center; }
        @media (max-width: 700px) {
            .topbar-inner, .nav-inner { width: min(100% - 24px, 1180px); }
            .nav-inner { flex-wrap: wrap; gap: 8px; padding: 12px 0; }
            .nav-links { width: 100%; overflow-x: auto; }
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
            <nav>
                <ul class="nav-links">
                    <li><a href="index.php?act=home">Trang chủ</a></li>
                    <?php if (isManager()): ?>
                        <li><a href="index.php?act=admin">Quản trị</a></li>
                        <li><a href="index.php?act=hienthiDM">Danh mục</a></li>
                        <li><a href="index.php?act=hienthiSP">Sản phẩm</a></li>
                        <li><a href="index.php?act=orders">Đơn hàng</a></li>
                    <?php endif; ?>
                    <li><a href="index.php?act=cart">Giỏ hàng (<?php echo isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0; ?>)</a></li>
                    <?php if (isManager()): ?>
                        <li><a href="index.php?act=logout">Đăng xuất</a></li>
                    <?php elseif (isCustomer()): ?>
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
                <a class="customer-account" href="index.php?act=customer_orders" title="Xem thông tin đơn hàng của bạn">
                    <span class="customer-avatar"><?php echo htmlspecialchars($customerInitial, ENT_QUOTES, 'UTF-8'); ?></span>
                    <span class="customer-account-info">
                        <span class="customer-account-name"><?php echo htmlspecialchars($customerName, ENT_QUOTES, 'UTF-8'); ?></span>
                        <span class="customer-account-contact"><?php echo htmlspecialchars($customerContact ?: 'Tài khoản khách hàng', ENT_QUOTES, 'UTF-8'); ?></span>
                    </span>
                </a>
            <?php endif; ?>
        </div>
    </header>
    <main>
