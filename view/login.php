<style>
    .login-page {
        min-height: 620px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 28px 0;
        background:
            radial-gradient(circle at 10% 10%, #dbeafe 0, transparent 35%),
            radial-gradient(circle at 90% 90%, #ccfbf1 0, transparent 35%);
    }
    .login-layout {
        display: grid;
        grid-template-columns: minmax(260px, 0.9fr) minmax(330px, 1.1fr);
        width: min(900px, 100%);
        overflow: hidden;
        background: #fff;
        border: 1px solid #dbe4f0;
        border-radius: 24px;
        box-shadow: 0 24px 60px #1e3a8a1a;
    }
    .login-intro {
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding: 48px 38px;
        color: #dbeafe;
        background: linear-gradient(145deg, #1d4ed8, #172554);
    }
    .login-intro .login-logo {
        margin-bottom: 30px;
        color: #fff;
        font-size: 30px;
        font-weight: 800;
    }
    .login-intro .login-logo span { color: #67e8f9; }
    .login-intro h2 { margin-bottom: 12px; color: #fff; font-size: 28px; line-height: 1.2; }
    .login-intro p { color: #bfdbfe; }
    .login-benefits { margin: 22px 0 0; padding: 0; list-style: none; }
    .login-benefits li { margin: 12px 0; color: #e0f2fe; }
    .login-benefits li::before { content: "✓"; display: inline-block; width: 25px; color: #67e8f9; font-weight: 800; }
    .login-form-panel { padding: 44px 42px; }
    .login-form-panel h1 { margin-bottom: 8px; font-size: 27px; }
    .login-form { margin-top: 28px; }
    .login-field { display: block; margin-bottom: 18px; color: #334155; font-weight: 700; }
    .login-field input { margin-top: 8px; padding: 13px 14px; background: #f8fafc; }
    .login-field input:focus { background: #fff; }
    .login-submit { width: 100%; padding: 13px 16px; font-size: 15px; }
    .login-actions { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 18px; }
    .login-actions .btn { flex: 1 1 140px; margin: 0 !important; padding: 10px 12px; text-align: center; font-size: 13px; }
    .login-error { padding: 12px 14px; color: #b91c1c; background: #fef2f2; border: 1px solid #fecaca; border-radius: 10px; }
    @media (max-width: 700px) {
        .login-page { min-height: auto; padding: 18px 0; }
        .login-layout { grid-template-columns: 1fr; border-radius: 18px; }
        .login-intro { padding: 30px 26px; }
        .login-intro .login-logo { margin-bottom: 18px; }
        .login-intro h2 { font-size: 23px; }
        .login-benefits { display: none; }
        .login-form-panel { padding: 30px 24px; }
    }
</style>

<div class="login-page">
    <div class="login-layout">
        <section class="login-intro">
            <div class="login-logo">Tech<span>Zone</span></div>
            <h2>Công nghệ trong tầm tay</h2>
            <p>Đăng nhập để mua sắm nhanh hơn và quản lý đơn hàng dễ dàng.</p>
            <ul class="login-benefits">
                <li>Theo dõi trạng thái đơn hàng</li>
                <li>Lưu thông tin giao hàng</li>
                <li>Nhận thông báo từ TechZone</li>
            </ul>
        </section>
        <section class="login-form-panel">
            <h1>Đăng nhập</h1>
            <p class="subtitle">Sử dụng email hoặc số điện thoại của bạn.</p>
        <?php if (!empty($loginError)): ?>
            <p class="login-error">
                <?php echo htmlspecialchars($loginError, ENT_QUOTES, 'UTF-8'); ?>
            </p>
        <?php endif; ?>
            <form class="login-form" method="post" action="index.php?act=login">
                <label class="login-field">Email hoặc số điện thoại
                    <input type="text" name="username" required autofocus autocomplete="username" placeholder="Nhập email hoặc số điện thoại">
                </label>
                <label class="login-field">Mật khẩu
                    <input type="password" name="password" required autocomplete="current-password" placeholder="Nhập mật khẩu">
                </label>
                <button class="login-submit" type="submit">Đăng nhập</button>
                <div class="login-actions">
                    <a class="btn" style="background:#0f766e;" href="index.php?act=register">Tạo tài khoản</a>
                    <a class="btn" style="background:#64748b;" href="index.php?act=forgot_password">Quên mật khẩu</a>
                    <a class="btn" style="background:#e2e8f0; color:#334155;" href="index.php?act=home">Về cửa hàng</a>
                </div>
            </form>
        </section>
    </div>
</div>
