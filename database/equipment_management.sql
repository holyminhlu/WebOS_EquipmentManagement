-- =====================================================
-- HỆ THỐNG QUẢN LÝ MƯỢN TRẢ THIẾT BỊ GIẢNG DẠY
-- TRƯỜNG ĐẠI HỌC TRÀ VINH
-- =====================================================
-- Database: equipment_management
-- Charset: utf8mb4_unicode_ci
-- =====================================================

-- Tạo database nếu chưa có
CREATE DATABASE IF NOT EXISTS qltb 
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE qltb;

-- =====================================================
-- XÓA CÁC BẢNG CŨ (NẾU TỒN TẠI)
-- =====================================================
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS ThongBao;
DROP TABLE IF EXISTS NhatKyHeThong;
DROP TABLE IF EXISTS PhieuPhat;
DROP TABLE IF EXISTS BaoTri;
DROP TABLE IF EXISTS DatTruoc;
DROP TABLE IF EXISTS ChiTietMuon;
DROP TABLE IF EXISTS PhieuMuon;
DROP TABLE IF EXISTS YeuCauMuon;
DROP TABLE IF EXISTS ThietBi;
DROP TABLE IF EXISTS LoaiThietBi;
DROP TABLE IF EXISTS NguoiDung;
DROP TABLE IF EXISTS TrangThaiThietBi;
DROP TABLE IF EXISTS DiaDiem;
DROP TABLE IF EXISTS KhoaPhongBan;
DROP TABLE IF EXISTS VaiTro;

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================
-- TẠO CÁC BẢNG
-- =====================================================

-- 1. Bảng VaiTro
CREATE TABLE VaiTro (
    MaVaiTro INT PRIMARY KEY,           
    TenVaiTro VARCHAR(50) NOT NULL,   

    IsDeleted TINYINT(1) DEFAULT 0 NOT NULL,
    DeletedAt DATETIME NULL,
    DeletedBy VARCHAR(20) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Bảng KhoaPhongBan
CREATE TABLE KhoaPhongBan (
    MaKhoa INT AUTO_INCREMENT PRIMARY KEY,      
    TenKhoa VARCHAR(100) NOT NULL,

    IsDeleted TINYINT(1) DEFAULT 0 NOT NULL,
    DeletedAt DATETIME NULL,
    DeletedBy VARCHAR(20) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Bảng DiaDiem
CREATE TABLE DiaDiem (
    MaDiaDiem INT AUTO_INCREMENT PRIMARY KEY,   
    TenDiaDiem VARCHAR(100) NOT NULL,
    DiaChi VARCHAR(500),                 
    NguoiPhuTrach VARCHAR(200),

    IsDeleted TINYINT(1) DEFAULT 0 NOT NULL,
    DeletedAt DATETIME NULL,
    DeletedBy VARCHAR(20) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Bảng TrangThaiThietBi
CREATE TABLE TrangThaiThietBi (
    MaTrangThai INT PRIMARY KEY,         
    TenTrangThai VARCHAR(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Bảng NguoiDung
CREATE TABLE NguoiDung (
    MaNguoiDung VARCHAR(20) PRIMARY KEY,   
    TenDangNhap VARCHAR(100) UNIQUE NOT NULL, 
    MatKhau VARCHAR(256) NOT NULL,            
    HoTen VARCHAR(200) NOT NULL,            
    Email VARCHAR(200) UNIQUE,              
    SoDienThoai VARCHAR(20),               
    MaVaiTro INT NOT NULL,                 
    MaKhoa INT NULL,                       
    MaSinhVien VARCHAR(50) NULL,           
    HoatDong TINYINT(1) DEFAULT 1,                
    NgayTao DATETIME DEFAULT CURRENT_TIMESTAMP,      
    NgayCapNhat DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,    

    IsDeleted TINYINT(1) DEFAULT 0 NOT NULL,
    DeletedAt DATETIME NULL,
    DeletedBy VARCHAR(20) NULL,

    FOREIGN KEY (MaVaiTro) REFERENCES VaiTro(MaVaiTro),
    FOREIGN KEY (MaKhoa) REFERENCES KhoaPhongBan(MaKhoa)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Bảng LoaiThietBi
CREATE TABLE LoaiThietBi (
    MaLoaiThietBi VARCHAR(20) PRIMARY KEY,    
    TenLoai VARCHAR(100) NOT NULL,          
    MoTa TEXT,                        
    DanhMuc VARCHAR(100),                     
    ThoiHanMuonMacDinh INT DEFAULT 7,

    IsDeleted TINYINT(1) DEFAULT 0 NOT NULL,
    DeletedAt DATETIME NULL,
    DeletedBy VARCHAR(20) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Bảng ThietBi
CREATE TABLE ThietBi (
    MaThietBi VARCHAR(20) PRIMARY KEY,     
    MaLoaiThietBi VARCHAR(20) NOT NULL,                
    MaTaiSan VARCHAR(100) UNIQUE,           
    SoSerial VARCHAR(200),                    
    MaDiaDiem INT NOT NULL,                   
    MaTrangThai INT NOT NULL DEFAULT 1,      
    NgayMua DATE,                            
    HanBaoHanh DATE,                         
    GiaMua DECIMAL(15,2),                      
    GhiChu TEXT,

    IsDeleted TINYINT(1) DEFAULT 0 NOT NULL,
    DeletedAt DATETIME NULL,
    DeletedBy VARCHAR(20) NULL,

    FOREIGN KEY (MaLoaiThietBi) REFERENCES LoaiThietBi(MaLoaiThietBi),
    FOREIGN KEY (MaDiaDiem) REFERENCES DiaDiem(MaDiaDiem),
    FOREIGN KEY (MaTrangThai) REFERENCES TrangThaiThietBi(MaTrangThai)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Bảng YeuCauMuon
CREATE TABLE YeuCauMuon (
    MaYeuCau VARCHAR(20) PRIMARY KEY,      
    MaNguoiYeuCau VARCHAR(20) NOT NULL,             
    NgayGui DATETIME DEFAULT CURRENT_TIMESTAMP,        
    TrangThai VARCHAR(50) DEFAULT 'Chờ duyệt',
    MucDich TEXT,                     
    NgayDuKienBatDau DATETIME,               
    NgayDuKienKetThuc DATETIME,                
    NguoiDuyet VARCHAR(20) NULL,                    
    NgayDuyet DATETIME NULL,                 
    GhiChu VARCHAR(500),                      

    IsDeleted TINYINT(1) DEFAULT 0 NOT NULL,
    DeletedAt DATETIME NULL,
    DeletedBy VARCHAR(20) NULL,

    FOREIGN KEY (MaNguoiYeuCau) REFERENCES NguoiDung(MaNguoiDung),
    FOREIGN KEY (NguoiDuyet) REFERENCES NguoiDung(MaNguoiDung)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Bảng PhieuMuon
CREATE TABLE PhieuMuon (
    MaPhieu VARCHAR(20) PRIMARY KEY,       
    SoPhieu VARCHAR(50) UNIQUE NOT NULL,      
    MaYeuCau VARCHAR(20) NULL,                     
    MaNguoiMuon VARCHAR(20) NOT NULL,           
    NguoiPhatThietBi VARCHAR(20) NOT NULL,     
    NgayPhat DATETIME DEFAULT CURRENT_TIMESTAMP,       
    NgayPhaiTra DATETIME NOT NULL,            
    NgayTraThucTe DATETIME NULL,              
    TrangThai VARCHAR(50) DEFAULT 'Đang mượn',
    TongTienPhat DECIMAL(10,2) DEFAULT 0,

    IsDeleted TINYINT(1) DEFAULT 0 NOT NULL,
    DeletedAt DATETIME NULL,
    DeletedBy VARCHAR(20) NULL,

    FOREIGN KEY (MaYeuCau) REFERENCES YeuCauMuon(MaYeuCau),
    FOREIGN KEY (MaNguoiMuon) REFERENCES NguoiDung(MaNguoiDung),
    FOREIGN KEY (NguoiPhatThietBi) REFERENCES NguoiDung(MaNguoiDung)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Bảng ChiTietMuon
CREATE TABLE ChiTietMuon (
    MaChiTiet VARCHAR(20) PRIMARY KEY,   
    MaPhieu VARCHAR(20) NOT NULL,                   
    MaThietBi VARCHAR(20) NOT NULL,                 
    SoLuong INT DEFAULT 1,                     
    TinhTrangLucMuon VARCHAR(200),           
    TinhTrangLucTra VARCHAR(200) NULL,       
    GhiChu VARCHAR(500),

    IsDeleted TINYINT(1) DEFAULT 0 NOT NULL,
    DeletedAt DATETIME NULL,
    DeletedBy VARCHAR(20) NULL,

    FOREIGN KEY (MaPhieu) REFERENCES PhieuMuon(MaPhieu),
    FOREIGN KEY (MaThietBi) REFERENCES ThietBi(MaThietBi)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. Bảng DatTruoc
CREATE TABLE DatTruoc (
    MaDatTruoc VARCHAR(20) PRIMARY KEY,    
    MaNguoiYeuCau VARCHAR(20) NOT NULL,             
    MaLoaiThietBi VARCHAR(20) NOT NULL,              
    NgayBatDau DATETIME NOT NULL,              
    NgayKetThuc DATETIME NOT NULL,             
    TrangThai VARCHAR(50) DEFAULT 'Chờ duyệt',
    NgayTao DATETIME DEFAULT CURRENT_TIMESTAMP,

    IsDeleted TINYINT(1) DEFAULT 0 NOT NULL,
    DeletedAt DATETIME NULL,
    DeletedBy VARCHAR(20) NULL,

    FOREIGN KEY (MaNguoiYeuCau) REFERENCES NguoiDung(MaNguoiDung),
    FOREIGN KEY (MaLoaiThietBi) REFERENCES LoaiThietBi(MaLoaiThietBi)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. Bảng BaoTri
CREATE TABLE BaoTri (
    MaBaoTri VARCHAR(20) PRIMARY KEY,      
    MaThietBi VARCHAR(20) NOT NULL,                 
    NgayBao DATETIME DEFAULT CURRENT_TIMESTAMP,        
    NgaySua DATETIME NULL,                     
    TrangThai VARCHAR(50) DEFAULT 'Đã báo',   
    MaNhaCungCap VARCHAR(20) NULL,                  
    ChiPhi DECIMAL(15,2),               
    MoTa TEXT,

    IsDeleted TINYINT(1) DEFAULT 0 NOT NULL,
    DeletedAt DATETIME NULL,
    DeletedBy VARCHAR(20) NULL,

    FOREIGN KEY (MaThietBi) REFERENCES ThietBi(MaThietBi)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. Bảng PhieuPhat
CREATE TABLE PhieuPhat (
    MaPhat VARCHAR(20) PRIMARY KEY,       
    MaPhieu VARCHAR(20) NOT NULL,                  
    MaNguoiDung VARCHAR(20) NOT NULL,               
    SoTien DECIMAL(10,2) NOT NULL,             
    LyDo VARCHAR(500),                        
    DaThanhToan TINYINT(1) DEFAULT 0,               
    NgayThanhToan DATETIME NULL,               
    NgayTao DATETIME DEFAULT CURRENT_TIMESTAMP,

    IsDeleted TINYINT(1) DEFAULT 0 NOT NULL,
    DeletedAt DATETIME NULL,
    DeletedBy VARCHAR(20) NULL,

    FOREIGN KEY (MaPhieu) REFERENCES PhieuMuon(MaPhieu),
    FOREIGN KEY (MaNguoiDung) REFERENCES NguoiDung(MaNguoiDung)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 14. Bảng NhatKyHeThong
CREATE TABLE NhatKyHeThong (
    MaNhatKy VARCHAR(20) PRIMARY KEY,      
    ThucThe VARCHAR(100) NOT NULL,            
    MaThucThe VARCHAR(20) NOT NULL,                 
    HanhDong VARCHAR(50) NOT NULL,           
    ThucHienBoi VARCHAR(20) NOT NULL,              
    ThoiGian DATETIME DEFAULT CURRENT_TIMESTAMP,      
    DuLieuTruoc TEXT,                 
    DuLieuSau TEXT,

    FOREIGN KEY (ThucHienBoi) REFERENCES NguoiDung(MaNguoiDung)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 15. Bảng ThongBao
CREATE TABLE ThongBao (
    MaThongBao VARCHAR(20) PRIMARY KEY,    
    MaNguoiDung VARCHAR(20) NOT NULL,               
    TieuDe VARCHAR(200) NOT NULL,            
    NoiDung TEXT NOT NULL,            
    DaDoc TINYINT(1) DEFAULT 0,                       
    NgayGui DATETIME DEFAULT CURRENT_TIMESTAMP,        
    Kenh VARCHAR(50) DEFAULT 'trong ứng dụng',

    IsDeleted TINYINT(1) DEFAULT 0 NOT NULL,
    DeletedAt DATETIME NULL,
    DeletedBy VARCHAR(20) NULL,

    FOREIGN KEY (MaNguoiDung) REFERENCES NguoiDung(MaNguoiDung)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- INSERT DỮ LIỆU MẪU
-- =====================================================

-- 1. Vai trò
INSERT INTO VaiTro (MaVaiTro, TenVaiTro) VALUES
(1, 'Admin'),
(2, 'Giảng viên'),
(3, 'Sinh viên');

-- 2. Khoa/Phòng ban
INSERT INTO KhoaPhongBan (TenKhoa) VALUES
('Khoa Công nghệ Thông tin'),
('Khoa Kỹ thuật Công nghệ'),
('Khoa Kinh tế'),
('Khoa Báo chí - Truyền thông'),
('Phòng Quản lý Thiết bị');

-- 3. Địa điểm
INSERT INTO DiaDiem (TenDiaDiem, DiaChi, NguoiPhuTrach) VALUES
('Phòng Thiết bị Trung tâm', 'Nhà A, Tầng 1', 'Nguyễn Văn A'),
('Khoa CNTT', 'Nhà B, Tầng 3', 'Trần Thị B'),
('Khoa Báo chí', 'Nhà C, Tầng 2', 'Lê Văn C');

-- 4. Trạng thái thiết bị
INSERT INTO TrangThaiThietBi (MaTrangThai, TenTrangThai) VALUES
(1, 'Khả dụng'),
(2, 'Đang được mượn'),
(3, 'Đang bảo trì'),
(4, 'Hỏng'),
(5, 'Đã thanh lý');

-- 5. Người dùng (password mặc định: 123456)
-- Admin
INSERT INTO NguoiDung (MaNguoiDung, TenDangNhap, MatKhau, HoTen, Email, SoDienThoai, MaVaiTro, MaKhoa) VALUES
('ND001', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Quản trị viên', 'admin@tvu.edu.vn', '0123456789', 1, 5);

-- Giảng viên
INSERT INTO NguoiDung (MaNguoiDung, TenDangNhap, MatKhau, HoTen, Email, SoDienThoai, MaVaiTro, MaKhoa) VALUES
('ND002', 'gv001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nguyễn Văn Giảng', 'giang.nv@tvu.edu.vn', '0987654321', 2, 1),
('ND003', 'gv002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Trần Thị Hoa', 'hoa.tt@tvu.edu.vn', '0912345678', 2, 4);

-- Sinh viên
INSERT INTO NguoiDung (MaNguoiDung, TenDangNhap, MatKhau, HoTen, Email, SoDienThoai, MaVaiTro, MaKhoa, MaSinhVien) VALUES
('ND004', 'sv001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Lê Văn Sinh', '2151120345@student.tvu.edu.vn', '0909123456', 3, 1, '2151120345'),
('ND005', 'sv002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Phạm Thị Mai', '2151120346@student.tvu.edu.vn', '0908765432', 3, 1, '2151120346');

-- 6. Loại thiết bị
INSERT INTO LoaiThietBi (MaLoaiThietBi, TenLoai, MoTa, DanhMuc, ThoiHanMuonMacDinh) VALUES
('LTB001', 'Máy chiếu', 'Các loại máy chiếu dùng trong giảng dạy', 'Thiết bị trình chiếu', 7),
('LTB002', 'Laptop', 'Laptop dùng cho giảng dạy và học tập', 'Thiết bị tính toán', 3),
('LTB003', 'Máy ảnh', 'Máy ảnh DSLR và Mirrorless', 'Thiết bị ghi hình', 2),
('LTB004', 'Loa trợ giảng', 'Loa không dây, loa di động', 'Thiết bị âm thanh', 7),
('LTB005', 'Micro', 'Micro cài áo, micro không dây', 'Thiết bị âm thanh', 7),
('LTB006', 'Cáp kết nối', 'HDMI, VGA, DisplayPort, USB-C', 'Phụ kiện', 7),
('LTB007', 'Tai nghe', 'Tai nghe có micro cho học trực tuyến', 'Thiết bị âm thanh', 3);

-- 7. Thiết bị cụ thể
INSERT INTO ThietBi (MaThietBi, MaLoaiThietBi, MaTaiSan, SoSerial, MaDiaDiem, MaTrangThai, NgayMua, HanBaoHanh, GiaMua, GhiChu) VALUES
('TB001', 'LTB001', 'TS-MC-001', 'SN-BQ-MW612-001', 1, 1, '2023-01-15', '2026-01-15', 8500000.00, 'Máy chiếu BenQ MW612'),
('TB002', 'LTB001', 'TS-MC-002', 'SN-BQ-MW612-002', 1, 1, '2023-01-15', '2026-01-15', 8500000.00, 'Máy chiếu BenQ MW612'),
('TB003', 'LTB001', 'TS-MC-003', 'SN-BQ-MW612-003', 2, 2, '2023-01-15', '2026-01-15', 8500000.00, 'Máy chiếu BenQ MW612'),
('TB004', 'LTB002', 'TS-LT-001', 'SN-DELL-5520-001', 1, 1, '2023-03-20', '2026-03-20', 15000000.00, 'Dell Latitude 5520'),
('TB005', 'LTB002', 'TS-LT-002', 'SN-DELL-5520-002', 1, 1, '2023-03-20', '2026-03-20', 15000000.00, 'Dell Latitude 5520'),
('TB006', 'LTB003', 'TS-MA-001', 'SN-CANON-200D-001', 3, 1, '2022-06-10', '2024-06-10', 12000000.00, 'Canon EOS 200D'),
('TB007', 'LTB004', 'TS-LOA-001', 'SN-TKS-E17-001', 1, 1, '2023-02-05', '2025-02-05', 2500000.00, 'Takstar E17'),
('TB008', 'LTB004', 'TS-LOA-002', 'SN-TKS-E17-002', 3, 1, '2023-02-05', '2025-02-05', 2500000.00, 'Takstar E17'),
('TB009', 'LTB006', 'TS-CAP-001', 'SN-CAP-HDMI-001', 1, 1, '2023-05-01', '2025-05-01', 350000.00, 'Cáp HDMI 5m'),
('TB010', 'LTB006', 'TS-CAP-002', 'SN-CAP-MDPORT-001', 1, 1, '2023-05-01', '2025-05-01', 450000.00, 'Mini DisplayPort to HDMI'),
('TB011', 'LTB007', 'TS-TAI-001', 'SN-LOG-H390-001', 1, 1, '2023-04-15', '2025-04-15', 750000.00, 'Logitech H390'),
('TB012', 'LTB007', 'TS-TAI-002', 'SN-LOG-H390-002', 1, 1, '2023-04-15', '2025-04-15', 750000.00, 'Logitech H390');

-- 8. Yêu cầu mượn mẫu
INSERT INTO YeuCauMuon (MaYeuCau, MaNguoiYeuCau, NgayGui, TrangThai, MucDich, NgayDuKienBatDau, NgayDuKienKetThuc, GhiChu) VALUES
('YC001', 'ND004', '2025-12-28 09:00:00', 'Chờ duyệt', 'Phục vụ thuyết trình đồ án tốt nghiệp', '2026-01-05 08:00:00', '2026-01-05 17:00:00', 'Cần máy chiếu và laptop'),
('YC002', 'ND005', '2025-12-29 10:30:00', 'Chờ duyệt', 'Ghi hình video bài thuyết trình', '2026-01-06 09:00:00', '2026-01-06 12:00:00', 'Cần máy ảnh và loa');

-- 9. Phiếu mượn mẫu (đã được duyệt trước đó)
INSERT INTO PhieuMuon (MaPhieu, SoPhieu, MaYeuCau, MaNguoiMuon, NguoiPhatThietBi, NgayPhat, NgayPhaiTra, TrangThai) VALUES
('PM001', 'SP001', NULL, 'ND002', 'ND001', '2025-12-20 08:00:00', '2025-12-27 17:00:00', 'Đang mượn');

-- 10. Chi tiết mượn
INSERT INTO ChiTietMuon (MaChiTiet, MaPhieu, MaThietBi, SoLuong, TinhTrangLucMuon, GhiChu) VALUES
('CTM001', 'PM001', 'TB003', 1, 'Tốt', 'Máy chiếu cho lớp học');

-- 11. Thông báo mẫu
INSERT INTO ThongBao (MaThongBao, MaNguoiDung, TieuDe, NoiDung, DaDoc) VALUES
('TB001', 'ND004', 'Chào mừng đến với hệ thống', 'Chào mừng bạn đến với Hệ thống mượn trả thiết bị giảng dạy Trường ĐH Trà Vinh. Vui lòng đọc kỹ quy định sử dụng thiết bị.', 0),
('TB002', 'ND005', 'Chào mừng đến với hệ thống', 'Chào mừng bạn đến với Hệ thống mượn trả thiết bị giảng dạy Trường ĐH Trà Vinh. Vui lòng đọc kỹ quy định sử dụng thiết bị.', 0);

-- =====================================================
-- HOÀN TẤT
-- =====================================================

SELECT 'Database qltb đã được tạo thành công!' as Message;
SELECT 'Tài khoản mặc định:' as Info;
SELECT 'Admin: admin / 123456' as Account;
SELECT 'Giảng viên: gv001 / 123456' as Account;
SELECT 'Sinh viên: sv001 / 123456' as Account;
