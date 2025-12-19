<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - Big Housing Land</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .login-page {
            background: linear-gradient(180deg, #0137AE 0%, #158EFF 33%, #000557 100%) !important;
        }

        .btn-login {
            background-color: #6ADBFD !important;
            /* Màu xanh đậm nổi bật */
            color: #013354 !important;
        }

        .login-options {
            align-items: center;
            /* Căn giữa theo chiều dọc cho cả hàng */
        }

        .login-options label {
            display: flex;
            align-items: center;
            /* Căn giữa checkbox và chữ */
            gap: 5px;
            /* Khoảng cách giữa checkbox và chữ */
        }
    </style>
</head>

<body>
    <div class="app-container login-page">
        <div class="login-header">
            <div class="logo-icon">
                <img src="images/Logo.png" alt="logo">
            </div>
            <div class="logo-login">
                <img src="images/toanha1.png" alt="login">
            </div>
            <div class="logo-login">
                <img src="images/toanha2.png" alt="login">
            </div>
        </div>

        <form action="<?= BASE_URL ?>/login" method="POST" class="login-form">

            <div class="input-group">
                <i class="fa-solid fa-user"></i>
                <input type="text" class="input-field" name="identity" placeholder="Số điện thoại hoặc CCCD">
            </div>

            <div class="input-group">
                <i class="fa-solid fa-lock"></i>
                <input type="password" class="input-field" name="password" placeholder="Mật khẩu">
                <i class="fa-regular fa-eye-slash toggle-password"></i>
            </div>
            <div class="login-options">
                <label><input type="checkbox"> Lưu mật khẩu</label>
                <a href="<?= BASE_URL ?>/forgot-password">Quên mật khẩu?</a>
            </div>
            <button type="submit" class="btn-login">Đăng nhập</button>
            <div class="register-link-wrapper">
                <a href="<?= BASE_URL ?>/register" class="register-link">Đăng ký tài khoản</a>
            </div>
        </form>

        <div class="city-bg"></div>
    </div>

    <script>
        const togglePassword = document.querySelector('.toggle-password');
        const passwordInput = togglePassword.previousElementSibling; // Lấy ô input ngay trước icon

        togglePassword.addEventListener('click', function() {
            // Chuyển đổi thuộc tính type giữa 'password' và 'text'
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);

            // Đổi icon từ mắt gạch chéo sang mắt mở và ngược lại
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    </script>
</body>

</html>