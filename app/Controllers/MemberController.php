<?php

class MemberController extends Controller
{
    public function owner()
    {
        // fetch query params for pagination / search
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = 10;
        $search = isset($_GET['q']) ? trim($_GET['q']) : null;
        
        $status = null;
        if (isset($_GET['status']) && $_GET['status'] !== 'all' && $_GET['status'] !== '') {
            $s = $_GET['status'];
            if ($s === 'hoạt động') $status = 1;
            elseif ($s === 'tạm dừng') $status = 2;
            elseif ($s === 'chờ duyệt') $status = 0;
            else $status = (int)$s; // Fallback nếu truyền số trực tiếp
        }

        $total = User::countByRole('admin', $search, $status);
        $pages = (int) ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;

        $users = User::getByRole('admin', $perPage, $offset, $search, $status);

        $this->view('superadmin/management-owner', [
            'users' => $users,
            'page' => $page,
            'pages' => $pages,
            'total' => $total,
            'perPage' => $perPage,
            'search' => $search,
            'status' => $status
        ]);
    }

    public function addpersonnel()
    {
        // If POST, process create
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // simple CSRF check
            $token = $_POST['_csrf'] ?? null;
            require_once __DIR__ . '/../Helpers/functions.php';
            if (!verify_csrf($token)) {
                $_SESSION['error'] = 'Token không hợp lệ. Vui lòng thử lại.';
                header('Location: ' . BASE_URL . '/superadmin/add-personnel');
                exit;
            }

            $data = [];
            $data['ho_ten'] = trim($_POST['ho_ten'] ?? '');
            $data['so_dien_thoai'] = trim($_POST['so_dien_thoai'] ?? '');
            $data['email'] = trim($_POST['email'] ?? '');
            $data['nam_sinh'] = trim($_POST['nam_sinh'] ?? null);
            $data['so_cccd'] = trim($_POST['so_cccd'] ?? null);
            $data['phong_ban'] = trim($_POST['phong_ban'] ?? null);
            $data['ma_nhan_su'] = trim($_POST['ma_nhan_su'] ?? null);
            $data['quyen'] = ($_POST['quyen'] ?? 'user') === 'admin' ? 'admin' : 'user';
            $data['dia_chi'] = trim($_POST['dia_chi'] ?? null);
            $data['nguoi_gioi_thieu'] = trim($_POST['nguoi_gioi_thieu'] ?? null);
            $password = $_POST['password'] ?? '';

            // Basic validation
            if (empty($data['ho_ten']) || empty($data['so_dien_thoai']) || empty($password)) {
                $_SESSION['error'] = 'Vui lòng điền tên, số điện thoại và mật khẩu.';
                header('Location: ' . BASE_URL . '/superadmin/add-personnel');
                exit;
            }

            // check phone uniqueness
            require_once __DIR__ . '/../Models/User.php';
            if (User::findByPhone($data['so_dien_thoai'])) {
                $_SESSION['error'] = 'Số điện thoại đã được sử dụng.';
                header('Location: ' . BASE_URL . '/superadmin/add-personnel');
                exit;
            }

            // check employee code uniqueness (ma_nhan_su)
            if (!empty($data['ma_nhan_su']) && User::findByMaNhanSu($data['ma_nhan_su'])) {
                $_SESSION['error'] = 'Mã nhân sự đã được sử dụng.';
                header('Location: ' . BASE_URL . '/superadmin/add-personnel');
                exit;
            }

            $data['password'] = password_hash($password, PASSWORD_DEFAULT);

            try {
                $ok = User::createWithRole($data);
            } catch (PDOException $e) {
                $_SESSION['error'] = 'Lỗi khi lưu vào cơ sở dữ liệu.';
                header('Location: ' . BASE_URL . '/superadmin/add-personnel');
                exit;
            }
            if ($ok) {
                $_SESSION['success'] = 'Tạo nhân sự thành công.';
                header('Location: ' . BASE_URL . '/superadmin/management-owner');
                exit;
            } else {
                $_SESSION['error'] = 'Lỗi khi lưu vào cơ sở dữ liệu.';
                header('Location: ' . BASE_URL . '/superadmin/add-personnel');
                exit;
            }
        }

        $this->view('superadmin/add-personnel');
    }

    public function updatepersonnel()
    {
        // Expect an id via query string: /superadmin/update-personnel?id=123
        $id = $_GET['id'] ?? null;
        if (empty($id) || !is_numeric($id)) {
            $_SESSION['error'] = 'ID không hợp lệ.';
            header('Location: ' . BASE_URL . '/superadmin/management-owner');
            exit;
        }

        require_once __DIR__ . '/../Models/User.php';

        // If POST: handle update
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../Helpers/functions.php';
            $token = $_POST['_csrf'] ?? null;
            if (!verify_csrf($token)) {
                $_SESSION['error'] = 'Token không hợp lệ. Vui lòng thử lại.';
                header('Location: ' . BASE_URL . '/superadmin/update-personnel?id=' . (int)$id);
                exit;
            }

            $data = [];
            $data['ho_ten'] = trim($_POST['ho_ten'] ?? '');
            $data['so_dien_thoai'] = trim($_POST['so_dien_thoai'] ?? '');
            $data['email'] = trim($_POST['email'] ?? '');
            $data['nam_sinh'] = trim($_POST['nam_sinh'] ?? '');
            
            // Nếu so_cccd rỗng thì gán bằng NULL để tránh lỗi Duplicate entry
            $data['so_cccd'] = trim($_POST['so_cccd'] ?? '');
            if ($data['so_cccd'] === '') $data['so_cccd'] = null;

            $data['phong_ban'] = trim($_POST['phong_ban'] ?? null);
            $data['ma_nhan_su'] = trim($_POST['ma_nhan_su'] ?? null);
            $data['ma_gioi_thieu'] = trim($_POST['ma_gioi_thieu'] ?? null);
            $data['link_fb'] = trim($_POST['link_fb'] ?? null);
            $data['dia_chi'] = trim($_POST['dia_chi'] ?? null);
            $data['quyen'] = trim($_POST['quyen'] ?? 'user');
            // Nhận giá trị trạng thái (0: Chờ duyệt, 1: Hoạt động, 2: Tạm dừng)
            $data['trang_thai'] = isset($_POST['trang_thai']) ? (int)$_POST['trang_thai'] : 0;

            // Handle optional password change
            $newPassword = $_POST['password'] ?? '';

            // Handle file upload (anh_cccd)
            if (!empty($_FILES['anh_cccd']) && $_FILES['anh_cccd']['error'] === UPLOAD_ERR_OK) {
                $tmp = $_FILES['anh_cccd']['tmp_name'];
                $origName = basename($_FILES['anh_cccd']['name']);
                $ext = pathinfo($origName, PATHINFO_EXTENSION);
                $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                if (in_array(strtolower($ext), $allowed)) {
                    $uploadsDir = __DIR__ . '/../../public/uploads';
                    if (!is_dir($uploadsDir)) mkdir($uploadsDir, 0755, true);
                    $fileName = 'cccd_' . (int)$id . '_' . time() . '.' . $ext;
                    $dest = $uploadsDir . '/' . $fileName;
                    if (move_uploaded_file($tmp, $dest)) {
                        // store web-accessible path
                        $data['anh_cccd'] = rtrim(BASE_URL, '/') . '/public/uploads/' . $fileName;
                    }
                }
            }

            // Persist changes
            $ok = User::updateProfile((int)$id, $data);

            // If password provided, update it
            if ($ok && !empty($newPassword)) {
                $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
                User::updatePasswordById((int)$id, $hashed);
            }

            if ($ok) {
                $_SESSION['success'] = 'Cập nhật thành công.';
            } else {
                $_SESSION['error'] = 'Lỗi khi lưu vào cơ sở dữ liệu.';
            }
            header('Location: ' . BASE_URL . '/superadmin/update-personnel?id=' . (int)$id);
            exit;
        }

        $user = User::findById((int)$id);
        if (!$user) {
            $_SESSION['error'] = 'Không tìm thấy nhân sự.';
            header('Location: ' . BASE_URL . '/superadmin/management-owner');
            exit;
        }

        $this->view('superadmin/update-personnel', ['user' => $user]);
    }

    public function guest()
    {
        // fetch query params for pagination / search (same logic as owner but for role 'user')
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = 10;
        $search = isset($_GET['q']) ? trim($_GET['q']) : null;

        $total = User::countByRole('user', $search);
        $pages = (int) ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;

        $users = User::getByRole('user', $perPage, $offset, $search);

        $this->view('superadmin/management-guest', [
            'users' => $users,
            'page' => $page,
            'pages' => $pages,
            'total' => $total,
            'perPage' => $perPage,
            'search' => $search
        ]);
    }

    // Handle AJAX delete request
    public function delete()
    {
        // Only allow POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['ok' => false, 'message' => 'Method not allowed']);
            return;
        }
        // Support both form POST and JSON body for AJAX
        $id = $_POST['id'] ?? null;
        $token = $_POST['_csrf'] ?? null;

        // If JSON body (AJAX), decode it
        if (!$id) {
            $body = file_get_contents('php://input');
            $json = json_decode($body, true);
            if (is_array($json)) {
                $id = $json['id'] ?? null;
                // allow CSRF token in JSON as well
                if (isset($json['_csrf'])) $token = $json['_csrf'];
            }
        }

        // Basic id validation
        if (empty($id) || !is_numeric($id)) {
            // If AJAX, return json
            $isJson = (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false) || (strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false);
            if ($isJson) {
                http_response_code(400);
                echo json_encode(['ok' => false, 'message' => 'Invalid id']);
                return;
            }
            $_SESSION['error'] = 'ID không hợp lệ.';
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/superadmin/management-owner'));
            return;
        }

        // Verify CSRF
        require_once __DIR__ . '/../Helpers/functions.php';
        if (!verify_csrf($token)) {
            $isJson = (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false) || (strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false);
            if ($isJson) {
                http_response_code(403);
                echo json_encode(['ok' => false, 'message' => 'CSRF token invalid']);
                return;
            }
            $_SESSION['error'] = 'Token không hợp lệ. Vui lòng thử lại.';
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/superadmin/management-owner'));
            return;
        }

        require_once __DIR__ . '/../Models/User.php';
        $ok = User::deleteById((int)$id);

        $isJson = (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false) || (strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false);

        if ($ok) {
            if ($isJson) {
                echo json_encode(['ok' => true, 'message' => 'Đã xóa thành công']);
                return;
            }
            $_SESSION['success'] = 'Xóa nhân sự thành công.';
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/superadmin/management-owner'));
            return;
        } else {
            if ($isJson) {
                http_response_code(500);
                echo json_encode(['ok' => false, 'message' => 'Lỗi server khi xóa']);
                return;
            }
            $_SESSION['error'] = 'Lỗi khi xóa trên server.';
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/superadmin/management-owner'));
            return;
        }
    }
}
