<?php

require_once __DIR__ . "/controller.php";
require_once __DIR__ . "/../services/mailer.php";
require_once __DIR__ . "/../services/auth.php";
require_once __DIR__ . "/../services/customer_auth.php";

if(isset($_REQUEST['act']))
{
    $act = $_REQUEST['act'];

    switch($act)
    {
        case 'search_suggestions':
            header('Content-Type: application/json; charset=utf-8');
            $keyword = trim($_GET['q'] ?? '');
            if ($keyword === '') {
                echo json_encode(['success' => true, 'products' => []], JSON_UNESCAPED_UNICODE);
                exit;
            }
            try {
                $products = (new controller())->searchProducts($keyword, 6);
                echo json_encode([
                    'success' => true,
                    'products' => array_map(static function (array $product): array {
                        return [
                            'id' => (int) $product['id_sp'],
                            'name' => $product['Name'],
                            'price' => number_format((float) $product['Price'], 0, ',', '.') . 'đ',
                            'image' => basename((string) ($product['image'] ?? '')),
                        ];
                    }, $products),
                ], JSON_UNESCAPED_UNICODE);
            } catch (Throwable $e) {
                http_response_code(500);
                echo json_encode(['success' => false, 'products' => []], JSON_UNESCAPED_UNICODE);
            }
            exit;
        case 'search':
            $keyword = trim($_GET['q'] ?? '');
            $searchResults = $keyword === '' ? [] : (new controller())->searchProducts($keyword);
            include __DIR__ . "/../view/search.php";
            break;
        case 'check_duplicate':
            header('Content-Type: application/json; charset=utf-8');
            $field = trim($_GET['field'] ?? '');
            $value = trim($_GET['value'] ?? '');
            $excludeId = filter_input(INPUT_GET, 'exclude_id', FILTER_VALIDATE_INT);
            if ($value === '' || $field === '') {
                echo json_encode(['success' => false, 'duplicate' => false, 'message' => 'Thiếu dữ liệu kiểm tra.']);
                exit;
            }
            try {
                $duplicate = (new controller())->isDuplicate($field, $value, $excludeId ?: null);
                echo json_encode(['success' => true, 'duplicate' => $duplicate]);
            } catch (Throwable $e) {
                http_response_code(400);
                echo json_encode(['success' => false, 'duplicate' => false, 'message' => $e->getMessage()]);
            }
            exit;
        case 'login':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $username = trim($_POST['username'] ?? '');
                $password = (string) ($_POST['password'] ?? '');
                $configuredUsername = (string) ($_ENV['ADMIN_USERNAME'] ?? '');
                $configuredPassword = (string) ($_ENV['ADMIN_PASSWORD'] ?? '');
                if ($configuredUsername !== '' && $username === $configuredUsername
                    && $configuredPassword !== '' && hash_equals($configuredPassword, $password)) {
                    session_regenerate_id(true);
                    unset(
                        $_SESSION['customer_id'],
                        $_SESSION['customer_name'],
                        $_SESSION['customer_email'],
                        $_SESSION['customer_phone']
                    );
                    $_SESSION['user_role'] = 'manager';
                    header('Location: index.php?act=admin');
                    exit;
                }
                $customer = (new khachhang())->findByIdentifier($username);
                if ($customer && password_verify($password, $customer['mat_khau'])) {
                    customerLogin($customer);
                    header('Location: index.php?act=home');
                    exit;
                }
                $loginError = 'Email/số điện thoại hoặc mật khẩu không đúng.';
            }
            include __DIR__ . "/../view/login.php";
            break;
        case 'register':
            $registerError = '';
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $name = trim($_POST['ho_ten'] ?? '');
                $phone = trim($_POST['sdt'] ?? '');
                $email = strtolower(trim($_POST['email'] ?? ''));
                $password = (string) ($_POST['password'] ?? '');
                $confirm = (string) ($_POST['password_confirmation'] ?? '');
                if ($name === '' || ($phone === '' && $email === '') || ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL))
                    || ($phone !== '' && !preg_match('/^[0-9+ ()-]{8,20}$/', $phone))
                    || strlen($password) < 8 || $password !== $confirm) {
                    $registerError = 'Vui lòng nhập đúng thông tin và mật khẩu tối thiểu 8 ký tự.';
                } else {
                    $customerModel = new khachhang();
                    if (($email !== '' && $customerModel->findByEmail($email))
                        || ($phone !== '' && $customerModel->findByIdentifier($phone))) {
                        $registerError = 'Email hoặc số điện thoại đã được đăng ký.';
                    } else {
                        try {
                            $customerModel->create($name, $phone, $email, password_hash($password, PASSWORD_DEFAULT));
                            $customer = $customerModel->findByIdentifier($email !== '' ? $email : $phone);
                            customerLogin($customer);
                            header('Location: index.php?act=home');
                            exit;
                        } catch (Throwable $e) {
                            $registerError = 'Không thể tạo tài khoản. Vui lòng kiểm tra lại dữ liệu.';
                        }
                    }
                }
            }
            include __DIR__ . "/../view/register.php";
            break;
        case 'forgot_password':
            $forgotMessage = '';
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $email = strtolower(trim($_POST['email'] ?? ''));
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $customerModel = new khachhang();
                    $customer = $customerModel->findByEmail($email);
                    if ($customer) {
                        $token = bin2hex(random_bytes(32));
                        $customerModel->saveResetToken((int) $customer['id'], hash('sha256', $token), date('Y-m-d H:i:s', time() + 3600));
                        try {
                            sendPasswordResetEmail($customer, $token);
                        } catch (Throwable $e) {
                            $forgotMessage = 'Không thể gửi email lúc này. Vui lòng thử lại sau.';
                        }
                    }
                }
                if ($forgotMessage === '') {
                    $forgotMessage = 'Nếu email đã được đăng ký, hướng dẫn đặt lại mật khẩu đã được gửi.';
                }
            }
            include __DIR__ . "/../view/forgot_password.php";
            break;
        case 'reset_password':
            $token = trim($_GET['token'] ?? $_POST['token'] ?? '');
            $resetError = '';
            if ($token === '' || !(new khachhang())->findByResetToken(hash('sha256', $token))) {
                $resetError = 'Liên kết đặt lại mật khẩu không hợp lệ hoặc đã hết hạn.';
            } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $password = (string) ($_POST['password'] ?? '');
                $confirm = (string) ($_POST['password_confirmation'] ?? '');
                if (strlen($password) < 8 || $password !== $confirm) {
                    $resetError = 'Mật khẩu phải có ít nhất 8 ký tự và khớp xác nhận.';
                } else {
                    $customer = (new khachhang())->findByResetToken(hash('sha256', $token));
                    (new khachhang())->updatePassword((int) $customer['id'], password_hash($password, PASSWORD_DEFAULT));
                    header('Location: index.php?act=login&reset=1');
                    exit;
                }
            }
            include __DIR__ . "/../view/reset_password.php";
            break;
        case 'logout':
            unset(
                $_SESSION['user_role'],
                $_SESSION['customer_id'],
                $_SESSION['customer_name'],
                $_SESSION['customer_email'],
                $_SESSION['customer_phone']
            );
            header('Location: index.php?act=home');
            exit;
        case 'customer_orders':
            requireCustomer();
            $orders = (new controller())->getCustomerOrders(customerId());
            include __DIR__ . "/../view/customer_orders.php";
            break;
        case 'order_detail':
            $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
            if (!$id || $id <= 0) {
                echo "<script>alert('Mã đơn hàng không hợp lệ.'); window.history.back();</script>";
                exit;
            }
            $orderController = new controller();
            if (isManager()) {
                $orderDetail = $orderController->getOrderDetail($id);
            } elseif (isCustomer()) {
                $orderDetail = $orderController->getOrderDetail($id, customerId());
            } else {
                header('Location: index.php?act=login');
                exit;
            }
            if (!$orderDetail) {
                http_response_code(404);
                echo '<div class="container"><div class="card empty">Không tìm thấy đơn hàng hoặc bạn không có quyền xem đơn này.</div></div>';
                exit;
            }
            include __DIR__ . "/../view/order_detail.php";
            break;
        case 'cancel_customer_order':
            requireCustomer();
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            $reason = trim($_POST['ly_do_huy'] ?? '');
            if (!$id || $reason === '' || mb_strlen($reason) > 1000) {
                echo "<script>alert('Vui lòng nhập lý do hủy hợp lệ.'); window.location.href='index.php?act=customer_orders';</script>";
                exit;
            }
            try {
                $cancelledOrder = (new controller())->cancelCustomerOrder($id, $reason, customerId());
                sendCustomerCancellationToShop($cancelledOrder);
                header('Location: index.php?act=customer_orders');
                exit;
            } catch (Throwable $e) {
                echo "<h3 style='color:red'>Không thể hủy đơn hàng: "
                    . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</h3>";
                echo "<a href='index.php?act=customer_orders'>Quay lại lịch sử đơn hàng</a>";
                exit;
            }
        case 'admin':
            requireManager();
            $controller = new controller();
            $danhsach = $controller->hienthisanpham();
            $revenueStatistics = $controller->getRevenueStatistics();
            include __DIR__ . "/../view/home.php";
            break;
        case 'orders':
            requireManager();
            $orders = (new controller())->getOrders();
            include __DIR__ . "/../view/orders.php";
            break;
        case 'update_order_status':
            requireManager();
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            $currentStatus = $_POST['trang_thai_hien_tai'] ?? '';
            $nextStatus = $_POST['trang_thai_tiep_theo'] ?? '';
            $statusFlow = [
                'cho_xac_nhan' => 'da_xac_nhan',
                'da_xac_nhan' => 'dang_giao',
                'dang_giao' => 'da_giao',
                'da_giao' => 'hoan_tra',
            ];
            if (!$id || !isset($statusFlow[$currentStatus]) || $statusFlow[$currentStatus] !== $nextStatus) {
                echo "<script>alert('Thông tin trạng thái không hợp lệ.'); window.location.href='index.php?act=orders';</script>";
                exit;
            }
            $updated = (new controller())->updateOrderStatus($id, $currentStatus, $nextStatus);
            if (!$updated) {
                echo "<script>alert('Đơn hàng đã được cập nhật hoặc trạng thái không còn hợp lệ.'); window.location.href='index.php?act=orders';</script>";
                exit;
            }
            $returnToDetail = ($_POST['return_to'] ?? '') === 'order_detail';
            header('Location: index.php?act=' . ($returnToDetail ? 'order_detail&id=' . $id : 'orders'));
            exit;
        case 'cancel_order':
            requireManager();
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            $reason = trim($_POST['ly_do_huy'] ?? '');
            if (!$id || $reason === '' || mb_strlen($reason) > 1000) {
                echo "<script>alert('Vui lòng nhập lý do hủy hợp lệ.'); window.location.href='index.php?act=orders';</script>";
                exit;
            }
            try {
                $cancelledOrder = (new controller())->cancelOrder($id, $reason);
                sendOrderCancellationEmail($cancelledOrder);
                header('Location: index.php?act=orders');
                exit;
            } catch (Throwable $e) {
                echo "<h3 style='color:red'>Không thể hủy đơn hàng: "
                    . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</h3>";
                echo "<a href='index.php?act=orders'>Quay lại danh sách đơn hàng</a>";
                exit;
            }
        case 'home':
            $controller = new controller();
            $danhsach = $controller->hienthisanpham();
            $sanPhamBanChay = $controller->getBestSellingProducts();
            $sanPhamMoi = $controller->getNewestProducts();
            if (isManager()) {
                $revenueStatistics = $controller->getRevenueStatistics();
                $quarterlyStatistics = $controller->getQuarterlyStatistics();
            }
            include __DIR__ . "/../view/home.php";
            break;
        case 'hienthiDM':
            requireManager();
            $controller = new controller();
            $danhmuc = $controller->hienthiDM();
            include __DIR__ . "/../view/danhmuc.php";   
            break;
        default:
            $controller = new controller();
            $danhsach = $controller->hienthisanpham();
            $sanPhamBanChay = $controller->getBestSellingProducts();
            $sanPhamMoi = $controller->getNewestProducts();
            if (isManager()) {
                $revenueStatistics = $controller->getRevenueStatistics();
                $quarterlyStatistics = $controller->getQuarterlyStatistics();
            }
            include __DIR__ . "/../view/home.php";
            break;
        case 'hienthiSP':
            requireManager();
            $controller = new controller();
            $danhmuc = $controller->hienthiDM();
            $danhsach = $controller->hienthisanpham();
            $showAddForm = false;
            include __DIR__ . "/../view/sanpham.php";
            break;
        case 'chitietsp':
            $id_sp = filter_input(INPUT_GET, 'idsp', FILTER_VALIDATE_INT);
            if ($id_sp === false || $id_sp === null || $id_sp <= 0) {
                echo "<script>
                        alert('Mã sản phẩm không hợp lệ!');
                        window.location.href = 'index.php?act=home';
                      </script>";
                exit;
            }

            $sp = new sanpham();
            $sp->setId($id_sp);
            $controller = new controller();
            $products = $controller->getOneProduct($sp);
            if (empty($products)) {
                echo "<script>
                        alert('Không tìm thấy sản phẩm!');
                        window.location.href = 'index.php?act=home';
                      </script>";
                exit;
            }

            $product = $products[0];
            include __DIR__ . "/../view/chitietsp.php";
            break;
        case 'addcart':
            $id_sp = filter_input(INPUT_POST, 'id_sp', FILTER_VALIDATE_INT);
            $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
            if (!$id_sp || !$quantity || $quantity < 1) {
                echo "<script>alert('Thông tin sản phẩm không hợp lệ!'); window.history.back();</script>";
                exit;
            }
            $sp = new sanpham();
            $sp->setId($id_sp);
            $controller = new controller();
            $products = $controller->getOneProduct($sp);
            if (empty($products) || (int) $products[0]['Mount'] < 1) {
                echo "<script>alert('Sản phẩm không tồn tại hoặc đã hết hàng!'); window.history.back();</script>";
                exit;
            }
            $stock = (int) $products[0]['Mount'];
            $current = (int) ($_SESSION['cart'][$id_sp] ?? 0);
            $_SESSION['cart'][$id_sp] = min($stock, $current + $quantity);
            echo "<script>window.location.href = 'index.php?act=cart';</script>";
            exit;

        case 'cart':
            $controller = new controller();
            $cart = $_SESSION['cart'] ?? [];
            $cartItems = $controller->getCartItems($cart);
            $cartTotal = 0;
            foreach ($cartItems as $item) {
                $cartTotal += $item['subtotal'];
                $_SESSION['cart'][$item['id_sp']] = $item['quantity'];
            }
            include __DIR__ . "/../view/cart.php";
            break;

        case 'updatecart':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo "<script>window.location.href = 'index.php?act=cart';</script>";
                exit;
            }
            $quantities = $_POST['quantities'] ?? [];
            $controller = new controller();
            foreach ($quantities as $id => $quantity) {
                $id_sp = filter_var($id, FILTER_VALIDATE_INT);
                $quantity = filter_var($quantity, FILTER_VALIDATE_INT);
                if (!$id_sp || $quantity === false || $quantity < 0) {
                    continue;
                }
                $sp = new sanpham();
                $sp->setId($id_sp);
                $products = $controller->getOneProduct($sp);
                if (empty($products) || $quantity === 0) {
                    unset($_SESSION['cart'][$id_sp]);
                    continue;
                }
                $_SESSION['cart'][$id_sp] = min($quantity, (int) $products[0]['Mount']);
            }
            echo "<script>window.location.href = 'index.php?act=cart';</script>";
            exit;

        case 'removecart':
            $id_sp = filter_input(INPUT_GET, 'idsp', FILTER_VALIDATE_INT);
            if ($id_sp) {
                unset($_SESSION['cart'][$id_sp]);
            }
            echo "<script>window.location.href = 'index.php?act=cart';</script>";
            exit;

        case 'checkout':
            requireCustomer();
            $controller = new controller();
            $cartItems = $controller->getCartItems($_SESSION['cart'] ?? []);
            if (empty($cartItems)) {
                echo "<script>
                        alert('Giỏ hàng đang trống!');
                        window.location.href = 'index.php?act=home';
                      </script>";
                exit;
            }
            $cartTotal = 0;
            foreach ($cartItems as $item) {
                $cartTotal += $item['subtotal'];
            }
            $voucherOptions = [
                'TECH10' => ['type' => 'percent', 'value' => 10],
                'TECH50K' => ['type' => 'fixed', 'value' => 50000],
            ];
            include __DIR__ . "/../view/checkout.php";
            break;

        case 'placeorder':
            requireCustomer();
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo "<script>window.location.href = 'index.php?act=cart';</script>";
                exit;
            }
            $buyerName = trim($_POST['buyer_name'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $address = trim($_POST['address'] ?? '');
            $paymentMethod = $_POST['payment_method'] ?? '';
            $voucherCode = strtoupper(trim($_POST['voucher_code'] ?? ''));
            $voucherOptions = [
                'TECH10' => ['type' => 'percent', 'value' => 10],
                'TECH50K' => ['type' => 'fixed', 'value' => 50000],
            ];
            if ($buyerName === '' || $address === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)
                || !preg_match('/^[0-9+ ()-]{8,20}$/', $phone)
                || !in_array($paymentMethod, ['cod', 'bank_transfer'], true)) {
                echo "<script>alert('Vui lòng nhập đúng họ tên, địa chỉ, số điện thoại và email.'); window.history.back();</script>";
                exit;
            }
            $controller = new controller();
            $cartItems = $controller->getCartItems($_SESSION['cart'] ?? []);
            if (empty($cartItems)) {
                echo "<script>alert('Giỏ hàng đang trống!'); window.location.href = 'index.php?act=home';</script>";
                exit;
            }
            $cartTotal = 0;
            foreach ($cartItems as $item) {
                $cartTotal += $item['subtotal'];
            }
            if ($voucherCode !== '' && !isset($voucherOptions[$voucherCode])) {
                echo "<script>alert('Mã voucher không hợp lệ.'); window.history.back();</script>";
                exit;
            }
            $discount = 0;
            if ($voucherCode !== '') {
                $voucher = $voucherOptions[$voucherCode];
                $discount = $voucher['type'] === 'percent'
                    ? $cartTotal * $voucher['value'] / 100
                    : $voucher['value'];
                $discount = min($cartTotal, $discount);
            }
            $order = [
                'customer_id' => customerId(),
                'ma_donhang' => 'DH' . date('YmdHis') . strtoupper(bin2hex(random_bytes(2))),
                'ten_khachhang' => $buyerName,
                'sdt' => $phone,
                'email' => $email,
                'dia_chi' => $address,
                'ghi_chu' => trim($_POST['note'] ?? ''),
                'phuong_thuc_thanh_toan' => $paymentMethod,
                'ma_voucher' => $voucherCode,
                'tien_giam' => $discount,
                'total' => $cartTotal - $discount,
                'items' => $cartItems,
            ];
            try {
                $controller->createOrder($order, $cartItems);
            } catch (Throwable $e) {
                echo "<h3 style='color:red'>Không thể lưu đơn hàng: "
                    . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8')
                    . "</h3>";
                echo "<p>Đơn hàng chưa được tạo và số lượng tồn kho chưa thay đổi. "
                    . "Vui lòng quay lại giỏ hàng để kiểm tra lại.</p>";
                echo "<a href='index.php?act=cart'>Quay lại giỏ hàng</a>";
                exit;
            }
            $emailSent = true;
            $emailError = '';
            try {
                sendOrderConfirmationEmail($order);
            } catch (Throwable $e) {
                $emailSent = false;
                $emailError = $e->getMessage();
            }
            $_SESSION['cart'] = [];
            $orderCode = $order['ma_donhang'];
            $paymentMethod = $order['phuong_thuc_thanh_toan'];
            $voucherCode = $order['ma_voucher'];
            $discount = $order['tien_giam'];
            include __DIR__ . "/../view/order_success.php";
            break;
        case 'addsp':
            requireManager();
            $controller = new controller();
            $danhmuc = $controller->hienthiDM();
            $danhsach = $controller->hienthisanpham();
            $nextProductId = $controller->getNextProductId();
            $showAddForm = true;
            include __DIR__ . "/../view/sanpham.php";
            break;
        case 'add_danhmuc':
            requireManager();
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                // 1. Lấy dữ liệu và làm sạch (xóa các dấu cách thừa ở 2 đầu)
                $name = trim($_POST['name']); 
                
                // 2. Validation: Kiểm tra xem ô nhập có bị rỗng không
                if (empty($name)) {
                    echo "<script>
                            alert('Tên danh mục không được để rỗng!'); 
                            window.history.back(); // Đẩy người dùng lùi lại trang trước
                          </script>";
                    exit;
                }
                if ((new controller())->isDuplicate('category_name', $name)) {
                    echo "<script>alert('Tên danh mục đã tồn tại!'); window.history.back();</script>";
                    exit;
                }
                              
                try {
                    // 3. Thực hiện lưu vào database
                    $dm = new danhmuc();
                    $dm->set_name($name);
                    
                    $controller = new controller();
                    $controller->add_danhmuc($dm);
                    
                    // 4. CHUYỂN HƯỚNG TRANG (Chuẩn logic chống F5)
                    // Vì dự án của bạn đã gọi Header.php ở trên nên ta dùng JS để chuyển hướng
                    echo "<script>window.location.href = 'index.php?act=hienthiDM';</script>";
                    exit;
                    
                } catch (Exception $e) {
                    // Nếu lỗi CSDL thì dừng lại và in ra
                    echo "<h3 style='color:red'>LỖI SQL: " . $e->getMessage() . "</h3>";
                }
            }
            break;

        case 'delete_dm':
            requireManager();
            // 1. Kiểm tra xem trên URL có tồn tại tham số 'id_dm' không
            if (isset($_GET['id_dm'])) {
                
                // 2. Bảo mật: Lấy ID và ép kiểu về số nguyên (intval) để chống SQL Injection
                $id_dm = intval($_GET['id_dm']);
                
                // 3. Kiểm tra ID phải là một số hợp lệ (lớn hơn 0)
                if ($id_dm > 0) {
                    try {
                        // 4. Thực thi logic Xóa
                        $dm = new danhmuc();
                        $dm->setID($id_dm); 
                        
                        $controller = new controller();
                        $controller->delete_dm($dm);
                        
                        // 5. CHUYỂN HƯỚNG VỀ LẠI TRANG DANH SÁCH (Tương tự case thêm)
                        echo "<script>window.location.href = 'index.php?act=hienthiDM';</script>";
                        exit;
                        
                    } catch (Exception $e) {
                        echo "<script>alert("
                            . json_encode($e->getMessage(), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP)
                            . "); window.location.href='index.php?act=hienthiDM';</script>";
                        exit;
                    }
                } else {
                    // Xử lý nếu người dùng cố tình nhập bậy bạ lên URL (vd: ?id_dm=abc)
                    echo "<script>
                            alert('ID danh mục không hợp lệ!'); 
                            window.location.href = 'index.php?act=hienthiDM';
                          </script>";
                    exit;
                }
            }
            break;
        case "xl_themsp":
        requireManager();
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $controller = new controller();
            $id_sp = $controller->getNextProductId();
            
            $valided = true; // Cờ kiểm tra dữ liệu hợp lệ

            // 1. Kiểm tra Validate (Bắt lỗi bỏ trống)[cite: 10]
            if(empty($_POST["name"])){
                $name_er = "input name";
                $valided = false;
            }
            if ($controller->isDuplicate('product_name', trim($_POST['name'] ?? ''))) {
                $name_er = "Tên sản phẩm đã tồn tại";
                $valided = false;
            }
            if(empty($_POST["price"])){
                $price_er = "input price";
                $valided = false;
            }
            if(empty($_POST["mount"])){
                $mount_er = "input mount";
                $valided = false;
            }
            if(empty($_POST["decribe"])){
                $decribe_er = "input describe";
                $valided = false;
            }
            if(isset($_POST["sale"]) == ""){
                $sale_er = "input sale";
                $valided = false;
            }
            if($_FILES["image"]["error"] <> 0){
                $image_er = "input image";
                $valided = false;
            }

            // 2. Nếu mọi dữ liệu đều hợp lệ (không rỗng) thì tiến hành Thêm[cite: 10]
            if($valided){
                $sp = new sanpham();
                
                // Gán giá trị từ form (POST) vào đối tượng sản phẩm
                $sp->setId($id_sp);
                $sp->setName($_POST["name"]);
                $sp->setPrice($_POST["price"]);
                $sp->setMount($_POST["mount"]);
                $sp->setDecribe($_POST["decribe"]);
                $sp->setSale($_POST["sale"]);
                $sp->setDate_import(date('Y-m-d H:i:s')); // Đặt ngày hiện tại
                $sp->setId_danhmuc($_POST["id_danhmuc"]);
                
                // Xử lý Upload Hình ảnh[cite: 10]
                $hinhsp = basename($_FILES['image']['name']);
                $sp->setImage($hinhsp);
                $path = __DIR__ . '/../view/images/';
                $target_file = $path . $hinhsp;
                
                // Di chuyển file từ bộ nhớ tạm vào thư mục dự án[cite: 10]
                if (!is_dir($path) || !is_writable($path)
                    || !move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                    $image_er = "Không thể lưu ảnh vào thư mục view/images.";
                    $valided = false;
                }
                
                // Gọi Controller để thực thi lưu vào Database[cite: 10]
                if ($valided) {
                    $controller->themsp($sp);
                }
            }
            
            // 3. Sau khi xử lý xong, lấy lại danh sách và nạp lại View[cite: 10]
            $controller = new controller();
            $danhmuc = $controller->hienthidm();
            $danhsach = $controller->hienthisanpham();
            $nextProductId = $controller->getNextProductId();
            $showAddForm = true;
            include __DIR__ . "/../view/sanpham.php";
        }
        break;                  

        case 'deletesp':
            requireManager();
            $id_sp = filter_input(INPUT_GET, 'idsp_del', FILTER_VALIDATE_INT);
            if ($id_sp === false || $id_sp === null || $id_sp <= 0) {
                echo "<script>
                        alert('Mã sản phẩm không hợp lệ!');
                        window.location.href = 'index.php?act=hienthiSP';
                      </script>";
                exit;
            }

            try {
                $sp = new sanpham();
                $sp->setId($id_sp);
                $controller = new controller();
                $controller->deletesp($sp);
                echo "<script>window.location.href = 'index.php?act=hienthiSP';</script>";
                exit;
            } catch (Exception $e) {
                echo "<script>alert("
                    . json_encode($e->getMessage(), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP)
                    . "); window.location.href='index.php?act=hienthiSP';</script>";
                exit;
            }

        case 'editsp':
            requireManager();
            $id_sp = filter_input(INPUT_GET, 'idsp', FILTER_VALIDATE_INT);
            if ($id_sp === false || $id_sp === null || $id_sp <= 0) {
                echo "<script>
                        alert('Mã sản phẩm không hợp lệ!');
                        window.location.href = 'index.php?act=hienthiSP';
                      </script>";
                exit;
            }

            $sp = new sanpham();
            $sp->setId($id_sp);
            $controller = new controller();
            $products = $controller->getOneProduct($sp);
            if (empty($products)) {
                echo "<script>
                        alert('Không tìm thấy sản phẩm!');
                        window.location.href = 'index.php?act=hienthiSP';
                      </script>";
                exit;
            }
            $product = $products[0];
            $danhmuc = $controller->hienthiDM();
            include __DIR__ . "/../view/edit_sanpham.php";
            break;

        case 'update_sp':
            requireManager();
            if ($_SERVER["REQUEST_METHOD"] !== "POST") {
                header('Location: index.php?act=hienthiSP');
                exit;
            }

            $id_sp = filter_input(INPUT_POST, 'id_sp', FILTER_VALIDATE_INT);
            $id_danhmuc = filter_input(INPUT_POST, 'id_danhmuc', FILTER_VALIDATE_INT);
            $name = trim($_POST['name'] ?? '');
            $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_INT);
            $mount = filter_input(INPUT_POST, 'mount', FILTER_VALIDATE_INT);
            $sale = filter_input(INPUT_POST, 'sale', FILTER_VALIDATE_INT);
            $decribe = trim($_POST['decribe'] ?? '');

            if (!$id_sp || !$id_danhmuc || $name === '' || $price === false || $price < 1
                || $mount === false || $mount < 0 || $sale === false || $decribe === '') {
                echo "<script>
                        alert('Vui lòng nhập đầy đủ thông tin sản phẩm hợp lệ!');
                        window.history.back();
                      </script>";
                exit;
            }

            try {
                $sp = new sanpham();
                $controller = new controller();
                $sp->setId($id_sp);
                $sp->setId_danhmuc($id_danhmuc);
                $sp->setName($name);
                $sp->setPrice($price);
                $sp->setMount($mount);
                $sp->setSale($sale);
                $sp->setDecribe($decribe);

                $oldProduct = $controller->getOneProduct($sp);
                if (empty($oldProduct)) {
                    throw new RuntimeException('Không tìm thấy sản phẩm cần cập nhật.');
                }
                $oldMount = (int) $oldProduct[0]['Mount'];
                $image = $oldProduct[0]['image'];
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $image = basename($_FILES['image']['name']);
                    $imagePath = __DIR__ . '/../view/images/' . $image;
                    if (!is_dir(dirname($imagePath)) || !is_writable(dirname($imagePath))
                        || !move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
                        throw new RuntimeException('Không thể lưu ảnh mới vào thư mục view/images.');
                    }
                }
                $sp->setImage($image);
                $controller->updateProduct($sp);
                $mountChange = $mount - $oldMount;
                if ($mountChange > 0) {
                    $sp->recordInventoryMovement($id_sp, 'import', $mountChange, 'product_update');
                } elseif ($mountChange < 0) {
                    $sp->recordInventoryMovement($id_sp, 'export', abs($mountChange), 'product_update');
                }

                echo "<script>window.location.href = 'index.php?act=hienthiSP';</script>";
                exit;
            } catch (Exception $e) {
                echo "<h3 style='color:red'>LỖI SQL KHI CẬP NHẬT SẢN PHẨM: "
                    . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8')
                    . "</h3>";
                echo "<a href='index.php?act=hienthiSP'>Quay lại</a>";
                exit;
            }

        case 'update_dm':
            requireManager();
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                $id_dm = filter_input(INPUT_POST, 'id_dm', FILTER_VALIDATE_INT);
                $name = trim($_POST['name'] ?? '');

                if ($id_dm === false || $id_dm === null || $id_dm <= 0 || $name === '') {
                    echo "<script>
                            alert('Thông tin danh mục không hợp lệ!');
                            window.location.href = 'index.php?act=hienthiDM';
                          </script>";
                    exit;
                }
                if ((new controller())->isDuplicate('category_name', $name, $id_dm)) {
                    echo "<script>alert('Tên danh mục đã tồn tại!'); window.history.back();</script>";
                    exit;
                }

                try {
                    $dm = new danhmuc();
                    $dm->setID($id_dm);
                    $dm->set_name($name);

                    $controller = new controller();
                    $controller->update_dm($dm);

                    echo "<script>window.location.href = 'index.php?act=hienthiDM';</script>";
                    exit;
                } catch (Exception $e) {
                    echo "<h3 style='color:red'>LỖI SQL KHI CẬP NHẬT: "
                        . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8')
                        . "</h3>";
                    echo "<a href='index.php?act=hienthiDM'>Quay lại</a>";
                    exit;
                }
            }
            break;

        case 'timdm':

            if ($_SERVER["REQUEST_METHOD"] == "POST")
            {
                $name = $_POST['keyword'];

                echo $name;
            }

            break;
    }
}


?>