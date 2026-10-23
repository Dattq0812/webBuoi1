<?php
require_once __DIR__ . "/xl_database.php";

class donhang
{
    public function create(array $order, array $items): void
    {
        $xl = new xl_data();
        $xl->transaction(function (PDO $connection) use ($order, $items): void {
            $orderStatement = $connection->prepare(
                "INSERT INTO donhang
                    (ma_donhang, ten_khachhang, sdt, email, dia_chi, ghi_chu,
                     phuong_thuc_thanh_toan, ma_voucher, tien_giam, tong_tien)
                 VALUES (:ma_donhang, :ten_khachhang, :sdt, :email, :dia_chi, :ghi_chu,
                         :phuong_thuc_thanh_toan, :ma_voucher, :tien_giam, :tong_tien)"
            );
            $orderStatement->execute([
                ':ma_donhang' => $order['ma_donhang'],
                ':ten_khachhang' => $order['ten_khachhang'],
                ':sdt' => $order['sdt'],
                ':email' => $order['email'] !== '' ? $order['email'] : null,
                ':dia_chi' => $order['dia_chi'],
                ':ghi_chu' => $order['ghi_chu'] !== '' ? $order['ghi_chu'] : null,
                ':phuong_thuc_thanh_toan' => $order['phuong_thuc_thanh_toan'],
                ':ma_voucher' => $order['ma_voucher'] !== '' ? $order['ma_voucher'] : null,
                ':tien_giam' => $order['tien_giam'],
                ':tong_tien' => $order['total'],
            ]);
            $orderId = (int) $connection->lastInsertId();
            $itemStatement = $connection->prepare(
                "INSERT INTO chitiet_donhang
                    (donhang_id, id_sp, ten_sanpham, don_gia, so_luong, thanh_tien)
                 VALUES (:donhang_id, :id_sp, :ten_sanpham, :don_gia, :so_luong, :thanh_tien)"
            );
            foreach ($items as $item) {
                $itemStatement->execute([
                    ':donhang_id' => $orderId,
                    ':id_sp' => (int) $item['id_sp'],
                    ':ten_sanpham' => $item['Name'],
                    ':don_gia' => $item['Price'],
                    ':so_luong' => (int) $item['quantity'],
                    ':thanh_tien' => $item['subtotal'],
                ]);
            }

            $stockStatement = $connection->prepare(
                "UPDATE sanpham
                 SET Mount = Mount - :quantity
                 WHERE id_sp = :id_sp AND Mount >= :quantity"
            );
            foreach ($items as $item) {
                $stockStatement->execute([
                    ':quantity' => (int) $item['quantity'],
                    ':id_sp' => (int) $item['id_sp'],
                ]);
                if ($stockStatement->rowCount() !== 1) {
                    throw new RuntimeException(
                        'Sản phẩm "' . $item['Name'] . '" không còn đủ số lượng trong kho.'
                    );
                }
            }
        });
    }

    public function getAll(): array
    {
        $xl = new xl_data();
        return $xl->readitem(
            "SELECT d.*, GROUP_CONCAT(
                CONCAT(ct.ten_sanpham, ' (', ct.so_luong, ')')
                ORDER BY ct.id SEPARATOR ', '
             ) AS san_pham
             FROM donhang d
             LEFT JOIN chitiet_donhang ct ON ct.donhang_id = d.id
             GROUP BY d.id
             ORDER BY d.ngay_dat DESC"
        );
    }

    public function updateStatus(int $id, string $status): void
    {
        $xl = new xl_data();
        $xl->executePrepared(
            "UPDATE donhang SET trang_thai = :trang_thai WHERE id = :id",
            [':trang_thai' => $status, ':id' => $id]
        );
    }
}
