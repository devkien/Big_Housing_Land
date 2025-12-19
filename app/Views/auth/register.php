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

        <header class="register-header">
            <a href="<?= BASE_URL ?>/login" class="back-btn"><i class="fa-solid fa-chevron-left"></i></a>
            <h3>Đăng ký</h3>
            <div style="width: 18px;"></div>
        </header>

        <form action="<?= BASE_URL ?>/register" method="POST">

            <div class="section-label">1. Thông tin đăng nhập</div>

            <div class="reg-box-input">
                <i class="fa-solid fa-user"></i>
                <input type="number" name="so_dien_thoai" placeholder="Nhập số điện thoại">
            </div>
            <div class="char-count">0/10</div>

            <div class="reg-box-input">
                <i class="fa-solid fa-lock" style="font-size: 12px;"></i> <input type="password" name="password" placeholder="Vui lòng nhập mật khẩu">
            </div>
            <div class="char-count">0/10</div>

            <div class="section-label">2. Thông tin cơ bản</div>

            <div class="reg-box-input">
                <i class="fa-solid fa-location-arrow"></i> <input type="text" name="ho_ten" placeholder="Nhập họ và tên">
            </div>
            <div class="char-count">0/50</div>

            <label class="reg-box-input">
                <i class="fa-regular fa-calendar"></i>
                <input type="number" name="nam_sinh" placeholder="Nhập năm sinh" min="1900" max="2025">
            </label>
            <div style="height: 10px;"></div>
            <div class="reg-box-input">
                <i class="fa-solid fa-location-dot icon-location"></i>
                <input type="text" name="dia_chi" placeholder="Địa chỉ">
            </div>
            <div class="char-count">0/50</div>
            <div class="reg-line-select">
                <i class="icon-left fa-solid fa-transgender"></i>
                <select name="gioi_tinh">
                    <option value="" disabled selected>Chọn giới tính</option>
                    <option value="nam">Nam</option>
                    <option value="nu">Nữ</option>
                    <option value="nu">Khác</option>
                </select>
                <i class="arrow-right fa-solid fa-chevron-down"></i>
            </div>

            <div class="reg-box-input">
                <i class="fa-solid fa-envelope"></i>
                <input type="email" name="email" placeholder="Nhập email">
            </div>

            <div class="reg-box-input">
                <i class="fa-brands fa-facebook"></i>
                <input type="text" name="link_fb" placeholder="Nhập link fb">
            </div>
            <div class="reg-box-input">
                <i class="fa-solid fa-user icon-user"></i>
                <input type="text" name="ma_gioi_thieu" placeholder="Mã giới thiệu">
            </div>


            <div class="section-label">3. Thông tin Big Housing</div>

            <div class="reg-line-select">
                <i class="icon-left fa-solid fa-book"></i>
                <select name="loai_tai_khoan">
                    <option value="" disabled selected>Chọn loại tài khoản</option>
                    <option value="nhan_vien">Cộng tác viên</option>
                    <option value="quan_ly">Đầu chủ</option>
                </select>
                <i class="arrow-right fa-solid fa-chevron-down"></i>
            </div>

            <div class="reg-line-select">
                <i class="icon-left fa-solid fa-fire"></i>
                <select name="phong_ban">
                    <option value="" disabled selected>Chọn phòng ban</option>
                    <option value="kd1">Thiện Chiến</option>
                    <option value="kd2">Hùng Phát</option>
                    <option value="kd3">Tinh Nhuệ</option>
                </select>
                <i class="arrow-right fa-solid fa-chevron-down"></i>
            </div>

            <button class="btn-save">Đăng ký</button>

        </form>
    </div>
</body>

</html>