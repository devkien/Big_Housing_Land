<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bộ sưu tập</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">
    <script>
        // Mock CKEditor để tránh lỗi trong script.js vì trang này không cần bộ soạn thảo
        window.ClassicEditor = {
            create: function() {
                // Trả về Promise không bao giờ resolve để script.js không làm gì tiếp theo
                return new Promise(() => {});
            }
        };
    </script>
    <script src="<?= BASE_URL ?>/js/script.js"></script>
</head>

<body>
    <div class="app-container" style="background: #f9f9f9; display: flex; flex-direction: column; height: 100vh;">

        <header class="detail-header">
            <a href="<?= BASE_URL ?>/home" class="header-icon-btn"><i class="fa-solid fa-chevron-left"></i></a>
            <div class="detail-title">Bộ sưu tập</div>
            <div class="header-icon-btn"></div>
        </header>

        <form class="search-collection-box" method="GET" action="<?= BASE_URL ?>/collection">
            <input type="text" name="q" value="<?= htmlspecialchars($search ?? '') ?>" placeholder="Nhập thông tin tìm kiếm..." style="border: none; outline: none; background: transparent; flex: 1;">
            <button type="submit" style="border: none; background: transparent; padding: 0; cursor: pointer;"><i class="fa-solid fa-magnifying-glass"></i></button>
        </form>

        <div style="flex: 1; overflow-y: auto;">
            <?php if (empty($collections)): ?>
                <div id="no-result-message" style="text-align: center; padding: 20px; color: #666; font-size: 14px;">
                    Không tìm thấy bộ sưu tập nào.
                </div>
            <?php else: ?>
                <?php foreach ($collections as $c):
                    $name = htmlspecialchars($c['ten_bo_suu_tap'] ?? '');
                    $count = $c['item_count'] ?? 0;
                    $firstChar = mb_substr($name, 0, 1, 'UTF-8');
                    $bgImage = '';
                    if (!empty($c['anh_dai_dien'])) {
                        $bgImage = "background-image: url('" . BASE_URL . "/" . $c['anh_dai_dien'] . "'); background-size: cover; background-position: center; color: transparent;";
                    }
                ?>
                    <div class="collection-card" data-id="<?= (int)$c['id'] ?>">
                        <div class="collection-thumb" style="<?= $bgImage ?>"><?= empty($bgImage) ? $firstChar : '' ?></div>
                        <div class="collection-info">
                            <div class="collection-name"><?= $name ?></div>
                            <div class="collection-count"><?= $count ?> tin</div>
                        </div>
                        <div class="btn-more-dots"
                            data-id="<?= $c['id'] ?>"
                            data-name="<?= $name ?>">
                            <i class="fa-solid fa-ellipsis"></i>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

        </div>
        <button class="btn-create-collection" onclick="window.location.href='<?= BASE_URL ?>/cre-collection'">
            Tạo bộ sưu tập
        </button>

        <!-- Modal Xác nhận xóa -->
        <div id="collection-modal" class="modal">
            <div class="modal-content">
                <h3 style="margin-bottom: 15px; font-size: 16px; text-align: center;">Tùy chọn</h3>
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <button id="edit-collection" class="btn-save" style="background-color: #0033cc; width: 100%; margin: 0;">Sửa tên bộ sưu tập</button>
                    <button id="confirm-delete" class="btn-save" style="background-color: #ff3333; width: 100%; margin: 0;">Xóa bộ sưu tập</button>
                    <button id="cancel-delete" class="btn-cancel" style="width: 100%; text-align: center;">Hủy</button>
                </div>
            </div>
        </div>

        <!-- Modal Đổi tên -->
        <div id="rename-collection-modal" class="modal">
            <div class="modal-content">
                <h3 style="margin-bottom: 15px; font-size: 16px;">Đổi tên bộ sưu tập</h3>
                <div class="filter-group">
                    <input type="text" id="rename-input" class="filter-input" placeholder="Nhập tên mới...">
                </div>
                <div class="modal-actions">
                    <button id="cancel-rename" class="btn-cancel">Hủy</button>
                    <button id="confirm-rename" class="btn-apply">Lưu</button>
                </div>
            </div>
        </div>

        <div style="height: 60px;"></div>
        <div id="bottom-nav-container">
            <?php require_once __DIR__ . '/layouts/bottom-nav.php'; ?>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Highlight bottom nav
            const navCollection = document.getElementById('nav-collection');
            if (navCollection) navCollection.classList.add('active');

            const modal = document.getElementById('collection-modal');
            const renameModal = document.getElementById('rename-collection-modal');
            const dots = document.querySelectorAll('.btn-more-dots');
            const cancelDelete = document.getElementById('cancel-delete');
            const cancelRename = document.getElementById('cancel-rename');

            const btnEdit = document.getElementById('edit-collection');
            const btnDelete = document.getElementById('confirm-delete');
            const btnSaveRename = document.getElementById('confirm-rename');
            const renameInput = document.getElementById('rename-input');

            let currentId = null;
            let currentName = '';

            // Open options modal
            dots.forEach(dot => {
                dot.addEventListener('click', function() {
                    currentId = this.getAttribute('data-id');
                    currentName = this.getAttribute('data-name');
                    modal.style.display = 'flex';
                });
            });

            // Click on collection card -> open collection detail (but ignore clicks on the dots button)
            document.querySelectorAll('.collection-card').forEach(card => {
                card.addEventListener('click', function(e) {
                    if (e.target.closest('.btn-more-dots')) return; // ignore clicks on options
                    const cid = this.getAttribute('data-id');
                    if (cid) {
                        window.location.href = '<?= BASE_URL ?>/collection-detail?id=' + encodeURIComponent(cid);
                    }
                });
            });

            // Close modals
            window.onclick = function(event) {
                if (event.target == modal) modal.style.display = "none";
                if (event.target == renameModal) renameModal.style.display = "none";
            }
            cancelDelete.onclick = () => modal.style.display = "none";
            cancelRename.onclick = () => renameModal.style.display = "none";

            // Open rename modal
            btnEdit.onclick = function() {
                modal.style.display = "none";
                renameModal.style.display = "flex";
                renameInput.value = currentName;
                renameInput.focus();
            };

            // Handle Rename
            btnSaveRename.onclick = function() {
                const newName = renameInput.value.trim();
                if (!newName) {
                    alert('Vui lòng nhập tên bộ sưu tập');
                    return;
                }

                const formData = new FormData();
                formData.append('id', currentId);
                formData.append('ten_bo_suu_tap', newName);

                fetch('<?= BASE_URL ?>/collection-rename', {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.ok) location.reload();
                        else alert(data.error || 'Lỗi khi đổi tên');
                    });
            };

            // Handle Delete
            btnDelete.onclick = function() {
                if (!confirm('Bạn có chắc chắn muốn xóa bộ sưu tập này?')) return;

                const formData = new FormData();
                formData.append('id', currentId);

                fetch('<?= BASE_URL ?>/collection-delete', {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.ok) location.reload();
                        else alert(data.error || 'Lỗi khi xóa');
                    });
            };
        });
    </script>
</body>

</html>