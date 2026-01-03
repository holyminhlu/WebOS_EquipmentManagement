<?php
/**
 * Đánh dấu phiếu mượn đã trả (admin only)
 * - Input: POST { maPhieu }
 * - Rule: nếu có phiếu phạt chưa thanh toán cho MaPhieu => không cho hoàn thành
 * - Update: PhieuMuon.NgayTraThucTe = NOW(), TrangThai = 'Hoàn thành'
 */

session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../includes/user.php';
require_once __DIR__ . '/../../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method không hợp lệ']);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
    exit;
}

$user = getUserInfo($_SESSION['user_id']);
$isAdmin = false;
if ($user && isset($user['MaVaiTro']) && (int)$user['MaVaiTro'] === 1) {
    $isAdmin = true;
} elseif ($user && !empty($user['TenVaiTro'])) {
    $tenVaiTro = mb_strtolower(trim((string)$user['TenVaiTro']), 'UTF-8');
    if ($tenVaiTro === 'admin' || $tenVaiTro === 'quản trị' || $tenVaiTro === 'quan tri' || str_contains($tenVaiTro, 'admin')) {
        $isAdmin = true;
    }
}

if (!$isAdmin) {
    echo json_encode(['success' => false, 'message' => 'Bạn không có quyền thực hiện chức năng này']);
    exit;
}

$maPhieu = isset($_POST['maPhieu']) ? trim((string)$_POST['maPhieu']) : '';
if ($maPhieu === '') {
    echo json_encode(['success' => false, 'message' => 'Mã phiếu không hợp lệ']);
    exit;
}

try {
    $pm = dbQueryOne(
        "SELECT MaPhieu, TrangThai, NgayTraThucTe FROM `phieumuon` WHERE MaPhieu = ? AND IsDeleted = 0 LIMIT 1",
        [$maPhieu]
    );

    if (!$pm) {
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy phiếu mượn']);
        exit;
    }

    // If already completed/returned, do nothing
    $currentStatus = (string)($pm['TrangThai'] ?? '');
    if ($currentStatus === 'Hoàn thành' || $currentStatus === 'Đã trả') {
        echo json_encode(['success' => true, 'message' => 'Phiếu mượn đã hoàn thành trước đó']);
        exit;
    }

    // Block if any unpaid fines exist for this borrow
    $unpaid = dbQueryOne(
        "SELECT COUNT(*) AS cnt FROM `phieuphat` WHERE IsDeleted = 0 AND MaPhieu = ? AND DaThanhToan = 0",
        [$maPhieu]
    );
    $cnt = ($unpaid && isset($unpaid['cnt'])) ? (int)$unpaid['cnt'] : 0;
    if ($cnt > 0) {
        echo json_encode(['success' => false, 'message' => 'Chưa thanh toán phiếu phạt, không thể cập nhật đã trả/hoàn thành']);
        exit;
    }

    dbExecute(
        "UPDATE `phieumuon` SET NgayTraThucTe = NOW(), TrangThai = 'Hoàn thành' WHERE MaPhieu = ? AND IsDeleted = 0",
        [$maPhieu]
    );

    // When completed, return devices to available (only if currently borrowed)
    dbExecute(
        "UPDATE `thietbi`
         SET MaTrangThai = 1
         WHERE IsDeleted = 0
           AND MaTrangThai = 2
           AND MaThietBi IN (
                SELECT MaThietBi
                FROM `chitietmuon`
                WHERE IsDeleted = 0 AND MaPhieu = ?
           )",
        [$maPhieu]
    );

    echo json_encode(['success' => true, 'message' => 'Đã cập nhật phiếu mượn: Hoàn thành']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
}
