<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Báo cáo dẫn khách</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../Public/Css/style.css">
    <script src="../Public/Js/script.js"></script>
</head>

<body>
    <div class="app-container" style="background: white;">

        <header class="detail-header" style="justify-content: flex-start;">
            <a href="<?= BASE_URL ?>/superadmin/home" class="header-icon-btn"><i class="fa-solid fa-chevron-left"></i></a>
            <div class="detail-title" style="margin-left: 15px;">Báo cáo dẫn khách</div>
        </header>

        <div class="search-box-blue-border" style="width: 65%;">
            <input type="text" id="report-search-input" placeholder="Tên đầu chủ">
            <i class="fa-solid fa-magnifying-glass" id="report-search-btn" style="cursor: pointer;"></i>
        </div>

        <div class="table-wrapper" style="padding-bottom: 80px;">
            <table class="report-list-table">
                <thead>
                    <tr>
                        <th style="padding-left: 10px; padding-right: 5px; text-align: left;">Thời gian gửi</th>
                        <th style="text-align: left; padding-left: 5px; padding-right: 5px;">Người gửi</th>
                        <th class="text-center" style="padding-left: 5px; padding-right: 5px;">SĐT</th>
                        <th class="text-right" style="padding-right: 10px; padding-left: 5px;">Đầu khách</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($reports)) : ?>
                        <tr>
                            <td colspan="4" style="text-align:center; padding:20px; color:#666;">Không có báo cáo nào.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($reports as $r) :
                            $time = !empty($r['created_at']) ? date('d/m/Y H:i', strtotime($r['created_at'])) : '';
                            $sender = htmlspecialchars($r['sender_name'] ?? '');
                            $phone = htmlspecialchars($r['customer_phone'] ?? '');
                            $customerName = htmlspecialchars($r['customer_name'] ?? '');
                        ?>
                            <tr onclick="window.location.href='<?= BASE_URL ?>/superadmin/report-customer?id=<?= (int)$r['id'] ?>'" style="cursor: pointer;">
                                <td style="padding-left: 10px; padding-right: 5px;"><?= $time ?></td>
                                <td style="padding-left: 5px; padding-right: 5px;"><?= $sender ?></td>
                                <td class="text-center" style="padding-left: 5px; padding-right: 5px;"><?= $phone ?></td>
                                <td class="text-right" style="padding-right: 10px; padding-left: 5px;"><?= $customerName ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4">
                            <div class="pagination-container">
                                <?php
                                $queryBase = [];
                                if (!empty($search)) $queryBase['q'] = $search;
                                $prev = max(1, $page - 1);
                                $next = min($totalPages, $page + 1);
                                ?>
                                <a href="<?= BASE_URL ?>/superadmin/report-list?<?= http_build_query(array_merge($queryBase, ['page' => $prev])) ?>" class="page-link"><i class="fa-solid fa-chevron-left"></i></a>
                                <?php for ($i = 1; $i <= $totalPages; $i++) : ?>
                                    <a href="<?= BASE_URL ?>/superadmin/report-list?<?= http_build_query(array_merge($queryBase, ['page' => $i])) ?>" class="page-link <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
                                <?php endfor; ?>
                                <a href="<?= BASE_URL ?>/superadmin/report-list?<?= http_build_query(array_merge($queryBase, ['page' => $next])) ?>" class="page-link"><i class="fa-solid fa-chevron-right"></i></a>
                            </div>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div id="bottom-nav-container">
            <?php require_once __DIR__ . '/layouts/bottom-nav.php'; ?>
        </div>

    </div>
    <script src="../Public/Js/script.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('report-search-input');
            const searchBtn = document.getElementById('report-search-btn');
            const tableRows = document.querySelectorAll('.report-list-table tbody tr');

            function filterTable() {
                const searchTerm = searchInput.value.toLowerCase().trim();
                tableRows.forEach(row => {
                    const rowText = row.textContent.toLowerCase();
                    row.style.display = rowText.includes(searchTerm) ? '' : 'none';
                });
            }

            if (searchInput) {
                searchInput.addEventListener('input', filterTable);
            }
            if (searchBtn) {
                searchBtn.addEventListener('click', filterTable);
            }
        });
    </script>
</body>

</html>