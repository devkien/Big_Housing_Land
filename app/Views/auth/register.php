<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký tài khoản</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <div class="app-container register-page">
        <?php 
        require_once __DIR__ . '/../partials/alert.php'; 
        $old = $_SESSION['old'] ?? [];
        unset($_SESSION['old']);
        ?>

        <header class="register-header">
            <a href="<?= BASE_URL ?>/login" class="back-btn"><i class="fa-solid fa-chevron-left"></i></a>
            <h3>Đăng ký</h3>
            <div style="width: 18px;"></div>
        </header>

        <form action="<?= BASE_URL ?>/register" method="POST" enctype="multipart/form-data">

            <div class="section-label">1. Thông tin đăng nhập</div>

            <div class="reg-box-input">
                <i class="fa-solid fa-user"></i>
                <input type="text" name="so_dien_thoai" placeholder="Nhập số điện thoại" pattern="[0-9]{10}" value="<?= htmlspecialchars($old['so_dien_thoai'] ?? '') ?>" required>
            </div>

            <div class="reg-box-input">
                <i class="fa-solid fa-lock" style="font-size: 12px;"></i> <input type="password" name="password" placeholder="Vui lòng nhập mật khẩu" required>
            </div>

            <div class="section-label">2. Thông tin cơ bản</div>

            <div class="reg-box-input">
                <i class="fa-solid fa-location-arrow"></i> <input type="text" name="ho_ten" placeholder="Nhập họ và tên" value="<?= htmlspecialchars($old['ho_ten'] ?? '') ?>" required>
            </div>


            <label class="reg-box-input">
                <i class="fa-regular fa-calendar"></i>
                <input type="number" name="nam_sinh" placeholder="Nhập năm sinh" min="1900" max="2025" value="<?= htmlspecialchars($old['nam_sinh'] ?? '') ?>" required>
            </label>
            <div style="height: 10px;"></div>
            <div class="reg-box-input">
                <i class="fa-solid fa-location-dot icon-location"></i>
                <input type="text" name="dia_chi" placeholder="Địa chỉ" value="<?= htmlspecialchars($old['dia_chi'] ?? '') ?>" required>
            </div>
            <div class="reg-line-select">
                <i class="icon-left fa-solid fa-transgender"></i>
                <select name="gioi_tinh" required>
                    <option value="" disabled <?= empty($old['gioi_tinh']) ? 'selected' : '' ?>>Chọn giới tính</option>
                    <option value="nam" <?= ($old['gioi_tinh'] ?? '') === 'nam' ? 'selected' : '' ?>>Nam</option>
                    <option value="nu" <?= ($old['gioi_tinh'] ?? '') === 'nu' ? 'selected' : '' ?>>Nữ</option>
                    <option value="khac" <?= ($old['gioi_tinh'] ?? '') === 'khac' ? 'selected' : '' ?>>Khác</option>
                </select>
                <i class="arrow-right fa-solid fa-chevron-down"></i>
            </div>

            <div class="reg-box-input">
                <i class="fa-solid fa-envelope"></i>
                <input type="email" name="email" placeholder="Nhập email" value="<?= htmlspecialchars($old['email'] ?? '') ?>" required>
            </div>

            <div class="reg-box-input">
                <i class="fa-brands fa-facebook"></i>
                <input type="text" name="link_fb" placeholder="Nhập link fb" value="<?= htmlspecialchars($old['link_fb'] ?? '') ?>" required>
            </div>
            <div class="reg-box-input">
                <i class="fa-solid fa-user icon-user"></i>
                <input type="text" name="ma_gioi_thieu" placeholder="Mã giới thiệu" value="<?= htmlspecialchars($old['ma_gioi_thieu'] ?? '') ?>" required>
            </div>


            <div class="section-label">3. Thông tin Big Housing</div>

            <div class="reg-line-select">
                <i class="icon-left fa-solid fa-book"></i>
                <select name="loai_tai_khoan" required>
                    <option value="" disabled <?= empty($old['loai_tai_khoan']) ? 'selected' : '' ?>>Chọn loại tài khoản</option>
                    <option value="nhan_vien" <?= ($old['loai_tai_khoan'] ?? '') === 'nhan_vien' ? 'selected' : '' ?>>Cộng tác viên</option>
                    <option value="quan_ly" <?= ($old['loai_tai_khoan'] ?? '') === 'quan_ly' ? 'selected' : '' ?>>Đầu chủ</option>
                </select>
                <i class="arrow-right fa-solid fa-chevron-down"></i>
            </div>

            <div class="reg-line-select">
                <i class="icon-left fa-solid fa-map-marker-alt"></i>
                <select name="vi_tri" required>
                    <option value="" disabled <?= empty($old['vi_tri']) ? 'selected' : '' ?>>Chọn vị trí</option>
                    <option value="kho_nha_dat" <?= ($old['vi_tri'] ?? '') === 'kho_nha_dat' ? 'selected' : '' ?>>Kho nhà đất</option>
                    <option value="kho_nha_cho_thue" <?= ($old['vi_tri'] ?? '') === 'kho_nha_cho_thue' ? 'selected' : '' ?>>Kho nhà cho thuê</option>
                    <option value="ca_hai" <?= ($old['vi_tri'] ?? '') === 'ca_hai' ? 'selected' : '' ?>>Kho nhà đất và kho nhà cho thuê</option>
                </select>
                <i class="arrow-right fa-solid fa-chevron-down"></i>
            </div>

            <div class="reg-line-select">
                <i class="icon-left fa-solid fa-fire"></i>
                <select name="phong_ban" required>
                    <option value="" disabled <?= empty($old['phong_ban']) ? 'selected' : '' ?>>Chọn phòng ban</option>
                    <option value="kd1" <?= ($old['phong_ban'] ?? '') === 'kd1' ? 'selected' : '' ?>>Thiện Chiến</option>
                    <option value="kd2" <?= ($old['phong_ban'] ?? '') === 'kd2' ? 'selected' : '' ?>>Hùng Phát</option>
                    <option value="kd3" <?= ($old['phong_ban'] ?? '') === 'kd3' ? 'selected' : '' ?>>Tinh Nhuệ</option>
                </select>
                <i class="arrow-right fa-solid fa-chevron-down"></i>
            </div>

            <div class="edit-form-group">
                <div class="edit-label-row"><span>4.Mặt trước CCCD</span></div>
                <div class="upload-box-large-center" id="upload-box-cccd" style="position: relative; cursor: pointer; margin-top: 10px;">
                    <div class="upload-hint-text" style="z-index: 2;">
                        <i class="fa-solid fa-circle-info"></i> Tải hình ảnh
                    </div>

                    <i class="fa-solid fa-camera icon-camera-large" id="icon-camera-cccd"></i>
                    <i class="fa-solid fa-plus icon-plus-absolute" id="icon-plus-cccd"></i>

                    <div class="upload-preview-container" id="preview-container-cccd" style="display: none;">
                        <img src="" class="upload-preview-img" id="preview-img-cccd" alt="Preview">
                        <button class="btn-remove-image" id="btn-remove-img-cccd" type="button"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                    <input type="file" id="file-upload-cccd" name="anh_cccd" style="display: none;" accept="image/*">
                </div>
            </div>

            <button class="btn-save">Đăng ký</button>

        </form>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const uploadBox = document.getElementById('upload-box-cccd');
            const fileInput = document.getElementById('file-upload-cccd');
            const previewContainer = document.getElementById('preview-container-cccd');
            const previewImg = document.getElementById('preview-img-cccd');
            const btnRemove = document.getElementById('btn-remove-img-cccd');
            const iconCamera = document.getElementById('icon-camera-cccd');
            const iconPlus = document.getElementById('icon-plus-cccd');
            const hintText = uploadBox.querySelector('.upload-hint-text');

            uploadBox.addEventListener('click', function() {
                if (previewContainer.style.display === 'none') {
                    fileInput.click();
                }
            });

            fileInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file && file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(evt) {
                        previewContainer.style.display = 'flex';
                        iconCamera.style.display = 'none';
                        iconPlus.style.display = 'none';
                        if (hintText) hintText.style.display = 'none';
                        uploadBox.style.backgroundColor = 'white';
                        previewImg.src = evt.target.result;
                    }
                    reader.readAsDataURL(file);
                }
            });

            btnRemove.addEventListener('click', function(e) {
                e.stopPropagation();
                fileInput.value = '';
                previewContainer.style.display = 'none';
                previewImg.src = '';
                iconCamera.style.display = 'block';
                iconPlus.style.display = 'block';
                if (hintText) hintText.style.display = 'block';
                uploadBox.style.backgroundColor = '#f2f6ff';
            });
        });
    </script>
</body>

</html>

<!DOCTYPE html>
<html