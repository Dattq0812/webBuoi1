<div class="container">
    <div class="card" style="max-width:520px; margin:35px auto;">
        <h1>Đặt lại mật khẩu</h1>
        <?php if ($resetError !== ''): ?><p style="color:#b91c1c;"><?php echo htmlspecialchars($resetError, ENT_QUOTES, 'UTF-8'); ?></p><?php endif; ?>
        <?php if ($resetError === ''): ?>
            <form method="post" action="index.php?act=reset_password">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>">
                <label>Mật khẩu mới</label><input name="password" type="password" minlength="8" required><br><br>
                <label>Xác nhận mật khẩu</label><input name="password_confirmation" type="password" minlength="8" required><br><br>
                <button type="submit">Lưu mật khẩu</button>
            </form>
        <?php endif; ?>
    </div>
</div>
