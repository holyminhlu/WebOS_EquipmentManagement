<?php
/**
 * Handle approve action for YeuCauMuon
 * - Admin only
 * - Creates a PhieuMuon record from YeuCauMuon
 * - Marks the YeuCauMuon as 'Đã duyệt' and records approver
 * - Inserts a thongbao for the requester
 */
session_start();

require_once __DIR__ . '/../../includes/auth.php';
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

if (!$user || !$isAdmin) {
    // not an admin
    header('Location: ../dashboard.php');
    exit;
}

$action = $_POST['action'] ?? '';
$maYeuCau = $_POST['MaYeuCau'] ?? '';

if ($action !== 'approve' || empty($maYeuCau)) {
    header('Location: ../dashboard.php');
    exit;
}

// Fetch request
$yc = dbQueryOne("SELECT * FROM `yeucaumuon` WHERE MaYeuCau = ? AND IsDeleted = 0", [$maYeuCau]);
if (!$yc) {
    header('Location: ../dashboard.php');
    exit;
}

$beforeYc = $yc;

// If already approved, nothing to do
if (isset($yc['TrangThai']) && $yc['TrangThai'] === 'Đã duyệt') {
    header('Location: ../dashboard.php');
    exit;
}

// Generate new MaPhieu (PM###) by inspecting last MaPhieu
$last = dbQueryOne("SELECT MaPhieu FROM `phieumuon` ORDER BY MaPhieu DESC LIMIT 1");
$nextNum = 1;
if ($last && !empty($last['MaPhieu'])) {
    if (preg_match('/(\d+)$/', $last['MaPhieu'], $m)) {
        $nextNum = intval($m[1]) + 1;
    }
}
$maPhieu = 'PM' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);
$soPhieu = 'SP' . str_pad($nextNum, 3, '0', STR_PAD_LEFT); // Số phiếu uses SP### prefix

$ngayPhat = date('Y-m-d H:i:s');
$ngayPhaiTra = $yc['ThoiGianKetThuc'] ?? ($yc['NgayDuKienKetThuc'] ?? null);
$trangThai = 'Đang mượn';

// Insert into phieumuon (use a minimal column set that is expected to exist)
$sqlInsert = "INSERT INTO `phieumuon` (MaPhieu, SoPhieu, MaYeuCau, MaNguoiMuon, NgayPhat, NgayPhaiTra, TrangThai, NguoiPhatThietBi) VALUES (?,?,?,?,?,?,?,?)";
$insertResult = dbExecute($sqlInsert, [$maPhieu, $soPhieu, $maYeuCau, $yc['MaNguoiYeuCau'], $ngayPhat, $ngayPhaiTra, $trangThai, $_SESSION['user_id']]);

if ($insertResult === false) {
    // Failed to create phieu; abort
    header('Location: ../dashboard.php');
    exit;
}

$afterPm = dbQueryOne("SELECT * FROM `phieumuon` WHERE MaPhieu = ? LIMIT 1", [$maPhieu]);
auditLog('PhieuMuon', $maPhieu, 'CREATE', null, $afterPm);

// After creating PhieuMuon, create at least one ChiTietMuon row
// Strategy: create ChiTietMuon for all requested devices from YeuCauMuon.GhiChu
// Format stored by create_yeucaumuon.php: "DS_TB:TB001,TB002"
$requestedIds = [];
if (!empty($yc['GhiChu']) && preg_match('/DS_TB:([^\n\r]+)/', $yc['GhiChu'], $mList)) {
    $raw = trim($mList[1]);
    if ($raw !== '') {
        $requestedIds = array_values(array_filter(array_map('trim', explode(',', $raw))));
    }
}

// Fallback: if none stored, pick one available
if (empty($requestedIds)) {
    $fallback = dbQueryOne("SELECT MaThietBi FROM `thietbi` WHERE IsDeleted = 0 AND MaTrangThai = 1 LIMIT 1");
    if ($fallback && !empty($fallback['MaThietBi'])) {
        $requestedIds = [$fallback['MaThietBi']];
    }
}

// Validate requested devices are available at approval time
$validIds = [];
if (!empty($requestedIds)) {
    $placeholders = implode(',', array_fill(0, count($requestedIds), '?'));
    $rows = dbQuery("SELECT MaThietBi FROM `thietbi` WHERE IsDeleted = 0 AND MaTrangThai = 1 AND MaThietBi IN ($placeholders)", $requestedIds);
    foreach ($rows as $r) {
        $validIds[] = $r['MaThietBi'];
    }
}

// Generate base CTM number once
$lastCt = dbQueryOne("SELECT MaChiTiet FROM `chitietmuon` ORDER BY MaChiTiet DESC LIMIT 1");
$nextCtNum = 1;
if ($lastCt && !empty($lastCt['MaChiTiet']) && preg_match('/(\d+)$/', $lastCt['MaChiTiet'], $mct)) {
    $nextCtNum = intval($mct[1]) + 1;
}

foreach ($validIds as $deviceId) {
    $maChiTiet = 'CTM' . str_pad($nextCtNum, 3, '0', STR_PAD_LEFT);
    $nextCtNum++;

    $sqlCt = "INSERT INTO `chitietmuon` (MaChiTiet, MaPhieu, MaThietBi, SoLuong, TinhTrangLucMuon, GhiChu, IsDeleted) VALUES (?,?,?,?,?,?,0)";
    dbExecute($sqlCt, [$maChiTiet, $maPhieu, $deviceId, 1, 'Tốt', 'Tạo khi duyệt yêu cầu']);

    $afterCtm = dbQueryOne("SELECT * FROM `chitietmuon` WHERE MaChiTiet = ? LIMIT 1", [$maChiTiet]);
    auditLog('ChiTietMuon', $maChiTiet, 'CREATE', null, $afterCtm);

    // Update device status to borrowed (MaTrangThai = 2)
    $beforeTb = dbQueryOne("SELECT * FROM `thietbi` WHERE MaThietBi = ? LIMIT 1", [$deviceId]);
    dbExecute("UPDATE `thietbi` SET MaTrangThai = 2 WHERE MaThietBi = ?", [$deviceId]);
    $afterTb = dbQueryOne("SELECT * FROM `thietbi` WHERE MaThietBi = ? LIMIT 1", [$deviceId]);
    auditLog('ThietBi', $deviceId, 'UPDATE', $beforeTb, $afterTb);
}

// Update yeucaumuon: mark as approved and set approver + date (if columns exist)
dbExecute("UPDATE `yeucaumuon` SET TrangThai = ?, NguoiDuyet = ?, NgayDuyet = NOW() WHERE MaYeuCau = ?", ['Đã duyệt', $_SESSION['user_id'], $maYeuCau]);

$afterYc = dbQueryOne("SELECT * FROM `yeucaumuon` WHERE MaYeuCau = ? AND IsDeleted = 0 LIMIT 1", [$maYeuCau]);
auditLog('YeuCauMuon', $maYeuCau, 'APPROVE', $beforeYc, $afterYc);

// Insert a notification for the requester
$tieuDe = "Yêu cầu {$maYeuCau} đã được duyệt";
$noiDung = "Yêu cầu mượn của bạn đã được duyệt. Phiếu mượn: {$maPhieu}. Vui lòng kiểm tra phần Phiếu mượn.";
// Generate MaThongBao to avoid duplicate primary key issues (some schemas use MaThongBao as PK)
$lastTb = dbQueryOne("SELECT MaThongBao FROM `thongbao` ORDER BY MaThongBao DESC LIMIT 1");
$nextTbNum = 1;
if ($lastTb && !empty($lastTb['MaThongBao']) && preg_match('/(\d+)$/', $lastTb['MaThongBao'], $m)) {
    $nextTbNum = intval($m[1]) + 1;
}
$maThongBao = 'TB' . str_pad($nextTbNum, 3, '0', STR_PAD_LEFT);

dbExecute("INSERT INTO `thongbao` (MaThongBao, MaNguoiDung, TieuDe, NoiDung, NgayGui, DaDoc, IsDeleted) VALUES (?,?,?,?,?,0,0)", [$maThongBao, $yc['MaNguoiYeuCau'], $tieuDe, $noiDung, $ngayPhat]);

header('Location: ../dashboard.php');
exit;

