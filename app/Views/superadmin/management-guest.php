<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý nhân sự</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
</head>

<body>
    <div class="app-container" style="background: white;">

        <header class="detail-header">
            <a href="<?= BASE_URL ?>/superadmin/home" class="header-icon-btn"><i class="fa-solid fa-chevron-left"></i></a>
            <div class="detail-title">Quản lý nhân sự</div>
            <div style="width: 20px;"></div>
        </header>

        <div class="hr-tab-container">
            <button class="hr-tab-btn" onclick="window.location.href='<?= BASE_URL ?>/superadmin/management-owner'">Quản lý đầu chủ</button>
            <button class="hr-tab-btn active">Quản lý đầu khách</button>
        </div>

        <?php require_once __DIR__ . '/../partials/alert.php'; ?>

        <div class="hr-toolbar">
            <div style="display: flex; gap: 10px;">
                <button id="btn-hr-filter" class="btn-tool-outline"><i class="fa-solid fa-filter"></i> Lọc</button>
                <button id="btn-hr-search" class="btn-tool-outline"><i class="fa-solid fa-magnifying-glass"></i> Tìm kiếm</button>
            </div>
            <a href="<?= BASE_URL ?>/superadmin/add-personnel" class="btn-add-blue" style="text-decoration: none;">
                <i class="fa-solid fa-user-plus"></i> Thêm nhân sự
            </a>
        </div>

        <div class="table-wrapper" style="margin-bottom: 0;">
            <table class="hr-table">
                <thead>
                    <tr>
                        <th style="padding-left:10px; width: 40px;">XOÁ</th>
                        <th>MÃ NS</th>
                        <th>TRẠNG THÁI</th>
                        <th>HỌ TÊN</th>
                        <th>SĐT</th>
                        <th style="padding-right:10px;">ĐỊA CHỈ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $rows = $users ?? [];
                    if (count($rows) === 0) : ?>
                        <tr>
                            <td colspan="6" style="text-align:center; padding: 20px;">Không có dữ liệu</td>
                        </tr>
                        <?php else:
                        foreach ($rows as $u):
                            $statusClass = (isset($u['trang_thai']) && $u['trang_thai'] == 1) ? 'status-active' : 'status-pause';
                            $statusText = (isset($u['trang_thai']) && $u['trang_thai'] == 1) ? 'Hoạt động' : 'Tạm dừng';
                        ?>
                            <tr>
                                <td style="padding-left:10px; text-align: center;">
                                    <form method="POST" action="<?= BASE_URL ?>/superadmin/management-delete" style="display:inline;">
                                        <?php require_once __DIR__ . '/../../Helpers/functions.php';
                                        echo csrf_field(); ?>
                                        <input type="hidden" name="id" value="<?= htmlspecialchars($u['id']) ?>">
                                        <button type="button" style="background:transparent;border:none;padding:0;cursor:pointer;">
                                            <i class="fa-regular fa-trash-can icon-trash-red"></i>
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    <a class="text-id-blue" href="<?= BASE_URL ?>/superadmin/update-personnel?id=<?= (int)($u['id'] ?? 0) ?>">
                                        <?= htmlspecialchars($u['ma_nhan_su'] ?? '') ?>
                                    </a>
                                </td>
                                <td class="<?= $statusClass ?>"><?= $statusText ?></td>
                                <td><?= htmlspecialchars($u['ho_ten'] ?? '') ?></td>
                                <td><?= htmlspecialchars($u['so_dien_thoai'] ?? '') ?></td>
                                <td style="padding-right:10px;"><?= htmlspecialchars($u['dia_chi'] ?? '') ?></td>
                            </tr>
                    <?php endforeach;
                    endif; ?>
                </tbody>
            </table>
        </div>

        <div class="pagination-container" style="text-align:center; margin-top:18px;">
            <?php if (isset($pages) && $pages > 1): ?>
                <?php if ($page > 1): ?>
                    <a class="page-link" href="<?= BASE_URL ?>/superadmin/management-guest?page=<?= $page - 1 ?><?= !empty($search) ? '&q=' . urlencode($search) : '' ?>">&lt;</a>
                <?php endif; ?>

                <?php for ($p = 1; $p <= $pages; $p++): ?>
                    <?php if ($p == $page): ?>
                        <span class="page-link active"><?= $p ?></span>
                    <?php else: ?>
                        <a class="page-link" href="<?= BASE_URL ?>/superadmin/management-guest?page=<?= $p ?><?= !empty($search) ? '&q=' . urlencode($search) : '' ?>"><?= $p ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($page < $pages): ?>
                    <a class="page-link" href="<?= BASE_URL ?>/superadmin/management-guest?page=<?= $page + 1 ?><?= !empty($search) ? '&q=' . urlencode($search) : '' ?>">&gt;</a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <!-- Shared modals (delete confirmation, success notification) -->
        <?php require_once __DIR__ . '/../partials/modals.php'; ?>

        <div id="bottom-nav-container">
            <?php require_once __DIR__ . '/layouts/bottom-nav.php'; ?>
        </div>
    </div>
</body>

</html>