<?php
/**
 * Trang chủ - Hệ thống mượn trả thiết bị giảng dạy
 * Đại học Trà Vinh
 * 
 * @author System Development Team
 * @version 1.0
 * @date 2024
 */

// Hiển thị lỗi để debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Bắt đầu session để quản lý người dùng
session_start();

/**
 * Mock Equipment Database
 * Trong thực tế, dữ liệu sẽ được lấy từ MySQL database
 */
function getMockEquipment() {
    return [
        [
            'id' => 1,
            'name' => 'Máy chiếu BenQ MW612',
            'category' => 'Máy chiếu',
            'description' => 'Máy chiếu BenQ MW612 với độ phân giải Full HD, độ sáng 3000 lumens, phù hợp cho phòng học lớn.',
            'image' => 'images/projector.jpg',
            'available' => 5,
            'total' => 8,
            'location' => 'Khoa CNTT',
            'status' => 'available',
            'icon' => 'fas fa-projector'
        ],
        [
            'id' => 2,
            'name' => 'Cáp chuyển Mini Displayport ra HDMI',
            'category' => 'Cáp kết nối',
            'description' => 'Cáp chuyển đổi Mini Displayport sang HDMI chất lượng cao, hỗ trợ độ phân giải lên đến 4K.',
            'image' => 'images/laptop.jpg',
            'available' => 2,
            'total' => 10,
            'location' => 'Phòng Thiết bị',
            'status' => 'limited',
            'icon' => 'fas fa-plug'
        ],
        [
            'id' => 3,
            'name' => 'Loa trợ giảng Takstar E17',
            'category' => 'Loa, mic',
            'description' => 'Loa trợ giảng xách tay Takstar E17 New Edition với công suất 30W, pin lithium bền bỉ.',
            'image' => 'images/camera.jpg',
            'available' => 3,
            'total' => 5,
            'location' => 'Khoa Báo chí',
            'status' => 'available',
            'icon' => 'fas fa-microphone'
        ],
        [
            'id' => 4,
            'name' => 'Laptop Dell Latitude 5520',
            'category' => 'Laptop',
            'description' => 'Laptop Dell Latitude 5520, CPU Intel Core i5, RAM 8GB, SSD 256GB, phục vụ giảng dạy và thuyết trình.',
            'image' => 'images/laptop.jpg',
            'available' => 4,
            'total' => 6,
            'location' => 'Phòng Thiết bị',
            'status' => 'available',
            'icon' => 'fas fa-laptop'
        ],
        [
            'id' => 5,
            'name' => 'Máy ảnh Canon EOS 200D',
            'category' => 'Máy ảnh',
            'description' => 'Máy ảnh DSLR Canon EOS 200D với ống kính 18-55mm, phù hợp cho ghi hình giảng dạy.',
            'image' => 'images/camera.jpg',
            'available' => 2,
            'total' => 3,
            'location' => 'Khoa Báo chí',
            'status' => 'limited',
            'icon' => 'fas fa-camera'
        ],
        [
            'id' => 6,
            'name' => 'Tai nghe Logitech H390',
            'category' => 'Tai nghe',
            'description' => 'Tai nghe có micro Logitech H390, chất lượng âm thanh cao, phục vụ giảng dạy trực tuyến.',
            'image' => 'images/laptop.jpg',
            'available' => 8,
            'total' => 10,
            'location' => 'Phòng Thiết bị',
            'status' => 'available',
            'icon' => 'fas fa-headphones'
        ]
    ];
}

/**
 * Lấy danh sách thiết bị với filter
 */
function getEquipmentList($search = '', $category = '') {
    $equipment = getMockEquipment();
    
    // Filter theo tìm kiếm
    if (!empty($search)) {
        $equipment = array_filter($equipment, function($item) use ($search) {
            return stripos($item['name'], $search) !== false || 
                   stripos($item['description'], $search) !== false ||
                   stripos($item['category'], $search) !== false;
        });
    }
    
    // Filter theo category
    if (!empty($category)) {
        $equipment = array_filter($equipment, function($item) use ($category) {
            return $item['category'] === $category;
        });
    }
    
    return array_values($equipment);
}

// Xử lý tìm kiếm
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
$categoryFilter = isset($_GET['category']) ? trim($_GET['category']) : '';
$equipmentList = getEquipmentList($searchQuery, $categoryFilter);

// Lấy danh sách categories duy nhất
$allEquipment = getMockEquipment();
$categories = array_unique(array_column($allEquipment, 'category'));

// Kiểm tra trạng thái đăng nhập
$isLoggedIn = isset($_SESSION['user_id']);
$userData = null;
if ($isLoggedIn) {
    require_once __DIR__ . '/../includes/user.php';
    $userData = getUserInfo($_SESSION['user_id']);
}

// Thống kê hệ thống
$stats = [
    'total_equipment' => count($allEquipment),
    'total_items' => array_sum(array_column($allEquipment, 'total')),
    'available_items' => array_sum(array_column($allEquipment, 'available')),
    'total_users' => 2500,
    'total_borrows' => 5000
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Hệ thống mượn trả thiết bị giảng dạy trực tuyến tại Trường Đại học Trà Vinh. Đăng ký và mượn thiết bị giảng dạy một cách dễ dàng và hiệu quả.">
    <meta name="keywords" content="mượn thiết bị, đại học trà vinh, thiết bị giảng dạy, máy chiếu, laptop">
    <meta name="author" content="Trường Đại học Trà Vinh">
    <title>HỆ THỐNG MƯỢN TRẢ THIẾT BỊ GIẢNG DẠY - ĐH Trà Vinh</title>
    
    <!-- CSS Files -->
    <link rel="stylesheet" href="css/styleAbout.css?v=<?php echo time(); ?>">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Additional Styles for Enhanced Features -->
    <style>
        /* Hero Image Styles */
        .hero-image-img {
            width: 100%;
            height: auto;
            max-width: 86%; /* Điều chỉnh kích thước của ảnh menu */
            object-fit: contain;
            border-radius: var(--border-radius);
            animation: fadeInUp 0.8s ease-out;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Search Section Styles */
        .search-section {
            background-color: var(--bg-light);
            padding: 3rem 0;
        }
        
        .search-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }
        
        .search-form {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }
        
        .search-input-group {
            flex: 1;
            min-width: 250px;
        }
        
        .search-input {
            width: 100%;
            padding: 1rem 1.5rem;
            border: 2px solid var(--border-color);
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: var(--transition);
            outline: none;
        }
        
        .search-input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(44, 90, 160, 0.1);
        }
        
        .search-input::placeholder {
            color: var(--text-light);
        }
        
        .category-select {
            padding: 1rem 1.5rem;
            border: 2px solid var(--border-color);
            border-radius: var(--border-radius);
            font-size: 1rem;
            background-color: white;
            color: var(--text-color);
            cursor: pointer;
            transition: var(--transition);
            outline: none;
            min-width: 200px;
        }
        
        .category-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(44, 90, 160, 0.1);
        }
        
        .search-btn-submit {
            padding: 1rem 2rem;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: var(--border-radius);
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .search-btn-submit:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(44, 90, 160, 0.3);
        }
        
        .search-btn-submit:focus {
            outline: 3px solid rgba(44, 90, 160, 0.3);
            outline-offset: 2px;
        }
        
        /* Equipment Grid Enhanced */
        .equipment-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }
        
        .equipment-card {
            background-color: var(--bg-white);
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: var(--transition);
            border: 1px solid var(--border-color);
        }
        
        .equipment-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }
        
        .equipment-card:focus-within {
            outline: 3px solid var(--primary-color);
            outline-offset: 2px;
        }
        
        .equipment-image {
            position: relative;
            height: 200px;
            background: linear-gradient(135deg, var(--primary-light) 0%, var(--primary-color) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        
        .equipment-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .equipment-image .equipment-icon {
            font-size: 4rem;
            color: white;
            opacity: 0.8;
        }
        
        .equipment-status {
            position: absolute;
            top: 1rem;
            right: 1rem;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            color: white;
        }
        
        .equipment-status.available {
            background-color: var(--success, #28a745);
        }
        
        .equipment-status.limited {
            background-color: var(--warning, #ffc107);
            color: var(--text-color);
        }
        
        .equipment-status.unavailable {
            background-color: var(--danger, #dc3545);
        }
        
        .equipment-info {
            padding: 1.5rem;
        }
        
        .equipment-category {
            font-size: 0.85rem;
            color: var(--primary-color);
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 0.5rem;
        }
        
        .equipment-info h3 {
            font-size: 1.2rem;
            color: var(--text-color);
            margin-bottom: 0.75rem;
            font-weight: 600;
        }
        
        .equipment-info p {
            color: var(--text-light);
            font-size: 0.9rem;
            line-height: 1.6;
            margin-bottom: 1rem;
        }
        
        .equipment-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            color: var(--text-light);
        }
        
        .equipment-meta span {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .equipment-actions {
            display: flex;
            gap: 0.75rem;
        }
        
        .btn-borrow, .btn-detail {
            flex: 1;
            padding: 0.75rem 1rem;
            border: none;
            border-radius: var(--border-radius);
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            text-decoration: none;
            font-size: 0.9rem;
        }
        
        .btn-borrow {
            background-color: var(--primary-color);
            color: white;
        }
        
        .btn-borrow:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
        }
        
        .btn-borrow:focus {
            outline: 3px solid rgba(44, 90, 160, 0.3);
            outline-offset: 2px;
        }
        
        .btn-detail {
            background-color: transparent;
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
        }
        
        .btn-detail:hover {
            background-color: var(--primary-color);
            color: white;
        }
        
        .btn-detail:focus {
            outline: 3px solid rgba(44, 90, 160, 0.3);
            outline-offset: 2px;
        }
        
        /* Quick Links Section */
        .quick-links-section {
            background-color: var(--bg-white);
            padding: 3rem 0;
        }
        
        .quick-links-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }
        
        .quick-link-card {
            background-color: var(--bg-light);
            padding: 2rem;
            border-radius: var(--border-radius);
            text-align: center;
            transition: var(--transition);
            text-decoration: none;
            color: var(--text-color);
            border: 2px solid transparent;
        }
        
        .quick-link-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow);
            border-color: var(--primary-color);
        }
        
        .quick-link-card:focus {
            outline: 3px solid var(--primary-color);
            outline-offset: 2px;
        }
        
        .quick-link-icon {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }
        
        .quick-link-card h4 {
            font-size: 1.1rem;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }
        
        .quick-link-card p {
            font-size: 0.9rem;
            color: var(--text-light);
        }
        
        /* Accessibility Improvements */
        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border-width: 0;
        }
        
        /* Skip to main content link for screen readers */
        .skip-link {
            position: absolute;
            top: -40px;
            left: 0;
            background: var(--primary-color);
            color: white;
            padding: 8px;
            text-decoration: none;
            z-index: 1000;
        }
        
        .skip-link:focus {
            top: 0;
        }
        
        /* High contrast mode support */
        @media (prefers-contrast: high) {
            .nav-link, .btn-primary, .btn-secondary {
                border-width: 2px;
            }
        }
        
        /* Reduced motion support */
        @media (prefers-reduced-motion: reduce) {
            * {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }
        
        /* How it Works Section - 4 items in one row */
        #features .features-grid {
            grid-template-columns: repeat(4, 1fr);
            gap: 1.5rem;
        }
        
        #features .feature-card {
            padding: 1.5rem;
        }
        
        #features .feature-card h3 {
            font-size: 1.1rem;
            margin-bottom: 0.75rem;
        }
        
        #features .feature-card p {
            font-size: 0.9rem;
        }
        
        /* Responsive for How it Works section */
        @media (max-width: 1200px) {
            #features .features-grid {
                grid-template-columns: repeat(4, 1fr);
                gap: 1rem;
            }
            
            #features .feature-card {
                padding: 1.25rem;
            }
        }
        
        @media (max-width: 992px) {
            #features .features-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            #features .features-grid {
                grid-template-columns: 1fr;
            }
        }
        
        /* Print styles */
        @media print {
            .header, .nav-auth, .search-section, .cta-section, .footer {
                display: none;
            }
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
    </style>
</head>
<body>
    <!-- Skip to main content link for accessibility -->
    <a href="#main-content" class="skip-link">Bỏ qua đến nội dung chính</a>
    
    <!-- Header -->
    <header class="header" role="banner">
        <nav class="navbar" role="navigation" aria-label="Điều hướng chính">
            <div class="nav-container">
                <div class="nav-logo">
                    <a href="index.php" aria-label="Trang chủ - Đại học Trà Vinh">
                        <img src="images/tvu-logo.png" alt="Logo Trường Đại học Trà Vinh" class="logo">
                    </a>
                    <div class="system-name">
                        <h1>HỆ THỐNG MƯỢN TRẢ THIẾT BỊ</h1>
                        <span>Trường Đại học Trà Vinh</span>
                    </div>
                </div>
                <div class="nav-menu" role="menubar">
                    <a href="index.php" class="nav-link active" aria-current="page" role="menuitem">Trang chủ</a>
                    <a href="about.php" class="nav-link" role="menuitem">Giới thiệu</a>
                    <a href="equipment.php" class="nav-link" role="menuitem">Thiết bị</a>
                    <a href="regulations.php" class="nav-link" role="menuitem">Quy định & Hướng dẫn</a>
                    <a href="contact.php" class="nav-link" role="menuitem">Liên hệ</a>
                    <?php if ($isLoggedIn): ?>
                        <a href="dashboard.php" class="nav-link" role="menuitem">Dashboard</a>
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
                        <a href="logout.php" class="btn-login" aria-label="Đăng xuất">
                            <i class="fas fa-sign-out-alt" aria-hidden="true"></i> Đăng xuất
                        </a>
                    <?php else: ?>
                        <!-- User chưa đăng nhập -->
                        <a href="login.php" class="btn-login" aria-label="Đăng nhập vào hệ thống">
                            <i class="fas fa-sign-in-alt" aria-hidden="true"></i> Đăng nhập
                        </a>
                        <a href="register.php" class="btn-register" aria-label="Đăng ký tài khoản mới">
                            <i class="fas fa-user-plus" aria-hidden="true"></i> Đăng ký
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main id="main-content" role="main">
        <!-- Hero Section -->
        <section class="hero-section" aria-labelledby="hero-title">
            <div class="hero-container">
                <div class="hero-content">
                    <h1 class="hero-title" id="hero-title">Đơn giản hóa việc mượn trả thiết bị giảng dạy</h1>
                    <p class="hero-description">Hệ thống trực tuyến giúp giảng viên, sinh viên dễ dàng đặt mượn và quản lý thiết bị giảng dạy một cách hiệu quả</p>
                    <div class="hero-actions">
                        <?php if (!$isLoggedIn): ?>
                            <a href="register.php" class="btn-primary" aria-label="Đăng ký tài khoản mới để bắt đầu">
                                <i class="fas fa-rocket" aria-hidden="true"></i> Bắt đầu ngay
                            </a>
                        <?php else: ?>
                            <a href="equipment.php" class="btn-primary" aria-label="Xem danh sách thiết bị">
                                <i class="fas fa-search" aria-hidden="true"></i> Đặt thiết bị ngay
                            </a>
                        <?php endif; ?>
                        <a href="#features" class="btn-secondary" aria-label="Tìm hiểu thêm về hệ thống">
                            <i class="fas fa-info-circle" aria-hidden="true"></i> Tìm hiểu thêm
                        </a>
                    </div>
                </div>
                <div class="hero-image">
                    <img src="images/hero-equipment.png" 
                         alt="Thiết bị giảng dạy - Hệ thống mượn trả thiết bị Đại học Trà Vinh" 
                         class="hero-image-img">
                </div>
            </div>
        </section>

        <!-- Quick Links Section -->
        <section class="quick-links-section" aria-labelledby="quick-links-title">
            <div class="container">
                <div class="section-header">
                    <h2 id="quick-links-title">Liên kết nhanh</h2>
                    <p>Truy cập nhanh các tính năng quan trọng</p>
                </div>
                <div class="quick-links-grid" role="list">
                    <a href="equipment.php" class="quick-link-card" role="listitem" aria-label="Xem danh sách tất cả thiết bị">
                        <div class="quick-link-icon">
                            <i class="fas fa-list" aria-hidden="true"></i>
                        </div>
                        <h4>Danh sách thiết bị</h4>
                        <p>Xem tất cả thiết bị có sẵn</p>
                    </a>
                    <?php if ($isLoggedIn): ?>
                        <a href="dashboard.php" class="quick-link-card" role="listitem" aria-label="Xem lịch sử mượn thiết bị">
                            <div class="quick-link-icon">
                                <i class="fas fa-history" aria-hidden="true"></i>
                            </div>
                            <h4>Lịch sử mượn</h4>
                            <p>Xem các thiết bị đã mượn</p>
                        </a>
                        <a href="dashboard.php" class="quick-link-card" role="listitem" aria-label="Đi đến bảng điều khiển">
                            <div class="quick-link-icon">
                                <i class="fas fa-tachometer-alt" aria-hidden="true"></i>
                            </div>
                            <h4>Bảng điều khiển</h4>
                            <p>Quản lý tài khoản của bạn</p>
                        </a>
                    <?php else: ?>
                        <a href="register.php" class="quick-link-card" role="listitem" aria-label="Đăng ký tài khoản mới">
                            <div class="quick-link-icon">
                                <i class="fas fa-user-plus" aria-hidden="true"></i>
                            </div>
                            <h4>Đăng ký</h4>
                            <p>Tạo tài khoản mới</p>
                        </a>
                        <a href="login.php" class="quick-link-card" role="listitem" aria-label="Đăng nhập vào hệ thống">
                            <div class="quick-link-icon">
                                <i class="fas fa-sign-in-alt" aria-hidden="true"></i>
                            </div>
                            <h4>Đăng nhập</h4>
                            <p>Truy cập tài khoản của bạn</p>
                        </a>
                    <?php endif; ?>
                    <a href="regulations.php" class="quick-link-card" role="listitem" aria-label="Xem hướng dẫn sử dụng">
                        <div class="quick-link-icon">
                            <i class="fas fa-book" aria-hidden="true"></i>
                        </div>
                        <h4>Hướng dẫn</h4>
                        <p>Cách sử dụng hệ thống</p>
                    </a>
                    <a href="contact.php" class="quick-link-card" role="listitem" aria-label="Liên hệ với chúng tôi">
                        <div class="quick-link-icon">
                            <i class="fas fa-envelope" aria-hidden="true"></i>
                        </div>
                        <h4>Liên hệ</h4>
                        <p>Hỗ trợ và phản hồi</p>
                    </a>
                </div>
            </div>
        </section>

        <!-- How it Works Section -->
        <section id="features" class="about-section" aria-labelledby="how-it-works-title">
            <div class="container">
                <div class="section-header">
                    <h2 id="how-it-works-title">4 Bước Đơn Giản Để Mượn Thiết Bị</h2>
                    <p>Quy trình mượn trả thiết bị nhanh chóng và tiện lợi</p>
                </div>
                <div class="features-grid" role="list">
                    <div class="feature-card" role="listitem">
                        <div class="feature-icon">
                            <i class="fas fa-sign-in-alt" aria-hidden="true"></i>
                        </div>
                        <h3>Đăng nhập</h3>
                        <p>Sử dụng tài khoản nhà trường để đăng nhập vào hệ thống</p>
                    </div>
                    
                    <div class="feature-card" role="listitem">
                        <div class="feature-icon">
                            <i class="fas fa-search" aria-hidden="true"></i>
                        </div>
                        <h3>Tìm kiếm</h3>
                        <p>Tìm thiết bị cần mượn theo danh mục hoặc sử dụng công cụ tìm kiếm</p>
                    </div>
                    
                    <div class="feature-card" role="listitem">
                        <div class="feature-icon">
                            <i class="fas fa-calendar-check" aria-hidden="true"></i>
                        </div>
                        <h3>Đặt lịch mượn</h3>
                        <p>Chọn thời gian mượn và trả thiết bị phù hợp với nhu cầu</p>
                    </div>
                    
                    <div class="feature-card" role="listitem">
                        <div class="feature-icon">
                            <i class="fas fa-check-circle" aria-hidden="true"></i>
                        </div>
                        <h3>Nhận thiết bị</h3>
                        <p>Đến phòng quản lý thiết bị để nhận thiết bị theo lịch hẹn</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Benefits Section -->
        <section class="benefits-section" aria-labelledby="benefits-title">
            <div class="container">
                <div class="section-header">
                    <h2 id="benefits-title">Lợi Ích Của Hệ Thống</h2>
                    <p>Hệ thống mang lại nhiều lợi ích thiết thực cho cả giảng viên, sinh viên và nhà quản lý</p>
                </div>
                <div class="benefits-content">
                    <div class="benefits-image">
                        <div class="image-placeholder-benefits" aria-hidden="true">
                            <i class="fas fa-chart-line"></i>
                        </div>
                    </div>
                    <div class="benefits-list" role="list">
                        <div class="benefit-item" role="listitem">
                            <div class="benefit-icon" aria-hidden="true">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="benefit-text">
                                <h4>Tiết kiệm thời gian</h4>
                                <p>Giảm thiểu thời gian chờ đợi và thủ tục hành chính trong việc mượn trả thiết bị.</p>
                            </div>
                        </div>
                        <div class="benefit-item" role="listitem">
                            <div class="benefit-icon" aria-hidden="true">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="benefit-text">
                                <h4>Quản lý tập trung</h4>
                                <p>Quản lý tập trung toàn bộ thiết bị giảng dạy, dễ dàng theo dõi tình trạng và lịch sử sử dụng.</p>
                            </div>
                        </div>
                        <div class="benefit-item" role="listitem">
                            <div class="benefit-icon" aria-hidden="true">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="benefit-text">
                                <h4>Minh bạch thông tin</h4>
                                <p>Cung cấp thông tin minh bạch về tình trạng thiết bị, lịch mượn và các quy định sử dụng.</p>
                            </div>
                        </div>
                        <div class="benefit-item" role="listitem">
                            <div class="benefit-icon" aria-hidden="true">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="benefit-text">
                                <h4>Tối ưu hóa sử dụng</h4>
                                <p>Giúp tối ưu hóa việc sử dụng thiết bị, giảm thiểu thời gian thiết bị không được sử dụng.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- News & Announcements Section -->
        <section class="about-section" aria-labelledby="news-title">
            <div class="container">
                <div class="section-header">
                    <h2 id="news-title">Tin tức & Thông báo</h2>
                    <p>Cập nhật thông tin mới nhất từ hệ thống</p>
                </div>
                <div class="features-grid" role="list">
                    <article class="feature-card" role="listitem">
                        <div class="feature-icon" aria-hidden="true">
                            <i class="fas fa-newspaper"></i>
                        </div>
                        <h3>Bổ sung thiết bị giảng dạy mới</h3>
                        <p>Nhà trường vừa bổ sung 20 máy chiếu và 15 laptop phục vụ công tác giảng dạy. Các thiết bị mới đã được cập nhật vào hệ thống và sẵn sàng cho việc mượn.</p>
                        <a href="news.php?id=1" class="btn-detail" style="margin-top: 1rem; display: inline-block;">
                            Đọc thêm <i class="fas fa-arrow-right" aria-hidden="true"></i>
                        </a>
                    </article>
                    
                    <article class="feature-card" role="listitem">
                        <div class="feature-icon" aria-hidden="true">
                            <i class="fas fa-tools"></i>
                        </div>
                        <h3>Lịch bảo trì hệ thống</h3>
                        <p>Hệ thống sẽ được bảo trì từ 22h00 ngày 20/12 đến 06h00 ngày 21/12. Trong thời gian này, hệ thống sẽ tạm thời không thể truy cập.</p>
                        <a href="news.php?id=2" class="btn-detail" style="margin-top: 1rem; display: inline-block;">
                            Đọc thêm <i class="fas fa-arrow-right" aria-hidden="true"></i>
                        </a>
                    </article>
                    
                    <article class="feature-card" role="listitem">
                        <div class="feature-icon" aria-hidden="true">
                            <i class="fas fa-book"></i>
                        </div>
                        <h3>Hướng dẫn sử dụng thiết bị mới</h3>
                        <p>Tài liệu hướng dẫn sử dụng các thiết bị công nghệ mới đã được cập nhật. Vui lòng tham khảo trước khi mượn thiết bị.</p>
                        <a href="news.php?id=3" class="btn-detail" style="margin-top: 1rem; display: inline-block;">
                            Đọc thêm <i class="fas fa-arrow-right" aria-hidden="true"></i>
                        </a>
                    </article>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="cta-section" aria-labelledby="cta-title">
            <div class="container">
                <div class="cta-content">
                    <h2 id="cta-title">Sẵn sàng trải nghiệm hệ thống?</h2>
                    <p>Đăng ký ngay hôm nay để bắt đầu sử dụng hệ thống mượn trả thiết bị hiện đại và tiện lợi</p>
                    <div class="cta-actions">
                        <?php if (!$isLoggedIn): ?>
                            <a href="register.php" class="btn-primary" aria-label="Đăng ký tài khoản mới">
                                <i class="fas fa-user-plus" aria-hidden="true"></i> Đăng ký tài khoản
                            </a>
                        <?php else: ?>
                            <a href="equipment.php" class="btn-primary" aria-label="Xem danh sách thiết bị">
                                <i class="fas fa-search" aria-hidden="true"></i> Tìm thiết bị ngay
                            </a>
                        <?php endif; ?>
                        <a href="contact.php" class="btn-secondary" aria-label="Liên hệ hỗ trợ">
                            <i class="fas fa-headset" aria-hidden="true"></i> Liên hệ hỗ trợ
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="footer" role="contentinfo">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <div class="footer-logo">
                        <img src="images/tvu-logo.png" alt="Logo Trường Đại học Trà Vinh" class="logo">
                        <div class="system-name">
                            <h3>HỆ THỐNG MƯỢN TRẢ THIẾT BỊ</h3>
                            <span>Trường Đại học Trà Vinh</span>
                        </div>
                    </div>
                    <p>Hệ thống quản lý và cho mượn thiết bị giảng dạy hiện đại, hiệu quả tại Trường Đại học Trà Vinh.</p>
                    <div class="social-links" role="list">
                        <a href="#" aria-label="Facebook Trường Đại học Trà Vinh" role="listitem">
                            <i class="fab fa-facebook-f" aria-hidden="true"></i>
                        </a>
                        <a href="#" aria-label="Twitter Trường Đại học Trà Vinh" role="listitem">
                            <i class="fab fa-twitter" aria-hidden="true"></i>
                        </a>
                        <a href="#" aria-label="LinkedIn Trường Đại học Trà Vinh" role="listitem">
                            <i class="fab fa-linkedin-in" aria-hidden="true"></i>
                        </a>
                        <a href="#" aria-label="YouTube Trường Đại học Trà Vinh" role="listitem">
                            <i class="fab fa-youtube" aria-hidden="true"></i>
                        </a>
                    </div>
                </div>
                <div class="footer-section">
                    <h4>Liên kết nhanh</h4>
                    <ul role="list">
                        <li><a href="index.php">Trang chủ</a></li>
                        <li><a href="about.php">Giới thiệu</a></li>
                        <li><a href="equipment.php">Thiết bị</a></li>
                        <li><a href="regulations.php">Quy định & Hướng dẫn</a></li>
                        <li><a href="contact.php">Liên hệ</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Dịch vụ</h4>
                    <ul role="list">
                        <li><a href="equipment.php">Mượn thiết bị</a></li>
                        <li><a href="equipment.php">Tra cứu thiết bị</a></li>
                        <li><a href="regulations.php">Hướng dẫn sử dụng</a></li>
                        <li><a href="regulations.php">Quy định mượn trả</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Liên hệ</h4>
                    <div class="contact-info">
                        <p>
                            <i class="fas fa-map-marker-alt" aria-hidden="true"></i> 
                            Số 126, Nguyễn Thiện Thành, Khóm 4, Phường 5, TP. Trà Vinh
                        </p>
                        <p>
                            <i class="fas fa-phone" aria-hidden="true"></i> 
                            <a href="tel:+842943855959" aria-label="Gọi điện: 0294 3 855 959">(0294) 3 855 959</a>
                        </p>
                        <p>
                            <i class="fas fa-envelope" aria-hidden="true"></i> 
                            <a href="mailto:info@tvu.edu.vn" aria-label="Gửi email: info@tvu.edu.vn">info@tvu.edu.vn</a>
                        </p>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> Trường Đại học Trà Vinh. Tất cả các quyền được bảo lưu.</p>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="js/script.js"></script>
    <script>
        // Enhanced accessibility: Keyboard navigation support
        document.addEventListener('DOMContentLoaded', function() {
            // Skip link functionality
            const skipLink = document.querySelector('.skip-link');
            if (skipLink) {
                skipLink.addEventListener('click', function(e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.focus();
                        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                });
            }
            
            // Enhanced keyboard navigation for cards
            const cards = document.querySelectorAll('.equipment-card, .quick-link-card, .feature-card');
            cards.forEach(card => {
                card.setAttribute('tabindex', '0');
                card.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        const link = this.querySelector('a');
                        if (link) {
                            link.click();
                        }
                    }
                });
            });
            
            
            // Announce dynamic content changes to screen readers
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.addedNodes.length) {
                        // Content was added, could announce to screen readers
                    }
                });
            });
            
            const mainContent = document.getElementById('main-content');
            if (mainContent) {
                observer.observe(mainContent, { childList: true, subtree: true });
            }
        });
    </script>
</body>
</html>
