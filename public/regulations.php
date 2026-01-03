<?php
/**
 * Trang Quy định & Hướng dẫn - Hệ thống mượn trả thiết bị giảng dạy
 * Đại học Trà Vinh
 * 
 * @author System Development Team
 * @version 1.0
 * @date 2024
 */

// Bắt đầu session để quản lý người dùng
session_start();

// Kiểm tra trạng thái đăng nhập
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
    <meta name="description" content="Quy định và hướng dẫn mượn trả thiết bị giảng dạy tại Trường Đại học Trà Vinh">
    <meta name="keywords" content="quy định, hướng dẫn, mượn thiết bị, đại học trà vinh">
    <title>Quy định & Hướng dẫn - Hệ thống mượn trả thiết bị giảng dạy</title>
    
    <!-- CSS Files -->
    <link rel="stylesheet" href="css/styleAbout.css?v=<?php echo time(); ?>">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .regulations-hero {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 4rem 0;
            text-align: center;
            margin-top: 0;
        }
        
        .regulations-hero h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            font-weight: 700;
        }
        
        .regulations-hero p {
            font-size: 1.2rem;
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
        
        .regulations-section {
            padding: 4rem 0;
            background-color: var(--bg-white);
        }
        
        .regulations-content {
            max-width: 1000px;
            margin: 0 auto;
        }
        
        .regulation-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 2.5rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow);
            border-left: 5px solid var(--primary-color);
            opacity: 0;
            transform: translateY(30px);
            animation: fadeInUp 0.6s ease forwards;
        }
        
        .regulation-card:nth-child(1) { animation-delay: 0.1s; }
        .regulation-card:nth-child(2) { animation-delay: 0.2s; }
        .regulation-card:nth-child(3) { animation-delay: 0.3s; }
        .regulation-card:nth-child(4) { animation-delay: 0.4s; }
        .regulation-card:nth-child(5) { animation-delay: 0.5s; }
        
        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .regulation-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
            transition: all 0.3s ease;
        }
        
        .regulation-card h2 {
            color: var(--primary-color);
            font-size: 1.8rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .regulation-card h2 i {
            font-size: 2rem;
        }
        
        .regulation-card h3 {
            color: var(--text-color);
            font-size: 1.3rem;
            margin-top: 2rem;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--border-color);
        }
        
        .regulation-card ul, .regulation-card ol {
            margin-left: 1.5rem;
            line-height: 1.8;
        }
        
        .regulation-card li {
            margin-bottom: 0.8rem;
            color: var(--text-light);
        }
        
        .regulation-card li strong {
            color: var(--text-color);
        }
        
        .highlight-box {
            background: var(--bg-light);
            padding: 1.5rem;
            border-radius: var(--border-radius);
            margin: 1.5rem 0;
            border-left: 4px solid var(--accent, #ff6b35);
        }
        
        .highlight-box h4 {
            color: var(--accent, #ff6b35);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .process-steps {
            display: grid;
            gap: 1.5rem;
            margin: 2rem 0;
        }
        
        .process-step {
            display: flex;
            gap: 1.5rem;
            background: var(--bg-light);
            padding: 1.5rem;
            border-radius: var(--border-radius);
            transition: var(--transition);
            opacity: 0;
            transform: translateX(-30px);
            animation: slideInLeft 0.5s ease forwards;
        }
        
        .process-step:nth-child(1) { animation-delay: 0.1s; }
        .process-step:nth-child(2) { animation-delay: 0.2s; }
        .process-step:nth-child(3) { animation-delay: 0.3s; }
        .process-step:nth-child(4) { animation-delay: 0.4s; }
        .process-step:nth-child(5) { animation-delay: 0.5s; }
        .process-step:nth-child(6) { animation-delay: 0.6s; }
        
        @keyframes slideInLeft {
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        .process-step:hover {
            transform: translateX(10px);
            background: white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .process-step:hover {
            transform: translateX(10px);
            box-shadow: var(--shadow);
        }
        
        .step-number {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: 700;
            flex-shrink: 0;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(65, 105, 225, 0.3);
        }
        
        .process-step:hover .step-number {
            transform: rotate(360deg) scale(1.15);
            box-shadow: 0 6px 20px rgba(65, 105, 225, 0.5);
        }
        
        .step-content h4 {
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }
        
        .step-content p {
            color: var(--text-light);
            line-height: 1.6;
        }
        
        .fine-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin: 1.5rem 0;
            background: white;
            box-shadow: var(--shadow);
            border-radius: var(--border-radius);
            overflow: hidden;
            opacity: 0;
            animation: fadeIn 0.8s ease forwards 0.5s;
        }
        
        @keyframes fadeIn {
            to {
                opacity: 1;
            }
        }
        
        .fine-table thead {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
        }
        
        .fine-table th {
            padding: 1.2rem 1.5rem;
            text-align: left;
            font-weight: 600;
            font-size: 1rem;
            letter-spacing: 0.03em;
            border-bottom: none;
        }
        
        .fine-table td {
            padding: 1.5rem 1.5rem;
            text-align: left;
            border-bottom: 1px solid #f3f4f6;
            color: var(--text-dark);
            line-height: 1.8;
            vertical-align: top;
        }
        
        .fine-table tbody tr {
            transition: all 0.3s ease;
        }
        
        .fine-table tbody tr:hover {
            background: #f9fafb;
        }
        
        .fine-table tbody tr:last-child td {
            border-bottom: none;
        }
        
        .contact-cta {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 3rem;
            border-radius: var(--border-radius);
            text-align: center;
            margin-top: 3rem;
            opacity: 0;
            transform: scale(0.9);
            animation: scaleIn 0.6s ease forwards 0.8s;
        }
        
        @keyframes scaleIn {
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
        
        .contact-cta:hover {
            transform: scale(1.02);
            box-shadow: 0 10px 30px rgba(65, 105, 225, 0.4);
            transition: all 0.3s ease;
        }
        
        .contact-cta h3 {
            font-size: 1.8rem;
            margin-bottom: 1rem;
        }
        
        .contact-cta p {
            font-size: 1.1rem;
            margin-bottom: 2rem;
            opacity: 0.95;
        }
        
        .contact-cta .btn {
            background: white;
            color: var(--primary-color);
            padding: 1rem 2.5rem;
            border-radius: var(--border-radius);
            text-decoration: none;
            font-weight: 600;
            display: inline-block;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .contact-cta .btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(65, 105, 225, 0.1);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }
        
        .contact-cta .btn:hover::before {
            width: 300px;
            height: 300px;
        }
        
        .contact-cta .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
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
                    <a href="equipment.php" class="nav-link">Thiết bị</a>
                    <a href="regulations.php" class="nav-link active">Quy định & Hướng dẫn</a>
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
    <section class="regulations-hero">
        <div class="container">
            <h1><i class="fas fa-book"></i> Quy định & Hướng dẫn</h1>
            <p>Hướng dẫn chi tiết về quy trình và quy định mượn trả thiết bị giảng dạy</p>
        </div>
    </section>

    <!-- Main Content -->
    <main>
        <section class="regulations-section">
            <div class="container regulations-content">
                
                <!-- Quy định chung -->
                <div id="general" class="regulation-card">
                    <h2><i class="fas fa-gavel"></i> Quy định chung</h2>
                    
                    <h3>1. Đối tượng được mượn thiết bị</h3>
                    <ul>
                        <li><strong>Giảng viên:</strong> Toàn bộ giảng viên của trường có nhu cầu sử dụng thiết bị phục vụ giảng dạy, nghiên cứu khoa học.</li>
                        <li><strong>Sinh viên:</strong> Sinh viên đang học tập tại trường, có nhu cầu mượn thiết bị phục vụ học tập, làm đồ án, khóa luận.</li>
                        <li><strong>Đơn vị:</strong> Các khoa, phòng ban trong trường có nhu cầu tổ chức sự kiện, hội thảo.</li>
                    </ul>
                    
                    <h3>2. Điều kiện mượn thiết bị</h3>
                    <ul>
                        <li>Phải có tài khoản trên hệ thống và đã xác thực thông tin cá nhân.</li>
                        <li>Không có thiết bị đang mượn quá hạn chưa trả.</li>
                        <li>Không có khoản phạt nào chưa thanh toán.</li>
                        <li>Đối với sinh viên: Phải có xác nhận từ giảng viên hướng dẫn (đối với thiết bị giá trị cao).</li>
                    </ul>
                    
                    <h3>3. Thời gian mượn tối đa</h3>
                    <ul>
                        <li><strong>Máy chiếu, loa trợ giảng, micro:</strong> 7 ngày</li>
                        <li><strong>Laptop, máy ảnh:</strong> 3 ngày</li>
                        <li><strong>Phụ kiện (cáp, tai nghe):</strong> 7 ngày</li>
                        <li>Có thể gia hạn 1 lần nếu không có yêu cầu mượn từ người khác.</li>
                    </ul>
                </div>

                <!-- Hướng dẫn mượn trả -->
                <div id="guidelines" class="regulation-card">
                    <h2><i class="fas fa-clipboard-list"></i> Hướng dẫn mượn trả thiết bị</h2>
                    
                    <h3>Quy trình mượn thiết bị</h3>
                    <div class="process-steps">
                        <div class="process-step">
                            <div class="step-number">1</div>
                            <div class="step-content">
                                <h4>Đăng ký tài khoản & Đăng nhập</h4>
                                <p>Truy cập hệ thống và đăng ký tài khoản với email @tvu.edu.vn hoặc email sinh viên. Đăng nhập vào hệ thống.</p>
                            </div>
                        </div>
                        
                        <div class="process-step">
                            <div class="step-number">2</div>
                            <div class="step-content">
                                <h4>Tìm kiếm thiết bị</h4>
                                <p>Tìm kiếm thiết bị cần mượn trên trang chủ. Xem thông tin chi tiết về tình trạng, vị trí lưu trữ và thời gian có thể mượn.</p>
                            </div>
                        </div>
                        
                        <div class="process-step">
                            <div class="step-number">3</div>
                            <div class="step-content">
                                <h4>Gửi yêu cầu mượn</h4>
                                <p>Điền đầy đủ thông tin: mục đích sử dụng, thời gian dự kiến mượn và trả. Gửi yêu cầu và chờ phê duyệt.</p>
                            </div>
                        </div>
                        
                        <div class="process-step">
                            <div class="step-number">4</div>
                            <div class="step-content">
                                <h4>Nhận thông báo duyệt</h4>
                                <p>Hệ thống sẽ gửi thông báo qua email và trong hệ thống khi yêu cầu được duyệt. Kiểm tra Dashboard để xem chi tiết phiếu mượn.</p>
                            </div>
                        </div>
                        
                        <div class="process-step">
                            <div class="step-number">5</div>
                            <div class="step-content">
                                <h4>Nhận thiết bị</h4>
                                <p>Đến phòng Quản lý Thiết bị vào giờ hành chính (8h-17h) để nhận thiết bị. Mang theo thẻ sinh viên/giảng viên.</p>
                            </div>
                        </div>
                        
                        <div class="process-step">
                            <div class="step-number">6</div>
                            <div class="step-content">
                                <h4>Trả thiết bị</h4>
                                <p>Trả thiết bị đúng hạn tại phòng Quản lý Thiết bị. Nhân viên sẽ kiểm tra tình trạng thiết bị trước khi xác nhận hoàn trả.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="highlight-box">
                        <h4><i class="fas fa-exclamation-triangle"></i> Lưu ý quan trọng</h4>
                        <ul>
                            <li>Kiểm tra kỹ tình trạng thiết bị trước khi nhận</li>
                            <li>Bảo quản thiết bị cẩn thận, không cho người khác mượn lại</li>
                            <li>Trả đúng hạn để tránh bị phạt và ảnh hưởng đến việc mượn sau này</li>
                            <li>Báo ngay cho phòng Quản lý nếu thiết bị bị hỏng hoặc mất</li>
                        </ul>
                    </div>
                </div>

                <!-- Chính sách phạt -->
                <div id="fines" class="regulation-card">
                    <h2><i class="fas fa-money-bill-wave"></i> Chính sách phạt</h2>
                    
                    <h3>Các trường hợp bị phạt</h3>
                    <table class="fine-table">
                        <thead>
                            <tr>
                                <th>Vi phạm</th>
                                <th>Mức phạt</th>
                                <th>Ghi chú</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Trả thiết bị trễ hạn (1-3 ngày)</td>
                                <td>50.000 VNĐ/ngày</td>
                                <td>Tính từ ngày hết hạn</td>
                            </tr>
                            <tr>
                                <td>Trả thiết bị trễ hạn (từ 4 ngày trở lên)</td>
                                <td>100.000 VNĐ/ngày</td>
                                <td>Cấm mượn trong 1 tháng</td>
                            </tr>
                            <tr>
                                <td>Làm hỏng thiết bị nhẹ (có thể sửa chữa)</td>
                                <td>50% giá trị sửa chữa</td>
                                <td>Tối thiểu 500.000 VNĐ</td>
                            </tr>
                            <tr>
                                <td>Làm hỏng thiết bị nặng (không thể sửa chữa)</td>
                                <td>100% giá trị thiết bị</td>
                                <td>Cấm mượn vĩnh viễn</td>
                            </tr>
                            <tr>
                                <td>Làm mất thiết bị</td>
                                <td>120% giá trị thiết bị</td>
                                <td>Cấm mượn vĩnh viễn + báo cáo lên nhà trường</td>
                            </tr>
                            <tr>
                                <td>Cho người khác mượn lại</td>
                                <td>200.000 VNĐ</td>
                                <td>Cấm mượn trong 3 tháng</td>
                            </tr>
                            <tr>
                                <td>Sử dụng không đúng mục đích</td>
                                <td>300.000 VNĐ</td>
                                <td>Cấm mượn trong 6 tháng</td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <div class="highlight-box">
                        <h4><i class="fas fa-info-circle"></i> Phương thức thanh toán phạt</h4>
                        <p>Người mượn phải thanh toán tiền phạt trong vòng 7 ngày kể từ ngày phát sinh. Thanh toán trực tiếp tại phòng Quản lý Thiết bị hoặc chuyển khoản theo thông tin:</p>
                        <ul>
                            <li><strong>Ngân hàng:</strong> Vietcombank Chi nhánh Trà Vinh</li>
                            <li><strong>Số tài khoản:</strong> 0123456789</li>
                            <li><strong>Chủ tài khoản:</strong> Trường Đại học Trà Vinh</li>
                            <li><strong>Nội dung:</strong> [Mã phiếu phạt] - [Họ tên] - [Mã SV/GV]</li>
                        </ul>
                    </div>
                </div>

                <!-- Trách nhiệm người mượn -->
                <div id="responsibilities" class="regulation-card">
                    <h2><i class="fas fa-user-check"></i> Trách nhiệm người mượn</h2>
                    
                    <h3>Trước khi nhận thiết bị</h3>
                    <ol>
                        <li>Kiểm tra kỹ tình trạng thiết bị, ghi nhận đầy đủ vào biên bản bàn giao.</li>
                        <li>Ký xác nhận đã nhận thiết bị và cam kết tuân thủ quy định.</li>
                        <li>Nhận hướng dẫn sử dụng cơ bản (nếu là lần đầu mượn thiết bị đó).</li>
                    </ol>
                    
                    <h3>Trong quá trình sử dụng</h3>
                    <ol>
                        <li>Sử dụng đúng mục đích đã đăng ký, không cho người khác mượn lại.</li>
                        <li>Bảo quản thiết bị cẩn thận, tránh va đập, tiếp xúc với nước.</li>
                        <li>Không tự ý tháo rời, sửa chữa thiết bị.</li>
                        <li>Báo ngay cho phòng Quản lý nếu phát hiện thiết bị có dấu hiệu hỏng hóc.</li>
                    </ol>
                    
                    <h3>Khi trả thiết bị</h3>
                    <ol>
                        <li>Vệ sinh sạch sẽ thiết bị trước khi trả.</li>
                        <li>Trả đầy đủ thiết bị và phụ kiện đã mượn.</li>
                        <li>Trả đúng giờ đã cam kết, nếu muốn gia hạn phải thông báo trước ít nhất 1 ngày.</li>
                        <li>Ký xác nhận hoàn trả và nhận phiếu xác nhận đã trả.</li>
                    </ol>
                </div>

                <!-- Liên hệ hỗ trợ -->
                <div class="contact-cta">
                    <h3><i class="fas fa-headset"></i> Cần hỗ trợ?</h3>
                    <p>Nếu bạn có bất kỳ thắc mắc nào về quy định hoặc quy trình mượn trả thiết bị, vui lòng liên hệ với chúng tôi.</p>
                    <a href="contact.php" class="btn">
                        <i class="fas fa-phone-alt"></i> Liên hệ ngay
                    </a>
                </div>

            </div>
        </section>
    </main>

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
</body>
</html>
