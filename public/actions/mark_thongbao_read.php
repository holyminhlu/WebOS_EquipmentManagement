<?php
/**
 * Mark one notification as read
 * POST: maThongBao
 * Output: JSON { success, message }
 */

session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method không hợp lệ']);
    exit;
}

$maThongBao = isset($_POST['maThongBao']) ? trim((string)$_POST['maThongBao']) : '';
if ($maThongBao === '') {
    echo json_encode(['success' => false, 'message' => 'Mã thông báo không hợp lệ']);
    exit;
}

try {
    $affected = dbExecute(
        "UPDATE `thongbao`
         SET DaDoc = 1
         WHERE MaThongBao = ? AND MaNguoiDung = ? AND IsDeleted = 0",
        [$maThongBao, (string)$_SESSION['user_id']]
    );

    if ($affected === false) {
        echo json_encode(['success' => false, 'message' => 'Không thể cập nhật thông báo']);
        exit;
    }

    echo json_encode(['success' => true, 'message' => 'Đã cập nhật trạng thái đã đọc']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
}
