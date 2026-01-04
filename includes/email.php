<?php
/**
 * Functions gửi email
 * 
 * @author System Development Team
 * @version 1.0
 */

// Email cấu hình
define('ADMIN_EMAIL', 'trantrungphuc98021@gmail.com'); // Email admin nhận tin nhắn
define('SYSTEM_EMAIL', 'tranphilip91@gmail.com'); // Email gửi đi
define('SYSTEM_NAME', 'Hệ thống mượn trả thiết bị - ĐH Trà Vinh');

/**
 * Gửi email thông báo liên hệ cho admin
 * 
 * @param array $data Dữ liệu liên hệ
 * @return bool Kết quả gửi email
 */
function sendContactEmailToAdmin($data) {
    $to = ADMIN_EMAIL;
    $subject = '[Liên hệ mới] ' . $data['HoTen'] . ' - ' . date('d/m/Y H:i');
    
    // Tạo nội dung email HTML
    $message = '
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
    
    // Headers
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= 'From: ' . SYSTEM_NAME . ' <' . SYSTEM_EMAIL . '>' . "\r\n";
    $headers .= 'Reply-To: ' . $data['Email'] . "\r\n";
    
    return mail($to, $subject, $message, $headers);
}

/**
 * Gửi email xác nhận cho người dùng
 * 
 * @param array $data Dữ liệu liên hệ
 * @return bool Kết quả gửi email
 */
function sendContactConfirmationEmail($data) {
    $to = $data['Email'];
    $subject = 'Xác nhận đã nhận tin nhắn - ' . SYSTEM_NAME;
    
    $message = '
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
    
    // Headers
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= 'From: ' . SYSTEM_NAME . ' <' . SYSTEM_EMAIL . '>' . "\r\n";
    
    return mail($to, $subject, $message, $headers);
}

/**
 * Gửi email liên hệ (cả admin và người dùng)
 * 
 * @param array $data Dữ liệu liên hệ
 * @return array Kết quả gửi email
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
