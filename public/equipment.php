<?php
/**
 * Trang Thiết Bị - Hệ thống mượn trả thiết bị giảng dạy
 * Đại học Trà Vinh
 */

session_start();
header('Content-Type: text/html; charset=utf-8');
require_once __DIR__ . '/../includes/db.php';

$isLoggedIn = isset($_SESSION['user_id']);
$userData = null;
if ($isLoggedIn) {
    require_once __DIR__ . '/../includes/user.php';
    $userData = getUserInfo($_SESSION['user_id']);
}

$hasUnpaidFines = false;
if ($isLoggedIn) {
    $hasUnpaidFines = userHasUnpaidPhieuPhat($_SESSION['user_id']);
}

// Lấy tham số tìm kiếm và lọc
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$loaiThietBi = isset($_GET['loai']) ? trim($_GET['loai']) : '';
$trangThai = isset($_GET['trangthai']) ? trim($_GET['trangthai']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

// Xây dựng câu query
$where = ["tb.IsDeleted = 0"];
$params = [];

if (!empty($search)) {
    $where[] = "(tb.MaThietBi LIKE ? OR tb.MaTaiSan LIKE ? OR tb.SoSerial LIKE ? OR ltb.TenLoai LIKE ?)";
    $searchParam = "%$search%";
    $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam]);
}

if (!empty($loaiThietBi)) {
    $where[] = "tb.MaLoaiThietBi = ?";
    $params[] = $loaiThietBi;
}

if (!empty($trangThai)) {
    $where[] = "tb.MaTrangThai = ?";
    $params[] = $trangThai;
}

$whereClause = implode(" AND ", $where);

// Đếm tổng số thiết bị
$countSql = "SELECT COUNT(*) as total 
             FROM ThietBi tb
             LEFT JOIN LoaiThietBi ltb ON tb.MaLoaiThietBi = ltb.MaLoaiThietBi
             WHERE $whereClause";
$totalResult = dbQueryOne($countSql, $params);
$total = $totalResult['total'];
$totalPages = ceil($total / $limit);

// Lấy danh sách thiết bị
$sql = "SELECT tb.*, ltb.TenLoai, ltb.DanhMuc, ltb.ThoiHanMuonMacDinh,
               dd.TenDiaDiem, dd.DiaChi,
               tttb.TenTrangThai
        FROM ThietBi tb
        LEFT JOIN LoaiThietBi ltb ON tb.MaLoaiThietBi = ltb.MaLoaiThietBi
        LEFT JOIN DiaDiem dd ON tb.MaDiaDiem = dd.MaDiaDiem
        LEFT JOIN TrangThaiThietBi tttb ON tb.MaTrangThai = tttb.MaTrangThai
        WHERE $whereClause
        ORDER BY tb.MaTrangThai ASC, tb.MaThietBi DESC
        LIMIT $limit OFFSET $offset";

$equipments = dbQuery($sql, $params);

// Lấy danh sách loại thiết bị với số lượng
$loaiThietBiList = dbQuery("
    SELECT ltb.*, 
           COUNT(tb.MaThietBi) as SoLuong,
           SUM(CASE WHEN tb.MaTrangThai = 1 THEN 1 ELSE 0 END) as SoLuongKhaDung
    FROM LoaiThietBi ltb
    LEFT JOIN ThietBi tb ON ltb.MaLoaiThietBi = tb.MaLoaiThietBi AND tb.IsDeleted = 0
    WHERE ltb.IsDeleted = 0
    GROUP BY ltb.MaLoaiThietBi
    ORDER BY ltb.TenLoai
");

// Lấy danh sách trạng thái
$trangThaiList = dbQuery("SELECT * FROM TrangThaiThietBi ORDER BY MaTrangThai");

// Lấy danh sách khu + phòng/địa điểm (dùng cho form mượn)
$khuList = dbQuery(
    "SELECT DISTINCT Khu
     FROM DiaDiem
     WHERE IsDeleted = 0 AND Khu IS NOT NULL AND TRIM(Khu) <> ''
     ORDER BY Khu ASC"
);

$defaultKhu = '';
if (!empty($khuList) && isset($khuList[0]['Khu'])) {
    $defaultKhu = trim((string)$khuList[0]['Khu']);
}

$diaDiemList = [];
if ($defaultKhu !== '') {
    $diaDiemList = dbQuery(
        "SELECT MaDiaDiem, TenDiaDiem
         FROM DiaDiem
         WHERE IsDeleted = 0 AND Khu = ?
         ORDER BY TenDiaDiem ASC",
        [$defaultKhu]
    );
} else {
    // Fallback: nếu chưa có dữ liệu Khu, hiển thị toàn bộ phòng/địa điểm như cũ
    $diaDiemList = dbQuery(
        "SELECT MaDiaDiem, TenDiaDiem
         FROM DiaDiem
         WHERE IsDeleted = 0
         ORDER BY TenDiaDiem ASC"
    );
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh Sách Thiết Bị - Hệ thống mượn trả thiết bị</title>
    <link rel="stylesheet" href="css/styleAbout.css?v=<?php echo time(); ?>">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .equipment-hero {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 3rem 0;
            text-align: center;
        }
        
        .equipment-hero h1 {
            font-size: 2.2rem;
            margin-bottom: 0.5rem;
            font-weight: 700;
        }
        
        .equipment-hero p {
            font-size: 1.1rem;
            opacity: 0.95;
        }
        
        /* User info box styles */
        .user-info-box {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: linear-gradient(135deg, #4169E1 0%, #1e40af 100%);
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
            color: white;
            font-weight: 500;
            box-shadow: 0 2px 6px rgba(65, 105, 225, 0.25);
            margin-right: 0.75rem;
        }
        .user-info-box .user-avatar {
            width: 26px;
            height: 26px;
            border-radius: 50%;
            background: white;
            color: #4169E1;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .user-info-box .user-details {
            display: flex;
            flex-direction: column;
            line-height: 1.15;
        }
        .user-info-box .user-name {
            font-size: 0.85rem;
            font-weight: 600;
        }
        .user-info-box .user-role {
            font-size: 0.7rem;
            opacity: 0.85;
        }
        
        .equipment-section {
            padding: 3rem 0;
            background-color: var(--bg-light);
        }

        /* Category Filter Tabs */
        .category-tabs {
            background: white;
            padding: 1.5rem 2rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
        }

        .category-tabs-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e0e7ff;
        }

        .category-tabs-header h3 {
            font-size: 1.3rem;
            color: var(--text-dark);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin: 0;
        }

        .category-tabs-header h3 i {
            color: var(--primary-color);
        }

        .view-all-btn {
            padding: 0.5rem 1rem;
            background: linear-gradient(135deg, #f8f9ff 0%, #e8edff 100%);
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
            border-radius: 20px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .view-all-btn:hover {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
            transform: translateY(-2px);
        }

        .category-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
        }

        .category-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 1.5rem 1rem;
            background: linear-gradient(135deg, #f8f9ff 0%, #e8edff 100%);
            border: 2px solid transparent;
            border-radius: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            color: var(--text-dark);
        }

        .category-item:hover {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
        }

        .category-item.active {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
            border-color: var(--primary-dark);
        }

        .category-icon {
            font-size: 2.5rem;
            margin-bottom: 0.75rem;
        }

        .category-name {
            font-weight: 600;
            font-size: 0.95rem;
            text-align: center;
            margin-bottom: 0.5rem;
        }

        .category-count {
            font-size: 0.85rem;
            opacity: 0.9;
        }

        .category-item:hover .category-count,
        .category-item.active .category-count {
            opacity: 1;
        }
        
        .search-filter-bar {
            background: white;
            padding: 2rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
        }
        
        .search-form {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr auto;
            gap: 1rem;
            align-items: end;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
        }
        
        .form-group label {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--text-color);
        }
        
        .form-group input,
        .form-group select {
            padding: 0.75rem;
            border: 2px solid var(--border-color);
            border-radius: var(--border-radius);
            font-size: 0.95rem;
            transition: var(--transition);
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary-color);
        }
        
        .btn-search {
            padding: 0.75rem 2rem;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
            border: none;
            border-radius: var(--border-radius);
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
        }
        
        .btn-search:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(44, 90, 160, 0.3);
        }
        
        .equipment-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            text-align: center;
            transition: var(--transition);
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .stat-label {
            color: var(--text-light);
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }
        
        .equipment-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
        }
        
        .equipment-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            transition: var(--transition);
            border: 2px solid transparent;
        }
        
        .equipment-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary-color);
        }
        
        .equipment-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 1.5rem;
            text-align: center;
        }
        
        .equipment-icon {
            font-size: 3rem;
            margin-bottom: 0.5rem;
        }
        
        .equipment-name {
            font-size: 1rem;
            font-weight: 600;
            margin: 0;
            word-wrap: break-word;
            overflow-wrap: break-word;
            hyphens: auto;
        }
        
        .equipment-body {
            padding: 1.5rem;
        }
        
        .equipment-info {
            margin-bottom: 1rem;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid var(--border-color);
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            color: var(--text-light);
            font-size: 0.85rem;
        }
        
        .info-value {
            font-weight: 600;
            color: var(--text-color);
            font-size: 0.85rem;
        }
        
        .status-badge {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .status-available {
            background: #d4edda;
            color: #155724;
        }
        
        .status-borrowed {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-maintenance {
            background: #f8d7da;
            color: #721c24;
        }
        
        .status-broken {
            background: #d6d8db;
            color: #383d41;
        }
        
        .equipment-footer {
            padding: 0 1.5rem 1.5rem;
        }
        
        .btn-request {
            width: 100%;
            padding: 0.75rem;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
            border: none;
            border-radius: var(--border-radius);
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
        }
        
        .btn-request:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(44, 90, 160, 0.3);
        }
        
        .btn-request:disabled {
            background: #ccc;
            cursor: not-allowed;
        }

        .borrow-select-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.75rem;
            font-weight: 600;
            color: var(--text-color);
            user-select: none;
        }

        .borrow-select {
            width: 18px;
            height: 18px;
        }

        .borrow-footer-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .borrow-footer-row .borrow-select-label {
            margin-bottom: 0;
        }

        /* (removed) per-card reserve checkbox */

        .borrow-selection-bar {
            position: fixed;
            left: 0;
            right: 0;
            bottom: 0;
            background: white;
            border-top: 1px solid var(--border-color);
            box-shadow: var(--shadow);
            padding: 0.75rem 1rem;
            z-index: 9999;
            display: none;
        }

        .borrow-selection-inner {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }

        .borrow-selection-actions {
            display: flex;
            gap: 0.75rem;
        }

        .borrow-bar-btn {
            padding: 0.6rem 0.9rem;
            border-radius: var(--border-radius);
            border: 2px solid var(--border-color);
            background: white;
            color: var(--text-color);
            font-weight: 700;
            cursor: pointer;
        }

        .borrow-bar-btn.primary {
            border: none;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
        }

        /* Borrow request modal */
        .borrow-modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 10000;
            padding: 1rem;
        }

        .borrow-modal {
            width: 100%;
            max-width: 640px;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        /* Keep borrow modal within viewport height and scroll its body if needed */
        #borrowModalOverlay .borrow-modal {
            max-height: calc(100vh - 2rem);
            display: flex;
            flex-direction: column;
        }

        #borrowModalOverlay .borrow-modal-body {
            overflow: auto;
        }

        .borrow-modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 1.25rem;
            background: var(--bg-light);
            border-bottom: 1px solid var(--border-color);
        }

        .borrow-modal-header h3 {
            margin: 0;
            font-size: 1.1rem;
            color: var(--text-color);
        }

        .borrow-close {
            border: none;
            background: transparent;
            font-size: 1.25rem;
            cursor: pointer;
            color: var(--text-color);
        }

        .borrow-modal-body {
            padding: 1rem 1.25rem;
        }

        .borrow-form .form-row {
            display: grid;
            grid-template-columns: 1fr;
            gap: 0.5rem;
            margin-bottom: 0.9rem;
        }

        .borrow-form label {
            font-weight: 700;
            color: var(--text-color);
        }

        .borrow-form input[type="text"],
        .borrow-form input[type="datetime-local"],
        .borrow-form input[type="time"],
        .borrow-form textarea,
        .borrow-form select {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid var(--border-color);
            border-radius: var(--border-radius);
            font-family: inherit;
        }

        .borrow-form textarea {
            min-height: 90px;
            resize: vertical;
        }

        /* Force display for datetime-local as dd/mm/yyyy HH:mm (overlay), while keeping native picker */
        .dt-display-wrap {
            position: relative;
        }

        .dt-display-wrap input[type="datetime-local"] {
            color: transparent;
            caret-color: transparent;
        }

        .dt-display-wrap input[type="datetime-local"]::-webkit-datetime-edit,
        .dt-display-wrap input[type="datetime-local"]::-webkit-datetime-edit-fields-wrapper,
        .dt-display-wrap input[type="datetime-local"]::-webkit-datetime-edit-text,
        .dt-display-wrap input[type="datetime-local"]::-webkit-datetime-edit-month-field,
        .dt-display-wrap input[type="datetime-local"]::-webkit-datetime-edit-day-field,
        .dt-display-wrap input[type="datetime-local"]::-webkit-datetime-edit-year-field,
        .dt-display-wrap input[type="datetime-local"]::-webkit-datetime-edit-hour-field,
        .dt-display-wrap input[type="datetime-local"]::-webkit-datetime-edit-minute-field,
        .dt-display-wrap input[type="datetime-local"]::-webkit-datetime-edit-ampm-field {
            color: transparent;
        }

        .dt-display-wrap .dt-display {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            padding: 0.75rem;
            padding-right: 2.5rem;
            pointer-events: none;
            color: var(--text-color);
            font-family: inherit;
            font-size: inherit;
            line-height: inherit;
        }

        .borrow-modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
            padding: 1rem 1.25rem;
            border-top: 1px solid var(--border-color);
        }

        @media (min-width: 640px) {
            .borrow-form .form-row.two {
                grid-template-columns: 1fr 1fr;
                gap: 0.75rem;
            }
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
            margin-top: 3rem;
        }
        
        .page-link {
            padding: 0.5rem 1rem;
            background: white;
            border: 2px solid var(--border-color);
            border-radius: var(--border-radius);
            text-decoration: none;
            color: var(--text-color);
            font-weight: 500;
            transition: var(--transition);
        }
        
        .page-link:hover {
            border-color: var(--primary-color);
            color: var(--primary-color);
        }
        
        .page-link.active {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        
        .no-results {
            text-align: center;
            padding: 3rem;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
        }
        
        .no-results i {
            font-size: 4rem;
            color: var(--text-light);
            margin-bottom: 1rem;
        }
        
        @media (max-width: 992px) {
            .search-form {
                grid-template-columns: 1fr;
            }
            
            .equipment-stats {
                grid-template-columns: repeat(2, 1fr);
            }

            .category-grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            }

            .category-tabs-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
        }
        
        @media (max-width: 768px) {
            .equipment-stats {
                grid-template-columns: 1fr;
            }

            .category-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 480px) {
            .category-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <nav class="navbar">
            <div class="nav-container">
                <div class="nav-logo">
                    <a href="index.php"><img src="images/tvu-logo.png" alt="TVU Logo" class="logo"></a>
                    <div class="system-name">
                        <h1>HỆ THỐNG MƯỢN TRẢ THIẾT BỊ</h1>
                        <span>Trường Đại học Trà Vinh</span>
                    </div>
                </div>
                <div class="nav-menu">
                    <a href="index.php" class="nav-link">Trang chủ</a>
                    <a href="about.php" class="nav-link">Giới thiệu</a>
                    <a href="equipment.php" class="nav-link active">Thiết bị</a>
                    <a href="regulations.php" class="nav-link">Quy định & Hướng dẫn</a>
                    <a href="contact.php" class="nav-link">Liên hệ</a>
                    <?php if ($isLoggedIn): ?>
                        <a href="dashboard.php" class="nav-link">Dashboard</a>
                    <?php endif; ?>
                </div>
                <div class="nav-auth">
                    <?php if ($isLoggedIn && isset($userData)): ?>
                        <!-- User đã đăng nhập -->
                        <div class="user-info-box">
                            <div class="user-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="user-details">
                                <span class="user-name"><?php echo htmlspecialchars($userData['HoTen']); ?></span>
                                <span class="user-role"><?php echo htmlspecialchars($userData['TenVaiTro'] ?? 'Người dùng'); ?></span>
                            </div>
                        </div>
                        <a href="logout.php" class="btn-login">
                            <i class="fas fa-sign-out-alt"></i> Đăng xuất
                        </a>
                    <?php else: ?>
                        <!-- User chưa đăng nhập -->
                        <a href="login.php" class="btn-login">
                            <i class="fas fa-sign-in-alt"></i> Đăng nhập
                        </a>
                        <a href="register.php" class="btn-register">
                            <i class="fas fa-user-plus"></i> Đăng ký
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </header>

    <!-- Hero Section -->
    <section class="equipment-hero">
        <div class="container">
            <h1><i class="fas fa-laptop"></i> Danh Sách Thiết Bị</h1>
            <p>Khám phá và đăng ký mượn thiết bị giảng dạy hiện đại</p>
        </div>
    </section>

    <!-- Equipment Section -->
    <section class="equipment-section">
        <div class="container">
            <!-- Category Tabs -->
            <div class="category-tabs">
                <div class="category-tabs-header">
                    <h3><i class="fas fa-layer-group"></i> Nhóm Thiết Bị</h3>
                    <a href="equipment.php" class="view-all-btn">
                        <i class="fas fa-th"></i> Xem tất cả
                    </a>
                </div>
                <div class="category-grid">
                    <?php foreach ($loaiThietBiList as $loai): ?>
                        <?php
                        // Xác định icon cho từng loại
                        $categoryIcon = 'fa-laptop';
                        $tenLoai = strtolower($loai['TenLoai']);
                        if (stripos($tenLoai, 'máy chiếu') !== false) $categoryIcon = 'fa-video';
                        elseif (stripos($tenLoai, 'projector') !== false) $categoryIcon = 'fa-video';
                        elseif (stripos($tenLoai, 'micro') !== false) $categoryIcon = 'fa-microphone';
                        elseif (stripos($tenLoai, 'máy ảnh') !== false) $categoryIcon = 'fa-camera';
                        elseif (stripos($tenLoai, 'camera') !== false) $categoryIcon = 'fa-camera';
                        elseif (stripos($tenLoai, 'tai nghe') !== false) $categoryIcon = 'fa-headphones';
                        elseif (stripos($tenLoai, 'headphone') !== false) $categoryIcon = 'fa-headphones';
                        elseif (stripos($tenLoai, 'bàn phím') !== false) $categoryIcon = 'fa-keyboard';
                        elseif (stripos($tenLoai, 'chuột') !== false) $categoryIcon = 'fa-mouse';
                        elseif (stripos($tenLoai, 'màn hình') !== false) $categoryIcon = 'fa-desktop';
                        elseif (stripos($tenLoai, 'monitor') !== false) $categoryIcon = 'fa-desktop';
                        elseif (stripos($tenLoai, 'máy in') !== false) $categoryIcon = 'fa-print';
                        elseif (stripos($tenLoai, 'printer') !== false) $categoryIcon = 'fa-print';
                        elseif (stripos($tenLoai, 'máy scan') !== false) $categoryIcon = 'fa-scanner';
                        elseif (stripos($tenLoai, 'tablet') !== false) $categoryIcon = 'fa-tablet-alt';
                        elseif (stripos($tenLoai, 'máy tính bảng') !== false) $categoryIcon = 'fa-tablet-alt';
                        elseif (stripos($tenLoai, 'điện thoại') !== false) $categoryIcon = 'fa-mobile-alt';
                        elseif (stripos($tenLoai, 'loa') !== false) $categoryIcon = 'fa-volume-up';
                        elseif (stripos($tenLoai, 'speaker') !== false) $categoryIcon = 'fa-volume-up';
                        ?>
                        <a href="equipment.php?loai=<?php echo urlencode($loai['MaLoaiThietBi']); ?>" 
                           class="category-item <?php echo ($loaiThietBi === $loai['MaLoaiThietBi']) ? 'active' : ''; ?>">
                            <div class="category-icon">
                                <i class="fas <?php echo $categoryIcon; ?>"></i>
                            </div>
                            <div class="category-name"><?php echo htmlspecialchars($loai['TenLoai']); ?></div>
                            <div class="category-count">
                                <?php echo $loai['SoLuongKhaDung']; ?>/<?php echo $loai['SoLuong']; ?> khả dụng
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Search and Filter -->
            <div class="search-filter-bar">
                <form method="GET" action="equipment.php" class="search-form">
                    <div class="form-group">
                        <label for="search"><i class="fas fa-search"></i> Tìm kiếm</label>
                        <input 
                            type="text" 
                            id="search" 
                            name="search" 
                            placeholder="Nhập mã thiết bị, tên, số serial..." 
                            value="<?php echo htmlspecialchars($search); ?>"
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="loai"><i class="fas fa-filter"></i> Loại thiết bị</label>
                        <select id="loai" name="loai">
                            <option value="">Tất cả</option>
                            <?php foreach ($loaiThietBiList as $loai): ?>
                                <option value="<?php echo htmlspecialchars($loai['MaLoaiThietBi']); ?>"
                                    <?php echo $loaiThietBi === $loai['MaLoaiThietBi'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($loai['TenLoai']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="trangthai"><i class="fas fa-check-circle"></i> Trạng thái</label>
                        <select id="trangthai" name="trangthai">
                            <option value="">Tất cả</option>
                            <?php foreach ($trangThaiList as $tt): ?>
                                <option value="<?php echo htmlspecialchars($tt['MaTrangThai']); ?>"
                                    <?php echo $trangThai == $tt['MaTrangThai'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($tt['TenTrangThai']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn-search">
                        <i class="fas fa-search"></i> Tìm kiếm
                    </button>
                </form>
            </div>

            <!-- Statistics -->
            <div class="equipment-stats">
                <div class="stat-card">
                    <div class="stat-number">
                        <?php 
                        $totalAll = dbQueryOne("SELECT COUNT(*) as total FROM ThietBi WHERE IsDeleted = 0");
                        echo $totalAll['total'];
                        ?>
                    </div>
                    <div class="stat-label">Tổng thiết bị</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">
                        <?php 
                        $available = dbQueryOne("SELECT COUNT(*) as total FROM ThietBi WHERE MaTrangThai = 1 AND IsDeleted = 0");
                        echo $available['total'];
                        ?>
                    </div>
                    <div class="stat-label">Khả dụng</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">
                        <?php 
                        $borrowed = dbQueryOne("SELECT COUNT(*) as total FROM ThietBi WHERE MaTrangThai = 2 AND IsDeleted = 0");
                        echo $borrowed['total'];
                        ?>
                    </div>
                    <div class="stat-label">Đang mượn</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo count($loaiThietBiList); ?></div>
                    <div class="stat-label">Loại thiết bị</div>
                </div>
            </div>

            <!-- Equipment Grid -->
            <?php if (empty($equipments)): ?>
                <div class="no-results">
                    <i class="fas fa-box-open"></i>
                    <h3>Không tìm thấy thiết bị</h3>
                    <p>Vui lòng thử lại với từ khóa khác</p>
                </div>
            <?php else: ?>
                <div class="equipment-grid">
                    <?php foreach ($equipments as $eq): ?>
                        <div class="equipment-card">
                            <div class="equipment-header">
                                <div class="equipment-icon">
                                    <?php
                                    $icon = 'fa-laptop';
                                    if (stripos($eq['TenLoai'], 'máy chiếu') !== false) $icon = 'fa-video';
                                    elseif (stripos($eq['TenLoai'], 'micro') !== false) $icon = 'fa-microphone';
                                    elseif (stripos($eq['TenLoai'], 'máy ảnh') !== false) $icon = 'fa-camera';
                                    elseif (stripos($eq['TenLoai'], 'tai nghe') !== false) $icon = 'fa-headphones';
                                    ?>
                                    <i class="fas <?php echo $icon; ?>"></i>
                                </div>
                                <h3 class="equipment-name"><?php echo htmlspecialchars($eq['TenLoai']); ?></h3>
                            </div>
                            
                            <div class="equipment-body">
                                <div class="equipment-info">
                                    <div class="info-row">
                                        <span class="info-label">Mã thiết bị:</span>
                                        <span class="info-value"><?php echo htmlspecialchars($eq['MaThietBi']); ?></span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">Mã tài sản:</span>
                                        <span class="info-value"><?php echo htmlspecialchars($eq['MaTaiSan'] ?? 'N/A'); ?></span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">Vị trí:</span>
                                        <span class="info-value"><?php echo htmlspecialchars($eq['TenDiaDiem']); ?></span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">Trạng thái:</span>
                                        <span class="info-value">
                                            <?php
                                            $statusClass = 'status-available';
                                            if ($eq['MaTrangThai'] == 2) $statusClass = 'status-borrowed';
                                            elseif ($eq['MaTrangThai'] == 3) $statusClass = 'status-maintenance';
                                            elseif ($eq['MaTrangThai'] == 4) $statusClass = 'status-broken';
                                            ?>
                                            <span class="status-badge <?php echo $statusClass; ?>">
                                                <?php echo htmlspecialchars($eq['TenTrangThai']); ?>
                                            </span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="equipment-footer">
                                <?php if ($isLoggedIn): ?>
                                    <?php if ($eq['MaTrangThai'] == 1): ?>
                                        <div class="borrow-footer-row">
                                            <label class="borrow-select-label">
                                                <input class="borrow-select" type="checkbox" value="<?php echo htmlspecialchars($eq['MaThietBi']); ?>" data-tenloai="<?php echo htmlspecialchars($eq['TenLoai'], ENT_QUOTES); ?>" data-maloai="<?php echo htmlspecialchars($eq['MaLoaiThietBi'], ENT_QUOTES); ?>">
                                                Chọn thiết bị này
                                            </label>
                                        </div>
                                    <?php else: ?>
                                        <div class="borrow-hint">Thiết bị không khả dụng</div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div class="borrow-hint"><a href="login.php">Đăng nhập</a> để chọn và tạo yêu cầu</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo ($page - 1); ?>&search=<?php echo urlencode($search); ?>&loai=<?php echo urlencode($loaiThietBi); ?>&trangthai=<?php echo urlencode($trangThai); ?>" class="page-link">
                                <i class="fas fa-chevron-left"></i> Trước
                            </a>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <?php if ($i == $page || $i == 1 || $i == $totalPages || abs($i - $page) <= 2): ?>
                                <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&loai=<?php echo urlencode($loaiThietBi); ?>&trangthai=<?php echo urlencode($trangThai); ?>" 
                                   class="page-link <?php echo $i == $page ? 'active' : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php elseif (abs($i - $page) == 3): ?>
                                <span class="page-link">...</span>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                            <a href="?page=<?php echo ($page + 1); ?>&search=<?php echo urlencode($search); ?>&loai=<?php echo urlencode($loaiThietBi); ?>&trangthai=<?php echo urlencode($trangThai); ?>" class="page-link">
                                Sau <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <div class="footer-logo">
                        <img src="images/tvu-logo.png" alt="TVU Logo" class="logo">
                        <div class="system-name">
                            <h3>HỆ THỐNG MƯỢN TRẢ THIẾT BỊ</h3>
                            <span>Trường Đại học Trà Vinh</span>
                        </div>
                    </div>
                    <p>Hệ thống quản lý và cho mượn thiết bị giảng dạy hiện đại, hiệu quả tại Trường Đại học Trà Vinh.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                <div class="footer-section">
                    <h4>Liên kết nhanh</h4>
                    <ul>
                        <li><a href="index.php">Trang chủ</a></li>
                        <li><a href="about.php">Giới thiệu</a></li>
                        <li><a href="equipment.php">Thiết bị</a></li>
                        <li><a href="regulations.php">Quy định & Hướng dẫn</a></li>
                        <li><a href="contact.php">Liên hệ</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Dịch vụ</h4>
                    <ul>
                        <li><a href="equipment.php">Mượn thiết bị</a></li>
                        <li><a href="equipment.php">Tra cứu thiết bị</a></li>
                        <li><a href="regulations.php">Hướng dẫn sử dụng</a></li>
                        <li><a href="regulations.php">Quy định mượn trả</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Liên hệ</h4>
                    <div class="contact-info">
                        <p><i class="fas fa-map-marker-alt"></i> Số 126, Nguyễn Thiện Thành, Khóm 4, Phường 5, TP. Trà Vinh</p>
                        <p><i class="fas fa-phone"></i> (0294) 3 855 959</p>
                        <p><i class="fas fa-envelope"></i> info@tvu.edu.vn</p>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 Trường Đại học Trà Vinh. Tất cả các quyền được bảo lưu.</p>
            </div>
        </div>
    </footer>

    <?php if ($isLoggedIn): ?>
        <div id="borrowSelectionBar" class="borrow-selection-bar">
            <div class="borrow-selection-inner">
                <div><strong>Đã chọn:</strong> <span id="borrowSelectedCount">0</span> thiết bị</div>
                <div class="borrow-selection-actions">
                    <button type="button" class="borrow-bar-btn" onclick="clearBorrowSelection()">Bỏ chọn</button>
                    <button type="button" class="borrow-bar-btn" onclick="openReserveModalFromSelection()">Đặt trước</button>
                    <button type="button" class="borrow-bar-btn primary" onclick="openBorrowModalFromSelection()">Tạo yêu cầu</button>
                </div>
            </div>
        </div>

        <div id="borrowModalOverlay" class="borrow-modal-overlay" aria-hidden="true">
            <div class="borrow-modal" role="dialog" aria-modal="true" aria-labelledby="borrowModalTitle">
                <div class="borrow-modal-header">
                    <h3 id="borrowModalTitle">Tạo yêu cầu mượn (nhiều thiết bị)</h3>
                    <button type="button" class="borrow-close" onclick="closeBorrowModal()" aria-label="Đóng">&times;</button>
                </div>
                <div class="borrow-modal-body">
                    <form id="borrowForm" class="borrow-form" onsubmit="submitBorrowRequest(event)">
                        <div class="form-row">
                            <label>Thiết bị đã chọn</label>
                            <input type="text" id="borrowDeviceList" value="" readonly>
                        </div>

                        <div class="form-row">
                            <label for="borrowMucDich">Mục đích mượn *</label>
                            <textarea id="borrowMucDich" required placeholder="Ví dụ: Phục vụ giảng dạy, thuyết trình, ghi hình..."></textarea>
                        </div>

                        <div class="form-row">
                            <label for="borrowKhuSuDung">Khu *</label>
                            <select id="borrowKhuSuDung" required>
                                <option value="">-- Chọn khu --</option>
                                <?php foreach ($khuList as $k):
                                    $kv = isset($k['Khu']) ? trim((string)$k['Khu']) : '';
                                    if ($kv === '') continue;
                                    $selected = ($kv === $defaultKhu) ? 'selected' : '';
                                ?>
                                    <option value="<?php echo htmlspecialchars($kv); ?>" <?php echo $selected; ?>><?php echo htmlspecialchars($kv); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-row">
                            <label for="borrowMaDiaDiemSuDung">Phòng/địa điểm sử dụng thiết bị *</label>
                            <select id="borrowMaDiaDiemSuDung" required>
                                <option value="">-- Chọn phòng/địa điểm --</option>
                                <?php foreach ($diaDiemList as $dd): ?>
                                    <option value="<?php echo (int)$dd['MaDiaDiem']; ?>"><?php echo htmlspecialchars($dd['TenDiaDiem'] ?? ''); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-row two">
                            <div>
                                <label for="borrowNgayBatDau">Thời gian bắt đầu *</label>
                                <input id="borrowNgayBatDau" type="time" required>
                            </div>
                            <div>
                                <label for="borrowNgayKetThuc">Thời gian kết thúc *</label>
                                <input id="borrowNgayKetThuc" type="time" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <small><strong>Ngày mượn cố định:</strong> <span id="borrowFixedDateText"></span></small>
                        </div>

                        <div class="form-row">
                            <label for="borrowGhiChu">Ghi chú (tuỳ chọn)</label>
                            <textarea id="borrowGhiChu" placeholder="Ví dụ: Cần kèm cáp HDMI..." maxlength="300"></textarea>
                        </div>
                    </form>
                </div>
                <div class="borrow-modal-footer">
                    <button type="button" class="borrow-bar-btn" onclick="closeBorrowModal()">Hủy</button>
                    <button type="submit" form="borrowForm" class="borrow-bar-btn primary">Gửi yêu cầu</button>
                </div>
            </div>
        </div>

        <div id="reserveModalOverlay" class="borrow-modal-overlay" aria-hidden="true">
            <div class="borrow-modal" role="dialog" aria-modal="true" aria-labelledby="reserveModalTitle">
                <div class="borrow-modal-header">
                    <h3 id="reserveModalTitle">Đặt trước thiết bị</h3>
                    <button type="button" class="borrow-close" onclick="closeReserveModal()" aria-label="Đóng">&times;</button>
                </div>
                <div class="borrow-modal-body">
                    <form id="reserveForm" class="borrow-form" onsubmit="submitReserveRequest(event)">
                        <input type="hidden" id="reserveMaLoaiThietBi" value="">

                        <div class="form-row">
                            <label>Loại thiết bị</label>
                            <input type="text" id="reserveTenLoai" value="" readonly>
                        </div>

                        <div class="form-row two">
                            <div>
                                <label for="reserveNgayBatDau">Ngày bắt đầu *</label>
                                <input id="reserveNgayBatDau" type="datetime-local" lang="vi-VN" required>
                            </div>
                            <div>
                                <label for="reserveNgayKetThuc">Ngày kết thúc *</label>
                                <input id="reserveNgayKetThuc" type="datetime-local" lang="vi-VN" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <label for="reserveKhuSuDung">Khu *</label>
                            <select id="reserveKhuSuDung" required>
                                <option value="">-- Chọn khu --</option>
                                <?php foreach ($khuList as $k):
                                    $kv = isset($k['Khu']) ? trim((string)$k['Khu']) : '';
                                    if ($kv === '') continue;
                                    $selected = ($kv === $defaultKhu) ? 'selected' : '';
                                ?>
                                    <option value="<?php echo htmlspecialchars($kv); ?>" <?php echo $selected; ?>><?php echo htmlspecialchars($kv); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-row">
                            <label for="reserveMaDiaDiemSuDung">Phòng/địa điểm sử dụng *</label>
                            <select id="reserveMaDiaDiemSuDung" required>
                                <option value="">-- Chọn phòng/địa điểm --</option>
                                <?php foreach ($diaDiemList as $dd): ?>
                                    <option value="<?php echo (int)$dd['MaDiaDiem']; ?>"><?php echo htmlspecialchars($dd['TenDiaDiem'] ?? ''); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="borrow-modal-footer">
                    <button type="button" class="borrow-bar-btn" onclick="closeReserveModal()">Hủy</button>
                    <button type="submit" form="reserveForm" class="borrow-bar-btn primary">Gửi đặt trước</button>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <script>
        const UNPAID_FINES_MESSAGE = 'Vui lòng thanh toán tất cả các phiếu phạt trước khi thực hiện thao tác';
        const HAS_UNPAID_FINES_SNAPSHOT = <?php echo $hasUnpaidFines ? 'true' : 'false'; ?>;

        function showUnpaidFinesPopup() {
            alert(UNPAID_FINES_MESSAGE);
        }

        function ensureNoUnpaidFinesThen(next) {
            // Fast path (page snapshot). Still verify with server to support "pay then borrow" without reload.
            fetch('actions/check_unpaid_fines.php', { method: 'GET', headers: { 'Accept': 'application/json' } })
                .then(r => r.json())
                .then(data => {
                    if (!data || data.success !== true) {
                        // If cannot verify, fall back to snapshot
                        if (HAS_UNPAID_FINES_SNAPSHOT) {
                            showUnpaidFinesPopup();
                            return;
                        }
                        if (typeof next === 'function') next();
                        return;
                    }

                    if (data.hasUnpaid === 1) {
                        showUnpaidFinesPopup();
                        return;
                    }

                    if (typeof next === 'function') next();
                })
                .catch(() => {
                    if (HAS_UNPAID_FINES_SNAPSHOT) {
                        showUnpaidFinesPopup();
                        return;
                    }
                    if (typeof next === 'function') next();
                });
        }

        function pad2(n) { return String(n).padStart(2, '0'); }

        function toDateTimeLocalValue(dateObj) {
            const yyyy = dateObj.getFullYear();
            const mm = pad2(dateObj.getMonth() + 1);
            const dd = pad2(dateObj.getDate());
            const hh = pad2(dateObj.getHours());
            const mi = pad2(dateObj.getMinutes());
            return `${yyyy}-${mm}-${dd}T${hh}:${mi}`;
        }

        function formatDmyHmFromLocalValue(val) {
            // val is expected: YYYY-MM-DDTHH:MM
            if (!val || typeof val !== 'string') return '';
            const parts = val.split('T');
            if (parts.length !== 2) return '';
            const d = parts[0].split('-');
            const t = parts[1].split(':');
            if (d.length !== 3 || t.length < 2) return '';
            const yyyy = d[0];
            const mm = d[1];
            const dd = d[2];
            const hh = t[0];
            const mi = t[1];
            if (!yyyy || !mm || !dd || hh == null || mi == null) return '';
            return `${dd}/${mm}/${yyyy} ${hh}:${mi}`;
        }

        function localYmd(d) {
            const yyyy = d.getFullYear();
            const mm = String(d.getMonth() + 1).padStart(2, '0');
            const dd = String(d.getDate()).padStart(2, '0');
            return `${yyyy}-${mm}-${dd}`;
        }

        function enforceMinTomorrow(startInput) {
            if (!startInput) return;
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            const minYmd = localYmd(tomorrow);
            const minVal = `${minYmd}T00:00`;
            startInput.min = minVal;
        }

        function timeToMinutes(t) {
            const parts = (t || '').split(':');
            if (parts.length < 2) return NaN;
            const hh = parseInt(parts[0], 10);
            const mm = parseInt(parts[1], 10);
            if (!Number.isFinite(hh) || !Number.isFinite(mm)) return NaN;
            return hh * 60 + mm;
        }

        function minutesToTime(m) {
            const mm = Math.max(0, Math.min(23 * 60 + 59, m));
            const hh = Math.floor(mm / 60);
            const mi = mm % 60;
            return `${pad2(hh)}:${pad2(mi)}`;
        }

        function roundUpToStepMinutes(dateObj, step) {
            const d = new Date(dateObj.getTime());
            const s = Number.isFinite(step) && step > 0 ? step : 5;
            d.setSeconds(0, 0);
            const mins = d.getMinutes();
            const rounded = Math.ceil(mins / s) * s;
            d.setMinutes(rounded);
            return d;
        }

        function bindBorrowTimeRangeToday(startInput, endInput) {
            if (!startInput || !endInput) return;

            function apply() {
                // Do not restrict time input based on current time; validate on submit.
                startInput.min = '00:00';
                startInput.max = '23:59';
                endInput.min = '00:00';
                endInput.max = '23:59';
            }

            if (startInput.dataset.borrowTimeBound !== '1') {
                startInput.dataset.borrowTimeBound = '1';
                startInput.addEventListener('change', apply);
                startInput.addEventListener('input', apply);
                startInput.addEventListener('focus', apply);
                startInput.addEventListener('click', apply);
                endInput.addEventListener('change', apply);
                endInput.addEventListener('focus', apply);
                endInput.addEventListener('click', apply);
            }
            apply();
        }

        function enhanceDateTimeLocalDisplay() {
            const inputs = Array.from(document.querySelectorAll('input[type="datetime-local"]'));
            inputs.forEach(function(input) {
                // Avoid double-wrapping
                if (input.closest('.dt-display-wrap')) return;

                const wrap = document.createElement('div');
                wrap.className = 'dt-display-wrap';
                input.parentNode.insertBefore(wrap, input);
                wrap.appendChild(input);

                const span = document.createElement('div');
                span.className = 'dt-display';
                span.setAttribute('aria-hidden', 'true');
                wrap.appendChild(span);

                function sync() {
                    const txt = formatDmyHmFromLocalValue(input.value);
                    span.textContent = txt || '';
                }

                input.addEventListener('input', sync);
                input.addEventListener('change', sync);
                sync();
            });
        }

        // Apply display overlay on load
        enhanceDateTimeLocalDisplay();

        function notifyValueChanged(el) {
            if (!el) return;
            el.dispatchEvent(new Event('input', { bubbles: true }));
            el.dispatchEvent(new Event('change', { bubbles: true }));
        }

        function bindSameDayRange(startInput, endInput, opts) {
            if (!startInput || !endInput) return;
            opts = opts || {};
            const defaultEndHour = Number.isFinite(opts.defaultEndHour) ? opts.defaultEndHour : 17;

            function datePart(v) { return (v || '').split('T')[0] || ''; }
            function timePart(v) { return (v || '').split('T')[1] || '00:00'; }
            function endOfDay(date) { return `${date}T23:59`; }
            function defaultEnd(date) { return `${date}T${pad2(defaultEndHour)}:00`; }

            function apply() {
                const s = (startInput.value || '').trim();
                if (!s) return;

                const d = datePart(s);
                if (!d) return;

                // Do not auto-correct user's chosen datetime; only constrain end date to same day.
                endInput.min = `${d}T00:00`;
                endInput.max = endOfDay(d);
            }

            if (startInput.dataset.sameDayBound !== '1') {
                startInput.dataset.sameDayBound = '1';
                startInput.addEventListener('change', apply);
                startInput.addEventListener('input', apply);
                endInput.addEventListener('change', apply);
            }
            apply();
        }

        const BORROW_SELECTION_KEY = 'borrow_selection_v2';
        let borrowSelectionCache = null; // { [id]: { tenLoai: string, maLoai: string } }

        // Reservation uses the existing selected devices (borrow selection)

        function loadBorrowSelection() {
            if (borrowSelectionCache) return borrowSelectionCache;
            try {
                const raw = localStorage.getItem(BORROW_SELECTION_KEY);
                const parsed = raw ? JSON.parse(raw) : {};
                borrowSelectionCache = (parsed && typeof parsed === 'object') ? parsed : {};
            } catch (e) {
                borrowSelectionCache = {};
            }
            return borrowSelectionCache;
        }

        function saveBorrowSelection() {
            try {
                localStorage.setItem(BORROW_SELECTION_KEY, JSON.stringify(borrowSelectionCache || {}));
            } catch (e) {
                // ignore (storage may be disabled)
            }
        }

        function setBorrowSelected(maThietBi, tenLoai, maLoaiThietBi, isSelected) {
            const store = loadBorrowSelection();
            if (isSelected) {
                store[maThietBi] = {
                    tenLoai: tenLoai || (store[maThietBi] && store[maThietBi].tenLoai) || '',
                    maLoai: maLoaiThietBi || (store[maThietBi] && store[maThietBi].maLoai) || ''
                };
            } else {
                delete store[maThietBi];
            }
            borrowSelectionCache = store;
            saveBorrowSelection();
        }

        function getSelectedDevices() {
            const store = loadBorrowSelection();
            return Object.keys(store).map(id => {
                const val = store[id];
                if (val && typeof val === 'object') {
                    return { id, tenLoai: val.tenLoai || '', maLoai: val.maLoai || '' };
                }
                // Backward-compat if any old data sneaks in
                return { id, tenLoai: (val || ''), maLoai: '' };
            });
        }

        function getSelectedReserveTypes() {
            const selected = getSelectedDevices();
            const map = {};
            selected.forEach(x => {
                if (!x.maLoai) return;
                if (!map[x.maLoai]) map[x.maLoai] = x.tenLoai || x.maLoai;
            });
            return Object.keys(map).map(maLoai => ({ maLoai, tenLoai: map[maLoai] }));
        }

        function syncCheckboxesFromSelection() {
            const store = loadBorrowSelection();
            document.querySelectorAll('.borrow-select').forEach(cb => {
                cb.checked = Object.prototype.hasOwnProperty.call(store, cb.value);
            });
        }

        function updateBorrowSelectionUI() {
            const selected = getSelectedDevices();
            const bar = document.getElementById('borrowSelectionBar');
            const countEl = document.getElementById('borrowSelectedCount');

            if (!bar || !countEl) return;

            countEl.textContent = String(selected.length);
            bar.style.display = selected.length > 0 ? 'block' : 'none';
        }

        function clearBorrowSelection() {
            borrowSelectionCache = {};
            saveBorrowSelection();
            document.querySelectorAll('.borrow-select:checked').forEach(el => { el.checked = false; });
            updateBorrowSelectionUI();
        }

        function openBorrowModalFromSelection() {
            const selected = getSelectedDevices();
            if (selected.length === 0) {
                alert('Vui lòng chọn ít nhất 1 thiết bị.');
                return;
            }
            ensureNoUnpaidFinesThen(() => openBorrowModal());
        }

        function openReserveModalFromSelection() {
            const devices = getSelectedDevices();
            if (devices.length === 0) {
                alert('Vui lòng chọn ít nhất 1 thiết bị (checkbox "Chọn thiết bị này") để đặt trước.');
                return;
            }
            ensureNoUnpaidFinesThen(() => openReserveModalForDevices(devices));
        }

        function openBorrowModal() {
            const overlay = document.getElementById('borrowModalOverlay');
            if (!overlay) return;

            const selected = getSelectedDevices();
            if (selected.length === 0) {
                alert('Vui lòng chọn ít nhất 1 thiết bị.');
                return;
            }

            document.getElementById('borrowDeviceList').value = selected.map(x => `${x.id} - ${x.tenLoai}`).join(', ');

            const mucDich = document.getElementById('borrowMucDich');
            const startInput = document.getElementById('borrowNgayBatDau');
            const endInput = document.getElementById('borrowNgayKetThuc');
            const ghiChu = document.getElementById('borrowGhiChu');
            const maDiaDiemSuDung = document.getElementById('borrowMaDiaDiemSuDung');

            mucDich.value = mucDich.value || 'Phục vụ giảng dạy';
            ghiChu.value = '';

            if (maDiaDiemSuDung) {
                maDiaDiemSuDung.value = maDiaDiemSuDung.value || '';
            }

            // Borrow date is fixed to today; user selects only time
            const today = new Date();
            const fixedDateText = document.getElementById('borrowFixedDateText');
            if (fixedDateText) {
                fixedDateText.textContent = `${pad2(today.getDate())}/${pad2(today.getMonth() + 1)}/${today.getFullYear()}`;
            }

            const now = new Date();
            const startD = roundUpToStepMinutes(now, 5);
            const endD = new Date(startD.getTime());
            endD.setMinutes(endD.getMinutes() + 60);
            if (localYmd(endD) !== localYmd(today)) {
                endD.setHours(23, 59, 0, 0);
            }

            startInput.value = `${pad2(startD.getHours())}:${pad2(startD.getMinutes())}`;
            endInput.value = `${pad2(endD.getHours())}:${pad2(endD.getMinutes())}`;

            bindBorrowTimeRangeToday(startInput, endInput);

            overlay.style.display = 'flex';
            overlay.setAttribute('aria-hidden', 'false');
        }

        function closeBorrowModal() {
            const overlay = document.getElementById('borrowModalOverlay');
            if (!overlay) return;
            overlay.style.display = 'none';
            overlay.setAttribute('aria-hidden', 'true');
        }

        function openReserveModal(maLoaiThietBi, tenLoai) {
            // Legacy entry point: keep modal usable (reserve by type)
            openReserveModalForTypes([{ maLoai: maLoaiThietBi || '', tenLoai: tenLoai || '' }]);
        }

        function openReserveModalForDevices(devices) {
            const overlay = document.getElementById('reserveModalOverlay');
            if (!overlay) return;

            const maEl = document.getElementById('reserveMaLoaiThietBi');
            const tenEl = document.getElementById('reserveTenLoai');
            const startInput = document.getElementById('reserveNgayBatDau');
            const endInput = document.getElementById('reserveNgayKetThuc');

            const filtered = (devices || []).filter(d => d && d.id);
            if (filtered.length === 0) {
                alert('Không xác định được thiết bị để đặt trước.');
                return;
            }

            if (filtered.length > 10) {
                alert('Chỉ được chọn tối đa 10 thiết bị cho mỗi lần đặt trước.');
                return;
            }

            // Reuse hidden field to store device IDs as comma-separated list
            maEl.value = filtered.map(d => d.id).join(',');
            tenEl.value = filtered.map(d => `${d.id} - ${d.tenLoai || ''}`.trim()).join(', ');

            const start = new Date();
            start.setDate(start.getDate() + 1);
            start.setHours(8, 0, 0, 0);
            const end = new Date(start.getTime());
            end.setHours(17, 0, 0, 0);

            startInput.value = toDateTimeLocalValue(start);
            endInput.value = toDateTimeLocalValue(end);
            notifyValueChanged(startInput);
            notifyValueChanged(endInput);

            // Must be after today (>= tomorrow)
            enforceMinTomorrow(startInput);
            if (startInput.dataset.minTomorrowBound !== '1') {
                startInput.dataset.minTomorrowBound = '1';
                startInput.addEventListener('focus', function() { enforceMinTomorrow(startInput); });
                startInput.addEventListener('click', function() { enforceMinTomorrow(startInput); });
            }

            // Đặt trong 1 ngày
            bindSameDayRange(startInput, endInput, { defaultEndHour: 17 });

            overlay.style.display = 'flex';
            overlay.setAttribute('aria-hidden', 'false');
        }

        function openReserveModalForTypes(types) {
            const overlay = document.getElementById('reserveModalOverlay');
            if (!overlay) return;

            const maEl = document.getElementById('reserveMaLoaiThietBi');
            const tenEl = document.getElementById('reserveTenLoai');
            const startInput = document.getElementById('reserveNgayBatDau');
            const endInput = document.getElementById('reserveNgayKetThuc');

            const filtered = (types || []).filter(t => t && t.maLoai);
            if (filtered.length === 0) {
                alert('Không xác định được loại thiết bị để đặt trước.');
                return;
            }

            if (filtered.length > 10) {
                alert('Chỉ được chọn tối đa 10 loại thiết bị cho mỗi lần đặt trước.');
                return;
            }

            maEl.value = filtered.map(t => t.maLoai).join(',');
            tenEl.value = filtered.map(t => `${t.maLoai} - ${t.tenLoai || ''}`.trim()).join(', ');

            const start = new Date();
            start.setDate(start.getDate() + 1);
            start.setHours(8, 0, 0, 0);
            const end = new Date(start.getTime());
            end.setHours(17, 0, 0, 0);

            startInput.value = toDateTimeLocalValue(start);
            endInput.value = toDateTimeLocalValue(end);
            notifyValueChanged(startInput);
            notifyValueChanged(endInput);

            // Must be after today (>= tomorrow)
            enforceMinTomorrow(startInput);
            if (startInput.dataset.minTomorrowBound !== '1') {
                startInput.dataset.minTomorrowBound = '1';
                startInput.addEventListener('focus', function() { enforceMinTomorrow(startInput); });
                startInput.addEventListener('click', function() { enforceMinTomorrow(startInput); });
            }

            // Đặt trong 1 ngày
            bindSameDayRange(startInput, endInput, { defaultEndHour: 17 });

            overlay.style.display = 'flex';
            overlay.setAttribute('aria-hidden', 'false');
        }

        function closeReserveModal() {
            const overlay = document.getElementById('reserveModalOverlay');
            if (!overlay) return;
            overlay.style.display = 'none';
            overlay.setAttribute('aria-hidden', 'true');
        }

        const reserveOverlay = document.getElementById('reserveModalOverlay');
        if (reserveOverlay) {
            reserveOverlay.addEventListener('click', function(e) {
                if (e.target === this) closeReserveModal();
            });
        }

        function submitReserveRequest(e) {
            e.preventDefault();

            // Block if unpaid fines exist (live check)
            ensureNoUnpaidFinesThen(() => {

            const rawTypes = document.getElementById('reserveMaLoaiThietBi').value.trim();
            const ngayBatDau = document.getElementById('reserveNgayBatDau').value;
            const ngayKetThuc = document.getElementById('reserveNgayKetThuc').value;
            const maDiaDiemSuDung = (document.getElementById('reserveMaDiaDiemSuDung')?.value || '').trim();

            // In device-based reserve, rawTypes holds device IDs
            const maThietBiList = rawTypes
                ? rawTypes.split(',').map(s => s.trim()).filter(Boolean)
                : [];
            const uniqueDevices = Array.from(new Set(maThietBiList));

            if (uniqueDevices.length === 0 || !ngayBatDau || !ngayKetThuc || !maDiaDiemSuDung) {
                alert('Vui lòng nhập đầy đủ thông tin bắt buộc.');
                return;
            }

            const start = new Date(ngayBatDau);
            const end = new Date(ngayKetThuc);
            if (!(start < end)) {
                alert('Thời gian không hợp lệ: ngày kết thúc phải sau ngày bắt đầu.');
                return;
            }

            if (ngayBatDau.split('T')[0] !== ngayKetThuc.split('T')[0]) {
                alert('Thời gian đặt trước chỉ trong 1 ngày. Vui lòng chọn ngày kết thúc cùng ngày bắt đầu.');
                return;
            }

            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            const minYmd = localYmd(tomorrow);
            if (ngayBatDau.split('T')[0] < minYmd) {
                alert('Ngày bắt đầu phải từ ngày mai trở đi.');
                return;
            }

            const formData = new FormData();
            uniqueDevices.forEach(id => formData.append('maThietBi[]', id));
            formData.append('ngayBatDau', ngayBatDau.replace('T', ' ') + ':00');
            formData.append('ngayKetThuc', ngayKetThuc.replace('T', ' ') + ':00');
            formData.append('maDiaDiemSuDung', maDiaDiemSuDung);

            fetch('actions/create_dattruoc.php', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    closeReserveModal();
                    window.location.href = 'dashboard.php';
                } else {
                    if (data && data.message === UNPAID_FINES_MESSAGE) {
                        showUnpaidFinesPopup();
                    } else {
                        alert('Lỗi: ' + (data && data.message ? data.message : 'Không xác định'));
                    }
                }
            })
            .catch(err => alert('Lỗi kết nối: ' + err.message));
            });
        }

        const modalOverlay = document.getElementById('borrowModalOverlay');
        if (modalOverlay) {
            modalOverlay.addEventListener('click', function(e) {
                if (e.target === this) closeBorrowModal();
            });
        }

        function submitBorrowRequest(e) {
            e.preventDefault();

            // Block if unpaid fines exist (live check)
            ensureNoUnpaidFinesThen(() => {

            const selected = getSelectedDevices();
            if (selected.length === 0) {
                alert('Vui lòng chọn ít nhất 1 thiết bị.');
                return;
            }

            const mucDich = document.getElementById('borrowMucDich').value.trim();
            const gioBatDau = document.getElementById('borrowNgayBatDau').value;
            const gioKetThuc = document.getElementById('borrowNgayKetThuc').value;
            const ghiChu = document.getElementById('borrowGhiChu').value.trim();
            const maDiaDiemSuDung = (document.getElementById('borrowMaDiaDiemSuDung').value || '').trim();

            if (!mucDich || !gioBatDau || !gioKetThuc || !maDiaDiemSuDung) {
                alert('Vui lòng nhập đầy đủ thông tin bắt buộc.');
                return;
            }

            const todayYmd = localYmd(new Date());
            const ngayBatDau = `${todayYmd}T${gioBatDau}`;
            const ngayKetThuc = `${todayYmd}T${gioKetThuc}`;

            const start = new Date(ngayBatDau);
            const end = new Date(ngayKetThuc);
            if (!(start < end)) {
                alert('Thời gian không hợp lệ: ngày kết thúc phải sau ngày bắt đầu.');
                return;
            }

            const formData = new FormData();
            selected.forEach(x => formData.append('maThietBi[]', x.id));
            formData.append('mucDich', mucDich);
            formData.append('ngayBatDau', ngayBatDau.replace('T', ' ') + ':00');
            formData.append('ngayKetThuc', ngayKetThuc.replace('T', ' ') + ':00');
            formData.append('maDiaDiemSuDung', maDiaDiemSuDung);
            if (ghiChu) formData.append('ghiChu', ghiChu);

            fetch('actions/create_yeucaumuon.php', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    closeBorrowModal();
                    clearBorrowSelection();
                    window.location.reload();
                } else {
                    if (data && data.message === UNPAID_FINES_MESSAGE) {
                        showUnpaidFinesPopup();
                    } else {
                        alert('Lỗi: ' + (data && data.message ? data.message : 'Không xác định'));
                    }
                }
            })
            .catch(err => alert('Lỗi kết nối: ' + err.message));
            });
        }

        // Restore selection across filtering/pagination
        syncCheckboxesFromSelection();
        document.querySelectorAll('.borrow-select').forEach(el => {
            el.addEventListener('change', function() {
                setBorrowSelected(
                    this.value,
                    this.getAttribute('data-tenloai') || '',
                    this.getAttribute('data-maloai') || '',
                    this.checked
                );
                updateBorrowSelectionUI();
            });
        });
        updateBorrowSelectionUI();

        // Dependent dropdown: Khu -> Phòng
        (function initKhuPhongBorrow() {
            const khuSelect = document.getElementById('borrowKhuSuDung');
            const phongSelect = document.getElementById('borrowMaDiaDiemSuDung');
            if (!khuSelect || !phongSelect) return;

            function setPhongLoading() {
                phongSelect.innerHTML = '<option value="">-- Đang tải phòng/địa điểm --</option>';
                phongSelect.disabled = true;
            }

            function setPhongOptions(items) {
                const opts = ['<option value="">-- Chọn phòng/địa điểm --</option>'];
                (items || []).forEach(function(it) {
                    const id = (it && it.MaDiaDiem != null) ? String(it.MaDiaDiem) : '';
                    const name = (it && it.TenDiaDiem != null) ? String(it.TenDiaDiem) : '';
                    if (!id) return;
                    opts.push('<option value="' + id.replace(/"/g, '&quot;') + '">' +
                        name.replace(/</g, '&lt;').replace(/>/g, '&gt;') +
                    '</option>');
                });
                phongSelect.innerHTML = opts.join('');
                phongSelect.disabled = false;
            }

            function loadPhongByKhu() {
                const khu = (khuSelect.value || '').trim();
                // If no khu selected, keep current options
                if (!khu) return;

                setPhongLoading();
                fetch('actions/get_diadiem_by_khu.php?khu=' + encodeURIComponent(khu), {
                    method: 'GET',
                    headers: { 'Accept': 'application/json' }
                })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (!data || !data.success) {
                        throw new Error((data && data.message) ? data.message : 'Không lấy được danh sách phòng');
                    }
                    setPhongOptions(data.items || []);
                })
                .catch(function(err) {
                    phongSelect.innerHTML = '<option value="">-- Chọn phòng/địa điểm --</option>';
                    phongSelect.disabled = false;
                    console.error(err);
                });
            }

            khuSelect.addEventListener('change', loadPhongByKhu);

            // If server-rendered list is empty, load immediately
            try {
                const hasOnlyPlaceholder = (phongSelect.options && phongSelect.options.length <= 1);
                if ((khuSelect.value || '').trim() && hasOnlyPlaceholder) {
                    loadPhongByKhu();
                }
            } catch (e) {
                // ignore
            }
        })();

        // Dependent dropdown: Khu -> Phòng (reserve)
        (function initKhuPhongReserve() {
            const khuSelect = document.getElementById('reserveKhuSuDung');
            const phongSelect = document.getElementById('reserveMaDiaDiemSuDung');
            if (!khuSelect || !phongSelect) return;

            function setPhongLoading() {
                phongSelect.innerHTML = '<option value="">-- Đang tải phòng/địa điểm --</option>';
                phongSelect.disabled = true;
            }

            function setPhongOptions(items) {
                const opts = ['<option value="">-- Chọn phòng/địa điểm --</option>'];
                (items || []).forEach(function(it) {
                    const id = (it && it.MaDiaDiem != null) ? String(it.MaDiaDiem) : '';
                    const name = (it && it.TenDiaDiem != null) ? String(it.TenDiaDiem) : '';
                    if (!id) return;
                    opts.push('<option value="' + id.replace(/"/g, '&quot;') + '">' +
                        name.replace(/</g, '&lt;').replace(/>/g, '&gt;') +
                    '</option>');
                });
                phongSelect.innerHTML = opts.join('');
                phongSelect.disabled = false;
            }

            function loadPhongByKhu() {
                const khu = (khuSelect.value || '').trim();
                if (!khu) return;

                setPhongLoading();
                fetch('actions/get_diadiem_by_khu.php?khu=' + encodeURIComponent(khu), {
                    method: 'GET',
                    headers: { 'Accept': 'application/json' }
                })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (!data || !data.success) {
                        throw new Error((data && data.message) ? data.message : 'Không lấy được danh sách phòng');
                    }
                    setPhongOptions(data.items || []);
                })
                .catch(function(err) {
                    phongSelect.innerHTML = '<option value="">-- Chọn phòng/địa điểm --</option>';
                    phongSelect.disabled = false;
                    console.error(err);
                });
            }

            khuSelect.addEventListener('change', loadPhongByKhu);

            // If server-rendered list is empty, load immediately
            try {
                const hasOnlyPlaceholder = (phongSelect.options && phongSelect.options.length <= 1);
                if ((khuSelect.value || '').trim() && hasOnlyPlaceholder) {
                    loadPhongByKhu();
                }
            } catch (e) {
                // ignore
            }
        })();
    </script>
</body>
</html>
