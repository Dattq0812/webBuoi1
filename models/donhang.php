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
                $movementStatement = $connection->prepare(
                    "INSERT INTO inventory_movements
                        (product_id, movement_type, quantity, reference_type, reference_id)
                     VALUES (:product_id, 'export', :quantity, 'order', :reference_id)"
                );
                $movementStatement->execute([
                    ':product_id' => (int) $item['id_sp'],
                    ':quantity' => (int) $item['quantity'],
                    ':reference_id' => $orderId,
                ]);
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
             ORDER BY d.ngay_dat DESC, d.id DESC"
        );
    }

    public function getRevenueStatistics(): array
    {
        $xl = new xl_data();
        return $xl->readPrepared(
            "SELECT
                COALESCE(SUM(CASE
                    WHEN YEARWEEK(ngay_dat, 1) = YEARWEEK(CURDATE(), 1)
                    THEN tong_tien ELSE 0 END), 0) AS week_revenue,
                COALESCE(SUM(CASE
                    WHEN YEAR(ngay_dat) = YEAR(CURDATE())
                     AND MONTH(ngay_dat) = MONTH(CURDATE())
                    THEN tong_tien ELSE 0 END), 0) AS month_revenue,
                COALESCE(SUM(CASE
                    WHEN YEAR(ngay_dat) = YEAR(CURDATE())
                    THEN tong_tien ELSE 0 END), 0) AS year_revenue,
                COALESCE(SUM(CASE
                    WHEN YEARWEEK(ngay_dat, 1) = YEARWEEK(CURDATE(), 1)
                    THEN 1 ELSE 0 END), 0) AS week_orders,
                COALESCE(SUM(CASE
                    WHEN YEAR(ngay_dat) = YEAR(CURDATE())
                     AND MONTH(ngay_dat) = MONTH(CURDATE())
                    THEN 1 ELSE 0 END), 0) AS month_orders,
                COALESCE(SUM(CASE
                    WHEN YEAR(ngay_dat) = YEAR(CURDATE())
                    THEN 1 ELSE 0 END), 0) AS year_orders
             FROM donhang
             WHERE trang_thai <> 'da_huy'"
        );
    }

    public function getQuarterlyStatistics(): array
    {
        $xl = new xl_data();
        $rows = $xl->readPreparedAll(
            "SELECT
                QUARTER(d.ngay_dat) AS quarter_number,
                COALESCE(SUM(CASE WHEN d.trang_thai NOT IN ('da_huy', 'hoan_tra')
                    THEN d.tong_tien ELSE 0 END), 0) AS revenue,
                COALESCE(SUM(CASE WHEN d.trang_thai NOT IN ('da_huy', 'hoan_tra')
                    THEN ct.so_luong ELSE 0 END), 0) AS exported_quantity
             FROM donhang d
             LEFT JOIN chitiet_donhang ct ON ct.donhang_id = d.id
             WHERE YEAR(d.ngay_dat) = YEAR(CURDATE())
             GROUP BY QUARTER(d.ngay_dat)
             ORDER BY quarter_number"
        );
        $imports = $xl->readPreparedAll(
            "SELECT QUARTER(created_at) AS quarter_number, SUM(quantity) AS imported_quantity
             FROM inventory_movements
             WHERE movement_type = 'import' AND YEAR(created_at) = YEAR(CURDATE())
             GROUP BY QUARTER(created_at)
             ORDER BY quarter_number"
        );
        $importByQuarter = [];
        foreach ($imports as $row) {
            $importByQuarter[(int) $row['quarter_number']] = (int) $row['imported_quantity'];
        }
        $statistics = [];
        for ($quarter = 1; $quarter <= 4; $quarter++) {
            $statistics[] = [
                'quarter' => $quarter,
                'revenue' => 0,
                'exported_quantity' => 0,
                'imported_quantity' => $importByQuarter[$quarter] ?? 0,
            ];
        }
        foreach ($rows as $row) {
            $quarter = (int) $row['quarter_number'];
            $statistics[$quarter - 1]['revenue'] = (float) $row['revenue'];
            $statistics[$quarter - 1]['exported_quantity'] = (int) $row['exported_quantity'];
        }
        return $statistics;
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
             ORDER BY d.ngay_dat DESC, d.id DESC",
            [':customer_id' => $customerId]
        );
    }

    public function getDetail(int $id, ?int $customerId = null): array
    {
        $xl = new xl_data();
        $customerClause = $customerId === null ? '' : ' AND d.customer_id = :customer_id';
        $params = [':id' => $id];
        if ($customerId !== null) {
            $params[':customer_id'] = $customerId;
        }

        $order = $xl->readPrepared(
            "SELECT d.*
             FROM donhang d
             WHERE d.id = :id" . $customerClause . "
             LIMIT 1",
            $params
        );
        if (!$order) {
            return [];
        }

        $order['items'] = $xl->readPreparedAll(
            "SELECT ct.*, sp.image
             FROM chitiet_donhang ct
             LEFT JOIN sanpham sp ON sp.id_sp = ct.id_sp
             WHERE ct.donhang_id = :donhang_id
             ORDER BY ct.id",
            [':donhang_id' => $id]
        );
        return $order;
    }

    public function updateStatus(int $id, string $currentStatus, string $nextStatus): bool
    {
        $xl = new xl_data();
        if ($nextStatus !== 'hoan_tra') {
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

        $updated = false;
        $xl->transaction(function (PDO $connection) use ($id, $currentStatus, $nextStatus, &$updated): void {
            $orderStatement = $connection->prepare(
                "SELECT id
                 FROM donhang
                 WHERE id = :id AND trang_thai = :current_status
                 FOR UPDATE"
            );
            $orderStatement->execute([
                ':id' => $id,
                ':current_status' => $currentStatus,
            ]);
            if (!$orderStatement->fetch(PDO::FETCH_ASSOC)) {
                return;
            }

            $itemsStatement = $connection->prepare(
                "SELECT id_sp, so_luong
                 FROM chitiet_donhang
                 WHERE donhang_id = :donhang_id"
            );
            $itemsStatement->execute([':donhang_id' => $id]);
            $items = $itemsStatement->fetchAll(PDO::FETCH_ASSOC);
            $restoreStock = $connection->prepare(
                "UPDATE sanpham
                 SET Mount = Mount + :quantity
                 WHERE id_sp = :id_sp"
            );
            foreach ($items as $item) {
                $restoreStock->execute([
                    ':quantity' => (int) $item['so_luong'],
                    ':id_sp' => (int) $item['id_sp'],
                ]);
                $movementStatement = $connection->prepare(
                    "INSERT INTO inventory_movements
                        (product_id, movement_type, quantity, reference_type, reference_id)
                     VALUES (:product_id, 'import', :quantity, 'order_return', :reference_id)"
                );
                $movementStatement->execute([
                    ':product_id' => (int) $item['id_sp'],
                    ':quantity' => (int) $item['so_luong'],
                    ':reference_id' => $id,
                ]);
            }

            $statusStatement = $connection->prepare(
                "UPDATE donhang
                 SET trang_thai = :next_status
                 WHERE id = :id AND trang_thai = :current_status"
            );
            $statusStatement->execute([
                ':next_status' => $nextStatus,
                ':id' => $id,
                ':current_status' => $currentStatus,
            ]);
            $updated = $statusStatement->rowCount() === 1;
            if (!$updated) {
                throw new RuntimeException('Không thể cập nhật trạng thái trả hàng.');
            }
        });
        return $updated;
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
                $movementStatement = $connection->prepare(
                    "INSERT INTO inventory_movements
                        (product_id, movement_type, quantity, reference_type, reference_id)
                     VALUES (:product_id, 'import', :quantity, 'order_cancel', :reference_id)"
                );
                $movementStatement->execute([
                    ':product_id' => (int) $item['id_sp'],
                    ':quantity' => (int) $item['quantity'],
                    ':reference_id' => $id,
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
