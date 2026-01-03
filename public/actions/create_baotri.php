<?php
/**
 * Tạo phiếu bảo trì (admin only)
 * - Chỉ cho phép với thiết bị đang khả dụng (MaTrangThai = 1)
 * - Sau khi tạo, cập nhật thiết bị sang trạng thái đang bảo trì (MaTrangThai = 3)
 */

session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../includes/auth.php';
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

if (!$user || !$isAdmin) {
    echo json_encode(['success' => false, 'message' => 'Bạn không có quyền thực hiện chức năng này']);
    exit;
}

$maThietBi = isset($_POST['maThietBi']) ? trim((string)$_POST['maThietBi']) : '';
$maNhaCungCap = isset($_POST['maNhaCungCap']) ? trim((string)$_POST['maNhaCungCap']) : '';
$moTa = isset($_POST['moTa']) ? trim((string)$_POST['moTa']) : '';

if ($maThietBi === '' || $maNhaCungCap === '' || $moTa === '') {
    echo json_encode(['success' => false, 'message' => 'Vui lòng nhập đầy đủ thông tin bắt buộc']);
    exit;
}

$allowedSuppliers = [
    'Công ty Ngọc Diệp',
    'Công ty Tương Lai',
    'Phú Diễn',
    'An Thái',
];
if (!in_array($maNhaCungCap, $allowedSuppliers, true)) {
    echo json_encode(['success' => false, 'message' => 'Nhà cung cấp không hợp lệ']);
    exit;
}

try {
    // Ensure device exists and is available
    $tb = dbQueryOne(
        "SELECT MaThietBi, MaTrangThai
         FROM `thietbi`
         WHERE IsDeleted = 0 AND MaThietBi = ?
         LIMIT 1",
        [$maThietBi]
    );

    if (!$tb) {
        echo json_encode(['success' => false, 'message' => 'Thiết bị không tồn tại hoặc đã bị xóa']);
        exit;
    }

    if ((int)($tb['MaTrangThai'] ?? 0) !== 1) {
        echo json_encode(['success' => false, 'message' => 'Thiết bị không ở trạng thái khả dụng để bảo trì']);
        exit;
    }

    // Generate MaBaoTri (BT###)
    $last = dbQueryOne("SELECT MaBaoTri FROM `baotri` ORDER BY MaBaoTri DESC LIMIT 1");
    $nextNum = 1;
    if ($last && !empty($last['MaBaoTri']) && preg_match('/(\d+)$/', (string)$last['MaBaoTri'], $m)) {
        $nextNum = intval($m[1]) + 1;
    }
    $maBaoTri = 'BT' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);

    // Insert maintenance ticket
    $sql = "INSERT INTO `baotri` (MaBaoTri, MaThietBi, NgayBao, NgaySua, TrangThai, MaNhaCungCap, ChiPhi, MoTa, IsDeleted)
            VALUES (?, ?, NOW(), NULL, 'Đang bảo trì', ?, NULL, ?, 0)";
    $ins = dbExecute($sql, [$maBaoTri, $maThietBi, $maNhaCungCap, $moTa]);

    if ($ins === false) {
        echo json_encode(['success' => false, 'message' => 'Không thể tạo phiếu bảo trì']);
        exit;
    }

    // Update device status to maintenance (3)
    $upd = dbExecute("UPDATE `thietbi` SET MaTrangThai = 3 WHERE MaThietBi = ? AND IsDeleted = 0 AND MaTrangThai = 1", [$maThietBi]);
    if ($upd === false || (int)$upd <= 0) {
        echo json_encode(['success' => false, 'message' => 'Đã tạo phiếu nhưng không thể cập nhật trạng thái thiết bị']);
        exit;
    }

    echo json_encode([
        'success' => true,
        'message' => 'Tạo phiếu bảo trì thành công',
        'maBaoTri' => $maBaoTri,
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
}
