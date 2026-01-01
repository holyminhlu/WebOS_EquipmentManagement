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
                                        <button class="btn-request" onclick="requestEquipment('<?php echo $eq['MaThietBi']; ?>')">
                                            <i class="fas fa-hand-paper"></i> Yêu cầu mượn
                                        </button>
                                    <?php else: ?>
                                        <button class="btn-request" disabled>
                                            <i class="fas fa-ban"></i> Không khả dụng
                                        </button>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <button class="btn-request" onclick="window.location.href='login.php'">
                                        <i class="fas fa-sign-in-alt"></i> Đăng nhập để mượn
                                    </button>
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

    <script>
        function requestEquipment(maThietBi) {
            if (confirm('Bạn có chắc chắn muốn yêu cầu mượn thiết bị này?')) {
                // Gửi AJAX request để tạo yêu cầu mượn
                const formData = new FormData();
                formData.append('maThietBi', maThietBi);
                formData.append('mucDich', 'Phục vụ giảng dạy');
                
                fetch('actions/create_yeucaumuon.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        // Reload trang để cập nhật trạng thái
                        window.location.reload();
                    } else {
                        alert('Lỗi: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Lỗi kết nối: ' + error.message);
                });
            }
        }
    </script>
</body>
</html>
