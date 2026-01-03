<?php

session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../../includes/user.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
    exit;
}

$user = getUserInfo($_SESSION['user_id']);
if (!$user || !isset($user['MaVaiTro']) || (int)$user['MaVaiTro'] !== 1101) {
    echo json_encode(['success' => false, 'message' => 'Bạn không có quyền thực hiện chức năng này']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method không hợp lệ']);
    exit;
}
