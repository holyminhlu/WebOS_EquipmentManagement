<?php
/**
 * System admin: soft delete device
 */

require_once __DIR__ . '/_guard.php';
require_once __DIR__ . '/../../../includes/db.php';

$maThietBi = isset($_POST['maThietBi']) ? trim((string)$_POST['maThietBi']) : '';
if ($maThietBi === '') {
    echo json_encode(['success' => false, 'message' => 'Thiếu mã thiết bị']);
    exit;
}

try {
    $ok = dbExecute(
        "UPDATE `thietbi`
         SET IsDeleted = 1, DeletedAt = NOW(), DeletedBy = ?
         WHERE MaThietBi = ? AND IsDeleted = 0",
        [$_SESSION['user_id'], $maThietBi]
    );

    if ($ok === false || (int)$ok <= 0) {
        echo json_encode(['success' => false, 'message' => 'Không thể xóa thiết bị (có thể không tồn tại)']);
        exit;
    }

    echo json_encode(['success' => true, 'message' => 'Đã xóa thiết bị']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
}
