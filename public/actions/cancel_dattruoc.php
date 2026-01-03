<?php
/**
 * Xử lý hủy yêu cầu đặt trước thiết bị
 */

session_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/audit.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Vui lòng đăng nhập để thực hiện chức năng này'
    ]);
    exit;
}

// Kiểm tra method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Method không hợp lệ'
    ]);
    exit;
}

$maDatTruoc = isset($_POST['maDatTruoc']) ? trim((string)$_POST['maDatTruoc']) : '';

if ($maDatTruoc === '') {
    echo json_encode([
        'success' => false,
        'message' => 'Mã đặt trước không hợp lệ'
    ]);
    exit;
}

try {
    // Support grouped cancel: if maDatTruoc is a group key (e.g. DT001D12), cancel all DT001D12 and DT001D12-*
    $like = $maDatTruoc . '-%';

    $rows = dbQuery(
        "SELECT MaDatTruoc, TrangThai
         FROM dattruoc
         WHERE IsDeleted = 0
           AND MaNguoiYeuCau = ?
           AND (MaDatTruoc = ? OR MaDatTruoc LIKE ?)",
        [$_SESSION['user_id'], $maDatTruoc, $like]
    );

    if (empty($rows)) {
        echo json_encode([
            'success' => false,
            'message' => 'Không tìm thấy đặt trước hoặc bạn không có quyền hủy yêu cầu này'
        ]);
        exit;
    }

    foreach ($rows as $r) {
        if (($r['TrangThai'] ?? '') !== 'Chờ duyệt') {
            echo json_encode([
                'success' => false,
                'message' => 'Chỉ có thể hủy yêu cầu đang ở trạng thái "Chờ duyệt"'
            ]);
            exit;
        }
    }

    $beforeGroup = $rows;

    dbExecute(
        "UPDATE dattruoc
         SET TrangThai = 'Đã hủy'
         WHERE MaNguoiYeuCau = ?
           AND IsDeleted = 0
           AND TrangThai = 'Chờ duyệt'
           AND (MaDatTruoc = ? OR MaDatTruoc LIKE ?)",
        [$_SESSION['user_id'], $maDatTruoc, $like]
    );

    $afterGroup = dbQuery(
        "SELECT MaDatTruoc, TrangThai
         FROM dattruoc
         WHERE IsDeleted = 0
           AND MaNguoiYeuCau = ?
           AND (MaDatTruoc = ? OR MaDatTruoc LIKE ?)",
        [$_SESSION['user_id'], $maDatTruoc, $like]
    );
    auditLog('DatTruoc', $maDatTruoc, 'CANCEL', $beforeGroup, $afterGroup);

    // Tạo thông báo
    $lastTb = dbQueryOne("SELECT MaThongBao FROM thongbao ORDER BY MaThongBao DESC LIMIT 1");
    $nextTbNum = 1;
    if ($lastTb && !empty($lastTb['MaThongBao']) && preg_match('/(\d+)$/', $lastTb['MaThongBao'], $m)) {
        $nextTbNum = intval($m[1]) + 1;
    }
    $maThongBao = 'TB' . str_pad($nextTbNum, 3, '0', STR_PAD_LEFT);

    $noiDung = "Bạn đã hủy yêu cầu đặt trước mã {$maDatTruoc} thành công.";
    $sqlThongBao = "INSERT INTO thongbao (MaThongBao, MaNguoiDung, TieuDe, NoiDung, Kenh, NgayGui, DaDoc, IsDeleted)
                    VALUES (?, ?, 'Đặt trước đã bị hủy', ?, 'Hệ thống', NOW(), 0, 0)";
    dbExecute($sqlThongBao, [$maThongBao, $_SESSION['user_id'], $noiDung]);

    echo json_encode([
        'success' => true,
        'message' => 'Đã hủy đặt trước thành công!'
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi hệ thống: ' . $e->getMessage()
    ]);
}

