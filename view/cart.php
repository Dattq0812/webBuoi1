 <?php
$cartItems = $cartItems ?? [];
$cartTotal = $cartTotal ?? 0;
?>

<div class="container">
    <div class="page-heading">
        <div>
            <h1>Giỏ hàng</h1>
            <p class="subtitle">Kiểm tra sản phẩm, số lượng và giá trị đơn hàng trước khi đặt.</p>
        </div>
        <a class="btn" href="index.php?act=home">Tiếp tục mua sắm</a>
    </div>

    <?php if (empty($cartItems)): ?>
        <div class="card empty">
            <h2>Giỏ hàng đang trống</h2>
            <p>Hãy chọn sản phẩm bạn muốn mua để bắt đầu.</p>
            <a class="btn" href="index.php?act=home">Xem sản phẩm</a>
        </div>
    <?php else: ?>
        <form method="post" action="index.php?act=updatecart">
            <div style="overflow-x:auto;">
                <table>
                    <thead><tr><th>Sản phẩm</th><th>Đơn giá</th><th>Số lượng</th><th>Thành tiền</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($cartItems as $item): ?>
                        <tr>
                            <td style="min-width:260px;">
                                <div style="display:flex; align-items:center; gap:12px;">
                                    <?php if ($item['imageExists']): ?>
                                        <img src="view/images/<?php echo rawurlencode($item['image']); ?>" alt="" width="70" height="90" style="object-fit:cover; border-radius:8px;">
                                    <?php endif; ?>
                                    <a href="index.php?act=chitietsp&amp;idsp=<?php echo (int) $item['id_sp']; ?>">
                                        <?php echo htmlspecialchars($item['Name'], ENT_QUOTES, 'UTF-8'); ?>
                                    </a>
                                </div>
                            </td>
                            <td><?php echo number_format($item['Price'], 0, ',', '.'); ?>đ</td>
                            <td>
                                <input type="number" name="quantities[<?php echo (int) $item['id_sp']; ?>]"
                                       min="0" max="<?php echo (int) $item['Mount']; ?>"
                                       value="<?php echo (int) $item['quantity']; ?>" style="width:90px;">
                            </td>
                            <td><strong><?php echo number_format($item['subtotal'], 0, ',', '.'); ?>đ</strong></td>
                            <td><a class="btn-delete" href="index.php?act=removecart&amp;idsp=<?php echo (int) $item['id_sp']; ?>">Xóa</a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div style="display:flex; justify-content:space-between; align-items:center; margin-top:18px; gap:15px; flex-wrap:wrap;">
                <button type="submit">Cập nhật giỏ hàng</button>
                <div style="font-size:20px;">Tạm tính: <strong style="color:var(--primary);"><?php echo number_format($cartTotal, 0, ',', '.'); ?>đ</strong></div>
            </div>
        </form>
        <div style="text-align:right; margin-top:18px;">
            <a class="btn" href="index.php?act=checkout">Tiến hành đặt hàng</a>
        </div>
    <?php endif; ?>
</div>
