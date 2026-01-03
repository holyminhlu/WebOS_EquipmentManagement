<?php
/**
 * System admin: create/update user/admin accounts
 */

require_once __DIR__ . '/_guard.php';
require_once __DIR__ . '/../../../includes/auth.php';
require_once __DIR__ . '/../../../includes/db.php';
require_once __DIR__ . '/../../../includes/audit.php';

$type = isset($_POST['type']) ? trim((string)$_POST['type']) : 'user';
$maNguoiDung = isset($_POST['maNguoiDung']) ? trim((string)$_POST['maNguoiDung']) : '';
$hoTen = isset($_POST['hoTen']) ? trim((string)$_POST['hoTen']) : '';
$tenDangNhap = isset($_POST['tenDangNhap']) ? trim((string)$_POST['tenDangNhap']) : '';
$email = isset($_POST['email']) ? trim((string)$_POST['email']) : '';
$soDienThoai = isset($_POST['soDienThoai']) ? trim((string)$_POST['soDienThoai']) : '';
$maVaiTro = isset($_POST['maVaiTro']) ? (int)$_POST['maVaiTro'] : 0;
$maKhoaRaw = isset($_POST['maKhoa']) ? trim((string)$_POST['maKhoa']) : '';
$maKhoa = ($maKhoaRaw !== '' && ctype_digit($maKhoaRaw)) ? (int)$maKhoaRaw : null;
$maSinhVien = isset($_POST['maSinhVien']) ? trim((string)$_POST['maSinhVien']) : '';
$matKhau = isset($_POST['matKhau']) ? (string)$_POST['matKhau'] : '';
$hoatDong = isset($_POST['hoatDong']) ? (int)$_POST['hoatDong'] : 1;

if ($type === 'admin') {
    $maVaiTro = 1;
}

if ($hoTen === '' || $tenDangNhap === '' || $maVaiTro === 0) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng nhập đầy đủ thông tin bắt buộc']);
    exit;
}

if (!in_array($maVaiTro, [1, 2, 3], true)) {
    echo json_encode(['success' => false, 'message' => 'Vai trò không hợp lệ']);
    exit;
}

if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Email không hợp lệ']);
    exit;
}

$hoatDong = ($hoatDong === 0) ? 0 : 1;

try {
    if ($maNguoiDung === '') {
        // Create
        if (trim($matKhau) === '') {
            echo json_encode(['success' => false, 'message' => 'Vui lòng nhập mật khẩu để tạo tài khoản']);
            exit;
        }

        $res = registerUser([
            'TenDangNhap' => $tenDangNhap,
            'MatKhau' => $matKhau,
            'HoTen' => $hoTen,
            'Email' => $email !== '' ? $email : null,
            'SoDienThoai' => $soDienThoai !== '' ? $soDienThoai : null,
            'MaVaiTro' => $maVaiTro,
            'MaKhoa' => $maKhoa,
            'MaSinhVien' => $maSinhVien !== '' ? $maSinhVien : null,
        ]);

        if (!$res['success']) {
            echo json_encode(['success' => false, 'message' => $res['message'] ?? 'Không thể tạo tài khoản']);
            exit;
        }

        // Set active flag if needed
        if ($hoatDong === 0 && isset($res['user']['MaNguoiDung'])) {
            dbExecute("UPDATE `nguoidung` SET HoatDong = 0 WHERE MaNguoiDung = ? AND IsDeleted = 0", [(string)$res['user']['MaNguoiDung']]);
        }

        if (isset($res['user']['MaNguoiDung'])) {
            $newId = (string)$res['user']['MaNguoiDung'];
            $after = dbQueryOne("SELECT * FROM `nguoidung` WHERE MaNguoiDung = ? LIMIT 1", [$newId]);
            auditLog('NguoiDung', $newId, 'CREATE', null, $after);
        }

        echo json_encode(['success' => true, 'message' => 'Tạo tài khoản thành công']);
        exit;
    }

    // Update
    $existing = dbQueryOne(
        "SELECT * FROM `nguoidung` WHERE MaNguoiDung = ? AND IsDeleted = 0 LIMIT 1",
        [$maNguoiDung]
    );
    if (!$existing) {
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy tài khoản']);
        exit;
    }

    // Prevent changing role outside allowed set
    if (!in_array($maVaiTro, [1, 2, 3], true)) {
        echo json_encode(['success' => false, 'message' => 'Vai trò không hợp lệ']);
        exit;
    }

    // Unique checks
    $dupUser = dbQueryOne(
        "SELECT MaNguoiDung FROM `nguoidung` WHERE TenDangNhap = ? AND IsDeleted = 0 AND MaNguoiDung <> ? LIMIT 1",
        [$tenDangNhap, $maNguoiDung]
    );
    if ($dupUser) {
        echo json_encode(['success' => false, 'message' => 'Tên đăng nhập đã tồn tại']);
        exit;
    }

    if ($email !== '') {
        $dupEmail = dbQueryOne(
            "SELECT MaNguoiDung FROM `nguoidung` WHERE Email = ? AND IsDeleted = 0 AND MaNguoiDung <> ? LIMIT 1",
            [$email, $maNguoiDung]
        );
        if ($dupEmail) {
            echo json_encode(['success' => false, 'message' => 'Email đã được sử dụng']);
            exit;
        }
    }

    $params = [];
    $sets = [];

    $sets[] = 'HoTen = ?';
    $params[] = $hoTen;

    $sets[] = 'TenDangNhap = ?';
    $params[] = $tenDangNhap;

    $sets[] = 'Email = ?';
    $params[] = ($email !== '') ? $email : null;

    $sets[] = 'SoDienThoai = ?';
    $params[] = ($soDienThoai !== '') ? $soDienThoai : null;

    $sets[] = 'MaVaiTro = ?';
    $params[] = $maVaiTro;

    $sets[] = 'MaKhoa = ?';
    $params[] = $maKhoa;

    $sets[] = 'MaSinhVien = ?';
    $params[] = ($maSinhVien !== '') ? $maSinhVien : null;

    $sets[] = 'HoatDong = ?';
    $params[] = $hoatDong;

    if (trim($matKhau) !== '') {
        $sets[] = 'MatKhau = ?';
        $params[] = password_hash($matKhau, PASSWORD_DEFAULT);
    }

    $params[] = $maNguoiDung;

    $ok = dbExecute(
        "UPDATE `nguoidung` SET " . implode(', ', $sets) . " WHERE MaNguoiDung = ? AND IsDeleted = 0",
        $params
    );

    if ($ok === false) {
        echo json_encode(['success' => false, 'message' => 'Không thể cập nhật tài khoản']);
        exit;
    }

    $after = dbQueryOne("SELECT * FROM `nguoidung` WHERE MaNguoiDung = ? AND IsDeleted = 0 LIMIT 1", [$maNguoiDung]);
    auditLog('NguoiDung', $maNguoiDung, 'UPDATE', $existing, $after);

    echo json_encode(['success' => true, 'message' => 'Cập nhật tài khoản thành công']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
}

