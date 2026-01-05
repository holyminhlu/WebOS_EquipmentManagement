<?php
/**
 * Xử lý tạo yêu cầu mượn thiết bị từ trang Equipment
 */

session_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/user.php';

$UNPAID_FINES_MESSAGE = 'Vui lòng thanh toán toàn bộ phiếu phạt trước khi thực hiện thao tác';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Vui lòng đăng nhập để thực hiện chức năng này'
    ]);
    exit;
}

// Block: users with unpaid fines cannot borrow
if (userHasUnpaidPhieuPhat($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'code' => 'UNPAID_FINES',
        'message' => $UNPAID_FINES_MESSAGE,
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

// Lấy dữ liệu từ POST
$rawMaThietBi = $_POST['maThietBi'] ?? '';
$mucDich = isset($_POST['mucDich']) ? trim($_POST['mucDich']) : 'Phục vụ giảng dạy';
$ngayBatDau = isset($_POST['ngayBatDau']) ? trim($_POST['ngayBatDau']) : date('Y-m-d H:i:s', strtotime('tomorrow 08:00:00'));
$ngayKetThuc = isset($_POST['ngayKetThuc']) ? trim($_POST['ngayKetThuc']) : date('Y-m-d H:i:s', strtotime('tomorrow 17:00:00'));
$ghiChuUser = isset($_POST['ghiChu']) ? trim($_POST['ghiChu']) : '';
$rawMaDiaDiemSuDung = $_POST['maDiaDiemSuDung'] ?? '';
$diaDiemSuDung = isset($_POST['diaDiemSuDung']) ? trim((string)$_POST['diaDiemSuDung']) : ''; // backward-compat

$maDiaDiemSuDung = '';
if (!is_array($rawMaDiaDiemSuDung)) {
    $maDiaDiemSuDung = trim((string)$rawMaDiaDiemSuDung);
}

// Normalize device ids (support single value and array maThietBi[])
$deviceIds = [];
if (is_array($rawMaThietBi)) {
    foreach ($rawMaThietBi as $id) {
        $id = trim((string)$id);
        if ($id !== '') $deviceIds[] = $id;
    }
} else {
    $id = trim((string)$rawMaThietBi);
    if ($id !== '') $deviceIds[] = $id;
}

// Remove duplicates
$deviceIds = array_values(array_unique($deviceIds));

// Validate
if (empty($deviceIds)) {
    echo json_encode([
        'success' => false,
        'message' => 'Vui lòng chọn ít nhất 1 thiết bị'
    ]);
    exit;
}

// Validate thời gian (mượn phải trả trong ngày)
$startTs = strtotime($ngayBatDau);
$endTs = strtotime($ngayKetThuc);
if ($startTs === false || $endTs === false) {
    echo json_encode([
        'success' => false,
        'message' => 'Định dạng thời gian không hợp lệ'
    ]);
    exit;
}

if ($startTs >= $endTs) {
    echo json_encode([
        'success' => false,
        'message' => 'Thời gian không hợp lệ: ngày kết thúc phải sau ngày bắt đầu'
    ]);
    exit;
}

if (date('Y-m-d', $startTs) !== date('Y-m-d', $endTs)) {
    echo json_encode([
        'success' => false,
        'message' => 'Mượn phải trả trong ngày. Vui lòng chọn ngày kết thúc cùng ngày bắt đầu'
    ]);
    exit;
}

// Không ép thời gian bắt đầu phải >= hiện tại: lưu đúng thời gian người dùng chọn.

if ($maDiaDiemSuDung === '' && $diaDiemSuDung === '') {
    echo json_encode([
        'success' => false,
        'message' => 'Vui lòng chọn phòng/địa điểm sử dụng thiết bị'
    ]);
    exit;
}

if ($diaDiemSuDung !== '') {
    $diaDiemSuDung = mb_substr($diaDiemSuDung, 0, 150, 'UTF-8');
}

if (count($deviceIds) > 10) {
    echo json_encode([
        'success' => false,
        'message' => 'Chỉ được chọn tối đa 10 thiết bị cho mỗi yêu cầu'
    ]);
    exit;
}

// Kiểm tra thiết bị có tồn tại và khả dụng không
$placeholders = implode(',', array_fill(0, count($deviceIds), '?'));
$devices = dbQuery(
    "SELECT tb.MaThietBi, tb.MaTrangThai, ltb.TenLoai
     FROM thietbi tb
     JOIN loaithietbi ltb ON tb.MaLoaiThietBi = ltb.MaLoaiThietBi
     WHERE tb.IsDeleted = 0 AND tb.MaThietBi IN ($placeholders)",
    $deviceIds
);

if (count($devices) !== count($deviceIds)) {
    echo json_encode([
        'success' => false,
        'message' => 'Có thiết bị không tồn tại hoặc đã bị xóa'
    ]);
    exit;
}

foreach ($devices as $d) {
    if ((int)$d['MaTrangThai'] !== 1) {
        echo json_encode([
            'success' => false,
            'message' => 'Có thiết bị không khả dụng (đang mượn/bảo trì/hỏng)'
        ]);
        exit;
    }
}

try {
    // Validate room/location if provided
    $ddRow = null;
    if ($maDiaDiemSuDung !== '') {
        if (!ctype_digit($maDiaDiemSuDung)) {
            echo json_encode([
                'success' => false,
                'message' => 'Phòng/địa điểm không hợp lệ'
            ]);
            exit;
        }
        $ddRow = dbQueryOne(
            "SELECT MaDiaDiem, TenDiaDiem
             FROM diadiem
             WHERE IsDeleted = 0 AND MaDiaDiem = ?
             LIMIT 1",
            [(int)$maDiaDiemSuDung]
        );
        if (!$ddRow) {
            echo json_encode([
                'success' => false,
                'message' => 'Phòng/địa điểm không tồn tại hoặc đã bị xóa'
            ]);
            exit;
        }
    }

    // Enforce: cannot have 2 borrow requests in the same room within the same half-day (morning/afternoon)
    // Morning: before 12:00, Afternoon: after 12:00 (spanning noon counts as both)
    if (!$ddRow || !isset($ddRow['MaDiaDiem'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Vui lòng chọn phòng/địa điểm hợp lệ để kiểm tra lịch mượn'
        ]);
        exit;
    }

    $dateYmd = date('Y-m-d', $startTs);
    $noonStr = $dateYmd . ' 12:00:00';
    $noonTs = strtotime($noonStr);
    $coversMorning = ($noonTs !== false) ? ($startTs < $noonTs) : true;
    $coversAfternoon = ($noonTs !== false) ? ($endTs > $noonTs) : true;

    $sessionParts = [];
    $sessionClauses = [];
    $sessionParams = [];
    if ($coversMorning) {
        $sessionParts[] = 'buổi sáng';
        $sessionClauses[] = "ThoiGianBatDau < ?";
        $sessionParams[] = $noonStr;
    }
    if ($coversAfternoon) {
        $sessionParts[] = 'buổi chiều';
        $sessionClauses[] = "ThoiGianKetThuc > ?";
        $sessionParams[] = $noonStr;
    }

    // Safety: if somehow neither session is detected, treat as conflict-protected
    if (empty($sessionClauses)) {
        $sessionClauses[] = "1=1";
    }

    $roomId = (int)$ddRow['MaDiaDiem'];
    // Match exact DD:<id> on its own line to avoid false positives (e.g. DD:1 vs DD:12)
    $roomRegex = '(^|\\r\\n|\\r|\\n)DD:' . $roomId . '(\\r\\n|\\r|\\n|$)';

    $conflictRow = dbQueryOne(
        "SELECT COUNT(*) AS cnt
         FROM yeucaumuon
         WHERE IsDeleted = 0
           AND TrangThai IN ('Chờ duyệt', 'Đã duyệt')
           AND DATE(ThoiGianBatDau) = ?
           AND GhiChu REGEXP ?
           AND (" . implode(' OR ', $sessionClauses) . ")",
        array_merge([$dateYmd, $roomRegex], $sessionParams)
    );

    $conflictCount = ($conflictRow && isset($conflictRow['cnt'])) ? (int)$conflictRow['cnt'] : 0;
    if ($conflictCount > 0) {
        $dmy = date('d/m/Y', $startTs);
        $sessionText = !empty($sessionParts) ? implode(' và ', $sessionParts) : 'buổi này';
        echo json_encode([
            'success' => false,
            'message' => 'Phòng/địa điểm "' . ($ddRow['TenDiaDiem'] ?? ('#' . $roomId)) . '" đã có lịch mượn trong ' . $sessionText . ' ngày ' . $dmy . '. Vui lòng chọn phòng khác hoặc đổi thời gian.'
        ]);
        exit;
    }

    // Tạo mã yêu cầu mượn
    $lastYc = dbQueryOne("SELECT MaYeuCau FROM yeucaumuon ORDER BY MaYeuCau DESC LIMIT 1");
    $nextNum = 1;
    if ($lastYc && !empty($lastYc['MaYeuCau']) && preg_match('/(\d+)$/', $lastYc['MaYeuCau'], $m)) {
        $nextNum = intval($m[1]) + 1;
    }
    $maYeuCau = 'YC' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);

        // Store selected devices & usage location into YeuCauMuon.GhiChu without changing schema
        // Format:
        //   DS_TB:TB001,TB002
        //   DD:<MaDiaDiem>
        // Backward-compat:
        //   DD_SD:<text>
        $ghiChu = 'DS_TB:' . implode(',', $deviceIds);
        if ($ddRow && isset($ddRow['MaDiaDiem'])) {
            $ghiChu .= "\nDD:" . (int)$ddRow['MaDiaDiem'];
        } else {
            $ghiChu .= "\nDD_SD:" . $diaDiemSuDung;
        }
        if (!empty($ghiChuUser)) {
        $ghiChuUser = mb_substr($ghiChuUser, 0, 300, 'UTF-8');
        $ghiChu .= "\nGhi chú: " . $ghiChuUser;
        }
        $ghiChu = mb_substr($ghiChu, 0, 500, 'UTF-8');
    
    // Thêm yêu cầu mượn vào database
        $sql = "INSERT INTO yeucaumuon (MaYeuCau, MaNguoiYeuCau, MucDich, NgayGui, ThoiGianBatDau, ThoiGianKetThuc, TrangThai, GhiChu, IsDeleted) 
            VALUES (?, ?, ?, NOW(), ?, ?, 'Chờ duyệt', ?, 0)";
    
        dbExecute($sql, [$maYeuCau, $_SESSION['user_id'], $mucDich, $ngayBatDau, $ngayKetThuc, $ghiChu]);
    
    // Tạo thông báo cho user
    $lastTb = dbQueryOne("SELECT MaThongBao FROM thongbao ORDER BY MaThongBao DESC LIMIT 1");
    $nextTbNum = 1;
    if ($lastTb && !empty($lastTb['MaThongBao']) && preg_match('/(\d+)$/', $lastTb['MaThongBao'], $m)) {
        $nextTbNum = intval($m[1]) + 1;
    }
    $maThongBao = 'TB' . str_pad($nextTbNum, 3, '0', STR_PAD_LEFT);
    
    $noiDung = 'Yêu cầu mượn ' . count($deviceIds) . ' thiết bị của bạn đã được gửi thành công. Vui lòng chờ quản trị viên phê duyệt.';
    
    $sqlThongBao = "INSERT INTO thongbao (MaThongBao, MaNguoiDung, TieuDe, NoiDung, Kenh, NgayGui, DaDoc, IsDeleted) 
                    VALUES (?, ?, 'Yêu cầu mượn đã gửi', ?, 'Hệ thống', NOW(), 0, 0)";
    
    dbExecute($sqlThongBao, [$maThongBao, $_SESSION['user_id'], $noiDung]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Đã gửi yêu cầu mượn ' . count($deviceIds) . " thiết bị thành công!\n\nYêu cầu của bạn đang chờ quản trị viên phê duyệt.",
        'maYeuCau' => $maYeuCau,
        'soThietBi' => count($deviceIds)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi hệ thống: ' . $e->getMessage()
    ]);
}
?>
