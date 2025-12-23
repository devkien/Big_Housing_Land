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

        $total = Property::countByLoaiKho('kho_nha_dat', $searchTerm, $status);
        $pages = (int)ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;

        $properties = Property::getByLoaiKho('kho_nha_dat', $perPage, $offset, $searchTerm, $status);

        $this->view('superadmin/resource', [
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

        $this->view('superadmin/resource-rent', [
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
}
