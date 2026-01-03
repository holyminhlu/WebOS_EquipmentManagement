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

// Quản trị hệ thống (MaVaiTro = 1101) chỉ dùng giao diện riêng
if (isset($user['MaVaiTro']) && (int)$user['MaVaiTro'] === 1101) {
    header('Location: system_admin.php');
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

// Snapshot: user có phiếu phạt chưa thanh toán (chỉ áp dụng cho user)
$hasUnpaidFines = false;
if (!$isAdmin) {
    $hasUnpaidFines = userHasUnpaidPhieuPhat($_SESSION['user_id']);
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
$thongBaoLimit = 4;
$thongBaoOffset = 0;
$thongBao = getUserThongBao($_SESSION['user_id'], $thongBaoLimit, $thongBaoOffset);
$totalThongBaoRow = dbQueryOne(
    "SELECT COUNT(*) AS cnt FROM `thongbao` WHERE MaNguoiDung = ? AND IsDeleted = 0",
    [$_SESSION['user_id']]
);
$totalThongBao = ($totalThongBaoRow && isset($totalThongBaoRow['cnt'])) ? (int)$totalThongBaoRow['cnt'] : 0;
$thongBaoNextOffset = $thongBaoOffset + count($thongBao);
$thongBaoHasMore = $thongBaoNextOffset < $totalThongBao;
$phieuPhat = [];
// Phiếu phạt: admin xem tất cả, user xem của mình
if ($isAdmin) {
    $phieuPhat = getAllPhieuPhat();
} else {
    $phieuPhat = getUserPhieuPhat($_SESSION['user_id']);
}
$baoTriList = [];
if ($isAdmin) {
    // Maintenance requests (admin only)
    $baoTriList = dbQuery(
        "SELECT bt.*, tb.MaLoaiThietBi, ltb.TenLoai
         FROM `baotri` bt
         INNER JOIN `thietbi` tb ON bt.MaThietBi = tb.MaThietBi
         LEFT JOIN `loaithietbi` ltb ON tb.MaLoaiThietBi = ltb.MaLoaiThietBi
         WHERE bt.IsDeleted = 0
         ORDER BY bt.NgayBao DESC"
    );
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
                        <?php
                            // Admin helpers: determine if a borrow slip already has any fine(s)
                            // so we can disable Phạt/Đã trả per requirement.
                            $fineInfoByPhieu = []; // [MaPhieu] => ['total' => int, 'unpaid' => int]
                            // Borrow slip room/location (Phòng): parse from YeuCauMuon.GhiChu line "DD:<MaDiaDiem>"
                            $roomIdByYeuCau = [];   // [MaYeuCau] => int
                            $roomNameById = [];     // [MaDiaDiem] => TenDiaDiem
                            if (!empty($phieuMuon)) {
                                $yeuCauIds = [];
                                foreach ($phieuMuon as $pRoom) {
                                    $ycId = isset($pRoom['MaYeuCau']) ? trim((string)$pRoom['MaYeuCau']) : '';
                                    if ($ycId !== '') $yeuCauIds[] = $ycId;
                                }
                                $yeuCauIds = array_values(array_unique($yeuCauIds));

                                if (!empty($yeuCauIds)) {
                                    $phYc = implode(',', array_fill(0, count($yeuCauIds), '?'));
                                    $ycRows = dbQuery(
                                        "SELECT MaYeuCau, GhiChu FROM `yeucaumuon` WHERE IsDeleted = 0 AND MaYeuCau IN ($phYc)",
                                        $yeuCauIds
                                    );
                                    $roomIds = [];
                                    foreach ($ycRows as $yr) {
                                        $ycId = isset($yr['MaYeuCau']) ? trim((string)$yr['MaYeuCau']) : '';
                                        if ($ycId === '') continue;
                                        $ghiChu = (string)($yr['GhiChu'] ?? '');
                                        if ($ghiChu === '') continue;
                                        $m = [];
                                        if (preg_match('/(?:^|\r\n|\r|\n)DD:(\d+)(?:\r\n|\r|\n|$)/', $ghiChu, $m)) {
                                            $rid = (int)$m[1];
                                            if ($rid > 0) {
                                                $roomIdByYeuCau[$ycId] = $rid;
                                                $roomIds[] = $rid;
                                            }
                                        }
                                    }

                                    $roomIds = array_values(array_unique($roomIds));
                                    if (!empty($roomIds)) {
                                        $phDd = implode(',', array_fill(0, count($roomIds), '?'));
                                        $ddRows = dbQuery(
                                            "SELECT MaDiaDiem, TenDiaDiem FROM `diadiem` WHERE IsDeleted = 0 AND MaDiaDiem IN ($phDd)",
                                            $roomIds
                                        );
                                        foreach ($ddRows as $dd) {
                                            $id = isset($dd['MaDiaDiem']) ? (int)$dd['MaDiaDiem'] : 0;
                                            if ($id <= 0) continue;
                                            $roomNameById[$id] = (string)($dd['TenDiaDiem'] ?? '');
                                        }
                                    }
                                }
                            }
                            if ($isAdmin && !empty($phieuMuon)) {
                                $phieuIds = [];
                                foreach ($phieuMuon as $p0) {
                                    $id0 = isset($p0['MaPhieu']) ? trim((string)$p0['MaPhieu']) : '';
                                    if ($id0 !== '') $phieuIds[] = $id0;
                                }
                                $phieuIds = array_values(array_unique($phieuIds));
                                if (!empty($phieuIds)) {
                                    $placeholders = implode(',', array_fill(0, count($phieuIds), '?'));
                                    $rows = dbQuery(
                                        "SELECT MaPhieu,
                                                COUNT(*) AS total,
                                                SUM(CASE WHEN DaThanhToan = 0 THEN 1 ELSE 0 END) AS unpaid
                                         FROM `phieuphat`
                                         WHERE IsDeleted = 0 AND MaPhieu IN ($placeholders)
                                         GROUP BY MaPhieu",
                                        $phieuIds
                                    );
                                    foreach ($rows as $r) {
                                        $mp = (string)($r['MaPhieu'] ?? '');
                                        if ($mp === '') continue;
                                        $fineInfoByPhieu[$mp] = [
                                            'total' => isset($r['total']) ? (int)$r['total'] : 0,
                                            'unpaid' => isset($r['unpaid']) ? (int)$r['unpaid'] : 0,
                                        ];
                                    }
                                }
                            }
                        ?>
                        <table class="data-table" id="phieuMuonTable">
                            <thead>
                                <tr>
                                    <th>Phiếu</th>
                                    <th>Thiết bị</th>
                                    <th>Phòng</th>
                                    <th>Ngày phát</th>
                                    <th>Ngày trả</th>
                                    <th>Thực trả</th>
                                    <th>Trạng thái</th>
                                    <th>Tiền Phạt</th>
                                    <th>Người phát</th>
                                    <th>Chi tiết</th>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($phieuMuon as $phieu): ?>
                                    <?php
                                        // Preload details once to reuse in summary + expanded section
                                        $chiTiet = getChiTietMuon($phieu['MaPhieu']);
                                        $tenSanPhamText = 'N/A';
                                        $thietBiIdText = 'N/A';
                                        if (!empty($chiTiet)) {
                                            $tenLoaiSet = [];
                                            $tbIdSet = [];
                                            foreach ($chiTiet as $ct0) {
                                                $tenLoai = trim((string)($ct0['TenLoai'] ?? ''));
                                                if ($tenLoai !== '') {
                                                    $tenLoaiSet[$tenLoai] = true;
                                                }
                                                $tbId = trim((string)($ct0['MaThietBi'] ?? ''));
                                                if ($tbId !== '') {
                                                    $tbIdSet[$tbId] = true;
                                                }
                                            }
                                            if (!empty($tenLoaiSet)) {
                                                $tenLoaiList = array_keys($tenLoaiSet);
                                                sort($tenLoaiList);
                                                $tenSanPhamText = implode(', ', $tenLoaiList);
                                            }
                                            if (!empty($tbIdSet)) {
                                                $tbIdList = array_keys($tbIdSet);
                                                sort($tbIdList);
                                                $thietBiIdText = implode(', ', $tbIdList);
                                            }
                                        }
                                    ?>
                                    <tr class="pm-item" data-key="<?php echo htmlspecialchars((string)$phieu['MaPhieu']); ?>">
                                        <?php $isDatTruocPhieu = !empty($phieu['MaYeuCau']) && preg_match('/^DT\d+/', (string)$phieu['MaYeuCau']); ?>
                                        <td><strong<?php echo $isDatTruocPhieu ? ' style="color: var(--danger-color);"' : ''; ?>><?php echo htmlspecialchars($phieu['SoPhieu']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($tenSanPhamText); ?></td>
                                        <td>
                                            <?php
                                                $phongText = 'N/A';
                                                $ycId = isset($phieu['MaYeuCau']) ? trim((string)$phieu['MaYeuCau']) : '';
                                                if ($ycId !== '' && isset($roomIdByYeuCau[$ycId])) {
                                                    $rid = (int)$roomIdByYeuCau[$ycId];
                                                    $name = $roomNameById[$rid] ?? '';
                                                    $phongText = ($name !== '') ? $name : ('#' . $rid);
                                                }
                                                echo htmlspecialchars($phongText);
                                            ?>
                                        </td>
                                        <td><?php echo formatDate($phieu['NgayPhat']); ?></td>
                                        <td><?php echo formatDate($phieu['NgayPhaiTra']); ?></td>
                                        <td><?php echo formatDate($phieu['NgayTraThucTe']); ?></td>
                                        <td>
                                            <span class="status-badge 
                                                <?php 
                                                if ($phieu['TrangThai'] == 'Đã trả' || $phieu['TrangThai'] == 'Hoàn thành') echo 'success';
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
                                        <td>
                                            <?php if ($isAdmin): ?>
                                                <?php
                                                    $statusNow = (string)($phieu['TrangThai'] ?? '');
                                                    $isCompleted = ($statusNow === 'Hoàn thành' || $statusNow === 'Đã trả');
                                                    $maYeuCauFromPhieu = isset($phieu['MaYeuCau']) ? (string)$phieu['MaYeuCau'] : '';
                                                    $fineInfo = $fineInfoByPhieu[(string)$phieu['MaPhieu']] ?? ['total' => 0, 'unpaid' => 0];
                                                    $hasFine = ((int)$fineInfo['total'] > 0);
                                                    $disableActions = $isCompleted || $hasFine;
                                                    $disableTitle = '';
                                                    if ($isCompleted) {
                                                        $disableTitle = 'Phiếu mượn đã hoàn thành';
                                                    } elseif ($hasFine) {
                                                        $disableTitle = 'Đã lập phiếu phạt, không thể thao tác';
                                                    }
                                                ?>
                                                <div style="display:flex;flex-direction:column;gap:0.5rem;align-items:flex-start;">
                                                    <button type="button" class="btn btn-danger" <?php echo $disableActions ? 'disabled' : ''; ?> <?php echo $disableTitle !== '' ? 'title="' . htmlspecialchars($disableTitle) . '"' : ''; ?>
                                                        onclick='openFineModalForPhieu(<?php echo json_encode((string)$phieu['MaPhieu']); ?>, <?php echo json_encode((string)$phieu['SoPhieu']); ?>, <?php echo json_encode((string)$thietBiIdText); ?>, <?php echo json_encode((string)$maYeuCauFromPhieu); ?>)'>
                                                        <i class="fas fa-gavel"></i> Phạt
                                                    </button>
                                                    <button type="button" class="btn btn-success" <?php echo $disableActions ? 'disabled' : ''; ?> <?php echo $disableTitle !== '' ? 'title="' . htmlspecialchars($disableTitle) . '"' : ''; ?>
                                                        onclick='markReturned(<?php echo json_encode((string)$phieu['MaPhieu']); ?>)'>
                                                        <i class="fas fa-undo"></i> Đã trả
                                                    </button>
                                                </div>
                                            <?php else: ?>
                                                <span class="status-badge secondary">N/A</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <!-- Chi tiết thiết bị trong phiếu -->
                                    <tr id="chiTiet_<?php echo $phieu['MaPhieu']; ?>" class="pm-detail" data-parent="<?php echo htmlspecialchars((string)$phieu['MaPhieu']); ?>" style="display: none;">
                                        <td colspan="10">
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
                        <?php if (count($phieuMuon) > 5): ?>
                            <div style="margin-top: 1rem; text-align: center;">
                                <button type="button" id="btnLoadMorePhieuMuon" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Xem thêm
                                </button>
                            </div>
                        <?php endif; ?>
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
                    <?php
                        // Pre-resolve room names for YeuCauMuon by parsing GhiChu line "DD:<MaDiaDiem>"
                        $ycRoomNameById = []; // [int] => TenDiaDiem
                        if (!empty($yeuCauMuon)) {
                            $roomIds = [];
                            foreach ($yeuCauMuon as $yc0) {
                                $gc = (string)($yc0['GhiChu'] ?? '');
                                if ($gc === '') continue;
                                $m = [];
                                if (preg_match('/(?:^|\r\n|\r|\n)DD:(\d+)(?:\r\n|\r|\n|$)/', $gc, $m)) {
                                    $rid = (int)$m[1];
                                    if ($rid > 0) $roomIds[] = $rid;
                                }
                            }
                            $roomIds = array_values(array_unique($roomIds));
                            if (!empty($roomIds)) {
                                $phDd = implode(',', array_fill(0, count($roomIds), '?'));
                                $ddRows = dbQuery(
                                    "SELECT MaDiaDiem, TenDiaDiem FROM `diadiem` WHERE IsDeleted = 0 AND MaDiaDiem IN ($phDd)",
                                    $roomIds
                                );
                                foreach ($ddRows as $dd) {
                                    $id = isset($dd['MaDiaDiem']) ? (int)$dd['MaDiaDiem'] : 0;
                                    if ($id <= 0) continue;
                                    $ycRoomNameById[$id] = (string)($dd['TenDiaDiem'] ?? '');
                                }
                            }
                        }
                    ?>
                    <table class="data-table" id="yeuCauMuonTable">
                        <thead>
                            <tr>
                                <th>Yêu cầu</th>
                                <th>Thiết bị</th>
                                <th>Mục đích</th>
                                <th>Phòng</th>
                                <th>Ngày gửi</th>
                                <th>Bắt đầu</th>
                                <th>Kết thúc</th>
                                <th>Trạng thái</th>
                                <th>Người duyệt</th>
                                <th>Ngày duyệt</th>
                                <th>Ghi chú</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($yeuCauMuon as $yc): ?>
                                <tr class="yc-item">
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
                                        <td>
                                            <?php
                                                $phongText = 'N/A';
                                                $gc = (string)($yc['GhiChu'] ?? '');
                                                if ($gc !== '') {
                                                    $mRoom = [];
                                                    if (preg_match('/(?:^|\r\n|\r|\n)DD:(\d+)(?:\r\n|\r|\n|$)/', $gc, $mRoom)) {
                                                        $rid = (int)$mRoom[1];
                                                        $name = $ycRoomNameById[$rid] ?? '';
                                                        $phongText = ($name !== '') ? $name : ('#' . $rid);
                                                    } elseif (preg_match('/DD_SD:([^\n\r]+)/', $gc, $mText)) {
                                                        $t = trim((string)$mText[1]);
                                                        if ($t !== '') $phongText = $t;
                                                    }
                                                }
                                                echo htmlspecialchars($phongText);
                                            ?>
                                        </td>
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
                    <?php if (count($yeuCauMuon) > 5): ?>
                        <div style="margin-top: 1rem; text-align: center;">
                            <button type="button" id="btnLoadMoreYeuCauMuon" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Xem thêm
                            </button>
                        </div>
                    <?php endif; ?>
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
                    <table class="data-table" id="datTruocTable">
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
                                <tr class="dt-item">
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
                    <?php if (count($datTruocGroups) > 5): ?>
                        <div style="margin-top: 1rem; text-align: center;">
                            <button type="button" id="btnLoadMoreDatTruoc" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Xem thêm
                            </button>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <!-- Yêu cầu bảo trì thiết bị (Admin) -->
            <?php if ($isAdmin): ?>
                <div class="dashboard-section">
                    <h2><i class="fas fa-tools"></i> Yêu cầu bảo trì thiết bị</h2>
                    <div class="section-actions">
                        <button type="button" class="btn btn-primary" onclick="openBaoTriModal('')">
                            <i class="fas fa-plus"></i> Bảo trì
                        </button>
                    </div>

                    <?php if (empty($baoTriList)): ?>
                        <div class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <p>Chưa có yêu cầu bảo trì nào</p>
                        </div>
                    <?php else: ?>
                        <table class="data-table" id="baoTriTable">
                            <thead>
                                <tr>
                                    <th>Mã bảo trì</th>
                                    <th>Thiết bị</th>
                                    <th>Loại thiết bị</th>
                                    <th>Ngày báo</th>
                                    <th>Ngày sửa</th>
                                    <th>Trạng thái</th>
                                    <th>Nhà cung cấp</th>
                                    <th>Chi phí</th>
                                    <th>Mô tả</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($baoTriList as $bt): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($bt['MaBaoTri']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($bt['MaThietBi']); ?></td>
                                        <td><?php echo htmlspecialchars($bt['TenLoai'] ?? $bt['MaLoaiThietBi'] ?? 'N/A'); ?></td>
                                        <td><?php echo formatDate($bt['NgayBao'] ?? null, true); ?></td>
                                        <td><?php echo !empty($bt['NgaySua']) ? formatDate($bt['NgaySua'], true) : 'N/A'; ?></td>
                                        <td>
                                            <span class="status-badge <?php echo (($bt['TrangThai'] ?? '') === 'Đang bảo trì') ? 'warning' : 'info'; ?>">
                                                <?php echo htmlspecialchars($bt['TrangThai'] ?? 'N/A'); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($bt['MaNhaCungCap'] ?? 'N/A'); ?></td>
                                        <td class="money"><?php echo isset($bt['ChiPhi']) && $bt['ChiPhi'] !== null ? formatMoney($bt['ChiPhi']) : 'N/A'; ?></td>
                                        <td><?php echo htmlspecialchars($bt['MoTa'] ?? 'N/A'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- 5. Thông báo -->
            <div class="dashboard-section">
                <h2><i class="fas fa-bell"></i> Thông báo 
                    <?php if ($unreadNotifications > 0): ?>
                        <span class="status-badge danger"><?php echo $unreadNotifications; ?> chưa đọc</span>
                    <?php endif; ?>
                    <button
                        type="button"
                        class="btn btn-secondary"
                        style="margin-left: 1rem;"
                        onclick="markAllThongBaoRead()"
                        <?php echo ($unreadNotifications > 0) ? '' : 'disabled'; ?>
                        title="Đánh dấu đã đọc tất cả"
                    >
                        <i class="fas fa-check-double"></i> Đánh dấu đã đọc tất cả
                    </button>
                </h2>
                <?php if (empty($thongBao)): ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <p>Bạn chưa có thông báo nào</p>
                    </div>
                <?php else: ?>
                    <div id="notificationsList">
                        <?php foreach ($thongBao as $tb): ?>
                            <div class="notification-item <?php echo !$tb['DaDoc'] ? 'unread' : ''; ?>" data-ma-thongbao="<?php echo htmlspecialchars($tb['MaThongBao']); ?>">
                                <h4><?php echo htmlspecialchars($tb['TieuDe']); ?></h4>
                                <p><?php echo nl2br(htmlspecialchars($tb['NoiDung'])); ?></p>
                                <div class="notification-date">
                                    <i class="fas fa-calendar"></i> <?php echo formatDate($tb['NgayGui']); ?>
                                    <?php if (!$tb['DaDoc']): ?>
                                        <span class="status-badge info" style="margin-left: 1rem;">Chưa đọc</span>
                                        <button type="button" class="btn btn-secondary" style="margin-left: 0.75rem;" onclick="markThongBaoRead('<?php echo htmlspecialchars($tb['MaThongBao']); ?>', this)">
                                            <i class="fas fa-check"></i> Đã đọc
                                        </button>
                                    <?php else: ?>
                                        <button type="button" class="btn btn-secondary" style="margin-left: 0.75rem;" disabled>
                                            <i class="fas fa-check"></i> Đã đọc
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if ($thongBaoHasMore): ?>
                        <div style="margin-top: 1rem; text-align: center;">
                            <button type="button" id="btnLoadMoreThongBao" class="btn btn-primary" onclick="loadMoreThongBao()">
                                <i class="fas fa-plus"></i> Xem thêm
                            </button>
                        </div>
                    <?php endif; ?>
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
                    <?php if ($isAdmin): ?>
                        <table class="data-table" id="phieuPhatAdminTable">
                            <thead>
                                <tr>
                                    <th>Mã phạt</th>
                                    <th>Tên người dùng</th>
                                    <th>Thiết bị</th>
                                    <th>Số tiền phạt</th>
                                    <th>Lý do</th>
                                    <th>Ngày thanh toán</th>
                                    <th>Ngày tạo</th>
                                    <th>Thanh toán</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($phieuPhat as $pp): ?>
                                    <tr class="pp-admin-item">
                                        <td><strong><?php echo htmlspecialchars($pp['MaPhat']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($pp['TenNguoiDung'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($pp['ThietBi'] ?? 'N/A'); ?></td>
                                        <td class="money"><?php echo formatMoney($pp['SoTien']); ?></td>
                                        <td><?php echo htmlspecialchars($pp['LyDo'] ?? 'N/A'); ?></td>
                                        <td><?php echo !empty($pp['NgayThanhToan']) ? formatDate($pp['NgayThanhToan'], true) : 'Null'; ?></td>
                                        <td><?php echo formatDate($pp['NgayTao'] ?? null, true); ?></td>
                                        <td>
                                            <?php if (!empty($pp['DaThanhToan'])): ?>
                                                <button type="button" class="btn btn-secondary" disabled>
                                                    <i class="fas fa-check"></i> Đã thanh toán
                                                </button>
                                            <?php else: ?>
                                                <form method="post" action="actions/mark_phieuphat_paid.php" onsubmit="return confirm('Xác nhận đã thanh toán phiếu phạt <?php echo htmlspecialchars($pp['MaPhat']); ?>?');" style="display:inline-block;">
                                                    <input type="hidden" name="maPhat" value="<?php echo htmlspecialchars($pp['MaPhat']); ?>">
                                                    <button type="submit" class="btn btn-success" title="Chưa thanh toán">
                                                        <i class="fas fa-times"></i> Đã thanh toán
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php if (count($phieuPhat) > 5): ?>
                            <div style="margin-top: 1rem; text-align: center;">
                                <button type="button" id="btnLoadMorePhieuPhatAdmin" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Xem thêm
                                </button>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <table class="data-table" id="phieuPhatUserTable">
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
                                    <tr class="pp-item" data-key="<?php echo htmlspecialchars((string)$pp['MaPhat']); ?>">
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
                                    <tr id="ppDetail_<?php echo $pp['MaPhat']; ?>" class="pp-detail" data-parent="<?php echo htmlspecialchars((string)$pp['MaPhat']); ?>" style="display:none;">
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
                        <?php if (count($phieuPhat) > 5): ?>
                            <div style="margin-top: 1rem; text-align: center;">
                                <button type="button" id="btnLoadMorePhieuPhatUser" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Xem thêm
                                </button>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
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

        const UNPAID_FINES_MESSAGE = 'Vui lòng thanh toán tất cả các phiếu phạt trước khi thực hiện thao tác';
        const HAS_UNPAID_FINES_SNAPSHOT = <?php echo $hasUnpaidFines ? 'true' : 'false'; ?>;

        function showUnpaidFinesPopup() {
            alert(UNPAID_FINES_MESSAGE);
        }

        function ensureNoUnpaidFinesThen(next) {
            fetch('actions/check_unpaid_fines.php', { method: 'GET', headers: { 'Accept': 'application/json' } })
                .then(r => r.json())
                .then(data => {
                    if (!data || data.success !== true) {
                        if (HAS_UNPAID_FINES_SNAPSHOT) {
                            showUnpaidFinesPopup();
                            return;
                        }
                        if (typeof next === 'function') next();
                        return;
                    }

                    if (data.hasUnpaid === 1) {
                        showUnpaidFinesPopup();
                        return;
                    }

                    if (typeof next === 'function') next();
                })
                .catch(() => {
                    if (HAS_UNPAID_FINES_SNAPSHOT) {
                        showUnpaidFinesPopup();
                        return;
                    }
                    if (typeof next === 'function') next();
                });
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
            ensureNoUnpaidFinesThen(() => {
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
            });
        }

        function closeReserveCreateModal() {
            const modal = document.getElementById('reserveCreateModal');
            if (!modal) return;
            modal.classList.remove('open');
        }

        function submitReserveCreate(e) {
            e.preventDefault();

            ensureNoUnpaidFinesThen(() => {

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
                    if (data && data.message === UNPAID_FINES_MESSAGE) {
                        showUnpaidFinesPopup();
                    } else {
                        alert('Lỗi: ' + (data && data.message ? data.message : 'Không xác định'));
                    }
                }
            })
            .catch(err => alert('Lỗi kết nối: ' + err.message));

            });
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

        // ===== Fine (Phạt) modal for admin =====
        var fineState = { maPhieu: '', soPhieu: '', thietBi: '', maYeuCau: '' };

        function openFineModalForPhieu(maPhieu, soPhieu, thietBi, maYeuCau) {
            fineState.maPhieu = String(maPhieu || '').trim();
            fineState.soPhieu = String(soPhieu || '').trim();
            fineState.thietBi = String(thietBi || '').trim();
            fineState.maYeuCau = String(maYeuCau || '').trim();
            if (!fineState.maPhieu) return;

            const modal = document.getElementById('fineModal');
            const elMa = document.getElementById('fineMaYeuCau');
            const elTb = document.getElementById('fineThietBi');
            const elMoney = document.getElementById('fineSoTien');

            if (elMa) elMa.textContent = fineState.soPhieu ? (fineState.soPhieu + ' (' + fineState.maPhieu + ')') : fineState.maPhieu;
            if (elTb) elTb.textContent = fineState.thietBi || 'N/A';
            if (elMoney) elMoney.value = '';

            if (modal) modal.classList.add('open');
        }

        function closeFineModal() {
            const modal = document.getElementById('fineModal');
            if (modal) modal.classList.remove('open');
        }

        function submitFine() {
            const reason = (document.getElementById('fineLyDo')?.value || '').trim();
            const soTien = (document.getElementById('fineSoTien')?.value || '').trim();
            if (!fineState.maPhieu) {
                alert('Thiếu mã phiếu mượn.');
                return;
            }
            if (!reason) {
                alert('Vui lòng chọn lý do phạt.');
                return;
            }
            if (!soTien || Number(soTien) <= 0) {
                alert('Vui lòng nhập số tiền phạt hợp lệ.');
                return;
            }

            const formData = new FormData();
            formData.append('maPhieu', fineState.maPhieu);
            if (fineState.maYeuCau) formData.append('maYeuCau', fineState.maYeuCau);
            formData.append('lyDo', reason);
            formData.append('soTien', soTien);

            fetch('actions/create_phieuphat.php', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    alert(data.message + (data.maPhat ? ('\nMã phạt: ' + data.maPhat) : ''));
                    closeFineModal();
                    window.location.reload();
                } else {
                    alert('Lỗi: ' + data.message);
                }
            })
            .catch(err => alert('Lỗi kết nối: ' + err.message));
        }

        function markReturned(maPhieu) {
            const mp = String(maPhieu || '').trim();
            if (!mp) return;
            if (!confirm('Xác nhận thiết bị đã trả?\n\nHệ thống sẽ cập nhật Ngày trả thực tế = hiện tại và Trạng thái = Hoàn thành (nếu không còn phiếu phạt chưa thanh toán).')) {
                return;
            }
            const formData = new FormData();
            formData.append('maPhieu', mp);
            fetch('actions/mark_phieumuon_returned.php', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    window.location.reload();
                } else {
                    alert('Lỗi: ' + data.message);
                }
            })
            .catch(err => alert('Lỗi kết nối: ' + err.message));
        }

        // ===== Notifications (4-by-4) =====
        var thongBaoState = {
            nextOffset: <?php echo (int)$thongBaoNextOffset; ?>,
            hasMore: <?php echo $thongBaoHasMore ? 'true' : 'false'; ?>,
            limit: <?php echo (int)$thongBaoLimit; ?>,
            unread: <?php echo (int)$unreadNotifications; ?>
        };

        function escapeHtml(str) {
            return String(str || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function nl2brSafe(str) {
            return escapeHtml(str).replace(/\n/g, '<br>');
        }

        function updateUnreadBadge() {
            const h2 = document.querySelector('.dashboard-section h2');
            // The notifications section is the only one with fa-bell in h2
            const notifHeader = Array.from(document.querySelectorAll('.dashboard-section h2')).find(h => h.querySelector('.fa-bell'));
            if (!notifHeader) return;

            const existing = notifHeader.querySelector('.status-badge.danger');
            if (thongBaoState.unread > 0) {
                if (existing) {
                    existing.textContent = thongBaoState.unread + ' chưa đọc';
                } else {
                    const badge = document.createElement('span');
                    badge.className = 'status-badge danger';
                    badge.textContent = thongBaoState.unread + ' chưa đọc';
                    notifHeader.insertBefore(badge, notifHeader.querySelector('button'));
                }
            } else {
                if (existing) existing.remove();
            }

            const markAllBtn = notifHeader.querySelector('button.btn.btn-secondary');
            if (markAllBtn) {
                markAllBtn.disabled = thongBaoState.unread <= 0;
            }
        }

        function createThongBaoElement(tb) {
            const wrap = document.createElement('div');
            const isUnread = !(tb && (tb.DaDoc === 1 || tb.DaDoc === '1' || tb.DaDoc === true));
            wrap.className = 'notification-item' + (isUnread ? ' unread' : '');
            wrap.dataset.maThongbao = tb.MaThongBao;

            const h4 = document.createElement('h4');
            h4.textContent = tb.TieuDe || '';

            const p = document.createElement('p');
            p.innerHTML = nl2brSafe(tb.NoiDung || '');

            const date = document.createElement('div');
            date.className = 'notification-date';
            const dateText = tb.NgayGuiFormatted || tb.NgayGui || '';
            date.innerHTML = '<i class="fas fa-calendar"></i> ' + escapeHtml(dateText);

            if (isUnread) {
                const badge = document.createElement('span');
                badge.className = 'status-badge info';
                badge.style.marginLeft = '1rem';
                badge.textContent = 'Chưa đọc';
                date.appendChild(badge);

                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'btn btn-secondary';
                btn.style.marginLeft = '0.75rem';
                btn.innerHTML = '<i class="fas fa-check"></i> Đã đọc';
                btn.addEventListener('click', function() { markThongBaoRead(tb.MaThongBao, btn); });
                date.appendChild(btn);
            } else {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'btn btn-secondary';
                btn.style.marginLeft = '0.75rem';
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-check"></i> Đã đọc';
                date.appendChild(btn);
            }

            wrap.appendChild(h4);
            wrap.appendChild(p);
            wrap.appendChild(date);
            return wrap;
        }

        function loadMoreThongBao() {
            if (!thongBaoState.hasMore) return;
            const btn = document.getElementById('btnLoadMoreThongBao');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang tải...';
            }

            const url = 'actions/get_thongbao.php?offset=' + encodeURIComponent(thongBaoState.nextOffset) + '&limit=' + encodeURIComponent(thongBaoState.limit);
            fetch(url, {
                method: 'GET',
                headers: { 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(data => {
                if (!data || !data.success) {
                    throw new Error((data && data.message) ? data.message : 'Không tải được thông báo');
                }
                const list = document.getElementById('notificationsList');
                (data.items || []).forEach(function(item) {
                    if (!list) return;
                    list.appendChild(createThongBaoElement(item));
                });
                thongBaoState.nextOffset = Number.isFinite(data.nextOffset) ? data.nextOffset : thongBaoState.nextOffset;
                thongBaoState.hasMore = !!data.hasMore;

                if (btn) {
                    if (thongBaoState.hasMore) {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-plus"></i> Xem thêm';
                    } else {
                        btn.remove();
                    }
                }
            })
            .catch(err => {
                alert('Lỗi: ' + err.message);
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-plus"></i> Xem thêm';
                }
            });
        }

        function markThongBaoRead(maThongBao, btnEl) {
            const mtb = String(maThongBao || '').trim();
            if (!mtb) return;
            if (btnEl) btnEl.disabled = true;

            const formData = new FormData();
            formData.append('maThongBao', mtb);

            fetch('actions/mark_thongbao_read.php', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (!data || !data.success) {
                    throw new Error((data && data.message) ? data.message : 'Không thể cập nhật');
                }
                const item = document.querySelector('.notification-item[data-ma-thongbao="' + mtb.replace(/"/g, '\\"') + '"]');
                if (item) {
                    item.classList.remove('unread');
                    const badge = item.querySelector('.status-badge.info');
                    if (badge) badge.remove();
                }
                if (thongBaoState.unread > 0) thongBaoState.unread -= 1;
                updateUnreadBadge();
            })
            .catch(err => {
                alert('Lỗi: ' + err.message);
                if (btnEl) btnEl.disabled = false;
            });
        }

        function markAllThongBaoRead() {
            if (thongBaoState.unread <= 0) return;
            if (!confirm('Bạn có chắc chắn muốn đánh dấu đã đọc tất cả thông báo không?')) {
                return;
            }

            fetch('actions/mark_all_thongbao_read.php', {
                method: 'POST'
            })
            .then(r => r.json())
            .then(data => {
                if (!data || !data.success) {
                    throw new Error((data && data.message) ? data.message : 'Không thể cập nhật');
                }
                // Update current list UI
                document.querySelectorAll('#notificationsList .notification-item').forEach(function(item) {
                    item.classList.remove('unread');
                    const badge = item.querySelector('.status-badge.info');
                    if (badge) badge.remove();
                    const btn = item.querySelector('button.btn.btn-secondary');
                    if (btn) btn.disabled = true;
                });
                thongBaoState.unread = 0;
                updateUnreadBadge();
            })
            .catch(err => alert('Lỗi: ' + err.message));
        }

        // ===== Maintenance (Bảo trì) modal for admin =====
        function openBaoTriModal(maThietBi) {
            const modal = document.getElementById('baoTriModal');
            if (!modal) return;

            const selectTb = document.getElementById('baoTriMaThietBi');
            const supplier = document.getElementById('baoTriNhaCungCap');
            const moTa = document.getElementById('baoTriMoTa');

            const pre = String(maThietBi || '').trim();
            if (selectTb) {
                if (pre) {
                    selectTb.value = pre;
                } else {
                    selectTb.value = '';
                }
            }
            if (supplier) supplier.value = '';
            if (moTa) moTa.value = '';

            modal.classList.add('open');
        }

        function closeBaoTriModal() {
            const modal = document.getElementById('baoTriModal');
            if (modal) modal.classList.remove('open');
        }

        function submitBaoTri() {
            const maThietBi = (document.getElementById('baoTriMaThietBi')?.value || '').trim();
            const maNhaCungCap = (document.getElementById('baoTriNhaCungCap')?.value || '').trim();
            const moTa = (document.getElementById('baoTriMoTa')?.value || '').trim();

            if (!maThietBi || !maNhaCungCap || !moTa) {
                alert('Vui lòng chọn thiết bị, nhà cung cấp và nhập mô tả lỗi.');
                return;
            }

            const formData = new FormData();
            formData.append('maThietBi', maThietBi);
            formData.append('maNhaCungCap', maNhaCungCap);
            formData.append('moTa', moTa);

            fetch('actions/create_baotri.php', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data && data.success) {
                    alert(data.message + (data.maBaoTri ? ('\nMã bảo trì: ' + data.maBaoTri) : ''));
                    closeBaoTriModal();
                    window.location.reload();
                } else {
                    alert('Lỗi: ' + ((data && data.message) ? data.message : 'Không thể tạo phiếu bảo trì'));
                }
            })
            .catch(err => alert('Lỗi kết nối: ' + err.message));
        }

        // ===== Dashboard tables: show 5 latest + load more 5 =====
        function cssEscapeValue(val) {
            if (window.CSS && typeof window.CSS.escape === 'function') {
                return window.CSS.escape(String(val));
            }
            // Minimal escape fallback for attribute selectors
            return String(val).replace(/[^a-zA-Z0-9_\-]/g, function(ch) {
                return '\\' + ch;
            });
        }

        function initLoadMoreSimple(opts) {
            opts = opts || {};
            const tableId = opts.tableId;
            const rowSelector = opts.rowSelector;
            const buttonId = opts.buttonId;
            const chunk = Number.isFinite(opts.chunk) ? opts.chunk : 5;

            const table = document.getElementById(tableId);
            const btn = document.getElementById(buttonId);
            if (!table) return;

            const rows = Array.from(table.querySelectorAll(rowSelector));
            let visible = chunk;

            function apply() {
                rows.forEach(function(r, idx) {
                    r.style.display = (idx < visible) ? 'table-row' : 'none';
                });
                if (btn) {
                    btn.style.display = (rows.length > visible) ? 'inline-block' : 'none';
                }
            }

            if (btn) {
                btn.addEventListener('click', function() {
                    visible = Math.min(rows.length, visible + chunk);
                    apply();
                });
            }

            apply();
        }

        function initLoadMoreWithDetail(opts) {
            opts = opts || {};
            const tableId = opts.tableId;
            const itemSelector = opts.itemSelector;
            const detailSelector = opts.detailSelector;
            const buttonId = opts.buttonId;
            const chunk = Number.isFinite(opts.chunk) ? opts.chunk : 5;
            const keyAttr = opts.keyAttr || 'data-key';
            const detailKeyAttr = opts.detailKeyAttr || 'data-parent';

            const table = document.getElementById(tableId);
            const btn = document.getElementById(buttonId);
            if (!table) return;

            const items = Array.from(table.querySelectorAll(itemSelector));
            let visible = chunk;

            function findDetailRow(key) {
                if (!key) return null;
                const esc = cssEscapeValue(key);
                try {
                    return table.querySelector(detailSelector + '[' + detailKeyAttr + '="' + esc + '"]');
                } catch (e) {
                    // Fallback: linear search
                    const all = Array.from(table.querySelectorAll(detailSelector));
                    return all.find(function(r) { return String(r.getAttribute(detailKeyAttr) || '') === String(key); }) || null;
                }
            }

            function apply() {
                items.forEach(function(r, idx) {
                    const key = String(r.getAttribute(keyAttr) || '');
                    const detail = findDetailRow(key);
                    if (idx < visible) {
                        r.style.display = 'table-row';
                        if (detail) detail.style.display = 'none';
                    } else {
                        r.style.display = 'none';
                        if (detail) detail.style.display = 'none';
                    }
                });
                if (btn) {
                    btn.style.display = (items.length > visible) ? 'inline-block' : 'none';
                }
            }

            if (btn) {
                btn.addEventListener('click', function() {
                    visible = Math.min(items.length, visible + chunk);
                    apply();
                });
            }

            apply();
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

            // Maintenance modal close on outside click
            var baoTriModal = document.getElementById('baoTriModal');
            if (baoTriModal) {
                baoTriModal.addEventListener('click', function(e) {
                    if (e.target === baoTriModal) {
                        closeBaoTriModal();
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

            // Init 5-by-5 for dashboard tables
            initLoadMoreWithDetail({
                tableId: 'phieuMuonTable',
                itemSelector: 'tr.pm-item',
                detailSelector: 'tr.pm-detail',
                buttonId: 'btnLoadMorePhieuMuon',
                chunk: 5,
                keyAttr: 'data-key',
                detailKeyAttr: 'data-parent'
            });
            initLoadMoreSimple({ tableId: 'yeuCauMuonTable', rowSelector: 'tr.yc-item', buttonId: 'btnLoadMoreYeuCauMuon', chunk: 5 });
            initLoadMoreSimple({ tableId: 'datTruocTable', rowSelector: 'tr.dt-item', buttonId: 'btnLoadMoreDatTruoc', chunk: 5 });
            initLoadMoreSimple({ tableId: 'phieuPhatAdminTable', rowSelector: 'tr.pp-admin-item', buttonId: 'btnLoadMorePhieuPhatAdmin', chunk: 5 });
            initLoadMoreWithDetail({
                tableId: 'phieuPhatUserTable',
                itemSelector: 'tr.pp-item',
                detailSelector: 'tr.pp-detail',
                buttonId: 'btnLoadMorePhieuPhatUser',
                chunk: 5,
                keyAttr: 'data-key',
                detailKeyAttr: 'data-parent'
            });
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
    <!-- Fine modal (admin) -->
    <div id="fineModal" class="modal" aria-hidden="true">
        <div class="modal-content" role="dialog" aria-modal="true" aria-labelledby="fineModalTitle">
            <h3 id="fineModalTitle">Lập phiếu phạt</h3>
            <p style="margin:0 0 1rem 0;">
                Phiếu mượn: <strong id="fineMaYeuCau"></strong><br>
                Thiết bị: <span id="fineThietBi"></span>
            </p>

            <div class="reserve-form">
                <div class="field">
                    <label for="fineLyDo">Lý do phạt *</label>
                    <select id="fineLyDo" required>
                        <option value="">-- Chọn lý do --</option>
                        <option value="Trả thiết bị quá hạn">Trả thiết bị quá hạn</option>
                        <option value="Thiết bị bị hư hỏng trong quá trình mượn">Thiết bị bị hư hỏng trong quá trình mượn</option>
                        <option value="Tự ý sửa chữa hoặc can thiệp thiết bị">Tự ý sửa chữa hoặc can thiệp thiết bị</option>
                    </select>
                </div>
                <div class="field">
                    <label for="fineSoTien">Tiền phạt (VNĐ) *</label>
                    <input id="fineSoTien" type="number" min="1" step="1000" placeholder="Nhập số tiền phạt" required>
                </div>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeFineModal()">Hủy</button>
                <button type="button" class="btn btn-danger" onclick="submitFine()">OK</button>
            </div>
        </div>
    </div>
    <!-- Maintenance modal (admin) -->
    <div id="baoTriModal" class="modal" aria-hidden="true">
        <div class="modal-content" role="dialog" aria-modal="true" aria-labelledby="baoTriModalTitle">
            <h3 id="baoTriModalTitle">Tạo phiếu bảo trì</h3>

            <p style="margin:0 0 1rem 0;">
                Ngày báo: <strong>Hôm nay</strong> &nbsp;|&nbsp;
                Trạng thái: <strong>Đang bảo trì</strong>
            </p>

            <div class="reserve-form">
                <div class="field">
                    <label for="baoTriMaThietBi">Thiết bị *</label>
                    <select id="baoTriMaThietBi" required>
                        <option value="">-- Chọn thiết bị --</option>
                        <?php foreach ($thietBiKhaDungAll as $tb): ?>
                            <option value="<?php echo htmlspecialchars($tb['MaThietBi']); ?>">
                                <?php echo htmlspecialchars($tb['MaThietBi'] . ' - ' . ($tb['TenLoai'] ?? $tb['MaLoaiThietBi'])); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="field">
                    <label for="baoTriNhaCungCap">Nhà cung cấp *</label>
                    <select id="baoTriNhaCungCap" required>
                        <option value="">-- Chọn nhà cung cấp --</option>
                        <option value="Công ty Ngọc Diệp">Công ty Ngọc Diệp</option>
                        <option value="Công ty Tương Lai">Công ty Tương Lai</option>
                        <option value="Phú Diễn">Phú Diễn</option>
                        <option value="An Thái">An Thái</option>
                    </select>
                </div>

                <div class="field">
                    <label for="baoTriMoTa">Mô tả lỗi cần bảo trì *</label>
                    <textarea id="baoTriMoTa" rows="4" placeholder="Ví dụ: Màn hình không lên, pin chai, bàn phím kẹt..." required></textarea>
                </div>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeBaoTriModal()">Hủy</button>
                <button type="button" class="btn btn-primary" onclick="submitBaoTri()">Tạo phiếu</button>
            </div>
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

