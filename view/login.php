<div class="container">
    <div class="card" style="max-width:460px; margin:35px auto;">
        <h1>Đăng nhập quản lý</h1>
        <p class="subtitle">Đăng nhập để sử dụng khu vực quản trị TechZone.</p>
        <?php if (!empty($loginError)): ?>
            <p style="padding:10px; color:#b91c1c; background:#fef2f2; border-radius:8px;">
                <?php echo htmlspecialchars($loginError, ENT_QUOTES, 'UTF-8'); ?>
            </p>
        <?php endif; ?>
        <form method="post" action="index.php?act=login">
            <label>Tên đăng nhập</label>
            <input type="text" name="username" required autofocus><br><br>
            <label>Mật khẩu</label>
            <input type="password" name="password" required><br><br>
            <button type="submit">Đăng nhập</button>
            <a class="btn" style="margin-left:8px; background:#64748b;" href="index.php?act=home">Quay lại cửa hàng</a>
        </form>
    </div>
</div>
