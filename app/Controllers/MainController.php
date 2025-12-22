<?php

class MainController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        // Đảm bảo người dùng đã đăng nhập
        if (!isset($_SESSION['user'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    }

    public function index()
    {
        $this->view('main/home');
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
        $this->view('main/profile', ['user' => $user]);
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

        $this->view('main/detailprofile', ['user' => $user]);
    }

    public function editprofile()
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
            $data = [
                'ho_ten' => trim($_POST['ho_ten'] ?? ''),
                'so_dien_thoai' => trim($_POST['so_dien_thoai'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'nam_sinh' => trim($_POST['nam_sinh'] ?? null),
                'so_cccd' => trim($_POST['so_cccd'] ?? null),
                'dia_chi' => trim($_POST['dia_chi'] ?? null),
            ];

            if (empty($data['so_dien_thoai'])) {
                $_SESSION['error'] = 'Số điện thoại là bắt buộc';
                header('Location: ' . BASE_URL . '/editprofile');
                exit;
            }

            $existing = User::findByPhone($data['so_dien_thoai']);
            if ($existing && !empty($existing['id']) && $existing['id'] != $id) {
                $_SESSION['error'] = 'Số điện thoại đã được sử dụng';
                header('Location: ' . BASE_URL . '/editprofile');
                exit;
            }

            $ok = User::update($id, $data);
            if ($ok) {
                $updated = User::findById($id);
                if ($updated) $_SESSION['user'] = $updated;
                $_SESSION['success'] = 'Cập nhật hồ sơ thành công';
                header('Location: ' . BASE_URL . '/detailprofile');
                exit;
            } else {
                $_SESSION['error'] = 'Lỗi khi lưu dữ liệu';
                header('Location: ' . BASE_URL . '/editprofile');
                exit;
            }
        }

        $user = User::findById($id);
        if (!$user) $user = $sessionUser;

        $this->view('main/editprofile', ['user' => $user]);
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
                header('Location: ' . BASE_URL . '/changepassword');
                exit;
            }

            if ($new !== $confirm) {
                $_SESSION['error'] = 'Mật khẩu mới và xác nhận không khớp';
                header('Location: ' . BASE_URL . '/changepassword');
                exit;
            }

            $user = User::findById($id);
            if (!$user || !password_verify($current, $user['password'])) {
                $_SESSION['error'] = 'Mật khẩu hiện tại không đúng';
                header('Location: ' . BASE_URL . '/changepassword');
                exit;
            }

            $hash = password_hash($new, PASSWORD_BCRYPT);
            $ok = User::updatePasswordById($id, $hash);

            if ($ok) {
                $updated = User::findById($id);
                if ($updated) $_SESSION['user'] = $updated;
                $_SESSION['success'] = 'Đổi mật khẩu thành công';
                header('Location: ' . BASE_URL . '/changepassword');
                exit;
            } else {
                $_SESSION['error'] = 'Lỗi khi lưu mật khẩu mới';
                header('Location: ' . BASE_URL . '/changepassword');
                exit;
            }
        }

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
        $officeBadge = $user['phong_ban'] ?? $user['dia_chi'] ?? '';

        $this->view('main/changepassword', [
            'user' => $user,
            'displayRole' => $displayRole,
            'officeBadge' => $officeBadge
        ]);
    }
}
