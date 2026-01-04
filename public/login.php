<?php
/**
 * Trang đăng nhập
 * 
 * @author System Development Team
 * @version 1.0
 */

session_start();

// Nếu đã đăng nhập, chuyển đến dashboard
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['user_role_id']) && (int)$_SESSION['user_role_id'] === 1101) {
        header('Location: system_admin.php');
    } else {
        header('Location: dashboard.php');
    }
    exit;
}

require_once __DIR__ . '/../includes/auth.php';

$error = '';
$success = '';

// Xử lý đăng nhập
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    if (empty($username) || empty($password)) {
        $error = 'Vui lòng nhập đầy đủ thông tin đăng nhập';
    } else {
        $user = loginUser($username, $password);
        
        if ($user) {
            // Lưu thông tin vào session
            $_SESSION['user_id'] = $user['MaNguoiDung'];
            $_SESSION['user_name'] = $user['HoTen'];
            $_SESSION['user_username'] = $user['TenDangNhap'];
            $_SESSION['user_email'] = $user['Email'];
            $_SESSION['user_role'] = $user['TenVaiTro'];
            $_SESSION['user_role_id'] = $user['MaVaiTro'];
            
            // Nếu chọn ghi nhớ đăng nhập
            if ($remember) {
                // Set cookie (30 ngày)
                setcookie('remember_user', $user['MaNguoiDung'], time() + (30 * 24 * 60 * 60), '/');
            }
            
            // Chuyển đến đúng giao diện theo quyền
            if ((int)$_SESSION['user_role_id'] === 1101) {
                header('Location: system_admin.php');
            } else {
                header('Location: dashboard.php');
            }
            exit;
        } else {
            $error = 'Tên đăng nhập/Email hoặc mật khẩu không đúng';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - Hệ thống mượn trả thiết bị</title>
    <link rel="stylesheet" href="css/styleRegister.css?v=<?php echo time(); ?>">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <header class="header" role="banner">
        <nav class="navbar" role="navigation" aria-label="Điều hướng chính">
            <div class="nav-container">
                <div class="nav-logo">
                    <a href="index.php" aria-label="Trang chủ - Đại học Trà Vinh">
                        <img src="images/tvu-logo.png" alt="Logo Trường Đại học Trà Vinh" class="logo">
                    </a>
                    <div class="system-name">
                        <h1>HỆ THỐNG MƯỢN TRẢ THIẾT BỊ</h1>
                        <span>Trường Đại học Trà Vinh</span>
                    </div>
                </div>
                <div class="nav-menu" role="menubar">
                    <a href="index.php" class="nav-link" role="menuitem">Trang chủ</a>
                    <a href="about.php" class="nav-link" role="menuitem">Giới thiệu</a>
                    <a href="equipment.php" class="nav-link" role="menuitem">Thiết bị</a>
                    <a href="regulations.php" class="nav-link" role="menuitem">Quy định & Hướng dẫn</a>
                    <a href="contact.php" class="nav-link" role="menuitem">Liên hệ</a>
                </div>
                <div class="nav-auth">
                    <a href="login.php" class="btn-login" aria-label="Đăng nhập vào hệ thống">
                        <i class="fas fa-sign-in-alt" aria-hidden="true"></i> Đăng nhập
                    </a>
                    <a href="register.php" class="btn-register" aria-label="Đăng ký tài khoản mới">
                        <i class="fas fa-user-plus" aria-hidden="true"></i> Đăng ký
                    </a>
                </div>
            </div>
        </nav>
    </header>

    <main class="auth-page">
        <div class="auth-card">
            <h2>Đăng nhập</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-error" style="background: #f8d7da; color: #721c24; padding: 1rem; border-radius: 5px; margin-bottom: 1rem;">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success" style="background: #d4edda; color: #155724; padding: 1rem; border-radius: 5px; margin-bottom: 1rem;">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <form class="auth-form" method="post" action="">
                <input type="hidden" name="login" value="1">
                <div class="form-group">
                    <label for="username">Tên đăng nhập hoặc Email</label>
                    <input id="username" name="username" type="text" placeholder="Tên đăng nhập hoặc Email" 
                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label for="password">Mật khẩu</label>
                    <input id="password" name="password" type="password" placeholder="Mật khẩu" required>
                </div>
                <div class="form-group">
                    <label><input type="checkbox" name="remember" value="1"> Ghi nhớ đăng nhập</label>
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
