<?php

class ResourceController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        // Only super_admin
        $this->requireRole([ROLE_SUPER_ADMIN]);
    }
    public function resource()
    {
        $this->view('superadmin/resource');
    }

    public function resourceRent()
    {
        $this->view('superadmin/resource-rent');
    }

    public function resourcePost()
    {
        $this->view('superadmin/resource-post');
    }
}
