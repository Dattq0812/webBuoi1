<?php
// Bật toàn bộ thông báo lỗi ra màn hình
    ob_start();
    session_start();
    require_once __DIR__ . "/services/auth.php";
    require_once __DIR__ . "/services/customer_auth.php";
    loadEnvironment();
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    $act = isset($_REQUEST['act']) ? $_REQUEST['act'] : 'home';
$_REQUEST['act'] = $act;
    if ($act === 'check_duplicate') {
        require_once __DIR__ . "/controllers/routes/search.php";
        exit;
    }
    require_once __DIR__ . "/view/header.php";
    require_once __DIR__ . "/controllers/index.php"; 
echo '</main><footer class="site-footer"><div class="site-footer-inner">'
    . '<div class="site-map">'
    . '<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d629.2580554811294!2d106.6419809085125!3d10.784852134470063!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752f0053ce82c5%3A0xdeec755f3d47e479!2zQ8O0bmcgdHkgQ-G7lSBwaOG6p24gVGjhu7FjIHBo4bqpbSBIw7luZyBI4bqtdQ!5e0!3m2!1sen!2sus!4v1792743550052!5m2!1sen!2sus" width="400" height="300" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="strict-origin-when-cross-origin"></iframe>'
    . '</div>'
    . '<div class="site-footer-info">'
    . '<strong>TechZoneVN</strong> &copy; 2026 - Thiết bị công nghệ chính hãng'
    . '<br>Địa chỉ: 642 Âu Cơ, Phường Bảy Hiền, Thành Phố Hồ Chí Minh, Việt Nam'
    . ' | Email: <a href="mailto:cskh@techzonevn.store">cskh@techzonevn.store</a>'
    . '</div>'
    . '</div></footer></body></html>';
?>