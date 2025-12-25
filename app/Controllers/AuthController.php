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

        if ($user['trang_thai'] != 1) {
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
            'gioi_tinh'       => $_POST['gioi_tinh'] ?? '',
            'email'           => trim($_POST['email'] ?? ''),
            'link_fb'         => trim($_POST['link_fb'] ?? ''),
            'ma_gioi_thieu'   => trim($_POST['ma_gioi_thieu'] ?? ''),
            'loai_tai_khoan'  => $_POST['loai_tai_khoan'] ?? '',
            'phong_ban'       => $_POST['phong_ban'] ?? '',
            'vi_tri'          => $_POST['vi_tri'] ?? '',
        ];
        // Helper lưu lại input cũ (trừ password)
        $saveOldInput = function() use ($data) {
            $old = $data;
            unset($old['password']);
            $_SESSION['old'] = $old;
        };

        // ===== VALIDATE BẮT BUỘC =====
        if (
            empty($data['so_dien_thoai']) ||
            empty($data['password']) ||
            empty($data['ho_ten']) ||
            empty($data['nam_sinh']) ||
            empty($data['dia_chi']) ||
            empty($data['gioi_tinh']) ||
            empty($data['email']) ||
            empty($data['link_fb']) ||
            empty($data['ma_gioi_thieu']) ||
            empty($data['loai_tai_khoan']) ||
            empty($data['phong_ban']) ||
            empty($data['vi_tri'])
        ) {
            $saveOldInput();
            $_SESSION['error'] = 'Vui lòng nhập đầy đủ thông tin bắt buộc';
            header('Location: ' . BASE_URL . '/register');
            exit;
        }

        // ===== CHECK ẢNH CCCD BẮT BUỘC =====
        if (empty($_FILES['anh_cccd']) || $_FILES['anh_cccd']['error'] !== UPLOAD_ERR_OK) {
            $saveOldInput();
            $_SESSION['error'] = 'Vui lòng tải lên ảnh CCCD';
            header('Location: ' . BASE_URL . '/register');
            exit;
        }

        // ===== CHECK TRÙNG SĐT =====
        if (User::findByPhone($data['so_dien_thoai'])) {
            $saveOldInput();
            $_SESSION['error'] = 'Số điện thoại đã tồn tại';
            header('Location: ' . BASE_URL . '/register');
            exit;
        }

        // ===== CHECK TRÙNG EMAIL (NẾU CÓ) =====
        if (!empty($data['email']) && User::findByEmail($data['email'])) {
            $saveOldInput();
            $_SESSION['error'] = 'Email đã tồn tại';
            header('Location: ' . BASE_URL . '/register');
            exit;
        }

        // ===== CHECK MÃ GIỚI THIỆU TỒN TẠI =====
        if (!User::findByMaNhanSu($data['ma_gioi_thieu'])) {
            $saveOldInput();
            $_SESSION['error'] = 'Mã giới thiệu không tồn tại';
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
                $saveOldInput();
                $_SESSION['error'] = 'Kích thước ảnh không được vượt quá 3MB';
                header('Location: ' . BASE_URL . '/register');
                exit;
            }
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            if (!in_array($mime, $allowed, true)) {
                $saveOldInput();
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
                $saveOldInput();
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

        // Map loai_tai_khoan to quyen
        if ($data['loai_tai_khoan'] === 'quan_ly') {
            $data['quyen'] = 'admin';
        } else {
            // 'nhan_vien' hoặc các trường hợp khác sẽ có quyền 'user'
            $data['quyen'] = 'user';
        }
        $data['trang_thai'] = 0;

        // ===== TỰ ĐỘNG TẠO MÃ USER (MNV01, MNV02...) =====
        $db = \Database::connect();
        // Lấy mã nhân sự gần nhất có định dạng MNV...
        $stmt = $db->query("SELECT ma_nhan_su FROM users WHERE ma_nhan_su LIKE 'MNV%' ORDER BY id DESC LIMIT 1");
        $lastUser = $stmt->fetch(PDO::FETCH_ASSOC);

        $nextNum = 1; // Mặc định bắt đầu là 1
        if ($lastUser && !empty($lastUser['ma_nhan_su'])) {
            // Lấy phần số sau chữ 'MNV' (cắt bỏ 3 ký tự đầu)
            $numPart = substr($lastUser['ma_nhan_su'], 3);
            if (is_numeric($numPart)) {
                $nextNum = (int)$numPart + 1;
            }
        }
        // Tạo mã mới: MNV + số được đệm số 0 (ví dụ: 1 -> 01, 10 -> 10)
        $data['ma_nhan_su'] = 'MNV' . str_pad($nextNum, 2, '0', STR_PAD_LEFT);

        $result = User::createWithRole($data);

        if (!$result) {
            $saveOldInput();
            $_SESSION['error'] = 'Đăng ký thất bại, vui lòng thử lại';
            header('Location: ' . BASE_URL . '/register');
            exit;
        }

        unset($_SESSION['old']); // Xóa dữ liệu cũ nếu thành công
        $_SESSION['success'] = 'Đăng ký thành công, vui lòng chờ xét duyệt';
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