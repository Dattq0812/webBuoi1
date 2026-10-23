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

    public function __construct(){
        $this->ensureInventoryMovementsTable();
    }

    private function ensureInventoryMovementsTable(): void {
        $xl = new xl_data();
        $xl->execute_item(
            "CREATE TABLE IF NOT EXISTS inventory_movements (
                id INT AUTO_INCREMENT PRIMARY KEY,
                product_id INT NOT NULL,
                movement_type ENUM('import', 'export') NOT NULL,
                quantity INT NOT NULL,
                reference_type VARCHAR(30) NULL,
                reference_id INT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_inventory_product (product_id),
                INDEX idx_inventory_created (created_at)
            ) ENGINE=InnoDB"
        );
    }

    public function recordInventoryMovement(
        int $productId,
        string $movementType,
        int $quantity,
        ?string $referenceType = null,
        ?int $referenceId = null
    ): void {
        if ($quantity <= 0 || !in_array($movementType, ['import', 'export'], true)) {
            return;
        }
        (new xl_data())->executePrepared(
            "INSERT INTO inventory_movements
                (product_id, movement_type, quantity, reference_type, reference_id)
             VALUES (:product_id, :movement_type, :quantity, :reference_type, :reference_id)",
            [
                ':product_id' => $productId,
                ':movement_type' => $movementType,
                ':quantity' => $quantity,
                ':reference_type' => $referenceType,
                ':reference_id' => $referenceId,
            ]
        );
    }

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

    public function getBestSelling(int $limit = 6): array {
        $limit = max(1, min($limit, 24));
        $xl = new xl_data();
        return $xl->readPreparedAll(
            "SELECT sp.*, COALESCE(SUM(
                        CASE WHEN d.trang_thai <> 'da_huy' THEN ct.so_luong ELSE 0 END
                    ), 0) AS sold_quantity
             FROM sanpham sp
             LEFT JOIN chitiet_donhang ct ON ct.id_sp = sp.id_sp
             LEFT JOIN donhang d ON d.id = ct.donhang_id
             WHERE sp.Mount > 0
             GROUP BY sp.id_sp
             ORDER BY sold_quantity DESC, sp.Date_import DESC, sp.id_sp DESC
             LIMIT " . $limit
        );
    }

    public function getNewest(int $limit = 6): array {
        $limit = max(1, min($limit, 24));
        $xl = new xl_data();
        return $xl->readPreparedAll(
            "SELECT *
             FROM sanpham
             WHERE Mount > 0
             ORDER BY Date_import DESC, id_sp DESC
             LIMIT " . $limit
        );
    }

    public function search(string $keyword, int $limit = 24): array {
        $keyword = trim($keyword);
        if ($keyword === '') {
            return [];
        }
        $limit = max(1, min($limit, 50));
        $xl = new xl_data();
        return $xl->readPreparedAll(
            "SELECT *
             FROM sanpham
             WHERE Mount > 0
               AND (Name LIKE :keyword OR Decribe LIKE :keyword)
             ORDER BY
                CASE WHEN Name LIKE :exact_keyword THEN 0 ELSE 1 END,
                Date_import DESC, id_sp DESC
             LIMIT " . $limit,
            [
                ':keyword' => '%' . $keyword . '%',
                ':exact_keyword' => $keyword . '%',
            ]
        );
    }

    public function getNextId(): int {
        $xl = new xl_data();
        $result = $xl->readitem("SELECT COALESCE(MAX(id_sp), 0) + 1 AS next_id FROM sanpham");
        return (int) ($result[0]['next_id'] ?? 1);
    }

    // Hàm Xóa sản phẩm theo ID
    public function xoasp(sanpham $sp){
        $xl = new xl_data();
        $productId = (int) $sp->getId();
        $usedInDeliveredOrder = $xl->readPrepared(
            "SELECT 1
             FROM chitiet_donhang ct
             INNER JOIN donhang d ON d.id = ct.donhang_id
             WHERE ct.id_sp = :product_id
               AND d.trang_thai <> 'da_huy'
             LIMIT 1",
            [':product_id' => $productId]
        );
        if ($usedInDeliveredOrder) {
            throw new RuntimeException(
                'Không thể xóa sản phẩm vì sản phẩm đang thuộc một đơn chưa bị hủy.'
            );
        }

        $xl->executePrepared(
            "DELETE FROM sanpham WHERE id_sp = :id",
            [':id' => $productId]
        );
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