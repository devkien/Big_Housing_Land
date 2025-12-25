<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết nhân sự</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
    <style>
        /* Fix lỗi mắt bé */
        input::-ms-reveal,
        input::-ms-clear {
            display: none;
        }

        /* Fix lỗi select */
        .custom-select.no-border {
            border: none !important;
            padding: 0 10px !important;
            outline: none !important;
            flex: 1;
            width: 100%;
            background-color: transparent !important;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            color: #000;
            font-weight: 500;
        }
    </style>
</head>

<body>
    <div class="app-container" style="background: white;">

        <div class="edit-header">
            <a href="<?= BASE_URL ?>/superadmin/management-owner" style="position: absolute; left: 15px; color: black;"><i class="fa-solid fa-chevron-left"></i></a>
            Chi tiết - QL Nhân sự
        </div>

        <?php $user = $user ?? []; ?>
        <div class="profile-card-edit" style="margin-top: 20px;">
            <div class="avatar-edit-wrapper">
                <img src="<?= htmlspecialchars($user['avatar'] ?? BASE_URL . '/public/icon/menuanhdaidien.png') ?>" class="avatar-img-edit">
            </div>

            <div class="profile-info">
                <h3 style="font-size: 18px; font-weight: 700; margin-bottom: 4px;"><?= htmlspecialchars($user['ho_ten'] ?? '') ?></h3>
                <div style="font-size: 13px;"><?= (($user['quyen'] ?? '') === 'admin') ? 'Đầu chủ' : 'Đầu khách' ?></div>
                <div class="deal-count-badge"><?php echo isset($user['so_vu_chot']) ? 'Số vụ chốt: ' . (int)$user['so_vu_chot'] : ''; ?></div>
            </div>

            <i class="fa-solid fa-chevron-right arrow-right-absolute"></i>
        </div>

        <div style="padding-bottom: 80px;">

            <?php require_once __DIR__ . '/../../Helpers/functions.php'; ?>
            <form method="POST" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="id" value="<?= (int)($user['id'] ?? 0) ?>">

                <div class="edit-form-group">
                    <div class="edit-label-row"><span>1. Họ và tên</span></div>
                    <div class="edit-input-box">
                        <i class="fa-solid fa-user"></i>
                        <input type="text" name="ho_ten" value="<?= htmlspecialchars($user['ho_ten'] ?? '') ?>">
                    </div>
                </div>

                <div class="edit-form-group">
                    <div class="edit-label-row">
                        <span>2. Số điện thoại</span>
                        <span class="counter-text">0/10</span>
                    </div>
                    <div class="edit-input-box">
                        <i class="fa-solid fa-phone"></i>
                        <input type="text" name="so_dien_thoai" value="<?= htmlspecialchars($user['so_dien_thoai'] ?? '') ?>">
                    </div>
                </div>

                <div class="edit-form-group">
                    <div class="edit-label-row">
                        <span>3. Email</span>
                        <span class="counter-text">0/10</span>
                    </div>
                    <div class="edit-input-box">
                        <i class="fa-solid fa-envelope"></i>
                        <input type="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>">
                    </div>
                </div>

                <div class="edit-form-group">
                    <div class="edit-label-row">
                        <span>4. Ngày sinh</span>
                        <span class="counter-text">0/10</span>
                    </div>
                    <div class="edit-input-box">
                        <i class="fa-regular fa-calendar"></i>
                        <input type="text" name="nam_sinh" value="<?= htmlspecialchars($user['nam_sinh'] ?? '') ?>">
                    </div>
                </div>

                <div class="edit-form-group">
                    <div class="edit-label-row"><span>5. Link Facebook</span></div>
                    <div class="edit-input-box">
                        <i class="fa-brands fa-facebook"></i>
                        <input type="text" name="link_fb" value="<?= htmlspecialchars($user['link_fb'] ?? '') ?>">
                    </div>
                </div>

                <div class="edit-form-group">
                    <div class="edit-label-row"><span>6. Căn Cước công dân</span></div>
                    <div class="edit-input-box">
                        <i class="fa-regular fa-id-card"></i>
                        <input type="text" name="so_cccd" value="<?= htmlspecialchars($user['so_cccd'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-row-split">
                    <div class="col-left-60">
                        <div class="edit-label-row"><span>7. Phòng ban</span></div>
                        <div class="input-relative">
                            <select class="custom-select" name="phong_ban">
                                <option value="<?= htmlspecialchars($user['phong_ban'] ?? '') ?>"><?= htmlspecialchars($user['phong_ban'] ?? '') ?></option>
                            </select>
                            <i class="fa-solid fa-chevron-down arrow-down-absolute"></i>
                        </div>
                    </div>
                    <div class="col-right-40">
                        <div class="edit-label-row">
                            <span>Mã nhân viên</span>
                            <span class="counter-text">0/10</span>
                        </div>
                        <div class="edit-input-box" style="padding: 10px;">
                            <input type="text" name="ma_nhan_su" value="<?= htmlspecialchars($user['ma_nhan_su'] ?? '') ?>" style="text-align: center;">
                        </div>
                    </div>
                </div>

                <div class="edit-form-group">
                    <div class="edit-label-row"><span>8. Phân loại</span></div>
                    <div class="edit-input-box input-relative">
                        <i class="fa-solid fa-key icon-key" style="transform: scaleX(-1); display: inline-block;"></i>
                        <select class="custom-select select-type-fix" name="quyen">
                            <option value="admin" <?= (($user['quyen'] ?? '') === 'admin') ? 'selected' : '' ?>>Đầu chủ</option>
                            <option value="user" <?= (($user['quyen'] ?? '') !== 'admin') ? 'selected' : '' ?>>Đầu khách</option>
                        </select>
                        <i class="fa-solid fa-chevron-down arrow-down-absolute"></i>
                    </div>
                </div>

                <div class="edit-form-group">
                    <div class="edit-label-row"><span>9. Địa chỉ</span></div>
                    <div class="edit-input-box">
                        <i class="fa-solid fa-location-dot"></i>
                        <input type="text" name="dia_chi" value="<?= htmlspecialchars($user['dia_chi'] ?? '') ?>">
                    </div>
                </div>

                <div class="edit-form-group">
                    <div class="edit-label-row"><span>10.Mã người giới thiệu</span></div>
                    <div class="edit-input-box">
                        <input type="text" name="ma_gioi_thieu" value="<?= htmlspecialchars($user['ma_gioi_thieu'] ?? '') ?>" style="padding-left: 5px;">
                    </div>
                </div>

                <div class="edit-form-group">
                    <div class="edit-label-row"><span>11.Mật khẩu</span></div>
                    <div class="edit-input-box input-relative" style="position: relative;">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" name="password" value="" id="password-input">
                        <i class="fa-regular fa-eye-slash" id="toggle-password" style="color:#000; cursor: pointer; position: absolute; right: 10px; top: 50%; transform: translateY(-50%);"></i>
                    </div>
                </div>

                <div class="edit-form-group">
                    <div class="edit-label-row"><span>12.Mặt trước CCCD</span></div>
                    <div class="upload-box-large-center" id="upload-box-cccd" style="position: relative; cursor: pointer; margin-top: 10px;">
                        <div class="upload-hint-text" style="z-index: 2;">
                            <i class="fa-solid fa-circle-info"></i> Tải hình ảnh
                        </div>

                        <i class="fa-solid fa-camera icon-camera-large" id="icon-camera-cccd"></i>
                        <i class="fa-solid fa-plus icon-plus-absolute" id="icon-plus-cccd"></i>

                        <div class="upload-preview-container" id="preview-container-cccd" style="display: <?= !empty($user['anh_cccd']) ? 'flex' : 'none' ?>;">
                            <img src="<?= !empty($user['anh_cccd']) ? htmlspecialchars($user['anh_cccd']) : '' ?>" class="upload-preview-img" id="preview-img-cccd" alt="Preview">
                            <button class="btn-remove-image" id="btn-remove-img-cccd" type="button"><i class="fa-solid fa-xmark"></i></button>
                        </div>
                        <input type="file" id="file-upload-cccd" name="anh_cccd" style="display: none;" accept="image/*">
                    </div>
                </div>

                <div class="edit-form-group">
                    <div class="edit-label-row"><span>13.Trạng thái hoạt động</span></div>
                    <div class="edit-input-box input-relative">
                        <?php $status = old('trang_thai', isset($user['trang_thai']) ? $user['trang_thai'] : 0); ?>
                        <select class="custom-select" name="trang_thai" style="padding-left: 10px; cursor: pointer;">
                            <option value="1" <?= (string)$status === '1' ? 'selected' : '' ?>>Hoạt động</option>
                            <option value="2" <?= (string)$status === '2' ? 'selected' : '' ?>>Tạm dừng</option>
                            <option value="0" <?= (string)$status === '0' ? 'selected' : '' ?>>Chờ duyệt</option>
                        </select>
                        <i class="fa-solid fa-chevron-down arrow-down-absolute"></i>
                    </div>
                </div>
                <div class="bottom-action-bar">
                    <a href="<?= BASE_URL ?>/superadmin/management-owner" class="btn-delete-red" style="text-decoration:none;display:inline-block;padding:10px 16px;">Quay lại</a>
                    <button type="submit" class="btn-save-right">Lưu thay đổi</button>
                </div>
            </form>

        </div>
        <div id="bottom-nav-container">
            <?php require_once __DIR__ . '/layouts/bottom-nav.php'; ?>
        </div>
    </div>
    <script src="<?= BASE_URL ?>/public/js/script.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const uploadBox = document.getElementById('upload-box-cccd');
            const fileInput = document.getElementById('file-upload-cccd');
            const previewContainer = document.getElementById('preview-container-cccd');
            const previewImg = document.getElementById('preview-img-cccd');
            const btnRemove = document.getElementById('btn-remove-img-cccd');
            const iconCamera = document.getElementById('icon-camera-cccd');
            const iconPlus = document.getElementById('icon-plus-cccd');

            // Xử lý click vào box để tải ảnh
            uploadBox.addEventListener('click', function() {
                if (previewContainer.style.display === 'none') {
                    fileInput.click();
                }
            });

            // Xử lý khi chọn file
            fileInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file && file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(evt) {
                        previewContainer.style.display = 'flex';
                        iconCamera.style.display = 'none';
                        iconPlus.style.display = 'none';
                        uploadBox.style.backgroundColor = 'white';
                        previewImg.src = evt.target.result;
                    }
                    reader.readAsDataURL(file);
                }
            });

            // Xử lý click nút Xóa
            btnRemove.addEventListener('click', function(e) {
                e.stopPropagation();
                fileInput.value = '';
                previewContainer.style.display = 'none';
                previewImg.src = '';
                iconCamera.style.display = 'block';
                iconPlus.style.display = 'block';
                uploadBox.style.backgroundColor = '#f2f6ff';
            });
        });
    </script>
</body>

</html>