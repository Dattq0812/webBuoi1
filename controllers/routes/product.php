<?php

require_once __DIR__ . "/../controller.php";
require_once __DIR__ . "/../../services/mailer.php";
require_once __DIR__ . "/../../services/auth.php";
require_once __DIR__ . "/../../services/customer_auth.php";

switch ($act)
{
        case 'hienthiSP':
            requireManager();
            $controller = new controller();
            $danhmuc = $controller->hienthiDM();
            $danhsach = $controller->hienthisanpham();
            $showAddForm = false;
            include __DIR__ . "/../../view/sanpham.php";
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
            include __DIR__ . "/../../view/chitietsp.php";
            break;
        case 'addsp':
            requireManager();
            $controller = new controller();
            $danhmuc = $controller->hienthiDM();
            $danhsach = $controller->hienthisanpham();
            $nextProductId = $controller->getNextProductId();
            $showAddForm = true;
            include __DIR__ . "/../../view/sanpham.php";
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
                $path = __DIR__ . '/../../view/images/';
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
            include __DIR__ . "/../../view/sanpham.php";
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
            include __DIR__ . "/../../view/edit_sanpham.php";
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
                    $imagePath = __DIR__ . '/../../view/images/' . $image;
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

}
?>

