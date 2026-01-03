<?php
/**
 * Tạo phiếu phạt từ YêuCauMuon (admin only)
 * - Input: POST { maYeuCau, lyDo, soTien }
 * - Output: JSON { success, message, maPhat? }
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

$maYeuCau = isset($_POST['maYeuCau']) ? trim((string)$_POST['maYeuCau']) : '';
$maPhieuInput = isset($_POST['maPhieu']) ? trim((string)$_POST['maPhieu']) : '';
$lyDo = isset($_POST['lyDo']) ? trim((string)$_POST['lyDo']) : '';
$soTienRaw = isset($_POST['soTien']) ? trim((string)$_POST['soTien']) : '';

$allowedReasons = [
    'Trả thiết bị quá hạn',
    'Thiết bị bị hư hỏng trong quá trình mượn',
    'Tự ý sửa chữa hoặc can thiệp thiết bị',
];

if (($maYeuCau === '' && $maPhieuInput === '') || $lyDo === '' || $soTienRaw === '') {
    echo json_encode(['success' => false, 'message' => 'Vui lòng nhập đầy đủ thông tin']);
    exit;
}

if (!in_array($lyDo, $allowedReasons, true)) {
    echo json_encode(['success' => false, 'message' => 'Lý do phạt không hợp lệ']);
    exit;
}

// Allow input like: 10000, 10.000, 10,000
$normalized = preg_replace('/[^0-9]/', '', $soTienRaw);
if ($normalized === '') {
    echo json_encode(['success' => false, 'message' => 'Số tiền phạt không hợp lệ']);
    exit;
}
$soTien = (int)$normalized;
if ($soTien <= 0) {
    echo json_encode(['success' => false, 'message' => 'Số tiền phạt phải lớn hơn 0']);
    exit;
}

try {
    $maPhieu = '';
    $maNguoiDung = '';

    if ($maPhieuInput !== '') {
        // Prefer direct phieu
        $pm = dbQueryOne(
            "SELECT MaPhieu, MaNguoiMuon, MaYeuCau, TrangThai FROM `phieumuon` WHERE IsDeleted = 0 AND MaPhieu = ? LIMIT 1",
            [$maPhieuInput]
        );
        if (!$pm || empty($pm['MaPhieu'])) {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy phiếu mượn']);
            exit;
        }
        $maPhieu = (string)$pm['MaPhieu'];
        $maNguoiDung = (string)($pm['MaNguoiMuon'] ?? '');
        if ($maYeuCau === '' && !empty($pm['MaYeuCau'])) {
            $maYeuCau = (string)$pm['MaYeuCau'];
        }
    } else {
        // Backward-compat: find from yeucaumuon
        $yc = dbQueryOne(
            "SELECT MaYeuCau, MaNguoiYeuCau, TrangThai FROM `yeucaumuon` WHERE MaYeuCau = ? AND IsDeleted = 0 LIMIT 1",
            [$maYeuCau]
        );
        if (!$yc) {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy yêu cầu mượn']);
            exit;
        }
        if (($yc['TrangThai'] ?? '') !== 'Đã duyệt') {
            echo json_encode(['success' => false, 'message' => 'Chỉ có thể phạt đối với yêu cầu đã được duyệt']);
            exit;
        }
        $pm = dbQueryOne(
            "SELECT MaPhieu, MaNguoiMuon FROM `phieumuon` WHERE IsDeleted = 0 AND MaYeuCau = ? ORDER BY NgayPhat DESC LIMIT 1",
            [$maYeuCau]
        );
        if (!$pm || empty($pm['MaPhieu'])) {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy phiếu mượn tương ứng để lập phiếu phạt']);
            exit;
        }
        $maPhieu = (string)$pm['MaPhieu'];
        $maNguoiDung = (string)($pm['MaNguoiMuon'] ?? ($yc['MaNguoiYeuCau'] ?? ''));
    }

    if ($maPhieu === '') {
        echo json_encode(['success' => false, 'message' => 'Không xác định được phiếu mượn để lập phiếu phạt']);
        exit;
    }

    // Extra rule requested: if fine already paid => mark phieumuon completed; if unpaid exists => keep unchanged
    $anyUnpaid = dbQueryOne(
        "SELECT COUNT(*) AS cnt FROM `phieuphat` WHERE IsDeleted = 0 AND MaPhieu = ? AND DaThanhToan = 0",
        [$maPhieu]
    );
    $unpaidCnt = ($anyUnpaid && isset($anyUnpaid['cnt'])) ? (int)$anyUnpaid['cnt'] : 0;
    if ($unpaidCnt > 0) {
        echo json_encode(['success' => false, 'message' => 'Phiếu mượn đang có phiếu phạt chưa thanh toán. Không thể hoàn thành và không tạo thêm phiếu phạt.']);
        exit;
    }

    $anyPaid = dbQueryOne(
        "SELECT COUNT(*) AS cnt FROM `phieuphat` WHERE IsDeleted = 0 AND MaPhieu = ? AND DaThanhToan = 1",
        [$maPhieu]
    );
    $paidCnt = ($anyPaid && isset($anyPaid['cnt'])) ? (int)$anyPaid['cnt'] : 0;
    if ($paidCnt > 0) {
        // Mark completed
        dbExecute(
            "UPDATE `phieumuon` SET NgayTraThucTe = NOW(), TrangThai = 'Hoàn thành' WHERE MaPhieu = ? AND IsDeleted = 0",
            [$maPhieu]
        );

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
        echo json_encode(['success' => true, 'message' => 'Phiếu phạt đã thanh toán. Đã cập nhật phiếu mượn: Hoàn thành']);
        exit;
    }

    // Generate MaPhat: PP-YYYYMMDD-XXXX (sequence per day)
    $ymd = date('Ymd');
    $prefix = 'PP-' . $ymd . '-';
    $last = dbQueryOne(
        "SELECT MaPhat FROM `phieuphat` WHERE IsDeleted = 0 AND MaPhat LIKE ? ORDER BY MaPhat DESC LIMIT 1",
        [$prefix . '%']
    );

    $next = 1;
    if ($last && !empty($last['MaPhat'])) {
        $m = [];
        if (preg_match('/^PP-[0-9]{8}-([0-9]{4})$/', (string)$last['MaPhat'], $m)) {
            $next = ((int)$m[1]) + 1;
        }
    }

    $maPhat = $prefix . str_pad((string)$next, 4, '0', STR_PAD_LEFT);

    $sql = "INSERT INTO `phieuphat` (MaPhat, MaPhieu, MaNguoiDung, SoTien, LyDo, DaThanhToan, NgayThanhToan, NgayTao, IsDeleted)
            VALUES (?, ?, ?, ?, ?, 0, NULL, NOW(), 0)";

    $ok = dbExecute($sql, [$maPhat, $maPhieu, $maNguoiDung, $soTien, $lyDo]);

    if ($ok === false) {
        echo json_encode(['success' => false, 'message' => 'Không thể tạo phiếu phạt. Vui lòng thử lại']);
        exit;
    }

    // Create notification for the fined user
    $warn = '';
    try {
        $pmInfo = dbQueryOne(
            "SELECT pm.SoPhieu,
                    GROUP_CONCAT(DISTINCT ctm.MaThietBi ORDER BY ctm.MaThietBi SEPARATOR ', ') AS ThietBi
             FROM `phieumuon` pm
             LEFT JOIN `chitietmuon` ctm ON ctm.MaPhieu = pm.MaPhieu AND ctm.IsDeleted = 0
             WHERE pm.IsDeleted = 0 AND pm.MaPhieu = ?
             GROUP BY pm.MaPhieu",
            [$maPhieu]
        );
        $soPhieuText = ($pmInfo && !empty($pmInfo['SoPhieu'])) ? (string)$pmInfo['SoPhieu'] : (string)$maPhieu;
        $thietBiText = ($pmInfo && !empty($pmInfo['ThietBi'])) ? (string)$pmInfo['ThietBi'] : '';

        $lastTb = dbQueryOne("SELECT MaThongBao FROM `thongbao` ORDER BY MaThongBao DESC LIMIT 1");
        $nextTbNum = 1;
        if ($lastTb && !empty($lastTb['MaThongBao']) && preg_match('/(\d+)$/', (string)$lastTb['MaThongBao'], $m)) {
            $nextTbNum = intval($m[1]) + 1;
        }
        $maThongBao = 'TB' . str_pad((string)$nextTbNum, 3, '0', STR_PAD_LEFT);

        $tieuDe = 'Phiếu phạt mới';
        $noiDung = "Bạn vừa bị lập phiếu phạt {$maPhat} cho phiếu mượn {$soPhieuText}.";
        if ($thietBiText !== '') {
            $noiDung .= "\nThiết bị: {$thietBiText}.";
        }
        $noiDung .= "\nSố tiền: " . number_format($soTien, 0, ',', '.') . " VNĐ.";
        $noiDung .= "\nLý do: {$lyDo}.";
        $noiDung .= "\nVui lòng thanh toán theo hướng dẫn của nhà trường/Phòng quản lý thiết bị.";

        $sqlThongBao = "INSERT INTO `thongbao` (MaThongBao, MaNguoiDung, TieuDe, NoiDung, Kenh, NgayGui, DaDoc, IsDeleted)
                        VALUES (?, ?, ?, ?, 'Hệ thống', NOW(), 0, 0)";
        $okTb = dbExecute($sqlThongBao, [$maThongBao, $maNguoiDung, $tieuDe, $noiDung]);
        if ($okTb === false) {
            $warn = ' (Lưu ý: không thể tạo thông báo cho người dùng)';
        }
    } catch (Exception $e2) {
        $warn = ' (Lưu ý: không thể tạo thông báo cho người dùng)';
    }

    echo json_encode([
        'success' => true,
        'message' => 'Đã tạo phiếu phạt thành công' . $warn,
        'maPhat' => $maPhat,
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
}
