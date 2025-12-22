<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng tin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../Public/Css/style.css">
    <script src="../Public/Js/script.js"></script>
</head>

<body>
    <div class="app-container" style="background: white;">

        <header class="detail-header">
            <a href="<?= BASE_URL ?>/superadmin/home" class="header-icon-btn"><i class="fa-solid fa-chevron-left"></i></a>
            <div class="detail-title">Đăng tin</div>
            <div class="header-icon-btn"></div>
        </header>

        <div class="post-form-scroll" style="padding-bottom: 80px;">
            <div class="form-section-title">Người đăng</div>
            <div class="form-group">
                <input type="text" class="form-input focus-blue" value="Họ tên đầu chủ">
            </div>
            <div class="form-group">
                <select class="form-input">
                    <option value="">Chọn Phòng Ban</option>
                    <option value="kd1">Thiện Chiến</option>
                    <option value="kd2">Hùng Phát</option>
                    <option value="tc">Tinh Nhuệ</option>
                </select>
            </div>

            <div class="form-section-title">Thông tin BĐS</div>
            <div class="form-group" style="position: relative;">
                <input type="text" class="form-input" placeholder=" ">
                <span class="fake-placeholder">Tiêu đề <span class="required-star">*</span></span>
            </div>
            <div class="form-group" style="position: relative;">
                <select class="form-input" required>
                    <option value="" disabled selected></option>
                    <option value="nha-pho">Nhà phố</option>
                    <option value="dat-nen">Đất nền</option>
                </select>
                <span class="fake-placeholder">Chọn Loại tin BĐS <span class="required-star">*</span></span>
            </div>
            <div class="form-group" style="position: relative;">
                <select class="form-input" required>
                    <option value="" disabled selected></option>
                    <option value="nha-pho">Có sổ</option>
                    <option value="dat-nen">Không sổ</option>
                </select>
                <span class="fake-placeholder">Pháp lý <span class="required-star">*</span></span>
            </div>
            <div class="form-section-title">Diện tích & giá</div>

            <div class="form-group input-with-unit">
                <div style="position: relative; flex: 1;">
                    <input type="number" class="form-input" placeholder=" ">
                    <span class="fake-placeholder">Diện tích đất <span class="required-star">*</span></span>
                </div>
                <select class="unit-select">
                    <option>m²</option>
                    <option>ha</option>
                </select>
            </div>

            <div class="form-row-2col">
                <div class="col-half">
                    <input type="number" class="form-input" placeholder="Chiều ngang">
                </div>
                <div class="col-half">
                    <input type="number" class="form-input" placeholder="Chiều dài">
                </div>
            </div>

            <div class="form-group" style="position: relative;">
                <select class="form-input" required>
                    <option value="" disabled selected></option>
                    <option value="nha-pho">1</option>
                    <option value="dat-nen">2</option>
                    <option value="nha-pho">3</option>
                    <option value="dat-nen">4</option>
                    <option value="nha-pho">5</option>
                    <option value="dat-nen">6</option>
                    <option value="nha-pho">7</option>
                    <option value="dat-nen">8</option>
                    <option value="nha-pho">9</option>
                    <option value="dat-nen">10</option>
                </select>
                <span class="fake-placeholder">Số tầng</span>
            </div>

            <div class="form-group input-with-unit">
                <div style="position: relative; flex: 1;">
                    <input type="number" class="form-input" placeholder=" ">
                    <span class="fake-placeholder">Giá chào</span>
                </div>
                <select class="unit-select">
                    <option>%</option>
                    <option>VND</option>
                </select>
            </div>

            <div class="form-group">
                <input type="text" class="form-input" placeholder="Trích thưởng">
            </div>

            <div class="form-section-title">Địa chỉ</div>
            <div class="form-group" style="position: relative;">
                <select class="form-input" required>
                    <option value="" disabled selected></option>
                    <option value="hn">Hà Nội</option>
                    <option value="hcm">TP. Hồ Chí Minh</option>
                </select>
                <span class="fake-placeholder">Tỉnh / Thành <span class="required-star">*</span></span>
            </div>
            <div class="form-group" style="position: relative;">
                <select class="form-input" required>
                    <option value="" disabled selected></option>
                    <option value="phuong1">Phường 1</option>
                    <option value="xa1">Xã A</option>
                </select>
                <span class="fake-placeholder">Xã / Phường <span class="required-star">*</span></span>
            </div>
            <div class="form-group">
                <input type="text" class="form-input" placeholder="Số nhà, tên đường">
            </div>
            <div class="checkbox-row">
                <input type="checkbox" id="showAddress">
                <label for="showAddress">Hiển thị số nhà</label>
            </div>
            <div class="form-group">
                <textarea class="form-textarea" placeholder="Thêm mô tả:"></textarea>
                <div class="char-counter">0/1500 ký tự</div>
            </div>
            <div class="upload-box" onclick="document.getElementById('file-upload').click()">
                <i class="fa-solid fa-camera upload-icon"></i>
                <div class="upload-text"><i class="fa-solid fa-plus"></i> Tải hình ảnh/video</div>
                <input type="file" id="file-upload" style="display: none;" accept="image/*,video/*" multiple onchange="previewMedia(this)">
            </div>
            <div id="media-preview-container" style="display: flex; gap: 10px; padding: 0 15px; flex-wrap: wrap; margin-bottom: 15px;"></div>

            <button class="btn-submit-blue">XONG</button>

        </div>

        <div id="bottom-nav-container">
            <?php require_once __DIR__ . '/layouts/bottom-nav.php'; ?>
        </div>

    </div>
</body>

</html>