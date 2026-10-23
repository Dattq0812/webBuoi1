<?php
$danhmuc = $danhmuc ?? [];
$danhsach = $danhsach ?? [];
$showAddForm = $showAddForm ?? false;
$nextProductId = $nextProductId ?? 1;
?>

<div class="container">
    <div class="page-heading">
        <div>
            <h1>Quản lý sản phẩm</h1>
            <p class="subtitle">Theo dõi và quản lý các thiết bị công nghệ trong cửa hàng.</p>
        </div>
        <?php if (!$showAddForm): ?>
            <a class="btn" href="index.php?act=addsp">+ Thêm sản phẩm</a>
        <?php endif; ?>
    </div>

<?php if ($showAddForm): ?>
<div class="card" style="max-width:760px; margin-bottom:28px;">
    <div class="page-heading">
        <div>
            <h2>Thêm sản phẩm mới</h2>
            <p class="subtitle">Nhập thông tin sản phẩm để đưa vào kho.</p>
        </div>
        <a class="btn" href="index.php?act=hienthiSP">Hủy</a>
    </div>

<form id="product-add-form" action="index.php?act=xl_themsp" method="post" enctype="multipart/form-data">
    Danh mục:<br>
    <select name="id_danhmuc" required>
        <?php foreach ($danhmuc as $rc): ?>
            <option value="<?php echo (int) $rc['id']; ?>">
                <?php echo htmlspecialchars($rc['name'], ENT_QUOTES, 'UTF-8'); ?>
            </option>
        <?php endforeach; ?>
    </select><br><br>

    Mã sản phẩm:<br>
    <input type="number" name="id_sp" value="<?php echo (int) $nextProductId; ?>" readonly>
    <br><span class="subtitle">Mã được tự động tạo theo số thứ tự sản phẩm tiếp theo.</span><br><br>

    Tên sản phẩm:<br>
    <input id="product-name-input" type="text" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
    <br><span style="color: red;"><?php echo htmlspecialchars($name_er ?? '', ENT_QUOTES, 'UTF-8'); ?></span><br>
    <span id="product-duplicate-message" style="color:var(--danger);"></span><br>

    Giá:<br>
    <input type="number" min="1" name="price" value="<?php echo htmlspecialchars($_POST['price'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
    <br><span style="color: red;"><?php echo htmlspecialchars($price_er ?? '', ENT_QUOTES, 'UTF-8'); ?></span><br>

    Số lượng:<br>
    <input type="number" min="0" name="mount" value="<?php echo htmlspecialchars($_POST['mount'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
    <br><span style="color: red;"><?php echo htmlspecialchars($mount_er ?? '', ENT_QUOTES, 'UTF-8'); ?></span><br>

    Hình ảnh:<br>
    <input type="file" name="image" accept="image/*" required>
    <br><span style="color: red;"><?php echo htmlspecialchars($image_er ?? '', ENT_QUOTES, 'UTF-8'); ?></span><br>

    Giảm giá:
    <select name="sale" required>
        <option value="">Chọn mức giảm</option>
        <?php foreach ([0, 10, 20, 30, 50] as $sale): ?>
            <option value="<?php echo $sale; ?>" <?php echo (isset($_POST['sale']) && (int) $_POST['sale'] === $sale) ? 'selected' : ''; ?>>
                <?php echo $sale; ?>%
            </option>
        <?php endforeach; ?>
    </select>
    <br><span style="color: red;"><?php echo htmlspecialchars($sale_er ?? '', ENT_QUOTES, 'UTF-8'); ?></span><br>

    Mô tả:<br>
    <textarea name="decribe" rows="4" cols="50" required><?php echo htmlspecialchars($_POST['decribe'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
    <br><span style="color: red;"><?php echo htmlspecialchars($decribe_er ?? '', ENT_QUOTES, 'UTF-8'); ?></span><br>

    <button type="submit">Thêm sản phẩm</button>
</form>
</div>
<?php endif; ?>

<div class="page-heading" style="margin-top:30px;">
    <div>
        <h2>Sản phẩm hiện có</h2>
        <p class="subtitle"><?php echo count($danhsach); ?> sản phẩm trong danh sách.</p>
    </div>
    <?php if ($showAddForm): ?>
    <script>
    const productForm = document.getElementById('product-add-form');
    const productInput = document.getElementById('product-name-input');
    const productMessage = document.getElementById('product-duplicate-message');
    let productDuplicate = false;
    productInput.addEventListener('input', async () => {
        if (productInput.value.trim() === '') {
            productDuplicate = false;
            productMessage.textContent = '';
            return;
        }
        try {
            const params = new URLSearchParams({
                act: 'check_duplicate',
                field: 'product_name',
                value: productInput.value
            });
            const response = await fetch('index.php?' + params.toString());
            if (!response.ok) throw new Error();
            productDuplicate = (await response.json()).duplicate === true;
            productMessage.textContent = productDuplicate ? 'Tên sản phẩm đã tồn tại.' : '';
        } catch (error) {
            productDuplicate = false;
            productMessage.textContent = 'Không thể kiểm tra dữ liệu lúc này.';
        }
    });
    productForm.addEventListener('submit', (event) => {
        if (productDuplicate) event.preventDefault();
    });
    </script>
    <?php endif; ?>
    <?php if ($showAddForm): ?>
        <a class="btn" href="index.php?act=hienthiSP">Quay lại danh sách</a>
    <?php endif; ?>
</div>
<div style="overflow-x:auto;">
<table>
    <thead>
        <tr>
            <th>Danh mục</th>
            <th>Mã sản phẩm</th>
            <th>Hình ảnh</th>
            <th>Tên sản phẩm</th>
            <th>Giá</th>
            <th>Ngày nhập</th>
            <th>Mô tả</th>
            <th>Số lượng</th>
            <th>Giảm giá</th>
            <th>Edit</th>
            <th>Delete</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($danhsach)): ?>
            <?php foreach ($danhsach as $rc): ?>
                <tr>
                    <td><?php echo (int) $rc['id_danhmuc']; ?></td>
                    <td><?php echo (int) $rc['id_sp']; ?></td>
                    <td>
                        <?php
                        $imageName = basename((string) ($rc['image'] ?? ''));
                        $imageFile = __DIR__ . '/images/' . $imageName;
                        ?>
                        <?php if ($imageName !== '' && is_file($imageFile)): ?>
                            <img height="100" width="100"
                                 src="view/images/<?php echo rawurlencode($imageName); ?>"
                                 alt="<?php echo htmlspecialchars($rc['Name'], ENT_QUOTES, 'UTF-8'); ?>"
                                 onerror="this.hidden=true; this.nextElementSibling.hidden=false;">
                            <span class="empty" hidden>Không tải được ảnh</span>
                        <?php else: ?>
                            <span class="empty">Chưa có ảnh</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($rc['Name'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($rc['Price'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($rc['Date_import'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($rc['Decribe'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($rc['Mount'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($rc['Sale'], ENT_QUOTES, 'UTF-8'); ?>%</td>
                    <td>
                        <a class="btn" href="index.php?act=editsp&amp;idsp=<?php echo (int) $rc['id_sp']; ?>">
                            Edit
                        </a>
                    </td>
                    <td>
                        <a href="index.php?act=deletesp&amp;idsp_del=<?php echo (int) $rc['id_sp']; ?>"
                           onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này?');">
                            Delete
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td class="empty" colspan="11">Hiện chưa có sản phẩm nào.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
</div>
</div>
