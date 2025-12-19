<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên mật khẩu</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <div class="app-container register-page">

        <header class="register-header">
            <a href="<?= BASE_URL ?>/login" class="back-btn"><i class="fa-solid fa-chevron-left"></i></a>
            <h3>Quên mật khẩu</h3>
        </header>

        <div style="padding: 20px;">
            <form action="login.html">

                <div class="forgot-input-group">
                    <i class="fa-solid fa-envelope"></i>
                    <input type="email" placeholder="Nhập email" required>
                </div>

                <button class="btn-save" style="margin: 0; width: 100%;">Gửi</button>

                <a href="<?= BASE_URL ?>/login" class="back-login-link">
                    <i class="fa-solid fa-chevron-left"></i> Quay lại đăng nhập
                </a>

            </form>
        </div>

    </div>
</body>

</html>