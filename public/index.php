<?php

session_start();

/**
 * ============================
 * BASE CONFIG
 * ============================
 */

// Thư mục project trong localhost
define('BASE_PATH', '/Big_Housing_Land');
define('BASE_URL', BASE_PATH);

/**
 * ============================
 * CORE
 * ============================
 */

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Router.php';
require_once __DIR__ . '/../core/Controller.php';
// Load canonical role constants
require_once __DIR__ . '/../config/roles.php';

/**
 * ============================
 * AUTOLOAD
 * ============================
 */
spl_autoload_register(function ($class) {
    $classPath = str_replace('\\', '/', $class) . '.php';
    $base = __DIR__ . '/../';

    $candidates = [
        $base . 'app/Models/' . $classPath,
        $base . 'app/Controllers/' . $classPath,
        $base . 'app/Helpers/' . $classPath,
        $base . 'core/' . $classPath,
        $base . $classPath, // fallback to root
    ];

    foreach ($candidates as $file) {
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
    // If not found, let PHP trigger its usual error (or you can log here)
});

/**
 * ============================
 * ROUTER
 * ============================
 */
$router = new Router();

/**
 * ============================
 * ROUTES
 * ============================
 */

$router->get('/login', 'AuthController@login');
$router->post('/login', 'AuthController@handleLogin');

$router->get('/register', 'AuthController@register');
$router->post('/register', 'AuthController@handleRegister');

$router->get('/logout', 'AuthController@logout');

$router->get('/forgot-password', 'AuthController@forgot');
$router->post('/forgot-password', 'AuthController@handleForgot');

$router->get('/reset-password', 'AuthController@reset');
$router->post('/reset-password', 'AuthController@handleReset');


// ==================== Main ( Người dùng ) ====================
$router->get('/home', 'MainController@index', 'auth');


// ==================== Admin ====================
$router->get('/admin/home', 'AdminController@index', 'role:admin,super_admin');


// ==================== SuperAdmin ====================

$router->get('/superadmin/home', 'SuperAdminController@index', 'role:super_admin');

$router->get('/superadmin/logout', 'SuperAdminController@logout', 'role:super_admin');

// Profile routes
$router->get('/superadmin/profile', 'SuperAdminController@profile', 'role:super_admin');
$router->get('/superadmin/detailprofile', 'SuperAdminController@detailprofile', 'role:super_admin');
$router->get('/superadmin/editprofile', 'SuperAdminController@editprofile', 'role:super_admin');
$router->post('/superadmin/editprofile', 'SuperAdminController@editprofile', 'role:super_admin');

// Change password routes
$router->get('/superadmin/changepassword', 'SuperAdminController@changepassword', 'role:super_admin');
$router->post('/superadmin/changepassword', 'SuperAdminController@changepassword', 'role:super_admin');

// Member management routes
$router->get('/superadmin/management-owner', 'MemberController@owner', 'role:super_admin');
$router->get('/superadmin/management-guest', 'MemberController@guest', 'role:super_admin');
$router->post('/superadmin/management-delete', 'MemberController@delete', 'role:super_admin');

$router->get('/superadmin/add-personnel', 'MemberController@addpersonnel', 'role:super_admin');
$router->post('/superadmin/add-personnel', 'MemberController@addpersonnel', 'role:super_admin');

/**
 * ============================
 * DISPATCH
 * ============================
 */
$router->dispatch();
