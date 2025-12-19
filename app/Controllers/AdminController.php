<?php

class AdminController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        // Admin area: allow admin and super_admin
        $this->requireRole([ROLE_ADMIN, ROLE_SUPER_ADMIN]);
    }
    public function index()
    {
        $this->view('admin/home');
    }
}
