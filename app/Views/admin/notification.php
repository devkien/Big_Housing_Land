<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông báo vụ chốt</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <?php require_once __DIR__ . '/../../Helpers/functions.php'; ?>
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">
    <script src="<?= BASE_URL ?>/js/script.js"></script>
    <style>
        .menu-item:hover { background-color: #f5f5f5; }
    </style>
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
                    <article class="post-card" data-post-id="<?= $p['id'] ?>">
                        <div class="user-row">
                            <div class="user-left">
                                <img src="<?= BASE_URL ?>/icon/menuanhdaidien.png" class="user-avatar">
                                <div class="notify-user-info">
                                    <div class="user-name" onclick="window.location.href='<?= !empty($p['user_id']) ? BASE_URL . '/admin/detailprofile?id=' . (int)$p['user_id'] : '#' ?>'" style="cursor:pointer; color: #0044cc;"><?= htmlspecialchars($p['author_name'] ?? 'Người dùng') ?></div>
                                    <div class="notify-time"><?= isset($p['created_at']) ? date('d/m/Y H:i', strtotime($p['created_at'])) : '' ?></div>
                                </div>
                            </div>
                            <div class="post-options" style="position: relative;">
                                <i class="fa-solid fa-ellipsis" style="cursor: pointer; padding: 10px; color: #666;" onclick="togglePostMenu(event, this)"></i>
                                <div class="post-menu" style="display: none; position: absolute; right: 0; top: 30px; background: white; border: 1px solid #eee; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); z-index: 10; width: 120px; overflow: hidden;">
                                    <a href="<?= BASE_URL ?>/admin/edit-notification?id=<?= $p['id'] ?>" class="menu-item" style="display: block; padding: 10px 15px; color: #333; text-decoration: none; font-size: 13px; transition: background 0.2s;">
                                        <i class="fa-solid fa-pen" style="margin-right: 8px; color: #0044cc;"></i> Sửa
                                    </a>
                                    <a href="javascript:void(0)" onclick="deletePost(<?= $p['id'] ?>)" class="menu-item" style="display: block; padding: 10px 15px; color: #d32f2f; text-decoration: none; font-size: 13px; transition: background 0.2s;">
                                        <i class="fa-solid fa-trash" style="margin-right: 8px;"></i> Xóa
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="notify-content">
                            <?php
                            $raw = $p['noi_dung'] ?? '';
                            // Only use DB title if present; do not auto-generate a title
                            $title = !empty($p['tieu_de']) ? mb_substr(trim($p['tieu_de']), 0, 120) : null;
                            // Extract hashtags
                            $tags = [];
                            if (preg_match_all('/#([\p{L}\p{N}_\-]+)/u', $raw, $mt)) {
                                $tags = array_unique($mt[0]);
                            }
                            ?>

                            <?php if (!empty($title)): ?>
                                <div class="notify-title" style="font-weight:600; margin-bottom:6px;"><?= htmlspecialchars($title) ?></div>
                            <?php endif; ?>
                            <div class="auto-truncate-text" data-limit="150">
                                <?= $raw ?>
                            </div>

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

        <!-- Modal Xóa -->
        <div id="delete-post-modal" class="modal">
            <div class="modal-content">
                <h3 style="margin-bottom: 15px; font-size: 16px; text-align: center;">Xác nhận xóa</h3>
                <p style="text-align: center; margin-bottom: 20px; font-size: 13px;">Bạn có chắc chắn muốn xóa bài viết này?</p>
                <div class="modal-actions" style="justify-content: center;">
                    <button id="confirm-delete-post" class="btn-save" style="background-color: #ff3333; margin: 0; width: auto; padding: 10px 30px;">Xóa</button>
                    <button onclick="document.getElementById('delete-post-modal').style.display='none'" class="btn-cancel">Hủy</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePostMenu(event, icon) {
            event.stopPropagation();
            const menu = icon.nextElementSibling;
            document.querySelectorAll('.post-menu').forEach(el => {
                if (el !== menu) el.style.display = 'none';
            });
            menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
        }

        document.addEventListener('click', function() {
            document.querySelectorAll('.post-menu').forEach(el => el.style.display = 'none');
        });

        let deletePostId = null;
        function deletePost(id) {
            deletePostId = id;
            document.getElementById('delete-post-modal').style.display = 'flex';
        }

        document.getElementById('confirm-delete-post').addEventListener('click', function() {
            if (deletePostId) {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const btn = this;
                btn.disabled = true;

                fetch('<?= BASE_URL ?>/admin/delete-notification', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ id: deletePostId, _csrf: csrfToken })
                })
                .then(res => {
                    return res.text().then(text => {
                        try {
                            return JSON.parse(text);
                        } catch (e) {
                            console.error("Failed to parse JSON response:", text);
                            throw new Error("Server trả về phản hồi không hợp lệ. Vui lòng kiểm tra Console (F12).");
                        }
                    });
                })
                .then(data => {
                    if (data.ok) {
                        const cardToRemove = document.querySelector('.post-card[data-post-id="' + deletePostId + '"]');
                        if (cardToRemove) cardToRemove.remove();
                        alert('Đã xóa bài viết.');
                    } else {
                        alert('Lỗi: ' + (data.message || 'Không thể xóa bài viết.'));
                    }
                })
                .catch(err => {
                    console.error('Delete error:', err);
                    alert(err.message || 'Đã xảy ra lỗi kết nối.');
                })
                .finally(() => {
                    btn.disabled = false;
                    document.getElementById('delete-post-modal').style.display = 'none';
                    deletePostId = null;
                });
            }
        });
    </script>
</body>

</html>