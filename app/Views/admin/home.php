<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang chủ - Big Housing Land</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">
</head>

<body>
    <div class="app-container" style="background-color: #E8F4FF;">

        <header class="home-header">
            <form class="search-bar" action="resource.html" method="GET">
                <input type="text" name="keyword" placeholder="Nhập thông tin tìm kiếm...">
                <button type="submit" style="border: none; background: transparent; padding: 0; cursor: pointer;"><i class="fa-solid fa-magnifying-glass"></i></button>
            </form>
        </header>

        <section class="menu-section" id="menu-container">
            <?php require_once __DIR__ . '/layouts/menu.php'; ?>
        </section>

        <section class="banner-section">
            <img src="<?= BASE_URL ?>/images/home.png" alt="Banner Thanh Tri" class="banner-images">
        </section>

        <section class="news-section">
            <div class="btn-news-header">BẢNG TIN</div>

            <div class="news-scroll">
                <?php if (!empty($pinnedPosts)): ?>
                    <?php foreach ($pinnedPosts as $post): ?>
                        <?php
                            $thumb = null;
                            if (!empty($post['images'])) {
                                $thumb = $post['images'][0]['image_path'] ?? null;
                            }
                            $excerpt = strip_tags($post['noi_dung'] ?? '');
                            if (mb_strlen($excerpt) > 150) {
                                $excerpt = mb_substr($excerpt, 0, 150);
                            }
                        ?>
                        <article class="news-card" onclick="window.location.href='<?= BASE_URL ?>/admin/internal-info-detail?id=<?= $post['id'] ?>'" style="cursor: pointer;">
                            <div class="news-header">
                                <img src="<?= BASE_URL ?>/icon/menuanhdaidien.png" class="avatar" />
                                <div class="news-info">
                                    <h4><?= htmlspecialchars($post['author_name'] ?? 'Big Housing Land') ?></h4>
                                    <span><?= !empty($post['created_at']) ? date('d/m/Y H:i', strtotime($post['created_at'])) : '' ?></span>
                                </div>
                            </div>
                            <div class="news-content">
                                <p>
                                    <strong><?= htmlspecialchars($post['tieu_de'] ?? '') ?>:</strong> <?= htmlspecialchars($excerpt) ?><span class="dots">...</span>
                                    <a href="<?= BASE_URL ?>/admin/internal-info-detail?id=<?= $post['id'] ?>" class="see-more">Xem thêm</a>
                                </p>
                                <?php if ($thumb): ?>
                                    <img src="<?= BASE_URL . '/' . htmlspecialchars($thumb) ?>" alt="Văn bản thông báo" class="doc-preview-images">
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="text-align: center; padding: 20px; color: #666;">Chưa có tin nào được ghim.</div>
                <?php endif; ?>
            </div>

        </section>

        <div id="bottom-nav-container">
            <?php require_once __DIR__ . '/layouts/bottom-nav.php'; ?>
        </div>

    </div>

    <script>
        // Highlight trang chủ trong bottom nav (nếu cần)
        document.addEventListener('DOMContentLoaded', function() {
            var el = document.getElementById('nav-home');
            if (el) el.classList.add('active');
        });

    </script>
</body>

</html>