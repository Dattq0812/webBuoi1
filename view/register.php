<div class="container">
    <div class="card" style="max-width:520px; margin:35px auto;">
        <h1>Tạo tài khoản khách hàng</h1>
        <p class="subtitle">Đăng ký bằng email hoặc số điện thoại để theo dõi đơn hàng.</p>
        <?php if ($registerError !== ''): ?><p style="color:#b91c1c;"><?php echo htmlspecialchars($registerError, ENT_QUOTES, 'UTF-8'); ?></p><?php endif; ?>
        <form method="post" action="index.php?act=register">
            <label>Họ và tên</label><input name="ho_ten" required><br><br>
            <label>Số điện thoại</label><input name="sdt" type="tel"><br><br>
            <label>Email</label><input name="email" type="email"><br><br>
            <label>Mật khẩu</label><input name="password" type="password" minlength="8" required><br><br>
            <label>Xác nhận mật khẩu</label><input name="password_confirmation" type="password" minlength="8" required><br><br>
            <button type="submit">Đăng ký</button>
            <a class="btn" style="margin-left:8px; background:#64748b;" href="index.php?act=login">Đăng nhập</a>
        </form>
    </div>
</div>
