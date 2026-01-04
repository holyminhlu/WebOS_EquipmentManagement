<?php
/**
 * Trang Giới thiệu - Hệ thống mượn trả thiết bị giảng dạy
 */

session_start();

// Lấy thông tin user nếu đã đăng nhập
$isLoggedIn = isset($_SESSION['user_id']);
$userData = null;
if ($isLoggedIn) {
    require_once __DIR__ . '/../includes/user.php';
    $userData = getUserInfo($_SESSION['user_id']);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giới thiệu - Hệ thống mượn trả thiết bị</title>
    <link rel="stylesheet" href="css/styleAbout.css?v=<?php echo time(); ?>">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Đẩy nội dung lên cao hơn, gần menu */
        .about-section:first-of-type {
            padding-top: 1rem;
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
    <!-- Header -->
    <header class="header">
        <nav class="navbar">
            <div class="nav-container">
                <div class="nav-logo">
                    <a href="index.php" aria-label="Trang chủ">
                        <img src="images/tvu-logo.png" alt="Logo Trường Đại học Trà Vinh" class="logo">
                    </a>
                    <div class="system-name">
                        <h1>HỆ THỐNG MƯỢN TRẢ THIẾT BỊ</h1>
                        <span>Trường Đại học Trà Vinh</span>
                    </div>
                </div>
                <div class="nav-menu">
                    <a href="index.php" class="nav-link">Trang chủ</a>
                    <a href="about.php" class="nav-link active">Giới thiệu</a>
                    <a href="equipment.php" class="nav-link">Thiết bị</a>
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

   

    <!-- About Section -->
    <section class="about-section">
        <div class="container">
            <div class="section-header">
                <h2>Về Hệ Thống Của Chúng Tôi</h2>
                <p>Hệ thống được phát triển để nâng cao hiệu quả quản lý và sử dụng thiết bị giảng dạy</p>
            </div>
            <div class="about-content">
                <div class="about-text">
                    <h3>Giới thiệu tổng quan</h3>
                    <p>Hệ thống mượn trả thiết bị giảng dạy tại Trường Đại học Trà Vinh là một giải pháp công nghệ hiện đại, được thiết kế để quản lý và tối ưu hóa việc sử dụng các thiết bị phục vụ công tác giảng dạy và học tập.</p>
                    <p>Với hệ thống này, giảng viên và sinh viên có thể dễ dàng đăng ký mượn, theo dõi tình trạng và trả thiết bị một cách nhanh chóng, minh bạch.</p>
                    
                    <div class="stats-grid">
                        <div class="stat-item">
                            <div class="stat-number">400+</div>
                            <div class="stat-label">Thiết bị hiện có</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">3,000+</div>
                            <div class="stat-label">Người dùng</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">15,000+</div>
                            <div class="stat-label">Lượt mượn/năm</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">99%</div>
                            <div class="stat-label">Hài lòng</div>
                        </div>
                    </div>
                </div>
                <div class="about-image">
                    <div class="image-card">
                        <i class="fas fa-university"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="features-section">
        <div class="container">
            <div class="section-header">
                <h2>Tính Năng Nổi Bật</h2>
                <p>Những tính năng giúp hệ thống trở nên hiệu quả và thân thiện với người dùng</p>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3>Tìm kiếm thông minh</h3>
                    <p>Tìm kiếm và đặt trước thiết bị nhanh chóng với hệ thống tìm kiếm thông minh, hỗ trợ lọc theo nhiều tiêu chí.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <h3>Đặt lịch linh hoạt</h3>
                    <p>Hệ thống cho phép đặt lịch mượn thiết bị linh hoạt theo thời gian rảnh của người dùng và tình trạng thiết bị.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-bell"></i>
                    </div>
                    <h3>Thông báo tự động</h3>
                    <p>Nhận thông báo tự động về lịch mượn, nhắc nhở trả thiết bị và các thông tin quan trọng khác.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <h3>Báo cáo chi tiết</h3>
                    <p>Theo dõi và xuất báo cáo chi tiết về tình hình sử dụng thiết bị, lịch sử mượn trả và thống kê hiệu quả.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <h3>Responsive</h3>
                    <p>Hệ thống hoạt động tốt trên mọi thiết bị từ máy tính để bàn, máy tính bảng đến điện thoại thông minh.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3>Bảo mật cao</h3>
                    <p>Đảm bảo an toàn thông tin người dùng với hệ thống bảo mật nhiều lớp và mã hóa dữ liệu nhạy cảm.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Benefits Section -->
    <section class="benefits-section">
        <div class="container">
            <div class="section-header">
                <h2>Lợi Ích Của Hệ Thống</h2>
                <p>Hệ thống mang lại nhiều lợi ích thiết thực cho cả giảng viên, sinh viên và nhà quản lý</p>
            </div>
            <div class="benefits-content">
                <div class="benefits-image">
                    <div class="image-placeholder-benefits">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
                <div class="benefits-list">
                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="benefit-text">
                            <h4>Tiết kiệm thời gian</h4>
                            <p>Giảm thiểu thời gian chờ đợi và thủ tục hành chính trong việc mượn trả thiết bị.</p>
                        </div>
                    </div>
                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="benefit-text">
                            <h4>Quản lý tập trung</h4>
                            <p>Quản lý tập trung toàn bộ thiết bị giảng dạy, dễ dàng theo dõi tình trạng và lịch sử sử dụng.</p>
                        </div>
                    </div>
                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="benefit-text">
                            <h4>Minh bạch thông tin</h4>
                            <p>Cung cấp thông tin minh bạch về tình trạng thiết bị, lịch mượn và các quy định sử dụng.</p>
                        </div>
                    </div>
                    <div class="benefit-item">
                        <div class="benefit-icon">
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

    <!-- Team Section -->
    <section class="team-section">
        <div class="container">
            <div class="section-header">
                <h2>Đội Ngũ Phát Triển</h2>
                <p>Hệ thống được phát triển bởi đội ngũ chuyên gia giàu kinh nghiệm</p>
            </div>
            <div class="team-grid">
                <div class="team-card">
                    <div class="team-avatar">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <h3>TS. Nguyễn Văn A</h3>
                    <p class="team-role">Trưởng nhóm phát triển</p>
                    <p class="team-bio">Chuyên gia công nghệ thông tin với 10 năm kinh nghiệm trong lĩnh vực phát triển hệ thống quản lý giáo dục.</p>
                </div>
                <div class="team-card">
                    <div class="team-avatar">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <h3>ThS. Trần Thị B</h3>
                    <p class="team-role">Chuyên gia phân tích hệ thống</p>
                    <p class="team-bio">Chuyên gia phân tích nghiệp vụ và thiết kế hệ thống, có nhiều kinh nghiệm trong lĩnh vực giáo dục đại học.</p>
                </div>
                <div class="team-card">
                    <div class="team-avatar">
                        <i class="fas fa-code"></i>
                    </div>
                    <h3>KS. Lê Văn C</h3>
                    <p class="team-role">Lập trình viên Full-stack</p>
                    <p class="team-bio">Lập trình viên full-stack với kỹ năng chuyên sâu về PHP, JavaScript và các framework hiện đại.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-content">
                <h2>Sẵn sàng trải nghiệm hệ thống?</h2>
                <p>Đăng ký ngay hôm nay để bắt đầu sử dụng hệ thống mượn trả thiết bị hiện đại và tiện lợi</p>
                <div class="cta-actions">
                    <a href="register.php" class="btn-primary">Đăng ký tài khoản</a>
                    <a href="contact.php" class="btn-secondary">Liên hệ hỗ trợ</a>
                </div>
            </div>
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
                        <li><a href="services.php">Dịch vụ</a></li>
                        <li><a href="contact.php">Liên hệ</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Dịch vụ</h4>
                    <ul>
                        <li><a href="#">Mượn thiết bị</a></li>
                        <li><a href="#">Tra cứu thiết bị</a></li>
                        <li><a href="#">Hướng dẫn sử dụng</a></li>
                        <li><a href="#">Quy định mượn trả</a></li>
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

    <script src="js/scriptAbout.js"></script>
</body>
</html>