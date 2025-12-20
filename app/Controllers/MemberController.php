<?php

class MemberController extends Controller
{
    public function owner()
    {
        // fetch query params for pagination / search
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = 10;
        $search = isset($_GET['q']) ? trim($_GET['q']) : null;

        $total = User::countByRole('admin', $search);
        $pages = (int) ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;

        $users = User::getByRole('admin', $perPage, $offset, $search);

        $this->view('superadmin/management-owner', [
            'users' => $users,
            'page' => $page,
            'pages' => $pages,
            'total' => $total,
            'perPage' => $perPage,
            'search' => $search
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

            $data['password'] = password_hash($password, PASSWORD_DEFAULT);

            $ok = User::createWithRole($data);
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

        // Read input (support form-encoded or JSON)
        $id = $_POST['id'] ?? null;
        if (!$id) {
            // try JSON body
            $body = file_get_contents('php://input');
            $json = json_decode($body, true);
            $id = $json['id'] ?? null;
        }

        if (empty($id) || !is_numeric($id)) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'message' => 'Invalid id']);
            return;
        }

        require_once __DIR__ . '/../Models/User.php';
        $ok = User::deleteById((int)$id);

        if ($ok) {
            echo json_encode(['ok' => true, 'message' => 'Đã xóa thành công']);
        } else {
            http_response_code(500);
            echo json_encode(['ok' => false, 'message' => 'Lỗi server khi xóa']);
        }
    }
}
