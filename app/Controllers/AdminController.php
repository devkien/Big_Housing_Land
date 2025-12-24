<?php

class AdminController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        // Admin area: allow admin and super_admin
        $this->requireRole([ROLE_ADMIN, ROLE_SUPER_ADMIN]);
    }
    public function index()
    {
        $this->view('admin/home');
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
        $this->view('admin/profile', ['user' => $user]);
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

        $this->view('admin/detailprofile', ['user' => $user]);
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
                'nam_sinh' => trim($_POST['nam_sinh'] ?? null),
                'so_cccd' => trim($_POST['so_cccd'] ?? null),
                'dia_chi' => trim($_POST['dia_chi'] ?? null),
            ];

            if (empty($data['so_dien_thoai'])) {
                // $_SESSION['error'] = 'Số điện thoại là bắt buộc';
                header('Location: ' . BASE_URL . '/admin/editprofile');
                exit;
            }

            $existing = User::findByPhone($data['so_dien_thoai']);
            if ($existing && !empty($existing['id']) && $existing['id'] != $id) {
                // $_SESSION['error'] = 'Số điện thoại đã được sử dụng';
                header('Location: ' . BASE_URL . '/admin/editprofile');
                exit;
            }

            $ok = User::update($id, $data);
            if ($ok) {
                $updated = User::findById($id);
                if ($updated) $_SESSION['user'] = $updated;
                // $_SESSION['success'] = 'Cập nhật hồ sơ thành công';
                header('Location: ' . BASE_URL . '/admin/detailprofile');
                exit;
            } else {
                // $_SESSION['error'] = 'Lỗi khi lưu dữ liệu';
                header('Location: ' . BASE_URL . '/admin/editprofile');
                exit;
            }
        }

        $user = User::findById($id);
        if (!$user) $user = $sessionUser;

        $this->view('admin/editprofile', ['user' => $user]);
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
                header('Location: ' . BASE_URL . '/admin/changepassword');
                exit;
            }

            if ($new !== $confirm) {
                $_SESSION['error'] = 'Mật khẩu mới và xác nhận không khớp';
                header('Location: ' . BASE_URL . '/admin/changepassword');
                exit;
            }

            $user = User::findById($id);
            if (!$user || !password_verify($current, $user['password'])) {
                $_SESSION['error'] = 'Mật khẩu hiện tại không đúng';
                header('Location: ' . BASE_URL . '/admin/changepassword');
                exit;
            }

            $hash = password_hash($new, PASSWORD_BCRYPT);
            $ok = User::updatePasswordById($id, $hash);

            if ($ok) {
                // Refresh session user
                $updated = User::findById($id);
                if ($updated) $_SESSION['user'] = $updated;
                $_SESSION['success'] = 'Đổi mật khẩu thành công';
                header('Location: ' . BASE_URL . '/admin/changepassword');
                exit;
            } else {
                $_SESSION['error'] = 'Lỗi khi lưu mật khẩu mới';
                header('Location: ' . BASE_URL . '/admin/changepassword');
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

        $this->view('admin/changepassword', [
            'user' => $user,
            'displayRole' => $displayRole,
            'officeBadge' => $officeBadge
        ]);
    }

    // Admin vào kho tài nguyền 
    public function resourcePost()
    {
        // If POST: handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../Helpers/functions.php';
            if (!verify_csrf($_POST['_csrf'] ?? null)) {
                $_SESSION['error'] = 'Token không hợp lệ.';
                header('Location: ' . BASE_URL . '/admin/management-resource-post');
                exit;
            }

            require_once __DIR__ . '/../Models/Property.php';
            require_once __DIR__ . '/../../core/Auth.php';

            $sessionUser = \Auth::user();
            $userId = $sessionUser['id'] ?? null;

            // ----- Server-side mapping & validation -----
            // Allowed enums / values (map client inputs to canonical DB values)
            $allowed = [
                'loai_bds' => ['ban', 'cho_thue'],
                'phap_ly' => ['co_so', 'khong_so'],
                'don_vi_dien_tich' => ['m2', 'm²', 'ha'],
                'trich_thuong_don_vi' => ['%', 'VND']
            ];

            // normalize helpers
            $normalizeUnit = function ($v) {
                if ($v === null) return null;
                $v = trim((string)$v);
                if ($v === 'm²' || $v === 'm2') return 'm2';
                if ($v === 'ha') return 'ha';
                return $v;
            };

            $loai_bds = trim($_POST['loai_bds'] ?? '');
            if (!in_array($loai_bds, $allowed['loai_bds'], true)) {
                $loai_bds = $allowed['loai_bds'][0];
            }

            $phap_ly = trim($_POST['phap_ly'] ?? '');
            if (!in_array($phap_ly, $allowed['phap_ly'], true)) {
                $phap_ly = $allowed['phap_ly'][0];
            }

            $don_vi = $normalizeUnit($_POST['don_vi_dien_tich'] ?? '');
            if (!in_array($don_vi, ['m2', 'ha'], true)) $don_vi = 'm2';

            $trich_unit = trim($_POST['trich_thuong_don_vi'] ?? '');
            if (!in_array($trich_unit, $allowed['trich_thuong_don_vi'], true)) $trich_unit = '%';

            // floors validation
            $so_tang_raw = $_POST['so_tang'] ?? '';
            $so_tang = null;
            if ($so_tang_raw !== '') {
                $so_tang_val = filter_var($so_tang_raw, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 100]]);
                if ($so_tang_val === false) {
                    $_SESSION['error'] = 'Số tầng không hợp lệ';
                    header('Location: ' . BASE_URL . '/admin/management-resource-post');
                    exit;
                }
                $so_tang = $so_tang_val;
            }

            // numeric fields
            $makeFloat = function ($v) {
                if ($v === null || $v === '') return null;
                if (!is_numeric($v)) return null;
                return (float)$v;
            };

            // Determine loai_kho (DB enum) from loai_bds
            $loai_kho = ($loai_bds === 'ban') ? 'kho_nha_dat' : 'kho_cho_thue';

            // Build sanitized data array
            $data = [
                'user_id' => $userId,
                'phong_ban' => trim($_POST['phong_ban'] ?? ''),
                'tieu_de' => trim($_POST['tieu_de'] ?? ''),
                'loai_bds' => $loai_bds,
                'loai_kho' => $loai_kho,
                'phap_ly' => $phap_ly,
                'dien_tich' => $makeFloat($_POST['dien_tich'] ?? null),
                'don_vi_dien_tich' => $don_vi,
                'chieu_dai' => $makeFloat($_POST['chieu_dai'] ?? null),
                'chieu_rong' => $makeFloat($_POST['chieu_rong'] ?? null),
                'so_tang' => $so_tang,
                'gia_chao' => $makeFloat($_POST['gia_chao'] ?? null),
                'trich_thuong_gia_tri' => trim($_POST['trich_thuong_gia_tri'] ?? ''),
                'trich_thuong_don_vi' => $trich_unit,
                'tinh_thanh' => trim($_POST['tinh_thanh'] ?? ''),
                'quan_huyen' => trim($_POST['quan_huyen'] ?? ''),
                'xa_phuong' => trim($_POST['xa_phuong'] ?? ''),
                'dia_chi_chi_tiet' => trim($_POST['dia_chi_chi_tiet'] ?? ''),
                'mo_ta' => trim($_POST['mo_ta'] ?? ''),
                'is_visible' => isset($_POST['is_visible']) ? 1 : 0,
                'trang_thai' => trim($_POST['trang_thai'] ?? '')
            ];

            // Basic required fields
            if (empty($data['tieu_de']) || empty($data['tinh_thanh'])) {
                $_SESSION['error'] = 'Vui lòng điền tiêu đề và tỉnh/thành.';
                header('Location: ' . BASE_URL . '/admin/management-resource-post');
                exit;
            }

            // ===== map/validate trang_thai (DB enum) =====
            $allowedStatuses = ['ban_manh', 'tam_dung_ban', 'dung_ban', 'da_ban'];
            $trang_thai = trim($_POST['trang_thai'] ?? '');
            if (!in_array($trang_thai, $allowedStatuses, true)) {
                $trang_thai = 'ban_manh';
            }
            // ensure it's set in data
            $data['trang_thai'] = $trang_thai;

            // ----- Validate uploaded media -----
            $savedMedia = [];
            $maxFiles = 12;
            $maxSize = 8 * 1024 * 1024; // 8MB each
            $allowedMimes = [
                'image/jpeg',
                'image/png',
                'image/webp',
                'image/gif',
                'video/mp4',
                'video/quicktime'
            ];

            if (!empty($_FILES['media']) && is_array($_FILES['media']['tmp_name'])) {
                $count = count($_FILES['media']['tmp_name']);
                if ($count > $maxFiles) {
                    $_SESSION['error'] = "Chỉ được tải tối đa $maxFiles file.";
                    header('Location: ' . BASE_URL . '/admin/management-resource-post');
                    exit;
                }
                // prepare upload dir early
                $uploadsDir = realpath(__DIR__ . '/../../public') . '/uploads/properties_temp';
                if (!is_dir($uploadsDir)) mkdir($uploadsDir, 0755, true);
                // files checked later and moved after property created
            }

            $propertyId = Property::create($data);
            if (!$propertyId) {
                $_SESSION['error'] = 'Lưu tin thất bại. Vui lòng thử lại.';
                header('Location: ' . BASE_URL . '/admin/management-resource-post');
                exit;
            }

            // Handle uploaded media files
            $savedMedia = [];
            if (!empty($_FILES['media']) && is_array($_FILES['media']['tmp_name'])) {
                $uploadsDir = realpath(__DIR__ . '/../../public') . '/uploads/properties/' . $propertyId;
                if (!is_dir($uploadsDir)) mkdir($uploadsDir, 0755, true);

                $count = count($_FILES['media']['tmp_name']);
                for ($i = 0; $i < $count; $i++) {
                    $err = $_FILES['media']['error'][$i];
                    if ($err !== UPLOAD_ERR_OK) continue;
                    $tmp = $_FILES['media']['tmp_name'][$i];
                    $orig = basename($_FILES['media']['name'][$i]);
                    $ext = pathinfo($orig, PATHINFO_EXTENSION);
                    // validate size
                    $size = filesize($tmp);
                    if ($size > $maxSize) continue;
                    // validate mime from tmp
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mime = finfo_file($finfo, $tmp);
                    finfo_close($finfo);
                    if (!in_array($mime, $allowedMimes, true)) continue;

                    $filename = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
                    $dest = $uploadsDir . '/' . $filename;
                    if (move_uploaded_file($tmp, $dest)) {
                        $webPath = 'uploads/properties/' . $propertyId . '/' . $filename;
                        $type = strpos($mime, 'video/') === 0 ? 'video' : 'image';
                        $savedMedia[] = ['type' => $type, 'path' => $webPath];
                    }
                }
            }

            if (!empty($savedMedia)) {
                Property::addMedia($propertyId, $savedMedia);
            }

            $_SESSION['success'] = 'Đăng tin thành công.';
            header('Location: ' . BASE_URL . '/admin/management-resource-post');
            exit;
        }

        $this->view('admin/resource-post');
    }

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

        // Lấy danh sách bộ sưu tập để hiển thị trong modal
        $db = \Database::connect();
        $stmt = $db->query("SELECT * FROM collections WHERE trang_thai = 1 ORDER BY id DESC");
        $collections = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->view('admin/resource', [
            'properties' => $properties,
            'page' => $page,
            'pages' => $pages,
            'total' => $total,
            'perPage' => $perPage,
            'search' => $search,
            'status' => $status,
            'address' => $address,
            'collections' => $collections
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

        $this->view('admin/resource-rent', [
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
        require_once __DIR__ . '/../Models/LeadReport.php';

        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = 10;
        $search = isset($_GET['q']) ? trim($_GET['q']) : null;

        $total = LeadReport::countAll($search);
        $pages = (int)ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;

        $reports = LeadReport::getList($perPage, $offset, $search);

        $this->view('admin/report_list', [
            'reports' => $reports,
            'page' => $page,
            'pages' => $pages,
            'total' => $total,
            'search' => $search
        ]);
    }

    public function reportCustomerDetail()
    {
        require_once __DIR__ . '/../Models/LeadReport.php';

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id <= 0) {
            $_SESSION['error'] = 'ID báo cáo không hợp lệ.';
            header('Location: ' . BASE_URL . '/admin/report_list');
            exit;
        }

        $report = LeadReport::getById($id);

        if (!$report) {
            $_SESSION['error'] = 'Không tìm thấy báo cáo.';
            header('Location: ' . BASE_URL . '/admin/report_list');
            exit;
        }

        $this->view('admin/report_customer', [
            'report' => $report,
        ]);
    }

    public function updateResourceStatus()
    {
        // Chỉ xử lý method POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;
            $status = $_POST['status'] ?? null;

            if ($id && $status) {
                // Sử dụng Database class trực tiếp để tránh lỗi nếu Model chưa có hàm update
                $db = \Database::connect();
                $sql = "UPDATE properties SET trang_thai = :status WHERE id = :id";
                $stmt = $db->prepare($sql);
                $result = $stmt->execute([':status' => $status, ':id' => $id]);

                if ($result) {
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Lỗi khi cập nhật cơ sở dữ liệu']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Thiếu ID hoặc trạng thái']);
            }
            exit;
        }
    }

    public function detail()
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id <= 0) {
            header('Location: ' . BASE_URL . '/admin/management-resource');
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
             header('Location: ' . BASE_URL . '/admin/management-resource');
             exit;
        }

        // Lấy hình ảnh/media
        require_once __DIR__ . '/../Models/Property.php';
        $media = [];
        if (method_exists('Property', 'getMedia')) {
            $media = Property::getMedia($id);
        } else {
             $sqlMedia = "SELECT * FROM property_media WHERE property_id = :id";
             $stmtMedia = $db->prepare($sqlMedia);
             $stmtMedia->execute([':id' => $id]);
             $media = $stmtMedia->fetchAll(PDO::FETCH_ASSOC);
        }
        $property['media'] = $media;

        $this->view('admin/detail', ['property' => $property]);
    }

    public function addToCollection()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $propertyId = $_POST['property_id'] ?? null;
            // Luôn đảm bảo collectionIds là một mảng, kể cả khi không có checkbox nào được chọn
            $collectionIds = $_POST['collection_ids'] ?? [];

            if ($propertyId) {
                $db = \Database::connect();
                try {
                    $db->beginTransaction();

                    // 1. Xóa tất cả các liên kết cũ của tài nguyên này
                    $delStmt = $db->prepare("DELETE FROM collection_items WHERE property_id = ?");
                    $delStmt->execute([$propertyId]);

                    // 2. Thêm lại các liên kết mới được chọn
                    if (!empty($collectionIds)) {
                        $insStmt = $db->prepare("INSERT INTO collection_items (collection_id, property_id) VALUES (?, ?)");
                        foreach ($collectionIds as $cid) {
                            $insStmt->execute([(int)$cid, $propertyId]);
                        }
                    }

                    $db->commit();
                    echo json_encode(['success' => true]);
                } catch (Exception $e) {
                    $db->rollBack();
                    echo json_encode(['success' => false, 'message' => 'Lỗi cơ sở dữ liệu: ' . $e->getMessage()]);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Thiếu ID tài nguyên.']);
            }
            exit;
        }
    }

    public function getPropertyCollections()
    {
        header('Content-Type: application/json');
        $id = $_GET['id'] ?? 0;
        if ($id) {
            $db = \Database::connect();
            try {
                $stmt = $db->prepare("SELECT collection_id FROM collection_items WHERE property_id = ?");
                $stmt->execute([$id]);
                $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
                echo json_encode(['success' => true, 'collection_ids' => $ids]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
        } else {
            echo json_encode(['success' => false]);
        }
        exit;
    }

    public function collection()
    {
        require_once __DIR__ . '/../Models/Collection.php';

        $search = isset($_GET['q']) ? trim($_GET['q']) : null;
        // Lấy danh sách bộ sưu tập (giả sử Admin thấy hết hoặc logic tương tự SuperAdmin)
        $collections = Collection::allWithCount($search);

        $this->view('admin/collection', [
            'collections' => $collections,
            'search' => $search
        ]);
    }

    public function creCollection()
    {
        // Handle POST (form submit)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../Models/Collection.php';
            require_once __DIR__ . '/../Helpers/functions.php';
            require_once __DIR__ . '/../../core/Auth.php';

            $name = isset($_POST['ten_bo_suu_tap']) ? trim($_POST['ten_bo_suu_tap']) : '';
            $mo_ta = isset($_POST['mo_ta']) ? trim($_POST['mo_ta']) : null;

            $user = \Auth::user();
            $userId = $user['id'] ?? null;

            $uploadPath = __DIR__ . '/../../public/uploads/collections';
            if (!is_dir($uploadPath)) {
                @mkdir($uploadPath, 0755, true);
            }

            $savedPath = null;
            if (!empty($_FILES['anh_dai_dien']) && $_FILES['anh_dai_dien']['error'] === UPLOAD_ERR_OK) {
                $tmp = $_FILES['anh_dai_dien']['tmp_name'];
                $orig = basename($_FILES['anh_dai_dien']['name']);
                $ext = pathinfo($orig, PATHINFO_EXTENSION);
                $filename = uniqid('coll_') . '.' . $ext;
                $dest = $uploadPath . '/' . $filename;
                if (@move_uploaded_file($tmp, $dest)) {
                    $savedPath = 'uploads/collections/' . $filename;
                }
            }

            $data = [
                'user_id' => $userId,
                'ten_bo_suu_tap' => $name,
                'anh_dai_dien' => $savedPath,
                'mo_ta' => $mo_ta,
                'is_default' => 0,
                'trang_thai' => 1,
            ];

            $created = Collection::create($data);
            if ($created) {
                header('Location: ' . BASE_URL . '/admin/collection');
                exit;
            } else {
                $_SESSION['error'] = 'Không thể tạo bộ sưu tập';
            }
        }

        $this->view('admin/cre-collection');
    }

    public function renameCollection()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
            exit;
        }

        require_once __DIR__ . '/../Models/Collection.php';

        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $name = isset($_POST['ten_bo_suu_tap']) ? trim($_POST['ten_bo_suu_tap']) : '';

        if ($id <= 0 || $name === '') {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Dữ liệu không hợp lệ']);
            exit;
        }

        $ok = Collection::updateName($id, $name);
        echo json_encode(['ok' => (bool)$ok]);
        exit;
    }

    public function deleteCollection()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
            exit;
        }

        require_once __DIR__ . '/../Models/Collection.php';
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'ID không hợp lệ']);
            exit;
        }

        $ok = Collection::deleteById($id);
        echo json_encode(['ok' => (bool)$ok]);
        exit;
    }

    public function notification()
    {
        require_once __DIR__ . '/../Models/DealPost.php';

        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = 20;
        $search = isset($_GET['q']) ? trim($_GET['q']) : null;
        $offset = ($page - 1) * $perPage;

        $posts = DealPost::getList($perPage, $offset, $search);

        $this->view('admin/notification', [
            'posts' => $posts,
            'page' => $page,
            'search' => $search
        ]);
    }

    public function creNotification()
    {
        require_once __DIR__ . '/../Helpers/functions.php';
        $user = \Auth::user();

        // Handle POST (create new deal post)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../Models/DealPost.php';
            $userId = $user['id'] ?? null;

            $tieu_de = isset($_POST['tieu_de']) ? trim($_POST['tieu_de']) : null;
            $noi_dung = isset($_POST['noi_dung']) ? trim($_POST['noi_dung']) : '';
            $ma_nhan_vien = isset($_POST['ma_nhan_vien']) ? trim($_POST['ma_nhan_vien']) : null;

            $errors = [];
            // Validation: title and content required
            if (empty($tieu_de)) {
                $errors[] = 'Tiêu đề không được để trống.';
            }
            if (empty(strip_tags($noi_dung))) {
                $errors[] = 'Nội dung không được để trống.';
            }

            // Validate files if provided
            $saved = [];
            $uploadDir = __DIR__ . '/../../public/uploads/deal_posts';
            if (!is_dir($uploadDir)) @mkdir($uploadDir, 0755, true);

            if (!empty($_FILES['images']) && is_array($_FILES['images']['tmp_name'])) {
                $allowed = ['image/jpeg', 'image/png', 'image/gif', 'video/mp4', 'video/webm', 'video/quicktime'];
                $maxSize = 20 * 1024 * 1024; // 20MB
                for ($i = 0; $i < count($_FILES['images']['tmp_name']); $i++) {
                    $err = $_FILES['images']['error'][$i] ?? UPLOAD_ERR_NO_FILE;
                    if ($err === UPLOAD_ERR_NO_FILE) continue;
                    if ($err !== UPLOAD_ERR_OK) {
                        $errors[] = 'Lỗi upload file ' . ($_FILES['images']['name'][$i] ?? '') . '.';
                        continue;
                    }
                    $tmp = $_FILES['images']['tmp_name'][$i];
                    if (filesize($tmp) > $maxSize) {
                        $errors[] = 'Kích thước file quá lớn (max 20MB): ' . ($_FILES['images']['name'][$i] ?? '');
                        continue;
                    }
                    // More robust validation if needed
                    $orig = basename($_FILES['images']['name'][$i]);
                    $ext = pathinfo($orig, PATHINFO_EXTENSION);
                    $filename = uniqid('deal_') . '.' . $ext;
                    $dest = $uploadDir . '/' . $filename;
                    if (@move_uploaded_file($tmp, $dest)) {
                        $saved[] = 'uploads/deal_posts/' . $filename;
                    } else {
                        $errors[] = 'Không thể lưu file: ' . $orig;
                    }
                }
            }

            if (empty($errors)) {
                $postId = DealPost::create(['user_id' => $userId, 'tieu_de' => $tieu_de, 'noi_dung' => $noi_dung]);
                if ($postId && !empty($saved)) {
                    DealPost::addImages($postId, $saved);
                }
                header('Location: ' . BASE_URL . '/admin/notification');
                exit;
            }

            $this->view('admin/cre-notification', ['errors' => $errors, 'old' => $_POST, 'user' => $user]);
            return;
        }

        $this->view('admin/cre-notification', ['user' => $user]);
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
            header('Location: ' . BASE_URL . '/admin/auto-match' . ($qs ? ('?' . $qs) : ''));
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
            $db = Database::connect();
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

        $this->view('admin/auto_match', [
            'properties' => $properties,
            'filters' => ['type' => $type, 'location' => $location, 'price' => $price, 'legal' => $legal, 'area' => $area]
        ]);
    }

    public function policy()
    {
        $this->view('admin/policy');
    }
    // info thông tin nội bộ
    public function info()
    {
        // Load internal posts and pass to view
        require_once __DIR__ . '/../Models/InternalPost.php';
        $posts = InternalPost::getActive(50, 0);
        $this->view('admin/info', ['posts' => $posts]);
    }

    public function addInternalInfo()
    {
        // Handle POST (create new internal info)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../Helpers/functions.php';
            if (!verify_csrf($_POST['_csrf'] ?? null)) {
                $_SESSION['error'] = 'Token không hợp lệ.';
                header('Location: ' . BASE_URL . '/admin/add-internal-info');
                exit;
            }

            require_once __DIR__ . '/../Models/InternalPost.php';
            require_once __DIR__ . '/../../core/Auth.php';

            $sessionUser = \Auth::user();
            $userId = $sessionUser['id'] ?? null;

            $tieu_de = trim($_POST['tieu_de'] ?? '');
            $noi_dung = trim($_POST['noi_dung'] ?? '');

            if ($tieu_de === '' || $noi_dung === '') {
                $_SESSION['error'] = 'Vui lòng điền tiêu đề và nội dung.';
                header('Location: ' . BASE_URL . '/admin/add-internal-info');
                exit;
            }

            $data = [
                'user_id' => $userId,
                'tieu_de' => $tieu_de,
                'noi_dung' => $noi_dung,
                'trang_thai' => 1
            ];

            $postId = InternalPost::create($data);
            if (!$postId) {
                $_SESSION['error'] = 'Lưu thông tin thất bại.';
                header('Location: ' . BASE_URL . '/admin/add-internal-info');
                exit;
            }

            // Handle uploaded media
            $saved = [];
            $maxFiles = 6;
            $maxSize = 8 * 1024 * 1024; // 8MB
            $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'video/mp4', 'video/quicktime'];

            if (!empty($_FILES['media']) && is_array($_FILES['media']['tmp_name'])) {
                $count = count($_FILES['media']['tmp_name']);
                if ($count > $maxFiles) $count = $maxFiles;

                $uploadsDir = realpath(__DIR__ . '/../../public') . '/uploads/internal/' . $postId;
                if (!is_dir($uploadsDir)) mkdir($uploadsDir, 0755, true);

                for ($i = 0; $i < $count; $i++) {
                    $err = $_FILES['media']['error'][$i] ?? UPLOAD_ERR_NO_FILE;
                    if ($err !== UPLOAD_ERR_OK) continue;
                    $tmp = $_FILES['media']['tmp_name'][$i];
                    $orig = basename($_FILES['media']['name'][$i] ?? 'file');
                    $ext = pathinfo($orig, PATHINFO_EXTENSION);
                    $size = filesize($tmp);
                    if ($size > $maxSize) continue;
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mime = finfo_file($finfo, $tmp);
                    finfo_close($finfo);
                    if (!in_array($mime, $allowedMimes, true)) continue;

                    $filename = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
                    $dest = $uploadsDir . '/' . $filename;
                    if (move_uploaded_file($tmp, $dest)) {
                        $webPath = 'uploads/internal/' . $postId . '/' . $filename;
                        $saved[] = $webPath;
                    }
                }
            }

            if (!empty($saved)) {
                InternalPost::addImages($postId, $saved);
            }

            $_SESSION['success'] = 'Thêm thông tin nội bộ thành công.';
            header('Location: ' . BASE_URL . '/admin/info');
            exit;
        }

        $this->view('admin/add-internal-info');
    }
    public function internalInfoList()
    {
        require_once __DIR__ . '/../Models/InternalPost.php';
        require_once __DIR__ . '/../Helpers/functions.php';

        // Handle POST (delete) - supports form POST and JSON (AJAX)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;
            $token = $_POST['_csrf'] ?? null;

            // JSON body support
            if (!$id) {
                $body = file_get_contents('php://input');
                $json = json_decode($body, true);
                if (is_array($json)) {
                    $id = $json['id'] ?? null;
                    if (isset($json['_csrf'])) $token = $json['_csrf'];
                }
            }
            if (!verify_csrf($token)) {
                $isJson = (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false) || (strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false);
                if ($isJson) {
                    http_response_code(403);
                    echo json_encode(['ok' => false, 'message' => 'CSRF token invalid']);
                    return;
                }
                $_SESSION['error'] = 'Token không hợp lệ.';
                header('Location: ' . BASE_URL . '/admin/internal-info-list');
                return;
            }

            if (empty($id) || !is_numeric($id)) {
                $isJson = (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false) || (strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false);
                if ($isJson) {
                    http_response_code(400);
                    echo json_encode(['ok' => false, 'message' => 'Invalid id']);
                    return;
                }
                $_SESSION['error'] = 'ID không hợp lệ.';
                header('Location: ' . BASE_URL . '/admin/internal-info-list');
                return;
            }

            $ok = InternalPost::deleteById((int)$id);

            $isJson = (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false) || (strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false);
            if ($ok) {
                if ($isJson) {
                    echo json_encode(['ok' => true]);
                    return;
                }
                $_SESSION['success'] = 'Đã xóa thông tin nội bộ.';
            } else {
                if ($isJson) {
                    http_response_code(500);
                    echo json_encode(['ok' => false, 'message' => 'Xóa thất bại']);
                    return;
                }
                $_SESSION['error'] = 'Xóa thất bại.';
            }

            header('Location: ' . BASE_URL . '/admin/internal-info-list');
            return;
        }

        // GET: list posts with pagination
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = 10;
        $search = isset($_GET['q']) ? trim($_GET['q']) : null;
        $offset = ($page - 1) * $perPage;

        // Use countActive with search support
        $total = InternalPost::countActive($search);
        $pages = (int)ceil($total / $perPage);

        $posts = InternalPost::getActive($perPage, $offset, $search);

        $this->view('admin/internal-info-list', [
            'posts' => $posts,
            'page' => $page,
            'pages' => $pages,
            'total' => $total,
            'perPage' => $perPage,
            'search' => $search
        ]);
    }

    public function deleteInternalInfo()
    {
        require_once __DIR__ . '/../Models/InternalPost.php';
        require_once __DIR__ . '/../Helpers/functions.php';

        // Kiểm tra method
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit;
        }

        $id = $_POST['id'] ?? null;
        $token = $_POST['_csrf'] ?? null;

        // Hỗ trợ nhận JSON (nếu frontend gửi JSON)
        if (!$id) {
            $input = file_get_contents('php://input');
            $json = json_decode($input, true);
            if (is_array($json)) {
                $id = $json['id'] ?? null;
                $token = $json['_csrf'] ?? ($json['token'] ?? null);
            }
        }

        // Kiểm tra request JSON hay Form thường
        $isJson = (!empty($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) ||
                  (!empty($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false);

        // Validate Token
        if (!verify_csrf($token)) {
            if ($isJson) {
                http_response_code(403);
                echo json_encode(['ok' => false, 'message' => 'Invalid Token']);
                exit;
            }
            $_SESSION['error'] = 'Token không hợp lệ.';
            header('Location: ' . BASE_URL . '/admin/internal-info-list');
            exit;
        }

        // Validate ID
        if (empty($id) || !is_numeric($id)) {
            if ($isJson) {
                http_response_code(400);
                echo json_encode(['ok' => false, 'message' => 'Invalid ID']);
                exit;
            }
            $_SESSION['error'] = 'ID không hợp lệ.';
            header('Location: ' . BASE_URL . '/admin/internal-info-list');
            exit;
        }

        // --- BẮT ĐẦU XỬ LÝ XÓA (KÈM TRY-CATCH) ---
        try {
            // Gọi hàm xóa trong Model
            $ok = InternalPost::deleteById((int)$id);
        } catch (\Throwable $e) {
            // Bắt lỗi SQL (ví dụ: lỗi khóa ngoại chưa xóa ảnh)
            $ok = false;
            // Ghi log lỗi nếu cần: error_log($e->getMessage());
            
            // Nếu là JSON, trả về lỗi chi tiết để hiển thị lên màn hình
            if ($isJson) {
                http_response_code(500); // Báo lỗi server
                echo json_encode([
                    'ok' => false, 
                    'message' => 'Lỗi Server: ' . $e->getMessage() // Quan trọng: Xem lỗi gì ở đây
                ]);
                exit;
            }
        }
        // --- KẾT THÚC XỬ LÝ ---

        // Trả về kết quả
        if ($isJson) {
            echo json_encode(['ok' => $ok]);
            exit;
        }

        if ($ok) $_SESSION['success'] = 'Đã xóa thông tin.';
        else $_SESSION['error'] = 'Xóa thất bại (Có thể do dữ liệu liên quan).';

        header('Location: ' . BASE_URL . '/admin/internal-info-list');
        exit;
    } // <--- Đảm bảo có dấu ngoặc này!
    public function InternalInfoDetail()
    {
        require_once __DIR__ . '/../Models/InternalPost.php';

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            header('Location: ' . BASE_URL . '/admin/info');
            exit;
        }

        $post = InternalPost::getById($id);
        if (!$post) {
            header('Location: ' . BASE_URL . '/admin/info');
            exit;
        }

        $this->view('admin/internal-info-detail', ['post' => $post]);
    }
    public function InternalInfoEdit()
    {
        require_once __DIR__ . '/../Models/InternalPost.php';
        require_once __DIR__ . '/../Helpers/functions.php';
        require_once __DIR__ . '/../../core/Auth.php';

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            $_SESSION['error'] = 'ID không hợp lệ.';
            header('Location: ' . BASE_URL . '/admin/internal-info-list');
            exit;
        }

        // If POST, process update
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['_csrf'] ?? null;
            if (!verify_csrf($token)) {
                $_SESSION['error'] = 'Token không hợp lệ.';
                header('Location: ' . BASE_URL . '/admin/internal-info-edit?id=' . $id);
                exit;
            }

            $tieu_de = trim($_POST['tieu_de'] ?? '');
            $noi_dung = trim($_POST['noi_dung'] ?? '');

            if ($tieu_de === '' || $noi_dung === '') {
                $_SESSION['error'] = 'Vui lòng nhập tiêu đề và nội dung.';
                header('Location: ' . BASE_URL . '/admin/internal-info-edit?id=' . $id);
                exit;
            }

            $data = [
                'tieu_de' => $tieu_de,
                'noi_dung' => $noi_dung,
                'trang_thai' => 1
            ];

            $ok = InternalPost::update($id, $data);

            // Handle removed images (checkboxes with name remove_images[] containing image ids)
            if (!empty($_POST['remove_images']) && is_array($_POST['remove_images'])) {
                foreach ($_POST['remove_images'] as $imgId) {
                    $imgId = (int)$imgId;
                    if ($imgId > 0) InternalPost::deleteImageById($imgId);
                }
            }

            // Handle newly uploaded media
            $saved = [];
            $maxFiles = 6;
            $maxSize = 8 * 1024 * 1024; // 8MB
            $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'video/mp4', 'video/quicktime'];

            if (!empty($_FILES['media']) && is_array($_FILES['media']['tmp_name'])) {
                $count = count($_FILES['media']['tmp_name']);
                if ($count > $maxFiles) $count = $maxFiles;

                $uploadsDir = realpath(__DIR__ . '/../../public') . '/uploads/internal/' . $id;
                if (!is_dir($uploadsDir)) mkdir($uploadsDir, 0755, true);

                for ($i = 0; $i < $count; $i++) {
                    $err = $_FILES['media']['error'][$i] ?? UPLOAD_ERR_NO_FILE;
                    if ($err !== UPLOAD_ERR_OK) continue;
                    $tmp = $_FILES['media']['tmp_name'][$i];
                    $orig = basename($_FILES['media']['name'][$i] ?? 'file');
                    $ext = pathinfo($orig, PATHINFO_EXTENSION);
                    $size = filesize($tmp);
                    if ($size > $maxSize) continue;
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mime = finfo_file($finfo, $tmp);
                    finfo_close($finfo);
                    if (!in_array($mime, $allowedMimes, true)) continue;

                    $filename = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
                    $dest = $uploadsDir . '/' . $filename;
                    if (move_uploaded_file($tmp, $dest)) {
                        $webPath = 'uploads/internal/' . $id . '/' . $filename;
                        $saved[] = $webPath;
                    }
                }
            }

            if (!empty($saved)) InternalPost::addImages($id, $saved);

            if ($ok) {
                $_SESSION['success'] = 'Cập nhật thông tin nội bộ thành công.';
            } else {
                $_SESSION['error'] = 'Cập nhật thất bại.';
            }

            header('Location: ' . BASE_URL . '/admin/internal-info-edit?id=' . $id);
            exit;
        }

        // GET: load and show
        $post = InternalPost::getById($id);
        if (!$post) {
            $_SESSION['error'] = 'Không tìm thấy bài viết.';
            header('Location: ' . BASE_URL . '/admin/internal-info-list');
            exit;
        }

        $this->view('admin/internal-info-edit', ['post' => $post]);
    }
     public function termsService()
    {
        $this->view('admin/terms-service');
    }
    public function privacyPolicy()
    {
        $this->view('admin/privacy-policy');
    }

    public function cookiePolicy()
    {
        $this->view('admin/cookie-policy');
    }
    public function paymentPolicy()
    {
        $this->view('admin/payment-policy');
    }

}
