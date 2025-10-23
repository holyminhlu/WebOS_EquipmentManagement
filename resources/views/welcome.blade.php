<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HỆ THỐNG MƯỢN TRẢ THIẾT BỊ GIẢNG DẠY - ĐH Trà Vinh</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <nav class="navbar">
            <div class="nav-container">
                <div class="nav-logo">
                    <img src="images/tvu-logo.png" alt="TVU Logo" class="logo">
                    <div class="system-name">
                        <h1>HỆ THỐNG MƯỢN TRẢ THIẾT BỊ</h1>
                        <span>Trường Đại học Trà Vinh</span>
                    </div>
                </div>
                
                <div class="nav-search">
                    <div class="search-box">
                        <input type="text" placeholder="Tìm kiếm thiết bị, mã số...">
                        <button class="search-btn">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                
                <div class="nav-auth">
                    <button class="btn-login">
                        <i class="fas fa-sign-in-alt"></i>
                        Đăng nhập
                    </button>
                    <button class="btn-register">
                        <i class="fas fa-user-plus"></i>
                        Đăng ký
                    </button>
                </div>
            </div>
        </nav>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-container">
            <div class="hero-content">
                <h2>Đơn giản hóa việc mượn trả thiết bị giảng dạy</h2>
                <p>Hệ thống trực tuyến giúp giảng viên, sinh viên dễ dàng đặt mượn và quản lý thiết bị giảng dạy một cách hiệu quả</p>
                <div class="hero-buttons">
                    <button class="btn-primary">
                        <i class="fas fa-play-circle"></i>
                        Bắt đầu ngay
                    </button>
                    <button class="btn-secondary">
                        <i class="fas fa-info-circle"></i>
                        Tìm hiểu thêm
                    </button>
                </div>
            </div>
            <div class="hero-image">
                <img src="images/hero-equipment.png" alt="Thiết bị giảng dạy">
            </div>
        </div>
    </section>

    <!-- Quick Stats -->
    <section class="stats">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="fas fa-laptop"></i>
                    </div>
                    <div class="stat-info">
                        <h3>150+</h3>
                        <p>Thiết bị hiện có</p>
                    </div>
                </div>
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3>2,500+</h3>
                        <p>Người dùng</p>
                    </div>
                </div>
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-info">
                        <h3>5,000+</h3>
                        <p>Lượt mượn thành công</p>
                    </div>
                </div>
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-info">
                        <h3>24/7</h3>
                        <p>Hỗ trợ trực tuyến</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Equipment -->
    <section class="equipment">
        <div class="container">
            <div class="section-header">
                <h2>Thiết bị nổi bật</h2>
                <p>Những thiết bị được sử dụng phổ biến nhất</p>
            </div>
            
            <div class="equipment-grid">
                <!-- Equipment Item 1 -->
                <div class="equipment-card">
                    <div class="equipment-image">
                        <img src="images/projector.jpg" alt="Máy chiếu">
                        <span class="status available">Có sẵn</span>
                    </div>
                    <div class="equipment-info">
                        <h3>Máy chiếu Sony VPL-DX120</h3>
                        <p class="equipment-desc">Độ phân giải XGA, 3200 lumens</p>
                        <div class="equipment-meta">
                            <span><i class="fas fa-map-marker-alt"></i> Khoa CNTT</span>
                            <span><i class="fas fa-cube"></i> 5/8 có sẵn</span>
                        </div>
                        <div class="equipment-actions">
                            <button class="btn-detail">
                                <i class="fas fa-eye"></i>
                                Chi tiết
                            </button>
                            <button class="btn-borrow">
                                <i class="fas fa-cart-plus"></i>
                                Đặt mượn
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Equipment Item 2 -->
                <div class="equipment-card">
                    <div class="equipment-image">
                        <img src="images/laptop.jpg" alt="Laptop">
                        <span class="status limited">Số lượng ít</span>
                    </div>
                    <div class="equipment-info">
                        <h3>Laptop Dell Latitude 5420</h3>
                        <p class="equipment-desc">Core i5, RAM 8GB, SSD 256GB</p>
                        <div class="equipment-meta">
                            <span><i class="fas fa-map-marker-alt"></i> Phòng Thiết bị</span>
                            <span><i class="fas fa-cube"></i> 2/10 có sẵn</span>
                        </div>
                        <div class="equipment-actions">
                            <button class="btn-detail">
                                <i class="fas fa-eye"></i>
                                Chi tiết
                            </button>
                            <button class="btn-borrow">
                                <i class="fas fa-cart-plus"></i>
                                Đặt mượn
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Equipment Item 3 -->
                <div class="equipment-card">
                    <div class="equipment-image">
                        <img src="images/camera.jpg" alt="Máy ảnh">
                        <span class="status available">Có sẵn</span>
                    </div>
                    <div class="equipment-info">
                        <h3>Máy ảnh Canon EOS 200D</h3>
                        <p class="equipment-desc">Kit 18-55mm, Cảm biến 24.2MP</p>
                        <div class="equipment-meta">
                            <span><i class="fas fa-map-marker-alt"></i> Khoa Báo chí</span>
                            <span><i class="fas fa-cube"></i> 3/5 có sẵn</span>
                        </div>
                        <div class="equipment-actions">
                            <button class="btn-detail">
                                <i class="fas fa-eye"></i>
                                Chi tiết
                            </button>
                            <button class="btn-borrow">
                                <i class="fas fa-cart-plus"></i>
                                Đặt mượn
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="section-footer">
                <button class="btn-view-all">
                    <i class="fas fa-list"></i>
                    Xem tất cả thiết bị
                </button>
            </div>
        </div>
    </section>

    <!-- How it Works -->
    <section class="how-it-works">
        <div class="container">
            <div class="section-header">
                <h2>4 Bước Đơn Giản Để Mượn Thiết Bị</h2>
                <p>Quy trình mượn trả thiết bị nhanh chóng và tiện lợi</p>
            </div>
            
            <div class="steps-grid">
                <div class="step-item">
                    <div class="step-number">1</div>
                    <div class="step-icon">
                        <i class="fas fa-sign-in-alt"></i>
                    </div>
                    <h3>Đăng nhập</h3>
                    <p>Sử dụng tài khoản nhà trường để đăng nhập vào hệ thống</p>
                </div>
                
                <div class="step-item">
                    <div class="step-number">2</div>
                    <div class="step-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3>Tìm kiếm</h3>
                    <p>Tìm thiết bị cần mượn theo danh mục hoặc sử dụng công cụ tìm kiếm</p>
                </div>
                
                <div class="step-item">
                    <div class="step-number">3</div>
                    <div class="step-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <h3>Đặt lịch mượn</h3>
                    <p>Chọn thời gian mượn và trả thiết bị phù hợp với nhu cầu</p>
                </div>
                
                <div class="step-item">
                    <div class="step-number">4</div>
                    <div class="step-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h3>Nhận thiết bị</h3>
                    <p>Đến phòng quản lý thiết bị để nhận thiết bị theo lịch hẹn</p>
                </div>
            </div>
        </div>
    </section>

    <!-- News & Announcements -->
    <section class="news">
        <div class="container">
            <div class="section-header">
                <h2>Tin tức & Thông báo</h2>
                <p>Cập nhật thông tin mới nhất từ hệ thống</p>
            </div>
            
            <div class="news-grid">
                <div class="news-card">
                    <div class="news-date">
                        <span class="day">15</span>
                        <span class="month">TH12</span>
                    </div>
                    <div class="news-content">
                        <h3>Bổ sung thiết bị giảng dạy mới</h3>
                        <p>Nhà trường vừa bổ sung 20 máy chiếu và 15 laptop phục vụ công tác giảng dạy...</p>
                        <a href="#" class="news-link">Đọc thêm <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                
                <div class="news-card">
                    <div class="news-date">
                        <span class="day">10</span>
                        <span class="month">TH12</span>
                    </div>
                    <div class="news-content">
                        <h3>Lịch bảo trì hệ thống</h3>
                        <p>Hệ thống sẽ được bảo trì từ 22h00 ngày 20/12 đến 06h00 ngày 21/12...</p>
                        <a href="#" class="news-link">Đọc thêm <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                
                <div class="news-card">
                    <div class="news-date">
                        <span class="day">05</span>
                        <span class="month">TH12</span>
                    </div>
                    <div class="news-content">
                        <h3>Hướng dẫn sử dụng thiết bị mới</h3>
                        <p>Tài liệu hướng dẫn sử dụng các thiết bị công nghệ mới đã được cập nhật...</p>
                        <a href="#" class="news-link">Đọc thêm <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col">
                    <div class="footer-logo">
                        <img src="images/tvu-logo.png" alt="TVU Logo">
                        <h3>Đại học Trà Vinh</h3>
                    </div>
                    <p>Hệ thống mượn trả thiết bị giảng dạy trực tuyến, phục vụ công tác giảng dạy và học tập.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>
                
                <div class="footer-col">
                    <h4>Liên kết nhanh</h4>
                    <ul>
                        <li><a href="#">Trang chủ</a></li>
                        <li><a href="#">Danh mục thiết bị</a></li>
                        <li><a href="#">Hướng dẫn sử dụng</a></li>
                        <li><a href="#">Quy định mượn trả</a></li>
                        <li><a href="#">Câu hỏi thường gặp</a></li>
                    </ul>
                </div>
                
                <div class="footer-col">
                    <h4>Thông tin liên hệ</h4>
                    <div class="contact-info">
                        <p><i class="fas fa-map-marker-alt"></i> 126 Nguyễn Thiện Thành, Khóm 4, P. 5, Tp. Trà Vinh</p>
                        <p><i class="fas fa-phone"></i> (0294) 3 855 959</p>
                        <p><i class="fas fa-envelope"></i> thietbi@tvu.edu.vn</p>
                        <p><i class="fas fa-clock"></i> Thứ 2 - Thứ 6: 7h00 - 17h00</p>
                    </div>
                </div>
                
                <div class="footer-col">
                    <h4>Hỗ trợ</h4>
                    <div class="support-info">
                        <p>Hotline hỗ trợ: <strong>1800 1234</strong></p>
                        <p>Email hỗ trợ: <strong>support@tvu.edu.vn</strong></p>
                        <button class="btn-support">
                            <i class="fas fa-headset"></i>
                            Chat với hỗ trợ viên
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2024 Trường Đại học Trà Vinh. Tất cả các quyền được bảo lưu.</p>
            </div>
        </div>
    </footer>

    <script src="js/script.js"></script>
</body>
</html>