<?php
/**
 * Authentication Functions
 * Xử lý đăng nhập, đăng ký, xác thực người dùng
 * 
 * @author System Development Team
 * @version 1.0
 */

require_once __DIR__ . '/db.php';

/**
 * Đăng nhập người dùng
 * @param string $username Tên đăng nhập hoặc email
 * @param string $password Mật khẩu
 * @return array|false Thông tin người dùng hoặc false nếu đăng nhập thất bại
 */
function loginUser($username, $password) {
    // Tìm người dùng theo tên đăng nhập hoặc email
    $sql = "SELECT nd.*, vt.TenVaiTro, kpb.TenKhoa 
            FROM NguoiDung nd
            LEFT JOIN VaiTro vt ON nd.MaVaiTro = vt.MaVaiTro
            LEFT JOIN KhoaPhongBan kpb ON nd.MaKhoa = kpb.MaKhoa
            WHERE (nd.TenDangNhap = ? OR nd.Email = ?)
            AND nd.IsDeleted = 0
            AND nd.HoatDong = 1";
    
    $user = dbQueryOne($sql, [$username, $username]);
    
    if (!$user) {
        return false;
    }
    
    // Kiểm tra mật khẩu
    // Nếu mật khẩu đã được hash bằng password_hash, sử dụng password_verify
    // Nếu mật khẩu chưa hash (plain text), so sánh trực tiếp (để tương thích với dữ liệu cũ)
    $passwordValid = false;
    
    if (password_verify($password, $user['MatKhau'])) {
        // Mật khẩu đã được hash
        $passwordValid = true;
    } elseif ($user['MatKhau'] === md5($password)) {
        // Mật khẩu hash bằng MD5 (tương thích với dữ liệu cũ)
        $passwordValid = true;
    } elseif ($user['MatKhau'] === $password) {
        // Mật khẩu plain text (chỉ để test, không nên dùng trong production)
        $passwordValid = true;
    }
    
    if (!$passwordValid) {
        return false;
    }
    
    return $user;
}

/**
 * Đăng ký người dùng mới
 * @param array $userData Dữ liệu người dùng
 * @return array Kết quả đăng ký ['success' => bool, 'message' => string, 'user' => array|null]
 */
function registerUser($userData) {
    // Validate dữ liệu
    $errors = [];
    
    if (empty($userData['TenDangNhap'])) {
        $errors[] = 'Tên đăng nhập không được để trống';
    }
    
    if (empty($userData['MatKhau'])) {
        $errors[] = 'Mật khẩu không được để trống';
    } elseif (strlen($userData['MatKhau']) < 6) {
        $errors[] = 'Mật khẩu phải có ít nhất 6 ký tự';
    }
    
    if (empty($userData['HoTen'])) {
        $errors[] = 'Họ tên không được để trống';
    }
    
    if (!empty($userData['Email']) && !filter_var($userData['Email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email không hợp lệ';
    }
    
    if (!empty($errors)) {
        return [
            'success' => false,
            'message' => implode(', ', $errors),
            'user' => null
        ];
    }
    
    // Kiểm tra tên đăng nhập đã tồn tại chưa
    $checkUsername = dbQueryOne(
        "SELECT MaNguoiDung FROM NguoiDung WHERE TenDangNhap = ? AND IsDeleted = 0",
        [$userData['TenDangNhap']]
    );
    
    if ($checkUsername) {
        return [
            'success' => false,
            'message' => 'Tên đăng nhập đã tồn tại',
            'user' => null
        ];
    }
    
    // Kiểm tra email đã tồn tại chưa (nếu có)
    if (!empty($userData['Email'])) {
        $checkEmail = dbQueryOne(
            "SELECT MaNguoiDung FROM NguoiDung WHERE Email = ? AND IsDeleted = 0",
            [$userData['Email']]
        );
        
        if ($checkEmail) {
            return [
                'success' => false,
                'message' => 'Email đã được sử dụng',
                'user' => null
            ];
        }
    }
    
    // Hash mật khẩu
    $hashedPassword = password_hash($userData['MatKhau'], PASSWORD_DEFAULT);
    
    // Phân vai trò tự động dựa trên thông tin đăng ký
    // 1. Nếu có mã sinh viên → Sinh viên (MaVaiTro = 3)
    // 2. Nếu không có mã sinh viên nhưng có khoa → Giảng viên (MaVaiTro = 2)
    // 3. Admin không đăng ký qua form thông thường
    if (isset($userData['MaVaiTro']) && !empty($userData['MaVaiTro'])) {
        // Nếu đã được chỉ định vai trò từ bên ngoài (ví dụ: admin tạo tài khoản)
        $maVaiTro = (int)$userData['MaVaiTro'];
    } elseif (!empty($userData['MaSinhVien'])) {
        // Có mã sinh viên → Sinh viên
        $maVaiTro = 3;
    } elseif (!empty($userData['MaKhoa'])) {
        // Không có mã sinh viên nhưng có khoa → Giảng viên
        $maVaiTro = 2;
    } else {
        // Mặc định: Sinh viên (nếu không có thông tin gì)
        $maVaiTro = 3;
    }
    
    // Tạo mã người dùng tự động với retry logic để tránh trùng
    $maNguoiDung = null;
    $maxRetries = 20;
    $retryCount = 0;
    $startNumber = null;
    
    while ($retryCount < $maxRetries) {
        $maNguoiDung = generateUserCode($startNumber);
        
        // Kiểm tra mã đã tồn tại chưa (kể cả đã xóa)
        $existing = dbQueryOne(
            "SELECT MaNguoiDung FROM NguoiDung WHERE MaNguoiDung = ?",
            [$maNguoiDung]
        );
        
        if (!$existing) {
            // Mã chưa tồn tại, có thể sử dụng
            break;
        }
        
        // Mã đã tồn tại, tăng số và thử lại
        $retryCount++;
        if ($startNumber === null) {
            // Lấy số từ mã vừa tạo
            $startNumber = (int) preg_replace('/[^0-9]/', '', $maNguoiDung);
        }
        $startNumber++; // Tăng lên 1 và thử lại
        usleep(50000); // Đợi 0.05 giây để tránh race condition
    }
    
    if ($retryCount >= $maxRetries) {
        return [
            'success' => false,
            'message' => 'Không thể tạo mã người dùng. Vui lòng thử lại sau.',
            'user' => null
        ];
    }
    
    // Insert vào database
    $sql = "INSERT INTO NguoiDung (
        MaNguoiDung, TenDangNhap, MatKhau, HoTen, Email, SoDienThoai, 
        MaVaiTro, MaKhoa, MaSinhVien, HoatDong, NgayTao
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())";
    
    $result = dbExecute($sql, [
        $maNguoiDung,
        $userData['TenDangNhap'],
        $hashedPassword,
        $userData['HoTen'],
        $userData['Email'] ?? null,
        $userData['SoDienThoai'] ?? null,
        $maVaiTro,
        $userData['MaKhoa'] ?? null,
        $userData['MaSinhVien'] ?? null
    ]);
    
    if ($result) {
        // Lấy thông tin người dùng vừa đăng ký
        $newUser = dbQueryOne(
            "SELECT nd.*, vt.TenVaiTro, kpb.TenKhoa 
             FROM NguoiDung nd
             LEFT JOIN VaiTro vt ON nd.MaVaiTro = vt.MaVaiTro
             LEFT JOIN KhoaPhongBan kpb ON nd.MaKhoa = kpb.MaKhoa
             WHERE nd.MaNguoiDung = ?",
            [$maNguoiDung]
        );
        
        return [
            'success' => true,
            'message' => 'Đăng ký thành công',
            'user' => $newUser
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Có lỗi xảy ra khi đăng ký. Vui lòng thử lại.',
            'user' => null
        ];
    }
}

/**
 * Tạo mã người dùng tự động
 * @param int $startNumber Số bắt đầu (dùng khi retry)
 * @return string Mã người dùng mới
 */
function generateUserCode($startNumber = null) {
    if ($startNumber === null) {
        // Lấy mã người dùng lớn nhất (kể cả đã xóa để tránh trùng)
        $lastUser = dbQueryOne(
            "SELECT MaNguoiDung FROM NguoiDung ORDER BY MaNguoiDung DESC LIMIT 1"
        );
        
        if ($lastUser) {
            // Tách số từ mã cũ và tăng lên 1
            $lastNumber = (int) preg_replace('/[^0-9]/', '', $lastUser['MaNguoiDung']);
            $startNumber = $lastNumber + 1;
        } else {
            // Nếu chưa có người dùng nào, bắt đầu từ 1
            $startNumber = 1;
        }
    }
    
    return 'ND' . str_pad($startNumber, 8, '0', STR_PAD_LEFT);
}

/**
 * Lấy thông tin người dùng hiện tại từ session
 * @return array|false Thông tin người dùng hoặc false
 */
function getCurrentUser() {
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    
    $sql = "SELECT nd.*, vt.TenVaiTro, kpb.TenKhoa 
            FROM NguoiDung nd
            LEFT JOIN VaiTro vt ON nd.MaVaiTro = vt.MaVaiTro
            LEFT JOIN KhoaPhongBan kpb ON nd.MaKhoa = kpb.MaKhoa
            WHERE nd.MaNguoiDung = ?
            AND nd.IsDeleted = 0
            AND nd.HoatDong = 1";
    
    return dbQueryOne($sql, [$_SESSION['user_id']]);
}

/**
 * Kiểm tra người dùng đã đăng nhập chưa
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Đăng xuất người dùng
 */
function logoutUser() {
    session_unset();
    session_destroy();
    session_start();
    session_regenerate_id(true);
}

