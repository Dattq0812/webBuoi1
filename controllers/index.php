<?php

include __DIR__ . "/controller.php";
require_once __DIR__ . "/../services/mailer.php";
require_once __DIR__ . "/../services/auth.php";

if(isset($_REQUEST['act']))
{
    $act = $_REQUEST['act'];

    switch($act)
    {
        case 'login':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $username = trim($_POST['username'] ?? '');
                $password = (string) ($_POST['password'] ?? '');
                $configuredUsername = (string) ($_ENV['ADMIN_USERNAME'] ?? '');
                $configuredPassword = (string) ($_ENV['ADMIN_PASSWORD'] ?? '');
                if ($configuredUsername !== '' && $username === $configuredUsername
                    && $configuredPassword !== '' && hash_equals($configuredPassword, $password)) {
                    session_regenerate_id(true);
                    $_SESSION['user_role'] = 'manager';
                    header('Location: index.php?act=admin');
                    exit;
                }
                $loginError = 'Tên đăng nhập hoặc mật khẩu không đúng.';
            }
            include __DIR__ . "/../view/login.php";
            break;
        case 'logout':
            unset($_SESSION['user_role']);
            header('Location: index.php?act=home');
            exit;
        case 'admin':
            requireManager();
            $controller = new controller();
            $danhsach = $controller->hienthisanpham();
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
            $status = $_POST['trang_thai'] ?? '';
            $allowedStatuses = ['cho_xac_nhan', 'da_xac_nhan', 'dang_giao', 'da_giao', 'da_huy', 'hoan_tra'];
            if (!$id || !in_array($status, $allowedStatuses, true)) {
                echo "<script>alert('Thông tin trạng thái không hợp lệ.'); window.location.href='index.php?act=orders';</script>";
                exit;
            }
            (new controller())->updateOrderStatus($id, $status);
            header('Location: index.php?act=orders');
            exit;
        case 'home':
            $controller = new controller();
            $danhsach = $controller->hienthisanpham();
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
                        // Bắt lỗi SQL (ví dụ: Không thể xóa vì danh mục này đang chứa sản phẩm - lỗi khóa ngoại)
                        echo "<h3 style='color:red'>LỖI SQL KHI XÓA: " . $e->getMessage() . "</h3>";
                        echo "<a href='index.php?act=hienthiDM'>Quay lại</a>";
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
            
            $valided = true; // Cờ kiểm tra dữ liệu hợp lệ

            // 1. Kiểm tra Validate (Bắt lỗi bỏ trống)[cite: 10]
            if(empty($_POST["id_sp"])){
                $id_sp_er = "input id_sp";
                $valided = false;
            }
            if(empty($_POST["name"])){
                $name_er = "input name";
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
                $sp->setId($_POST["id_sp"]);
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
                    $controller = new controller();
                    $controller->themsp($sp);
                }
            }
            
            // 3. Sau khi xử lý xong, lấy lại danh sách và nạp lại View[cite: 10]
            $controller = new controller();
            $danhmuc = $controller->hienthidm();
            $danhsach = $controller->hienthisanpham();
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
                echo "<h3 style='color:red'>LỖI SQL KHI XÓA SẢN PHẨM: "
                    . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8')
                    . "</h3>";
                echo "<a href='index.php?act=hienthiSP'>Quay lại</a>";
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