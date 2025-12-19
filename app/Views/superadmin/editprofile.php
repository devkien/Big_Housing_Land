<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa hồ sơ</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/style.css">
</head>

<body>
    <div class="app-container" style="background: white;">

        <div class="edit-header">
            Chỉnh sửa hồ sơ
        </div>

        <?php $user = $user ?? null; ?>

        <?php if (!empty($_SESSION['success'])): ?>
            <div class="alert-success"><?php echo htmlspecialchars($_SESSION['success']);
                                        unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        <?php if (!empty($_SESSION['error'])): ?>
            <div class="alert-error"><?php echo htmlspecialchars($_SESSION['error']);
                                        unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <div class="profile-card-banner" onclick="window.location.href='<?php echo BASE_URL; ?>/superadmin/detailprofile'">
            <img src="<?php echo BASE_URL; ?>/icon/menuanhdaidien.png" class="profile-avatar-large">
            <div class="profile-info">
                <h3><?php echo htmlspecialchars($user['ho_ten'] ?? 'Người dùng'); ?></h3>
                <div class="profile-role">Cấp quản lý</div>
                <div class="office-badge"><?php echo htmlspecialchars($user['dia_chi'] ?? ''); ?></div>
            </div>
            <i class="fa-solid fa-chevron-right arrow-right-absolute"></i>
        </div>

        <form action="<?php echo BASE_URL; ?>/superadmin/editprofile" method="post">

            <div class="edit-form-group">
                <div class="edit-label-row">
                    <span>1. Họ và tên</span>
                </div>
                <div class="edit-input-box">
                    <i class="fa-solid fa-user"></i>
                    <input type="text" name="ho_ten" value="<?php echo htmlspecialchars($user['ho_ten'] ?? ''); ?>">
                </div>
            </div>

            <div class="edit-form-group">
                <div class="edit-label-row">
                    <span>2. Số điện thoại</span>
                    <span class="counter-text">0/10</span>
                </div>
                <div class="edit-input-box">
                    <i class="fa-solid fa-phone"></i>
                    <input type="text" name="so_dien_thoai" value="<?php echo htmlspecialchars($user['so_dien_thoai'] ?? ''); ?>">
                </div>
            </div>

            <div class="edit-form-group">
                <div class="edit-label-row">
                    <span>3. Email</span>
                    <span class="counter-text">0/10</span>
                </div>
                <div class="edit-input-box">
                    <i class="fa-solid fa-envelope"></i>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>">
                </div>
            </div>

            <div class="edit-form-group">
                <div class="edit-label-row">
                    <span>4. Ngày sinh</span>
                    <span class="counter-text">0/10</span>
                </div>
                <div class="edit-input-box">
                    <i class="fa-regular fa-calendar"></i>
                    <input type="text" name="nam_sinh" value="<?php echo htmlspecialchars($user['nam_sinh'] ?? ''); ?>">
                </div>
            </div>

            <div class="edit-form-group">
                <div class="edit-label-row">
                    <span>5. Căn Cước công dân</span>
                    <span class="counter-text">0/10</span>
                </div>
                <div class="edit-input-box">
                    <i class="fa-regular fa-id-card"></i>
                    <input type="text" name="so_cccd" value="<?php echo htmlspecialchars($user['so_cccd'] ?? ''); ?>">
                </div>
            </div>

            <div class="edit-form-group">
                <div class="edit-label-row">
                    <span>6. Địa chỉ thường trú</span>
                    <span class="counter-text">0/10</span>
                </div>
                <div class="edit-input-box">
                    <i class="fa-solid fa-location-dot"></i>
                    <input type="text" name="dia_chi" value="<?php echo htmlspecialchars($user['dia_chi'] ?? ''); ?>">
                </div>
                <div class="counter-text" style="text-align: right; margin-top: 5px;">0/10</div>
            </div>

            <button class="btn-save-change">Lưu thay đổi</button>

        </form>

        <div style="height: 60px;"></div>

        <div id="bottom-nav-container">
            <?php require_once __DIR__ . '/layouts/bottom-nav.php'; ?>
        </div>

    </div>
</body>

</html>