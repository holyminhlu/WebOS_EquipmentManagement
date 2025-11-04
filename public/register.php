<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký - Hệ thống mượn trả thiết bị</title>
    <link rel="stylesheet" href="css/styleRegister.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <header class="header">
        <nav class="navbar">
            <div class="nav-container">
                <div class="nav-logo">
                    <a href="index.php"><img src="images/tvu-logo.png" alt="TVU Logo" class="logo"></a>
                    <div class="system-name">
                        <h1>HỆ THỐNG MƯỢN TRẢ THIẾT BỊ</h1>
                        <span>Trường Đại học Trà Vinh</span>
                    </div>
                </div>
                <div class="nav-auth">
                    <a href="login.php" class="btn-login"><i class="fas fa-sign-in-alt"></i> Đăng nhập</a>
                    <a href="register.php" class="btn-register"><i class="fas fa-user-plus"></i> Đăng ký</a>
                </div>
            </div>
        </nav>
    </header>

    <main class="auth-page">
        <div class="auth-card">
            <h2>Đăng ký tài khoản</h2>
            <form class="auth-form" action="#" method="post">
                <div class="form-group">
                    <label for="name">Họ và tên</label>
                    <input id="name" name="name" type="text" placeholder="Nguyễn Văn A" required>
                </div>
                <div class="form-group">
                    <label for="email">Email trường</label>
                    <input id="email" name="email" type="email" placeholder="you@tvu.edu.vn" required>
                </div>
                <div class="form-group">
                    <label for="password">Mật khẩu</label>
                    <input id="password" name="password" type="password" placeholder="Mật khẩu" required>
                </div>
                <div class="form-group">
                    <label for="password_confirm">Xác nhận mật khẩu</label>
                    <input id="password_confirm" name="password_confirm" type="password" placeholder="Nhập lại mật khẩu" required>
                </div>
                <div class="auth-actions">
                    <button type="submit" class="btn-primary">Đăng ký</button>
                    <a href="index.php" class="btn-secondary">Hủy</a>
                </div>
                <div class="auth-note">
                    <p>Đã có tài khoản? <a href="login.php">Đăng nhập</a></p>
                </div>
            </form>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <div class="footer-bottom">
                <p>&copy; 2024 Trường Đại học Trà Vinh. Tất cả các quyền được bảo lưu.</p>
            </div>
        </div>
    </footer>
</body>
</html>
