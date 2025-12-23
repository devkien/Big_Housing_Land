<?php

class PolicyController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->requireRole([ROLE_SUPER_ADMIN]);
    }

    public function index()
    {
        $this->view('superadmin/policy');
    }
}
