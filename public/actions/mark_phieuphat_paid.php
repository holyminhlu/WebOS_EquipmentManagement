<?php
/**
 * Đánh dấu phiếu phạt đã thanh toán (admin only)
 * - Updates: PhieuPhat.DaThanhToan=1, NgayThanhToan=NOW()
 * - Also updates: PhieuMuon.TongTienPhat += PhieuPhat.SoTien
 */

session_start();

require_once __DIR__ . '/../../includes/user.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/audit.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../dashboard.php');
    exit;
}

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
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
    header('Location: ../dashboard.php');
    exit;
}

$maPhat = isset($_POST['maPhat']) ? trim((string)$_POST['maPhat']) : '';
if ($maPhat === '') {
    header('Location: ../dashboard.php');
    exit;
}

try {
    $pp = dbQueryOne(
        "SELECT MaPhat, MaPhieu, SoTien, DaThanhToan FROM `phieuphat` WHERE MaPhat = ? AND IsDeleted = 0 LIMIT 1",
        [$maPhat]
    );

    if (!$pp) {
        header('Location: ../dashboard.php');
        exit;
    }

    if (!empty($pp['DaThanhToan'])) {
        header('Location: ../dashboard.php');
        exit;
    }

    $soTien = isset($pp['SoTien']) ? (float)$pp['SoTien'] : 0;
    $maPhieu = (string)($pp['MaPhieu'] ?? '');

    $beforePp = $pp;

    // Mark paid
    dbExecute(
        "UPDATE `phieuphat` SET DaThanhToan = 1, NgayThanhToan = NOW() WHERE MaPhat = ? AND IsDeleted = 0",
        [$maPhat]
    );

    $afterPp = dbQueryOne("SELECT * FROM `phieuphat` WHERE MaPhat = ? AND IsDeleted = 0 LIMIT 1", [$maPhat]);
    auditLog('PhieuPhat', $maPhat, 'PAY', $beforePp, $afterPp);

    // Save info into PhieuMuon (accumulate fine)
    if ($maPhieu !== '' && $soTien > 0) {
        dbExecute(
            "UPDATE `phieumuon` SET TongTienPhat = COALESCE(TongTienPhat, 0) + ? WHERE MaPhieu = ? AND IsDeleted = 0",
            [$soTien, $maPhieu]
        );
    }

    // If all fines for this borrow are paid, auto-complete the borrow slip
    if ($maPhieu !== '') {
        $beforePm = dbQueryOne("SELECT * FROM `phieumuon` WHERE IsDeleted = 0 AND MaPhieu = ? LIMIT 1", [$maPhieu]);

        $unpaid = dbQueryOne(
            "SELECT COUNT(*) AS cnt FROM `phieuphat` WHERE IsDeleted = 0 AND MaPhieu = ? AND DaThanhToan = 0",
            [$maPhieu]
        );
        $cnt = ($unpaid && isset($unpaid['cnt'])) ? (int)$unpaid['cnt'] : 0;
        if ($cnt === 0) {
            dbExecute(
                "UPDATE `phieumuon` SET NgayTraThucTe = NOW(), TrangThai = 'Hoàn thành' WHERE MaPhieu = ? AND IsDeleted = 0",
                [$maPhieu]
            );

            $afterPm = dbQueryOne("SELECT * FROM `phieumuon` WHERE IsDeleted = 0 AND MaPhieu = ? LIMIT 1", [$maPhieu]);
            auditLog('PhieuMuon', $maPhieu, 'COMPLETE', $beforePm, $afterPm);

            // Return devices to available when borrow is completed
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
        }
    }
} catch (Exception $e) {
    // Fail closed: just redirect
}

header('Location: ../dashboard.php');
exit;

