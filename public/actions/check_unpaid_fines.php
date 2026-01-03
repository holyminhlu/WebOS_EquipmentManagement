<?php
/**
 * Check unpaid fines (DaThanhToan=0) for current user
 */

session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/user.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Vui lòng đăng nhập để thực hiện chức năng này'
    ]);
    exit;
}

$hasUnpaid = userHasUnpaidPhieuPhat($_SESSION['user_id']);

echo json_encode([
    'success' => true,
    'hasUnpaid' => $hasUnpaid ? 1 : 0
]);
