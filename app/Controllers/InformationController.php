<?php

class InformationController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->requireRole([ROLE_SUPER_ADMIN, ROLE_ADMIN]);
    }

    public function info()
    {
        // Load internal posts and pass to view
        require_once __DIR__ . '/../Models/InternalPost.php';
        $posts = InternalPost::getActive(50, 0);
        $this->view('superadmin/info', ['posts' => $posts]);
    }

    public function addInternalInfo()
    {
        $this->view('superadmin/add-internal-info');
    }
    public function internalInfoList()
    {
        $this->view('superadmin/internal-info-list');
    }
    public function InternalInfoDetail()
    {
        $this->view('superadmin/internal-info-detail');
    }
    public function InternalInfoEdit()
    {
        $this->view('superadmin/internal-info-edit');
    }
}
