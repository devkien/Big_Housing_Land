<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kho tài nguyên</title>
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
            <button class="tab-btn active">Kho nhà đất</button>

            <button class="tab-btn inactive" onclick="window.location.href='<?= BASE_URL ?>/admin/management-resource-rent'">Kho nhà cho thuê</button>
        </div>

        <div class="toolbar-section">
            <button class="tool-btn" id="btn-filter"><i class="fa-solid fa-filter"></i> Lọc</button>
            <div style="flex:1;"></div>
        </div>
        <div class="table-wrapper" style="margin-bottom: 0;">
            <table class="resource-table" style="min-width:800px;">
                <thead>
                    <tr>
                        <th style="padding-left:15px; width: 60px;">LƯU</th>
                        <th style="width: 60px;">GHI CHÚ</th>
                        <th style="width: 100px;">MÃ TÀI NGUYÊN</th>
                        <th style="width: 100px;">THỜI GIAN</th>
                        <th style="width: 120px;">HIỆN TRẠNG</th>
                        <th style="text-align:right; padding-right:15px;">ĐỊA CHỈ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $statusMap = [
                        'ban_manh' => 'Bán mạnh',
                        'tam_dung_ban' => 'Tạm dừng',
                        'dung_ban' => 'Dừng bán',
                        'da_ban' => 'Đã bán',
                        'tang_chao' => 'Tăng chào',
                        'ha_chao' => 'Hạ chào'
                    ];
                    if (empty($properties)) :
                    ?>
                        <tr>
                            <td colspan="6" style="text-align:center; padding:20px;">Không tìm thấy tài nguyên nào.</td>
                        </tr>
                        <?php else :
                        foreach ($properties as $p) :
                            $itemCode = htmlspecialchars($p['ma_hien_thi'] ?? '');
                            if (empty($itemCode)) $itemCode = '#' . htmlspecialchars($p['id'] ?? '');
                            $itemCreated = !empty($p['created_at']) ? date('d/m/Y', strtotime($p['created_at'])) : '';
                            $itemStatus = $statusMap[$p['trang_thai'] ?? ''] ?? ($p['trang_thai'] ?? '');
                            $itemAddress = trim($p['dia_chi_chi_tiet'] ?? '');
                            if ($itemAddress === '') {
                                $parts = array_filter([$p['tinh_thanh'] ?? '', $p['quan_huyen'] ?? '', $p['xa_phuong'] ?? '']);
                                $itemAddress = htmlspecialchars(implode(', ', $parts));
                            } else {
                                $itemAddress = htmlspecialchars($itemAddress);
                            }
                        ?>
                            <tr onclick="window.location.href='<?= BASE_URL ?>/admin/detail?id=<?= htmlspecialchars($p['id']) ?>'">
                                <td style="padding-left:15px; cursor: pointer;" class="action-cell-save" data-id="<?= $p['id'] ?>"><i class="fa-regular fa-bookmark icon-save"></i></td>
                                <td style="cursor: pointer;" class="action-cell-note" data-id="<?= $p['id'] ?>" data-status="<?= htmlspecialchars($p['trang_thai'] ?? '') ?>"><i class="fa-regular fa-note-sticky icon-note"></i></td>
                                <td><?= $itemCode ?></td>
                                <td><?= $itemCreated ?></td>
                                <td><span class="status-badge strong"><?= htmlspecialchars($itemStatus) ?></span></td>
                                <td style="text-align:right; padding-right:15px;"><?= $itemAddress ?></td>
                            </tr>
                    <?php
                        endforeach;
                    endif;
                    ?>
                </tbody>
            </table>
        </div>
        <div class="pagination-container">
            <?php
            // Build query string to persist filters
            $queryParams = [];
            if (!empty($status)) $queryParams['status'] = $status;
            if (!empty($address)) $queryParams['address'] = $address;
            $queryString = http_build_query($queryParams);
            ?>

            <?php if ($page > 1): ?>
                <a href="<?= BASE_URL ?>/admin/management-resource?page=<?= $page - 1 ?>&<?= $queryString ?>" class="page-link"><i class="fa-solid fa-chevron-left"></i></a>
            <?php endif; ?>
            
            <a href="#" class="page-link active"><?= $page ?> / <?= $pages > 0 ? $pages : 1 ?></a>
            
            <?php if ($page < $pages): ?>
                <a href="<?= BASE_URL ?>/admin/management-resource?page=<?= $page + 1 ?>&<?= $queryString ?>" class="page-link"><i class="fa-solid fa-chevron-right"></i></a>
            <?php endif; ?>
        </div>

        <!-- Modal Lọc -->
        <div id="filter-modal" class="modal">
            <div class="modal-content">
                <h3 style="margin-bottom: 15px; font-size: 16px;">Bộ lọc tìm kiếm</h3>

                <div class="filter-group">
                    <label class="filter-label">Hiện trạng</label>
                    <select id="filter-status" class="filter-select">
                        <option value="all" <?= (empty($status) || $status === 'all') ? 'selected' : '' ?>>Tất cả</option>
                        <option value="ban_manh" <?= (isset($status) && $status === 'ban_manh') ? 'selected' : '' ?>>Bán mạnh</option>
                        <option value="tam_dung_ban" <?= (isset($status) && $status === 'tam_dung_ban') ? 'selected' : '' ?>>Tạm dừng bán</option>
                        <option value="dung_ban" <?= (isset($status) && $status === 'dung_ban') ? 'selected' : '' ?>>Dừng bán</option>
                        <option value="da_ban" <?= (isset($status) && $status === 'da_ban') ? 'selected' : '' ?>>Đã bán</option>
                        <option value="tang_chao" <?= (isset($status) && $status === 'tang_chao') ? 'selected' : '' ?>>Tăng chào</option>
                        <option value="ha_chao" <?= (isset($status) && $status === 'ha_chao') ? 'selected' : '' ?>>Hạ chào</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label">Mã tin / Địa chỉ</label>
                    <input type="text" id="filter-address" class="filter-input" placeholder="Nhập mã tin (VD: 1277)..." value="<?= htmlspecialchars($address ?? '') ?>">
                </div>
                <div class="modal-actions">
                    <button id="close-filter" class="btn-cancel">Hủy</button>
                    <button id="apply-filter" class="btn-apply">Áp dụng</button>
                </div>
            </div>
        </div>

        <!-- Modal Tìm kiếm -->
        <div id="search-modal" class="modal">
            <div class="modal-content">
                <h3 style="margin-bottom: 15px; font-size: 16px;">Tìm kiếm</h3>
                <div class="filter-group">
                    <input type="text" id="search-input" class="filter-input" placeholder="Nhập từ khóa (Mã tin, địa chỉ, ghi chú)...">
                </div>
                <div class="modal-actions">
                    <button id="close-search" class="btn-cancel">Hủy</button>
                    <button id="apply-search" class="btn-apply">Tìm kiếm</button>
                </div>
            </div>
        </div>

        <!-- Modal Lưu vào bộ sưu tập -->
        <div id="save-collection-modal" class="modal">
            <div class="modal-content">
                <h3 style="margin-bottom: 15px; font-size: 16px;">Lưu vào bộ sưu tập</h3>

                <div class="filter-group">
                    <div class="collection-list-select" style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; border-radius: 8px; padding: 5px;">
                        <?php if (!empty($collections)): ?>
                            <?php foreach ($collections as $c): ?>
                                <label class="collection-option" style="display: flex; align-items: center; padding: 10px; border-bottom: 1px solid #eee; cursor: pointer;">
                                    <input type="checkbox" name="collection" value="<?= $c['id'] ?>" style="margin-right: 10px;">
                                    <span style="font-size: 14px; color: #000;"><?= htmlspecialchars($c['ten_bo_suu_tap']) ?></span>
                                </label>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div style="padding: 10px; text-align: center; color: #666;">Chưa có bộ sưu tập nào.</div>
                        <?php endif; ?>
                    </div>
                </div>

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
            const cellNotes = document.querySelectorAll('.action-cell-note');
            const saveStatusBtn = document.getElementById('save-status-btn');
            const cellSaves = document.querySelectorAll('.action-cell-save');
            const saveCollectionModal = document.getElementById('save-collection-modal');
            const closeSaveCollection = document.getElementById('close-save-collection');
            const confirmSaveCollection = document.getElementById('confirm-save-collection');
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

            if (closeSaveCollection) {
                closeSaveCollection.addEventListener('click', () => {
                    if (saveCollectionModal) saveCollectionModal.style.display = 'none';
                });
            }

            if (cellNotes) {
                cellNotes.forEach(cell => {
                    cell.addEventListener('click', (e) => {
                        e.stopPropagation();
                        currentPropertyId = cell.getAttribute('data-id');
                        const currentStatus = cell.getAttribute('data-status');
                        const select = document.getElementById('edit-status-select');
                        if (select) select.value = currentStatus;
                        if (statusModal) statusModal.style.display = 'flex';
                    });
                });
            }

            if (cellSaves) {
                cellSaves.forEach(cell => {
                    cell.addEventListener('click', (e) => {
                        e.stopPropagation();
                        currentPropertyId = cell.getAttribute('data-id');
                        // Reset checkboxes
                        const checkboxes = saveCollectionModal.querySelectorAll('input[name="collection"]');
                        checkboxes.forEach(cb => cb.checked = false);
                        
                        // Fetch các bộ sưu tập đã lưu của tài nguyên này
                        fetch('<?= BASE_URL ?>/admin/get-property-collections?id=' + currentPropertyId)
                            .then(r => {
                                if (!r.ok) throw new Error('Lỗi server: ' + r.status);
                                return r.json();
                            })
                            .then(data => {
                                if(data.success && data.collection_ids) {
                                    data.collection_ids.forEach(cid => {
                                        const cb = saveCollectionModal.querySelector(`input[name="collection"][value="${cid}"]`);
                                        if(cb) cb.checked = true;
                                    });
                                }
                            })
                            .catch(e => console.error('Lỗi tải bộ sưu tập:', e));

                        if (saveCollectionModal) saveCollectionModal.style.display = 'flex';
                    });
                });
            }

            if (saveStatusBtn) {
                saveStatusBtn.addEventListener('click', () => {
                    if (!currentPropertyId) return;
                    const newStatus = document.getElementById('edit-status-select').value;
                    
                    // Gửi request cập nhật lên server
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

            if (confirmSaveCollection) {
                confirmSaveCollection.addEventListener('click', () => {
                    if (!currentPropertyId) return;
                    
                    const selected = [];
                    saveCollectionModal.querySelectorAll('input[name="collection"]:checked').forEach(cb => {
                        selected.push(cb.value);
                    });

                    const formData = new FormData();
                    formData.append('property_id', currentPropertyId);
                    selected.forEach(id => formData.append('collection_ids[]', id));

                    fetch('<?= BASE_URL ?>/admin/add-to-collection', {
                        method: 'POST',
                        body: formData
                    })
                    .then(r => {
                        if (!r.ok) throw new Error('Lỗi server: ' + r.status);
                        return r.json();
                    })
                    .then(data => {
                        if(data.success) {
                            alert('Đã lưu vào bộ sưu tập!');
                            if (saveCollectionModal) saveCollectionModal.style.display = 'none';
                        } else {
                            alert('Lỗi: ' + (data.message || 'Không thể lưu.'));
                        }
                    })
                    .catch(e => {
                        console.error(e);
                        alert('Có lỗi xảy ra khi lưu. Vui lòng kiểm tra lại kết nối hoặc database.');
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
                if (event.target == saveCollectionModal) {
                    saveCollectionModal.style.display = 'none';
                }
            });

            if (applyFilter) {
                applyFilter.addEventListener('click', () => {
                    const status = document.getElementById('filter-status').value;
                    const address = document.getElementById('filter-address').value;
                    
                    const url = new URL('<?= BASE_URL ?>/admin/management-resource', window.location.origin);
                    url.searchParams.set('page', '1'); // Reset to first page on new filter

                    if (status && status !== 'all') url.searchParams.set('status', status);
                    if (address) url.searchParams.set('address', address);
                    
                    window.location.href = url.toString();
                });
            }

            if (applySearch) {
                applySearch.addEventListener('click', () => {
                    const search = document.getElementById('search-input').value;
                    
                    const url = new URL('<?= BASE_URL ?>/admin/management-resource', window.location.origin);
                    url.searchParams.set('page', '1');

                    if (search) url.searchParams.set('search', search);
                    
                    window.location.href = url.toString();
                });
            }
        });
    </script>
</body>

</html>