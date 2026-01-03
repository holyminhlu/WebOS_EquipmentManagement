<?php
/**
 * System admin: lock/unlock user/admin account (HoatDong)
 */

require_once __DIR__ . '/_guard.php';
require_once __DIR__ . '/../../../includes/db.php';

$maNguoiDung = isset($_POST['maNguoiDung']) ? trim((string)$_POST['maNguoiDung']) : '';
$activeRaw = isset($_POST['active']) ? trim((string)$_POST['active']) : '';
$active = ($activeRaw === '0') ? 0 : 1;

if ($maNguoiDung === '') {
    echo json_encode(['success' => false, 'message' => 'Thiếu mã người dùng']);
    exit;
}

try {
    $ok = dbExecute(
        "UPDATE `nguoidung` SET HoatDong = ? WHERE MaNguoiDung = ? AND IsDeleted = 0",
        [$active, $maNguoiDung]
    );

    if ($ok === false) {
        echo json_encode(['success' => false, 'message' => 'Không thể cập nhật trạng thái tài khoản']);
        exit;
    }

    echo json_encode(['success' => true, 'message' => $active ? 'Đã mở khóa tài khoản' : 'Đã khóa tài khoản']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
}
