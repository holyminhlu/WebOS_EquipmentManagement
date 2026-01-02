<?php
/**
 * Xử lý hủy yêu cầu mượn thiết bị
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
$maYeuCau = isset($_POST['maYeuCau']) ? trim($_POST['maYeuCau']) : '';

// Validate
if (empty($maYeuCau)) {
    echo json_encode([
        'success' => false,
        'message' => 'Mã yêu cầu không hợp lệ'
    ]);
    exit;
}

try {
    // Kiểm tra yêu cầu có tồn tại và thuộc về user này không
    $yeuCau = dbQueryOne("SELECT * FROM yeucaumuon WHERE MaYeuCau = ? AND MaNguoiYeuCau = ? AND IsDeleted = 0", 
                         [$maYeuCau, $_SESSION['user_id']]);
    
    if (!$yeuCau) {
        echo json_encode([
            'success' => false,
            'message' => 'Không tìm thấy yêu cầu hoặc bạn không có quyền hủy yêu cầu này'
        ]);
        exit;
    }
    
    // Kiểm tra trạng thái - chỉ cho phép hủy khi đang "Chờ duyệt"
    if ($yeuCau['TrangThai'] !== 'Chờ duyệt') {
        echo json_encode([
            'success' => false,
            'message' => 'Chỉ có thể hủy yêu cầu đang ở trạng thái "Chờ duyệt"'
        ]);
        exit;
    }
    
    // Cập nhật trạng thái thành "Đã hủy"
    $sql = "UPDATE yeucaumuon SET TrangThai = 'Đã hủy' WHERE MaYeuCau = ?";
    dbExecute($sql, [$maYeuCau]);
    
    // Tạo thông báo
    $lastTb = dbQueryOne("SELECT MaThongBao FROM thongbao ORDER BY MaThongBao DESC LIMIT 1");
    $nextTbNum = 1;
    if ($lastTb && !empty($lastTb['MaThongBao']) && preg_match('/(\d+)$/', $lastTb['MaThongBao'], $m)) {
        $nextTbNum = intval($m[1]) + 1;
    }
    $maThongBao = 'TB' . str_pad($nextTbNum, 3, '0', STR_PAD_LEFT);
    
    $noiDung = "Bạn đã hủy yêu cầu mượn thiết bị mã {$maYeuCau} thành công.";
    
    $sqlThongBao = "INSERT INTO thongbao (MaThongBao, MaNguoiDung, TieuDe, NoiDung, Kenh, NgayGui, DaDoc, IsDeleted) 
                    VALUES (?, ?, 'Yêu cầu đã bị hủy', ?, 'Hệ thống', NOW(), 0, 0)";
    
    dbExecute($sqlThongBao, [$maThongBao, $_SESSION['user_id'], $noiDung]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Đã hủy yêu cầu mượn thành công!'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi hệ thống: ' . $e->getMessage()
    ]);
}
?>
