<?php
/**
 * Dashboard - Trang chủ người dùng
 * Hiển thị thông tin cá nhân, phiếu mượn, yêu cầu, thông báo, phạt
 * 
 * @author System Development Team
 * @version 1.0
 */

session_start();

// Kiểm tra đăng nhập
// Development helper: allow local impersonation for debugging
// Usage (ONLY for local dev): /public/dashboard.php?debug_user=ND003&show_raw=1
// normalize localhost check (covers ::1, 127.0.0.1 and IPv4-mapped IPv6)
$remote = $_SERVER['REMOTE_ADDR'] ?? '';
$isLocal = in_array($remote, ['127.0.0.1', '::1', '::ffff:127.0.0.1']);
// Allow forcing debug when developer includes force_debug=1 (temporary)
$forceDebug = isset($_GET['force_debug']) && $_GET['force_debug'] === '1';
$enableDebug = $isLocal || $forceDebug;
if (isset($_GET['debug_user']) && $enableDebug) {
    $_SESSION['user_id'] = $_GET['debug_user'];
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/user.php';

// Lấy thông tin người dùng
$user = getUserInfo($_SESSION['user_id']);

if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Lấy dữ liệu
$phieuMuon = getUserPhieuMuon($_SESSION['user_id']);
$yeuCauMuon = getUserYeuCauMuon($_SESSION['user_id']);
$datTruoc = getUserDatTruoc($_SESSION['user_id']);
$thongBao = getUserThongBao($_SESSION['user_id'], 10);
$phieuPhat = getUserPhieuPhat($_SESSION['user_id']);
$unreadNotifications = countUnreadNotifications($_SESSION['user_id']);

// Dev-only raw output for debugging query results (only from localhost)
if (isset($_GET['show_raw']) && $enableDebug) {
    echo '<div style="padding:1rem;background:#fff8c4;border:1px solid #f1e6a8;margin:1rem 2rem;border-radius:6px;">';
    echo '<strong>DEBUG: $yeuCauMuon (count=' . count($yeuCauMuon) . ')</strong>';
    echo '<pre>' . htmlspecialchars(print_r($yeuCauMuon, true)) . '</pre>';
    echo '</div>';
}

// Fallback: nếu hàm getUserYeuCauMuon trả về rỗng, thử truy vấn trực tiếp vào bảng yeucaumuon
if (empty($yeuCauMuon)) {
    // Đảm bảo hàm dbQuery có sẵn (được require trong includes/user.php)
    $sqlFallback = "SELECT ycm.*, nd_duyet.HoTen as TenNguoiDuyet
                    FROM `yeucaumuon` ycm
                    LEFT JOIN `nguoidung` nd_duyet ON ycm.NguoiDuyet = nd_duyet.MaNguoiDung
                    WHERE ycm.MaNguoiYeuCau = ?
                    AND ycm.IsDeleted = 0
                    ORDER BY ycm.NgayGui DESC";

    $try = dbQuery($sqlFallback, [$_SESSION['user_id']]);
    if (!empty($try)) {
        $yeuCauMuon = $try;
        // Optional debug message when fallback succeeded
        if ($enableDebug) {
            echo '<div style="padding:1rem;background:#e8fff0;border:1px solid #bfeccf;margin:1rem 2rem;border-radius:6px;">';
            echo '<strong>DEBUG: fallback query returned ' . count($try) . ' rows</strong>';
            echo '</div>';
        }
    }
}

// Additional verbose debug: print session and user info when debug enabled
if ($enableDebug) {
    echo '<div style="padding:1rem;background:#f0f7ff;border:1px solid #c5ddff;margin:1rem 2rem;border-radius:6px;">';
    echo '<strong>DEBUG INFO</strong><br/>';
    echo 'Remote IP: ' . htmlspecialchars($remote) . '<br/>';
    echo 'Session user_id: ' . htmlspecialchars($_SESSION['user_id'] ?? 'NULL') . '<br/>';
    echo '<div style="margin-top:.5rem;"><strong>$user (getUserInfo):</strong><pre>' . htmlspecialchars(print_r($user, true)) . '</pre></div>';
    echo '<div style="margin-top:.5rem;"><strong>$yeuCauMuon (count=' . count($yeuCauMuon) . '):</strong><pre>' . htmlspecialchars(print_r($yeuCauMuon, true)) . '</pre></div>';
    echo '</div>';
}

// Helper function để format date
function formatDate($date) {
    if (empty($date)) return 'N/A';
    return date('d/m/Y H:i', strtotime($date));
}

// Helper function để format money
function formatMoney($amount) {
    return number_format($amount, 0, ',', '.') . ' VNĐ';
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Hệ thống mượn trả thiết bị</title>
    <link rel="stylesheet" href="css/styleAbout.css">
    <link rel="stylesheet" href="css/styleDashboard.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
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
                    <a href="dashboard.php" class="nav-link active">Dashboard</a>
                    <a href="about.php" class="nav-link">Giới thiệu</a>
                    <a href="contact.php" class="nav-link">Liên hệ</a>
                </div>
                <div class="nav-auth">
                    <span style="color: var(--primary-color); margin-right: 1rem;">
                        <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($user['HoTen']); ?>
                    </span>
                    <a href="logout.php" class="btn-login">
                        <i class="fas fa-sign-out-alt"></i> Đăng xuất
                    </a>
                </div>
            </div>
        </nav>
    </header>

    <!-- Dashboard Content -->
    <main>
        <div class="dashboard-container">
            <!-- Dashboard Header -->
            <div class="dashboard-header">
                <h1><i class="fas fa-tachometer-alt"></i> Dashboard</h1>
                <p>Chào mừng, <?php echo htmlspecialchars($user['HoTen']); ?>!</p>
            </div>

            <!-- Stats Cards -->
            <div class="info-grid">
                <div class="info-card">
                    <h3><i class="fas fa-file-invoice"></i> Phiếu mượn</h3>
                    <div class="value"><?php echo count($phieuMuon); ?></div>
                    <div class="label">Tổng số phiếu mượn</div>
                </div>
                <div class="info-card">
                    <h3><i class="fas fa-clock"></i> Yêu cầu mượn</h3>
                    <div class="value"><?php echo count($yeuCauMuon); ?></div>
                    <div class="label">Tổng số yêu cầu</div>
                </div>
                <div class="info-card">
                    <h3><i class="fas fa-calendar-check"></i> Đặt trước</h3>
                    <div class="value"><?php echo count($datTruoc); ?></div>
                    <div class="label">Tổng số đặt trước</div>
                </div>
                <div class="info-card">
                    <h3><i class="fas fa-bell"></i> Thông báo</h3>
                    <div class="value">
                        <?php echo $unreadNotifications; ?>
                        <?php if ($unreadNotifications > 0): ?>
                            <span class="status-badge danger" style="margin-left: 0.5rem;">Chưa đọc</span>
                        <?php endif; ?>
                    </div>
                    <div class="label">Thông báo chưa đọc</div>
                </div>
            </div>

            <!-- 1. Thông tin cá nhân -->
            <div class="dashboard-section">
                <h2><i class="fas fa-user"></i> Thông tin cá nhân</h2>
                <div class="personal-info">
                    <div class="info-item">
                        <i class="fas fa-id-card"></i>
                        <div class="info-item-content">
                            <div class="info-item-label">Họ tên</div>
                            <div class="info-item-value"><?php echo htmlspecialchars($user['HoTen']); ?></div>
                        </div>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-user"></i>
                        <div class="info-item-content">
                            <div class="info-item-label">Tên đăng nhập</div>
                            <div class="info-item-value"><?php echo htmlspecialchars($user['TenDangNhap']); ?></div>
                        </div>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-envelope"></i>
                        <div class="info-item-content">
                            <div class="info-item-label">Email</div>
                            <div class="info-item-value"><?php echo htmlspecialchars($user['Email'] ?? 'N/A'); ?></div>
                        </div>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-phone"></i>
                        <div class="info-item-content">
                            <div class="info-item-label">Số điện thoại</div>
                            <div class="info-item-value"><?php echo htmlspecialchars($user['SoDienThoai'] ?? 'N/A'); ?></div>
                        </div>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-graduation-cap"></i>
                        <div class="info-item-content">
                            <div class="info-item-label">Mã sinh viên</div>
                            <div class="info-item-value"><?php echo htmlspecialchars($user['MaSinhVien'] ?? 'N/A'); ?></div>
                        </div>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-building"></i>
                        <div class="info-item-content">
                            <div class="info-item-label">Khoa/Phòng ban</div>
                            <div class="info-item-value"><?php echo htmlspecialchars($user['TenKhoa'] ?? 'N/A'); ?></div>
                        </div>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-user-tag"></i>
                        <div class="info-item-content">
                            <div class="info-item-label">Vai trò</div>
                            <div class="info-item-value"><?php echo htmlspecialchars($user['TenVaiTro'] ?? 'N/A'); ?></div>
                        </div>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-circle"></i>
                        <div class="info-item-content">
                            <div class="info-item-label">Trạng thái hoạt động</div>
                            <div class="info-item-value">
                                <span class="status-badge <?php echo $user['HoatDong'] ? 'success' : 'danger'; ?>">
                                    <?php echo $user['HoatDong'] ? 'Đang hoạt động' : 'Không hoạt động'; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-calendar-plus"></i>
                        <div class="info-item-content">
                            <div class="info-item-label">Ngày tạo tài khoản</div>
                            <div class="info-item-value"><?php echo formatDate($user['NgayTao']); ?></div>
                        </div>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-calendar-edit"></i>
                        <div class="info-item-content">
                            <div class="info-item-label">Ngày cập nhật</div>
                            <div class="info-item-value"><?php echo formatDate($user['NgayCapNhat']); ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Phiếu mượn thiết bị -->
            <div class="dashboard-section">
                <h2><i class="fas fa-file-invoice"></i> Phiếu mượn thiết bị</h2>
                <?php if (empty($phieuMuon)): ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <p>Bạn chưa có phiếu mượn nào</p>
                    </div>
                <?php else: ?>
                    <div class="tabs">
                        <button class="tab-button active" onclick="showTab('phieuMuon')">Tất cả</button>
                    </div>
                    <div id="phieuMuon" class="tab-content active">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Số phiếu</th>
                                    <th>Ngày phát</th>
                                    <th>Ngày phải trả</th>
                                    <th>Ngày trả thực tế</th>
                                    <th>Trạng thái</th>
                                    <th>Tổng tiền phạt</th>
                                    <th>Người phát</th>
                                    <th>Chi tiết</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($phieuMuon as $phieu): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($phieu['SoPhieu']); ?></strong></td>
                                        <td><?php echo formatDate($phieu['NgayPhat']); ?></td>
                                        <td><?php echo formatDate($phieu['NgayPhaiTra']); ?></td>
                                        <td><?php echo formatDate($phieu['NgayTraThucTe']); ?></td>
                                        <td>
                                            <span class="status-badge 
                                                <?php 
                                                if ($phieu['TrangThai'] == 'Đã trả') echo 'success';
                                                elseif ($phieu['TrangThai'] == 'Đang mượn') echo 'info';
                                                else echo 'warning';
                                                ?>">
                                                <?php echo htmlspecialchars($phieu['TrangThai']); ?>
                                            </span>
                                        </td>
                                        <td class="money"><?php echo formatMoney($phieu['TongTienPhat']); ?></td>
                                        <td><?php echo htmlspecialchars($phieu['TenNguoiPhat'] ?? 'N/A'); ?></td>
                                        <td>
                                            <button class="btn btn-primary" onclick="showChiTiet('<?php echo $phieu['MaPhieu']; ?>')">
                                                <i class="fas fa-eye"></i> Xem
                                            </button>
                                        </td>
                                    </tr>
                                    <!-- Chi tiết thiết bị trong phiếu -->
                                    <tr id="chiTiet_<?php echo $phieu['MaPhieu']; ?>" style="display: none;">
                                        <td colspan="8">
                                            <div style="padding: 1rem; background: var(--bg-light); border-radius: var(--border-radius);">
                                                <h4 style="margin-bottom: 1rem; color: var(--primary-color);">Chi tiết thiết bị:</h4>
                                                <?php 
                                                $chiTiet = getChiTietMuon($phieu['MaPhieu']);
                                                if (empty($chiTiet)): ?>
                                                    <p>Không có chi tiết</p>
                                                <?php else: ?>
                                                    <ul class="equipment-detail-list">
                                                        <?php foreach ($chiTiet as $ct): ?>
                                                            <li class="equipment-detail-item">
                                                                <h4><?php echo htmlspecialchars($ct['TenLoai']); ?></h4>
                                                                <div class="detail-row">
                                                                    <div class="detail-row-item">
                                                                        <strong>Mã tài sản:</strong>
                                                                        <?php echo htmlspecialchars($ct['MaTaiSan'] ?? 'N/A'); ?>
                                                                    </div>
                                                                    <div class="detail-row-item">
                                                                        <strong>Số serial:</strong>
                                                                        <?php echo htmlspecialchars($ct['SoSerial'] ?? 'N/A'); ?>
                                                                    </div>
                                                                    <div class="detail-row-item">
                                                                        <strong>Số lượng:</strong>
                                                                        <?php echo $ct['SoLuong']; ?>
                                                                    </div>
                                                                    <div class="detail-row-item">
                                                                        <strong>Tình trạng lúc mượn:</strong>
                                                                        <?php echo htmlspecialchars($ct['TinhTrangLucMuon'] ?? 'N/A'); ?>
                                                                    </div>
                                                                    <div class="detail-row-item">
                                                                        <strong>Tình trạng lúc trả:</strong>
                                                                        <?php echo htmlspecialchars($ct['TinhTrangLucTra'] ?? 'Chưa trả'); ?>
                                                                    </div>
                                                                    <div class="detail-row-item">
                                                                        <strong>Ghi chú:</strong>
                                                                        <?php echo htmlspecialchars($ct['GhiChu'] ?? 'N/A'); ?>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <!-- 3. Yêu cầu mượn thiết bị -->
            <div class="dashboard-section">
                <h2><i class="fas fa-clock"></i> Yêu cầu mượn thiết bị</h2>
                <?php if (empty($yeuCauMuon)): ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <p>Bạn chưa có yêu cầu mượn nào</p>
                    </div>
                <?php else: ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Mã yêu cầu</th>
                                <th>Mục đích</th>
                                <th>Ngày gửi</th>
                                <th>Ngày dự kiến bắt đầu</th>
                                <th>Ngày dự kiến kết thúc</th>
                                <th>Trạng thái</th>
                                <th>Người duyệt</th>
                                <th>Ngày duyệt</th>
                                <th>Ghi chú</th>
                                <th>Chi tiết</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($yeuCauMuon as $yc): ?>
                                <tr>
                                        <td><strong><?php echo htmlspecialchars($yc['MaYeuCau']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($yc['MucDich'] ?? 'N/A'); ?></td>
                                    <td><?php echo formatDate($yc['NgayGui']); ?></td>
                                    <td><?php echo formatDate($yc['NgayDuKienBatDau']); ?></td>
                                    <td><?php echo formatDate($yc['NgayDuKienKetThuc']); ?></td>
                                    <td>
                                        <span class="status-badge 
                                            <?php 
                                            if ($yc['TrangThai'] == 'Đã duyệt') echo 'success';
                                            elseif ($yc['TrangThai'] == 'Từ chối') echo 'danger';
                                            else echo 'warning';
                                            ?>">
                                            <?php echo htmlspecialchars($yc['TrangThai']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($yc['TenNguoiDuyet'] ?? 'N/A'); ?></td>
                                    <td><?php echo formatDate($yc['NgayDuyet']); ?></td>
                                        <td><?php echo htmlspecialchars($yc['GhiChu'] ?? 'N/A'); ?></td>
                                        <td>
                                            <button class="btn btn-secondary" onclick="toggleYeuCauDetail('<?php echo $yc['MaYeuCau']; ?>')">
                                                <i class="fas fa-eye"></i> Xem
                                            </button>
                                        </td>
                                </tr>
                                    <tr id="ycDetail_<?php echo $yc['MaYeuCau']; ?>" style="display: none;">
                                        <td colspan="10">
                                            <div style="padding: 1rem; background: var(--bg-light); border-radius: var(--border-radius);">
                                                <h4 style="margin-bottom: 1rem; color: var(--primary-color);">Chi tiết yêu cầu: <?php echo htmlspecialchars($yc['MaYeuCau']); ?></h4>
                                                <div class="detail-row">
                                                    <div class="detail-row-item"><strong>Mục đích:</strong> <?php echo nl2br(htmlspecialchars($yc['MucDich'] ?? 'N/A')); ?></div>
                                                    <div class="detail-row-item"><strong>Ghi chú:</strong> <?php echo nl2br(htmlspecialchars($yc['GhiChu'] ?? 'N/A')); ?></div>
                                                    <div class="detail-row-item"><strong>Trạng thái:</strong> <?php echo htmlspecialchars($yc['TrangThai']); ?></div>
                                                    <div class="detail-row-item"><strong>Người duyệt:</strong> <?php echo htmlspecialchars($yc['TenNguoiDuyet'] ?? 'N/A'); ?></div>
                                                    <div class="detail-row-item"><strong>Ngày gửi:</strong> <?php echo formatDate($yc['NgayGui']); ?></div>
                                                    <div class="detail-row-item"><strong>Ngày dự kiến:</strong> <?php echo formatDate($yc['NgayDuKienBatDau']) . ' → ' . formatDate($yc['NgayDuKienKetThuc']); ?></div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <!-- 4. Đặt trước thiết bị -->
            <div class="dashboard-section">
                <h2><i class="fas fa-calendar-check"></i> Đặt trước thiết bị</h2>
                <?php if (empty($datTruoc)): ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <p>Bạn chưa có đặt trước nào</p>
                    </div>
                <?php else: ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Loại thiết bị</th>
                                <th>Ngày bắt đầu</th>
                                <th>Ngày kết thúc</th>
                                <th>Trạng thái</th>
                                <th>Ngày tạo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($datTruoc as $dt): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($dt['TenLoai']); ?></td>
                                    <td><?php echo formatDate($dt['NgayBatDau']); ?></td>
                                    <td><?php echo formatDate($dt['NgayKetThuc']); ?></td>
                                    <td>
                                        <span class="status-badge 
                                            <?php 
                                            if ($dt['TrangThai'] == 'Đã duyệt') echo 'success';
                                            elseif ($dt['TrangThai'] == 'Từ chối') echo 'danger';
                                            else echo 'warning';
                                            ?>">
                                            <?php echo htmlspecialchars($dt['TrangThai']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo formatDate($dt['NgayTao']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <!-- 5. Thông báo -->
            <div class="dashboard-section">
                <h2><i class="fas fa-bell"></i> Thông báo 
                    <?php if ($unreadNotifications > 0): ?>
                        <span class="status-badge danger"><?php echo $unreadNotifications; ?> chưa đọc</span>
                    <?php endif; ?>
                </h2>
                <?php if (empty($thongBao)): ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <p>Bạn chưa có thông báo nào</p>
                    </div>
                <?php else: ?>
                    <div>
                        <?php foreach ($thongBao as $tb): ?>
                            <div class="notification-item <?php echo !$tb['DaDoc'] ? 'unread' : ''; ?>">
                                <h4><?php echo htmlspecialchars($tb['TieuDe']); ?></h4>
                                <p><?php echo nl2br(htmlspecialchars($tb['NoiDung'])); ?></p>
                                <div class="notification-date">
                                    <i class="fas fa-calendar"></i> <?php echo formatDate($tb['NgayGui']); ?>
                                    <?php if (!$tb['DaDoc']): ?>
                                        <span class="status-badge info" style="margin-left: 1rem;">Chưa đọc</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- 6. Phiếu phạt -->
            <div class="dashboard-section">
                <h2><i class="fas fa-exclamation-triangle"></i> Phiếu phạt</h2>
                <?php if (empty($phieuPhat)): ?>
                    <div class="empty-state">
                        <i class="fas fa-check-circle"></i>
                        <p>Bạn không có phiếu phạt nào</p>
                    </div>
                <?php else: ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Số phiếu mượn</th>
                                <th>Số tiền phạt</th>
                                <th>Lý do phạt</th>
                                <th>Trạng thái thanh toán</th>
                                <th>Ngày thanh toán</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($phieuPhat as $pp): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($pp['SoPhieu']); ?></td>
                                    <td class="money"><?php echo formatMoney($pp['SoTien']); ?></td>
                                    <td><?php echo htmlspecialchars($pp['LyDo'] ?? 'N/A'); ?></td>
                                    <td>
                                        <span class="status-badge <?php echo $pp['DaThanhToan'] ? 'success' : 'danger'; ?>">
                                            <?php echo $pp['DaThanhToan'] ? 'Đã thanh toán' : 'Chưa thanh toán'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo formatDate($pp['NgayThanhToan']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-bottom">
                <p>&copy; 2024 Trường Đại học Trà Vinh. Tất cả các quyền được bảo lưu.</p>
            </div>
        </div>
    </footer>

    <script>
        function showChiTiet(maPhieu) {
            const chiTietRow = document.getElementById('chiTiet_' + maPhieu);
            if (chiTietRow.style.display === 'none' || chiTietRow.style.display === '') {
                chiTietRow.style.display = 'table-row';
            } else {
                chiTietRow.style.display = 'none';
            }
        }

        function toggleYeuCauDetail(maYeuCau) {
            const row = document.getElementById('ycDetail_' + maYeuCau);
            if (!row) return;
            if (row.style.display === 'none' || row.style.display === '') {
                row.style.display = 'table-row';
            } else {
                row.style.display = 'none';
            }
        }

        function showTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelectorAll('.tab-button').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Show selected tab
            document.getElementById(tabName).classList.add('active');
            event.target.classList.add('active');
        }
    </script>
</body>
</html>

