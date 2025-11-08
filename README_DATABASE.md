# Hướng Dẫn Sử Dụng Database Connection

## 📋 Tổng Quan

File kết nối database MySQL đã được tạo để sử dụng với XAMPP.

## 📁 Cấu Trúc File

- `config/database.php` - File cấu hình kết nối database
- `includes/db.php` - File xử lý kết nối và các hàm database

## ⚙️ Cấu Hình

### 1. Cấu hình Database

Mở file `config/database.php` và điều chỉnh các thông tin:

```php
define('DB_HOST', 'localhost');      // Host MySQL
define('DB_NAME', 'equipment_management');  // Tên database
define('DB_USER', 'root');           // Username MySQL
define('DB_PASS', '');               // Password MySQL (XAMPP mặc định là trống)
define('DB_CHARSET', 'utf8mb4');     // Charset
```

### 2. Tạo Database

1. Mở XAMPP Control Panel
2. Khởi động MySQL
3. Mở phpMyAdmin (http://localhost/phpmyadmin)
4. Tạo database mới tên `equipment_management`
5. Chọn charset: `utf8mb4_unicode_ci`

## 🔧 Sử Dụng

### 1. Kết nối Database

```php
require_once __DIR__ . '/../includes/db.php';

// Lấy kết nối
$conn = getDBConnection();
```

### 2. Thực thi Query SELECT

```php
// Query đơn giản (không có tham số)
$results = dbQuery("SELECT * FROM equipment");

// Query với tham số (an toàn hơn)
$results = dbQuery("SELECT * FROM equipment WHERE category = ?", ['Máy chiếu']);

// Lấy một dòng
$equipment = dbQueryOne("SELECT * FROM equipment WHERE id = ?", [1]);
```

### 3. Thực thi Query INSERT, UPDATE, DELETE

```php
// INSERT
$sql = "INSERT INTO equipment (name, category, description) VALUES (?, ?, ?)";
$result = dbExecute($sql, ['Máy chiếu BenQ', 'Máy chiếu', 'Mô tả...']);

if ($result) {
    $newId = dbLastInsertId();
    echo "Đã thêm với ID: " . $newId;
}

// UPDATE
$sql = "UPDATE equipment SET name = ? WHERE id = ?";
$result = dbExecute($sql, ['Tên mới', 1]);

// DELETE
$sql = "DELETE FROM equipment WHERE id = ?";
$result = dbExecute($sql, [1]);
```

### 4. Escape String (tránh SQL Injection)

```php
$safeString = dbEscape($_POST['input']);
```

## 🧪 Kiểm Tra Kết Nối

Truy cập: `http://localhost/WebOS_EquipmentManagement/WebOS_EquipmentManagement/database/test_connection.php`

File này sẽ:
- Kiểm tra kết nối database
- Hiển thị thông tin cấu hình
- Liệt kê các bảng trong database
- Test các hàm database

## 📝 Ví Dụ Sử Dụng

### Ví dụ 1: Lấy danh sách thiết bị

```php
require_once __DIR__ . '/../includes/db.php';

function getEquipmentList() {
    $sql = "SELECT * FROM equipment ORDER BY name ASC";
    return dbQuery($sql);
}

$equipment = getEquipmentList();
foreach ($equipment as $item) {
    echo $item['name'] . "<br>";
}
```

### Ví dụ 2: Tìm kiếm thiết bị

```php
function searchEquipment($keyword) {
    $sql = "SELECT * FROM equipment WHERE name LIKE ? OR description LIKE ?";
    $search = "%{$keyword}%";
    return dbQuery($sql, [$search, $search]);
}

$results = searchEquipment('laptop');
```

### Ví dụ 3: Thêm thiết bị mới

```php
function addEquipment($name, $category, $description, $total, $available) {
    $sql = "INSERT INTO equipment (name, category, description, total_quantity, available_quantity) 
            VALUES (?, ?, ?, ?, ?)";
    return dbExecute($sql, [$name, $category, $description, $total, $available]);
}

if (addEquipment('Laptop Dell', 'Laptop', 'Mô tả...', 10, 8)) {
    echo "Thêm thành công!";
}
```

## ⚠️ Lưu Ý

1. **Bảo mật**: Luôn sử dụng prepared statements với tham số để tránh SQL injection
2. **Error Handling**: Các hàm đã có xử lý lỗi cơ bản, có thể mở rộng thêm
3. **Connection**: Kết nối được quản lý theo Singleton pattern, tự động đóng khi script kết thúc
4. **Charset**: Đảm bảo database và kết nối sử dụng `utf8mb4` để hỗ trợ tiếng Việt

## 🐛 Xử Lý Lỗi

Nếu gặp lỗi kết nối:

1. Kiểm tra MySQL đã khởi động trong XAMPP chưa
2. Kiểm tra database đã được tạo chưa
3. Kiểm tra thông tin đăng nhập trong `config/database.php`
4. Kiểm tra quyền truy cập của user MySQL
5. Xem log lỗi trong file `error_log` (nếu có)

## 📚 Tài Liệu Tham Khảo

- [MySQLi Documentation](https://www.php.net/manual/en/book.mysqli.php)
- [XAMPP Documentation](https://www.apachefriends.org/docs/)

