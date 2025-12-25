<?php

class ResourceController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        // Only super_admin
        $this->requireRole([ROLE_SUPER_ADMIN]);
    }

    public function resourcePost()
    {
        // If POST: handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../Helpers/functions.php';
            if (!verify_csrf($_POST['_csrf'] ?? null)) {
                $_SESSION['error'] = 'Token không hợp lệ.';
                header('Location: ' . BASE_URL . '/superadmin/management-resource-post');
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
                'trich_thuong_don_vi' => ['%', 'VND'],
                'don_vi_gia' => ['nguyen_can', 'm2']
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

            $don_vi_gia = trim($_POST['don_vi_gia'] ?? '');
            if (!in_array($don_vi_gia, $allowed['don_vi_gia'], true)) {
                $don_vi_gia = 'nguyen_can';
            }

            // floors validation
            $so_tang_raw = $_POST['so_tang'] ?? '';
            $so_tang = null;
            if ($so_tang_raw !== '') {
                $so_tang_val = filter_var($so_tang_raw, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 100]]);
                if ($so_tang_val === false) {
                    $_SESSION['error'] = 'Số tầng không hợp lệ';
                    header('Location: ' . BASE_URL . '/superadmin/management-resource-post');
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
                // If phap_ly indicates there is a title ('co_so'), capture the mã số sổ; otherwise store null
                'ma_so_so' => ($phap_ly === 'co_so') ? (trim($_POST['ma_so_so'] ?? '') ?: null) : null,
                'dien_tich' => $makeFloat($_POST['dien_tich'] ?? null),
                'don_vi_dien_tich' => $don_vi,
                'chieu_dai' => $makeFloat($_POST['chieu_dai'] ?? null),
                'chieu_rong' => $makeFloat($_POST['chieu_rong'] ?? null),
                'so_tang' => $so_tang,
                'gia_chao' => $makeFloat($_POST['gia_chao'] ?? null),
                'don_vi_gia' => $don_vi_gia,
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
                header('Location: ' . BASE_URL . '/superadmin/management-resource-post');
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
                    header('Location: ' . BASE_URL . '/superadmin/management-resource-post');
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
                header('Location: ' . BASE_URL . '/superadmin/management-resource-post');
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
            header('Location: ' . BASE_URL . '/superadmin/management-resource-post');
            exit;
        }

        $this->view('superadmin/resource-post');
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

        // If search term looks like a resource code, try exact match on ma_hien_thi first
        $properties = [];
        if ($searchTerm) {
            $code = trim($searchTerm);
            $found = Property::findByMaHienThi($code);
            if ($found) {
                $total = 1;
                $pages = 1;
                $offset = 0;
                $properties = [$found];
            } else {
                $total = Property::countByLoaiKho('kho_nha_dat', $searchTerm, $status);
                $pages = (int)ceil($total / $perPage);
                $offset = ($page - 1) * $perPage;
                $properties = Property::getByLoaiKho('kho_nha_dat', $perPage, $offset, $searchTerm, $status);
            }
        } else {
            $total = Property::countByLoaiKho('kho_nha_dat', $searchTerm, $status);
            $pages = (int)ceil($total / $perPage);
            $offset = ($page - 1) * $perPage;
            $properties = Property::getByLoaiKho('kho_nha_dat', $perPage, $offset, $searchTerm, $status);
        }

        // load collections for "save to collection" modal
        require_once __DIR__ . '/../Models/Collection.php';
        $collections = Collection::allWithCount();

        $this->view('superadmin/resource', [
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

        $this->view('superadmin/resource-rent', [
            'properties' => $properties,
            'page' => $page,
            'pages' => $pages,
            'total' => $total,
            'perPage' => $perPage,
            'search' => $search,
            'status' => $status,
            'address' => $address,
            // load collections for save modal
            'collections' => (function () {
                require_once __DIR__ . '/../Models/Collection.php';
                return Collection::allWithCount();
            })()
        ]);
    }

    public function resourceDetail()
    {
        require_once __DIR__ . '/../Models/Property.php';

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (!$id) {
            $_SESSION['error'] = 'ID tài nguyên không hợp lệ.';
            header('Location: ' . BASE_URL . '/superadmin/management-resource');
            exit;
        }

        $property = Property::findById($id);
        if (!$property) {
            $_SESSION['error'] = 'Không tìm thấy tài nguyên.';
            header('Location: ' . BASE_URL . '/superadmin/management-resource');
            exit;
        }

        $media = Property::getMedia($id);

        $this->view('superadmin/resource-detail', [
            'property' => $property,
            'media' => $media
        ]);
    }

    // AJAX: save property into selected collections
    public function saveToCollections()
    {
        // Accept JSON body OR standard form POST (fallback)
        require_once __DIR__ . '/../Helpers/functions.php';
        $body = file_get_contents('php://input');
        $logPath = __DIR__ . '/../../storage/logs/save_collections.log';
        @file_put_contents($logPath, date('Y-m-d H:i:s') . " - Raw body: " . $body . "\n", FILE_APPEND);

        $data = json_decode($body, true);
        // If not JSON, try form-encoded POST
        if (!is_array($data) || empty($data)) {
            if (!empty($_POST)) {
                $data = $_POST;
                @file_put_contents($logPath, date('Y-m-d H:i:s') . " - Using \\$_POST payload: " . json_encode($data) . "\n", FILE_APPEND);
            }
        }

        header('Content-Type: application/json');

        if (!$data || !isset($data['property_id']) || !isset($data['collections'])) {
            @file_put_contents($logPath, date('Y-m-d H:i:s') . " - Missing params: " . json_encode($data) . "\n", FILE_APPEND);
            http_response_code(400);
            echo json_encode(['ok' => false, 'message' => 'Missing parameters']);
            return;
        }

        $csrfOk = verify_csrf($data['_csrf'] ?? ($_POST['_csrf'] ?? null));
        @file_put_contents($logPath, date('Y-m-d H:i:s') . " - CSRF ok: " . ($csrfOk ? '1' : '0') . "\n", FILE_APPEND);
        if (!$csrfOk) {
            http_response_code(403);
            echo json_encode(['ok' => false, 'message' => 'Invalid CSRF token']);
            return;
        }

        $propertyId = (int)$data['property_id'];
        $collections = is_array($data['collections']) ? $data['collections'] : [];

        if ($propertyId <= 0 || empty($collections)) {
            @file_put_contents($logPath, date('Y-m-d H:i:s') . " - Invalid params: property_id={$propertyId}, collections=" . json_encode($collections) . "\n", FILE_APPEND);
            http_response_code(400);
            echo json_encode(['ok' => false, 'message' => 'Invalid parameters']);
            return;
        }

        require_once __DIR__ . '/../Models/Collection.php';

        $added = Collection::addItems($collections, $propertyId, 'bat_dong_san');
        @file_put_contents($logPath, date('Y-m-d H:i:s') . " - Added count: {$added}\n", FILE_APPEND);

        echo json_encode(['ok' => true, 'added' => $added]);
    }

    // AJAX handler to update property status
    public function updateStatus()
    {
        // Expect JSON body: { id: int, status: 'ban_manh'|'tam_dung_ban'|..., _csrf: token }
        require_once __DIR__ . '/../Helpers/functions.php';
        // Read JSON payload
        $body = file_get_contents('php://input');
        $data = json_decode($body, true);

        if (!$data) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['ok' => false, 'message' => 'Invalid payload']);
            return;
        }

        if (!verify_csrf($data['_csrf'] ?? null)) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['ok' => false, 'message' => 'Invalid CSRF token']);
            return;
        }

        $id = isset($data['id']) ? (int)$data['id'] : 0;
        $statusInput = trim($data['status'] ?? '');
        if (!$id || $statusInput === '') {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['ok' => false, 'message' => 'Missing parameters']);
            return;
        }

        // Allow either display labels or internal enum values
        $map = [
            'Bán mạnh' => 'ban_manh',
            'Tạm dừng bán' => 'tam_dung_ban',
            'Dừng bán' => 'dung_ban',
            'Đã bán' => 'da_ban',
            'Tăng chào' => 'tang_chao',
            'Hạ chào' => 'ha_chao',
            'ban_manh' => 'ban_manh',
            'tam_dung_ban' => 'tam_dung_ban',
            'dung_ban' => 'dung_ban',
            'da_ban' => 'da_ban',
            'tang_chao' => 'tang_chao',
            'ha_chao' => 'ha_chao'
        ];

        $trang_thai = $map[$statusInput] ?? null;
        if (!$trang_thai) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['ok' => false, 'message' => 'Invalid status value']);
            return;
        }

        require_once __DIR__ . '/../Models/Property.php';
        $ok = Property::updateStatus($id, $trang_thai);
        header('Content-Type: application/json');
        if ($ok) {
            echo json_encode(['ok' => true, 'message' => 'Cập nhật trạng thái thành công']);
        } else {
            http_response_code(500);
            echo json_encode(['ok' => false, 'message' => 'Không thể cập nhật cơ sở dữ liệu']);
        }
    }
}
