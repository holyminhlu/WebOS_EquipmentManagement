<?php
/**
 * User Data Functions
 * Các hàm lấy dữ liệu người dùng cho dashboard
 * 
 * @author System Development Team
 * @version 1.0
 */

require_once __DIR__ . '/db.php';

/**
 * Lấy thông tin cá nhân của người dùng
 * @param string $maNguoiDung Mã người dùng
 * @return array|false
 */
function getUserInfo($maNguoiDung) {
    $sql = "SELECT nd.*, vt.TenVaiTro, kpb.TenKhoa 
            FROM NguoiDung nd
            LEFT JOIN VaiTro vt ON nd.MaVaiTro = vt.MaVaiTro
            LEFT JOIN KhoaPhongBan kpb ON nd.MaKhoa = kpb.MaKhoa
            WHERE nd.MaNguoiDung = ?
            AND nd.IsDeleted = 0";
    
    return dbQueryOne($sql, [$maNguoiDung]);
}

/**
 * Lấy danh sách phiếu mượn của người dùng
 * @param string $maNguoiDung Mã người dùng
 * @return array
 */
function getUserPhieuMuon($maNguoiDung) {
    $sql = "SELECT pm.*, 
                   nd_phat.HoTen as TenNguoiPhat,
                   ycm.MucDich,
                   ycm.NgayDuKienBatDau,
                   ycm.NgayDuKienKetThuc
            FROM PhieuMuon pm
            LEFT JOIN NguoiDung nd_phat ON pm.NguoiPhatThietBi = nd_phat.MaNguoiDung
            LEFT JOIN YeuCauMuon ycm ON pm.MaYeuCau = ycm.MaYeuCau
            WHERE pm.MaNguoiMuon = ?
            AND pm.IsDeleted = 0
            ORDER BY pm.NgayPhat DESC";
    
    return dbQuery($sql, [$maNguoiDung]);
}

/**
 * Lấy chi tiết thiết bị trong phiếu mượn
 * @param string $maPhieu Mã phiếu mượn
 * @return array
 */
function getChiTietMuon($maPhieu) {
    $sql = "SELECT ctm.*, 
                   tb.MaTaiSan, 
                   tb.SoSerial,
                   ltb.TenLoai,
                   tttb.TenTrangThai
            FROM ChiTietMuon ctm
            INNER JOIN ThietBi tb ON ctm.MaThietBi = tb.MaThietBi
            INNER JOIN LoaiThietBi ltb ON tb.MaLoaiThietBi = ltb.MaLoaiThietBi
            LEFT JOIN TrangThaiThietBi tttb ON tb.MaTrangThai = tttb.MaTrangThai
            WHERE ctm.MaPhieu = ?
            AND ctm.IsDeleted = 0
            ORDER BY ctm.MaChiTiet";
    
    return dbQuery($sql, [$maPhieu]);
}

/**
 * Lấy danh sách yêu cầu mượn của người dùng
 * @param string $maNguoiDung Mã người dùng
 * @return array
 */
function getUserYeuCauMuon($maNguoiDung) {
    $sql = "SELECT ycm.*, 
                   nd_duyet.HoTen as TenNguoiDuyet
            FROM YeuCauMuon ycm
            LEFT JOIN NguoiDung nd_duyet ON ycm.NguoiDuyet = nd_duyet.MaNguoiDung
            WHERE ycm.MaNguoiYeuCau = ?
            AND ycm.IsDeleted = 0
            ORDER BY ycm.NgayGui DESC";
    
    return dbQuery($sql, [$maNguoiDung]);
}

/**
 * Lấy danh sách đặt trước của người dùng
 * @param string $maNguoiDung Mã người dùng
 * @return array
 */
function getUserDatTruoc($maNguoiDung) {
    $sql = "SELECT dt.*, 
                   ltb.TenLoai
            FROM DatTruoc dt
            INNER JOIN LoaiThietBi ltb ON dt.MaLoaiThietBi = ltb.MaLoaiThietBi
            WHERE dt.MaNguoiYeuCau = ?
            AND dt.IsDeleted = 0
            ORDER BY dt.NgayTao DESC";
    
    return dbQuery($sql, [$maNguoiDung]);
}

/**
 * Lấy danh sách thông báo của người dùng
 * @param string $maNguoiDung Mã người dùng
 * @param int $limit Số lượng thông báo cần lấy (0 = tất cả)
 * @return array
 */
function getUserThongBao($maNguoiDung, $limit = 0) {
    $sql = "SELECT * 
            FROM ThongBao
            WHERE MaNguoiDung = ?
            AND IsDeleted = 0
            ORDER BY NgayGui DESC";
    
    if ($limit > 0) {
        $sql .= " LIMIT " . (int)$limit;
    }
    
    return dbQuery($sql, [$maNguoiDung]);
}

/**
 * Lấy danh sách phiếu phạt của người dùng
 * @param string $maNguoiDung Mã người dùng
 * @return array
 */
function getUserPhieuPhat($maNguoiDung) {
    $sql = "SELECT pp.*, 
                   pm.SoPhieu
            FROM PhieuPhat pp
            INNER JOIN PhieuMuon pm ON pp.MaPhieu = pm.MaPhieu
            WHERE pp.MaNguoiDung = ?
            AND pp.IsDeleted = 0
            ORDER BY pp.NgayTao DESC";
    
    return dbQuery($sql, [$maNguoiDung]);
}

/**
 * Đếm số thông báo chưa đọc
 * @param string $maNguoiDung Mã người dùng
 * @return int
 */
function countUnreadNotifications($maNguoiDung) {
    $sql = "SELECT COUNT(*) as count 
            FROM ThongBao
            WHERE MaNguoiDung = ?
            AND DaDoc = 0
            AND IsDeleted = 0";
    
    $result = dbQueryOne($sql, [$maNguoiDung]);
    return $result ? (int)$result['count'] : 0;
}

/**
 * Lấy danh sách khoa/phòng ban
 * @return array
 */
function getKhoaPhongBan() {
    $sql = "SELECT * 
            FROM KhoaPhongBan
            WHERE IsDeleted = 0
            ORDER BY TenKhoa ASC";
    
    return dbQuery($sql);
}

/**
 * Lấy danh sách vai trò
 * @return array
 */
function getVaiTro() {
    $sql = "SELECT * 
            FROM VaiTro
            WHERE IsDeleted = 0
            ORDER BY MaVaiTro ASC";
    
    return dbQuery($sql);
}

