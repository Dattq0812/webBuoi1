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
    public function getNextId(): int
    {
        $rows = (new xl_data())->readPreparedAll(
            "SELECT id FROM danhmuc ORDER BY id ASC"
        );
        $nextId = 1;
        foreach ($rows as $row) {
            $id = (int) $row['id'];
            if ($id === $nextId) {
                $nextId++;
            } elseif ($id > $nextId) {
                break;
            }
        }
        return $nextId;
    }

    public function add_Danhmuc(danhmuc $dm) {
        $xl = new xl_data();  
        $xl->executePrepared(
            "INSERT INTO danhmuc (id, `name`) VALUES (:id, :name)",
            [
                ':id' => $this->getNextId(),
                ':name' => $dm->getName(),
            ]
        );
        
    }
    public function delete_dm(danhmuc $dm){
        $xl = new xl_data();
        $categoryId = (int) $dm->getId();
        $categoryProduct = $xl->readPrepared(
            "SELECT 1
             FROM sanpham
             WHERE id_danhmuc = :category_id
             LIMIT 1",
            [':category_id' => $categoryId]
        );
        if ($categoryProduct) {
            throw new RuntimeException(
                'Không thể xóa danh mục vì danh mục vẫn còn sản phẩm. Vui lòng chuyển hoặc xóa sản phẩm trước.'
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
