<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa tin đăng</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
    <script src="<?= BASE_URL ?>/public/js/script.js"></script>
    <script>
        // Định nghĩa BASE_URL để JS sử dụng
        window.BASE_URL = '<?= BASE_URL ?>';
    </script>
</head>

<body>
    <div class="app-container" style="background: white;">
        <header class="detail-header">
            <a href="<?= BASE_URL ?>/superadmin/management-resource" class="header-icon-btn"><i class="fa-solid fa-chevron-left"></i></a>
            <div class="detail-title">Sửa tin đăng</div>
            <div class="header-icon-btn"></div>
        </header>

        <?php require_once __DIR__ . '/../../Helpers/functions.php'; ?>
        <form action="<?= BASE_URL ?>/superadmin/management-resource-edit?id=<?= $property['id'] ?>" method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= $property['id'] ?>">

            <div class="post-form-scroll" style="padding-bottom: 80px;">
                <div class="alert-wrapper">
                    <?php require_once __DIR__ . '/../partials/alert.php'; ?>
                </div>

                <div class="form-section-title">Thông tin BĐS</div>
                <div class="form-group" style="position: relative;">
                    <input type="text" name="tieu_de" class="form-input" placeholder=" " required value="<?= htmlspecialchars($property['tieu_de'] ?? '') ?>">
                    <span class="fake-placeholder">Tiêu đề <span class="required-star">*</span></span>
                </div>
                <div class="form-group" style="position: relative;">
                    <select name="loai_bds" class="form-input" required>
                        <option value="" disabled <?= empty($property['loai_bds']) ? 'selected' : '' ?>></option>
                        <option value="ban" <?= ($property['loai_bds'] ?? '') == 'ban' ? 'selected' : '' ?>>Bán</option>
                        <option value="cho_thue" <?= ($property['loai_bds'] ?? '') == 'cho_thue' ? 'selected' : '' ?>>Cho thuê</option>
                    </select>
                    <span class="fake-placeholder">Chọn Loại tin BĐS <span class="required-star">*</span></span>
                </div>
                <div class="form-group" style="position: relative;">
                    <select name="phap_ly" id="phap_ly_select" class="form-input" required>
                        <option value="" disabled <?= empty($property['phap_ly']) ? 'selected' : '' ?>></option>
                        <option value="co_so" <?= ($property['phap_ly'] ?? '') == 'co_so' ? 'selected' : '' ?>>Có sổ</option>
                        <option value="khong_so" <?= ($property['phap_ly'] ?? '') == 'khong_so' ? 'selected' : '' ?>>Không sổ</option>
                    </select>
                    <span class="fake-placeholder">Pháp lý <span class="required-star">*</span></span>
                </div>
                <div class="form-group" id="ma-so-so-group" style="display: <?= ($property['phap_ly'] ?? '') == 'co_so' ? 'block' : 'none' ?>; position: relative;">
                    <input type="text" name="ma_so_so" class="form-input" placeholder=" " value="<?= htmlspecialchars($property['ma_so_so'] ?? '') ?>">
                    <span class="fake-placeholder">Mã số sổ <span class="required-star">*</span></span>
                </div>
                <div class="form-section-title">Diện tích & giá <span class="required-star">*</span></div>

                <div class="form-group input-with-unit">
                    <div style="position: relative; flex: 1;">
                        <input type="number" name="dien_tich" step="any" class="form-input" placeholder=" " required value="<?= htmlspecialchars($property['dien_tich'] ?? '') ?>">
                        <span class="fake-placeholder">Diện tích đất</span>
                    </div>
                    <select name="don_vi_dien_tich" class="unit-select">
                        <option <?= ($property['don_vi_dien_tich'] ?? '') == 'm²' ? 'selected' : '' ?>>m²</option>
                        <option <?= ($property['don_vi_dien_tich'] ?? '') == 'ha' ? 'selected' : '' ?>>ha</option>
                    </select>
                </div>

                <div class="form-row-2col">
                    <div class="col-half">
                        <input type="number" name="chieu_rong" step="any" class="form-input" placeholder="Chiều ngang" value="<?= htmlspecialchars($property['chieu_rong'] ?? '') ?>">
                    </div>
                    <div class="col-half">
                        <input type="number" name="chieu_dai" step="any" class="form-input" placeholder="Chiều dài" value="<?= htmlspecialchars($property['chieu_dai'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-group" style="position: relative;">
                    <select name="so_tang" class="form-input" required>
                        <option value="" disabled <?= empty($property['so_tang']) ? 'selected' : '' ?>></option>
                        <?php for ($i = 1; $i <= 10; $i++): ?>
                            <option value="<?= $i ?>" <?= ($property['so_tang'] ?? '') == $i ? 'selected' : '' ?>><?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                    <span class="fake-placeholder">Số tầng</span>
                </div>

                <div class="form-group input-with-unit">
                    <div style="position: relative; flex: 1;">
                        <input type="number" name="gia_chao" step="any" class="form-input" placeholder=" " value="<?= htmlspecialchars($property['gia_chao'] ?? '') ?>">
                        <span class="fake-placeholder">Giá chào</span>
                    </div>
                    <select name="don_vi_gia" class="unit-select">
                        <option value="nguyen_can" <?= ($property['don_vi_gia'] ?? '') == 'nguyen_can' ? 'selected' : '' ?>>Nguyên căn</option>
                        <option value="m2" <?= ($property['don_vi_gia'] ?? '') == 'm2' ? 'selected' : '' ?>>m²</option>
                    </select>
                </div>

                <div class="form-group input-with-unit">
                    <div style="position: relative; flex: 1;">
                        <input type="text" name="trich_thuong_gia_tri" class="form-input" placeholder=" " value="<?= htmlspecialchars($property['trich_thuong_gia_tri'] ?? '') ?>">
                        <span class="fake-placeholder">Trích thưởng</span>
                    </div>
                    <select name="trich_thuong_don_vi" class="unit-select">
                        <option <?= ($property['trich_thuong_don_vi'] ?? '') == '%' ? 'selected' : '' ?>>%</option>
                        <option <?= ($property['trich_thuong_don_vi'] ?? '') == 'VND' ? 'selected' : '' ?>>VND</option>
                    </select>
                </div>

                <div class="form-section-title">Địa chỉ</div>

                <div class="form-group" style="position: relative;">
                    <select id="select-province" class="form-input" required>
                        <option value="" disabled selected>-- Chọn Tỉnh / Thành --</option>
                    </select>
                    <input type="hidden" name="tinh_thanh" id="input-tinh-thanh" value="<?= htmlspecialchars($property['tinh_thanh'] ?? '') ?>">
                </div>

                <div class="form-group" style="position: relative;">
                    <select id="select-ward" class="form-input">
                        <option value="" disabled selected>-- Chọn Xã / Phường (nếu có) --</option>
                    </select>
                    <input type="hidden" name="xa_phuong" id="input-xa-phuong" value="<?= htmlspecialchars($property['xa_phuong'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <input type="text" name="dia_chi_chi_tiet" class="form-input" placeholder="Số nhà, tên đường" value="<?= htmlspecialchars($property['dia_chi_chi_tiet'] ?? '') ?>">
                </div>

                <div class="checkbox-row">
                    <input type="checkbox" id="showAddress" name="is_visible" value="1" <?= !empty($property['is_visible']) ? 'checked' : '' ?>>
                    <label for="showAddress">Hiển thị số nhà</label>
                </div>

                <div class="form-group">
                    <textarea name="mo_ta" class="form-textarea" placeholder="Thêm mô tả:"><?= htmlspecialchars($property['mo_ta'] ?? '') ?></textarea>
                    <div class="char-counter">0/1500 ký tự</div>
                </div>

                <div class="upload-slots-row">
                    <div class="upload-slot">
                        <div class="form-section-title">Ảnh hiện trạng nhà</div>
                        <div class="upload-box" onclick="document.getElementById('file-upload-current').click()">
                            <i class="fa-solid fa-camera upload-icon"></i>
                            <div class="upload-text"><i class="fa-solid fa-plus"></i> Tải hình ảnh/video</div>
                            <input type="file" id="file-upload-current" name="media_current[]" style="display: none;" accept="image/*,video/*" multiple onchange="previewMediaSlot('current', this)">
                        </div>
                        <div id="media-preview-container-current" class="upload-preview-container">
                            <?php if (!empty($media_current)): ?>
                                <?php foreach ($media_current as $m): 
                                    $path = BASE_URL . '/' . ltrim($m['media_path'] ?? $m['path'], '/');
                                ?>
                                    <div class="preview-item">
                                        <img src="<?= htmlspecialchars($path) ?>">
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="upload-slot">
                        <div class="form-section-title">Ảnh HĐ trích thưởng</div>
                        <div class="upload-box" onclick="document.getElementById('file-upload-contract').click()">
                            <i class="fa-solid fa-camera upload-icon"></i>
                            <div class="upload-text"><i class="fa-solid fa-plus"></i> Tải hình ảnh/video</div>
                            <input type="file" id="file-upload-contract" name="media_contract[]" style="display: none;" accept="image/*,video/*" multiple onchange="previewMediaSlot('contract', this)">
                        </div>
                        <div id="media-preview-container-contract" class="upload-preview-container">
                             <?php if (!empty($media_contract)): ?>
                                <?php foreach ($media_contract as $m): 
                                    $path = BASE_URL . '/' . ltrim($m['media_path'] ?? $m['path'], '/');
                                ?>
                                    <div class="preview-item">
                                        <img src="<?= htmlspecialchars($path) ?>">
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Trạng thái</label>
                    <select name="trang_thai" class="form-input">
                        <option value="ban_manh" <?= ($property['trang_thai'] ?? '') == 'ban_manh' ? 'selected' : '' ?>>Bán mạnh</option>
                        <option value="tam_dung_ban" <?= ($property['trang_thai'] ?? '') == 'tam_dung_ban' ? 'selected' : '' ?>>Tạm dừng bán</option>
                        <option value="dung_ban" <?= ($property['trang_thai'] ?? '') == 'dung_ban' ? 'selected' : '' ?>>Dừng bán</option>
                        <option value="da_ban" <?= ($property['trang_thai'] ?? '') == 'da_ban' ? 'selected' : '' ?>>Đã bán</option>
                        <option value="tang_chao" <?= ($property['trang_thai'] ?? '') == 'tang_chao' ? 'selected' : '' ?>>Tăng chào</option>
                        <option value="ha_chao" <?= ($property['trang_thai'] ?? '') == 'ha_chao' ? 'selected' : '' ?>>Hạ chào</option>
                    </select>
                </div>

                <button type="submit" class="btn-submit-blue">LƯU THAY ĐỔI</button>
            </div>
        </form>

        <div id="bottom-nav-container">
            <?php require_once __DIR__ . '/layouts/bottom-nav.php'; ?>
        </div>
    </div>

    <script>
        (function() {
            // URL API
            const urls = [
                '<?= BASE_URL ?>' + '/api/locations.php',
                '<?= BASE_URL ?>' + '/public/api/locations.php',
                '/api/locations.php',
                'api/locations.php'
            ];

            const $prov = document.getElementById('select-province');
            const $ward = document.getElementById('select-ward');

            // Input hidden lưu DB
            const $inputProv = document.getElementById('input-tinh-thanh');
            const $inputWard = document.getElementById('input-xa-phuong');
            
            // Giá trị cũ (khi sửa tin)
            const oldProvName = $inputProv.value;
            const oldWardName = $inputWard.value;

            function clearSelect(el, placeholder) {
                el.innerHTML = '';
                const o = document.createElement('option');
                o.value = '';
                o.disabled = true;
                o.selected = true;
                o.textContent = placeholder || '-- Chọn --';
                el.appendChild(o);
            }

            function populateProvinces(data) {
                clearSelect($prov, '-- Chọn Tỉnh / Thành --');
                Object.keys(data).forEach(slug => {
                    const opt = document.createElement('option');
                    // Lấy tên hiển thị
                    const name = data[slug].name_with_type || data[slug].name || slug;
                    
                    // QUAN TRỌNG: Gán TÊN vào value để lấy ngay khi change
                    opt.value = name; 
                    opt.textContent = name;
                    
                    // Lưu slug vào attribute data-slug để dùng logic tìm huyện/xã
                    opt.setAttribute('data-slug', slug);

                    // Nếu trùng tên cũ thì select luôn
                    if (name === oldProvName) {
                        opt.selected = true;
                        // Load xã luôn
                        populateWards(data, slug);
                    }

                    $prov.appendChild(opt);
                });
            }

            function populateWards(data, provSlug) {
                clearSelect($ward, '-- Chọn Xã / Phường (nếu có) --');
                // Nếu chưa có ward name cũ thì reset input hidden, nếu có thì giữ nguyên
                if(!oldWardName) $inputWard.value = '';

                if (!provSlug || !data[provSlug]) return;

                const districts = data[provSlug].districts || {};
                const allWards = [];
                Object.keys(districts).forEach(dslug => {
                    const wards = districts[dslug].wards || [];
                    wards.forEach(w => allWards.push(w));
                });

                const seen = new Set();
                allWards.forEach(w => {
                    const name = w.name_with_type || w.name || w.id || w;
                    if (seen.has(name)) return;
                    seen.add(name);

                    const opt = document.createElement('option');
                    opt.value = name;
                    opt.textContent = name;

                    if (name === oldWardName) {
                        opt.selected = true;
                    }

                    $ward.appendChild(opt);
                });
            }

            function tryFetch(index) {
                if (index >= urls.length) return console.warn('Locations API not found');
                fetch(urls[index]).then(r => {
                    if (!r.ok) throw new Error('fetch failed');
                    return r.json();
                }).then(data => {
                    if (!data || Object.keys(data).length === 0) return console.warn('Locations data empty');
                    window._locationsData = data;
                    populateProvinces(data);
                }).catch(() => tryFetch(index + 1));
            }

            // --- XỬ LÝ SỰ KIỆN ---

            // 1. Khi chọn Tỉnh
            $prov.addEventListener('change', function() {
                const selectedName = this.value;
                $inputProv.value = selectedName; // Lưu TÊN

                const selectedOption = this.options[this.selectedIndex];
                const slug = selectedOption.getAttribute('data-slug');

                if (slug) {
                    populateWards(window._locationsData || {}, slug);
                } else {
                    clearSelect($ward, '-- Chọn Xã / Phường (nếu có) --');
                }
            });

            // 2. Khi chọn Xã
            $ward.addEventListener('change', function() {
                const selectedName = this.value;
                $inputWard.value = selectedName; // Lưu TÊN
            });

            // Init
            tryFetch(0);

            // Toggle Mã số sổ
            const phapLySelect = document.getElementById('phap_ly_select');
            const maSoGroup = document.getElementById('ma-so-so-group');
            if (phapLySelect) {
                phapLySelect.addEventListener('change', function() {
                    if (this.value === 'co_so') {
                        maSoGroup.style.display = 'block';
                    } else {
                        maSoGroup.style.display = 'none';
                    }
                });
            }
        })();

        // Hàm preview ảnh
        function previewMediaSlot(slot, input) {
            if (!input) return;
            const maxFiles = 12;
            const newFiles = Array.from(input.files || []);
            const key = '_selectedMedia_' + slot;
            window[key] = newFiles; 

            const container = document.getElementById('media-preview-container-' + slot);
            if (!container) return;
            
            // Xóa ảnh cũ (nếu muốn thay thế hoàn toàn)
            // container.innerHTML = ''; 

            newFiles.forEach((file) => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'preview-item';

                    const mediaElement = (file.type && file.type.startsWith('video/')) ? document.createElement('video') : document.createElement('img');
                    mediaElement.src = e.target.result;
                    if (file.type && file.type.startsWith('video/')) mediaElement.controls = true;

                    wrapper.appendChild(mediaElement);
                    container.appendChild(wrapper);
                };
                reader.readAsDataURL(file);
            });
        }
    </script>
</body>

</html>