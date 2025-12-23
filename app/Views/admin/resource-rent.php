<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kho nhà cho thuê</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">
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
            <button class="tab-btn inactive" onclick="window.location.href='<?= BASE_URL ?>/admin/management-resource'">Kho nhà đất</button>
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
                        <th style="padding-left:15px; width: 60px;">LƯU</th>
                        <th style="width: 60px;">GHI CHÚ</th>
                        <th style="width: 100px;">MÃ TÀI NGUYÊN</th>
                        <th style="width: 100px;">THỜI GIAN</th>
                        <th style="width: 120px;">HIỆN TRẠNG</th>
                        <th>ĐỊA CHỈ</th>
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
                            $code = htmlspecialchars($p['ma_hien_thi'] ?? '');
                            $created = !empty($p['created_at']) ? date('d/m/Y', strtotime($p['created_at'])) : '';
                            $status = $statusMap[$p['trang_thai'] ?? ''] ?? ($p['trang_thai'] ?? '');
                            $address = trim($p['dia_chi_chi_tiet'] ?? '');
                            if ($address === '') {
                                $parts = array_filter([$p['tinh_thanh'] ?? '', $p['quan_huyen'] ?? '', $p['xa_phuong'] ?? '']);
                                $address = htmlspecialchars(implode(', ', $parts));
                            } else {
                                $address = htmlspecialchars($address);
                            }
                        ?>
                            <tr
                                onclick="window.location.href='<?= BASE_URL ?>/admin/management-resource-rent?property=<?= htmlspecialchars($p['id']) ?>'">
                                <td style="padding-left:15px;"><i class="fa-regular fa-bookmark icon-save"></i></td>
                                <td><i class="fa-regular fa-note-sticky icon-note"></i></td>
                                <td>
                                    <?= $code ?>
                                </td>
                                <td>
                                    <?= $created ?>
                                </td>
                                <td><span class="status-badge strong">
                                        <?= htmlspecialchars($status) ?>
                                    </span></td>
                                <td style=" padding-right:15px;">
                                    <?= $address ?>
                                </td>
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
                <a href="<?= BASE_URL ?>/admin/management-resource-rent?page=<?= $page - 1 ?>&<?= $queryString ?>" class="page-link"><i class="fa-solid fa-chevron-left"></i></a>
            <?php endif; ?>
            
            <a href="#" class="page-link active"><?= $page ?> / <?= $pages > 0 ? $pages : 1 ?></a>
            
            <?php if ($page < $pages): ?>
                <a href="<?= BASE_URL ?>/admin/management-resource-rent?page=<?= $page + 1 ?>&<?= $queryString ?>" class="page-link"><i class="fa-solid fa-chevron-right"></i></a>
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
        <!-- Modal Lưu vào bộ sưu tập -->
        <div id="save-collection-modal" class="modal">
            <div class="modal-content">
                <h3 style="margin-bottom: 15px; font-size: 16px;">Lưu vào bộ sưu tập</h3>

                <div class="filter-group">
                    <div class="collection-list-select"
                        style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; border-radius: 8px; padding: 5px;">
                        <label class="collection-option"
                            style="display: flex; align-items: center; padding: 10px; border-bottom: 1px solid #eee; cursor: pointer;">
                            <input type="checkbox" name="collection" value="1" style="margin-right: 10px;">
                            <span style="font-size: 14px; color: #000;">Khách hàng tiềm năng</span>
                        </label>
                        <label class="collection-option"
                            style="display: flex; align-items: center; padding: 10px; border-bottom: 1px solid #eee; cursor: pointer;">
                            <input type="checkbox" name="collection" value="2" style="margin-right: 10px;">
                            <span style="font-size: 14px; color: #000;">Nhà đất Hà Đông</span>
                        </label>
                        <label class="collection-option"
                            style="display: flex; align-items: center; padding: 10px; cursor: pointer;">
                            <input type="checkbox" name="collection" value="3" style="margin-right: 10px;">
                            <span style="font-size: 14px; color: #000;">Dự án mới</span>
                        </label>
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
                        <option value="Bán mạnh">Bán mạnh</option>
                        <option value="Tạm dừng bán">Tạm dừng bán</option>
                        <option value="Dừng bán">Dừng bán</option>
                        <option value="Đã bán">Đã bán</option>
                        <option value="Tăng chào">Tăng chào</option>
                        <option value="Hạ chào">Hạ chào</option>
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
            const btnFilter = document.getElementById('btn-filter');
            const closeFilter = document.getElementById('close-filter');
            const applyFilter = document.getElementById('apply-filter');

            if (btnFilter) {
                btnFilter.addEventListener('click', () => {
                    if (filterModal) filterModal.style.display = 'flex';
                });
            }

            if (closeFilter) {
                closeFilter.addEventListener('click', () => {
                    if (filterModal) filterModal.style.display = 'none';
                });
            }

            window.addEventListener('click', (event) => {
                if (event.target == filterModal) {
                    filterModal.style.display = 'none';
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
        });
    </script>
</body>

</html>