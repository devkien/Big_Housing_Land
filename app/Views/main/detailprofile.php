<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông tin chi tiết</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/Public/Css/style.css">
</head>

<body>
    <div class="app-container" style="background: white;">

        <?php
        $displayName = $user['ho_ten'] ?? '---';
        ?>
        <header class="profile-detail-header">
            <a href="<?= BASE_URL ?>/profile" style="color: black; font-size: 18px;"><i class="fa-solid fa-chevron-left"></i></a>
            <div class="header-title"><?php echo htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8'); ?></div>
            <div style="font-size: 20px;"></div>
        </header>
        <div class="cover-wrapper">
            <img src="https://images.unsplash.com/photo-1524758631624-e2822e304c36?q=80&w=800&auto=format&fit=crop" class="cover-image">
            <?php
            $avatar = $user['avatar'] ?? null;
            if (!empty($avatar)) {
                // assume uploads stored in /uploads/
                $avatarUrl = rtrim(BASE_URL, '/') . '/uploads/' . ltrim($avatar, '/');
            } else {
                $avatarUrl = rtrim(BASE_URL, '/') . '/icon/menuanhdaidien.png';
            }
            ?>

            <img src="<?php echo htmlspecialchars($avatarUrl, ENT_QUOTES, 'UTF-8'); ?>" class="profile-avatar-circle">

            <a href="<?= BASE_URL ?>/editprofile" class="edit-profile-btn">
                <i class="fa-solid fa-pen"></i> Chỉnh sửa hồ sơ
            </a>
        </div>

        <div class="profile-text-info">
            <div class="user-fullname"><?php echo htmlspecialchars($user['ho_ten'] ?? '---', ENT_QUOTES, 'UTF-8'); ?></div>
            <div class="user-job-title">Cấp quản lý</div>
            <div class="user-office">Trụ sở <?php echo htmlspecialchars($user['phong_ban'] ?? $user['dia_chi'] ?? '', ENT_QUOTES, 'UTF-8'); ?></div>

            <?php if (!empty($user['link_fb'])): ?>
                <a href="<?php echo htmlspecialchars($user['link_fb'], ENT_QUOTES, 'UTF-8'); ?>" class="fb-link" target="_blank" rel="noopener">
                    <i class="fa-brands fa-facebook"></i> <?php echo htmlspecialchars($user['ho_ten'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                </a>
            <?php endif; ?>
        </div>

        <div class="info-grid">
            <div class="info-list-left">
                <div class="info-row"><i class="fa-solid fa-share-nodes"></i> <?php echo htmlspecialchars($user['phong_ban'] ?? $user['dia_chi'] ?? ''); ?></div>
                <div class="info-row"><i class="fa-regular fa-id-card"></i> <?php echo htmlspecialchars($user['ma_nhan_su'] ?? $user['so_cccd'] ?? ''); ?></div>
                <div class="info-row"><i class="fa-solid fa-phone"></i> <?php echo htmlspecialchars($user['so_dien_thoai'] ?? ''); ?></div>
                <div class="info-row"><i class="fa-regular fa-envelope"></i> <?php echo htmlspecialchars($user['email'] ?? ''); ?></div>
                <div class="info-row"><i class="fa-regular fa-clock"></i> Tham gia: <?php echo !empty($user['created_at']) ? htmlspecialchars($user['created_at'], ENT_QUOTES, 'UTF-8') : ''; ?></div>
                <div class="info-row"><i class="fa-regular fa-calendar"></i> Năm sinh: <?php echo htmlspecialchars($user['nam_sinh'] ?? ''); ?></div>
                <div class="info-row"><i class="fa-solid fa-location-dot"></i> Địa chỉ: <?php echo htmlspecialchars($user['dia_chi'] ?? ''); ?></div>
            </div>
        </div>
        <div style="padding: 0 15px; margin-bottom: 10px; font-weight: 700; font-size: 13px;">BÀI VIẾT</div>
        <div class="feed-list" style="background: #f2f4f8; padding-top: 10px; padding-bottom: 80px;">
            <article class="post-card" style="margin-bottom: 0;">
                <div class="user-row">
                    <div class="user-left">
                        <img src="../icon/menuanhdaidien.png" class="user-avatar">
                        <div class="user-info">
                            <div class="user-name"><?php echo htmlspecialchars($user['ho_ten'] ?? '---', ENT_QUOTES, 'UTF-8'); ?></div>
                            <div class="rating-stars" style="color: #FFC107;">
                                8 giờ trước • Khách cần mua gấp
                            </div>
                        </div>
                    </div>
                    <div style="color: #666;"><i class="fa-solid fa-ellipsis"></i></div>
                </div>

                <div class="post-content auto-truncate-text" data-limit="150">
                    <strong>NHÀ PHỐ VIỆT NAM - CHỐT NHÀ TOÀN QUỐC</strong> Kính thưa Chủ tịch, Tổng Giám đốc cùng các ban lãnh đạo. Tôi Trưởng phòng Nguyễn Kim Ngân - NPDN xin vui mừng thông báo CV Nguyễn Khanh - Nhà Phố 888 chốt hạ thành công căn nhà siêu phẩm. Kính thưa Chủ tịch, Tổng Giám đốc cùng các ban lãnh đạo. NPDN xin vui mừng thông báo CV Nguyễn Khanh - Nhà Phố 888 chốt hạ thành công căn nhà siêu phẩm. Kính thưa Chủ tịch, Tổng Giám đốc cùng các ban lãnh đạo.NPDN xin vui mừng thông báo CV Nguyễn Khanh - Nhà Phố 888 chốt hạ thành công căn nhà siêu phẩm. Kính thưa Chủ tịch, Tổng Giám đốc cùng các ban lãnh đạo.NPDN xin vui mừng thông báo CV Nguyễn Khanh - Nhà Phố 888 chốt hạ thành công căn nhà siêu phẩm. Kính thưa Chủ tịch, Tổng Giám đốc cùng các ban lãnh đạo.NPDN xin vui mừng thông báo CV Nguyễn Khanh - Nhà Phố 888 chốt hạ thành công căn nhà siêu phẩm. Kính thưa Chủ tịch, Tổng Giám đốc cùng các ban lãnh đạo.NPDN xin vui mừng thông báo CV Nguyễn Khanh - Nhà Phố 888 chốt hạ thành công căn nhà siêu phẩm. Kính thưa Chủ tịch, Tổng Giám đốc cùng các ban lãnh đạo.NPDN xin vui mừng thông báo CV Nguyễn Khanh - Nhà Phố 888 chốt hạ thành công căn nhà siêu phẩm. Kính thưa Chủ tịch, Tổng Giám đốc cùng các ban lãnh đạo.
                    <br>
                    <span class="hashtag">#tpnguyenkimngan</span> <span class="hashtag">#nphn</span>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 15px; padding: 0 15px 15px 15px;">
                    <i class="fa-brands fa-facebook-messenger" style="color: var(--link-blue); font-size: 20px;"></i>
                    <i class="fa-solid fa-z" style="color: var(--link-blue); font-size: 20px;"></i>
                    <i class="fa-solid fa-phone" style="color: var(--success-green); font-size: 20px;"></i>
                </div>
            </article>

            <article class="post-card" style="margin-top: 10px;">
                <div class="user-row">
                    <div class="user-left">
                        <img src="../icon/menuanhdaidien.png" class="user-avatar">
                        <div class="user-info">
                            <div class="user-name">TP Nguyễn Kim Ngàn - NPHN - 888</div>
                            <div class="rating-stars" style="color: #FFC107;">
                                8 giờ trước • Khách cần mua gấp
                            </div>
                        </div>
                    </div>
                    <div style="color: #666;"><i class="fa-solid fa-ellipsis"></i></div>
                </div>
                <div class="post-content">
                    <strong>NHÀ PHỐ VIỆT NAM</strong> ...
                </div>
            </article>
        </div>
        <div id="bottom-nav-container">
            <?php require_once __DIR__ . '/layouts/bottom-nav.php'; ?>
        </div>
    </div>
</body>

</html>