<?php
/**
 * Trang Liên Hệ - Hệ thống mượn trả thiết bị giảng dạy
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
$userName = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '';
$userRole = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : '';

// Xử lý form liên hệ
$formSubmitted = false;
$success = false;
$message = '';
$formErrors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_contact'])) {
    // Lấy và làm sạch dữ liệu
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $content = trim($_POST['content'] ?? '');
    
    // Validation
    if (empty($name)) {
        $formErrors['name'] = 'Vui lòng nhập họ và tên';
    }
    
    if (empty($email)) {
        $formErrors['email'] = 'Vui lòng nhập email';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $formErrors['email'] = 'Email không hợp lệ';
    }
    
    if (!empty($phone) && !preg_match('/^[0-9\s\-\+\(\)]+$/', $phone)) {
        $formErrors['phone'] = 'Số điện thoại không hợp lệ';
    }
    
    if (empty($content)) {
        $formErrors['content'] = 'Vui lòng nhập nội dung tin nhắn';
    } elseif (strlen($content) < 10) {
        $formErrors['content'] = 'Nội dung tin nhắn phải có ít nhất 10 ký tự';
    }
    
    // Nếu không có lỗi, xử lý form
    if (empty($formErrors)) {
        // Giả lập gửi email (trong thực tế sẽ gửi email thật)
        // Có thể lưu vào database hoặc gửi email thông qua mail server
        $success = true;
        $message = 'Cảm ơn bạn đã liên hệ! Tin nhắn của bạn đã được gửi thành công. Chúng tôi sẽ phản hồi trong thời gian sớm nhất.';
        
        // Reset form sau khi gửi thành công
        $name = $email = $phone = $content = '';
        $formSubmitted = true;
    } else {
        $message = 'Vui lòng kiểm tra lại thông tin đã nhập.';
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Liên hệ với chúng tôi - Hệ thống mượn trả thiết bị giảng dạy tại Trường Đại học Trà Vinh">
    <meta name="keywords" content="liên hệ, hỗ trợ, đại học trà vinh, thiết bị giảng dạy">
    <title>Liên Hệ - Hệ thống mượn trả thiết bị giảng dạy</title>
    
    <!-- CSS Files -->
    <link rel="stylesheet" href="css/styleAbout.css">
    <link rel="stylesheet" href="css/styleContact.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
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
                    <a href="index.php" class="nav-link" role="menuitem">Trang chủ</a>
                    <a href="about.php" class="nav-link" role="menuitem">Giới thiệu</a>
                    <a href="services.php" class="nav-link" role="menuitem">Dịch vụ</a>
                    <a href="contact.php" class="nav-link active" role="menuitem" aria-current="page">Liên hệ</a>
                </div>
                <div class="nav-auth">
                    <?php if (!$isLoggedIn): ?>
                        <a href="login.php" class="btn-login" aria-label="Đăng nhập vào hệ thống">
                            <i class="fas fa-sign-in-alt" aria-hidden="true"></i> Đăng nhập
                        </a>
                        <a href="register.php" class="btn-register" aria-label="Đăng ký tài khoản mới">
                            <i class="fas fa-user-plus" aria-hidden="true"></i> Đăng ký
                        </a>
                    <?php else: ?>
                        <a href="dashboard.php" class="btn-login" aria-label="Đi đến trang quản lý">
                            <i class="fas fa-tachometer-alt" aria-hidden="true"></i> Bảng điều khiển
                        </a>
                        <a href="my-borrows.php" class="btn-register" aria-label="Xem các thiết bị đã mượn">
                            <i class="fas fa-list" aria-hidden="true"></i> Thiết bị của tôi
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main id="main-content" role="main">
        <!-- Hero Section -->
        <section class="contact-hero" aria-labelledby="hero-title">
            <div class="container">
                <h1 id="hero-title">Liên Hệ Hỗ Trợ</h1>
                <p>Chúng tôi luôn sẵn sàng hỗ trợ bạn trong việc mượn và trả thiết bị giảng dạy.</p>
            </div>
        </section>

        <!-- Contact Section -->
        <section class="contact-section" aria-labelledby="contact-title">
            <div class="container">
                <!-- Row 1: Thông tin liên hệ và Form -->
                <div class="contact-top-row">
                    <!-- Cột 1: Thông tin liên hệ -->
                    <div class="contact-card">
                            <h3 id="contact-title">
                                <i class="fas fa-user-tie" aria-hidden="true"></i> 
                                Thông tin liên hệ
                            </h3>
                            <div class="contact-info-item">
                                <i class="fas fa-building" aria-hidden="true"></i>
                                <div>
                                    <strong>Quản lý thiết bị giảng dạy</strong>
                                </div>
                            </div>
                            <div class="contact-info-item">
                                <i class="fas fa-user" aria-hidden="true"></i>
                                <div>
                                    <strong>Ông Nguyễn Văn A</strong>
                                </div>
                            </div>
                            <div class="contact-info-item">
                                <i class="fas fa-briefcase" aria-hidden="true"></i>
                                <div>
                                    <strong>Chức vụ:</strong>
                                    <p>Trưởng phòng Quản lý Thiết bị</p>
                                </div>
                            </div>
                            <div class="contact-info-item">
                                <i class="fas fa-phone" aria-hidden="true"></i>
                                <div>
                                    <strong>Điện thoại:</strong>
                                    <p><a href="tel:0987654321" aria-label="Gọi điện: 0987 654 321">0987 654 321</a></p>
                                </div>
                            </div>
                            <div class="contact-info-item">
                                <i class="fas fa-envelope" aria-hidden="true"></i>
                                <div>
                                    <strong>Email:</strong>
                                    <p><a href="mailto:nguyenvana@tvu.edu.vn" aria-label="Gửi email: nguyenvana@tvu.edu.vn">nguyenvana@tvu.edu.vn</a></p>
                                </div>
                            </div>
                            <div class="contact-info-item">
                                <i class="fas fa-map-marker-alt" aria-hidden="true"></i>
                                <div>
                                    <strong>Văn phòng:</strong>
                                    <p>Tầng 2, Tòa nhà Hành chính, Khu A</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cột 2: Form liên hệ -->
                    <div class="contact-form-card">
                        <div class="contact-card">
                            <h3>
                                <i class="fas fa-map-marked-alt" aria-hidden="true"></i> 
                                Vị trí
                            </h3>
                            <div class="map-container">
                                <iframe 
                                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3926.1234567890123!2d106.12345678901234!3d9.87654321098765!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zOcKwNTInMzUuNiJOIDEwNsKwMDcnMjQuNCJF!5e0!3m2!1svi!2s!4v1234567890123!5m2!1svi!2s"
                                    width="100%" 
                                    height="400" 
                                    style="border:0;" 
                                    allowfullscreen="" 
                                    loading="lazy" 
                                    referrerpolicy="no-referrer-when-downgrade"
                                    title="Bản đồ Đại học Trà Vinh"
                                    aria-label="Bản đồ hiển thị vị trí Đại học Trà Vinh">
                                </iframe>
                            </div>
                            <div class="map-actions">
                                <a 
                                    href="https://www.google.com/maps/dir//Trường+Đại+học+Trà+Vinh" 
                                    target="_blank" 
                                    rel="noopener noreferrer"
                                    class="btn-view-directions"
                                    aria-label="Mở Google Maps để xem chỉ đường">
                                    <i class="fas fa-directions" aria-hidden="true"></i>
                                    Xem chỉ đường
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Cột 3: Form liên hệ -->
                    <div class="col-lg-4 col-md-12">
                        <div class="contact-form-card">
                            <h3>
                                <i class="fas fa-paper-plane" aria-hidden="true"></i> 
                                Gửi tin nhắn
                            </h3>
                            
                            <?php if ($formSubmitted && $success): ?>
                                <div class="alert alert-success" role="alert">
                                    <i class="fas fa-check-circle" aria-hidden="true"></i>
                                    <strong>Thành công!</strong> <?php echo htmlspecialchars($message); ?>
                                </div>
                            <?php elseif ($formSubmitted && !$success): ?>
                                <div class="alert alert-danger" role="alert">
                                    <i class="fas fa-exclamation-circle" aria-hidden="true"></i>
                                    <strong>Lỗi!</strong> <?php echo htmlspecialchars($message); ?>
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" novalidate>
                                <input type="hidden" name="submit_contact" value="1">
                                
                                <div class="form-group">
                                    <label for="name" class="form-label">
                                        Họ và tên <span class="required">*</span>
                                    </label>
                                    <input 
                                        type="text" 
                                        class="form-control <?php echo isset($formErrors['name']) ? 'is-invalid' : ''; ?>" 
                                        id="name" 
                                        name="name" 
                                        value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>"
                                        required
                                        aria-required="true"
                                        aria-describedby="<?php echo isset($formErrors['name']) ? 'name-error' : 'name-help'; ?>"
                                    >
                                    <?php if (isset($formErrors['name'])): ?>
                                        <div id="name-error" class="invalid-feedback" role="alert">
                                            <?php echo htmlspecialchars($formErrors['name']); ?>
                                        </div>
                                    <?php else: ?>
                                        <div id="name-help" class="sr-only">Nhập họ và tên của bạn</div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="form-group">
                                    <label for="email" class="form-label">
                                        Email <span class="required">*</span>
                                    </label>
                                    <input 
                                        type="email" 
                                        class="form-control <?php echo isset($formErrors['email']) ? 'is-invalid' : ''; ?>" 
                                        id="email" 
                                        name="email" 
                                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                        required
                                        aria-required="true"
                                        aria-describedby="<?php echo isset($formErrors['email']) ? 'email-error' : 'email-help'; ?>"
                                    >
                                    <?php if (isset($formErrors['email'])): ?>
                                        <div id="email-error" class="invalid-feedback" role="alert">
                                            <?php echo htmlspecialchars($formErrors['email']); ?>
                                        </div>
                                    <?php else: ?>
                                        <div id="email-help" class="sr-only">Nhập địa chỉ email của bạn</div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="form-group">
                                    <label for="phone" class="form-label">
                                        Số điện thoại
                                    </label>
                                    <input 
                                        type="tel" 
                                        class="form-control <?php echo isset($formErrors['phone']) ? 'is-invalid' : ''; ?>" 
                                        id="phone" 
                                        name="phone" 
                                        value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>"
                                        pattern="[0-9\s\-\+\(\)]+"
                                        aria-describedby="<?php echo isset($formErrors['phone']) ? 'phone-error' : 'phone-help'; ?>"
                                    >
                                    <?php if (isset($formErrors['phone'])): ?>
                                        <div id="phone-error" class="invalid-feedback" role="alert">
                                            <?php echo htmlspecialchars($formErrors['phone']); ?>
                                        </div>
                                    <?php else: ?>
                                        <div id="phone-help" class="form-text">Ví dụ: 0987 654 321</div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="form-group">
                                    <label for="content" class="form-label">
                                        Nội dung tin nhắn <span class="required">*</span>
                                    </label>
                                    <textarea 
                                        class="form-control <?php echo isset($formErrors['content']) ? 'is-invalid' : ''; ?>" 
                                        id="content" 
                                        name="content" 
                                        rows="5"
                                        required
                                        aria-required="true"
                                        aria-describedby="<?php echo isset($formErrors['content']) ? 'content-error' : 'content-help'; ?>"
                                    ><?php echo isset($_POST['content']) ? htmlspecialchars($_POST['content']) : ''; ?></textarea>
                                    <?php if (isset($formErrors['content'])): ?>
                                        <div id="content-error" class="invalid-feedback" role="alert">
                                            <?php echo htmlspecialchars($formErrors['content']); ?>
                                        </div>
                                    <?php else: ?>
                                        <div id="content-help" class="form-text">Nhập nội dung tin nhắn của bạn (tối thiểu 10 ký tự)</div>
                                    <?php endif; ?>
                                </div>
                                
                                <button type="submit" class="btn-submit" aria-label="Gửi tin nhắn liên hệ">
                                    <i class="fas fa-paper-plane" aria-hidden="true"></i>
                                    Gửi tin nhắn
                                </button>
                            </form>
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
                        <li><a href="services.php">Dịch vụ</a></li>
                        <li><a href="contact.php">Liên hệ</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Dịch vụ</h4>
                    <ul role="list">
                        <li><a href="equipment.php">Mượn thiết bị</a></li>
                        <li><a href="search.php">Tra cứu thiết bị</a></li>
                        <li><a href="guidelines.php">Hướng dẫn sử dụng</a></li>
                        <li><a href="rules.php">Quy định mượn trả</a></li>
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
                <p>&copy; <?php echo date('Y'); ?> Trường Đại học Trà Vinh - Hệ thống Mượn Trả Thiết Bị Giảng Dạy. Tất cả các quyền được bảo lưu.</p>
                <p style="margin-top: 0.5rem; font-size: 0.9rem;">
                    <a href="policy.php" style="color: rgba(255,255,255,0.8); text-decoration: none; margin-right: 1rem;">Chính sách</a>
                    <a href="support.php" style="color: rgba(255,255,255,0.8); text-decoration: none; margin-right: 1rem;">Hỗ trợ</a>
                    <?php if ($isLoggedIn): ?>
                        <a href="logout.php" style="color: rgba(255,255,255,0.8); text-decoration: none;">Đăng xuất</a>
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </footer>

    <!-- Custom JavaScript for Form Validation -->
    <script>
        // Client-side form validation
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form[method="POST"]');
            const nameInput = document.getElementById('name');
            const emailInput = document.getElementById('email');
            const phoneInput = document.getElementById('phone');
            const contentInput = document.getElementById('content');
            
            // Real-time validation
            function validateName() {
                if (nameInput.value.trim() === '') {
                    nameInput.setCustomValidity('Vui lòng nhập họ và tên');
                    return false;
                }
                nameInput.setCustomValidity('');
                return true;
            }
            
            function validateEmail() {
                const email = emailInput.value.trim();
                if (email === '') {
                    emailInput.setCustomValidity('Vui lòng nhập email');
                    return false;
                }
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email)) {
                    emailInput.setCustomValidity('Email không hợp lệ');
                    return false;
                }
                emailInput.setCustomValidity('');
                return true;
            }
            
            function validatePhone() {
                if (phoneInput.value.trim() === '') {
                    phoneInput.setCustomValidity('');
                    return true; // Phone is optional
                }
                const phoneRegex = /^[0-9\s\-\+\(\)]+$/;
                if (!phoneRegex.test(phoneInput.value)) {
                    phoneInput.setCustomValidity('Số điện thoại không hợp lệ');
                    return false;
                }
                phoneInput.setCustomValidity('');
                return true;
            }
            
            function validateContent() {
                if (contentInput.value.trim() === '') {
                    contentInput.setCustomValidity('Vui lòng nhập nội dung tin nhắn');
                    return false;
                }
                if (contentInput.value.trim().length < 10) {
                    contentInput.setCustomValidity('Nội dung tin nhắn phải có ít nhất 10 ký tự');
                    return false;
                }
                contentInput.setCustomValidity('');
                return true;
            }
            
            // Add event listeners
            nameInput.addEventListener('input', validateName);
            nameInput.addEventListener('blur', validateName);
            
            emailInput.addEventListener('input', validateEmail);
            emailInput.addEventListener('blur', validateEmail);
            
            phoneInput.addEventListener('input', validatePhone);
            phoneInput.addEventListener('blur', validatePhone);
            
            contentInput.addEventListener('input', validateContent);
            contentInput.addEventListener('blur', validateContent);
            
            // Form submission
            form.addEventListener('submit', function(e) {
                if (!validateName() || !validateEmail() || !validatePhone() || !validateContent()) {
                    e.preventDefault();
                    e.stopPropagation();
                    form.classList.add('was-validated');
                }
            });
            
            // Accessibility: Focus management
            const firstError = document.querySelector('.is-invalid');
            if (firstError) {
                firstError.focus();
            }
        });
    </script>
</body>
</html>

