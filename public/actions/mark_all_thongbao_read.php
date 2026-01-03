<?php
/**
 * Mark all notifications as read for current user
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

try {
    dbExecute(
        "UPDATE `thongbao`
         SET DaDoc = 1
         WHERE MaNguoiDung = ? AND IsDeleted = 0 AND DaDoc = 0",
        [(string)$_SESSION['user_id']]
    );

    echo json_encode(['success' => true, 'message' => 'Đã đánh dấu đã đọc tất cả thông báo']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
}
