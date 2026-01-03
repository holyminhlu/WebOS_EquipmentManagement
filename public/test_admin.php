<?php
/**
 * Test: Kiểm tra admin trong database
 */
session_start();
require_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    die('Vui lòng đăng nhập vào dashboard trước');
}

echo "<h2>Kiểm tra danh sách Admin</h2>";

$conn = getDBConnection();

// Lấy danh sách admin
$sqlAdmins = "SELECT MaNguoiDung, HoTen, Email, MaVaiTro, HoatDong, IsDeleted 
              FROM nguoidung 
              WHERE MaVaiTro IN (1, 1101)
              ORDER BY MaNguoiDung";
$result = $conn->query($sqlAdmins);

if ($result && $result->num_rows > 0) {
    echo "<h3>Tìm thấy " . $result->num_rows . " admin:</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f0f0f0;'>";
    echo "<th style='padding: 8px;'>Mã</th>";
    echo "<th style='padding: 8px;'>Họ tên</th>";
    echo "<th style='padding: 8px;'>Email</th>";
    echo "<th style='padding: 8px;'>MaVaiTro</th>";
    echo "<th style='padding: 8px;'>Hoạt động</th>";
    echo "<th style='padding: 8px;'>IsDeleted</th>";
    echo "<th style='padding: 8px;'>Nhận thông báo?</th>";
    echo "</tr>";
    
    while ($admin = $result->fetch_assoc()) {
        $canReceive = ($admin['HoatDong'] == 1 && $admin['IsDeleted'] == 0);
        $rowColor = $canReceive ? '#dfd' : '#fdd';
        
        echo "<tr style='background: $rowColor;'>";
        echo "<td style='padding: 8px;'>{$admin['MaNguoiDung']}</td>";
        echo "<td style='padding: 8px;'>{$admin['HoTen']}</td>";
        echo "<td style='padding: 8px;'>{$admin['Email']}</td>";
        echo "<td style='padding: 8px;'>{$admin['MaVaiTro']}</td>";
        echo "<td style='padding: 8px;'>" . ($admin['HoatDong'] ? '✓ Active' : '✗ Inactive') . "</td>";
        echo "<td style='padding: 8px;'>" . ($admin['IsDeleted'] ? '✗ Deleted' : '✓ OK') . "</td>";
        echo "<td style='padding: 8px; font-weight: bold;'>" . ($canReceive ? '✅ CÓ' : '❌ KHÔNG') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Đếm admin đủ điều kiện
    $sqlCount = "SELECT COUNT(*) as cnt FROM nguoidung 
                 WHERE MaVaiTro IN (1, 1101) 
                 AND HoatDong = 1 
                 AND IsDeleted = 0";
    $countResult = $conn->query($sqlCount);
    $count = 0;
    if ($countResult) {
        $row = $countResult->fetch_assoc();
        $count = $row['cnt'];
    }
    
    echo "<hr>";
    echo "<h3>Kết quả:</h3>";
    echo "<p><strong>Số admin đủ điều kiện nhận thông báo:</strong> $count admin</p>";
    
    if ($count == 0) {
        echo "<p style='color: red; font-weight: bold;'>⚠️ KHÔNG CÓ ADMIN NÀO ĐỦ ĐIỀU KIỆN! Tin nhắn liên hệ sẽ không được gửi.</p>";
        echo "<p>Cần bật trạng thái HoatDong = 1 cho ít nhất một admin.</p>";
    } else {
        echo "<p style='color: green; font-weight: bold;'>✅ OK! Tin nhắn liên hệ sẽ được gửi đến $count admin.</p>";
    }
} else {
    echo "<p style='color: red; font-weight: bold;'>KHÔNG TÌM THẤY ADMIN NÀO!</p>";
    echo "<p>Cần có ít nhất một user với MaVaiTro = 1 hoặc 1101</p>";
}

echo "<hr>";
echo "<p><a href='dashboard.php'>← Quay lại Dashboard</a></p>";
echo "<p><a href='contact.php'>→ Đến trang Liên hệ</a></p>";
