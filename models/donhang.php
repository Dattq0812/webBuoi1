<?php
require_once __DIR__ . "/xl_database.php";

class donhang
{
    public function create(array $order): void
    {
        $xl = new xl_data();
        $xl->executePrepared(
            "INSERT INTO donhang
                (ma_donhang, ten_khachhang, sdt, email, dia_chi, ghi_chu)
             VALUES (:ma_donhang, :ten_khachhang, :sdt, :email, :dia_chi, :ghi_chu)",
            [
                ':ma_donhang' => $order['ma_donhang'],
                ':ten_khachhang' => $order['ten_khachhang'],
                ':sdt' => $order['sdt'],
                ':email' => $order['email'] !== '' ? $order['email'] : null,
                ':dia_chi' => $order['dia_chi'],
                ':ghi_chu' => $order['ghi_chu'] !== '' ? $order['ghi_chu'] : null,
            ]
        );
    }
}
