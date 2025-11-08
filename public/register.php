<?php
/**
 * Trang đăng ký
 * 
 * @author System Development Team
 * @version 1.0
 */

session_start();

// Nếu đã đăng nhập, chuyển đến dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/user.php';

$error = '';
$success = '';
$khoaList = getKhoaPhongBan();

// Xử lý đăng ký
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $userData = [
        'TenDangNhap' => trim($_POST['username'] ?? ''),
        'MatKhau' => $_POST['password'] ?? '',
        'HoTen' => trim($_POST['name'] ?? ''),
        'Email' => trim($_POST['email'] ?? ''),
        'SoDienThoai' => trim($_POST['phone'] ?? ''),
        'MaKhoa' => !empty($_POST['makhoa']) ? (int)$_POST['makhoa'] : null,
        'MaSinhVien' => trim($_POST['masinhvien'] ?? ''),
        'MaVaiTro' => 2 // Mặc định là Sinh viên
    ];
    
    // Kiểm tra xác nhận mật khẩu
    if ($userData['MatKhau'] !== ($_POST['password_confirm'] ?? '')) {
        $error = 'Mật khẩu xác nhận không khớp';
    } else {
        $result = registerUser($userData);
        
        if ($result['success']) {
            $success = $result['message'];
            // Đăng nhập tự động sau khi đăng ký
            $_SESSION['user_id'] = $result['user']['MaNguoiDung'];
            $_SESSION['user_name'] = $result['user']['HoTen'];
            $_SESSION['user_username'] = $result['user']['TenDangNhap'];
            $_SESSION['user_email'] = $result['user']['Email'];
            $_SESSION['user_role'] = $result['user']['TenVaiTro'];
            $_SESSION['user_role_id'] = $result['user']['MaVaiTro'];
            
            // Chuyển đến dashboard sau 2 giây
            header('refresh:2;url=dashboard.php');
        } else {
            $error = $result['message'];
        }
    }
}
?>
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
            
            <?php if ($error): ?>
                <div class="alert alert-error" style="background: #f8d7da; color: #721c24; padding: 1rem; border-radius: 5px; margin-bottom: 1rem;">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success" style="background: #d4edda; color: #155724; padding: 1rem; border-radius: 5px; margin-bottom: 1rem;">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?> Đang chuyển đến trang chủ...
                </div>
            <?php endif; ?>
            
            <form class="auth-form" method="post" action="">
                <input type="hidden" name="register" value="1">
                <div class="form-group">
                    <label for="name">Họ và tên <span style="color: red;">*</span></label>
                    <input id="name" name="name" type="text" placeholder="Nguyễn Văn A" 
                           value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label for="username">Tên đăng nhập <span style="color: red;">*</span></label>
                    <input id="username" name="username" type="text" placeholder="Tên đăng nhập" 
                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email <span style="color: red;">*</span></label>
                    <input id="email" name="email" type="email" placeholder="you@tvu.edu.vn" 
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label for="phone">Số điện thoại</label>
                    <input id="phone" name="phone" type="tel" placeholder="0987654321" 
                           value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="masinhvien">Mã sinh viên</label>
                    <input id="masinhvien" name="masinhvien" type="text" placeholder="SV001234" 
                           value="<?php echo isset($_POST['masinhvien']) ? htmlspecialchars($_POST['masinhvien']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="makhoa">Khoa/Phòng ban</label>
                    <select id="makhoa" name="makhoa">
                        <option value="">-- Chọn khoa --</option>
                        <?php foreach ($khoaList as $khoa): ?>
                            <option value="<?php echo $khoa['MaKhoa']; ?>" 
                                    <?php echo (isset($_POST['makhoa']) && $_POST['makhoa'] == $khoa['MaKhoa']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($khoa['TenKhoa']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="password">Mật khẩu <span style="color: red;">*</span></label>
                    <input id="password" name="password" type="password" placeholder="Mật khẩu (tối thiểu 6 ký tự)" required>
                </div>
                <div class="form-group">
                    <label for="password_confirm">Xác nhận mật khẩu <span style="color: red;">*</span></label>
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
