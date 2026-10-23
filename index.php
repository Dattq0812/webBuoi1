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
        require_once __DIR__ . "/controllers/index.php";
        exit;
    }
    require_once __DIR__ . "/view/header.php";
    require_once __DIR__ . "/controllers/index.php"; 
echo '</main><footer class="site-footer">TechZone &copy; 2026 - Thiết bị công nghệ chính hãng</footer></body></html>';
?>