<?php
// Handle actions for DatTruoc (approve)
session_start();

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../dashboard.php');
    exit;
}

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

// Check user role
$user = getCurrentUser();
if (!$user || (int)$user['MaVaiTro'] !== 1) {
    // Not admin
    header('Location: ../dashboard.php');
    exit;
}

$action = $_POST['action'] ?? '';
$id = $_POST['id'] ?? '';

if ($action === 'approve' && !empty($id)) {
    // Update DatTruoc set TrangThai = 'Đã duyệt'
    $sql = "UPDATE `dattruoc` SET TrangThai = ?, NgayTao = NgayTao WHERE MaDatTruoc = ? AND IsDeleted = 0";
    // Note: table does not have NguoiDuyet/NgayDuyet in schema; only update TrangThai
    $res = dbExecute($sql, ['Đã duyệt', $id]);

    // Optionally create a ThongBao for user
    $notifSql = "INSERT INTO `thongbao` (MaThongBao, MaNguoiDung, TieuDe, NoiDung, DaDoc, NgayGui, Kenh, IsDeleted) VALUES (?, ?, ?, ?, 0, NOW(), 'trong ứng dụng', 0)";
    $maThongBao = 'TB' . uniqid();
    // Find MaNguoiYeuCau to notify
    $row = dbQueryOne("SELECT MaNguoiYeuCau FROM `dattruoc` WHERE MaDatTruoc = ?", [$id]);
    if ($row) {
        $toUser = $row['MaNguoiYeuCau'];
        $title = 'Đặt trước được duyệt';
        $content = 'Yêu cầu đặt trước ' . $id . ' đã được duyệt.';
        dbExecute($notifSql, [$maThongBao, $toUser, $title, $content]);
    }
}

header('Location: ../dashboard.php');
exit;
