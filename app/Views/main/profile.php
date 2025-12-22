<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh mục tài khoản</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/Public/Css/style.css">
</head>

<body>
    <div class="app-container" style="background: #F9F9F9;">

        <div class="page-big-title">Danh mục tài khoản</div>

        <div class="profile-card-banner" onclick="window.location.href='<?= BASE_URL ?>/detailprofile'">
            <img src="../icon/menuanhdaidien.png" class="profile-avatar-large">
            <div class="profile-info">
                <h3><?php echo isset($user['ho_ten']) ? htmlspecialchars($user['ho_ten'], ENT_QUOTES, 'UTF-8') : '---'; ?></h3>
                <div class="profile-role">Cấp quản lý</div>
                <div class="office-badge">TRỤ SỞ - HÀ NỘI</div>
            </div>
            <i class="fa-solid fa-chevron-right arrow-right-absolute"></i>
        </div>

        <div class="quick-access-grid">
            <a href="../resource.html" class="quick-card">
                <i class="fa-solid fa-house-chimney quick-icon"></i>
                <span class="quick-text">Kho tài nguyên</span>
            </a>
            <div class="quick-card">
                <i class="fa-regular fa-clipboard quick-icon"></i>
                <span class="quick-text">Quy định và hướng dẫn</span>
            </div>
            <a href="../notification.html" class="quick-card">
                <i class="fa-solid fa-money-bill-1-wave quick-icon"></i>
                <span class="quick-text">Thông báo vụ chốt</span>
            </a>
            <a href="../report_list.html" class="quick-card">
                <i class="fa-solid fa-chart-simple quick-icon"></i>
                <span class="quick-text">Báo cáo dẫn khách</span>
            </a>
        </div>

        <div class="settings-group">
            <div class="setting-item-header">
                <div class="setting-left">
                    <i class="fa-solid fa-gear setting-icon"></i> Cài đặt
                </div>
                <i class="fa-solid fa-chevron-up" style="font-size:12px;"></i>
            </div>

            <div class="sub-setting-item" onclick="window.location.href='<?= BASE_URL ?>/changepassword'">
                <span>Đổi mật khẩu</span>
                <i class="fa-solid fa-chevron-right" style="font-size:12px; color:#999;"></i>
            </div>
        </div>

        <div class="settings-group">
            <div class="setting-item-header">
                <div class="setting-left">
                    <i class="fa-solid fa-shield-halved setting-icon"></i> Điều khoản & chính sách
                </div>
                <i class="fa-solid fa-chevron-up" style="font-size:12px;"></i>
            </div>

            <div class="sub-setting-item">Điều khoản dịch vụ</div>
            <div class="sub-setting-item">Chính sách bảo mật</div>
            <div class="sub-setting-item">Chính sách hoàn tiền/đổi trả</div>
            <div class="sub-setting-item">Chính sách Cookie</div>
        </div>
        <button class="btn-logout" onclick="window.location.href='<?= BASE_URL ?>/logout'">Đăng xuất</button>
        <div style="height: 60px;"></div>
        <div id="bottom-nav-container">
            <?php require_once __DIR__ . '/layouts/bottom-nav.php'; ?>
        </div>
    </div>
</body>

</html>