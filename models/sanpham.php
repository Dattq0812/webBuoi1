<?php
// model/sanpham.php
class sanpham {
    // 1. Khai báo các thuộc tính (cột trong database)
    private $id_sp = 0;
    private $Name = "";
    private $Price = 0;
    private $Date_import = "";
    private $Viewsp = 0;
    private $Decribe = "";
    private $Mount = 0;
    private $Sale = 0;
    private $Image = "";
    private $id_danhmuc = 0;

    public function __construct(){}

    // 2. Các hàm Getter và Setter để gán/lấy giá trị cho thuộc tính một cách an toàn
    public function setId_danhmuc($id_danhmuc){ return $this->id_danhmuc =$id_danhmuc; }
    public function getId_danhmuc(){ return $this->id_danhmuc; }

    public function setPrice($Price){ return $this->Price =$Price; }
    public function getPrice(){ return $this->Price; }

    public function setImage($Image){ return $this->Image =$Image; }
    public function getImage(){ return $this->Image; }

    public function setSale($Sale){ return $this->Sale =$Sale; }
    public function getSale(){ return $this->Sale; }

    public function setMount($Mount){ return $this->Mount =$Mount; }
    public function getMount(){ return $this->Mount; }

    public function setDecribe($Decribe){ return $this->Decribe =$Decribe; }
    public function getDecribe(){ return $this->Decribe; }

    public function setViewsp($Viewsp){ return $this->Viewsp =$Viewsp; }
    public function getViewsp(){ return $this->Viewsp; }

    public function setDate_import($Date_import){ return $this->Date_import =$Date_import; }
    public function getDate_import(){ return $this->Date_import; }

    public function setName($Name){ return $this->Name =$Name; }
    public function getName(){ return $this->Name; }

    public function setId($id_sp){ return $this->id_sp =$id_sp; }
    public function getId(){ return $this->id_sp; }

    // 3. Các hàm tương tác với Cơ sở dữ liệu

    // Hàm Thêm sản phẩm
    public function themsp(sanpham $sp){
        $xl = new xl_data();$sql = "INSERT INTO `sanpham` (`id_sp`, `Name`, `Price`, `Date_import`, `Viewsp`, `Decribe`, `Mount`, `Sale`, `image`, `id_danhmuc`) 
                VALUES ('".$sp->getId()."', '".$sp->getName()."', '".$sp->getPrice()."', '".$sp->getDate_import()."', '0', '".$sp->getDecribe()."', '".$sp->getMount()."', '".$sp->getSale()."', '".$sp->getImage()."', '".$sp->getId_danhmuc()."');";
        $xl->execute_item($sql);
    }

    // Hàm Lấy tất cả danh sách sản phẩm
    public function getallsanpham(){
        $xl = new xl_data();$sql = "select * from sanpham";
        return $xl->readitem($sql);
    }

    // Hàm Xóa sản phẩm theo ID
    public function xoasp(sanpham $sp){
        $xl = new xl_data();$sql = "DELETE FROM sanpham WHERE `sanpham`.`id_sp` = " . $sp->getId();
        $xl->execute_item($sql);
    }

    // Hàm Lấy thông tin 1 sản phẩm cụ thể
    public function motsp(sanpham $sp){
        $xl = new xl_data();$sql = "SELECT * FROM `sanpham` WHERE id_sp = " . $sp->getId();$results = $xl->readitem($sql);
        return $results;
    }

    // Hàm Cập nhật (Sửa) sản phẩm
    public function capnhatsp(sanpham $sp): void {
        $xl = new xl_data();$sql = "UPDATE `sanpham` 
                SET `Name` = '".addslashes($sp->getName())."', 
                    `Price` = '".$sp->getPrice()."', 
                    `Viewsp` = '".$sp->getViewsp()."', 
                    `Decribe` = '".addslashes($sp->getDecribe())."', 
                    `Mount` = '".$sp->getMount()."', 
                    `Sale` = '".$sp->getSale()."', 
                    `image` = '".addslashes($sp->getImage())."', 
                    `id_danhmuc` = '".$sp->getId_danhmuc()."' 
                WHERE `sanpham`.`id_sp` = " . $sp->getId();
        $xl->execute_item($sql);
    }
}
?>