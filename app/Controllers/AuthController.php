<?php


class AuthController extends Controller
{
    public function login()
    {
        $this->view('auth/login');
    }

    public function handleLogin()
    {
        $identity = $_POST['identity'] ?? '';
        $password = $_POST['password'] ?? '';

        $user = User::findForLogin($identity);

        if (!$user || !password_verify($password, $user['password'])) {
            // $_SESSION['error'] = 'Sai thông tin đăng nhập';
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        if ($user['trang_thai'] == 0) {
            // $_SESSION['error'] = 'Tài khoản đã bị khóa';
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $_SESSION['user'] = $user;

        // KIỂM TRA ROLE VÀ REDIRECT
        // Redirect theo role (normalize bằng Auth::role())
        require_once __DIR__ . '/../../core/Auth.php';
        $role = \Auth::role();

        if ($role === 'super_admin') {
            header('Location: ' . BASE_URL . '/superadmin/home');
            exit;
        }

        if ($role === 'admin') {
            header('Location: ' . BASE_URL . '/admin/home');
            exit;
        }

        // Mặc định user thường
        header('Location: ' . BASE_URL . '/home');
        exit;
    }


    public function register()
    {
        $this->view('auth/register');
    }

    public function handleRegister()
    {
        $data = [
            'so_dien_thoai'   => trim($_POST['so_dien_thoai'] ?? ''),
            'password'        => $_POST['password'] ?? '',
            'ho_ten'          => trim($_POST['ho_ten'] ?? ''),
            'nam_sinh'        => $_POST['nam_sinh'] ?? '',
            'dia_chi'         => trim($_POST['dia_chi'] ?? ''),
            'gioi_tinh'       => $_POST['gioi_tinh'] ?? 'khac',
            'email'           => trim($_POST['email'] ?? ''),
            'link_fb'         => trim($_POST['link_fb'] ?? ''),
            'ma_gioi_thieu'   => trim($_POST['ma_gioi_thieu'] ?? ''),
            'loai_tai_khoan'  => $_POST['loai_tai_khoan'] ?? 'nhan_vien',
            'phong_ban'       => $_POST['phong_ban'] ?? null,
        ];

        // ===== VALIDATE BẮT BUỘC =====
        if (
            empty($data['so_dien_thoai']) ||
            empty($data['password']) ||
            empty($data['ho_ten']) ||
            empty($data['nam_sinh'])
        ) {
            $_SESSION['error'] = 'Vui lòng nhập đầy đủ thông tin bắt buộc';
            header('Location: ' . BASE_URL . '/register');
            exit;
        }

        // ===== CHECK TRÙNG SĐT =====
        if (User::findByPhone($data['so_dien_thoai'])) {
            $_SESSION['error'] = 'Số điện thoại đã tồn tại';
            header('Location: ' . BASE_URL . '/register');
            exit;
        }

        // ===== CHECK TRÙNG EMAIL (NẾU CÓ) =====
        if (!empty($data['email']) && User::findByEmail($data['email'])) {
            $_SESSION['error'] = 'Email đã tồn tại';
            header('Location: ' . BASE_URL . '/register');
            exit;
        }

        // ===== HASH PASSWORD =====
        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);

        // ===== HANDLE UPLOADED CCCD IMAGE =====
        if (!empty($_FILES['anh_cccd']) && $_FILES['anh_cccd']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['anh_cccd'];
            $allowed = ['image/jpeg', 'image/png', 'image/webp'];
            if ($file['size'] > 3 * 1024 * 1024) {
                $_SESSION['error'] = 'Kích thước ảnh không được vượt quá 3MB';
                header('Location: ' . BASE_URL . '/register');
                exit;
            }
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            if (!in_array($mime, $allowed, true)) {
                $_SESSION['error'] = 'Định dạng ảnh không hợp lệ (chỉ JPG/PNG/WEBP)';
                header('Location: ' . BASE_URL . '/register');
                exit;
            }

            $uploadsDir = realpath(__DIR__ . '/../../public') . '/uploads';
            if (!is_dir($uploadsDir)) {
                mkdir($uploadsDir, 0755, true);
            }

            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
            $dest = $uploadsDir . '/' . $filename;

            if (!move_uploaded_file($file['tmp_name'], $dest)) {
                $_SESSION['error'] = 'Không lưu được ảnh. Vui lòng thử lại.';
                header('Location: ' . BASE_URL . '/register');
                exit;
            }

            // Save filename relative to public
            $data['anh_cccd'] = 'uploads/' . $filename;
        }

        // ===== VALIDATE 'loai_tai_khoan' =====
        $allowedRoles = ['nhan_vien', 'quan_ly', 'admin'];
        if (!in_array($data['loai_tai_khoan'], $allowedRoles, true)) {
            $data['loai_tai_khoan'] = 'nhan_vien';
        }

        $data['quyen'] = 'user';
        $data['trang_thai'] = 1;

        $result = User::createWithRole($data);

        if (!$result) {
            $_SESSION['error'] = 'Đăng ký thất bại, vui lòng thử lại';
            header('Location: ' . BASE_URL . '/register');
            exit;
        }


        $_SESSION['success'] = 'Đăng ký thành công, vui lòng đăng nhập';
        header('Location: ' . BASE_URL . '/login');
    }



    // Form nhập email
    public function forgot()
    {
        $this->view('auth/forgot');
    }

    // Gửi token
    public function handleForgot()
    {
        $email = $_POST['email'] ?? '';

        if (!User::findByEmail($email)) {
            $_SESSION['error'] = 'Email không tồn tại';
            header('Location: ' . BASE_URL . '/forgot-password');
            exit;
        }

        $token = bin2hex(random_bytes(32));

        User::createResetToken($email, $token);

        // DEMO: hiển thị link (thực tế gửi email)
        $_SESSION['success'] =
            "Link reset: " . BASE_URL . "/reset-password?token=$token";

        header('Location: ' . BASE_URL . '/forgot-password');
    }

    // Form nhập mật khẩu mới
    public function reset()
    {
        if (empty($_GET['token'])) {
            die('Token không hợp lệ');
        }

        $this->view('auth/reset');
    }

    // Xử lý reset
    public function handleReset()
    {
        $token    = $_POST['token'];
        $password = $_POST['password'];
        $confirm  = $_POST['confirm'];

        if ($password !== $confirm) {
            die('Mật khẩu không khớp');
        }

        $email = User::getEmailByToken($token);
        if (!$email) {
            die('Token không hợp lệ hoặc đã hết hạn');
        }

        User::updatePassword(
            $email,
            password_hash($password, PASSWORD_BCRYPT)
        );

        User::deleteToken($token);

        $_SESSION['success'] = 'Đổi mật khẩu thành công';
        header('Location: ' . BASE_URL . '/login');
    }

    public function logout()
    {
        session_destroy();
        header('Location: ' . BASE_URL . '/login');
    }
}
