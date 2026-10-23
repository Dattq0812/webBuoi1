<?php

require_once __DIR__ . "/../controller.php";
require_once __DIR__ . "/../../services/mailer.php";
require_once __DIR__ . "/../../services/auth.php";
require_once __DIR__ . "/../../services/customer_auth.php";

switch ($act)
{
        case 'hienthiDM':
            requireManager();
            $controller = new controller();
            $danhmuc = $controller->hienthiDM();
            include __DIR__ . "/../../view/danhmuc.php";   
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
?>
