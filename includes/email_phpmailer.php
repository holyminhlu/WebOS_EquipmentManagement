<?php
/**
 * Functions gửi email sử dụng PHPMailer với Gmail SMTP
 * 
 * @author System Development Team
 * @version 1.0
 */

// PHPMailer - Download từ: https://github.com/PHPMailer/PHPMailer/releases
// Giải nén vào thư mục vendor/phpmailer/

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/phpmailer/src/Exception.php';
require __DIR__ . '/../vendor/phpmailer/src/PHPMailer.php';
require __DIR__ . '/../vendor/phpmailer/src/SMTP.php';

// ===== CẤU HÌNH EMAIL =====
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'tranphilip91@gmail.com'); // Email Gmail của bạn
define('SMTP_PASSWORD', ''); // Mật khẩu ứng dụng Gmail (App Password)
define('ADMIN_EMAIL', 'trantrungphuc98021@gmail.com');
define('SYSTEM_EMAIL', 'tranphilip91@gmail.com');
define('SYSTEM_NAME', 'Hệ thống mượn trả thiết bị - ĐH Trà Vinh');

/**
 * Tạo PHPMailer instance với cấu hình Gmail SMTP
 */
function createMailer() {
    $mail = new PHPMailer(true);
    
    try {
        // Cấu hình SMTP
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;
        $mail->CharSet = 'UTF-8';
        
        return $mail;
    } catch (Exception $e) {
        error_log("Error creating mailer: " . $e->getMessage());
        return null;
    }
}

/**
 * Gửi email thông báo liên hệ cho admin
 */
function sendContactEmailToAdmin($data) {
    $mail = createMailer();
    if (!$mail) return false;
    
    try {
        // Người gửi
        $mail->setFrom(SYSTEM_EMAIL, SYSTEM_NAME);
        $mail->addReplyTo($data['Email'], $data['HoTen']);
        
        // Người nhận
        $mail->addAddress(ADMIN_EMAIL);
        
        // Nội dung
        $mail->isHTML(true);
        $mail->Subject = '[Liên hệ mới] ' . $data['HoTen'] . ' - ' . date('d/m/Y H:i');
        
        $mail->Body = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #2c5aa0 0%, #1e3d72 100%); color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { background: #f8f9fa; padding: 20px; border: 1px solid #dee2e6; }
                .info-row { margin: 15px 0; padding: 10px; background: white; border-radius: 5px; }
                .label { font-weight: bold; color: #2c5aa0; }
                .value { margin-top: 5px; }
                .message-box { background: white; padding: 15px; border-left: 4px solid #2c5aa0; margin: 15px 0; border-radius: 5px; }
                .footer { background: #2c5aa0; color: white; padding: 15px; text-align: center; font-size: 12px; border-radius: 0 0 8px 8px; }
                .reply-button { display: inline-block; background: #ff6b35; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 15px 0; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h2>📧 TIN NHẮN LIÊN HỆ MỚI</h2>
                    <p>' . SYSTEM_NAME . '</p>
                </div>
                <div class="content">
                    <div class="info-row">
                        <div class="label">👤 Người gửi:</div>
                        <div class="value">' . htmlspecialchars($data['HoTen']) . '</div>
                    </div>
                    
                    <div class="info-row">
                        <div class="label">📧 Email:</div>
                        <div class="value"><a href="mailto:' . htmlspecialchars($data['Email']) . '">' . htmlspecialchars($data['Email']) . '</a></div>
                    </div>
                    
                    ' . (!empty($data['SoDienThoai']) ? '
                    <div class="info-row">
                        <div class="label">📱 Số điện thoại:</div>
                        <div class="value">' . htmlspecialchars($data['SoDienThoai']) . '</div>
                    </div>
                    ' : '') . '
                    
                    <div class="info-row">
                        <div class="label">🕐 Thời gian:</div>
                        <div class="value">' . date('d/m/Y H:i:s') . '</div>
                    </div>
                    
                    <div class="message-box">
                        <div class="label">💬 Nội dung tin nhắn:</div>
                        <div class="value" style="margin-top: 10px; white-space: pre-wrap;">' . nl2br(htmlspecialchars($data['NoiDung'])) . '</div>
                    </div>
                    
                    <div style="text-align: center;">
                        <a href="mailto:' . htmlspecialchars($data['Email']) . '?subject=Re: Liên hệ từ ' . urlencode($data['HoTen']) . '" class="reply-button">
                            ↩️ Trả lời ngay
                        </a>
                    </div>
                </div>
                <div class="footer">
                    <p>Email này được gửi tự động từ hệ thống mượn trả thiết bị<br>
                    Trường Đại học Trà Vinh</p>
                </div>
            </div>
        </body>
        </html>
        ';
        
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        error_log("Error sending email to admin: " . $mail->ErrorInfo);
        return false;
    }
}

/**
 * Gửi email xác nhận cho người dùng
 */
function sendContactConfirmationEmail($data) {
    $mail = createMailer();
    if (!$mail) return false;
    
    try {
        // Người gửi
        $mail->setFrom(SYSTEM_EMAIL, SYSTEM_NAME);
        
        // Người nhận
        $mail->addAddress($data['Email'], $data['HoTen']);
        
        // Nội dung
        $mail->isHTML(true);
        $mail->Subject = 'Xác nhận đã nhận tin nhắn - ' . SYSTEM_NAME;
        
        $mail->Body = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #2c5aa0 0%, #1e3d72 100%); color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { background: #f8f9fa; padding: 30px; border: 1px solid #dee2e6; }
                .success-icon { font-size: 48px; margin-bottom: 15px; }
                .message-box { background: white; padding: 20px; border-left: 4px solid #28a745; margin: 20px 0; border-radius: 5px; }
                .footer { background: #2c5aa0; color: white; padding: 20px; text-align: center; font-size: 12px; border-radius: 0 0 8px 8px; }
                .contact-info { background: white; padding: 15px; margin: 15px 0; border-radius: 5px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <div class="success-icon">✅</div>
                    <h2>CẢM ƠN BẠN ĐÃ LIÊN HỆ!</h2>
                    <p>' . SYSTEM_NAME . '</p>
                </div>
                <div class="content">
                    <p>Xin chào <strong>' . htmlspecialchars($data['HoTen']) . '</strong>,</p>
                    
                    <p>Chúng tôi đã nhận được tin nhắn của bạn và sẽ phản hồi trong thời gian sớm nhất (thường trong vòng 24 giờ làm việc).</p>
                    
                    <div class="message-box">
                        <strong>📝 Nội dung tin nhắn của bạn:</strong>
                        <p style="margin-top: 10px; white-space: pre-wrap;">' . nl2br(htmlspecialchars($data['NoiDung'])) . '</p>
                    </div>
                    
                    <p><em>Nếu bạn có thắc mắc gấp, vui lòng liên hệ trực tiếp qua:</em></p>
                    
                    <div class="contact-info">
                        <p><strong>📞 Hotline:</strong> 0294.3855.246</p>
                        <p><strong>📧 Email:</strong> <a href="mailto:' . ADMIN_EMAIL . '">' . ADMIN_EMAIL . '</a></p>
                        <p><strong>🏢 Địa chỉ:</strong> Số 126, Nguyễn Thiện Thành, Khóm 4, Phường 5, TP. Trà Vinh</p>
                    </div>
                    
                    <p style="margin-top: 20px;">Trân trọng,<br>
                    <strong>Phòng Quản lý Thiết bị</strong><br>
                    Trường Đại học Trà Vinh</p>
                </div>
                <div class="footer">
                    <p>Email này được gửi tự động, vui lòng không trả lời email này.<br>
                    Để liên hệ, vui lòng gửi email đến: ' . ADMIN_EMAIL . '</p>
                </div>
            </div>
        </body>
        </html>
        ';
        
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        error_log("Error sending confirmation email: " . $mail->ErrorInfo);
        return false;
    }
}

/**
 * Gửi email liên hệ (cả admin và người dùng)
 */
function sendContactEmails($data) {
    $adminEmailSent = sendContactEmailToAdmin($data);
    $userEmailSent = sendContactConfirmationEmail($data);
    
    if ($adminEmailSent && $userEmailSent) {
        return [
            'success' => true,
            'message' => 'Cảm ơn bạn đã liên hệ! Chúng tôi đã nhận được tin nhắn và sẽ phản hồi qua email trong thời gian sớm nhất.'
        ];
    } elseif ($adminEmailSent) {
        return [
            'success' => true,
            'message' => 'Tin nhắn của bạn đã được gửi thành công. Chúng tôi sẽ phản hồi sớm nhất có thể.'
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Có lỗi khi gửi email. Vui lòng thử lại sau hoặc liên hệ trực tiếp qua số điện thoại.'
        ];
    }
}
