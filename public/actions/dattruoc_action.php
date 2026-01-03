<?php
/**
 * Handle approve action for DatTruoc (admin only)
 * - Approves a grouped reservation (MaDatTruoc group key)
 * - Marks all rows in the group as 'Đã duyệt'
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
    header('Location: ../dashboard.php');
    exit;
}

$action = $_POST['action'] ?? '';
$maDatTruoc = isset($_POST['MaDatTruoc']) ? trim((string)$_POST['MaDatTruoc']) : '';

if ($action !== 'approve' || $maDatTruoc === '') {
    header('Location: ../dashboard.php');
    exit;
}

try {
    // Approve grouped ticket: group key is exact id prefix before '-' (e.g. DT001 or DT001D12)
    $like = $maDatTruoc . '-%';

    $rows = dbQuery(
        "SELECT MaDatTruoc, TrangThai, MaNguoiYeuCau\n         FROM dattruoc\n         WHERE IsDeleted = 0\n           AND (MaDatTruoc = ? OR MaDatTruoc LIKE ?)",
        [$maDatTruoc, $like]
    );

    if (empty($rows)) {
        header('Location: ../dashboard.php');
        exit;
    }

    foreach ($rows as $r) {
        if (($r['TrangThai'] ?? '') !== 'Chờ duyệt') {
            header('Location: ../dashboard.php');
            exit;
        }
    }

    $maNguoiYeuCau = (string)($rows[0]['MaNguoiYeuCau'] ?? '');
    if ($maNguoiYeuCau === '') {
        header('Location: ../dashboard.php');
        exit;
    }

    dbExecute(
        "UPDATE dattruoc\n         SET TrangThai = 'Đã duyệt'\n         WHERE IsDeleted = 0\n           AND TrangThai = 'Chờ duyệt'\n           AND (MaDatTruoc = ? OR MaDatTruoc LIKE ?)",
        [$maDatTruoc, $like]
    );

    // Thông báo cho người yêu cầu
    $lastTb = dbQueryOne("SELECT MaThongBao FROM thongbao ORDER BY MaThongBao DESC LIMIT 1");
    $nextTbNum = 1;
    if ($lastTb && !empty($lastTb['MaThongBao']) && preg_match('/(\d+)$/', $lastTb['MaThongBao'], $m)) {
        $nextTbNum = intval($m[1]) + 1;
    }
    $maThongBao = 'TB' . str_pad($nextTbNum, 3, '0', STR_PAD_LEFT);

    $noiDung = "Yêu cầu đặt trước mã {$maDatTruoc} đã được duyệt.";
    $sqlThongBao = "INSERT INTO thongbao (MaThongBao, MaNguoiDung, TieuDe, NoiDung, Kenh, NgayGui, DaDoc, IsDeleted)
                    VALUES (?, ?, 'Đặt trước đã được duyệt', ?, 'Hệ thống', NOW(), 0, 0)";
    dbExecute($sqlThongBao, [$maThongBao, $maNguoiYeuCau, $noiDung]);
} catch (Exception $e) {
    // Fail closed: just return to dashboard
}

header('Location: ../dashboard.php');
exit;
