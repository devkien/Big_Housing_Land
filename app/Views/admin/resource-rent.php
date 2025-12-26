<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kho nhà cho thuê</title>
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
    <div class="app-container" style="background: white;">

        <header class="resource-header">
            <a href="<?= BASE_URL ?>/admin/home" class="header-icon-btn"><i class="fa-solid fa-chevron-left"></i></a>
            <div class="resource-title">Kho tài nguyên</div>
            <div class="header-icon-btn"></div>
        </header>

        <div class="tabs-container">
            <button class="tab-btn active">Kho nhà cho thuê</button>
        </div>

        <div class="toolbar-section">
            <button class="tool-btn" id="btn-filter"><i class="fa-solid fa-filter"></i> Lọc</button>
            <div style="flex:1;"></div>
        </div>

        <div class="table-wrapper" style="margin-bottom: 0;">
            <table class="resource-table" style="min-width: 800px;">
                <thead>
                    <tr>
                        <!-- Inline page script removed. Centralized handlers live in public/js/script.js to avoid duplicate listeners. -->

                        <div class="modal-actions">
                            <button id="close-save-collection" class="btn-cancel">Hủy</button>
                            <button id="confirm-save-collection" class="btn-apply">Lưu</button>
                        </div>
        </div>
    </div>
    <!-- Modal Cập nhật trạng thái (Khi ấn icon ghi chú) -->
    <div id="status-modal" class="modal">
        <div class="modal-content">
            <h3 style="margin-bottom: 15px; font-size: 16px;">Cập nhật trạng thái</h3>

            <div class="filter-group">
                <label class="filter-label">Chọn trạng thái mới</label>
                <select id="edit-status-select" class="filter-select">
                    <option value="ban_manh">Bán mạnh</option>
                    <option value="tam_dung_ban">Tạm dừng bán</option>
                    <option value="dung_ban">Dừng bán</option>
                    <option value="da_ban">Đã bán</option>
                    <option value="tang_chao">Tăng chào</option>
                    <option value="ha_chao">Hạ chào</option>
                </select>
            </div>

            <div class="modal-actions">
                <button id="close-status-modal" class="btn-cancel">Hủy</button>
                <button id="save-status-btn" class="btn-apply">Lưu</button>
            </div>
        </div>
    </div>
    <div id="bottom-nav-container">
        <?php require_once __DIR__ . '/layouts/bottom-nav.php'; ?>
    </div>

    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const filterModal = document.getElementById('filter-modal');
            const searchModal = document.getElementById('search-modal');
            const btnFilter = document.getElementById('btn-filter');
            const btnSearch = document.getElementById('btn-search');
            const closeFilter = document.getElementById('close-filter');
            const closeSearch = document.getElementById('close-search');
            const applyFilter = document.getElementById('apply-filter');
            const applySearch = document.getElementById('apply-search');
            const statusModal = document.getElementById('status-modal');
            const closeStatusModal = document.getElementById('close-status-modal');
            const iconNotes = document.querySelectorAll('.icon-note');
            const saveStatusBtn = document.getElementById('save-status-btn');
            let currentPropertyId = null;

            if (btnFilter) {
                btnFilter.addEventListener('click', () => {
                    if (filterModal) filterModal.style.display = 'flex';
                });
            }

            if (btnSearch) {
                btnSearch.addEventListener('click', () => {
                    if (searchModal) searchModal.style.display = 'flex';
                });
            }

            if (closeFilter) {
                closeFilter.addEventListener('click', () => {
                    if (filterModal) filterModal.style.display = 'none';
                });
            }

            if (closeSearch) {
                closeSearch.addEventListener('click', () => {
                    if (searchModal) searchModal.style.display = 'none';
                });
            }

            if (closeStatusModal) {
                closeStatusModal.addEventListener('click', () => {
                    if (statusModal) statusModal.style.display = 'none';
                });
            }

            if (iconNotes) {
                iconNotes.forEach(icon => {
                    icon.addEventListener('click', (e) => {
                        e.stopPropagation();
                        currentPropertyId = icon.getAttribute('data-id');
                        const currentStatus = icon.getAttribute('data-status');
                        const select = document.getElementById('edit-status-select');
                        if (select) select.value = currentStatus;
                        if (statusModal) statusModal.style.display = 'flex';
                    });
                });
            }

            if (saveStatusBtn) {
                saveStatusBtn.addEventListener('click', () => {
                    if (!currentPropertyId) return;
                    const newStatus = document.getElementById('edit-status-select').value;

                    const formData = new FormData();
                    formData.append('id', currentPropertyId);
                    formData.append('status', newStatus);

                    fetch('<?= BASE_URL ?>/admin/update-resource-status', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => {
                            if (!response.ok) {
                                if (response.status === 404) throw new Error('Đường dẫn API chưa được tạo (404). Vui lòng kiểm tra Controller.');
                                throw new Error('Lỗi server: ' + response.status);
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data.success) {
                                alert('Cập nhật trạng thái thành công!');
                                location.reload();
                            } else {
                                alert('Có lỗi xảy ra: ' + (data.message || 'Không xác định'));
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Lỗi: ' + error.message);
                        });
                });
            }

            window.addEventListener('click', (event) => {
                if (event.target == filterModal) {
                    filterModal.style.display = 'none';
                }
                if (event.target == searchModal) {
                    searchModal.style.display = 'none';
                }
                if (event.target == statusModal) {
                    statusModal.style.display = 'none';
                }
            });

            if (applyFilter) {
                applyFilter.addEventListener('click', () => {
                    const status = document.getElementById('filter-status').value;
                    const address = document.getElementById('filter-address').value;

                    const url = new URL('<?= BASE_URL ?>/admin/management-resource-rent', window.location.origin);
                    url.searchParams.set('page', '1'); // Reset to first page on new filter

                    if (status && status !== 'all') url.searchParams.set('status', status);
                    if (address) url.searchParams.set('address', address);

                    window.location.href = url.toString();
                });
            }

            if (applySearch) {
                applySearch.addEventListener('click', () => {
                    const search = document.getElementById('search-input').value;

                    const url = new URL('<?= BASE_URL ?>/admin/management-resource-rent', window.location.origin);
                    url.searchParams.set('page', '1');

                    if (search) url.searchParams.set('q', search);

                    window.location.href = url.toString();
                });
            }
        });
    </script>
</body>

</html>