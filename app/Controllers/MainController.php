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
        // Load pinned internal posts for news feed (same as admin/superadmin)
        require_once __DIR__ . '/../Models/InternalPost.php';
        require_once __DIR__ . '/../Models/User.php';
        $pinned = InternalPost::getPinned(6);
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

        $this->view('main/home', ['pinnedPosts' => $pinnedFull]);
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
        require_once __DIR__ . '/../Models/Collection.php';
        require_once __DIR__ . '/../../core/Auth.php';

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

        $user = \Auth::user();
        $userId = $user['id'] ?? 0;
        $collections = Collection::getForUser($userId);

        // Build collection counts map for the displayed properties so the view
        // can render bookmark icons as filled when a resource belongs to any collection.
        $propertyIds = array_map(function ($r) {
            return (int)($r['id'] ?? 0);
        }, $properties);
        $collectionMap = Collection::getCountsForProperties(array_filter($propertyIds), 'kho_nha_dat', $userId);

        $this->view('main/resource', [
            'properties' => $properties,
            'page' => $page,
            'pages' => $pages,
            'total' => $total,
            'perPage' => $perPage,
            'search' => $search,
            'status' => $status,
            'address' => $address,
            'collections' => $collections,
            'collectionMap' => $collectionMap
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

        // load collections and collection map for current user so views can show saved state
        require_once __DIR__ . '/../Models/Collection.php';
        require_once __DIR__ . '/../../core/Auth.php';
        $user = \Auth::user();
        $userId = $user['id'] ?? 0;
        $collections = Collection::getForUser($userId);
        $propertyIds = array_map(function ($r) {
            return (int)($r['id'] ?? 0);
        }, $properties);
        $collectionMap = Collection::getCountsForProperties(array_filter($propertyIds), 'kho_cho_thue', $userId);

        $this->view('main/resource-rent', [
            'properties' => $properties,
            'page' => $page,
            'pages' => $pages,
            'total' => $total,
            'perPage' => $perPage,
            'search' => $search,
            'status' => $status,
            'address' => $address,
            'collections' => $collections,
            'collectionMap' => $collectionMap
        ]);
    }

    public function resourceSum()
    {
        // list kho_nha_dat (Sum version)
        require_once __DIR__ . '/../Models/Property.php';
        require_once __DIR__ . '/../Models/Collection.php';
        require_once __DIR__ . '/../../core/Auth.php';

        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = 12;
        $search = isset($_GET['q']) ? trim($_GET['q']) : null;
        $status = isset($_GET['status']) ? trim($_GET['status']) : null;
        $address = isset($_GET['address']) ? trim($_GET['address']) : null;

        $searchTerm = $address ?: $search;

        $total = Property::countByLoaiKho('kho_nha_dat', $searchTerm, $status);
        $pages = (int)ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;

        $properties = Property::getByLoaiKho('kho_nha_dat', $perPage, $offset, $searchTerm, $status);

        $user = \Auth::user();
        $userId = $user['id'] ?? 0;
        $collections = Collection::getForUser($userId);
        $propertyIds = array_map(function ($r) {
            return (int)($r['id'] ?? 0);
        }, $properties);
        $collectionMap = Collection::getCountsForProperties(array_filter($propertyIds), 'kho_nha_dat', $userId);

        $this->view('main/resource_sum', [
            'properties' => $properties,
            'page' => $page,
            'pages' => $pages,
            'total' => $total,
            'perPage' => $perPage,
            'search' => $search,
            'status' => $status,
            'address' => $address,
            'collections' => $collections,
            'collectionMap' => $collectionMap
        ]);
    }
    public function resourceSum2()
    {
        // list kho_nha_dat (Sum version)
        require_once __DIR__ . '/../Models/Property.php';
        require_once __DIR__ . '/../Models/Collection.php';
        require_once __DIR__ . '/../../core/Auth.php';

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

        $user = \Auth::user();
        $userId = $user['id'] ?? 0;
        $collections = Collection::getForUser($userId);
        $propertyIds = array_map(function ($r) {
            return (int)($r['id'] ?? 0);
        }, $properties);
        // resource type for this view is kho_cho_thue
        $collectionMap = Collection::getCountsForProperties(array_filter($propertyIds), 'kho_cho_thue', $userId);

        $this->view('main/resource_sum2', [
            'properties' => $properties,
            'page' => $page,
            'pages' => $pages,
            'total' => $total,
            'perPage' => $perPage,
            'search' => $search,
            'status' => $status,
            'address' => $address,
            'collections' => $collections,
            'collectionMap' => $collectionMap
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

    public function policy()
    {
        $this->view('main/policy');
    }

    public function info()
    {
        require_once __DIR__ . '/../Models/InternalPost.php';
        $posts = InternalPost::getActive(50, 0);
        $this->view('main/info', ['posts' => $posts]);
    }

    public function internalInfoDetail()
    {
        require_once __DIR__ . '/../Models/InternalPost.php';
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            header('Location: ' . BASE_URL . '/info');
            exit;
        }
        $post = InternalPost::getById($id);
        if (!$post) {
            header('Location: ' . BASE_URL . '/info');
            exit;
        }
        $this->view('main/internal-info-detail', ['post' => $post]);
    }

    public function notification()
    {
        require_once __DIR__ . '/../Models/DealPost.php';
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = 20;
        $search = isset($_GET['q']) ? trim($_GET['q']) : null;
        $offset = ($page - 1) * $perPage;

        $posts = DealPost::getList($perPage, $offset, $search);

        $this->view('main/notification', ['posts' => $posts, 'page' => $page, 'search' => $search]);
    }

    public function collection()
    {
        require_once __DIR__ . '/../Models/Collection.php';
        require_once __DIR__ . '/../../core/Auth.php';
        $user = \Auth::user();
        $userId = $user['id'] ?? 0;

        $search = isset($_GET['q']) ? trim($_GET['q']) : null;
        $collections = Collection::getForUser($userId, $search);

        $this->view('main/collection', [
            'collections' => $collections,
            'search' => $search
        ]);
    }

    public function collectionDetail()
    {
        require_once __DIR__ . '/../Models/Collection.php';
        require_once __DIR__ . '/../../core/Auth.php';

        $user = \Auth::user();
        $userId = $user['id'] ?? 0;

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            header('Location: ' . BASE_URL . '/collection');
            exit;
        }

        $collection = Collection::getById($id);
        if (!$collection) {
            header('Location: ' . BASE_URL . '/collection');
            exit;
        }

        // Ensure the collection belongs to current user
        if ((int)$collection['user_id'] !== (int)$userId) {
            // not allowed
            header('Location: ' . BASE_URL . '/collection');
            exit;
        }

        // Read optional filters from query string
        $filters = [];
        if (isset($_GET['q']) && trim($_GET['q']) !== '') $filters['q'] = trim($_GET['q']);
        if (isset($_GET['status']) && trim($_GET['status']) !== '' && trim($_GET['status']) !== 'all') $filters['status'] = trim($_GET['status']);
        if (isset($_GET['address']) && trim($_GET['address']) !== '') $filters['address'] = trim($_GET['address']);

        $items = Collection::getItems($id, 'bat_dong_san', $filters);

        $this->view('main/collection-detail', [
            'collection' => $collection,
            'items' => $items,
            'filters' => $filters
        ]);
    }

    public function creCollection()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../Models/Collection.php';
            require_once __DIR__ . '/../../core/Auth.php';

            $user = \Auth::user();
            $userId = $user['id'] ?? null;
            $name = isset($_POST['ten_bo_suu_tap']) ? trim($_POST['ten_bo_suu_tap']) : '';
            $mo_ta = isset($_POST['mo_ta']) ? trim($_POST['mo_ta']) : null;

            $savedPath = null;
            if (!empty($_FILES['anh_dai_dien']) && $_FILES['anh_dai_dien']['error'] === UPLOAD_ERR_OK) {
                $uploadPath = __DIR__ . '/../../public/uploads/collections';
                if (!is_dir($uploadPath)) @mkdir($uploadPath, 0755, true);

                $tmp = $_FILES['anh_dai_dien']['tmp_name'];
                $ext = pathinfo($_FILES['anh_dai_dien']['name'], PATHINFO_EXTENSION);
                $filename = uniqid('coll_') . '.' . $ext;
                if (move_uploaded_file($tmp, $uploadPath . '/' . $filename)) {
                    $savedPath = 'uploads/collections/' . $filename;
                }
            }

            $data = [
                'user_id' => $userId,
                'ten_bo_suu_tap' => $name,
                'anh_dai_dien' => $savedPath,
                'mo_ta' => $mo_ta,
                'is_default' => 0,
                'trang_thai' => 1
            ];

            if (Collection::create($data)) {
                header('Location: ' . BASE_URL . '/collection');
                exit;
            } else {
                $_SESSION['error'] = 'Lỗi khi tạo bộ sưu tập';
            }
        }
        $this->view('main/cre-collection');
    }

    public function renameCollection()
    {
        require_once __DIR__ . '/../Models/Collection.php';
        header('Content-Type: application/json');

        $id = $_POST['id'] ?? 0;
        $name = $_POST['ten_bo_suu_tap'] ?? '';
        $ok = Collection::updateName((int)$id, $name);
        echo json_encode(['ok' => $ok]);
    }

    public function deleteCollection()
    {
        require_once __DIR__ . '/../Models/Collection.php';
        header('Content-Type: application/json');

        $id = $_POST['id'] ?? 0;
        $ok = Collection::deleteById((int)$id);
        echo json_encode(['ok' => $ok]);
    }

    public function addToCollection()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
            exit;
        }

        require_once __DIR__ . '/../Models/Collection.php';
        require_once __DIR__ . '/../../core/Auth.php';

        $user = \Auth::user();
        $userId = $user['id'] ?? 0;
        if ($userId === 0) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $propertyId = $_POST['property_id'] ?? null;
        $collectionIds = $_POST['collection_ids'] ?? [];
        $resourceType = isset($_POST['resource_type']) ? trim($_POST['resource_type']) : 'bat_dong_san';

        if (empty($propertyId) || !is_numeric($propertyId)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid Property ID']);
            exit;
        }

        $ok = Collection::savePropertyToCollections((int)$propertyId, (array)$collectionIds, $userId, $resourceType);

        if ($ok) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Database error']);
        }
        exit;
    }

    public function removeFromCollection()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
            exit;
        }

        require_once __DIR__ . '/../Models/Collection.php';
        require_once __DIR__ . '/../../core/Auth.php';

        $user = \Auth::user();
        $userId = $user['id'] ?? 0;

        $collectionId = isset($_POST['collection_id']) ? (int)$_POST['collection_id'] : 0;
        $resourceId = isset($_POST['resource_id']) ? (int)$_POST['resource_id'] : 0;
        $resourceType = isset($_POST['resource_type']) ? trim($_POST['resource_type']) : 'bat_dong_san';

        if ($collectionId <= 0 || $resourceId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
            exit;
        }

        $ok = Collection::removeItem($collectionId, $resourceId, $userId, $resourceType, false);
        echo json_encode(['ok' => (bool)$ok]);
        exit;
    }

    public function getPropertyCollections()
    {
        header('Content-Type: application/json');
        require_once __DIR__ . '/../Models/Collection.php';
        require_once __DIR__ . '/../../core/Auth.php';

        $user = \Auth::user();
        $userId = $user['id'] ?? 0;

        $propertyId = $_GET['id'] ?? 0;
        $resourceType = $_GET['resource_type'] ?? 'bat_dong_san';
        $ids = Collection::getCollectionIdsForProperty((int)$propertyId, $userId, $resourceType);
        echo json_encode(['success' => true, 'collection_ids' => $ids]);
        exit;
    }

    public function autoMatch()
    {
        // Nếu là POST, chuyển hướng sang GET với các tham số
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $qs = [];
            if (!empty($_POST['type'])) $qs['type'] = $_POST['type'];
            if (!empty($_POST['location'])) $qs['location'] = $_POST['location'];
            if (!empty($_POST['price'])) $qs['price'] = $_POST['price'];
            if (!empty($_POST['legal'])) $qs['legal'] = $_POST['legal'];
            if (!empty($_POST['area'])) $qs['area'] = $_POST['area'];
            $qs = http_build_query($qs);
            header('Location: ' . BASE_URL . '/auto-match' . ($qs ? ('?' . $qs) : ''));
            exit;
        }

        require_once __DIR__ . '/../Models/Property.php';

        $type = isset($_GET['type']) ? trim($_GET['type']) : '';
        $location = isset($_GET['location']) ? trim($_GET['location']) : '';
        $price = isset($_GET['price']) ? trim($_GET['price']) : '';
        $legal = isset($_GET['legal']) ? trim($_GET['legal']) : '';
        $area = isset($_GET['area']) ? (float)$_GET['area'] : 0;

        $properties = null; // Khởi tạo là null để không hiển thị gì ban đầu

        // Chỉ thực hiện tìm kiếm nếu có ít nhất một tham số được gửi lên (người dùng đã bấm tìm kiếm)
        if (isset($_GET['type']) || isset($_GET['location']) || isset($_GET['price']) || isset($_GET['legal']) || isset($_GET['area'])) {
            $db = \Database::connect();
            $sql = "SELECT * FROM properties WHERE 1=1";
            $params = [];

            if ($type !== '') {
                $sql .= " AND loai_bds = ?";
                $params[] = $type;
            }

            if ($location !== '') {
                $sql .= " AND (tinh_thanh LIKE ? OR quan_huyen LIKE ? OR xa_phuong LIKE ? OR dia_chi_chi_tiet LIKE ?)";
                $like = '%' . $location . '%';
                array_push($params, $like, $like, $like, $like);
            }

            if ($price !== '') {
                if ($price === 'lt_5') $sql .= " AND gia_chao < 5000000000";
                elseif ($price === '5_10') $sql .= " AND gia_chao BETWEEN 5000000000 AND 10000000000";
                elseif ($price === '10_20') $sql .= " AND gia_chao BETWEEN 10000000000 AND 20000000000";
                elseif ($price === 'gt_20') $sql .= " AND gia_chao > 20000000000";
            }

            if ($legal === 'so_do') $sql .= " AND phap_ly LIKE '%so%'";
            if ($legal === 'khong_so') $sql .= " AND phap_ly LIKE '%khong%'";

            if ($area > 0) {
                $sql .= " AND dien_tich >= ?";
                $params[] = $area;
            }

            $sql .= " ORDER BY id DESC LIMIT 100";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($properties as &$p) {
                $p['thumb'] = Property::getFirstImagePath((int)$p['id']);
            }
        }

        $this->view('main/auto_match', [
            'properties' => $properties,
            'filters' => ['type' => $type, 'location' => $location, 'price' => $price, 'legal' => $legal, 'area' => $area]
        ]);
    }
    public function termsService()
    {
        $this->view('main/terms-service');
    }
    public function privacyPolicy()
    {
        $this->view('main/privacy-policy');
    }

    public function cookiePolicy()
    {
        $this->view('main/cookie-policy');
    }
    public function paymentPolicy()
    {
        $this->view('main/payment-policy');
    }
}
