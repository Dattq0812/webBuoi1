<?php $danhmuc = $danhmuc ?? []; ?>

<div class="container">
    <div class="page-heading">
        <div>
            <h1>Quản lý danh mục</h1>
            <p class="subtitle">Phân loại các thiết bị công nghệ trong cửa hàng.</p>
        </div>
    </div>

    <div class="card">
        <form id="category-add-form" method="POST" action="index.php?act=add_danhmuc" style="display:flex; gap:10px; max-width:620px;">
            <input id="category-name-input" type="text" name="name" placeholder="Nhập tên danh mục mới" required>
            <button type="submit">+ Thêm danh mục</button>
            <span id="category-duplicate-message" style="color:var(--danger);"></span>
        </form>
    </div>

    <div style="overflow-x:auto; margin-top:22px;">
        <table>
            <thead>
                <tr><th>ID</th><th>Tên danh mục</th><th>Xóa</th><th>Chỉnh sửa</th></tr>
            </thead>
            <tbody>
                <?php if (!empty($danhmuc)): ?>
                    <?php foreach ($danhmuc as $dm): ?>
                        <tr>
                            <td><?php echo (int) $dm['id']; ?></td>
                            <td>
                                <span class="category-name"><?php echo htmlspecialchars($dm['name'], ENT_QUOTES, 'UTF-8'); ?></span>
                                <input class="edit-input" type="text" name="name"
                                       value="<?php echo htmlspecialchars($dm['name'], ENT_QUOTES, 'UTF-8'); ?>"
                                       form="edit-form-<?php echo (int) $dm['id']; ?>" hidden required>
                            </td>
                            <td>
                                <a class="btn-delete" href="index.php?act=delete_dm&amp;id_dm=<?php echo (int) $dm['id']; ?>"
                                   onclick="return confirm('Bạn có chắc chắn muốn xóa danh mục này?');">Xóa</a>
                            </td>
                            <td>
                                <form id="edit-form-<?php echo (int) $dm['id']; ?>" method="POST" action="index.php?act=update_dm">
                                    <input type="hidden" name="id_dm" value="<?php echo (int) $dm['id']; ?>">
                                </form>
                                <button type="button" class="btn-edit" onclick="editCategory(this)">Sửa</button>
                                <button type="submit" class="btn-save" form="edit-form-<?php echo (int) $dm['id']; ?>" hidden>Lưu</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td class="empty" colspan="4">Chưa có danh mục nào.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function editCategory(button) {
    const row = button.closest('tr');
    row.querySelector('.category-name').hidden = true;
    const input = row.querySelector('.edit-input');
    input.hidden = false;
    button.hidden = true;
    row.querySelector('.btn-save').hidden = false;
    input.focus();
}

async function checkDuplicate(field, value, excludeId = '') {
    const params = new URLSearchParams({act: 'check_duplicate', field, value});
    if (excludeId) params.set('exclude_id', excludeId);
    const response = await fetch('index.php?' + params.toString());
    if (!response.ok) throw new Error('Không thể kiểm tra dữ liệu.');
    return (await response.json()).duplicate === true;
}

const categoryForm = document.getElementById('category-add-form');
const categoryInput = document.getElementById('category-name-input');
const categoryMessage = document.getElementById('category-duplicate-message');
categoryInput.addEventListener('input', async () => {
    if (categoryInput.value.trim() === '') {
        categoryMessage.textContent = '';
        return;
    }
    try {
        categoryMessage.textContent = await checkDuplicate('category_name', categoryInput.value)
            ? 'Tên danh mục đã tồn tại.' : '';
    } catch (error) {
        categoryMessage.textContent = 'Không thể kiểm tra dữ liệu lúc này.';
    }
});
categoryForm.addEventListener('submit', (event) => {
    if (categoryMessage.textContent !== '') event.preventDefault();
});
</script>
