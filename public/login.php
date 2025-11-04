<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - Hệ thống mượn trả thiết bị</title>
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
            <h2>Đăng nhập</h2>
            <form class="auth-form" action="#" method="post">
                <div class="form-group">
                    <label for="email">Email hoặc tên đăng nhập</label>
                    <input id="email" name="email" type="text" placeholder="you@tvu.edu.vn" required>
                </div>
                <div class="form-group">
                    <label for="password">Mật khẩu</label>
                    <input id="password" name="password" type="password" placeholder="Mật khẩu" required>
                </div>
                <div class="form-group">
                    <label><input type="checkbox" name="remember"> Ghi nhớ đăng nhập</label>
                </div>
                <div class="auth-actions">
                    <button type="submit" class="btn-primary">Đăng nhập</button>
                    <a href="index.php" class="btn-secondary">Hủy</a>
                </div>
                <div class="auth-note">
                    <p>Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a></p>
                    <p><a href="#">Quên mật khẩu?</a></p>
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
