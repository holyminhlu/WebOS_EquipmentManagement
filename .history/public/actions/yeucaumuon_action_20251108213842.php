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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../dashboard.php');
    exit;
}

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$user = getUserInfo($_SESSION['user_id']);
if (!$user || (int)$user['MaVaiTro'] !== 1) {
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
$ngayPhaiTra = $yc['NgayDuKienKetThuc'] ?? null;
$trangThai = 'Đang mượn';

// Insert into phieumuon (use a minimal column set that is expected to exist)
$sqlInsert = "INSERT INTO `phieumuon` (MaPhieu, SoPhieu, MaYeuCau, MaNguoiMuon, NgayPhat, NgayPhaiTra, TrangThai, NguoiPhatThietBi) VALUES (?,?,?,?,?,?,?,?)";
$insertResult = dbExecute($sqlInsert, [$maPhieu, $soPhieu, $maYeuCau, $yc['MaNguoiYeuCau'], $ngayPhat, $ngayPhaiTra, $trangThai, $_SESSION['user_id']]);

if ($insertResult === false) {
    // Failed to create phieu; abort
    header('Location: ../dashboard.php');
    exit;
}

// After creating PhieuMuon, create at least one ChiTietMuon row
// Strategy: pick one available device (MaTrangThai = 1) as default and insert SoLuong = 1
$available = dbQueryOne("SELECT MaThietBi FROM `thietbi` WHERE IsDeleted = 0 AND MaTrangThai = 1 LIMIT 1");
if ($available && !empty($available['MaThietBi'])) {
    // generate MaChiTiet CT###
    $lastCt = dbQueryOne("SELECT MaChiTiet FROM `chitietmuon` ORDER BY MaChiTiet DESC LIMIT 1");
    $nextCtNum = 1;
    if ($lastCt && !empty($lastCt['MaChiTiet']) && preg_match('/(\d+)$/', $lastCt['MaChiTiet'], $mct)) {
        $nextCtNum = intval($mct[1]) + 1;
    }
    $maChiTiet = 'CT' . str_pad($nextCtNum, 3, '0', STR_PAD_LEFT);

    // Insert into chitietmuon
    $sqlCt = "INSERT INTO `chitietmuon` (MaChiTiet, MaPhieu, MaThietBi, SoLuong, TinhTrangLucMuon, GhiChu, IsDeleted) VALUES (?,?,?,?,?,?,0)";
    $ctInsert = dbExecute($sqlCt, [$maChiTiet, $maPhieu, $available['MaThietBi'], 1, 'Tốt', 'Tạo tự động khi duyệt yêu cầu']);
    // Note: if insert fails we continue; admin can adjust later
}

// Update yeucaumuon: mark as approved and set approver + date (if columns exist)
dbExecute("UPDATE `yeucaumuon` SET TrangThai = ?, NguoiDuyet = ?, NgayDuyet = NOW() WHERE MaYeuCau = ?", ['Đã duyệt', $_SESSION['user_id'], $maYeuCau]);

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
