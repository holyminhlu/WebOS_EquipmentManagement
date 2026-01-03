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

// Xác định admin (robust theo MaVaiTro hoặc TenVaiTro)
$isAdmin = false;
if (isset($user['MaVaiTro']) && (int)$user['MaVaiTro'] === 1) {
    $isAdmin = true;
} elseif (!empty($user['TenVaiTro'])) {
    $tenVaiTro = mb_strtolower(trim((string)$user['TenVaiTro']), 'UTF-8');
    if ($tenVaiTro === 'admin' || $tenVaiTro === 'quản trị' || $tenVaiTro === 'quan tri' || str_contains($tenVaiTro, 'admin')) {
        $isAdmin = true;
    }
}

// Lấy dữ liệu
// Phiếu mượn: admin xem tất cả, user xem của mình
if ($isAdmin) {
    $phieuMuon = getAllPhieuMuon();
} else {
    $phieuMuon = getUserPhieuMuon($_SESSION['user_id']);
}

// Danh sách thiết bị khả dụng cho form đặt trước (đặt trước theo thiết bị cụ thể)
$thietBiKhaDungAll = dbQuery(
    "SELECT tb.MaThietBi, tb.MaLoaiThietBi, ltb.TenLoai
     FROM `thietbi` tb
     LEFT JOIN `loaithietbi` ltb ON tb.MaLoaiThietBi = ltb.MaLoaiThietBi
     WHERE tb.IsDeleted = 0 AND tb.MaTrangThai = 1
     ORDER BY ltb.TenLoai ASC, tb.MaThietBi ASC"
);

// Khu + phòng/địa điểm (dùng cho form đặt trước)
$reserveKhuList = dbQuery(
    "SELECT DISTINCT Khu
     FROM DiaDiem
     WHERE IsDeleted = 0 AND Khu IS NOT NULL AND TRIM(Khu) <> ''
     ORDER BY Khu ASC"
);

$reserveDefaultKhu = '';
if (!empty($reserveKhuList) && isset($reserveKhuList[0]['Khu'])) {
    $reserveDefaultKhu = trim((string)$reserveKhuList[0]['Khu']);
}

$reserveDiaDiemList = [];
if ($reserveDefaultKhu !== '') {
    $reserveDiaDiemList = dbQuery(
        "SELECT MaDiaDiem, TenDiaDiem
         FROM DiaDiem
         WHERE IsDeleted = 0 AND Khu = ?
         ORDER BY TenDiaDiem ASC",
        [$reserveDefaultKhu]
    );
} else {
    // Fallback: nếu chưa có dữ liệu Khu
    $reserveDiaDiemList = dbQuery(
        "SELECT MaDiaDiem, TenDiaDiem
         FROM DiaDiem
         WHERE IsDeleted = 0
         ORDER BY TenDiaDiem ASC"
    );
}

$yeuCauMuon = [];
// Nếu là admin (MaVaiTro = 1) => hiển thị tất cả yêu cầu để quản lý
if ($isAdmin) {
    // Lấy tất cả yêu cầu (hàm mới trong includes/user.php)
    $yeuCauMuon = getAllYeuCauMuon();
} else {
    $yeuCauMuon = getUserYeuCauMuon($_SESSION['user_id']);
}
$datTruoc = [];
// Đặt trước: admin xem tất cả, user xem của mình
if ($isAdmin) {
    $datTruoc = getAllDatTruoc();
} else {
    $datTruoc = getUserDatTruoc($_SESSION['user_id']);
}
$thongBao = getUserThongBao($_SESSION['user_id'], 10);
$phieuPhat = [];
// Phiếu phạt: admin xem tất cả, user xem của mình
if ($isAdmin) {
    $phieuPhat = getAllPhieuPhat();
} else {
    $phieuPhat = getUserPhieuPhat($_SESSION['user_id']);
}
$unreadNotifications = countUnreadNotifications($_SESSION['user_id']);

// Dev-only raw output for debugging query results (only from localhost)
if (isset($_GET['show_raw']) && $enableDebug) {
    // Dev-only raw output for debugging can be enabled by setting ?show_raw=1 when developing (no visual output in production)
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
            // fallback succeeded; no debug UI output here
        }
    }
}

// Additional verbose debug: print session and user info when debug enabled
if ($enableDebug) {
    // End of debug section (no verbose debug output shown)
}

// Helper function để format date
function formatDate($date, $withTime = false) {
    if (empty($date)) return 'N/A';
    $ts = strtotime($date);
    if ($ts === false) return 'N/A';
    return $withTime ? date('d/m/Y H:i', $ts) : date('d/m/Y', $ts);
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
    <link rel="stylesheet" href="css/styleAbout.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="css/styleDashboard.css?v=<?php echo time(); ?>">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        /* Minimal modal styles for confirmation popup */
        .modal {
            position: fixed;
            inset: 0;
            display: none;
            align-items: center;
            justify-content: center;
            background: rgba(0,0,0,0.45);
            z-index: 2000;
            padding: 1rem;
        }
        .modal.open { display: flex; }
        .modal-content {
            background: #fff;
            border-radius: 8px;
            padding: 1.25rem 1.5rem;
            max-width: 520px;
            width: 100%;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        .modal-content h3 { margin: 0 0 0.5rem 0; color: var(--primary-color); }
        .modal-content p { margin: 0 0 1rem 0; color: #333; }
        .modal-actions { text-align: right; }
        .modal-actions .btn { margin-left: 0.5rem; }

        .reserve-form {
            margin-top: 0.75rem;
        }

        .reserve-form .field {
            margin-bottom: 0.85rem;
        }

        .reserve-form label {
            display: block;
            font-weight: 700;
            color: var(--text-color);
            margin-bottom: 0.35rem;
        }

        .reserve-form input[type="datetime-local"] {
            width: 100%;
            padding: 0.65rem 0.75rem;
            border: 2px solid var(--border-color);
            border-radius: var(--border-radius);
            font-family: inherit;
        }

        /* Force display for datetime-local as dd/mm/yyyy HH:mm (overlay), while keeping native picker */
        .dt-display-wrap {
            position: relative;
        }

        .dt-display-wrap input[type="datetime-local"] {
            color: transparent;
            caret-color: transparent;
        }

        .dt-display-wrap input[type="datetime-local"]::-webkit-datetime-edit,
        .dt-display-wrap input[type="datetime-local"]::-webkit-datetime-edit-fields-wrapper,
        .dt-display-wrap input[type="datetime-local"]::-webkit-datetime-edit-text,
        .dt-display-wrap input[type="datetime-local"]::-webkit-datetime-edit-month-field,
        .dt-display-wrap input[type="datetime-local"]::-webkit-datetime-edit-day-field,
        .dt-display-wrap input[type="datetime-local"]::-webkit-datetime-edit-year-field,
        .dt-display-wrap input[type="datetime-local"]::-webkit-datetime-edit-hour-field,
        .dt-display-wrap input[type="datetime-local"]::-webkit-datetime-edit-minute-field,
        .dt-display-wrap input[type="datetime-local"]::-webkit-datetime-edit-ampm-field {
            color: transparent;
        }

        .dt-display-wrap .dt-display {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            padding: 0.65rem 0.75rem;
            padding-right: 2.5rem;
            pointer-events: none;
            color: var(--text-color);
            font-family: inherit;
            font-size: inherit;
            line-height: inherit;
        }

        .reserve-type-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 0.5rem;
            max-height: 240px;
            overflow: auto;
            padding: 0.75rem;
            border: 2px solid var(--border-color);
            border-radius: var(--border-radius);
            background: var(--bg-light);
        }

        .reserve-type-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
            color: var(--text-color);
            user-select: none;
        }

        .reserve-type-item input {
            width: 18px;
            height: 18px;
        }

        .reserve-two {
            display: grid;
            grid-template-columns: 1fr;
            gap: 0.75rem;
        }

        @media (min-width: 640px) {
            .reserve-two {
                grid-template-columns: 1fr 1fr;
            }
        }

        .section-actions {
            display: flex;
            justify-content: flex-end;
            margin-top: -0.5rem;
            margin-bottom: 0.75rem;
        }
        
        /* User info in header */
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
                    <a href="about.php" class="nav-link">Giới thiệu</a>
                    <a href="equipment.php" class="nav-link">Thiết bị</a>
                    <a href="regulations.php" class="nav-link">Quy định & Hướng dẫn</a>
                    <a href="contact.php" class="nav-link">Liên hệ</a>
                    <a href="dashboard.php" class="nav-link active">Dashboard</a>
                </div>
                <div class="nav-auth">
                    <div class="user-info-box">
                        <div class="user-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="user-details">
                            <span class="user-name"><?php echo htmlspecialchars($user['HoTen']); ?></span>
                            <span class="user-role"><?php echo htmlspecialchars($user['TenVaiTro'] ?? 'Người dùng'); ?></span>
                        </div>
                    </div>
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
                                    <th>Tên sản phẩm</th>
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
                                    <?php
                                        // Preload details once to reuse in summary + expanded section
                                        $chiTiet = getChiTietMuon($phieu['MaPhieu']);
                                        $tenSanPhamText = 'N/A';
                                        if (!empty($chiTiet)) {
                                            $tenLoaiSet = [];
                                            foreach ($chiTiet as $ct0) {
                                                $tenLoai = trim((string)($ct0['TenLoai'] ?? ''));
                                                if ($tenLoai !== '') {
                                                    $tenLoaiSet[$tenLoai] = true;
                                                }
                                            }
                                            if (!empty($tenLoaiSet)) {
                                                $tenLoaiList = array_keys($tenLoaiSet);
                                                sort($tenLoaiList);
                                                $tenSanPhamText = implode(', ', $tenLoaiList);
                                            }
                                        }
                                    ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($phieu['SoPhieu']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($tenSanPhamText); ?></td>
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
                                        <td colspan="9">
                                            <div style="padding: 1rem; background: var(--bg-light); border-radius: var(--border-radius);">
                                                <h4 style="margin-bottom: 1rem; color: var(--primary-color);">Chi tiết thiết bị:</h4>
                                                <?php if (empty($chiTiet)): ?>
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
                                <th>Thiết bị</th>
                                <th>Mục đích</th>
                                <th>Ngày gửi</th>
                                <th>Thời gian bắt đầu</th>
                                <th>Thời gian kết thúc</th>
                                <th>Trạng thái</th>
                                <th>Người duyệt</th>
                                <th>Ngày duyệt</th>
                                <th>Ghi chú</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($yeuCauMuon as $yc): ?>
                                <tr>
                                    <?php
                                        // Show requested devices in the main row (avoid a second detail row)
                                        $deviceText = 'N/A';
                                        $rawGhiChuRow = (string)($yc['GhiChu'] ?? '');
                                        if ($rawGhiChuRow !== '' && preg_match('/DS_TB:([^\n\r]+)/', $rawGhiChuRow, $mListRow)) {
                                            $ids = array_values(array_filter(array_map('trim', explode(',', trim($mListRow[1])))));
                                            if (!empty($ids)) {
                                                $deviceText = implode(', ', $ids);
                                            }
                                        }
                                    ?>
                                        <td><strong><?php echo htmlspecialchars($yc['MaYeuCau']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($deviceText); ?></td>
                                        <td><?php echo htmlspecialchars($yc['MucDich'] ?? 'N/A'); ?></td>
                                    <td><?php echo formatDate($yc['NgayGui']); ?></td>
                                    <td><?php echo formatDate($yc['ThoiGianBatDau'], true); ?></td>
                                    <td><?php echo formatDate($yc['ThoiGianKetThuc'], true); ?></td>
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
                                            <?php if ($isAdmin): ?>
                                                <!-- Admin: Nút duyệt -->
                                                <?php if ($yc['TrangThai'] !== 'Đã duyệt'): ?>
                                                    <form method="post" action="actions/yeucaumuon_action.php" class="confirm-approve" data-mayeucau="<?php echo htmlspecialchars($yc['MaYeuCau']); ?>" style="display:inline-block;">
                                                        <input type="hidden" name="action" value="approve">
                                                        <input type="hidden" name="MaYeuCau" value="<?php echo htmlspecialchars($yc['MaYeuCau']); ?>">
                                                        <button type="submit" class="btn btn-success"><i class="fas fa-check"></i> Duyệt</button>
                                                    </form>
                                                <?php else: ?>
                                                    <span class="status-badge success">Đã duyệt</span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <!-- User: Nút hủy -->
                                                <?php if ($yc['TrangThai'] === 'Chờ duyệt'): ?>
                                                    <button type="button" class="btn btn-danger" onclick="cancelYeuCau('<?php echo htmlspecialchars($yc['MaYeuCau']); ?>')">
                                                        <i class="fas fa-times"></i> Hủy yêu cầu
                                                    </button>
                                                <?php else: ?>
                                                    <span class="status-badge <?php echo ($yc['TrangThai'] == 'Đã duyệt') ? 'success' : 'danger'; ?>">
                                                        <?php echo htmlspecialchars($yc['TrangThai']); ?>
                                                    </span>
                                                <?php endif; ?>
                                            <?php endif; ?>
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
                <?php if (!$isAdmin): ?>
                    <div class="section-actions">
                        <button type="button" class="btn btn-primary" onclick="openReserveCreateModal()">
                            <i class="fas fa-plus"></i> Tạo phiếu đặt trước
                        </button>
                    </div>
                <?php endif; ?>
                <?php if (empty($datTruoc)): ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <p>Bạn chưa có đặt trước nào</p>
                    </div>
                <?php else: ?>
                    <?php
                        // Group multiple rows created in one reservation action into a single logical ticket.
                        // For fallback schema (no MaDiaDiem), ids are like DT###D<room>-TBxxx, group key is the prefix before '-'.
                        $datTruocGroups = [];
                        foreach ($datTruoc as $row) {
                            $id = (string)($row['MaDatTruoc'] ?? '');
                            $groupKey = $id;
                            $deviceId = '';

                            // Group key is the prefix before '-', works for both: DT###-TBxxx and DT###D<room>-TBxxx
                            if ($id !== '' && preg_match('/^(DT\d+(?:D\d+)?)(?:-(.+))?$/', $id, $m)) {
                                $groupKey = $m[1];
                                $suffix = isset($m[2]) ? (string)$m[2] : '';
                                if ($suffix !== '' && preg_match('/^TB\d+$/', $suffix)) {
                                    $deviceId = $suffix;
                                }
                            }

                            if (!isset($datTruocGroups[$groupKey])) {
                                $datTruocGroups[$groupKey] = [
                                    'groupKey' => $groupKey,
                                    'devices' => [],
                                    'types' => [],
                                    'NgayBatDau' => $row['NgayBatDau'] ?? null,
                                    'NgayKetThuc' => $row['NgayKetThuc'] ?? null,
                                    'TrangThai' => $row['TrangThai'] ?? null,
                                    'NgayTao' => $row['NgayTao'] ?? null,
                                ];
                            }

                            if ($deviceId !== '') {
                                $datTruocGroups[$groupKey]['devices'][$deviceId] = true;
                            }

                            $tenLoai = trim((string)($row['TenLoai'] ?? ''));
                            if ($tenLoai !== '') {
                                $datTruocGroups[$groupKey]['types'][$tenLoai] = true;
                            }
                        }
                        // Keep display stable: sort by NgayTao desc (fallback to groupKey)
                        uasort($datTruocGroups, function($a, $b) {
                            $ta = strtotime((string)($a['NgayTao'] ?? '')) ?: 0;
                            $tb = strtotime((string)($b['NgayTao'] ?? '')) ?: 0;
                            if ($ta === $tb) return strcmp((string)$b['groupKey'], (string)$a['groupKey']);
                            return $tb <=> $ta;
                        });
                    ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Thiết bị</th>
                                <th>Loại thiết bị</th>
                                <th>Ngày bắt đầu</th>
                                <th>Ngày kết thúc</th>
                                <th>Trạng thái</th>
                                <th>Ngày tạo</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($datTruocGroups as $dtg): ?>
                                <tr>
                                    <?php
                                        $deviceList = array_keys($dtg['devices']);
                                        sort($deviceList);
                                        $deviceText = !empty($deviceList) ? implode(', ', $deviceList) : 'N/A';

                                        $typeList = array_keys($dtg['types']);
                                        sort($typeList);
                                        $typeText = !empty($typeList) ? implode(', ', $typeList) : 'N/A';

                                        $status = (string)($dtg['TrangThai'] ?? '');
                                    ?>
                                    <td><?php echo htmlspecialchars($deviceText); ?></td>
                                    <td><?php echo htmlspecialchars($typeText); ?></td>
                                    <td><?php echo formatDate($dtg['NgayBatDau'], true); ?></td>
                                    <td><?php echo formatDate($dtg['NgayKetThuc'], true); ?></td>
                                    <td>
                                        <span class="status-badge 
                                            <?php 
                                            if ($status == 'Đã duyệt') echo 'success';
                                            elseif ($status == 'Từ chối') echo 'danger';
                                            elseif ($status == 'Đã hủy') echo 'danger';
                                            else echo 'warning';
                                            ?>">
                                            <?php echo htmlspecialchars($status); ?>
                                        </span>
                                    </td>
                                    <td><?php echo formatDate($dtg['NgayTao']); ?></td>
                                    <td>
                                        <?php if ($isAdmin): ?>
                                            <?php if ($status === 'Chờ duyệt'): ?>
                                                <form method="post" action="actions/dattruoc_action.php" style="display:inline-block;" onsubmit="return confirm('Bạn có chắc muốn duyệt phiếu đặt trước ' + <?php echo json_encode((string)$dtg['groupKey']); ?> + ' không?');">
                                                    <input type="hidden" name="action" value="approve">
                                                    <input type="hidden" name="MaDatTruoc" value="<?php echo htmlspecialchars($dtg['groupKey']); ?>">
                                                    <button type="submit" class="btn btn-success"><i class="fas fa-check"></i> Duyệt</button>
                                                </form>
                                            <?php else: ?>
                                                <span class="status-badge <?php echo ($status == 'Đã duyệt') ? 'success' : 'danger'; ?>">
                                                    <?php echo htmlspecialchars($status); ?>
                                                </span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <?php if ($status === 'Chờ duyệt'): ?>
                                                <button type="button" class="btn btn-danger" onclick="cancelDatTruoc('<?php echo htmlspecialchars($dtg['groupKey']); ?>')">
                                                    <i class="fas fa-times"></i> Hủy
                                                </button>
                                            <?php else: ?>
                                                <span class="status-badge <?php echo ($status == 'Đã duyệt') ? 'success' : 'danger'; ?>">
                                                    <?php echo htmlspecialchars($status); ?>
                                                </span>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
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
                                <th>Chi tiết</th>
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
                                    <td>
                                        <button class="btn btn-secondary" onclick="togglePhieuPhatDetail('<?php echo $pp['MaPhat']; ?>')">
                                            <i class="fas fa-eye"></i> Xem
                                        </button>
                                    </td>
                                </tr>
                                <tr id="ppDetail_<?php echo $pp['MaPhat']; ?>" style="display:none;">
                                    <td colspan="6">
                                        <div style="padding:1rem;background:var(--bg-light);border-radius:var(--border-radius);">
                                            <h4 style="color:var(--primary-color);">Chi tiết phiếu phạt: <?php echo htmlspecialchars($pp['MaPhat']); ?></h4>
                                            <div class="detail-row">
                                                <div class="detail-row-item"><strong>Số phiếu mượn:</strong> <?php echo htmlspecialchars($pp['MaPhieu']); ?></div>
                                                <div class="detail-row-item"><strong>Số tiền:</strong> <?php echo formatMoney($pp['SoTien']); ?></div>
                                                <div class="detail-row-item"><strong>Lý do:</strong> <?php echo htmlspecialchars($pp['LyDo'] ?? 'N/A'); ?></div>
                                                <div class="detail-row-item"><strong>Trạng thái thanh toán:</strong> <?php echo $pp['DaThanhToan'] ? 'Đã thanh toán' : 'Chưa thanh toán'; ?></div>
                                                <div class="detail-row-item"><strong>Ngày thanh toán:</strong> <?php echo formatDate($pp['NgayThanhToan']); ?></div>
                                            </div>
                                        </div>
                                    </td>
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
        function pad2(n) { return String(n).padStart(2, '0'); }
        function toDateTimeLocalValue(dateObj) {
            const yyyy = dateObj.getFullYear();
            const mm = pad2(dateObj.getMonth() + 1);
            const dd = pad2(dateObj.getDate());
            const hh = pad2(dateObj.getHours());
            const mi = pad2(dateObj.getMinutes());
            return `${yyyy}-${mm}-${dd}T${hh}:${mi}`;
        }

        function formatDmyHmFromLocalValue(val) {
            // val is expected: YYYY-MM-DDTHH:MM
            if (!val || typeof val !== 'string') return '';
            const parts = val.split('T');
            if (parts.length !== 2) return '';
            const d = parts[0].split('-');
            const t = parts[1].split(':');
            if (d.length !== 3 || t.length < 2) return '';
            const yyyy = d[0];
            const mm = d[1];
            const dd = d[2];
            const hh = t[0];
            const mi = t[1];
            if (!yyyy || !mm || !dd || hh == null || mi == null) return '';
            return `${dd}/${mm}/${yyyy} ${hh}:${mi}`;
        }

        function localYmd(d) {
            const yyyy = d.getFullYear();
            const mm = String(d.getMonth() + 1).padStart(2, '0');
            const dd = String(d.getDate()).padStart(2, '0');
            return `${yyyy}-${mm}-${dd}`;
        }

        function enforceMinTomorrow(startInput) {
            if (!startInput) return;
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            const minYmd = localYmd(tomorrow);
            const minVal = `${minYmd}T00:00`;
            startInput.min = minVal;
        }

        function enhanceDateTimeLocalDisplay() {
            const inputs = Array.from(document.querySelectorAll('input[type="datetime-local"]'));
            inputs.forEach(function(input) {
                if (input.closest('.dt-display-wrap')) return;

                const wrap = document.createElement('div');
                wrap.className = 'dt-display-wrap';
                input.parentNode.insertBefore(wrap, input);
                wrap.appendChild(input);

                const span = document.createElement('div');
                span.className = 'dt-display';
                span.setAttribute('aria-hidden', 'true');
                wrap.appendChild(span);

                function sync() {
                    span.textContent = formatDmyHmFromLocalValue(input.value) || '';
                }

                input.addEventListener('input', sync);
                input.addEventListener('change', sync);
                sync();
            });
        }

        enhanceDateTimeLocalDisplay();

        function notifyValueChanged(el) {
            if (!el) return;
            el.dispatchEvent(new Event('input', { bubbles: true }));
            el.dispatchEvent(new Event('change', { bubbles: true }));
        }

        function bindSameDayRange(startInput, endInput, opts) {
            if (!startInput || !endInput) return;
            opts = opts || {};
            const defaultEndHour = Number.isFinite(opts.defaultEndHour) ? opts.defaultEndHour : 17;

            function datePart(v) { return (v || '').split('T')[0] || ''; }
            function timePart(v) { return (v || '').split('T')[1] || '00:00'; }
            function endOfDay(date) { return `${date}T23:59`; }
            function defaultEnd(date) { return `${date}T${pad2(defaultEndHour)}:00`; }

            function apply() {
                const s = (startInput.value || '').trim();
                if (!s) return;
                const d = datePart(s);
                if (!d) return;

                // Do not auto-correct user's chosen datetime; only constrain end date to same day.
                endInput.min = `${d}T00:00`;
                endInput.max = endOfDay(d);
            }

            if (startInput.dataset.sameDayBound !== '1') {
                startInput.dataset.sameDayBound = '1';
                startInput.addEventListener('change', apply);
                startInput.addEventListener('input', apply);
                endInput.addEventListener('change', apply);
            }
            apply();
        }

        function openReserveCreateModal() {
            const modal = document.getElementById('reserveCreateModal');
            if (!modal) return;

            // reset
            document.querySelectorAll('.reserve-type-checkbox').forEach(cb => { cb.checked = false; });

            const start = new Date();
            start.setDate(start.getDate() + 1);
            start.setHours(8, 0, 0, 0);
            const end = new Date(start.getTime());
            end.setHours(17, 0, 0, 0);

            const startEl = document.getElementById('reserveCreateNgayBatDau');
            const endEl = document.getElementById('reserveCreateNgayKetThuc');
            if (startEl) startEl.value = toDateTimeLocalValue(start);
            if (endEl) endEl.value = toDateTimeLocalValue(end);
            notifyValueChanged(startEl);
            notifyValueChanged(endEl);

            // Reservation start must be after today (>= tomorrow)
            enforceMinTomorrow(startEl);
            if (startEl && startEl.dataset.minTomorrowBound !== '1') {
                startEl.dataset.minTomorrowBound = '1';
                startEl.addEventListener('focus', function() { enforceMinTomorrow(startEl); });
                startEl.addEventListener('click', function() { enforceMinTomorrow(startEl); });
            }

            // Đặt trong 1 ngày
            bindSameDayRange(startEl, endEl, { defaultEndHour: 17 });

            modal.classList.add('open');
        }

        function closeReserveCreateModal() {
            const modal = document.getElementById('reserveCreateModal');
            if (!modal) return;
            modal.classList.remove('open');
        }

        function submitReserveCreate(e) {
            e.preventDefault();

            const checked = Array.from(document.querySelectorAll('.reserve-type-checkbox:checked'));
            if (checked.length === 0) {
                alert('Vui lòng chọn ít nhất 1 thiết bị để đặt trước.');
                return;
            }
            if (checked.length > 10) {
                alert('Chỉ được chọn tối đa 10 thiết bị cho mỗi lần đặt trước.');
                return;
            }

            const ngayBatDau = document.getElementById('reserveCreateNgayBatDau').value;
            const ngayKetThuc = document.getElementById('reserveCreateNgayKetThuc').value;
            const maDiaDiemSuDung = (document.getElementById('reserveCreateMaDiaDiemSuDung')?.value || '').trim();
            if (!ngayBatDau || !ngayKetThuc || !maDiaDiemSuDung) {
                alert('Vui lòng nhập đầy đủ thông tin bắt buộc.');
                return;
            }

            const start = new Date(ngayBatDau);
            const end = new Date(ngayKetThuc);
            if (!(start < end)) {
                alert('Thời gian không hợp lệ: ngày kết thúc phải sau ngày bắt đầu.');
                return;
            }

            if (ngayBatDau.split('T')[0] !== ngayKetThuc.split('T')[0]) {
                alert('Thời gian đặt trước chỉ trong 1 ngày. Vui lòng chọn ngày kết thúc cùng ngày bắt đầu.');
                return;
            }
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            const minYmd = localYmd(tomorrow);
            if (ngayBatDau.split('T')[0] < minYmd) {
                alert('Ngày bắt đầu phải từ ngày mai trở đi.');
                return;
            }

            const formData = new FormData();
            checked.forEach(cb => formData.append('maThietBi[]', cb.value));
            formData.append('ngayBatDau', ngayBatDau.replace('T', ' ') + ':00');
            formData.append('ngayKetThuc', ngayKetThuc.replace('T', ' ') + ':00');
            formData.append('maDiaDiemSuDung', maDiaDiemSuDung);

            fetch('actions/create_dattruoc.php', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    closeReserveCreateModal();
                    window.location.reload();
                } else {
                    alert('Lỗi: ' + data.message);
                }
            })
            .catch(err => alert('Lỗi kết nối: ' + err.message));
        }
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
        
        function cancelYeuCau(maYeuCau) {
            if (!confirm('Bạn có chắc chắn muốn hủy yêu cầu mượn này?\n\nSau khi hủy, yêu cầu sẽ không thể được duyệt.')) {
                return;
            }
            
            // Gửi AJAX request để hủy yêu cầu
            const formData = new FormData();
            formData.append('maYeuCau', maYeuCau);
            
            fetch('actions/cancel_yeucaumuon.php', {
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

        function cancelDatTruoc(maDatTruoc) {
            if (!confirm('Bạn có chắc chắn muốn hủy yêu cầu đặt trước này?\n\nChỉ có thể hủy khi đang ở trạng thái "Chờ duyệt".')) {
                return;
            }

            const formData = new FormData();
            formData.append('maDatTruoc', maDatTruoc);

            fetch('actions/cancel_dattruoc.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    window.location.reload();
                } else {
                    alert('Lỗi: ' + data.message);
                }
            })
            .catch(error => {
                alert('Lỗi kết nối: ' + error.message);
            });
        }

        function togglePhieuPhatDetail(maPhat) {
            const row = document.getElementById('ppDetail_' + maPhat);
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

        // Use a custom modal for approve confirmation and double-submit prevention
        document.addEventListener('DOMContentLoaded', function () {
            var activeForm = null;
            var modal = document.getElementById('confirmModal');
            var modalMessage = document.getElementById('confirmModalMessage');
            var btnConfirm = document.getElementById('confirmModalConfirm');
            var btnCancel = document.getElementById('confirmModalCancel');

            document.querySelectorAll('form.confirm-approve').forEach(function(form) {
                form.addEventListener('submit', function (e) {
                    // If already processing, block
                    if (form.dataset.processing === '1') {
                        e.preventDefault();
                        return false;
                    }

                    e.preventDefault();
                    activeForm = form;
                    var ma = form.dataset.mayeucau || '';
                    modalMessage.textContent = 'Bạn có chắc muốn duyệt yêu cầu ' + ma + ' không?';
                    modal.classList.add('open');
                    return false;
                });
            });

            btnCancel.addEventListener('click', function () {
                modal.classList.remove('open');
                activeForm = null;
            });

            btnConfirm.addEventListener('click', function () {
                if (!activeForm) {
                    modal.classList.remove('open');
                    return;
                }
                // mark processing and disable submit buttons
                activeForm.dataset.processing = '1';
                activeForm.querySelectorAll('button[type="submit"]').forEach(function(btn){
                    btn.disabled = true;
                    btn.dataset.orig = btn.innerText;
                    btn.innerText = 'Đang xử lý...';
                });
                modal.classList.remove('open');
                // submit the form programmatically
                activeForm.submit();
            });

            // close modal when clicking outside content
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    modal.classList.remove('open');
                    activeForm = null;
                }
            });

            // Reservation create modal close on outside click
            var reserveModal = document.getElementById('reserveCreateModal');
            if (reserveModal) {
                reserveModal.addEventListener('click', function(e) {
                    if (e.target === reserveModal) {
                        closeReserveCreateModal();
                    }
                });
            }

            // Dependent dropdown: Khu -> Phòng (reserve create)
            (function initKhuPhongReserveCreate() {
                const khuSelect = document.getElementById('reserveCreateKhuSuDung');
                const phongSelect = document.getElementById('reserveCreateMaDiaDiemSuDung');
                if (!khuSelect || !phongSelect) return;

                function setPhongLoading() {
                    phongSelect.innerHTML = '<option value="">-- Đang tải phòng/địa điểm --</option>';
                    phongSelect.disabled = true;
                }

                function setPhongOptions(items) {
                    const opts = ['<option value="">-- Chọn phòng/địa điểm --</option>'];
                    (items || []).forEach(function(it) {
                        const id = (it && it.MaDiaDiem != null) ? String(it.MaDiaDiem) : '';
                        const name = (it && it.TenDiaDiem != null) ? String(it.TenDiaDiem) : '';
                        if (!id) return;
                        opts.push('<option value="' + id.replace(/"/g, '&quot;') + '">' +
                            name.replace(/</g, '&lt;').replace(/>/g, '&gt;') +
                        '</option>');
                    });
                    phongSelect.innerHTML = opts.join('');
                    phongSelect.disabled = false;
                }

                function loadPhongByKhu() {
                    const khu = (khuSelect.value || '').trim();
                    if (!khu) return;

                    setPhongLoading();
                    fetch('actions/get_diadiem_by_khu.php?khu=' + encodeURIComponent(khu), {
                        method: 'GET',
                        headers: { 'Accept': 'application/json' }
                    })
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        if (!data || !data.success) {
                            throw new Error((data && data.message) ? data.message : 'Không lấy được danh sách phòng');
                        }
                        setPhongOptions(data.items || []);
                    })
                    .catch(function(err) {
                        phongSelect.innerHTML = '<option value="">-- Chọn phòng/địa điểm --</option>';
                        phongSelect.disabled = false;
                        console.error(err);
                    });
                }

                khuSelect.addEventListener('change', loadPhongByKhu);

                // Auto-load if empty
                try {
                    const hasOnlyPlaceholder = (phongSelect.options && phongSelect.options.length <= 1);
                    if ((khuSelect.value || '').trim() && hasOnlyPlaceholder) {
                        loadPhongByKhu();
                    }
                } catch (e) {
                    // ignore
                }
            })();
        });
    </script>

    <!-- Create reservation modal -->
    <div id="reserveCreateModal" class="modal" role="dialog" aria-modal="true" aria-labelledby="reserveCreateTitle">
        <div class="modal-content">
            <h3 id="reserveCreateTitle">Tạo phiếu đặt trước</h3>
            <p>Chọn loại thiết bị và thời gian đặt trước (đúng theo CSDL DatTruoc).</p>

            <form class="reserve-form" onsubmit="submitReserveCreate(event)">
                <div class="field">
                    <label>Thiết bị (có thể chọn nhiều)</label>
                    <div class="reserve-type-grid">
                        <?php foreach ($thietBiKhaDungAll as $tb): ?>
                            <label class="reserve-type-item">
                                <input class="reserve-type-checkbox" type="checkbox" value="<?php echo htmlspecialchars($tb['MaThietBi']); ?>">
                                <?php echo htmlspecialchars($tb['MaThietBi'] . ' - ' . ($tb['TenLoai'] ?? $tb['MaLoaiThietBi'])); ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="field">
                    <label for="reserveCreateKhuSuDung">Khu *</label>
                    <select id="reserveCreateKhuSuDung" required>
                        <option value="">-- Chọn khu --</option>
                        <?php foreach ($reserveKhuList as $k):
                            $kv = isset($k['Khu']) ? trim((string)$k['Khu']) : '';
                            if ($kv === '') continue;
                            $selected = ($kv === $reserveDefaultKhu) ? 'selected' : '';
                        ?>
                            <option value="<?php echo htmlspecialchars($kv); ?>" <?php echo $selected; ?>><?php echo htmlspecialchars($kv); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="field">
                    <label for="reserveCreateMaDiaDiemSuDung">Phòng/địa điểm *</label>
                    <select id="reserveCreateMaDiaDiemSuDung" required>
                        <option value="">-- Chọn phòng/địa điểm --</option>
                        <?php foreach ($reserveDiaDiemList as $dd): ?>
                            <option value="<?php echo (int)$dd['MaDiaDiem']; ?>"><?php echo htmlspecialchars($dd['TenDiaDiem'] ?? ''); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="field reserve-two">
                    <div>
                        <label for="reserveCreateNgayBatDau">Ngày bắt đầu *</label>
                        <input id="reserveCreateNgayBatDau" type="datetime-local" lang="vi-VN" required>
                    </div>
                    <div>
                        <label for="reserveCreateNgayKetThuc">Ngày kết thúc *</label>
                        <input id="reserveCreateNgayKetThuc" type="datetime-local" lang="vi-VN" required>
                    </div>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeReserveCreateModal()">Hủy</button>
                    <button type="submit" class="btn btn-primary">Gửi đặt trước</button>
                </div>
            </form>
        </div>
    </div>
    <!-- Confirmation modal -->
    <div id="confirmModal" class="modal" aria-hidden="true">
        <div class="modal-content" role="dialog" aria-modal="true" aria-labelledby="confirmModalTitle">
            <h3 id="confirmModalTitle">Xác nhận duyệt</h3>
            <p id="confirmModalMessage">Bạn có chắc muốn duyệt?</p>
            <div class="modal-actions">
                <button id="confirmModalCancel" class="btn btn-secondary">Hủy</button>
                <button id="confirmModalConfirm" class="btn btn-success">Duyệt</button>
            </div>
        </div>
    </div>
</body>
</html>

