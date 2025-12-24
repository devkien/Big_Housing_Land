<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý khách tự khớp</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">
    
</head>
<body>
    <div class="app-container" style="background: white;">
        
        <header class="detail-header">
            <a href="<?= BASE_URL ?>/home" class="header-icon-btn"><i class="fa-solid fa-chevron-left"></i></a>
            <div class="detail-title">Quản lý khách tự khớp</div>
            <div class="header-icon-btn"></div>
        </header>

        <form id="filter-container" style="padding: 15px;" method="POST" action="<?= BASE_URL ?>/auto-match">
            <label class="search-label-small">Loại tin BĐS</label>
            <select class="select-blue-border" style="margin-bottom: 10px;" name="type">
                <option value="">Tất cả</option>
                <option value="ban" <?= (isset($filters['type']) && $filters['type'] === 'ban') ? 'selected' : '' ?>>Mua bán</option>
                <option value="cho_thue" <?= (isset($filters['type']) && $filters['type'] === 'cho_thue') ? 'selected' : '' ?>>Cho thuê</option>
            </select>

            <label class="search-label-small">Khu vực</label>
            <input type="text" class="input-blue-border" style="margin-bottom: 10px;" name="location" placeholder="Nhập khu vực..." value="<?= htmlspecialchars($filters['location'] ?? '') ?>">

            <label class="search-label-small">Khoảng giá</label>
            <select class="select-blue-border" style="margin-bottom: 10px;" name="price">
                <option value="">Tất cả</option>
                <option value="lt_5" <?= (isset($filters['price']) && $filters['price'] === 'lt_5') ? 'selected' : '' ?>>Dưới 5 tỷ</option>
                <option value="5_10" <?= (isset($filters['price']) && $filters['price'] === '5_10') ? 'selected' : '' ?>>5 - 10 tỷ</option>
                <option value="10_20" <?= (isset($filters['price']) && $filters['price'] === '10_20') ? 'selected' : '' ?>>10 - 20 tỷ</option>
                <option value="gt_20" <?= (isset($filters['price']) && $filters['price'] === 'gt_20') ? 'selected' : '' ?>>Trên 20 tỷ</option>
            </select>

            <label class="search-label-small">Pháp lý</label>
            <select class="select-blue-border" style="margin-bottom: 10px;" name="legal">
                <option value="">Tất cả</option>
                <option value="so_do" <?= (isset($filters['legal']) && $filters['legal'] === 'so_do') ? 'selected' : '' ?>>Sổ đỏ</option>
                <option value="khong_so" <?= (isset($filters['legal']) && $filters['legal'] === 'khong_so') ? 'selected' : '' ?>>Không sổ</option>
            </select>

            <label class="search-label-small">Diện tích</label>
            <div class="area-input-group">
                <input type="number" name="area" value="<?= htmlspecialchars($filters['area'] > 0 ? $filters['area'] : '') ?>">
                <div class="area-unit">m2</div>
            </div>
            <button class="btn-submit-blue" type="submit" style="margin-top: 20px; width: 100%; margin-left: 0; margin-right: 0;">Tìm kiếm</button>
        </form>

        <?php if (isset($properties)): ?>
            <div id="result-header-container" style="padding: 0 15px;">
                <div class="result-header">Các bài viết kết quả hiển thị</div>
            </div>

            <div id="post-list-container" style="padding-bottom: 80px;">
                <?php if (empty($properties)): ?>
                    <div style="text-align: center; padding: 20px; color: #666;">Không tìm thấy kết quả phù hợp.</div>
                <?php else: ?>
                    <?php foreach ($properties as $p): ?>
                        <?php
                            $title = htmlspecialchars($p['tieu_de'] ?? '');
                            $priceVal = (float)($p['gia_chao'] ?? 0);
                            $price = 'Thỏa thuận';
                            if ($priceVal >= 1000000000) {
                                $price = round($priceVal / 1000000000, 2) . ' tỷ';
                            } elseif ($priceVal >= 1000000) {
                                $price = round($priceVal / 1000000, 0) . ' triệu';
                            } elseif ($priceVal > 0) {
                                $price = number_format($priceVal) . ' VND';
                            }
                            
                            $code = htmlspecialchars($p['ma_hien_thi'] ?? '');
                            $status = htmlspecialchars($p['trang_thai'] ?? '');
                            $address = trim($p['dia_chi_chi_tiet'] ?? '');
                            if ($address === '') {
                                $parts = array_filter([$p['xa_phuong'] ?? '', $p['quan_huyen'] ?? '', $p['tinh_thanh'] ?? '']);
                                $address = htmlspecialchars(implode(', ', $parts));
                            } else {
                                $address = htmlspecialchars($address);
                            }
                            $thumb = '';
                            if (!empty($p['thumb'])) {
                                $img = $p['thumb'];
                                $src = (stripos($img, 'http') === 0 || strpos($img, '/') === 0) ? $img : (BASE_URL . '/' . ltrim($img, '/'));
                                $thumb = '<img src="' . htmlspecialchars($src) . '" style="width:100%;height:100%;object-fit:cover;border-radius:6px;">';
                            } else {
                                $thumb = '<div style="width:100%;height:100%;background:#eee;border-radius:6px;display:flex;align-items:center;justify-content:center;color:#888;">thumb</div>';
                            }
                        ?>
                        <article class="post-card" onclick="window.location.href='<?= BASE_URL ?>/detail?id=<?= (int)$p['id'] ?>'">
                            <div style="display:flex;gap:12px;align-items:flex-start;padding:12px;">
                                <div style="width:96px;height:72px;flex:0 0 96px;"><?= $thumb ?></div>
                                <div style="flex:1;">
                                    <div style="display:flex;justify-content:space-between;align-items:center;">
                                        <div style="font-weight:700;"><?= $title ?></div>
                                        <div class="price-text"><?= $price ?></div>
                                    </div>
                                    <div style="margin-top:6px;color:#666;font-size:13px;"><?= $address ?></div>
                                    <div style="margin-top:8px;color:#999;font-size:12px;">Mã: <?= $code ?> &nbsp; &bull; &nbsp; Trạng thái: <?= $status ?></div>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div id="bottom-nav-container">
            <?php require_once __DIR__ . '/layouts/bottom-nav.php'; ?>
        </div>

    </div>
    <script src="<?= BASE_URL ?>/js/script.js"></script>
</body>
</html>