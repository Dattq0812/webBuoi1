<?php

require_once __DIR__ . "/danhmuc.php";
require_once __DIR__ . "/../models/sanpham.php";
require_once __DIR__ . "/../models/donhang.php";
require_once __DIR__ . "/../models/duplicate_checker.php";
require_once __DIR__ . "/../models/khachhang.php";

class controller
{
    public function hienthiDM(): array
    {
        $dm = new danhmuc();

        return $dm->get_danhmuc();
    }

    public function add_danhmuc(danhmuc $dm): void
    {
        $danhmuc = new danhmuc();

        $danhmuc->add_Danhmuc($dm);
    }

    public function delete_dm(danhmuc $dm): void
    {
        $danhmuc = new danhmuc();

        $danhmuc->delete_dm($dm);
    }

    public function update_dm(danhmuc $dm): void
    {
        $danhmuc = new danhmuc();

        $danhmuc->update_dm($dm);
    }

    // Gọi hàm lấy danh sách sản phẩm từ Model
    public function hienthisanpham(): array {
        $sp = new sanpham();
        return $sp->getallsanpham();
    }

    public function getBestSellingProducts(int $limit = 6): array {
        return (new sanpham())->getBestSelling($limit);
    }

    public function getNewestProducts(int $limit = 6): array {
        return (new sanpham())->getNewest($limit);
    }

    public function getNextProductId(): int {
        return (new sanpham())->getNextId();
    }

    // Gọi hàm thêm sản phẩm từ Model[cite: 10]
    public function themsp(sanpham $sp): void {
        $sp->themsp($sp);
    }

    public function deletesp(sanpham $sp): void {
        $sp->xoasp($sp);
    }

    public function getOneProduct(sanpham $sp): array {
        return $sp->motsp($sp);
    }

    public function updateProduct(sanpham $sp): void {
        $sp->capnhatsp($sp);
    }

    public function getCartItems(array $cart): array {
        $items = [];
        foreach ($cart as $id => $quantity) {
            $product = new sanpham();
            $product->setId((int) $id);
            $result = $product->motsp($product);
            if (!empty($result)) {
                $row = $result[0];
                $available = max(0, (int) $row['Mount']);
                $quantity = min(max(1, (int) $quantity), $available);
                if ($quantity > 0) {
                    $row['quantity'] = $quantity;
                    $row['subtotal'] = $quantity * (float) $row['Price'];
                    $row['image'] = basename((string) ($row['image'] ?? ''));
                    $row['imageExists'] = $row['image'] !== ''
                        && is_file(__DIR__ . '/../view/images/' . $row['image']);
                    $items[] = $row;
                }
            }
        }
        return $items;
    }

    public function createOrder(array $order, array $items): void {
        $model = new donhang();
        $model->create($order, $items);
    }

    public function getOrders(): array {
        return (new donhang())->getAll();
    }

    public function updateOrderStatus(int $id, string $currentStatus, string $nextStatus): bool {
        return (new donhang())->updateStatus($id, $currentStatus, $nextStatus);
    }

    public function cancelOrder(int $id, string $reason): array {
        return (new donhang())->cancel($id, $reason);
    }

    public function cancelCustomerOrder(int $id, string $reason, int $customerId): array {
        return (new donhang())->cancel($id, $reason, $customerId);
    }

    public function getCustomerOrders(int $customerId): array {
        return (new donhang())->getByCustomer($customerId);
    }

    public function getOrderDetail(int $id, ?int $customerId = null): array {
        return (new donhang())->getDetail($id, $customerId);
    }

    public function isDuplicate(string $field, string $value, ?int $excludeId = null): bool {
        return (new duplicate_checker())->exists($field, $value, $excludeId);
    }
}
?>
