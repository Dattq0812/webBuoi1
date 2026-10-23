<?php

$act = $_REQUEST['act'] ?? 'home';

$routeFiles = [
    'search_suggestions' => __DIR__ . '/routes/search.php',
    'search' => __DIR__ . '/routes/search.php',
    'check_duplicate' => __DIR__ . '/routes/search.php',
    'login' => __DIR__ . '/routes/auth.php',
    'register' => __DIR__ . '/routes/auth.php',
    'forgot_password' => __DIR__ . '/routes/auth.php',
    'reset_password' => __DIR__ . '/routes/auth.php',
    'logout' => __DIR__ . '/routes/auth.php',
    'customer_orders' => __DIR__ . '/routes/customer.php',
    'customer_profile' => __DIR__ . '/routes/customer.php',
    'cancel_customer_order' => __DIR__ . '/routes/customer.php',
    'order_detail' => __DIR__ . '/routes/order.php',
    'orders' => __DIR__ . '/routes/order.php',
    'update_order_status' => __DIR__ . '/routes/order.php',
    'cancel_order' => __DIR__ . '/routes/order.php',
    'admin' => __DIR__ . '/routes/admin.php',
    'customers' => __DIR__ . '/routes/admin.php',
    'home' => __DIR__ . '/routes/admin.php',
    'hienthiDM' => __DIR__ . '/routes/catalog.php',
    'add_danhmuc' => __DIR__ . '/routes/catalog.php',
    'delete_dm' => __DIR__ . '/routes/catalog.php',
    'update_dm' => __DIR__ . '/routes/catalog.php',
    'timdm' => __DIR__ . '/routes/catalog.php',
    'hienthiSP' => __DIR__ . '/routes/product.php',
    'chitietsp' => __DIR__ . '/routes/product.php',
    'addsp' => __DIR__ . '/routes/product.php',
    'xl_themsp' => __DIR__ . '/routes/product.php',
    'deletesp' => __DIR__ . '/routes/product.php',
    'editsp' => __DIR__ . '/routes/product.php',
    'update_sp' => __DIR__ . '/routes/product.php',
    'addcart' => __DIR__ . '/routes/cart.php',
    'cart' => __DIR__ . '/routes/cart.php',
    'updatecart' => __DIR__ . '/routes/cart.php',
    'removecart' => __DIR__ . '/routes/cart.php',
    'checkout' => __DIR__ . '/routes/cart.php',
    'placeorder' => __DIR__ . '/routes/cart.php',
];

if (isset($routeFiles[$act])) {
    require_once $routeFiles[$act];
} else {
    require_once __DIR__ . '/routes/admin.php';
}
?>
