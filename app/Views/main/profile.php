<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Danh mục tài khoản</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
    
    <style>
        .custom-modal-overlay {
            display: none; /* Mặc định ẩn */
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 10000;
            justify-content: center;
            align-items: center;
        }

        .custom-modal-box {
            background: white;
            width: 90%;
            max-width: 320px;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }

        .custom-modal-title {
            color: #355C9C; /* Màu xanh đậm giống ảnh */
            font-size: 17px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .custom-modal-desc {
            font-size: 14px;
            color: #333;
            margin-bottom: 25px;
        }

        .custom-modal-actions {
            display: flex;
            justify-content: space-between;
            gap: 15px;
        }

        .btn-custom-confirm {
            flex: 1;
            padding: 10px 0;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            border: 1px solid transparent;
        }

        /* Nút Đồng ý màu xanh */
        .btn-agree {
            background-color: #3b5998; 
            color: white;
        }

        /* Nút Huỷ bỏ viền đỏ */
        .btn-cancel-custom {
            background-color: white;
            color: #d32f2f;
            border: 1px solid #d32f2f;
        }
        
        .btn-cancel-custom:active {
            background-color: #fce8e8;
        }
    </style>
</head>

<body>
    <div class="app-container" style="background: #F9F9F9;">
        <?php require_once __DIR__ . '/../../Helpers/functions.php'; ?>

        <div class="page-big-title">Danh mục tài khoản</div>

        <div class="profile-card-banner" onclick="window.location.href='<?= BASE_URL ?>/detailprofile'">
            <?php
            // Normalize avatar path: support full URL, root-relative path, or stored relative path
            $avatarSrc = rtrim(BASE_URL, '/') . '/icon/menuanhdaidien.png';
            $avatar = $user['avatar'] ?? null;
            if (!empty($avatar)) {
                $trim = ltrim($avatar);
                if (stripos($trim, 'http') === 0) {
                    $avatarSrc = $trim;
                } elseif (strpos($trim, '/') === 0) {
                    $avatarSrc = rtrim(BASE_URL, '/') . $trim;
                } else {
                    $avatarSrc = rtrim(BASE_URL, '/') . '/' . ltrim($trim, '/');
                }
            }
            ?>
            <img src="<?php echo htmlspecialchars($avatarSrc, ENT_QUOTES, 'UTF-8'); ?>" class="profile-avatar-large" onerror="this.onerror=null;this.src='<?= rtrim(BASE_URL, '/') ?>/icon/menuanhdaidien.png';">
            <div class="profile-info">
                <h3><?php echo isset($user['ho_ten']) ? htmlspecialchars($user['ho_ten'], ENT_QUOTES, 'UTF-8') : '---'; ?></h3>
                <div class="profile-role">Cấp đầu khách</div>
                <div class="office-badge">TRỤ SỞ - HÀ NỘI</div>
            </div>
            <i class="fa-solid fa-chevron-right arrow-right-absolute"></i>
        </div>

        <div class="quick-access-grid">
            <a href="<?= BASE_URL ?>/management-resource" class="quick-card">
                <i class="fa-solid fa-house-chimney quick-icon"></i>
                <span class="quick-text">Kho tài nguyên</span>
            </a>
            <a href="<?= BASE_URL ?>/policy" class="quick-card">
                <i class="fa-regular fa-clipboard quick-icon"></i>
                <span class="quick-text">Quy định và hướng dẫn</span>
            </a>
            <a href="<?= BASE_URL ?>/notification" class="quick-card">
                <i class="fa-solid fa-money-bill-1-wave quick-icon"></i>
                <span class="quick-text">Thông báo vụ chốt</span>
            </a>
            <a href="<?= BASE_URL ?>/report_list" class="quick-card">
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

            <div class="sub-setting-item" id="btn-show-delete-modal" style="cursor: pointer;">
                <span style="color: #d32f2f;">Xóa tài khoản</span>
                <i class="fa-solid fa-chevron-right" style="font-size:12px; color:#d32f2f;"></i>
            </div>
        </div>

        <div class="settings-group">
            <div class="setting-item-header">
                <div class="setting-left">
                    <i class="fa-solid fa-shield-halved setting-icon"></i> Điều khoản & chính sách
                </div>
                <i class="fa-solid fa-chevron-up" style="font-size:12px;"></i>
            </div>

            <div class="sub-setting-item" onclick="window.location.href='<?= BASE_URL ?>/terms-service'">Điều khoản dịch vụ</div>
            <div class="sub-setting-item" onclick="window.location.href='<?= BASE_URL ?>/privacy-policy'">Chính sách bảo mật</div>
            <div class="sub-setting-item" onclick="window.location.href='<?= BASE_URL ?>/payment-policy'">Chính sách hoàn tiền/đổi trả</div>
            <div class="sub-setting-item" onclick="window.location.href='<?= BASE_URL ?>/cookie-policy'">Chính sách Cookie</div>
        </div>
        <button class="btn-logout" onclick="window.location.href='<?= BASE_URL ?>/logout'">Đăng xuất</button>
        <div style="height: 60px;"></div>
        <div id="bottom-nav-container">
            <?php require_once __DIR__ . '/layouts/bottom-nav.php'; ?>
        </div>
    </div>

    <div id="delete-confirm-modal" class="custom-modal-overlay">
        <div class="custom-modal-box">
            <div class="custom-modal-title">Xoá tài khoản</div>
            <div class="custom-modal-desc">Bạn chắc chắn sẽ xoá tài khoản này?</div>
            <div class="custom-modal-actions">
                <button class="btn-custom-confirm btn-agree" onclick="window.location.href='<?= BASE_URL ?>/logout'">Đồng ý</button>
                <button class="btn-custom-confirm btn-cancel-custom" id="btn-close-modal">Huỷ bỏ</button>
            </div>
        </div>
    </div>

    <script src="<?= BASE_URL ?>/js/script.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Lấy các phần tử
            const modal = document.getElementById('delete-confirm-modal');
            const showBtn = document.getElementById('btn-show-delete-modal');
            const closeBtn = document.getElementById('btn-close-modal');

            // Mở modal khi ấn "Xóa tài khoản"
            if (showBtn) {
                showBtn.addEventListener('click', function() {
                    modal.style.display = 'flex';
                });
            }

            // Đóng modal khi ấn "Huỷ bỏ"
            if (closeBtn) {
                closeBtn.addEventListener('click', function() {
                    modal.style.display = 'none';
                });
            }

            // Đóng modal khi click ra vùng ngoài (overlay)
            window.addEventListener('click', function(e) {
                if (e.target == modal) {
                    modal.style.display = 'none';
                }
            });
        });
    </script>
</body>

</html>