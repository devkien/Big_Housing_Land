<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết tin đăng</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../Public/Css/style.css">
    <script src="../Public/Js/script.js"></script>
</head>

<body>
    <div class="app-container" style="background: white;">

        <header class="detail-header">
            <a href="<?= BASE_URL ?>/superadmin/management-resource" class="header-icon-btn"><i class="fa-solid fa-chevron-left"></i></a>
            <div class="detail-title">Chi tiết</div>
            <div class="header-icon-btn"></div>
        </header>
        <div class="feed-list" style="padding-bottom: 80px;">

            <article class="post-card">
                <div class="user-row">
                    <div class="user-left">
                        <img src="../icon/menuanhdaidien.png" class="user-avatar">
                        <div class="user-info">
                            <div class="user-name"><?php echo htmlspecialchars($property['phong_ban'] ?? 'Người đăng') ?> - <?php echo htmlspecialchars($property['user_id'] ?? '') ?></div>
                            <div class="rating-stars">
                                <i class="fa-solid fa-star"></i>
                                <i class="fa-solid fa-star"></i>
                                <i class="fa-solid fa-star"></i>
                                <i class="fa-solid fa-star"></i>
                                <i class="fa-solid fa-star"></i>
                                <div class="contact-icons">
                                    <?php
                                    // Prepare contact links: prefer explicit links in $property, otherwise fallback to a contact route
                                    $messengerLink = !empty($property['messenger_link']) ? $property['messenger_link'] : (isset($property['user_id']) ? BASE_URL . '/superadmin/contact?user=' . urlencode($property['user_id']) . '&via=messenger' : '#');
                                    $zaloLink = !empty($property['zalo_link']) ? $property['zalo_link'] : (isset($property['user_id']) ? BASE_URL . '/superadmin/contact?user=' . urlencode($property['user_id']) . '&via=zalo' : '#');
                                    $phoneNumber = $property['phone'] ?? $property['sdt'] ?? $property['user_phone'] ?? null;
                                    $phoneLink = $phoneNumber ? 'tel:' . preg_replace('/[^0-9+]/', '', $phoneNumber) : (isset($property['user_id']) ? BASE_URL . '/superadmin/contact?user=' . urlencode($property['user_id']) . '&via=phone' : '#');
                                    ?>
                                    <a class="c-icon" href="<?= htmlspecialchars($messengerLink) ?>" target="_blank" rel="noopener noreferrer"><i class="fa-brands fa-facebook-messenger icon-mess"></i></a>
                                    <a class="c-icon" href="<?= htmlspecialchars($zaloLink) ?>" target="_blank" rel="noopener noreferrer"><i class="fa-solid fa-z icon-zalo"></i></a>
                                    <a class="c-icon" href="<?= htmlspecialchars($phoneLink) ?>" <?= $phoneNumber ? '' : 'target="_blank"' ?> rel="noopener noreferrer"><i class="fa-solid fa-phone icon-phone"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button class="btn-status-outline"><?php
                                                        $statusMap = [
                                                            'ban_manh' => 'Bán mạnh',
                                                            'tam_dung_ban' => 'Tạm dừng bán',
                                                            'dung_ban' => 'Dừng bán',
                                                            'da_ban' => 'Đã bán',
                                                            'tang_chao' => 'Tăng chào',
                                                            'ha_chao' => 'Hạ chào',
                                                        ];
                                                        echo htmlspecialchars($statusMap[$property['trang_thai']] ?? ($property['trang_thai'] ?? ''));
                                                        ?></button>
                </div>

                <div class="price-tag-row">
                    <div class="price-text"><?php
                                            $gia = isset($property['gia_chao']) && $property['gia_chao'] !== null && $property['gia_chao'] !== '' ? (float)$property['gia_chao'] : null;
                                            $area = isset($property['dien_tich']) && $property['dien_tich'] !== null && $property['dien_tich'] !== '' ? (float)$property['dien_tich'] : null;
                                            $areaUnit = strtolower(trim($property['don_vi_dien_tich'] ?? 'm2'));

                                            // Format main price
                                            if ($gia === null) {
                                                $mainPrice = 'Liên hệ';
                                            } else {
                                                if ($gia >= 1000000000) {
                                                    $val = round($gia / 1000000000, 1);
                                                    $val = rtrim(rtrim(number_format($val, 1, '.', ''), '0'), '.');
                                                    $mainPrice = $val . ' tỷ';
                                                } elseif ($gia >= 1000000) {
                                                    $val = round($gia / 1000000);
                                                    $mainPrice = $val . ' triệu';
                                                } else {
                                                    $mainPrice = number_format($gia, 0, ',', '.') . ' VND';
                                                }
                                            }

                                            // Compute price per unit if possible
                                            $perUnitText = '';
                                            if ($gia !== null && $area && $area > 0) {
                                                // price per unit in million VND
                                                $ppu_million = ($gia / $area) / 1000000;
                                                // choose unit label
                                                if ($areaUnit === 'ha') {
                                                    $unitLabel = 'tr/ha';
                                                } else {
                                                    $unitLabel = 'tr/m²';
                                                }
                                                $ppu_round = round($ppu_million);
                                                $perUnitText = $ppu_round . $unitLabel;
                                            } elseif ($area && $area > 0) {
                                                // fallback to show area if can't compute price per unit
                                                $perUnitText = rtrim(rtrim(number_format($area, 2, ',', '.'), '0'), ',') . ($areaUnit === 'ha' ? ' ha' : ' m²');
                                            }

                                            echo htmlspecialchars($mainPrice) . ($perUnitText ? ' - ' . htmlspecialchars($perUnitText) : '');
                                            ?></div>
                    <div class="tags-group">
                        <span class="tag-gray"><?php
                                                $addrParts = [];
                                                if (!empty($property['tinh_thanh'])) $addrParts[] = $property['tinh_thanh'];
                                                if (!empty($property['quan_huyen'])) $addrParts[] = $property['quan_huyen'];
                                                if (!empty($property['xa_phuong'])) $addrParts[] = $property['xa_phuong'];
                                                if (!empty($property['dia_chi_chi_tiet'])) $addrParts[] = $property['dia_chi_chi_tiet'];
                                                $address = !empty($addrParts) ? implode(' , ', $addrParts) : 'Địa chỉ chưa cập nhật';
                                                echo htmlspecialchars($address);
                                                ?></span>
                    </div>
                </div>

                <div class="post-content">
                    <div class="auto-truncate-text">
                        <strong><?php echo htmlspecialchars($property['tieu_de'] ?? '') ?></strong>
                        <?php echo nl2br(htmlspecialchars($property['mo_ta'] ?? '')) ?>
                    </div>
                    <div style="margin-top: 5px;"></div>
                    <?php if (!empty($property['ma_gioi_thieu'] ?? null)): ?>
                        <span class="hashtag"><?php echo htmlspecialchars($property['ma_gioi_thieu']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="meta-row">
                    <?php if (isset($property['phap_ly']) && $property['phap_ly'] === 'co_so'): ?>
                        <span class="red-badge">Sổ đỏ / Sổ hồng</span>
                        <span class="code-text">Mã sổ: <span class="code-number"><?php echo htmlspecialchars($property['ma_so_so'] ?? '') ?></span></span>
                    <?php endif; ?>
                    <span class="code-text">Mã tin: <span class="code-number"><?php echo htmlspecialchars('#' . ($property['ma_hien_thi'] ?? $property['id'])) ?></span></span>
                </div>

                <?php
                $img = '../images/phongkhach.png';
                if (!empty($media) && is_array($media) && !empty($media[0]['media_path'])) {
                    $img = '../' . ltrim($media[0]['media_path'], '/');
                }
                ?>
                <img src="<?php echo htmlspecialchars($img) ?>" class="post-image-large">
            </article>
        </div>

        <div id="bottom-nav-container">
            <?php require_once __DIR__ . '/layouts/bottom-nav.php'; ?>
        </div>

    </div>
</body>

</html>