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
                    (customer_id, ma_donhang, ten_khachhang, sdt, email, dia_chi, ghi_chu,
                     phuong_thuc_thanh_toan, ma_voucher, tien_giam, tong_tien)
                 VALUES (:customer_id, :ma_donhang, :ten_khachhang, :sdt, :email, :dia_chi, :ghi_chu,
                         :phuong_thuc_thanh_toan, :ma_voucher, :tien_giam, :tong_tien)"
            );
            $orderStatement->execute([
                ':customer_id' => $order['customer_id'],
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
        return $xl->readPreparedAll(
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

    public function getByCustomer(int $customerId): array
    {
        $xl = new xl_data();
        return $xl->readPreparedAll(
            "SELECT d.*, GROUP_CONCAT(
                CONCAT(ct.ten_sanpham, ' (', ct.so_luong, ')')
                ORDER BY ct.id SEPARATOR ', '
             ) AS san_pham
             FROM donhang d
             LEFT JOIN chitiet_donhang ct ON ct.donhang_id = d.id
             WHERE d.customer_id = :customer_id
             GROUP BY d.id
             ORDER BY d.ngay_dat DESC",
            [':customer_id' => $customerId]
        );
    }

    public function updateStatus(int $id, string $currentStatus, string $nextStatus): bool
    {
        $xl = new xl_data();
        return $xl->executePrepared(
            "UPDATE donhang
             SET trang_thai = :next_status
             WHERE id = :id AND trang_thai = :current_status",
            [
                ':next_status' => $nextStatus,
                ':id' => $id,
                ':current_status' => $currentStatus,
            ]
        );
    }

    public function cancel(int $id, string $reason, ?int $customerId = null): array
    {
        $xl = new xl_data();
        $cancelledOrder = [];
        $xl->transaction(function (PDO $connection) use ($id, $reason, $customerId, &$cancelledOrder): void {
            $customerClause = $customerId === null ? '' : ' AND customer_id = :customer_id';
            $orderStatement = $connection->prepare(
                "SELECT * FROM donhang
             WHERE id = :id AND trang_thai " . ($customerId === null ? "IN ('cho_xac_nhan', 'da_xac_nhan')" : "= 'cho_xac_nhan'") . $customerClause . "
             FOR UPDATE"
            );
            $params = [':id' => $id];
            if ($customerId !== null) {
            $params[':customer_id'] = $customerId;
            }
            $orderStatement->execute($params);
            $order = $orderStatement->fetch(PDO::FETCH_ASSOC);
            if (!$order) {
                throw new RuntimeException('Đơn hàng đã chuyển sang đang giao hoặc không tồn tại.');
            }

            $itemsStatement = $connection->prepare(
                "SELECT id_sp, ten_sanpham AS Name, don_gia AS Price,
                        so_luong AS quantity, thanh_tien AS subtotal
                 FROM chitiet_donhang WHERE donhang_id = :donhang_id"
            );
            $itemsStatement->execute([':donhang_id' => $id]);
            $items = $itemsStatement->fetchAll(PDO::FETCH_ASSOC);

            $restoreStock = $connection->prepare(
                "UPDATE sanpham SET Mount = Mount + :quantity WHERE id_sp = :id_sp"
            );
            foreach ($items as $item) {
                $restoreStock->execute([
                    ':quantity' => (int) $item['quantity'],
                    ':id_sp' => (int) $item['id_sp'],
                ]);
            }

            $update = $connection->prepare(
                "UPDATE donhang SET trang_thai = 'da_huy', ly_do_huy = :reason
                 WHERE id = :id AND trang_thai IN ('cho_xac_nhan', 'da_xac_nhan')"
            );
            $update->execute([':reason' => $reason, ':id' => $id]);
            if ($update->rowCount() !== 1) {
                throw new RuntimeException('Trạng thái đơn hàng đã thay đổi, không thể hủy.');
            }

            $order['items'] = $items;
            $order['ly_do_huy'] = $reason;
            $cancelledOrder = $order;
        });
        return $cancelledOrder;
    }
}
