<?php
/**
 * Test: Kiểm tra thông báo liên hệ trong database
 */
session_start();
require_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    die('Vui lòng đăng nhập vào dashboard trước');
}

echo "<h2>Kiểm tra Thông Báo Liên Hệ</h2>";

$conn = getDBConnection();

// Kiểm tra tất cả thông báo
$sqlAll = "SELECT COUNT(*) as total FROM thongbao WHERE IsDeleted = 0";
$resultAll = $conn->query($sqlAll);
$totalAll = 0;
if ($resultAll) {
    $row = $resultAll->fetch_assoc();
    $totalAll = $row['total'];
}

// Kiểm tra thông báo liên hệ
$sqlContact = "SELECT COUNT(*) as total FROM thongbao WHERE IsDeleted = 0 AND Kenh = 'Liên hệ'";
$resultContact = $conn->query($sqlContact);
$totalContact = 0;
if ($resultContact) {
    $row = $resultContact->fetch_assoc();
    $totalContact = $row['total'];
}

echo "<h3>Thống kê:</h3>";
echo "<p><strong>Tổng số thông báo:</strong> $totalAll</p>";
echo "<p><strong>Thông báo liên hệ:</strong> $totalContact</p>";

// Lấy 10 thông báo liên hệ gần nhất
$sql = "SELECT MaThongBao, MaNguoiDung, TieuDe, NoiDung, DaDoc, NgayGui, Kenh 
        FROM thongbao 
        WHERE IsDeleted = 0 AND Kenh = 'Liên hệ'
        ORDER BY NgayGui DESC 
        LIMIT 10";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    echo "<hr>";
    echo "<h3>10 tin nhắn liên hệ gần nhất:</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f0f0f0;'>";
    echo "<th style='padding: 8px;'>Mã TB</th>";
    echo "<th style='padding: 8px;'>Người nhận</th>";
    echo "<th style='padding: 8px;'>Tiêu đề</th>";
    echo "<th style='padding: 8px;'>Ngày gửi</th>";
    echo "<th style='padding: 8px;'>Đã đọc</th>";
    echo "</tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td style='padding: 8px;'>{$row['MaThongBao']}</td>";
        echo "<td style='padding: 8px;'>{$row['MaNguoiDung']}</td>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($row['TieuDe']) . "</td>";
        echo "<td style='padding: 8px;'>{$row['NgayGui']}</td>";
        echo "<td style='padding: 8px;'>" . ($row['DaDoc'] ? '✓ Đã đọc' : '✗ Chưa đọc') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: orange; font-weight: bold;'>⚠️ Chưa có tin nhắn liên hệ nào trong database!</p>";
    echo "<p>Hãy thử gửi tin nhắn từ <a href='contact.php'>trang liên hệ</a> để test.</p>";
}

// Kiểm tra admin hiện tại có thông báo gì không
echo "<hr>";
echo "<h3>Thông báo của bạn (User: {$_SESSION['user_id']}):</h3>";
$sqlMyNotif = "SELECT COUNT(*) as total FROM thongbao 
               WHERE IsDeleted = 0 AND MaNguoiDung = ?";
$stmt = $conn->prepare($sqlMyNotif);
$stmt->bind_param('s', $_SESSION['user_id']);
$stmt->execute();
$resultMy = $stmt->get_result();
$myTotal = 0;
if ($resultMy) {
    $row = $resultMy->fetch_assoc();
    $myTotal = $row['total'];
}
echo "<p><strong>Tổng thông báo của bạn:</strong> $myTotal</p>";

if ($myTotal > 0) {
    $sqlMyList = "SELECT MaThongBao, TieuDe, NgayGui, Kenh, DaDoc 
                  FROM thongbao 
                  WHERE IsDeleted = 0 AND MaNguoiDung = ?
                  ORDER BY NgayGui DESC 
                  LIMIT 5";
    $stmt2 = $conn->prepare($sqlMyList);
    $stmt2->bind_param('s', $_SESSION['user_id']);
    $stmt2->execute();
    $resultMyList = $stmt2->get_result();
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f0f0f0;'>";
    echo "<th style='padding: 8px;'>Tiêu đề</th>";
    echo "<th style='padding: 8px;'>Kênh</th>";
    echo "<th style='padding: 8px;'>Ngày gửi</th>";
    echo "<th style='padding: 8px;'>Đã đọc</th>";
    echo "</tr>";
    
    while ($row = $resultMyList->fetch_assoc()) {
        $color = ($row['Kenh'] == 'Liên hệ') ? '#fffacd' : '#fff';
        echo "<tr style='background: $color;'>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($row['TieuDe']) . "</td>";
        echo "<td style='padding: 8px;'><strong>{$row['Kenh']}</strong></td>";
        echo "<td style='padding: 8px;'>{$row['NgayGui']}</td>";
        echo "<td style='padding: 8px;'>" . ($row['DaDoc'] ? '✓' : '✗') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "<hr>";
echo "<p><a href='dashboard.php'>← Quay lại Dashboard</a></p>";
echo "<p><a href='contact.php'>→ Thử gửi tin nhắn</a></p>";
