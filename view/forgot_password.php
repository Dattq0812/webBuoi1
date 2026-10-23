<div class="container">
    <div class="card" style="max-width:520px; margin:35px auto;">
        <h1>Quên mật khẩu</h1>
        <p class="subtitle">Nhập email đã đăng ký để nhận liên kết đặt lại mật khẩu.</p>
        <?php if ($forgotMessage !== ''): ?><p style="color:#166534;"><?php echo htmlspecialchars($forgotMessage, ENT_QUOTES, 'UTF-8'); ?></p><?php endif; ?>
        <form method="post" action="index.php?act=forgot_password">
            <label>Email</label><input name="email" type="email" required><br><br>
            <button type="submit">Gửi hướng dẫn</button>
            <a class="btn" style="margin-left:8px; background:#64748b;" href="index.php?act=login">Quay lại</a>
        </form>
    </div>
</div>
