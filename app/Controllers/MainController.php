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

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../Models/Customer.php';

            $ten_khach = trim($_POST['ten_khach'] ?? '');
            $sdt_khach = trim($_POST['sdt_khach'] ?? '');
            
            if (empty($ten_khach)) {
                $_SESSION['error'] = 'Vui lòng nhập tên khách hàng.';
                header('Location: ' . BASE_URL . '/profile');
                exit;
            }

            $data = [
                'user_id' => $user['id'],
                'ten_khach' => $ten_khach,
                'nam_sinh' => trim($_POST['nam_sinh_khach'] ?? ''),
                'sdt_khach' => $sdt_khach,
                'so_cccd' => trim($_POST['cccd_khach'] ?? ''),
                'ghi_chu' => trim($_POST['ghi_chu_nguoi_dan'] ?? '')
            ];

            $customerId = Customer::create($data);

            if ($customerId) {
                // Xử lý upload ảnh
                if (!empty($_FILES['images'])) {
                    $files = $_FILES['images'];
                    $count = count($files['name']);
                    // Giới hạn tối đa 3 ảnh như UI
                    $count = min($count, 3);

                    $uploadDir = 'uploads/customers/' . $customerId . '/';
                    $absDir = __DIR__ . '/../../public/' . $uploadDir;
                    
                    if (!is_dir($absDir)) {
                        mkdir($absDir, 0755, true);
                    }

                    for ($i = 0; $i < $count; $i++) {
                        if ($files['error'][$i] === UPLOAD_ERR_OK) {
                            $tmpName = $files['tmp_name'][$i];
                            $name = basename($files['name'][$i]);
                            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
                            
                            if (in_array($ext, $allowed)) {
                                $newName = uniqid() . '.' . $ext;
                                if (move_uploaded_file($tmpName, $absDir . $newName)) {
                                    Customer::addImage($customerId, $uploadDir . $newName);
                                }
                            }
                        }
                    }
                }

                $_SESSION['success'] = 'Báo cáo thành công.';
                header('Location: ' . BASE_URL . '/profile');
                exit;
            } else {
                $_SESSION['error'] = 'Có lỗi xảy ra khi lưu dữ liệu.';
                header('Location: ' . BASE_URL . '/profile');
                exit;
            }
        }

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

    //Dau khach lay du lieu xem vao kho tai nguyen
    public function resource()
    {
        // list kho_nha_dat
        require_once __DIR__ . '/../Models/Property.php';
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = 12;
        $search = isset($_GET['q']) ? trim($_GET['q']) : null;
        $status = isset($_GET['status']) ? trim($_GET['status']) : null;
        $address = isset($_GET['address']) ? trim($_GET['address']) : null;

        // prefer address as explicit search term
        $searchTerm = $address ?: $search;

        $total = Property::countByLoaiKho('kho_nha_dat', $searchTerm, $status);
        $pages = (int)ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;

        $properties = Property::getByLoaiKho('kho_nha_dat', $perPage, $offset, $searchTerm, $status);

        $this->view('main/resource', [
            'properties' => $properties,
            'page' => $page,
            'pages' => $pages,
            'total' => $total,
            'perPage' => $perPage,
            'search' => $search,
            'status' => $status,
            'address' => $address
        ]);
    }

    public function resourceRent()
    {
        // list kho_cho_thue
        require_once __DIR__ . '/../Models/Property.php';
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = 12;
        $search = isset($_GET['q']) ? trim($_GET['q']) : null;
        $status = isset($_GET['status']) ? trim($_GET['status']) : null;
        $address = isset($_GET['address']) ? trim($_GET['address']) : null;

        $searchTerm = $address ?: $search;

        $total = Property::countByLoaiKho('kho_cho_thue', $searchTerm, $status);
        $pages = (int)ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;

        $properties = Property::getByLoaiKho('kho_cho_thue', $perPage, $offset, $searchTerm, $status);

        $this->view('main/resource-rent', [
            'properties' => $properties,
            'page' => $page,
            'pages' => $pages,
            'total' => $total,
            'perPage' => $perPage,
            'search' => $search,
            'status' => $status,
            'address' => $address
        ]);
    }

    public function reportList()
    {
        require_once __DIR__ . '/../../core/Auth.php';
        $user = \Auth::user();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../Models/Customer.php';
            require_once __DIR__ . '/../Models/LeadReport.php';

            $ten_khach = trim($_POST['ho_ten'] ?? '');
            $sdt_khach = trim($_POST['so_dien_thoai'] ?? '');
            
            if (empty($ten_khach)) {
                $_SESSION['error'] = 'Vui lòng nhập tên khách hàng.';
                header('Location: ' . BASE_URL . '/report_list');
                exit;
            }

            // Dữ liệu lưu vào bảng customers
            $data = [
                'ho_ten' => $ten_khach,
                'nam_sinh' => trim($_POST['nam_sinh_khach'] ?? ''),
                'so_dien_thoai' => $sdt_khach,
                'cccd' => trim($_POST['cccd_khach'] ?? ''),
                'note' => trim($_POST['ghi_chu_nguoi_dan'] ?? '') // Vẫn lưu ghi chú vào khách (nếu cần backup)
            ];

            $customerId = Customer::create($data);

            if ($customerId) {
                // Lưu vào bảng lead_reports
                $reportData = [
                    'user_id' => $user['id'],
                    'customer_id' => $customerId,
                    'note' => trim($_POST['ghi_chu_nguoi_dan'] ?? ''),
                    'status' => 'cho_duyet'
                ];
                
                LeadReport::create($reportData);

                // Xử lý upload ảnh
                if (!empty($_FILES['images'])) {
                    $files = $_FILES['images'];
                    $count = count($files['name']);
                    // Giới hạn tối đa 3 ảnh như UI
                    $count = min($count, 3);

                    $uploadDir = 'uploads/customers/' . $customerId . '/';
                    $absDir = __DIR__ . '/../../public/' . $uploadDir;
                    
                    if (!is_dir($absDir)) {
                        mkdir($absDir, 0755, true);
                    }

                    for ($i = 0; $i < $count; $i++) {
                        if ($files['error'][$i] === UPLOAD_ERR_OK) {
                            $tmpName = $files['tmp_name'][$i];
                            $name = basename($files['name'][$i]);
                            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
                            
                            if (in_array($ext, $allowed)) {
                                $newName = uniqid() . '.' . $ext;
                                if (move_uploaded_file($tmpName, $absDir . $newName)) {
                                    Customer::addImage($customerId, $uploadDir . $newName);
                                }
                            }
                        }
                    }
                }

                $_SESSION['success'] = 'Báo cáo thành công.';
                header('Location: ' . BASE_URL . '/report_list');
                exit;
            } else {
                $_SESSION['error'] = 'Có lỗi xảy ra khi lưu dữ liệu.';
                header('Location: ' . BASE_URL . '/report_list');
                exit;
            }
        }

        $this->view('main/report_list', ['user' => $user]);
    }

    public function detail()
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id <= 0) {
            header('Location: ' . BASE_URL . '/management-resource');
            exit;
        }

        $db = \Database::connect();
        
        // Lấy thông tin bất động sản và người đăng
        $sql = "SELECT p.*, u.ho_ten as user_name, u.so_dien_thoai as user_phone, u.avatar as user_avatar, u.phong_ban 
                FROM properties p 
                LEFT JOIN users u ON p.user_id = u.id 
                WHERE p.id = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $property = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$property) {
             header('Location: ' . BASE_URL . '/management-resource');
             exit;
        }

        // Lấy hình ảnh/media
        require_once __DIR__ . '/../Models/Property.php';
        $media = [];
        if (method_exists('Property', 'getMedia')) {
            $media = Property::getMedia($id);
        }
        $property['media'] = $media;

        $this->view('main/detail', ['property' => $property]);
    }
}
