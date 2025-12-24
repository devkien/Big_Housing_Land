<?php

class SuperAdminController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        // Only super_admin
        $this->requireRole([ROLE_SUPER_ADMIN]);
    }
    public function index()
    {
        // Load pinned internal posts for news feed
        require_once __DIR__ . '/../Models/InternalPost.php';
        require_once __DIR__ . '/../Models/User.php';
        $pinned = InternalPost::getPinned(6);
        // expand each to include images (getById adds images) and author name
        $pinnedFull = [];
        foreach ($pinned as $p) {
            $full = InternalPost::getById((int)$p['id']);
            if ($full) {
                $author = null;
                if (!empty($full['user_id'])) {
                    $author = User::findById((int)$full['user_id']);
                }
                $full['author_name'] = $author['ho_ten'] ?? $author['name'] ?? 'Big Housing Land';
                $pinnedFull[] = $full;
            }
        }

        $this->view('superadmin/home', ['pinnedPosts' => $pinnedFull]);
    }

    public function logout()
    {
        unset($_SESSION['user']);
        header('Location: ' . BASE_URL . '/login');
        exit;
    }


    public function profile()
    {
        require_once __DIR__ . '/../../core/Auth.php';
        $user = \Auth::user();
        $this->view('superadmin/profile', ['user' => $user]);
    }

    public function detailprofile()
    {
        require_once __DIR__ . '/../../core/Auth.php';
        require_once __DIR__ . '/../Models/User.php';

        $sessionUser = \Auth::user();
        $user = null;
        if (!empty($sessionUser['id'])) {
            $user = User::findById($sessionUser['id']);
        }

        // Fallback to session user if DB lookup fails
        if (!$user) $user = $sessionUser;

        $this->view('superadmin/detailprofile', ['user' => $user]);
    }

    public function editprofile()
    {
        require_once __DIR__ . '/../../core/Auth.php';
        require_once __DIR__ . '/../Models/User.php';

        $sessionUser = \Auth::user();
        if (empty($sessionUser['id'])) {
            // $_SESSION['error'] = 'Người dùng không tồn tại';
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $id = $sessionUser['id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'ho_ten' => trim($_POST['ho_ten'] ?? ''),
                'so_dien_thoai' => trim($_POST['so_dien_thoai'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'nam_sinh' => trim($_POST['nam_sinh'] ?? ''),
                'so_cccd' => trim($_POST['so_cccd'] ?? ''),
                'dia_chi' => trim($_POST['dia_chi'] ?? ''),
            ];

            if (empty($data['so_dien_thoai'])) {
                // $_SESSION['error'] = 'Số điện thoại là bắt buộc';
                header('Location: ' . BASE_URL . '/superadmin/editprofile');
                exit;
            }

            $existing = User::findByPhone($data['so_dien_thoai']);
            if ($existing && !empty($existing['id']) && $existing['id'] != $id) {
                // $_SESSION['error'] = 'Số điện thoại đã được sử dụng';
                header('Location: ' . BASE_URL . '/superadmin/editprofile');
                exit;
            }

            $ok = User::update($id, $data);
            if ($ok) {
                $updated = User::findById($id);
                if ($updated) $_SESSION['user'] = $updated;
                // $_SESSION['success'] = 'Cập nhật hồ sơ thành công';
                header('Location: ' . BASE_URL . '/superadmin/detailprofile');
                exit;
            } else {
                // $_SESSION['error'] = 'Lỗi khi lưu dữ liệu';
                header('Location: ' . BASE_URL . '/superadmin/editprofile');
                exit;
            }
        }

        $user = User::findById($id);
        if (!$user) $user = $sessionUser;

        $this->view('superadmin/editprofile', ['user' => $user]);
    }

    public function changepassword()
    {
        require_once __DIR__ . '/../../core/Auth.php';
        require_once __DIR__ . '/../Models/User.php';

        $sessionUser = \Auth::user();
        if (empty($sessionUser['id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $id = $sessionUser['id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $current = $_POST['current_password'] ?? '';
            $new = $_POST['new_password'] ?? '';
            $confirm = $_POST['confirm_password'] ?? '';

            if (empty($current) || empty($new) || empty($confirm)) {
                $_SESSION['error'] = 'Vui lòng điền đầy đủ các trường';
                header('Location: ' . BASE_URL . '/superadmin/changepassword');
                exit;
            }

            if ($new !== $confirm) {
                $_SESSION['error'] = 'Mật khẩu mới và xác nhận không khớp';
                header('Location: ' . BASE_URL . '/superadmin/changepassword');
                exit;
            }

            $user = User::findById($id);
            if (!$user || !password_verify($current, $user['password'])) {
                $_SESSION['error'] = 'Mật khẩu hiện tại không đúng';
                header('Location: ' . BASE_URL . '/superadmin/changepassword');
                exit;
            }

            $hash = password_hash($new, PASSWORD_BCRYPT);
            $ok = User::updatePasswordById($id, $hash);

            if ($ok) {
                // Refresh session user
                $updated = User::findById($id);
                if ($updated) $_SESSION['user'] = $updated;
                $_SESSION['success'] = 'Đổi mật khẩu thành công';
                header('Location: ' . BASE_URL . '/superadmin/changepassword');
                exit;
            } else {
                $_SESSION['error'] = 'Lỗi khi lưu mật khẩu mới';
                header('Location: ' . BASE_URL . '/superadmin/changepassword');
                exit;
            }
        }

        // Prepare user data for the view
        $user = User::findById($id);
        if (!$user) $user = $sessionUser;

        // Map role to human readable label
        $roleRaw = strtolower($user['loai_tai_khoan'] ?? $user['quyen'] ?? '');
        $roleMap = [
            'nhan_vien' => 'Nhân viên',
            'quan_ly' => 'Cấp quản lý',
            'admin' => 'Quản trị',
            'super_admin' => 'Quản trị'
        ];
        $displayRole = $roleMap[$roleRaw] ?? $roleRaw;

        // Office badge - prefer 'phong_ban' then 'dia_chi'
        $officeBadge = $user['phong_ban'] ?? $user['dia_chi'] ?? '';

        $this->view('superadmin/changepassword', [
            'user' => $user,
            'displayRole' => $displayRole,
            'officeBadge' => $officeBadge
        ]);
    }
}
