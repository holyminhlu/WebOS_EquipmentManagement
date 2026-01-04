<?php
/**
 * Trang Liên Hệ - Hệ thống mượn trả thiết bị giảng dạy
 * Đại học Trà Vinh
 */

session_start();

// Lấy thông tin user nếu đã đăng nhập
$isLoggedIn = isset($_SESSION['user_id']);
if ($isLoggedIn) {
    require_once __DIR__ . '/../includes/user.php';
    $userData = getUserInfo($_SESSION['user_id']);
}

// Xử lý form liên hệ
$formSubmitted = false;
$success = false;
$message = '';
$formErrors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_contact'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $content = trim($_POST['content'] ?? '');
    
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
    
    if (empty($formErrors)) {
        // Kết nối database và lưu tin nhắn
        require_once __DIR__ . '/../includes/db.php';
        
        try {
            $conn = getDBConnection();
            
            // Tạo tiêu đề và nội dung thông báo
            $tieuDe = "📩 Tin nhắn liên hệ từ " . $name;
            
            // Format chủ đề
            $subjectLabels = [
                'thiet-bi' => 'Hỗ trợ về thiết bị',
                'tai-khoan' => 'Hỗ trợ tài khoản',
                'muon-tra' => 'Quy trình mượn trả',
                'ky-thuat' => 'Vấn đề kỹ thuật',
                'khac' => 'Khác'
            ];
            $subjectText = isset($subjectLabels[$subject]) ? $subjectLabels[$subject] : 'Không xác định';
            
            // Tạo nội dung thông báo
            $noiDung = "───────────────────────\n";
            $noiDung .= "👤 Người gửi: " . $name . "\n";
            $noiDung .= "📧 Email: " . $email . "\n";
            if (!empty($phone)) {
                $noiDung .= "📱 Số điện thoại: " . $phone . "\n";
            }
            $noiDung .= "📋 Chủ đề: " . $subjectText . "\n";
            $noiDung .= "🕐 Thời gian: " . date('d/m/Y H:i:s') . "\n";
            $noiDung .= "───────────────────────\n\n";
            $noiDung .= "💬 Nội dung:\n" . $content . "\n\n";
            $noiDung .= "───────────────────────\n";
            $noiDung .= "⚠️ Vui lòng phản hồi qua email: " . $email;
            
            // Lấy danh sách tất cả admin đang hoạt động
            $sqlAdmins = "SELECT MaNguoiDung FROM nguoidung 
                         WHERE MaVaiTro IN (1, 1101) 
                         AND HoatDong = 1 
                         AND IsDeleted = 0";
            $resultAdmins = $conn->query($sqlAdmins);
            
            if ($resultAdmins && $resultAdmins->num_rows > 0) {
                $countSuccess = 0;
                
                while ($admin = $resultAdmins->fetch_assoc()) {
                    $adminId = $admin['MaNguoiDung'];
                    
                    // Tạo mã thông báo unique
                    $maThongBao = 'TB' . date('YmdHis') . rand(1000, 9999);
                    
                    // Escape dữ liệu để tránh SQL injection
                    $maThongBaoEsc = $conn->real_escape_string($maThongBao);
                    $adminIdEsc = $conn->real_escape_string($adminId);
                    $tieuDeEsc = $conn->real_escape_string($tieuDe);
                    $noiDungEsc = $conn->real_escape_string($noiDung);
                    
                    // Insert thông báo
                    $sqlInsert = "INSERT INTO thongbao 
                                 (MaThongBao, MaNguoiDung, TieuDe, NoiDung, DaDoc, NgayGui, Kenh, IsDeleted) 
                                 VALUES 
                                 ('$maThongBaoEsc', '$adminIdEsc', '$tieuDeEsc', '$noiDungEsc', 0, NOW(), 'Liên hệ', 0)";
                    
                    if ($conn->query($sqlInsert)) {
                        $countSuccess++;
                    }
                    
                    // Delay nhỏ để tránh trùng mã
                    usleep(1000);
                }
                
                $formSubmitted = true;
                $success = true;
                $message = 'Cảm ơn bạn đã liên hệ! Tin nhắn của bạn đã được gửi đến ' . $countSuccess . ' quản trị viên. Chúng tôi sẽ phản hồi qua email trong thời gian sớm nhất.';
                $_POST = [];
            } else {
                $formErrors['general'] = 'Không tìm thấy quản trị viên trong hệ thống. Vui lòng thử lại sau.';
                $formSubmitted = true;
            }
            
        } catch (Exception $e) {
            $formErrors['general'] = 'Có lỗi xảy ra khi gửi tin nhắn. Vui lòng thử lại sau.';
            $formSubmitted = true;
            error_log('Contact form error: ' . $e->getMessage());
        }
    } else {
        $formSubmitted = true;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liên Hệ - Hệ thống mượn trả thiết bị</title>
    <!-- CSS Files -->
    <link rel="stylesheet" href="css/styleAbout.css?v=<?php echo time(); ?>">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .contact-hero {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 4rem 0;
            text-align: center;
            margin-top: 0;
        }
        
        .hero-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }
        
        .contact-hero h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
        }
        
        .contact-hero p {
            font-size: 1.2rem;
            opacity: 0.95;
            margin-bottom: 2rem;
        }

        /* Hero Stats */
        .hero-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 2rem;
            max-width: 900px;
            margin: 0 auto;
            padding-top: 2rem;
        }

        .stat-item {
            text-align: center;
            padding: 1.5rem;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }

        .stat-item:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.15);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: white;
            margin-bottom: 0.5rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }

        .stat-label {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.9);
            font-weight: 500;
        }

        @media (max-width: 768px) {
            .hero-stats {
                grid-template-columns: repeat(2, 1fr);
                gap: 1rem;
            }
            
            .stat-number {
                font-size: 2rem;
            }
        }
        
        /* Support Team Section */
        .support-team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 4rem;
        }

        .team-member {
            background: white;
            border-radius: 15px;
            padding: 2.5rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            text-align: center;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            position: relative;
            overflow: hidden;
        }

        .team-member::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
        }

        .team-member:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
            border-color: var(--primary-color);
        }

        .member-avatar {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 3rem;
            color: white;
            position: relative;
            box-shadow: 0 8px 25px rgba(37, 99, 235, 0.3);
        }

        .status-badge {
            position: absolute;
            bottom: 5px;
            right: 5px;
            width: 20px;
            height: 20px;
            background: #10b981;
            border: 3px solid white;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% {
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
            }
            50% {
                box-shadow: 0 0 0 10px rgba(16, 185, 129, 0);
            }
        }

        .team-member h4 {
            font-size: 1.3rem;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .member-role {
            color: var(--text-light);
            font-size: 0.95rem;
            margin-bottom: 1.5rem;
        }

        .member-contact {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }

        .contact-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 1rem;
            background: linear-gradient(135deg, #f8f9ff 0%, #e8edff 100%);
            border: 2px solid #e0e7ff;
            border-radius: 10px;
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .contact-btn:hover {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }

        .working-hours {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem;
            background: #f0fdf4;
            border-radius: 10px;
            color: #15803d;
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .contact-section {
            padding: 4rem 0;
            background-color: var(--bg-white);
        }
        
        .contact-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.5rem;
            margin-bottom: 3rem;
        }

        @media (max-width: 1200px) {
            .contact-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .contact-grid {
                grid-template-columns: 1fr;
            }
        }
        
        .contact-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 2.5rem;
            box-shadow: var(--shadow);
            text-align: center;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            position: relative;
            overflow: hidden;
        }
        
        .contact-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }
        
        .contact-card:hover::before {
            transform: scaleX(1);
        }
        
        .contact-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
            border-color: var(--primary-color);
        }
        
        .contact-card .icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2rem;
            color: white;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }
        
        .contact-card:hover .icon {
            transform: scale(1.1) rotate(5deg);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.5);
        }
        
        .contact-card h3 {
            font-size: 1.4rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--text-dark);
        }
        
        .contact-card p {
            color: var(--text-light);
            line-height: 1.8;
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }
        
        .contact-card p strong {
            color: var(--text-dark);
            font-weight: 600;
        }
        
        .contact-card p i {
            color: var(--primary-color);
            margin-right: 0.5rem;
            width: 20px;
            text-align: center;
        }
        
        .contact-card a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .contact-card a:hover {
            color: var(--primary-dark);
        }

        .view-map-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white !important;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            margin-top: 1rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .view-map-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }

        .availability-badge,
        .response-time,
        .emergency-note {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: #f0fdf4;
            color: #15803d;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-top: 1rem;
        }

        .availability-badge i {
            color: #10b981;
            animation: blink 1.5s infinite;
        }

        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.3; }
        }

        .response-time {
            background: #eff6ff;
            color: #1e40af;
        }

        .emergency-note {
            background: #fef3c7;
            color: #92400e;
            font-size: 0.8rem;
        }
        
        .map-form-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
            margin-top: 3rem;
        }
        
        .map-container {
            background: white;
            border-radius: var(--border-radius);
            padding: 2rem;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
        }
        
        .map-container:hover {
            box-shadow: 0 12px 35px rgba(0,0,0,0.15);
        }
        
        .map-container h3 {
            color: var(--text-dark);
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .map-container h3 i {
            color: var(--primary-color);
        }
        
        .map-container iframe {
            width: 100%;
            height: 450px;
            border-radius: var(--border-radius);
            border: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .contact-form {
            background: white;
            border-radius: var(--border-radius);
            padding: 2rem;
            box-shadow: var(--shadow);
            position: sticky;
            top: 2rem;
            transition: all 0.3s ease;
        }
        
        .contact-form:hover {
            box-shadow: 0 12px 35px rgba(0,0,0,0.15);
        }
        
        .contact-form h3 {
            color: var(--text-dark);
            font-size: 1.4rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            white-space: nowrap;
        }
        
        .contact-form h3 i {
            color: var(--primary-color);
            flex-shrink: 0;
        }

        .form-description {
            color: var(--text-light);
            margin-bottom: 1.5rem;
            font-size: 0.95rem;
            line-height: 1.6;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--text-dark);
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .form-group label i {
            color: var(--primary-color);
            font-size: 1rem;
        }
        
        .form-group .required {
            color: #dc3545;
        }
        
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid var(--border-color);
            border-radius: var(--border-radius);
            font-family: inherit;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #fafafa;
        }

        .form-group input:hover,
        .form-group textarea:hover,
        .form-group select:hover {
            border-color: #b8c5f0;
        }
        
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary-color);
            background: white;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
            transform: translateY(-2px);
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 140px;
            max-height: 300px;
        }
        
        .form-group .form-text {
            font-size: 0.875rem;
            color: var(--text-light);
            margin-top: 0.25rem;
        }
        
        .form-group .error {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .char-count {
            text-align: right;
            font-size: 0.85rem;
            color: var(--text-light);
            margin-top: 0.5rem;
            font-weight: 500;
        }

        .form-privacy {
            margin: 1.5rem 0;
            padding: 1rem;
            background: #f8f9ff;
            border-radius: 10px;
            border: 1px solid #e0e7ff;
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            cursor: pointer;
            font-size: 0.9rem;
            color: var(--text-dark);
        }

        .checkbox-label input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .checkbox-label a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
        }

        .checkbox-label a:hover {
            text-decoration: underline;
        }
        
        .btn-submit {
            width: 100%;
            padding: 1.1rem;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
            border: none;
            border-radius: var(--border-radius);
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
            position: relative;
            overflow: hidden;
        }
        
        .btn-submit::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }
        
        .btn-submit:hover::before {
            width: 300px;
            height: 300px;
        }
        
        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }
        
        .btn-submit:active {
            transform: translateY(-1px);
        }

        .btn-submit:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        /* Quick Contact Inside Map Container */
        .quick-contact-inner {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 2px solid #e0e7ff;
        }

        .quick-contact-title {
            text-align: center;
            color: var(--text-dark);
            font-size: 1.05rem;
            font-weight: 600;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .quick-contact-title i {
            color: var(--primary-color);
            font-size: 0.95rem;
        }

        .quick-contact-buttons {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.75rem;
        }

        @media (max-width: 576px) {
            .quick-contact-buttons {
                grid-template-columns: 1fr;
            }
        }

        .quick-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 0.85rem;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.85rem;
            transition: all 0.3s ease;
            border: 2px solid;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .quick-btn i {
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        .quick-btn span {
            font-size: 0.85rem;
        }

        .quick-btn.phone {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border-color: #10b981;
        }

        .quick-btn.phone:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(16, 185, 129, 0.3);
        }

        .quick-btn.email {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            border-color: #3b82f6;
        }

        .quick-btn.email:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(59, 130, 246, 0.3);
        }

        .quick-btn.zalo {
            background: linear-gradient(135deg, #0068ff 0%, #0052cc 100%);
            color: white;
            border-color: #0068ff;
        }

        .quick-btn.zalo:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(0, 104, 255, 0.3);
        }

        .quick-btn.messenger {
            background: linear-gradient(135deg, #006aff 0%, #0084ff 100%);
            color: white;
            border-color: #006aff;
        }

        .quick-btn.messenger:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(0, 132, 255, 0.3);
        }
        
        .alert {
            padding: 1rem;
            border-radius: var(--border-radius);
            margin-bottom: 1.5rem;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        /* FAQ Styles */
        .faq-item {
            background: white;
            border-radius: var(--border-radius);
            margin-bottom: 1rem;
            box-shadow: var(--shadow);
            overflow: hidden;
            border: 2px solid transparent;
            transition: all 0.3s ease;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.5s ease forwards;
        }
        
        .faq-item:nth-child(1) { animation-delay: 0.1s; }
        .faq-item:nth-child(2) { animation-delay: 0.2s; }
        .faq-item:nth-child(3) { animation-delay: 0.3s; }
        .faq-item:nth-child(4) { animation-delay: 0.4s; }
        .faq-item:nth-child(5) { animation-delay: 0.5s; }
        
        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .faq-item:hover {
            border-color: var(--primary-color);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            transform: translateX(8px);
        }
        
        .faq-question {
            padding: 1.5rem 2rem;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 600;
            font-size: 1.1rem;
            color: var(--text-dark);
            transition: all 0.3s ease;
            user-select: none;
            background: white;
            position: relative;
            overflow: hidden;
        }
        
        .faq-question::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 4px;
            background: linear-gradient(to bottom, var(--primary-color), #667eea);
            transform: scaleY(0);
            transition: transform 0.3s ease;
        }
        
        .faq-question:hover::before {
            transform: scaleY(1);
        }
        
        .faq-question:hover {
            background: linear-gradient(to right, rgba(102, 126, 234, 0.05) 0%, transparent 100%);
            padding-left: 2.5rem;
        }
        
        .faq-question i {
            transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            color: var(--primary-color);
            font-size: 1.2rem;
        }
        
        .faq-item.active .faq-question {
            background: linear-gradient(to right, rgba(102, 126, 234, 0.1) 0%, transparent 100%);
            color: var(--primary-color);
        }
        
        .faq-item.active .faq-question::before {
            transform: scaleY(1);
        }
        
        .faq-item.active .faq-question i {
            transform: rotate(180deg);
        }
        
        .faq-answer {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.4s ease, padding 0.3s ease;
            background: linear-gradient(to bottom, rgba(102, 126, 234, 0.03), transparent);
        }
        
        .faq-item.active .faq-answer {
            max-height: 600px;
            animation: slideDown 0.4s ease;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .faq-answer p,
        .faq-answer ol,
        .faq-answer ul {
            padding: 0 1.5rem 1.5rem;
            color: var(--text-light);
            line-height: 1.8;
        }
        
        .faq-answer ol,
        .faq-answer ul {
            padding-left: 3rem;
        }
        
        .faq-answer strong {
            color: var(--text-dark);
        }
        
        @media (max-width: 992px) {
            .map-form-grid {
                grid-template-columns: 1fr;
            }

            .contact-form {
                position: relative;
                top: 0;
            }

            .support-team-grid {
                grid-template-columns: 1fr;
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
                    <a href="regulations.php" class="nav-link">Quy định & Hướng dẫn</a>
                    <a href="contact.php" class="nav-link active">Liên hệ</a>
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
    <section class="contact-hero">
        <div class="hero-container">
            <h1>
                <i class="fas fa-envelope"></i> Liên Hệ & Hỗ Trợ
            </h1>
            
            <!-- Stats -->
            <div class="hero-stats">
                <div class="stat-item">
                    <div class="stat-number">24/7</div>
                    <div class="stat-label">Hỗ trợ trực tuyến</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">&lt;24h</div>
                    <div class="stat-label">Thời gian phản hồi</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">50+</div>
                    <div class="stat-label">Yêu cầu đã xử lý</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">98%</div>
                    <div class="stat-label">Hài lòng</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="contact-section">
        <div class="container">
            <!-- Support Team Section -->
            <div class="section-header" style="text-align: center; margin-bottom: 3rem;">
                <h2 style="color: var(--text-dark); font-size: 2rem; margin-bottom: 0.5rem;">
                    <i class="fas fa-headset"></i> Đội Ngũ Hỗ Trợ
                </h2>
                <p style="color: var(--text-light); font-size: 1.1rem;">Chúng tôi luôn sẵn sàng giải đáp mọi thắc mắc của bạn</p>
            </div>

            <!-- Support Team Grid -->
            <div class="support-team-grid">
                <div class="team-member">
                    <div class="member-avatar">
                        <i class="fas fa-user-tie"></i>
                        <span class="status-badge online"></span>
                    </div>
                    <h4>Phòng Quản Lý Thiết Bị</h4>
                    <p class="member-role">Hỗ trợ mượn trả thiết bị</p>
                    <div class="member-contact">
                        <a href="tel:02943855246" class="contact-btn">
                            <i class="fas fa-phone"></i> (0294) 3855246
                        </a>
                        <a href="mailto:thietbi@tvu.edu.vn" class="contact-btn">
                            <i class="fas fa-envelope"></i> thietbi@tvu.edu.vn
                        </a>
                    </div>
                    <div class="working-hours">
                        <i class="fas fa-clock"></i> 7:00 - 17:00 (T2 - T6)
                    </div>
                </div>

                <div class="team-member">
                    <div class="member-avatar">
                        <i class="fas fa-tools"></i>
                        <span class="status-badge online"></span>
                    </div>
                    <h4>Phòng Kỹ Thuật</h4>
                    <p class="member-role">Hỗ trợ kỹ thuật & sửa chữa</p>
                    <div class="member-contact">
                        <a href="tel:02943855269" class="contact-btn">
                            <i class="fas fa-phone"></i> (0294) 3855269
                        </a>
                        <a href="mailto:kythuat@tvu.edu.vn" class="contact-btn">
                            <i class="fas fa-envelope"></i> kythuat@tvu.edu.vn
                        </a>
                    </div>
                    <div class="working-hours">
                        <i class="fas fa-clock"></i> 7:30 - 16:30 (T2 - T6)
                    </div>
                </div>

                <div class="team-member">
                    <div class="member-avatar">
                        <i class="fas fa-user-shield"></i>
                        <span class="status-badge online"></span>
                    </div>
                    <h4>Bộ Phận IT</h4>
                    <p class="member-role">Hỗ trợ hệ thống & tài khoản</p>
                    <div class="member-contact">
                        <a href="tel:02943855959" class="contact-btn">
                            <i class="fas fa-phone"></i> (0294) 3855959
                        </a>
                        <a href="mailto:support@tvu.edu.vn" class="contact-btn">
                            <i class="fas fa-envelope"></i> support@tvu.edu.vn
                        </a>
                    </div>
                    <div class="working-hours">
                        <i class="fas fa-clock"></i> 24/7 Online Support
                    </div>
                </div>
            </div>

            <!-- Contact Cards -->
            <div class="section-header" style="text-align: center; margin: 4rem 0 2rem;">
                <h2 style="color: var(--text-dark); font-size: 2rem; margin-bottom: 0.5rem;">
                    <i class="fas fa-map-marker-alt"></i> Thông Tin Liên Hệ
                </h2>
            </div>

            <div class="contact-grid">
                <div class="contact-card">
                    <div class="icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <h3>Địa Chỉ Trường</h3>
                    <p><i class="fas fa-building"></i> 126 Nguyễn Thiện Thành, Khóm 4, Phường 5</p>
                    <p><i class="fas fa-city"></i> TP. Trà Vinh, Tỉnh Trà Vinh</p>
                    <p><i class="fas fa-globe"></i> <a href="https://tvu.edu.vn" target="_blank">www.tvu.edu.vn</a></p>
                    <a href="https://www.google.com/maps/search/?api=1&query=Đại+học+Trà+Vinh" target="_blank" class="view-map-btn">
                        <i class="fas fa-map-marked-alt"></i> Xem bản đồ
                    </a>
                </div>

                <div class="contact-card">
                    <div class="icon">
                        <i class="fas fa-phone-volume"></i>
                    </div>
                    <h3>Điện Thoại</h3>
                    <p><i class="fas fa-phone"></i> Hotline: <a href="tel:02943855246"><strong>(0294) 3855246</strong></a></p>
                    <p><i class="fas fa-fax"></i> Fax: (0294) 3855269</p>
                    <p><i class="fas fa-mobile-alt"></i> Zalo: <a href="https://zalo.me/0294385524"><strong>0294 385 524</strong></a></p>
                    <div class="availability-badge">
                        <i class="fas fa-circle"></i> Đang hoạt động
                    </div>
                </div>

                <div class="contact-card">
                    <div class="icon">
                        <i class="fas fa-envelope-open-text"></i>
                    </div>
                    <h3>Email Liên Hệ</h3>
                    <p><i class="fas fa-at"></i> Tổng đài: <a href="mailto:info@tvu.edu.vn"><strong>info@tvu.edu.vn</strong></a></p>
                    <p><i class="fas fa-life-ring"></i> Hỗ trợ: <a href="mailto:support@tvu.edu.vn"><strong>support@tvu.edu.vn</strong></a></p>
                    <p><i class="fas fa-user-cog"></i> Quản trị: <a href="mailto:admin@tvu.edu.vn"><strong>admin@tvu.edu.vn</strong></a></p>
                    <div class="response-time">
                        <i class="fas fa-clock"></i> Phản hồi trong 24h
                    </div>
                </div>

                <div class="contact-card">
                    <div class="icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h3>Giờ Làm Việc</h3>
                    <p><i class="fas fa-calendar-week"></i> <strong>Thứ 2 - Thứ 6:</strong></p>
                    <p style="padding-left: 2rem;">Sáng: 7:00 - 11:30</p>
                    <p style="padding-left: 2rem;">Chiều: 13:00 - 17:00</p>
                    <p><i class="fas fa-calendar-times"></i> <strong>Thứ 7 - Chủ nhật:</strong> Nghỉ</p>
                    <div class="emergency-note">
                        <i class="fas fa-exclamation-triangle"></i> Hotline 24/7
                    </div>
                </div>
            </div>

            <!-- Map and Form Grid -->
            <div class="map-form-grid">
                <!-- Map -->
                <div class="map-container">
                    <h3><i class="fas fa-map-marked-alt"></i> Vị trí trên bản đồ</h3>
                    <iframe 
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3930.126136853947!2d106.34393900917988!3d9.923451590137127!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31a0175ea296facb%3A0x55ded92e29068221!2zVHLGsOG7nW5nIMSQ4bqhaSBI4buNYyBUcsOgIFZpbmg!5e0!3m2!1svi!2s!4v1767466698764!5m2!1svi!2s"
                        allowfullscreen="" 
                        loading="lazy">
                    </iframe>

                    <!-- Quick Contact Buttons Inside Map Container -->
                    <div class="quick-contact-inner">
                        <h4 class="quick-contact-title">
                            <i class="fas fa-bolt"></i> Hoặc Liên Hệ Nhanh Qua
                        </h4>
                        <div class="quick-contact-buttons">
                            <a href="tel:02943855959" class="quick-btn phone">
                                <i class="fas fa-phone"></i>
                                <span>Gọi ngay</span>
                            </a>
                            <a href="https://mail.google.com/mail/?view=cm&fs=1&to=tranphilip91@gmail.com" target="_blank" class="quick-btn email">
                                <i class="fas fa-envelope"></i>
                                <span>Email</span>
                            </a>
                            <a href="https://zalo.me/0365530100" target="_blank" class="quick-btn zalo">
                                <i class="fas fa-comment"></i>
                                <span>Zalo</span>
                            </a>
                            <a href="https://m.me/tvu.edu.vn" target="_blank" class="quick-btn messenger">
                                <i class="fab fa-facebook-messenger"></i>
                                <span>Messenger</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Contact Form -->
                <div class="contact-form">
                    <h3><i class="fas fa-paper-plane"></i> Gửi Tin Nhắn Liên Hệ</h3>
                    <p class="form-description">Điền thông tin dưới đây, chúng tôi sẽ phản hồi trong thời gian sớm nhất.</p>
                    
                    <?php if ($formSubmitted && $success): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            <?php echo htmlspecialchars($message); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($formErrors['general'])): ?>
                        <div class="alert alert-danger" style="background: #fee; color: #c33; border: 1px solid #fcc;">
                            <i class="fas fa-exclamation-triangle"></i>
                            <?php echo htmlspecialchars($formErrors['general']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" id="contactForm">
                        <input type="hidden" name="submit_contact" value="1">
                        
                        <div class="form-group">
                            <label for="name">
                                <i class="fas fa-user"></i> Họ và tên <span class="required">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="name" 
                                name="name" 
                                placeholder="Nhập họ và tên của bạn"
                                value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>"
                                required
                            >
                            <?php if (isset($formErrors['name'])): ?>
                                <div class="error"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($formErrors['name']); ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="email">
                                    <i class="fas fa-envelope"></i> Email <span class="required">*</span>
                                </label>
                                <input 
                                    type="email" 
                                    id="email" 
                                    name="email" 
                                    placeholder="email@example.com"
                                    value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                    required
                                >
                                <?php if (isset($formErrors['email'])): ?>
                                    <div class="error"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($formErrors['email']); ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-group">
                                <label for="phone">
                                    <i class="fas fa-phone"></i> Số điện thoại
                                </label>
                                <input 
                                    type="tel" 
                                    id="phone" 
                                    name="phone" 
                                    placeholder="0987 654 321"
                                    value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>"
                                >
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="subject">
                                <i class="fas fa-tag"></i> Chủ đề
                            </label>
                            <select id="subject" name="subject" class="form-select">
                                <option value="">-- Chọn chủ đề --</option>
                                <option value="thiet-bi">Hỗ trợ về thiết bị</option>
                                <option value="tai-khoan">Hỗ trợ tài khoản</option>
                                <option value="muon-tra">Quy trình mượn trả</option>
                                <option value="ky-thuat">Vấn đề kỹ thuật</option>
                                <option value="khac">Khác</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="content">
                                <i class="fas fa-comment-dots"></i> Nội dung tin nhắn <span class="required">*</span>
                            </label>
                            <textarea 
                                id="content" 
                                name="content" 
                                placeholder="Nhập nội dung tin nhắn của bạn (tối thiểu 10 ký tự)..."
                                rows="6"
                                required
                            ><?php echo isset($_POST['content']) ? htmlspecialchars($_POST['content']) : ''; ?></textarea>
                            <?php if (isset($formErrors['content'])): ?>
                                <div class="error"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($formErrors['content']); ?></div>
                            <?php else: ?>
                                <div class="char-count">
                                    <span id="charCount">0</span>/500 ký tự
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-privacy">
                            <label class="checkbox-label">
                                <input type="checkbox" name="privacy" required>
                                <span>Tôi đồng ý với <a href="regulations.php" target="_blank">Điều khoản dịch vụ</a> và <a href="regulations.php" target="_blank">Chính sách bảo mật</a></span>
                            </label>
                        </div>
                        
                        <button type="submit" class="btn-submit">
                            <span class="btn-text">
                                <i class="fas fa-paper-plane"></i> Gửi Tin Nhắn
                            </span>
                            <span class="btn-loading" style="display: none;">
                                <i class="fas fa-spinner fa-spin"></i> Đang gửi...
                            </span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- FAQ Section -->
            <div class="section-header" style="margin-top: 4rem; text-align: center;">
                <h2 style="color: var(--text-dark); font-size: 2rem; margin-bottom: 0.5rem;">
                    <i class="fas fa-question-circle"></i> Câu Hỏi Thường Gặp
                </h2>
                <p style="color: var(--text-light);">Những thắc mắc phổ biến về quy trình mượn trả thiết bị</p>
            </div>
            
            <div style="max-width: 900px; margin: 2rem auto 0;">
                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFAQ(this)">
                        <span>Làm thế nào để đăng ký mượn thiết bị?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Để đăng ký mượn thiết bị, bạn cần thực hiện các bước sau:</p>
                        <ol>
                            <li>Đăng nhập vào hệ thống với tài khoản được cấp</li>
                            <li>Truy cập danh sách thiết bị có sẵn</li>
                            <li>Chọn thiết bị cần mượn và nhấn "Yêu cầu mượn"</li>
                            <li>Điền đầy đủ thông tin: mục đích sử dụng, thời gian mượn</li>
                            <li>Chờ phê duyệt từ quản trị viên (thường trong vòng 24h)</li>
                            <li>Nhận thiết bị tại phòng quản lý sau khi được duyệt</li>
                        </ol>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFAQ(this)">
                        <span>Thời gian mượn tối đa là bao lâu?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Thời gian mượn phụ thuộc vào loại thiết bị:</p>
                        <ul>
                            <li><strong>Thiết bị thông thường:</strong> Tối đa 7 ngày</li>
                            <li><strong>Thiết bị chuyên dụng:</strong> Tối đa 3 ngày</li>
                            <li><strong>Thiết bị phục vụ sự kiện:</strong> Thời gian theo sự kiện</li>
                        </ul>
                        <p>Bạn có thể gia hạn thêm 1 lần nếu có lý do chính đáng và thiết bị chưa có người đặt mượn tiếp theo.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFAQ(this)">
                        <span>Làm gì khi thiết bị bị hỏng trong quá trình sử dụng?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Nếu thiết bị bị hỏng, hãy:</p>
                        <ol>
                            <li><strong>Ngừng sử dụng ngay:</strong> Không tự ý sửa chữa</li>
                            <li><strong>Báo cáo ngay:</strong> Liên hệ hotline 0294 3855246</li>
                            <li><strong>Mang thiết bị đến:</strong> Phòng kỹ thuật để kiểm tra</li>
                            <li><strong>Làm biên bản:</strong> Xác định nguyên nhân và trách nhiệm</li>
                        </ol>
                        <p><strong>Lưu ý:</strong> Nếu hỏng do lỗi sử dụng, bạn sẽ phải chịu chi phí sửa chữa hoặc bồi thường theo quy định.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFAQ(this)">
                        <span>Có phí mượn thiết bị không?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Dịch vụ mượn thiết bị <strong>MIỄN PHÍ</strong> cho giảng viên và sinh viên của trường.</p>
                        <p>Tuy nhiên, bạn sẽ phải chịu phí nếu:</p>
                        <ul>
                            <li>Trả thiết bị trễ hạn: 20,000 VNĐ/ngày</li>
                            <li>Làm mất thiết bị: Bồi thường 100% giá trị</li>
                            <li>Làm hỏng thiết bị: Chi phí sửa chữa thực tế</li>
                        </ul>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFAQ(this)">
                        <span>Ai có thể mượn thiết bị?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Hệ thống mượn trả thiết bị phục vụ các đối tượng sau:</p>
                        <ul>
                            <li><strong>Giảng viên:</strong> Được ưu tiên mượn thiết bị phục vụ giảng dạy</li>
                            <li><strong>Sinh viên:</strong> Cần có giấy giới thiệu từ giảng viên hướng dẫn</li>
                            <li><strong>Nhân viên:</strong> Mượn thiết bị phục vụ công việc chuyên môn</li>
                            <li><strong>Các đơn vị:</strong> Mượn theo yêu cầu tổ chức sự kiện, hội thảo</li>
                        </ul>
                        <p><strong>Lưu ý:</strong> Tất cả phải có tài khoản trong hệ thống và được phê duyệt.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFAQ(this)">
                        <span>Làm thế nào để hủy yêu cầu mượn đã gửi?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Bạn có thể tự hủy yêu cầu mượn của mình theo các bước sau:</p>
                        <ol>
                            <li>Đăng nhập vào hệ thống</li>
                            <li>Vào trang <strong>Dashboard</strong></li>
                            <li>Tìm yêu cầu có trạng thái <strong>"Chờ duyệt"</strong></li>
                            <li>Nhấn nút <strong>"Hủy yêu cầu"</strong></li>
                            <li>Xác nhận hủy trong hộp thoại</li>
                        </ol>
                        <p><strong>Lưu ý:</strong> Chỉ có thể hủy yêu cầu đang ở trạng thái "Chờ duyệt". Sau khi đã được duyệt, cần liên hệ trực tiếp với phòng quản lý.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFAQ(this)">
                        <span>Tôi có thể mượn bao nhiêu thiết bị cùng lúc?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Số lượng thiết bị được phép mượn phụ thuộc vào vai trò của bạn:</p>
                        <ul>
                            <li><strong>Giảng viên:</strong> Tối đa 5 thiết bị cùng lúc</li>
                            <li><strong>Sinh viên:</strong> Tối đa 2 thiết bị cùng lúc</li>
                            <li><strong>Nhân viên:</strong> Tối đa 3 thiết bị cùng lúc</li>
                        </ul>
                        <p>Nếu cần mượn nhiều hơn cho mục đích đặc biệt (sự kiện, hội thảo), vui lòng:</p>
                        <ol>
                            <li>Liên hệ trực tiếp với phòng quản lý thiết bị</li>
                            <li>Cung cấp giấy tờ chứng minh mục đích sử dụng</li>
                            <li>Được phê duyệt từ lãnh đạo đơn vị</li>
                        </ol>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFAQ(this)">
                        <span>Khi nào yêu cầu mượn của tôi được duyệt?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Thời gian xử lý yêu cầu mượn thiết bị:</p>
                        <ul>
                            <li><strong>Trong giờ hành chính:</strong> Từ 1-4 giờ làm việc</li>
                            <li><strong>Ngoài giờ:</strong> Xử lý vào sáng hôm sau</li>
                            <li><strong>Cuối tuần/Lễ:</strong> Xử lý vào ngày làm việc tiếp theo</li>
                        </ul>
                        <p>Bạn sẽ nhận được thông báo qua:</p>
                        <ul>
                            <li>Thông báo trên hệ thống (biểu tượng chuông)</li>
                            <li>Email (nếu đã đăng ký)</li>
                        </ul>
                        <p><strong>Mẹo:</strong> Gửi yêu cầu trước ít nhất 1 ngày để đảm bảo có thiết bị khi cần.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFAQ(this)">
                        <span>Làm thế nào để kiểm tra lịch sử mượn trả?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Để xem lịch sử mượn trả thiết bị của bạn:</p>
                        <ol>
                            <li><strong>Đăng nhập</strong> vào hệ thống</li>
                            <li>Vào trang <strong>Dashboard</strong></li>
                            <li>Chọn tab <strong>"Lịch sử mượn trả"</strong></li>
                        </ol>
                        <p>Tại đây bạn có thể xem:</p>
                        <ul>
                            <li>Tất cả các lần mượn thiết bị (đã trả, đang mượn)</li>
                            <li>Thông tin chi tiết: thiết bị, thời gian, trạng thái</li>
                            <li>Phiếu phạt (nếu có)</li>
                            <li>Thông báo liên quan</li>
                        </ul>
                        <p><strong>Lưu ý:</strong> Lịch sử được lưu trữ vĩnh viễn để phục vụ tra cứu và thống kê.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFAQ(this)">
                        <span>Tôi quên mật khẩu, phải làm sao?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Nếu bạn quên mật khẩu, có 2 cách để khôi phục:</p>
                        <p><strong>Cách 1: Tự đặt lại mật khẩu (nếu có email)</strong></p>
                        <ol>
                            <li>Tại trang đăng nhập, nhấn "Quên mật khẩu?"</li>
                            <li>Nhập email đã đăng ký</li>
                            <li>Kiểm tra email và làm theo hướng dẫn</li>
                            <li>Đặt mật khẩu mới</li>
                        </ol>
                        <p><strong>Cách 2: Liên hệ bộ phận IT</strong></p>
                        <ul>
                            <li>Gọi: (0294) 3855959</li>
                            <li>Email: support@tvu.edu.vn</li>
                            <li>Cung cấp: Mã số giảng viên/sinh viên, họ tên, đơn vị</li>
                        </ul>
                        <p><strong>Thời gian xử lý:</strong> Trong vòng 30 phút (giờ hành chính)</p>
                    </div>
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
        // Toggle FAQ
        function toggleFAQ(element) {
            const faqItem = element.parentElement;
            const isActive = faqItem.classList.contains('active');
            
            // Close all FAQs
            document.querySelectorAll('.faq-item').forEach(item => {
                item.classList.remove('active');
            });
            
            // Toggle current FAQ
            if (!isActive) {
                faqItem.classList.add('active');
            }
        }

        // Character counter for textarea
        document.addEventListener('DOMContentLoaded', function() {
            const textarea = document.getElementById('content');
            const charCount = document.getElementById('charCount');
            
            if (textarea && charCount) {
                textarea.addEventListener('input', function() {
                    const count = this.value.length;
                    charCount.textContent = count;
                    
                    if (count > 500) {
                        charCount.style.color = '#dc3545';
                    } else if (count > 400) {
                        charCount.style.color = '#ff9800';
                    } else {
                        charCount.style.color = '#667eea';
                    }
                });
                
                // Initial count
                charCount.textContent = textarea.value.length;
            }

            // Form submission with loading state
            const form = document.getElementById('contactForm');
            if (form) {
                form.addEventListener('submit', function() {
                    const btnText = this.querySelector('.btn-text');
                    const btnLoading = this.querySelector('.btn-loading');
                    const submitBtn = this.querySelector('.btn-submit');
                    
                    if (btnText && btnLoading) {
                        btnText.style.display = 'none';
                        btnLoading.style.display = 'inline-block';
                        submitBtn.disabled = true;
                    }
                });
            }

            // Auto-fill user info if logged in
            <?php if ($isLoggedIn && isset($userData)): ?>
            const nameInput = document.getElementById('name');
            const emailInput = document.getElementById('email');
            
            if (nameInput && !nameInput.value) {
                nameInput.value = '<?php echo addslashes($userData['HoTen']); ?>';
            }
            <?php endif; ?>

            // Smooth scroll to form when clicking quick contact buttons
            document.querySelectorAll('a[href="#contact-form"]').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    document.querySelector('.contact-form').scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                });
            });
        });
    </script>
</body>
</html>
