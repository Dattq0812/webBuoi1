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
                // 1. Láº¥y dá»¯ liá»‡u vÃ  lÃ m sáº¡ch (xÃ³a cÃ¡c dáº¥u cÃ¡ch thá»«a á»Ÿ 2 Ä‘áº§u)
                $name = trim($_POST['name']); 
                
                // 2. Validation: Kiá»ƒm tra xem Ã´ nháº­p cÃ³ bá»‹ rá»—ng khÃ´ng
                if (empty($name)) {
                    echo "<script>
                            alert('TÃªn danh má»¥c khÃ´ng Ä‘Æ°á»£c Ä‘á»ƒ rá»—ng!'); 
                            window.history.back(); // Äáº©y ngÆ°á»i dÃ¹ng lÃ¹i láº¡i trang trÆ°á»›c
                          </script>";
                    exit;
                }
                if ((new controller())->isDuplicate('category_name', $name)) {
                    echo "<script>alert('TÃªn danh má»¥c Ä‘Ã£ tá»“n táº¡i!'); window.history.back();</script>";
                    exit;
                }
                              
                try {
                    // 3. Thá»±c hiá»‡n lÆ°u vÃ o database
                    $dm = new danhmuc();
                    $dm->set_name($name);
                    
                    $controller = new controller();
                    $controller->add_danhmuc($dm);
                    
                    // 4. CHUYá»‚N HÆ¯á»šNG TRANG (Chuáº©n logic chá»‘ng F5)
                    // VÃ¬ dá»± Ã¡n cá»§a báº¡n Ä‘Ã£ gá»i Header.php á»Ÿ trÃªn nÃªn ta dÃ¹ng JS Ä‘á»ƒ chuyá»ƒn hÆ°á»›ng
                    echo "<script>window.location.href = 'index.php?act=hienthiDM';</script>";
                    exit;
                    
                } catch (Exception $e) {
                    // Náº¿u lá»—i CSDL thÃ¬ dá»«ng láº¡i vÃ  in ra
                    echo "<h3 style='color:red'>Lá»–I SQL: " . $e->getMessage() . "</h3>";
                }
            }
            break;

        case 'delete_dm':
            requireManager();
            // 1. Kiá»ƒm tra xem trÃªn URL cÃ³ tá»“n táº¡i tham sá»‘ 'id_dm' khÃ´ng
            if (isset($_GET['id_dm'])) {
                
                // 2. Báº£o máº­t: Láº¥y ID vÃ  Ã©p kiá»ƒu vá» sá»‘ nguyÃªn (intval) Ä‘á»ƒ chá»‘ng SQL Injection
                $id_dm = intval($_GET['id_dm']);
                
                // 3. Kiá»ƒm tra ID pháº£i lÃ  má»™t sá»‘ há»£p lá»‡ (lá»›n hÆ¡n 0)
                if ($id_dm > 0) {
                    try {
                        // 4. Thá»±c thi logic XÃ³a
                        $dm = new danhmuc();
                        $dm->setID($id_dm); 
                        
                        $controller = new controller();
                        $controller->delete_dm($dm);
                        
                        // 5. CHUYá»‚N HÆ¯á»šNG Vá»€ Láº I TRANG DANH SÃCH (TÆ°Æ¡ng tá»± case thÃªm)
                        echo "<script>window.location.href = 'index.php?act=hienthiDM';</script>";
                        exit;
                        
                    } catch (Exception $e) {
                        echo "<script>alert("
                            . json_encode($e->getMessage(), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP)
                            . "); window.location.href='index.php?act=hienthiDM';</script>";
                        exit;
                    }
                } else {
                    // Xá»­ lÃ½ náº¿u ngÆ°á»i dÃ¹ng cá»‘ tÃ¬nh nháº­p báº­y báº¡ lÃªn URL (vd: ?id_dm=abc)
                    echo "<script>
                            alert('ID danh má»¥c khÃ´ng há»£p lá»‡!'); 
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
                            alert('ThÃ´ng tin danh má»¥c khÃ´ng há»£p lá»‡!');
                            window.location.href = 'index.php?act=hienthiDM';
                          </script>";
                    exit;
                }
                if ((new controller())->isDuplicate('category_name', $name, $id_dm)) {
                    echo "<script>alert('TÃªn danh má»¥c Ä‘Ã£ tá»“n táº¡i!'); window.history.back();</script>";
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
                    echo "<h3 style='color:red'>Lá»–I SQL KHI Cáº¬P NHáº¬T: "
                        . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8')
                        . "</h3>";
                    echo "<a href='index.php?act=hienthiDM'>Quay láº¡i</a>";
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
