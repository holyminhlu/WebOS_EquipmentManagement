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
    
    // Tạo mã người dùng tự động
    $maNguoiDung = generateUserCode();
    
    // Hash mật khẩu
    $hashedPassword = password_hash($userData['MatKhau'], PASSWORD_DEFAULT);
    
    // Mã vai trò mặc định: 2 = Sinh viên (cần điều chỉnh theo database của bạn)
    $maVaiTro = isset($userData['MaVaiTro']) ? $userData['MaVaiTro'] : 2;
    
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
 * @return string Mã người dùng mới
 */
function generateUserCode() {
    // Lấy mã người dùng lớn nhất
    $lastUser = dbQueryOne(
        "SELECT MaNguoiDung FROM NguoiDung ORDER BY MaNguoiDung DESC LIMIT 1"
    );
    
    if ($lastUser) {
        // Tách số từ mã cũ và tăng lên 1
        $lastNumber = (int) preg_replace('/[^0-9]/', '', $lastUser['MaNguoiDung']);
        $newNumber = $lastNumber + 1;
        return 'ND' . str_pad($newNumber, 8, '0', STR_PAD_LEFT);
    } else {
        // Nếu chưa có người dùng nào, bắt đầu từ ND00000001
        return 'ND00000001';
    }
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

