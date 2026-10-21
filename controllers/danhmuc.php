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
        $xl = new xl_data() ;  
        $sql = "delete from danhmuc where id    =  ".$dm->getId();
        $xl->execute_item($sql) ;
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
