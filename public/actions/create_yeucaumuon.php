<?php
/**
 * Xử lý tạo yêu cầu mượn thiết bị từ trang Equipment
 */

session_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../includes/db.php';

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

// Lấy dữ liệu từ POST
$maThietBi = isset($_POST['maThietBi']) ? trim($_POST['maThietBi']) : '';
$mucDich = isset($_POST['mucDich']) ? trim($_POST['mucDich']) : 'Phục vụ giảng dạy';
$ngayBatDau = isset($_POST['ngayBatDau']) ? trim($_POST['ngayBatDau']) : date('Y-m-d H:i:s', strtotime('+1 day'));
$ngayKetThuc = isset($_POST['ngayKetThuc']) ? trim($_POST['ngayKetThuc']) : date('Y-m-d H:i:s', strtotime('+8 days'));

// Validate
if (empty($maThietBi)) {
    echo json_encode([
        'success' => false,
        'message' => 'Mã thiết bị không hợp lệ'
    ]);
    exit;
}

// Kiểm tra thiết bị có tồn tại không
$thietBi = dbQueryOne("SELECT tb.*, ltb.TenLoai FROM thietbi tb 
                        JOIN loaithietbi ltb ON tb.MaLoaiThietBi = ltb.MaLoaiThietBi 
                        WHERE tb.MaThietBi = ? AND tb.IsDeleted = 0", [$maThietBi]);

if (!$thietBi) {
    echo json_encode([
        'success' => false,
        'message' => 'Thiết bị không tồn tại'
    ]);
    exit;
}

// Kiểm tra trạng thái thiết bị (1 = Sẵn sàng)
if ($thietBi['MaTrangThai'] != 1) {
    $trangThaiText = [
        2 => 'đang được mượn',
        3 => 'đang bảo trì',
        4 => 'đã hỏng'
    ];
    echo json_encode([
        'success' => false,
        'message' => 'Thiết bị ' . ($trangThaiText[$thietBi['MaTrangThai']] ?? 'không khả dụng')
    ]);
    exit;
}

try {
    // Tạo mã yêu cầu mượn
    $lastYc = dbQueryOne("SELECT MaYeuCau FROM yeucaumuon ORDER BY MaYeuCau DESC LIMIT 1");
    $nextNum = 1;
    if ($lastYc && !empty($lastYc['MaYeuCau']) && preg_match('/(\d+)$/', $lastYc['MaYeuCau'], $m)) {
        $nextNum = intval($m[1]) + 1;
    }
    $maYeuCau = 'YC' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);
    
    // Thêm yêu cầu mượn vào database
    $sql = "INSERT INTO yeucaumuon (MaYeuCau, MaNguoiYeuCau, MucDich, NgayGui, NgayDuKienBatDau, NgayDuKienKetThuc, TrangThai, IsDeleted) 
            VALUES (?, ?, ?, NOW(), ?, ?, 'Chờ duyệt', 0)";
    
    dbExecute($sql, [$maYeuCau, $_SESSION['user_id'], $mucDich, $ngayBatDau, $ngayKetThuc]);
    
    // Tạo thông báo cho user
    $lastTb = dbQueryOne("SELECT MaThongBao FROM thongbao ORDER BY MaThongBao DESC LIMIT 1");
    $nextTbNum = 1;
    if ($lastTb && !empty($lastTb['MaThongBao']) && preg_match('/(\d+)$/', $lastTb['MaThongBao'], $m)) {
        $nextTbNum = intval($m[1]) + 1;
    }
    $maThongBao = 'TB' . str_pad($nextTbNum, 3, '0', STR_PAD_LEFT);
    
    $noiDung = "Yêu cầu mượn thiết bị {$thietBi['TenLoai']} (Mã: {$maThietBi}) của bạn đã được gửi thành công. Vui lòng chờ quản trị viên phê duyệt.";
    
    $sqlThongBao = "INSERT INTO thongbao (MaThongBao, MaNguoiDung, TieuDe, NoiDung, Kenh, NgayGui, DaDoc, IsDeleted) 
                    VALUES (?, ?, 'Yêu cầu mượn đã gửi', ?, 'Hệ thống', NOW(), 0, 0)";
    
    dbExecute($sqlThongBao, [$maThongBao, $_SESSION['user_id'], $noiDung]);
    
    echo json_encode([
        'success' => true,
        'message' => "Đã gửi yêu cầu mượn thiết bị \"{$thietBi['TenLoai']}\" thành công!\n\nYêu cầu của bạn đang chờ quản trị viên phê duyệt. Bạn sẽ nhận được thông báo khi yêu cầu được xử lý.",
        'maYeuCau' => $maYeuCau,
        'tenThietBi' => $thietBi['TenLoai']
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi hệ thống: ' . $e->getMessage()
    ]);
}
?>
