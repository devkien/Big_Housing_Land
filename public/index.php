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

/**
 * ============================
 * kien
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
$router->get('/logout', 'MainController@logout', 'auth');
// Profile routes
$router->get('/profile', 'MainController@profile', 'auth');
$router->get('/detailprofile', 'MainController@detailprofile', 'auth');
$router->get('/editprofile', 'MainController@editprofile', 'auth');
$router->post('/editprofile', 'MainController@editprofile', 'auth');
// Change password routes
$router->get('/changepassword', 'MainController@changepassword', 'auth');
$router->post('/changepassword', 'MainController@changepassword', 'auth');
$router->get('/management-resource', 'MainController@resource', 'auth');
$router->get('/management-resource-rent', 'MainController@resourceRent', 'auth');
$router->get('/management-resource-sum', 'MainController@resourceSum', 'auth');
$router->get('/management-resource-sum2', 'MainController@resourceSum2', 'auth');
$router->get('/report_list', 'MainController@reportList', 'auth');
$router->post('/report_list', 'MainController@reportList', 'auth');
$router->get('/report_customer', 'MainController@reportCustomerDetail', 'auth');
$router->get('/detail', 'MainController@detail', 'auth');
$router->get('/collection', 'MainController@collection', 'auth');
$router->get('/cre-collection', 'MainController@creCollection', 'auth');
$router->post('/cre-collection', 'MainController@creCollection', 'auth');
$router->post('/collection-rename', 'MainController@renameCollection', 'auth');
$router->post('/collection-delete', 'MainController@deleteCollection', 'auth');
$router->post('/add-to-collection', 'MainController@addToCollection', 'auth');
$router->get('/get-property-collections', 'MainController@getPropertyCollections', 'auth');
$router->get('/policy', 'MainController@policy', 'auth');
$router->get('/info', 'MainController@info', 'auth');
$router->get('/internal-info-detail', 'MainController@internalInfoDetail', 'auth');
$router->get('/notification', 'MainController@notification', 'auth');
$router->get('/auto-match', 'MainController@autoMatch', 'auth');
$router->post('/auto-match', 'MainController@autoMatch', 'auth');

$router->get('/terms-service', 'MainController@termsService', 'auth');
$router->get('/privacy-policy', 'MainController@privacyPolicy', 'auth');
$router->get('/payment-policy', 'MainController@paymentPolicy', 'auth');
$router->get('/cookie-policy', 'MainController@cookiePolicy', 'auth');



// ==================== Admin ====================
$router->get('/admin/home', 'AdminController@index', 'role:admin,super_admin');
$router->get('/admin/logout', 'AdminController@logout', 'role:admin,super_admin');
// Profile routes
$router->get('/admin/profile', 'AdminController@profile', 'role:admin,super_admin');
$router->get('/admin/detailprofile', 'AdminController@detailprofile', 'role:admin,super_admin');
$router->get('/admin/editprofile', 'AdminController@editprofile', 'role:admin,super_admin');
$router->post('/admin/editprofile', 'AdminController@editprofile', 'role:admin,super_admin');
// Change password routes
$router->get('/admin/changepassword', 'AdminController@changepassword', 'role:admin,super_admin');
$router->post('/admin/changepassword', 'AdminController@changepassword', 'role:admin,super_admin');
// Resource  management routes kho tài nguyên
$router->get('/admin/management-resource', 'AdminController@resource', 'role:admin,super_admin');
$router->get('/admin/management-resource-rent', 'AdminController@resourceRent', 'role:admin,super_admin');
$router->get('/admin/management-resource-sum', 'AdminController@resourceSum', 'role:admin,super_admin');
$router->get('/admin/management-resource-sum_2', 'AdminController@resourceSum2', 'role:admin,super_admin');

$router->get('/admin/management-resource-post', 'AdminController@resourcePost', 'role:admin,super_admin');
$router->post('/admin/management-resource-post', 'AdminController@resourcePost', 'role:admin,super_admin');
$router->post('/admin/update-resource-status', 'AdminController@updateResourceStatus', 'role:admin,super_admin');
$router->post('/admin/add-to-collection', 'AdminController@addToCollection', 'role:admin,super_admin');
$router->get('/admin/get-property-collections', 'AdminController@getPropertyCollections', 'role:admin,super_admin');
$router->get('/admin/detail', 'AdminController@detail', 'role:admin,super_admin');
$router->get('/admin/report_list', 'AdminController@reportList', 'role:admin,super_admin');
$router->get('/admin/report_customer', 'AdminController@reportCustomerDetail', 'role:admin,super_admin');
$router->post('/admin/report_list', 'AdminController@reportList', 'role:admin,super_admin');
$router->get('/admin/collection', 'AdminController@collection', 'role:admin,super_admin');
$router->get('/admin/cre-collection', 'AdminController@creCollection', 'role:admin,super_admin');
$router->post('/admin/cre-collection', 'AdminController@creCollection', 'role:admin,super_admin');
$router->post('/admin/collection-rename', 'AdminController@renameCollection', 'role:admin,super_admin');
$router->post('/admin/collection-delete', 'AdminController@deleteCollection', 'role:admin,super_admin');

$router->get('/admin/notification', 'AdminController@notification', 'role:admin,super_admin');
$router->get('/admin/cre-notification', 'AdminController@creNotification', 'role:admin,super_admin');
$router->post('/admin/cre-notification', 'AdminController@creNotification', 'role:admin,super_admin');
$router->get('/admin/auto-match', 'AdminController@autoMatch', 'role:admin,super_admin');
$router->post('/admin/auto-match', 'AdminController@autoMatch', 'role:admin,super_admin');
$router->get('/admin/policy', 'AdminController@policy', 'role:admin,super_admin');
// Admin - Internal Info routes
$router->get('/admin/info', 'AdminController@info', 'role:admin,super_admin');
$router->get('/admin/add-internal-info', 'AdminController@addInternalInfo', 'role:admin,super_admin');
$router->post('/admin/add-internal-info', 'AdminController@addInternalInfo', 'role:admin,super_admin');
$router->get('/admin/internal-info-list', 'AdminController@internalInfoList', 'role:admin,super_admin');
$router->get('/admin/internal-info-detail', 'AdminController@InternalInfoDetail', 'role:admin,super_admin');
$router->get('/admin/internal-info-edit', 'AdminController@InternalInfoEdit', 'role:admin,super_admin');
$router->post('/admin/internal-info-edit', 'AdminController@InternalInfoEdit', 'role:admin,super_admin');
$router->post('/admin/internal-info-delete', 'AdminController@deleteInternalInfo', 'role:admin,super_admin');
$router->get('/admin/terms-service', 'AdminController@termsService', 'role:admin,super_admin');
$router->get('/admin/privacy-policy', 'AdminController@privacyPolicy', 'role:admin,super_admin');
$router->get('/admin/payment-policy', 'AdminController@paymentPolicy', 'role:admin,super_admin');
$router->get('/admin/cookie-policy', 'AdminController@cookiePolicy', 'role:admin,super_admin');

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

$router->get('/superadmin/update-personnel', 'MemberController@updatepersonnel', 'role:super_admin');
$router->post('/superadmin/update-personnel', 'MemberController@updatepersonnel', 'role:super_admin');

// Resource  management routes
$router->get('/superadmin/management-resource', 'ResourceController@resource', 'role:super_admin');
$router->get('/superadmin/management-resource-detail', 'ResourceController@resourceDetail', 'role:super_admin');
$router->get('/superadmin/management-resource-rent', 'ResourceController@resourceRent', 'role:super_admin');
$router->get('/superadmin/management-resource-post', 'ResourceController@resourcePost', 'role:super_admin');
$router->post('/superadmin/management-resource-post', 'ResourceController@resourcePost', 'role:super_admin');
// Endpoint to update property status via AJAX
$router->post('/superadmin/property-update-status', 'ResourceController@updateStatus', 'role:super_admin');
// Save resource to collections (AJAX)
$router->post('/superadmin/save-to-collections', 'ResourceController@saveToCollections', 'role:super_admin');

// Collection management routes
$router->get('/superadmin/collection', 'CollectionController@collection', 'role:super_admin');
$router->get('/superadmin/collection-detail', 'CollectionController@collectionDetail', 'role:super_admin');
$router->get('/superadmin/cre-collection', 'CollectionController@creCollection', 'role:super_admin');
$router->post('/superadmin/cre-collection', 'CollectionController@creCollection', 'role:super_admin');
// AJAX endpoints for collection management
$router->post('/superadmin/collection-rename', 'CollectionController@renameCollection', 'role:super_admin');
$router->post('/superadmin/collection-delete', 'CollectionController@deleteCollection', 'role:super_admin');
$router->post('/superadmin/collection-remove-item', 'CollectionController@removeItem', 'role:super_admin');

$router->get('/superadmin/auto-match', 'AutoMatchController@index', 'role:super_admin');
$router->post('/superadmin/auto-match', 'AutoMatchController@autoMatch', 'role:super_admin');
// Lead reports
$router->get('/superadmin/report-list', 'LeadReportController@list', 'role:super_admin');
$router->get('/superadmin/report-customer', 'LeadReportController@detail', 'role:super_admin');
// Notifications (deal posts)
$router->get('/superadmin/notification', 'NotificationController@notification', 'role:super_admin');
$router->get('/superadmin/cre-notification', 'NotificationController@creNotification', 'role:super_admin');
$router->post('/superadmin/cre-notification', 'NotificationController@creNotification', 'role:super_admin');

$router->get('/superadmin/policy', 'PolicyController@index', 'role:super_admin');


// Information routes
$router->get('/superadmin/info', 'InformationController@info', 'role:super_admin,admin');
$router->get('/superadmin/add-internal-info', 'InformationController@addInternalInfo', 'role:super_admin,admin');
$router->post('/superadmin/add-internal-info', 'InformationController@addInternalInfo', 'role:super_admin,admin');
$router->get('/superadmin/internal-info-list', 'InformationController@internalInfoList', 'role:super_admin,admin');
$router->post('/superadmin/internal-info-list', 'InformationController@internalInfoList', 'role:super_admin,admin');

// Pin/unpin internal info (AJAX)
$router->post('/superadmin/internal-info-pin', 'InformationController@pinInternalInfo', 'role:super_admin,admin');

$router->get('/superadmin/internal-info-detail', 'InformationController@InternalInfoDetail', 'role:super_admin,admin');
$router->get('/superadmin/internal-info-edit', 'InformationController@InternalInfoEdit', 'role:super_admin,admin');
$router->post('/superadmin/internal-info-edit', 'InformationController@InternalInfoEdit', 'role:super_admin,admin');


// terms of service
$router->get('/superadmin/terms-service', 'PolicyController@termsService', 'role:super_admin');
$router->get('/superadmin/privacy-policy', 'PolicyController@privacyPolicy', 'role:super_admin');
$router->get('/superadmin/payment-policy', 'PolicyController@paymentPolicy', 'role:super_admin');
$router->get('/superadmin/cookie-policy', 'PolicyController@cookiePolicy', 'role:super_admin');






/**
 * ============================
 * DISPATCH
 * ============================
 */
$router->dispatch();
