<?php
    require_once __DIR__ . "/../models/xl_database.php";
class danhmuc{
    private $id_dm;
    private $name;

    public function setID( $id_dm ){
        return $this->id_dm = $id_dm;
    }
    public function set_name( $name ){
        return $this->name = $name;
    }
    public function getId(){
        return $this->id_dm;
    }
    public function getName(){
        return $this->name;
    }
    public function get_danhmuc(){
        $xl=new xl_data() ;
        $sql = "select * from danhmuc";
        $result = $xl->readitem($sql) ;
        return $result ;
    }
    public function add_Danhmuc(danhmuc $dm) {
        $xl = new xl_data();  
        $sql = "INSERT INTO danhmuc (`name`) VALUES ('" . $dm->getName() . "')";
        $xl->execute_item($sql);
        
    }
    public function delete_dm(danhmuc $dm){
        $xl = new xl_data();
        $categoryId = (int) $dm->getId();
        $usedInDeliveredOrder = $xl->readPrepared(
            "SELECT 1
             FROM sanpham sp
             INNER JOIN chitiet_donhang ct ON ct.id_sp = sp.id_sp
             INNER JOIN donhang d ON d.id = ct.donhang_id
             WHERE sp.id_danhmuc = :category_id
               AND d.trang_thai <> 'da_huy'
             LIMIT 1",
            [':category_id' => $categoryId]
        );
        if ($usedInDeliveredOrder) {
            throw new RuntimeException(
                'Không thể xóa danh mục vì sản phẩm trong danh mục đang thuộc một đơn chưa bị hủy.'
            );
        }

        $xl->executePrepared(
            "DELETE FROM danhmuc WHERE id = :id",
            [':id' => $categoryId]
        );
    }
    public function update_dm(danhmuc $dm){
        $xl = new xl_data();
        $sql = "UPDATE danhmuc SET `name` = '"
            . addslashes($dm->getName())
            . "' WHERE id = " . (int) $dm->getId();
        $xl->execute_item($sql);
    }
    public function index() {
        // Bước 1: Gọi hàm get_danhmuc để lấy dữ liệu từ DB
        $danhSachDanhMuc = $this->get_danhmuc();
        echo "<pre>";
        print_r($danhSachDanhMuc);
        echo "</pre>";
        // Bước 2: Nạp file View để hiển thị. 
        // Biến $danhSachDanhMuc sẽ tự động khả dụng bên trong file View này.
        require_once __DIR__ . '/../view/danhmuc.php';
    }

}
?>
