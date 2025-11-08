<?php
/**
 * Test Database Connection
 * Script kiểm tra kết nối MySQL
 * 
 * @author System Development Team
 * @version 1.0
 */

// Load database connection
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Database Connection</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        .content {
            padding: 30px;
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
            border-left: 4px solid #28a745;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
            border-left: 4px solid #dc3545;
        }
        .info {
            background: #d1ecf1;
            color: #0c5460;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
            border-left: 4px solid #17a2b8;
        }
        .config-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
            font-family: 'Courier New', monospace;
        }
        .config-box strong {
            color: #495057;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }
        th {
            background-color: #667eea;
            color: white;
        }
        tr:hover {
            background-color: #f8f9fa;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #5568d3;
        }
        .btn-success {
            background: #28a745;
        }
        .btn-success:hover {
            background: #218838;
        }
        .status-icon {
            font-size: 1.5rem;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔍 Kiểm Tra Kết Nối Database</h1>
            <p>Hệ thống Mượn Trả Thiết Bị - Đại học Trà Vinh</p>
        </div>
        <div class="content">
            <?php
            try {
                // Test connection
                $conn = getDBConnection();
                
                echo "<div class='success'>";
                echo "✅ <strong>Kết nối database thành công!</strong><br>";
                echo "MySQL Server Version: " . $conn->server_info . "<br>";
                echo "Host Info: " . $conn->host_info;
                echo "</div>";
                
                // Hiển thị cấu hình
                echo "<div class='info'>";
                echo "<strong>📋 Cấu hình kết nối:</strong>";
                echo "<div class='config-box'>";
                echo "Host: " . DB_HOST . "<br>";
                echo "Database: " . DB_NAME . "<br>";
                echo "User: " . DB_USER . "<br>";
                echo "Password: " . (empty(DB_PASS) ? '(trống)' : '***') . "<br>";
                echo "Charset: " . DB_CHARSET;
                echo "</div>";
                echo "</div>";
                
                // Test query - Kiểm tra các bảng
                echo "<h2>📊 Kiểm tra các bảng trong database:</h2>";
                
                $tables = $conn->query("SHOW TABLES");
                $tableList = [];
                
                if ($tables && $tables->num_rows > 0) {
                    echo "<table>";
                    echo "<tr><th>Tên bảng</th><th>Số bản ghi</th><th>Trạng thái</th></tr>";
                    
                    while ($row = $tables->fetch_array()) {
                        $tableName = $row[0];
                        $countResult = $conn->query("SELECT COUNT(*) as count FROM `{$tableName}`");
                        $count = $countResult->fetch_assoc()['count'];
                        
                        echo "<tr>";
                        echo "<td><strong>{$tableName}</strong></td>";
                        echo "<td>{$count}</td>";
                        echo "<td style='color: green;'>✓ Tồn tại</td>";
                        echo "</tr>";
                        
                        $tableList[] = $tableName;
                    }
                    
                    echo "</table>";
                    
                    if (empty($tableList)) {
                        echo "<div class='info'>";
                        echo "⚠️ Database chưa có bảng nào. Bạn cần tạo các bảng để sử dụng hệ thống.";
                        echo "</div>";
                    }
                } else {
                    echo "<div class='info'>";
                    echo "⚠️ Database '{$conn->get_server_info()}' chưa có bảng nào.";
                    echo "</div>";
                }
                
                // Test query đơn giản
                echo "<h2>🔧 Test các hàm database:</h2>";
                
                // Test dbQuery
                $testQuery = "SELECT DATABASE() as current_db, VERSION() as mysql_version";
                $testResult = dbQueryOne($testQuery);
                
                if ($testResult) {
                    echo "<div class='success'>";
                    echo "✅ <strong>Test dbQueryOne():</strong> Thành công<br>";
                    echo "Current Database: " . $testResult['current_db'] . "<br>";
                    echo "MySQL Version: " . $testResult['mysql_version'];
                    echo "</div>";
                }
                
                // Test dbExecute (tạo bảng test nếu chưa có)
                $testTableQuery = "CREATE TABLE IF NOT EXISTS test_connection (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    message VARCHAR(255),
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )";
                
                if (dbExecute($testTableQuery)) {
                    echo "<div class='success'>";
                    echo "✅ <strong>Test dbExecute():</strong> Thành công - Đã tạo bảng test";
                    echo "</div>";
                    
                    // Test INSERT
                    $insertQuery = "INSERT INTO test_connection (message) VALUES (?)";
                    if (dbExecute($insertQuery, ['Test connection successful'])) {
                        echo "<div class='success'>";
                        echo "✅ <strong>Test dbExecute() với parameters:</strong> Thành công - Đã insert dữ liệu test";
                        echo "</div>";
                    }
                    
                    // Xóa bảng test
                    $conn->query("DROP TABLE IF EXISTS test_connection");
                }
                
                echo "<div class='info'>";
                echo "<strong>ℹ️ Thông tin hệ thống:</strong><br>";
                echo "PHP Version: " . phpversion() . "<br>";
                echo "MySQLi Extension: " . (extension_loaded('mysqli') ? 'Enabled' : 'Disabled') . "<br>";
                echo "Connection ID: " . $conn->thread_id;
                echo "</div>";
                
                echo "<div style='text-align: center; margin-top: 30px;'>";
                echo "<a href='../public/index.php' class='btn btn-success'>Về trang chủ</a>";
                echo "</div>";
                
            } catch (Exception $e) {
                echo "<div class='error'>";
                echo "❌ <strong>Lỗi kết nối database!</strong><br>";
                echo "Chi tiết lỗi: " . $e->getMessage() . "<br><br>";
                echo "<strong>Vui lòng kiểm tra:</strong><br>";
                echo "1. MySQL đã được khởi động trong XAMPP chưa?<br>";
                echo "2. Database '" . DB_NAME . "' đã được tạo chưa?<br>";
                echo "3. Thông tin đăng nhập trong config/database.php có đúng không?<br>";
                echo "4. User MySQL có quyền truy cập database không?";
                echo "</div>";
                
                echo "<div class='info'>";
                echo "<strong>📋 Cấu hình hiện tại:</strong>";
                echo "<div class='config-box'>";
                echo "Host: " . DB_HOST . "<br>";
                echo "Database: " . DB_NAME . "<br>";
                echo "User: " . DB_USER . "<br>";
                echo "Password: " . (empty(DB_PASS) ? '(trống)' : '***');
                echo "</div>";
                echo "</div>";
                
                echo "<div class='info'>";
                echo "<strong>💡 Hướng dẫn:</strong><br>";
                echo "1. Mở XAMPP Control Panel<br>";
                echo "2. Khởi động MySQL<br>";
                echo "3. Mở phpMyAdmin (http://localhost/phpmyadmin)<br>";
                echo "4. Tạo database tên '" . DB_NAME . "' nếu chưa có<br>";
                echo "5. Quay lại trang này để kiểm tra lại";
                echo "</div>";
            }
            ?>
        </div>
    </div>
</body>
</html>

