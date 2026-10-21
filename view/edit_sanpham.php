<?php
$product = $product ?? [];
$danhmuc = $danhmuc ?? [];
$currentImage = basename((string) ($product['image'] ?? ''));
$currentImageFile = __DIR__ . '/images/' . $currentImage;
?>

<div class="container">
    <div class="page-heading">
        <div>
            <h1>Chỉnh sửa sản phẩm</h1>
            <p class="subtitle">Cập nhật thông tin sản phẩm #<?php echo (int) ($product['id_sp'] ?? 0); ?>.</p>
        </div>
        <a class="btn" href="index.php?act=hienthiSP">Quay lại danh sách</a>
    </div>

    <div class="card" style="max-width:760px;">
        <form action="index.php?act=update_sp" method="post" enctype="multipart/form-data">
            <input type="hidden" name="id_sp" value="<?php echo (int) $product['id_sp']; ?>">

            <label>Danh mục</label>
            <select name="id_danhmuc" required>
                <?php foreach ($danhmuc as $category): ?>
                    <option value="<?php echo (int) $category['id']; ?>"
                        <?php echo (int) $category['id'] === (int) $product['id_danhmuc'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select><br><br>

            <label>Tên sản phẩm</label>
            <input type="text" name="name" required
                   value="<?php echo htmlspecialchars($product['Name'], ENT_QUOTES, 'UTF-8'); ?>"><br><br>

            <label>Giá</label>
            <input type="number" name="price" min="1" required
                   value="<?php echo htmlspecialchars($product['Price'], ENT_QUOTES, 'UTF-8'); ?>"><br><br>

            <label>Số lượng</label>
            <input type="number" name="mount" min="0" required
                   value="<?php echo htmlspecialchars($product['Mount'], ENT_QUOTES, 'UTF-8'); ?>"><br><br>

            <label>Giảm giá</label>
            <select name="sale" required>
                <?php foreach ([0, 10, 20, 30, 50] as $sale): ?>
                    <option value="<?php echo $sale; ?>" <?php echo (int) $product['Sale'] === $sale ? 'selected' : ''; ?>>
                        <?php echo $sale; ?>%
                    </option>
                <?php endforeach; ?>
            </select><br><br>

            <label>Mô tả</label>
            <textarea name="decribe" rows="5" required><?php echo htmlspecialchars($product['Decribe'], ENT_QUOTES, 'UTF-8'); ?></textarea><br><br>

            <label>Ảnh mới <small>(để trống nếu muốn giữ ảnh hiện tại)</small></label>
            <input type="file" name="image" accept="image/*"><br>
            <?php if ($currentImage !== '' && is_file($currentImageFile)): ?>
                <p class="subtitle">Ảnh hiện tại: <?php echo htmlspecialchars($currentImage, ENT_QUOTES, 'UTF-8'); ?></p>
                <img src="view/images/<?php echo rawurlencode($currentImage); ?>" alt="Ảnh hiện tại" width="120">
            <?php elseif ($currentImage !== ''): ?>
                <p class="empty">Không tìm thấy file ảnh hiện tại: <?php echo htmlspecialchars($currentImage, ENT_QUOTES, 'UTF-8'); ?></p>
            <?php endif; ?>
            <br>
            <button type="submit">Lưu thay đổi</button>
        </form>
    </div>
</div>
