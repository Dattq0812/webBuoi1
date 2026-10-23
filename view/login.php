<div class="container">
    <div class="card" style="max-width:460px; margin:35px auto;">
        <h1>Đăng nhập TechZone</h1>
        <p class="subtitle">Khách hàng đăng nhập bằng email hoặc số điện thoại.</p>
        <?php if (!empty($loginError)): ?>
            <p style="padding:10px; color:#b91c1c; background:#fef2f2; border-radius:8px;">
                <?php echo htmlspecialchars($loginError, ENT_QUOTES, 'UTF-8'); ?>
            </p>
        <?php endif; ?>
        <form method="post" action="index.php?act=login">
            <label>Email hoặc số điện thoại</label>
            <input type="text" name="username" required autofocus><br><br>
            <label>Mật khẩu</label>
            <input type="password" name="password" required><br><br>
            <button type="submit">Đăng nhập</button>
            <a class="btn" style="margin-left:8px; background:#0f766e;" href="index.php?act=register">Tạo tài khoản</a>
            <a class="btn" style="margin-left:8px; background:#64748b;" href="index.php?act=forgot_password">Quên mật khẩu</a>
            <a class="btn" style="margin-left:8px; background:#64748b;" href="index.php?act=home">Quay lại cửa hàng</a>
        </form>
    </div>
</div>
