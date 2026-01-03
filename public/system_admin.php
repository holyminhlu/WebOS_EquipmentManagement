<?php
/**
 * System Admin Panel (MaVaiTro = 1101)
 * - Quản lý người dùng (GV/SV)
 * - Quản lý admin
 * - Quản lý thiết bị
 * - Duyệt bảo trì / đánh dấu hỏng
 * - Báo cáo & thống kê (placeholder)
 */

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../includes/user.php';
require_once __DIR__ . '/../includes/db.php';

$user = getUserInfo($_SESSION['user_id']);
if (!$user || !isset($user['MaVaiTro']) || (int)$user['MaVaiTro'] !== 1101) {
    header('Location: dashboard.php');
    exit;
}

// Data for selects
$khoaList = getKhoaPhongBan();
$vaiTroList = getVaiTro();

// Users (role 2/3)
$gvSvList = dbQuery(
    "SELECT nd.*, vt.TenVaiTro, kpb.TenKhoa
     FROM `nguoidung` nd
     LEFT JOIN `vaitro` vt ON nd.MaVaiTro = vt.MaVaiTro
     LEFT JOIN `khoaphongban` kpb ON nd.MaKhoa = kpb.MaKhoa
     WHERE nd.IsDeleted = 0
       AND nd.MaVaiTro IN (2, 3)
     ORDER BY nd.NgayTao DESC"
);

// Admin (role 1)
$adminList = dbQuery(
    "SELECT nd.*, vt.TenVaiTro
     FROM `nguoidung` nd
     LEFT JOIN `vaitro` vt ON nd.MaVaiTro = vt.MaVaiTro
     WHERE nd.IsDeleted = 0
       AND nd.MaVaiTro = 1
     ORDER BY nd.NgayTao DESC"
);

// Devices
$deviceList = dbQuery(
    "SELECT tb.*, ltb.TenLoai, dd.TenDiaDiem, tttb.TenTrangThai
     FROM `thietbi` tb
     LEFT JOIN `loaithietbi` ltb ON tb.MaLoaiThietBi = ltb.MaLoaiThietBi
     LEFT JOIN `diadiem` dd ON tb.MaDiaDiem = dd.MaDiaDiem
     LEFT JOIN `trangthaithietbi` tttb ON tb.MaTrangThai = tttb.MaTrangThai
     WHERE tb.IsDeleted = 0
     ORDER BY tb.MaThietBi DESC"
);

$loaiThietBiList = dbQuery("SELECT * FROM `loaithietbi` WHERE IsDeleted = 0 ORDER BY TenLoai ASC");
$diaDiemList = dbQuery("SELECT * FROM `diadiem` WHERE IsDeleted = 0 ORDER BY TenDiaDiem ASC");
$trangThaiTbList = dbQuery("SELECT * FROM `trangthaithietbi` ORDER BY MaTrangThai ASC");

// Maintenance tickets pending
$baoTriPending = dbQuery(
    "SELECT bt.*, tb.MaLoaiThietBi, ltb.TenLoai, tb.MaTrangThai, tttb.TenTrangThai AS TenTrangThaiThietBi
     FROM `baotri` bt
     INNER JOIN `thietbi` tb ON bt.MaThietBi = tb.MaThietBi
     LEFT JOIN `loaithietbi` ltb ON tb.MaLoaiThietBi = ltb.MaLoaiThietBi
     LEFT JOIN `trangthaithietbi` tttb ON tb.MaTrangThai = tttb.MaTrangThai
     WHERE bt.IsDeleted = 0
       AND bt.TrangThai = 'Đang bảo trì'
     ORDER BY bt.NgayBao DESC"
);

function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Quản trị hệ thống</title>
    <link rel="stylesheet" href="css/styleDashboard.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="css/styleSystemAdmin.css?v=<?php echo time(); ?>">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
</head>
<body>
    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1><i class="fas fa-shield-halved"></i> Quản trị hệ thống</h1>
            <p>Chỉ dành cho tài khoản Quản trị hệ thống (MaVaiTro = 1101)</p>
        </div>

        <div class="sysadmin-topbar">
            <div class="sysadmin-user">
                <div class="sysadmin-user-name"><?php echo h($user['HoTen'] ?? ''); ?></div>
                <div class="sysadmin-user-meta">Mã: <?php echo h($user['MaNguoiDung'] ?? ''); ?> | Vai trò: <?php echo h($user['TenVaiTro'] ?? ''); ?></div>
            </div>
            <div class="sysadmin-actions">
                <a class="btn btn-secondary" href="logout.php"><i class="fas fa-right-from-bracket"></i> Đăng xuất</a>
            </div>
        </div>

        <div class="sysadmin-tabs">
            <button class="sysadmin-tab active" data-tab="tab-users"><i class="fas fa-user-graduate"></i> Người dùng (GV/SV)</button>
            <button class="sysadmin-tab" data-tab="tab-admins"><i class="fas fa-user-shield"></i> Admin</button>
            <button class="sysadmin-tab" data-tab="tab-devices"><i class="fas fa-boxes-stacked"></i> Thiết bị</button>
            <button class="sysadmin-tab" data-tab="tab-maint"><i class="fas fa-screwdriver-wrench"></i> Bảo trì</button>
            <button class="sysadmin-tab" data-tab="tab-reports"><i class="fas fa-chart-column"></i> Báo cáo</button>
        </div>

        <!-- 1) Users GV/SV -->
        <div class="dashboard-section sysadmin-pane" id="tab-users">
            <h2><i class="fas fa-user-graduate"></i> Quản lý người dùng (Giảng viên/Sinh viên)</h2>
            <div class="section-actions">
                <button class="btn btn-primary" type="button" onclick="openUserModal('user')"><i class="fas fa-plus"></i> Thêm người dùng</button>
            </div>

            <table class="data-table">
                <thead>
                    <tr>
                        <th>Mã</th>
                        <th>Họ tên</th>
                        <th>Tên đăng nhập</th>
                        <th>Email</th>
                        <th>Vai trò</th>
                        <th>Khoa</th>
                        <th>Hoạt động</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($gvSvList as $row): ?>
                    <tr>
                        <td><strong><?php echo h($row['MaNguoiDung'] ?? ''); ?></strong></td>
                        <td><?php echo h($row['HoTen'] ?? ''); ?></td>
                        <td><?php echo h($row['TenDangNhap'] ?? ''); ?></td>
                        <td><?php echo h($row['Email'] ?? ''); ?></td>
                        <td><?php echo h($row['TenVaiTro'] ?? $row['MaVaiTro'] ?? ''); ?></td>
                        <td><?php echo h($row['TenKhoa'] ?? ''); ?></td>
                        <td>
                            <span class="status-badge <?php echo ((int)($row['HoatDong'] ?? 0) === 1) ? 'success' : 'danger'; ?>">
                                <?php echo ((int)($row['HoatDong'] ?? 0) === 1) ? 'Đang hoạt động' : 'Đã khóa'; ?>
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-primary" type="button" onclick='openUserModal("user", <?php echo json_encode($row, JSON_UNESCAPED_UNICODE); ?>)'><i class="fas fa-pen"></i> Sửa</button>
                            <?php if ((int)($row['HoatDong'] ?? 0) === 1): ?>
                                <button class="btn btn-danger" type="button" onclick='setUserActive(<?php echo json_encode((string)$row['MaNguoiDung']); ?>, 0)'><i class="fas fa-lock"></i> Khóa</button>
                            <?php else: ?>
                                <button class="btn btn-success" type="button" onclick='setUserActive(<?php echo json_encode((string)$row['MaNguoiDung']); ?>, 1)'><i class="fas fa-unlock"></i> Mở</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- 2) Admins -->
        <div class="dashboard-section sysadmin-pane" id="tab-admins" style="display:none;">
            <h2><i class="fas fa-user-shield"></i> Quản lý người dùng (Admin)</h2>
            <div class="section-actions">
                <button class="btn btn-primary" type="button" onclick="openUserModal('admin')"><i class="fas fa-plus"></i> Thêm admin</button>
            </div>

            <table class="data-table">
                <thead>
                    <tr>
                        <th>Mã</th>
                        <th>Họ tên</th>
                        <th>Tên đăng nhập</th>
                        <th>Email</th>
                        <th>Hoạt động</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($adminList as $row): ?>
                    <tr>
                        <td><strong><?php echo h($row['MaNguoiDung'] ?? ''); ?></strong></td>
                        <td><?php echo h($row['HoTen'] ?? ''); ?></td>
                        <td><?php echo h($row['TenDangNhap'] ?? ''); ?></td>
                        <td><?php echo h($row['Email'] ?? ''); ?></td>
                        <td>
                            <span class="status-badge <?php echo ((int)($row['HoatDong'] ?? 0) === 1) ? 'success' : 'danger'; ?>">
                                <?php echo ((int)($row['HoatDong'] ?? 0) === 1) ? 'Đang hoạt động' : 'Đã khóa'; ?>
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-primary" type="button" onclick='openUserModal("admin", <?php echo json_encode($row, JSON_UNESCAPED_UNICODE); ?>)'><i class="fas fa-pen"></i> Sửa</button>
                            <?php if ((int)($row['HoatDong'] ?? 0) === 1): ?>
                                <button class="btn btn-danger" type="button" onclick='setUserActive(<?php echo json_encode((string)$row['MaNguoiDung']); ?>, 0)'><i class="fas fa-lock"></i> Khóa</button>
                            <?php else: ?>
                                <button class="btn btn-success" type="button" onclick='setUserActive(<?php echo json_encode((string)$row['MaNguoiDung']); ?>, 1)'><i class="fas fa-unlock"></i> Mở</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- 3) Devices -->
        <div class="dashboard-section sysadmin-pane" id="tab-devices" style="display:none;">
            <h2><i class="fas fa-boxes-stacked"></i> Quản lý thiết bị</h2>
            <div class="section-actions">
                <button class="btn btn-primary" type="button" onclick="openDeviceModal()"><i class="fas fa-plus"></i> Thêm thiết bị</button>
            </div>

            <table class="data-table">
                <thead>
                    <tr>
                        <th>Mã</th>
                        <th>Loại</th>
                        <th>Mã tài sản</th>
                        <th>Serial</th>
                        <th>Địa điểm</th>
                        <th>Trạng thái</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($deviceList as $row): ?>
                    <tr>
                        <td><strong><?php echo h($row['MaThietBi'] ?? ''); ?></strong></td>
                        <td><?php echo h($row['TenLoai'] ?? $row['MaLoaiThietBi'] ?? ''); ?></td>
                        <td><?php echo h($row['MaTaiSan'] ?? ''); ?></td>
                        <td><?php echo h($row['SoSerial'] ?? ''); ?></td>
                        <td><?php echo h($row['TenDiaDiem'] ?? $row['MaDiaDiem'] ?? ''); ?></td>
                        <td><?php echo h($row['TenTrangThai'] ?? $row['MaTrangThai'] ?? ''); ?></td>
                        <td>
                            <button class="btn btn-primary" type="button" onclick='openDeviceModal(<?php echo json_encode($row, JSON_UNESCAPED_UNICODE); ?>)'><i class="fas fa-pen"></i> Sửa</button>
                            <button class="btn btn-danger" type="button" onclick='deleteDevice(<?php echo json_encode((string)$row['MaThietBi']); ?>)'><i class="fas fa-trash"></i> Xóa</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- 4) Maintenance -->
        <div class="dashboard-section sysadmin-pane" id="tab-maint" style="display:none;">
            <h2><i class="fas fa-screwdriver-wrench"></i> Bảo trì thiết bị</h2>
            <?php if (empty($baoTriPending)): ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <p>Không có phiếu bảo trì đang chờ xử lý</p>
                </div>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Mã BT</th>
                            <th>Thiết bị</th>
                            <th>Loại</th>
                            <th>Ngày báo</th>
                            <th>Nhà cung cấp</th>
                            <th>Mô tả</th>
                            <th>Trạng thái TB</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($baoTriPending as $bt): ?>
                        <tr>
                            <td><strong><?php echo h($bt['MaBaoTri'] ?? ''); ?></strong></td>
                            <td><?php echo h($bt['MaThietBi'] ?? ''); ?></td>
                            <td><?php echo h($bt['TenLoai'] ?? $bt['MaLoaiThietBi'] ?? ''); ?></td>
                            <td><?php echo h($bt['NgayBao'] ?? ''); ?></td>
                            <td><?php echo h($bt['MaNhaCungCap'] ?? ''); ?></td>
                            <td><?php echo h($bt['MoTa'] ?? ''); ?></td>
                            <td><?php echo h($bt['TenTrangThaiThietBi'] ?? $bt['MaTrangThai'] ?? ''); ?></td>
                            <td>
                                <button class="btn btn-success" type="button" onclick='openMaintApproveModal(<?php echo json_encode((string)$bt['MaBaoTri']); ?>, <?php echo json_encode((string)$bt['MaThietBi']); ?>)'><i class="fas fa-check"></i> Duyệt</button>
                                <button class="btn btn-danger" type="button" onclick='markMaintBroken(<?php echo json_encode((string)$bt['MaBaoTri']); ?>, <?php echo json_encode((string)$bt['MaThietBi']); ?>)'><i class="fas fa-triangle-exclamation"></i> Hỏng</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- 5) Reports -->
        <div class="dashboard-section sysadmin-pane" id="tab-reports" style="display:none;">
            <h2><i class="fas fa-chart-column"></i> Báo cáo và thống kê</h2>

            <h3 style="margin-top: 0.75rem;"><i class="fas fa-chart-line"></i> Thống kê</h3>
            <form class="reserve-form" onsubmit="loadMonthlyStats(event)">
                <div class="reserve-two">
                    <div class="field">
                        <label>Chọn tháng</label>
                        <input type="month" id="statsMonth" value="<?php echo date('Y-m'); ?>" required>
                    </div>
                    <div class="field" style="display:flex;align-items:flex-end;gap:0.75rem;">
                        <button type="submit" class="btn btn-primary" id="btnStatsLoad"><i class="fas fa-magnifying-glass"></i> Xem thống kê</button>
                    </div>
                </div>
            </form>

            <div class="sysadmin-chart-card">
                <canvas id="statsChart" height="110"></canvas>
            </div>
            <div class="help-text">Biểu đồ thống kê số lần mượn (Phiếu mượn), đặt trước và phiếu phạt theo từng ngày trong tháng.</div>
        </div>

    </div>

    <!-- User/Admin Modal -->
    <div class="modal" id="userModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="userModalTitle">Tài khoản</h3>
                <button type="button" class="btn btn-secondary" onclick="closeUserModal()">Đóng</button>
            </div>

            <form class="reserve-form" id="userForm" onsubmit="submitUser(event)">
                <input type="hidden" id="uType" value="user">
                <input type="hidden" id="uMaNguoiDung" value="">

                <div class="reserve-two">
                    <div class="field">
                        <label>Họ tên *</label>
                        <input type="text" id="uHoTen" required>
                    </div>
                    <div class="field">
                        <label>Tên đăng nhập *</label>
                        <input type="text" id="uTenDangNhap" required>
                    </div>
                </div>

                <div class="reserve-two">
                    <div class="field">
                        <label>Email</label>
                        <input type="email" id="uEmail">
                    </div>
                    <div class="field">
                        <label>Số điện thoại</label>
                        <input type="text" id="uSoDienThoai">
                    </div>
                </div>

                <div class="reserve-two" id="uRoleRow">
                    <div class="field">
                        <label>Vai trò *</label>
                        <select id="uMaVaiTro">
                            <option value="2">Giảng viên (2)</option>
                            <option value="3">Sinh viên (3)</option>
                            <option value="1">Admin (1)</option>
                        </select>
                    </div>
                    <div class="field">
                        <label>Khoa</label>
                        <select id="uMaKhoa">
                            <option value="">-- Chọn khoa --</option>
                            <?php foreach ($khoaList as $k): ?>
                                <option value="<?php echo (int)($k['MaKhoa'] ?? 0); ?>"><?php echo h($k['TenKhoa'] ?? ''); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="reserve-two" id="uStudentRow">
                    <div class="field">
                        <label>Mã sinh viên</label>
                        <input type="text" id="uMaSinhVien">
                    </div>
                    <div class="field">
                        <label>Mật khẩu <?php echo '(bỏ trống nếu không đổi)'; ?></label>
                        <input type="password" id="uMatKhau" placeholder="Nhập để tạo/đổi mật khẩu">
                    </div>
                </div>

                <div class="field">
                    <label>Trạng thái hoạt động</label>
                    <select id="uHoatDong">
                        <option value="1">Đang hoạt động</option>
                        <option value="0">Bị khóa</option>
                    </select>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeUserModal()">Hủy</button>
                    <button type="submit" class="btn btn-primary">Lưu</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Device Modal -->
    <div class="modal" id="deviceModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="deviceModalTitle">Thiết bị</h3>
                <button type="button" class="btn btn-secondary" onclick="closeDeviceModal()">Đóng</button>
            </div>

            <form class="reserve-form" id="deviceForm" onsubmit="submitDevice(event)">
                <input type="hidden" id="dMode" value="create">

                <div class="reserve-two">
                    <div class="field">
                        <label>Mã thiết bị *</label>
                        <input type="text" id="dMaThietBi" required>
                    </div>
                    <div class="field">
                        <label>Loại thiết bị *</label>
                        <select id="dMaLoaiThietBi" required>
                            <option value="">-- Chọn loại --</option>
                            <?php foreach ($loaiThietBiList as $ltb): ?>
                                <option value="<?php echo h($ltb['MaLoaiThietBi'] ?? ''); ?>"><?php echo h(($ltb['MaLoaiThietBi'] ?? '') . ' - ' . ($ltb['TenLoai'] ?? '')); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="reserve-two">
                    <div class="field">
                        <label>Mã tài sản</label>
                        <input type="text" id="dMaTaiSan">
                    </div>
                    <div class="field">
                        <label>Số serial</label>
                        <input type="text" id="dSoSerial">
                    </div>
                </div>

                <div class="reserve-two">
                    <div class="field">
                        <label>Địa điểm *</label>
                        <select id="dMaDiaDiem" required>
                            <option value="">-- Chọn địa điểm --</option>
                            <?php foreach ($diaDiemList as $dd): ?>
                                <option value="<?php echo (int)($dd['MaDiaDiem'] ?? 0); ?>"><?php echo h($dd['TenDiaDiem'] ?? ''); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field">
                        <label>Trạng thái *</label>
                        <select id="dMaTrangThai" required>
                            <?php foreach ($trangThaiTbList as $tt): ?>
                                <option value="<?php echo (int)($tt['MaTrangThai'] ?? 0); ?>"><?php echo h(($tt['MaTrangThai'] ?? '') . ' - ' . ($tt['TenTrangThai'] ?? '')); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="reserve-two">
                    <div class="field">
                        <label>Ngày mua</label>
                        <input type="date" id="dNgayMua">
                    </div>
                    <div class="field">
                        <label>Hạn bảo hành</label>
                        <input type="date" id="dHanBaoHanh">
                    </div>
                </div>

                <div class="field">
                    <label>Giá mua</label>
                    <input type="number" id="dGiaMua" step="0.01" min="0">
                </div>

                <div class="field">
                    <label>Ghi chú</label>
                    <textarea id="dGhiChu" rows="3"></textarea>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeDeviceModal()">Hủy</button>
                    <button type="submit" class="btn btn-primary">Lưu</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Maintenance Approve Modal -->
    <div class="modal" id="maintModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Duyệt bảo trì</h3>
                <button type="button" class="btn btn-secondary" onclick="closeMaintModal()">Đóng</button>
            </div>

            <form class="reserve-form" id="maintForm" onsubmit="submitMaintApprove(event)">
                <input type="hidden" id="mMaBaoTri" value="">
                <input type="hidden" id="mMaThietBi" value="">

                <div class="field">
                    <label>Chi phí bảo trì</label>
                    <input type="number" id="mChiPhi" step="0.01" min="0" value="0">
                    <small class="help-text">Nhập 0 nếu miễn phí</small>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeMaintModal()">Hủy</button>
                    <button type="submit" class="btn btn-success">Duyệt</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Tabs
        document.querySelectorAll('.sysadmin-tab').forEach(btn => {
            btn.addEventListener('click', function() {
                const tabId = this.getAttribute('data-tab');
                document.querySelectorAll('.sysadmin-tab').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                document.querySelectorAll('.sysadmin-pane').forEach(p => p.style.display = 'none');
                const pane = document.getElementById(tabId);
                if (pane) pane.style.display = 'block';

                if (tabId === 'tab-reports') {
                    loadMonthlyStats();
                }
            });
        });

        function jsonOrError(r) {
            return r.json().catch(() => ({ success: false, message: 'Phản hồi không hợp lệ' }));
        }

        // === Reports: Monthly statistics ===
        let statsChart = null;

        function getCssVar(name, fallback) {
            const v = getComputedStyle(document.documentElement).getPropertyValue(name);
            const s = (v || '').trim();
            return s || fallback;
        }

        function loadMonthlyStats(e) {
            if (e && e.preventDefault) e.preventDefault();

            const monthEl = document.getElementById('statsMonth');
            const month = monthEl ? monthEl.value : '';
            if (!month) return;

            const btn = document.getElementById('btnStatsLoad');
            if (btn) btn.disabled = true;

            const fd = new FormData();
            fd.append('month', month);

            fetch('actions/system_admin/stats_monthly.php', { method: 'POST', body: fd })
                .then(jsonOrError)
                .then(data => {
                    if (!data.success) {
                        alert('Lỗi: ' + (data.message || 'Không xác định'));
                        return;
                    }

                    const labels = data.labels || [];
                    const series = data.series || {};

                    const cPrimary = getCssVar('--primary-color', '#2c5aa0');
                    const cSuccess = getCssVar('--success-color', '#2e7d32');
                    const cError = getCssVar('--error-color', '#c62828');

                    const ctx = document.getElementById('statsChart');
                    if (!ctx) return;

                    if (statsChart) {
                        statsChart.destroy();
                        statsChart = null;
                    }

                    statsChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels,
                            datasets: [
                                {
                                    label: 'Mượn',
                                    data: series.borrow || [],
                                    borderColor: cPrimary,
                                    backgroundColor: cPrimary,
                                    tension: 0.25,
                                    pointRadius: 3,
                                    pointHoverRadius: 5,
                                },
                                {
                                    label: 'Đặt trước',
                                    data: series.reserve || [],
                                    borderColor: cSuccess,
                                    backgroundColor: cSuccess,
                                    tension: 0.25,
                                    pointRadius: 3,
                                    pointHoverRadius: 5,
                                },
                                {
                                    label: 'Phiếu phạt',
                                    data: series.fine || [],
                                    borderColor: cError,
                                    backgroundColor: cError,
                                    tension: 0.25,
                                    pointRadius: 3,
                                    pointHoverRadius: 5,
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { position: 'top' },
                                tooltip: { mode: 'index', intersect: false }
                            },
                            interaction: { mode: 'index', intersect: false },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: { precision: 0 }
                                }
                            }
                        }
                    });
                })
                .catch(err => alert('Lỗi kết nối: ' + err.message))
                .finally(() => { if (btn) btn.disabled = false; });
        }

        // === User/Admin CRUD ===
        function openUserModal(type, row) {
            const modal = document.getElementById('userModal');
            if (!modal) return;

            document.getElementById('uType').value = type === 'admin' ? 'admin' : 'user';
            document.getElementById('userModalTitle').textContent = (type === 'admin')
                ? (row ? 'Sửa tài khoản Admin' : 'Thêm tài khoản Admin')
                : (row ? 'Sửa tài khoản người dùng' : 'Thêm tài khoản người dùng');

            document.getElementById('uMaNguoiDung').value = row && row.MaNguoiDung ? String(row.MaNguoiDung) : '';
            document.getElementById('uHoTen').value = row && row.HoTen ? String(row.HoTen) : '';
            document.getElementById('uTenDangNhap').value = row && row.TenDangNhap ? String(row.TenDangNhap) : '';
            document.getElementById('uEmail').value = row && row.Email ? String(row.Email) : '';
            document.getElementById('uSoDienThoai').value = row && row.SoDienThoai ? String(row.SoDienThoai) : '';
            document.getElementById('uMaSinhVien').value = row && row.MaSinhVien ? String(row.MaSinhVien) : '';
            document.getElementById('uMatKhau').value = '';
            document.getElementById('uHoatDong').value = (row && String(row.HoatDong) === '0') ? '0' : '1';

            const roleSelect = document.getElementById('uMaVaiTro');
            if (type === 'admin') {
                roleSelect.value = '1';
                roleSelect.disabled = true;
            } else {
                roleSelect.disabled = false;
                roleSelect.value = row && row.MaVaiTro ? String(row.MaVaiTro) : '3';
            }

            const khoaSelect = document.getElementById('uMaKhoa');
            khoaSelect.value = row && row.MaKhoa != null ? String(row.MaKhoa) : '';

            modal.classList.add('open');
        }

        function closeUserModal() {
            const modal = document.getElementById('userModal');
            if (modal) modal.classList.remove('open');
        }

        function submitUser(e) {
            e.preventDefault();

            const payload = new FormData();
            payload.append('type', document.getElementById('uType').value);
            payload.append('maNguoiDung', document.getElementById('uMaNguoiDung').value.trim());
            payload.append('hoTen', document.getElementById('uHoTen').value.trim());
            payload.append('tenDangNhap', document.getElementById('uTenDangNhap').value.trim());
            payload.append('email', document.getElementById('uEmail').value.trim());
            payload.append('soDienThoai', document.getElementById('uSoDienThoai').value.trim());
            payload.append('maVaiTro', document.getElementById('uMaVaiTro').value);
            payload.append('maKhoa', document.getElementById('uMaKhoa').value);
            payload.append('maSinhVien', document.getElementById('uMaSinhVien').value.trim());
            payload.append('matKhau', document.getElementById('uMatKhau').value);
            payload.append('hoatDong', document.getElementById('uHoatDong').value);

            fetch('actions/system_admin/save_user.php', { method: 'POST', body: payload })
                .then(jsonOrError)
                .then(data => {
                    if (data.success) {
                        alert(data.message || 'Lưu thành công');
                        window.location.reload();
                    } else {
                        alert('Lỗi: ' + (data.message || 'Không xác định'));
                    }
                })
                .catch(err => alert('Lỗi kết nối: ' + err.message));
        }

        function setUserActive(maNguoiDung, active) {
            if (!maNguoiDung) return;
            const actionText = active === 1 ? 'mở khóa' : 'khóa';
            if (!confirm('Xác nhận ' + actionText + ' tài khoản ' + maNguoiDung + '?')) return;

            const fd = new FormData();
            fd.append('maNguoiDung', maNguoiDung);
            fd.append('active', String(active));

            fetch('actions/system_admin/set_user_active.php', { method: 'POST', body: fd })
                .then(jsonOrError)
                .then(data => {
                    if (data.success) {
                        alert(data.message || 'Cập nhật thành công');
                        window.location.reload();
                    } else {
                        alert('Lỗi: ' + (data.message || 'Không xác định'));
                    }
                })
                .catch(err => alert('Lỗi kết nối: ' + err.message));
        }

        // === Device CRUD ===
        function openDeviceModal(row) {
            const modal = document.getElementById('deviceModal');
            if (!modal) return;

            const isEdit = !!(row && row.MaThietBi);
            document.getElementById('deviceModalTitle').textContent = isEdit ? 'Sửa thiết bị' : 'Thêm thiết bị';
            document.getElementById('dMode').value = isEdit ? 'edit' : 'create';

            document.getElementById('dMaThietBi').value = isEdit ? String(row.MaThietBi) : '';
            document.getElementById('dMaThietBi').readOnly = isEdit;

            document.getElementById('dMaLoaiThietBi').value = isEdit ? String(row.MaLoaiThietBi || '') : '';
            document.getElementById('dMaTaiSan').value = isEdit ? String(row.MaTaiSan || '') : '';
            document.getElementById('dSoSerial').value = isEdit ? String(row.SoSerial || '') : '';
            document.getElementById('dMaDiaDiem').value = isEdit ? String(row.MaDiaDiem || '') : '';
            document.getElementById('dMaTrangThai').value = isEdit ? String(row.MaTrangThai || '1') : '1';

            document.getElementById('dNgayMua').value = (isEdit && row.NgayMua) ? String(row.NgayMua).slice(0,10) : '';
            document.getElementById('dHanBaoHanh').value = (isEdit && row.HanBaoHanh) ? String(row.HanBaoHanh).slice(0,10) : '';
            document.getElementById('dGiaMua').value = isEdit && row.GiaMua != null ? String(row.GiaMua) : '';
            document.getElementById('dGhiChu').value = isEdit ? String(row.GhiChu || '') : '';

            modal.classList.add('open');
        }

        function closeDeviceModal() {
            const modal = document.getElementById('deviceModal');
            if (modal) modal.classList.remove('open');
        }

        function submitDevice(e) {
            e.preventDefault();

            const fd = new FormData();
            fd.append('mode', document.getElementById('dMode').value);
            fd.append('maThietBi', document.getElementById('dMaThietBi').value.trim());
            fd.append('maLoaiThietBi', document.getElementById('dMaLoaiThietBi').value);
            fd.append('maTaiSan', document.getElementById('dMaTaiSan').value.trim());
            fd.append('soSerial', document.getElementById('dSoSerial').value.trim());
            fd.append('maDiaDiem', document.getElementById('dMaDiaDiem').value);
            fd.append('maTrangThai', document.getElementById('dMaTrangThai').value);
            fd.append('ngayMua', document.getElementById('dNgayMua').value);
            fd.append('hanBaoHanh', document.getElementById('dHanBaoHanh').value);
            fd.append('giaMua', document.getElementById('dGiaMua').value);
            fd.append('ghiChu', document.getElementById('dGhiChu').value.trim());

            fetch('actions/system_admin/save_device.php', { method: 'POST', body: fd })
                .then(jsonOrError)
                .then(data => {
                    if (data.success) {
                        alert(data.message || 'Lưu thành công');
                        window.location.reload();
                    } else {
                        alert('Lỗi: ' + (data.message || 'Không xác định'));
                    }
                })
                .catch(err => alert('Lỗi kết nối: ' + err.message));
        }

        function deleteDevice(maThietBi) {
            if (!maThietBi) return;
            if (!confirm('Xác nhận xóa thiết bị ' + maThietBi + '?')) return;

            const fd = new FormData();
            fd.append('maThietBi', maThietBi);

            fetch('actions/system_admin/delete_device.php', { method: 'POST', body: fd })
                .then(jsonOrError)
                .then(data => {
                    if (data.success) {
                        alert(data.message || 'Đã xóa');
                        window.location.reload();
                    } else {
                        alert('Lỗi: ' + (data.message || 'Không xác định'));
                    }
                })
                .catch(err => alert('Lỗi kết nối: ' + err.message));
        }

        // === Maintenance approval ===
        function openMaintApproveModal(maBaoTri, maThietBi) {
            const modal = document.getElementById('maintModal');
            if (!modal) return;
            document.getElementById('mMaBaoTri').value = maBaoTri || '';
            document.getElementById('mMaThietBi').value = maThietBi || '';
            document.getElementById('mChiPhi').value = '0';
            modal.classList.add('open');
        }

        function closeMaintModal() {
            const modal = document.getElementById('maintModal');
            if (modal) modal.classList.remove('open');
        }

        function submitMaintApprove(e) {
            e.preventDefault();
            const maBaoTri = document.getElementById('mMaBaoTri').value.trim();
            const maThietBi = document.getElementById('mMaThietBi').value.trim();
            const chiPhi = document.getElementById('mChiPhi').value;
            if (!maBaoTri || !maThietBi) {
                alert('Thiếu thông tin phiếu bảo trì.');
                return;
            }

            const fd = new FormData();
            fd.append('maBaoTri', maBaoTri);
            fd.append('maThietBi', maThietBi);
            fd.append('chiPhi', chiPhi);

            fetch('actions/system_admin/maint_approve.php', { method: 'POST', body: fd })
                .then(jsonOrError)
                .then(data => {
                    if (data.success) {
                        alert(data.message || 'Đã duyệt bảo trì');
                        window.location.reload();
                    } else {
                        alert('Lỗi: ' + (data.message || 'Không xác định'));
                    }
                })
                .catch(err => alert('Lỗi kết nối: ' + err.message));
        }

        function markMaintBroken(maBaoTri, maThietBi) {
            if (!maBaoTri || !maThietBi) return;
            if (!confirm('Xác nhận đánh dấu thiết bị hỏng và không thể bảo trì?')) return;

            const fd = new FormData();
            fd.append('maBaoTri', maBaoTri);
            fd.append('maThietBi', maThietBi);

            fetch('actions/system_admin/maint_broken.php', { method: 'POST', body: fd })
                .then(jsonOrError)
                .then(data => {
                    if (data.success) {
                        alert(data.message || 'Đã cập nhật');
                        window.location.reload();
                    } else {
                        alert('Lỗi: ' + (data.message || 'Không xác định'));
                    }
                })
                .catch(err => alert('Lỗi kết nối: ' + err.message));
        }

        // Modal click outside
        document.querySelectorAll('.modal').forEach(m => {
            m.addEventListener('click', function(e) {
                if (e.target === this) this.classList.remove('open');
            });
        });
    </script>
</body>
</html>
