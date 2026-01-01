<?php
/**
 * Database Connection Handler
 * Xử lý kết nối MySQL sử dụng MySQLi
 * 
 * @author System Development Team
 * @version 1.0
 */

// Load database configuration
require_once __DIR__ . '/../config/database.php';

/**
 * Kết nối đến MySQL database
 * @return mysqli|false Kết nối MySQLi hoặc false nếu lỗi
 */
function getDBConnection() {
    static $connection = null;
    
    // Nếu đã có kết nối, trả về kết nối đó (Singleton pattern)
    if ($connection !== null) {
        return $connection;
    }
    
    // Tạo kết nối mới
    $connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Kiểm tra lỗi kết nối
    if ($connection->connect_error) {
        error_log("Database Connection Error: " . $connection->connect_error);
        die("Lỗi kết nối cơ sở dữ liệu: " . $connection->connect_error);
    }
    
    // Thiết lập charset UTF-8
    $connection->set_charset(DB_CHARSET);
    $connection->query("SET NAMES 'utf8mb4'");
    $connection->query("SET CHARACTER SET utf8mb4");
    
    return $connection;
}

/**
 * Thực thi query SELECT và trả về kết quả dạng mảng
 * @param string $sql Câu lệnh SQL
 * @param array $params Tham số cho prepared statement (optional)
 * @return array Mảng kết quả
 */
function dbQuery($sql, $params = []) {
    $conn = getDBConnection();
    $results = [];
    
    // Nếu có tham số, sử dụng prepared statement
    if (!empty($params)) {
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            error_log("Prepare failed: " . $conn->error);
            return [];
        }
        
        // Bind parameters nếu có
        if (!empty($params)) {
            $types = '';
            $values = [];
            
            foreach ($params as $key => $value) {
                if (is_int($value)) {
                    $types .= 'i';
                } elseif (is_float($value)) {
                    $types .= 'd';
                } else {
                    $types .= 's';
                }
                $values[] = $value;
            }
            
            $stmt->bind_param($types, ...$values);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $results[] = $row;
        }
        
        $stmt->close();
    } else {
        // Không có tham số, thực thi trực tiếp
        $result = $conn->query($sql);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $results[] = $row;
            }
            $result->free();
        } else {
            error_log("Query failed: " . $conn->error);
        }
    }
    
    return $results;
}

/**
 * Thực thi query và trả về một dòng kết quả
 * @param string $sql Câu lệnh SQL
 * @param array $params Tham số cho prepared statement (optional)
 * @return array|false Một dòng kết quả hoặc false
 */
function dbQueryOne($sql, $params = []) {
    $results = dbQuery($sql, $params);
    return !empty($results) ? $results[0] : false;
}

/**
 * Thực thi query INSERT, UPDATE, DELETE
 * @param string $sql Câu lệnh SQL
 * @param array $params Tham số cho prepared statement (optional)
 * @return int|false Số dòng bị ảnh hưởng hoặc false nếu lỗi
 */
function dbExecute($sql, $params = []) {
    $conn = getDBConnection();
    
    // Nếu có tham số, sử dụng prepared statement
    if (!empty($params)) {
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            error_log("Prepare failed: " . $conn->error);
            return false;
        }
        
        // Bind parameters
        $types = '';
        $values = [];
        
        foreach ($params as $key => $value) {
            if (is_int($value)) {
                $types .= 'i';
            } elseif (is_float($value)) {
                $types .= 'd';
            } else {
                $types .= 's';
            }
            $values[] = $value;
        }
        
        $stmt->bind_param($types, ...$values);
        $stmt->execute();
        $affected_rows = $stmt->affected_rows;
        $stmt->close();
        
        return $affected_rows;
    } else {
        // Không có tham số, thực thi trực tiếp
        if ($conn->query($sql)) {
            return $conn->affected_rows;
        } else {
            error_log("Execute failed: " . $conn->error);
            return false;
        }
    }
}

/**
 * Lấy ID của bản ghi vừa được INSERT
 * @return int|string ID vừa được insert
 */
function dbLastInsertId() {
    $conn = getDBConnection();
    return $conn->insert_id;
}

/**
 * Escapes special characters trong chuỗi để tránh SQL injection
 * @param string $string Chuỗi cần escape
 * @return string Chuỗi đã được escape
 */
function dbEscape($string) {
    $conn = getDBConnection();
    return $conn->real_escape_string($string);
}

/**
 * Đóng kết nối database (thường không cần thiết do PHP tự đóng)
 */
function dbClose() {
    $conn = getDBConnection();
    $conn->close();
}

