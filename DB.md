-- 1. Bảng VaiTro
CREATE TABLE VaiTro (
    MaVaiTro INT PRIMARY KEY,           
    TenVaiTro VARCHAR(50) NOT NULL,   

    IsDeleted TINYINT(1) DEFAULT 0 NOT NULL,
    DeletedAt DATETIME NULL,
    DeletedBy VARCHAR(20) NULL
);

-- 2. Bảng KhoaPhongBan
CREATE TABLE KhoaPhongBan (
    MaKhoa INT AUTO_INCREMENT PRIMARY KEY,      
    TenKhoa VARCHAR(100) NOT NULL,

    IsDeleted TINYINT(1) DEFAULT 0 NOT NULL,
    DeletedAt DATETIME NULL,
    DeletedBy VARCHAR(20) NULL
);

-- 3. Bảng DiaDiem
CREATE TABLE DiaDiem (
    MaDiaDiem INT AUTO_INCREMENT PRIMARY KEY,   
    TenDiaDiem VARCHAR(100) NOT NULL,
    DiaChi VARCHAR(500),  
    Khu VARCHAR(500),               
    NguoiPhuTrach VARCHAR(200),

    IsDeleted TINYINT(1) DEFAULT 0 NOT NULL,
    DeletedAt DATETIME NULL,
    DeletedBy VARCHAR(20) NULL
);

-- 4. Bảng TrangThaiThietBi
CREATE TABLE TrangThaiThietBi (
    MaTrangThai INT PRIMARY KEY,         
    TenTrangThai VARCHAR(50) NOT NULL
);

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
);

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
);

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
);

-- 8. Bảng YeuCauMuon
CREATE TABLE YeuCauMuon (
    MaYeuCau VARCHAR(20) PRIMARY KEY,      
    MaNguoiYeuCau VARCHAR(20) NOT NULL,             
    NgayGui DATETIME DEFAULT CURRENT_TIMESTAMP,        
    TrangThai VARCHAR(50) DEFAULT 'Chờ duyệt',
    MucDich TEXT,                     
    ThoiGianDuKienBatDau DATETIME,               
    ThoiGianDuKienKetThuc DATETIME,                
    NguoiDuyet VARCHAR(20) NULL,                    
    NgayDuyet DATETIME NULL,                 
    GhiChu VARCHAR(500),                      

    IsDeleted TINYINT(1) DEFAULT 0 NOT NULL,
    DeletedAt DATETIME NULL,
    DeletedBy VARCHAR(20) NULL,

    FOREIGN KEY (MaNguoiYeuCau) REFERENCES NguoiDung(MaNguoiDung),
    FOREIGN KEY (NguoiDuyet) REFERENCES NguoiDung(MaNguoiDung)
);

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
);

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
);

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
);

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
);

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
);

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
);

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
);
