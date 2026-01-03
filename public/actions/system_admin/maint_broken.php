<?php
/**
 * System admin: mark maintenance as broken
 * - Set BaoTri.NgaySua = NOW(), TrangThai = 'Thiết bị hỏng', ChiPhi = NULL
 * - Set ThietBi.MaTrangThai = 4 (Thiết bị hỏng)
 */

require_once __DIR__ . '/_guard.php';
require_once __DIR__ . '/../../../includes/db.php';
require_once __DIR__ . '/../../../includes/audit.php';

$maBaoTri = isset($_POST['maBaoTri']) ? trim((string)$_POST['maBaoTri']) : '';
$maThietBi = isset($_POST['maThietBi']) ? trim((string)$_POST['maThietBi']) : '';

if ($maBaoTri === '' || $maThietBi === '') {
    echo json_encode(['success' => false, 'message' => 'Thiếu thông tin phiếu bảo trì']);
    exit;
}

try {
    $bt = dbQueryOne(
        "SELECT MaBaoTri, MaThietBi, TrangThai
         FROM `baotri`
         WHERE IsDeleted = 0 AND MaBaoTri = ? AND MaThietBi = ?
         LIMIT 1",
        [$maBaoTri, $maThietBi]
    );

    if (!$bt) {
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy phiếu bảo trì']);
        exit;
    }

    if ((string)($bt['TrangThai'] ?? '') !== 'Đang bảo trì') {
        echo json_encode(['success' => false, 'message' => 'Phiếu bảo trì không ở trạng thái có thể cập nhật']);
        exit;
    }

    $beforeBt = $bt;
    $beforeTb = dbQueryOne("SELECT * FROM `thietbi` WHERE MaThietBi = ? AND IsDeleted = 0 LIMIT 1", [$maThietBi]);

    $ok1 = dbExecute(
        "UPDATE `baotri`
         SET NgaySua = NOW(), TrangThai = 'Thiết bị hỏng', ChiPhi = NULL
         WHERE MaBaoTri = ? AND MaThietBi = ? AND IsDeleted = 0",
        [$maBaoTri, $maThietBi]
    );

    if ($ok1 === false || (int)$ok1 <= 0) {
        echo json_encode(['success' => false, 'message' => 'Không thể cập nhật phiếu bảo trì']);
        exit;
    }

    $afterBt = dbQueryOne("SELECT * FROM `baotri` WHERE MaBaoTri = ? AND IsDeleted = 0 LIMIT 1", [$maBaoTri]);
    auditLog('BaoTri', $maBaoTri, 'BROKEN', $beforeBt, $afterBt);

    $ok2 = dbExecute(
        "UPDATE `thietbi` SET MaTrangThai = 4 WHERE MaThietBi = ? AND IsDeleted = 0",
        [$maThietBi]
    );

    if ($ok2 === false || (int)$ok2 <= 0) {
        echo json_encode(['success' => false, 'message' => 'Đã cập nhật phiếu nhưng không thể cập nhật trạng thái thiết bị']);
        exit;
    }

    $afterTb = dbQueryOne("SELECT * FROM `thietbi` WHERE MaThietBi = ? AND IsDeleted = 0 LIMIT 1", [$maThietBi]);
    auditLog('ThietBi', $maThietBi, 'UPDATE', $beforeTb, $afterTb);

    echo json_encode(['success' => true, 'message' => 'Đã đánh dấu thiết bị hỏng và cập nhật trạng thái thiết bị']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
}

