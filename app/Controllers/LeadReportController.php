<?php

class LeadReportController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->requireRole([ROLE_SUPER_ADMIN]);
    }

    public function list()
    {
        require_once __DIR__ . '/../Models/LeadReport.php';

        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = 10;
        $search = isset($_GET['q']) ? trim($_GET['q']) : null;

        $offset = ($page - 1) * $perPage;
        $reports = LeadReport::getList($perPage, $offset, $search);
        $total = LeadReport::countAll($search);
        $totalPages = (int)ceil($total / $perPage);

        $this->view('superadmin/report-list', [
            'reports' => $reports,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => $totalPages,
            'search' => $search
        ]);
    }

    public function detail()
    {
        require_once __DIR__ . '/../Models/LeadReport.php';

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            header('Location: ' . BASE_URL . '/superadmin/report-list');
            exit;
        }

        $report = LeadReport::getById($id);
        if (!$report) {
            header('Location: ' . BASE_URL . '/superadmin/report-list');
            exit;
        }

        $this->view('superadmin/report-customer', [
            'report' => $report
        ]);
    }
}
