<?php
require_once __DIR__ . "/xl_database.php";

class donhang
{
    public function __construct()
    {
        (new xl_data())->executePrepared(
            "CREATE TABLE IF NOT EXISTS order_reviews (
                id INT AUTO_INCREMENT PRIMARY KEY,
                order_id INT NOT NULL UNIQUE,
                customer_id INT NOT NULL,
                rating TINYINT NOT NULL,
                comment TEXT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT chk_order_review_rating CHECK (rating BETWEEN 1 AND 5)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );
    }

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
            "SELECT d.*,
                (SELECT GROUP_CONCAT(
                    CONCAT(ct.ten_sanpham, ' (', ct.so_luong, ')')
                    ORDER BY ct.id SEPARATOR ', '
                 )
                 FROM chitiet_donhang ct
                 WHERE ct.donhang_id = d.id) AS san_pham
             FROM donhang d
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
        $revenueRows = $xl->readPreparedAll(
            "SELECT QUARTER(ngay_dat) AS quarter_number,
                    COALESCE(SUM(tong_tien), 0) AS revenue
             FROM donhang
             WHERE YEAR(ngay_dat) = YEAR(CURDATE())
               AND trang_thai NOT IN ('da_huy', 'hoan_tra')
             GROUP BY QUARTER(ngay_dat)"
        );
        $exportRows = $xl->readPreparedAll(
            "SELECT QUARTER(d.ngay_dat) AS quarter_number,
                    COALESCE(SUM(ct.so_luong), 0) AS exported_quantity
             FROM donhang d
             INNER JOIN chitiet_donhang ct ON ct.donhang_id = d.id
             WHERE YEAR(d.ngay_dat) = YEAR(CURDATE())
               AND d.trang_thai NOT IN ('da_huy', 'hoan_tra')
             GROUP BY QUARTER(d.ngay_dat)"
        );
        $revenueByQuarter = [];
        foreach ($revenueRows as $row) {
            $revenueByQuarter[(int) $row['quarter_number']] = (float) $row['revenue'];
        }
        $exportByQuarter = [];
        foreach ($exportRows as $row) {
            $exportByQuarter[(int) $row['quarter_number']] = (int) $row['exported_quantity'];
        }
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
                'revenue' => $revenueByQuarter[$quarter] ?? 0,
                'exported_quantity' => $exportByQuarter[$quarter] ?? 0,
                'imported_quantity' => $importByQuarter[$quarter] ?? 0,
            ];
        }
        return $statistics;
    }

    public function getByCustomer(int $customerId): array
    {
        $xl = new xl_data();
        return $xl->readPreparedAll(
            "SELECT d.*,
                (SELECT GROUP_CONCAT(
                    CONCAT(ct.ten_sanpham, ' (', ct.so_luong, ')')
                    ORDER BY ct.id SEPARATOR ', '
                 )
                 FROM chitiet_donhang ct
                 WHERE ct.donhang_id = d.id) AS san_pham
             FROM donhang d
             WHERE d.customer_id = :customer_id
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
        $order['review'] = $xl->readPrepared(
            "SELECT rating, comment, created_at
             FROM order_reviews
             WHERE order_id = :order_id
             LIMIT 1",
            [':order_id' => $id]
        );
        return $order;
    }

    public function completeByCustomer(int $id, int $customerId, int $rating, string $comment): void
    {
        if ($rating < 1 || $rating > 5) {
            throw new InvalidArgumentException('Đánh giá phải từ 1 đến 5 sao.');
        }
        $comment = trim($comment);
        if (mb_strlen($comment) > 2000) {
            throw new InvalidArgumentException('Cảm nhận không được vượt quá 2000 ký tự.');
        }

        (new xl_data())->transaction(function (PDO $connection) use ($id, $customerId, $rating, $comment): void {
            $orderStatement = $connection->prepare(
                "SELECT id FROM donhang
                 WHERE id = :id AND customer_id = :customer_id AND trang_thai = 'da_giao'
                 FOR UPDATE"
            );
            $orderStatement->execute([':id' => $id, ':customer_id' => $customerId]);
            if (!$orderStatement->fetch(PDO::FETCH_ASSOC)) {
                throw new RuntimeException('Đơn hàng không còn ở trạng thái có thể hoàn tất.');
            }

            $reviewStatement = $connection->prepare(
                "INSERT INTO order_reviews (order_id, customer_id, rating, comment)
                 VALUES (:order_id, :customer_id, :rating, :comment)"
            );
            $reviewStatement->execute([
                ':order_id' => $id,
                ':customer_id' => $customerId,
                ':rating' => $rating,
                ':comment' => $comment !== '' ? $comment : null,
            ]);

            $statusStatement = $connection->prepare(
                "UPDATE donhang SET trang_thai = 'hoan_tat'
                 WHERE id = :id AND customer_id = :customer_id AND trang_thai = 'da_giao'"
            );
            $statusStatement->execute([':id' => $id, ':customer_id' => $customerId]);
            if ($statusStatement->rowCount() !== 1) {
                throw new RuntimeException('Không thể hoàn tất đơn hàng.');
            }
        });
    }

    public function returnByCustomer(int $id, int $customerId, string $reason): array
    {
        $reason = trim($reason);
        if ($reason === '' || mb_strlen($reason) > 1000) {
            throw new InvalidArgumentException('Vui lòng nhập lý do hoàn trả hợp lệ.');
        }
        $returnedOrder = [];
        (new xl_data())->transaction(function (PDO $connection) use ($id, $customerId, $reason, &$returnedOrder): void {
            $orderStatement = $connection->prepare(
                "SELECT * FROM donhang
                 WHERE id = :id AND customer_id = :customer_id AND trang_thai = 'da_giao'
                 FOR UPDATE"
            );
            $orderStatement->execute([':id' => $id, ':customer_id' => $customerId]);
            $order = $orderStatement->fetch(PDO::FETCH_ASSOC);
            if (!$order) {
                throw new RuntimeException('Đơn hàng không còn ở trạng thái có thể hoàn trả.');
            }
            $update = $connection->prepare(
                "UPDATE donhang SET trang_thai = 'yeu_cau_hoan_tra', ly_do_huy = :reason
                 WHERE id = :id AND customer_id = :customer_id AND trang_thai = 'da_giao'"
            );
            $update->execute([':reason' => $reason, ':id' => $id, ':customer_id' => $customerId]);
            if ($update->rowCount() !== 1) {
                throw new RuntimeException('Không thể cập nhật trạng thái hoàn trả.');
            }
            $order['ly_do_huy'] = $reason;
            $returnedOrder = $order;
        });
        return $returnedOrder;
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
