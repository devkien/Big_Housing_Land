<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông báo vụ chốt</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">
    <script src="<?= BASE_URL ?>/js/script.js"></script>
</head>

<body>
    <div class="app-container" style="background: white;">

        <header class="detail-header">
            <a href="<?= BASE_URL ?>/admin/home" class="header-icon-btn"><i class="fa-solid fa-chevron-left"></i></a>
            <div class="detail-title">Thông báo vụ chốt</div>
            <div class="header-icon-btn"></div>
        </header>

        <div class="create-post-bar">
            <button class="btn-create-post" onclick="window.location.href='<?= BASE_URL ?>/admin/cre-notification'">
                <i class="fa-solid fa-plus"></i> Tạo chốt vụ
            </button>
        </div>

        <div class="feed-list" style="padding-bottom: 80px;">
            <?php if (!empty($posts) && is_array($posts)): ?>
                <?php foreach ($posts as $p): ?>
                    <article class="post-card" onclick="window.location.href='#'" style="cursor:pointer;">
                        <div class="user-row">
                            <div class="user-left">
                                <img src="<?= BASE_URL ?>/icon/menuanhdaidien.png" class="user-avatar">
                                <div class="notify-user-info">
                                    <div class="user-name"><?= htmlspecialchars($p['author_name'] ?? 'Người dùng') ?></div>
                                    <div class="notify-time"><?= isset($p['created_at']) ? date('d/m/Y H:i', strtotime($p['created_at'])) : '' ?></div>
                                </div>
                            </div>
                        </div>

                        <div class="notify-content">
                            <?php
                            $raw = $p['noi_dung'] ?? '';
                            $plain = trim(strip_tags($raw));
                            // Only use DB title if present; do not auto-generate a title
                            $title = !empty($p['tieu_de']) ? mb_substr(trim($p['tieu_de']), 0, 120) : null;
                            $short = mb_substr($plain, 0, 220);
                            // Extract hashtags
                            $tags = [];
                            if (preg_match_all('/#([\p{L}\p{N}_\-]+)/u', $raw, $mt)) {
                                $tags = array_unique($mt[0]);
                            }
                            ?>

                            <?php if (!empty($title)): ?>
                                <div class="notify-title" style="font-weight:600; margin-bottom:6px;"><?= htmlspecialchars($title) ?></div>
                            <?php endif; ?>
                            <div><?= nl2br(htmlspecialchars($short)) ?><?php if (mb_strlen($plain) > 220) echo '...'; ?></div>

                            <?php if (!empty($tags)): ?>
                                <div style="margin-top:8px;">
                                    <?php foreach ($tags as $t): ?>
                                        <span class="hashtag"><?= htmlspecialchars($t) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($p['images']) && is_array($p['images'])): ?>
                            <?php $first = $p['images'][0];
                            $src = (stripos($first, 'http') === 0) ? $first : (BASE_URL . '/' . ltrim($first, '/')); ?>
                            <img src="<?= htmlspecialchars($src) ?>" class="post-image-large">
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">Chưa có thông báo nào.</div>
            <?php endif; ?>
        </div>

        <!-- Modal Tìm kiếm -->
        <div id="search-modal" class="modal">
            <div class="modal-content">
                <h3 style="margin-bottom: 15px; font-size: 16px;">Tìm kiếm thông báo</h3>
                <div class="filter-group">
                    <input type="text" id="search-input-notify" class="filter-input" placeholder="Nhập nội dung tìm kiếm...">
                </div>
                <div class="modal-actions">
                    <button id="close-search" class="btn-cancel">Hủy</button>
                    <button id="apply-search-notify" class="btn-apply">Tìm kiếm</button>
                </div>
            </div>
        </div>

        <div id="bottom-nav-container">
            <?php require_once __DIR__ . '/layouts/bottom-nav.php'; ?>
        </div>
    </div>
</body>

</html>