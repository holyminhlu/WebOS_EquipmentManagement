-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th1 03, 2026 lúc 10:14 AM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `qltb`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `baotri`
--

CREATE TABLE `baotri` (
  `MaBaoTri` varchar(20) NOT NULL,
  `MaThietBi` varchar(20) NOT NULL,
  `NgayBao` datetime DEFAULT current_timestamp(),
  `NgaySua` datetime DEFAULT NULL,
  `TrangThai` varchar(50) DEFAULT 'Đã báo',
  `MaNhaCungCap` varchar(20) DEFAULT NULL,
  `ChiPhi` decimal(15,2) DEFAULT NULL,
  `MoTa` text DEFAULT NULL,
  `IsDeleted` tinyint(1) NOT NULL DEFAULT 0,
  `DeletedAt` datetime DEFAULT NULL,
  `DeletedBy` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `baotri`
--

INSERT INTO `baotri` (`MaBaoTri`, `MaThietBi`, `NgayBao`, `NgaySua`, `TrangThai`, `MaNhaCungCap`, `ChiPhi`, `MoTa`, `IsDeleted`, `DeletedAt`, `DeletedBy`) VALUES
('BT001', 'TB008', '2026-01-03 12:45:50', '2026-01-03 14:07:02', 'Đã hoàn thành', 'Công ty Ngọc Diệp', 0.00, 'Lỗi loa bị rè', 0, NULL, NULL),
('BT002', 'TB013', '2026-01-03 14:07:37', '2026-01-03 14:07:47', 'Thiết bị hỏng', 'Công ty Tương Lai', NULL, 'Mic không hoạt động', 0, NULL, NULL),
('BT003', 'TB001', '2026-01-03 15:35:51', NULL, 'Đang bảo trì', 'Phú Diễn', NULL, 'Hư cổng Input', 0, NULL, NULL),
('BT004', 'TB008', '2026-01-03 15:56:05', NULL, 'Đang bảo trì', 'Phú Diễn', NULL, 'Loa không phát', 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chitietmuon`
--

CREATE TABLE `chitietmuon` (
  `MaChiTiet` varchar(20) NOT NULL,
  `MaPhieu` varchar(20) NOT NULL,
  `MaThietBi` varchar(20) NOT NULL,
  `SoLuong` int(11) DEFAULT 1,
  `TinhTrangLucMuon` varchar(200) DEFAULT NULL,
  `TinhTrangLucTra` varchar(200) DEFAULT NULL,
  `GhiChu` varchar(500) DEFAULT NULL,
  `IsDeleted` tinyint(1) NOT NULL DEFAULT 0,
  `DeletedAt` datetime DEFAULT NULL,
  `DeletedBy` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `chitietmuon`
--

INSERT INTO `chitietmuon` (`MaChiTiet`, `MaPhieu`, `MaThietBi`, `SoLuong`, `TinhTrangLucMuon`, `TinhTrangLucTra`, `GhiChu`, `IsDeleted`, `DeletedAt`, `DeletedBy`) VALUES
('CTM001', 'PM001', 'TB007', 1, 'Tốt', NULL, 'Tạo khi duyệt yêu cầu', 0, NULL, NULL),
('CTM002', 'PM001', 'TB011', 1, 'Tốt', NULL, 'Tạo khi duyệt yêu cầu', 0, NULL, NULL),
('CTM003', 'PM002', 'TB005', 1, 'Tốt', NULL, 'Tạo khi duyệt yêu cầu', 0, NULL, NULL),
('CTM004', 'PM003', 'TB009', 1, 'Tốt', NULL, 'Tạo khi duyệt yêu cầu', 0, NULL, NULL),
('CTM005', 'PM004', 'TB001', 1, 'Tốt', NULL, 'Tạo khi duyệt yêu cầu', 0, NULL, NULL),
('CTM006', 'PM004', 'TB006', 1, 'Tốt', NULL, 'Tạo khi duyệt yêu cầu', 0, NULL, NULL),
('CTM007', 'PM005', 'TB002', 1, 'Tốt', NULL, 'Tạo khi duyệt yêu cầu', 0, NULL, NULL),
('CTM008', 'PM005', 'TB004', 1, 'Tốt', NULL, 'Tạo khi duyệt yêu cầu', 0, NULL, NULL),
('CTM009', 'PM006', 'TB001', 1, 'Tốt', NULL, 'Tạo khi duyệt yêu cầu', 0, NULL, NULL),
('CTM010', 'PM006', 'TB008', 1, 'Tốt', NULL, 'Tạo khi duyệt yêu cầu', 0, NULL, NULL),
('CTM011', 'PM007', 'TB001', 1, 'Tốt', NULL, 'Tạo khi duyệt đặt trước', 0, NULL, NULL),
('CTM012', 'PM007', 'TB008', 1, 'Tốt', NULL, 'Tạo khi duyệt đặt trước', 0, NULL, NULL),
('CTM013', 'PM008', 'TB001', 1, 'Tốt', NULL, 'Tạo khi duyệt yêu cầu', 0, NULL, NULL),
('CTM014', 'PM008', 'TB008', 1, 'Tốt', NULL, 'Tạo khi duyệt yêu cầu', 0, NULL, NULL),
('CTM015', 'PM009', 'TB001', 1, 'Tốt', NULL, 'Tạo khi duyệt yêu cầu', 0, NULL, NULL),
('CTM016', 'PM010', 'TB008', 1, 'Tốt', NULL, 'Tạo khi duyệt đặt trước', 0, NULL, NULL),
('CTM017', 'PM010', 'TB014', 1, 'Tốt', NULL, 'Tạo khi duyệt đặt trước', 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `dattruoc`
--

CREATE TABLE `dattruoc` (
  `MaDatTruoc` varchar(20) NOT NULL,
  `MaNguoiYeuCau` varchar(20) NOT NULL,
  `MaLoaiThietBi` varchar(20) NOT NULL,
  `NgayBatDau` datetime NOT NULL,
  `NgayKetThuc` datetime NOT NULL,
  `TrangThai` varchar(50) DEFAULT 'Chờ duyệt',
  `NgayTao` datetime DEFAULT current_timestamp(),
  `IsDeleted` tinyint(1) NOT NULL DEFAULT 0,
  `DeletedAt` datetime DEFAULT NULL,
  `DeletedBy` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `dattruoc`
--

INSERT INTO `dattruoc` (`MaDatTruoc`, `MaNguoiYeuCau`, `MaLoaiThietBi`, `NgayBatDau`, `NgayKetThuc`, `TrangThai`, `NgayTao`, `IsDeleted`, `DeletedAt`, `DeletedBy`) VALUES
('DT001D1-TB008', 'ND004', 'LTB004', '2026-01-03 07:00:00', '2026-01-03 10:00:00', 'Đã duyệt', '2026-01-02 15:50:56', 0, NULL, NULL),
('DT001D1-TB011', 'ND004', 'LTB007', '2026-01-03 07:00:00', '2026-01-03 10:00:00', 'Đã duyệt', '2026-01-02 15:50:56', 0, NULL, NULL),
('DT002D1-TB008', 'ND004', 'LTB004', '2026-01-03 13:00:00', '2026-01-03 17:00:00', 'Đã duyệt', '2026-01-02 15:51:59', 0, NULL, NULL),
('DT002D1-TB011', 'ND004', 'LTB007', '2026-01-03 13:00:00', '2026-01-03 17:00:00', 'Đã duyệt', '2026-01-02 15:51:59', 0, NULL, NULL),
('DT003D2-TB008', 'ND004', 'LTB004', '2026-01-03 07:00:00', '2026-01-03 10:30:00', 'Đã duyệt', '2026-01-02 15:52:36', 0, NULL, NULL),
('DT003D2-TB011', 'ND004', 'LTB007', '2026-01-03 07:00:00', '2026-01-03 10:30:00', 'Đã duyệt', '2026-01-02 15:52:36', 0, NULL, NULL),
('DT004D5-TB001', 'ND00000006', 'LTB001', '2026-01-03 08:00:00', '2026-01-03 17:00:00', 'Đã duyệt', '2026-01-02 22:56:18', 0, NULL, NULL),
('DT004D5-TB008', 'ND00000006', 'LTB004', '2026-01-03 08:00:00', '2026-01-03 17:00:00', 'Đã duyệt', '2026-01-02 22:56:18', 0, NULL, NULL),
('DT005D6-TB008', 'ND00001102', 'LTB004', '2026-01-04 08:00:00', '2026-01-04 17:00:00', 'Đã duyệt', '2026-01-03 15:30:40', 0, NULL, NULL),
('DT005D6-TB014', 'ND00001102', 'LTB005', '2026-01-04 08:00:00', '2026-01-04 17:00:00', 'Đã duyệt', '2026-01-03 15:30:40', 0, NULL, NULL),
('DT006D4-TB014', 'ND00000006', 'LTB005', '2026-01-04 08:00:00', '2026-01-04 17:00:00', 'Chờ duyệt', '2026-01-03 15:54:05', 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `diadiem`
--

CREATE TABLE `diadiem` (
  `MaDiaDiem` int(11) NOT NULL,
  `TenDiaDiem` varchar(100) NOT NULL,
  `DiaChi` varchar(500) DEFAULT NULL,
  `NguoiPhuTrach` varchar(200) DEFAULT NULL,
  `IsDeleted` tinyint(1) NOT NULL DEFAULT 0,
  `DeletedAt` datetime DEFAULT NULL,
  `DeletedBy` varchar(20) DEFAULT NULL,
  `Khu` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `diadiem`
--

INSERT INTO `diadiem` (`MaDiaDiem`, `TenDiaDiem`, `DiaChi`, `NguoiPhuTrach`, `IsDeleted`, `DeletedAt`, `DeletedBy`, `Khu`) VALUES
(1, 'B31.101', 'Dãy B3, Tầng 1, Phòng 1', 'Nguyễn Văn A', 0, NULL, NULL, '1'),
(2, 'B31.201', 'Dãy B3, Tầng 2, Phòng 1', 'Trần Thị B', 0, NULL, NULL, '1'),
(3, 'A42.101', 'Dãy A4, Tầng 1, Phòng 1', 'Lê Văn C', 0, NULL, NULL, '2'),
(4, 'A42.201', 'Dãy A4, Tầng 2, Phòng 1', 'Trần Minh D', 0, NULL, NULL, '2'),
(5, 'D31.101', 'Dãy D3, Tầng 1, Phòng 1', 'Nguyễn Hữu Luân', 0, NULL, NULL, '1'),
(6, 'D31.103', 'Dãy D3, Tầng 1, Phòng 3', 'Nguyễn Hữu Luân', 0, NULL, NULL, '1'),
(7, 'A42.202', 'Dãy A4, Tầng 2, Phòng 2', 'Trần Trung Phúc', 0, NULL, NULL, '2'),
(8, 'A42.301', 'Dãy A4, Tầng 3, Phòng 1', 'Hồ Lý Minh Lữ', 0, NULL, NULL, '2');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `khoaphongban`
--

CREATE TABLE `khoaphongban` (
  `MaKhoa` int(11) NOT NULL,
  `TenKhoa` varchar(100) NOT NULL,
  `IsDeleted` tinyint(1) NOT NULL DEFAULT 0,
  `DeletedAt` datetime DEFAULT NULL,
  `DeletedBy` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `khoaphongban`
--

INSERT INTO `khoaphongban` (`MaKhoa`, `TenKhoa`, `IsDeleted`, `DeletedAt`, `DeletedBy`) VALUES
(1, 'Khoa Công nghệ Thông tin', 0, NULL, NULL),
(2, 'Khoa Kỹ thuật Công nghệ', 0, NULL, NULL),
(3, 'Khoa Kinh tế', 0, NULL, NULL),
(4, 'Khoa Báo chí - Truyền thông', 0, NULL, NULL),
(5, 'Phòng Quản lý Thiết bị', 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `loaithietbi`
--

CREATE TABLE `loaithietbi` (
  `MaLoaiThietBi` varchar(20) NOT NULL,
  `TenLoai` varchar(100) NOT NULL,
  `MoTa` text DEFAULT NULL,
  `DanhMuc` varchar(100) DEFAULT NULL,
  `ThoiHanMuonMacDinh` int(11) DEFAULT 7,
  `IsDeleted` tinyint(1) NOT NULL DEFAULT 0,
  `DeletedAt` datetime DEFAULT NULL,
  `DeletedBy` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `loaithietbi`
--

INSERT INTO `loaithietbi` (`MaLoaiThietBi`, `TenLoai`, `MoTa`, `DanhMuc`, `ThoiHanMuonMacDinh`, `IsDeleted`, `DeletedAt`, `DeletedBy`) VALUES
('LTB001', 'Máy chiếu', 'Các loại máy chiếu dùng trong giảng dạy', 'Thiết bị trình chiếu', 7, 0, NULL, NULL),
('LTB002', 'Laptop', 'Laptop dùng cho giảng dạy và học tập', 'Thiết bị tính toán', 3, 0, NULL, NULL),
('LTB003', 'Máy ảnh', 'Máy ảnh DSLR và Mirrorless', 'Thiết bị ghi hình', 2, 0, NULL, NULL),
('LTB004', 'Loa trợ giảng', 'Loa không dây, loa di động', 'Thiết bị âm thanh', 7, 0, NULL, NULL),
('LTB005', 'Micro', 'Micro cài áo, micro không dây', 'Thiết bị âm thanh', 7, 0, NULL, NULL),
('LTB006', 'Cáp kết nối', 'HDMI, VGA, DisplayPort, USB-C', 'Phụ kiện', 7, 0, NULL, NULL),
('LTB007', 'Tai nghe', 'Tai nghe có micro cho học trực tuyến', 'Thiết bị âm thanh', 3, 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `nguoidung`
--

CREATE TABLE `nguoidung` (
  `MaNguoiDung` varchar(20) NOT NULL,
  `TenDangNhap` varchar(100) NOT NULL,
  `MatKhau` varchar(256) NOT NULL,
  `HoTen` varchar(200) NOT NULL,
  `Email` varchar(200) DEFAULT NULL,
  `SoDienThoai` varchar(20) DEFAULT NULL,
  `MaVaiTro` int(11) NOT NULL,
  `MaKhoa` int(11) DEFAULT NULL,
  `MaSinhVien` varchar(50) DEFAULT NULL,
  `HoatDong` tinyint(1) DEFAULT 1,
  `NgayTao` datetime DEFAULT current_timestamp(),
  `NgayCapNhat` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `IsDeleted` tinyint(1) NOT NULL DEFAULT 0,
  `DeletedAt` datetime DEFAULT NULL,
  `DeletedBy` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `nguoidung`
--

INSERT INTO `nguoidung` (`MaNguoiDung`, `TenDangNhap`, `MatKhau`, `HoTen`, `Email`, `SoDienThoai`, `MaVaiTro`, `MaKhoa`, `MaSinhVien`, `HoatDong`, `NgayTao`, `NgayCapNhat`, `IsDeleted`, `DeletedAt`, `DeletedBy`) VALUES
('ND00000006', 'holyminhlu', '$2y$10$DOZVAIC69Ih6.477GWcGTeW7WErnaVFabPsUAZ8hKESxu74HSxQ2S', 'Hồ Lý Minh Lữ', '110122231@st.tvu.edu.vn', '0983149203', 3, 1, '110122231', 1, '2026-01-02 19:20:33', '2026-01-02 19:20:33', 0, NULL, NULL),
('ND00001102', 'nguyenhuuluan', '$2y$10$QjlHBQRdwhX5TcW9dPomu.WtkPBZzVtFII9ajXXhp9sB7pDL/AeSG', 'Nguyễn Hữu Luân', 'nguyenhuuluan@gmail.com', '1234567890', 3, 1, '110122101', 1, '2026-01-03 14:09:59', '2026-01-03 15:30:19', 0, NULL, NULL),
('ND001', 'admin', '827ccb0eea8a706c4c34a16891f84e7b', 'Quản trị viên', 'admin@tvu.edu.vn', '0123456789', 1, 5, NULL, 1, '2026-01-02 11:47:16', '2026-01-02 15:13:17', 0, NULL, NULL),
('ND002', 'gv001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nguyễn Văn Giảng', 'giang.nv@tvu.edu.vn', '0987654321', 2, 1, NULL, 1, '2026-01-02 11:47:16', '2026-01-02 11:47:16', 0, NULL, NULL),
('ND003', 'gv002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Trần Thị Hoa', 'hoa.tt@tvu.edu.vn', '0912345678', 2, 4, NULL, 1, '2026-01-02 11:47:16', '2026-01-02 11:47:16', 0, NULL, NULL),
('ND004', 'sv001', '827ccb0eea8a706c4c34a16891f84e7b', 'Lê Văn Sinh', '2151120345@student.tvu.edu.vn', '0909123456', 3, 1, '2151120345', 1, '2026-01-02 11:47:16', '2026-01-02 11:49:09', 0, NULL, NULL),
('ND005', 'sv002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Phạm Thị Mai', '2151120346@student.tvu.edu.vn', '0908765432', 3, 1, '2151120346', 1, '2026-01-02 11:47:16', '2026-01-02 11:47:16', 0, NULL, NULL),
('ND0123', 'holyminhlu1', 'e10adc3949ba59abbe56e057f20f883e', 'Hồ Lý Minh Lữ inactive', 'holyminhlu@inactive.com', '0983149203', 3, 1, '110122123', 0, '2026-01-03 12:22:58', '2026-01-03 12:22:58', 0, NULL, NULL),
('QTHT1101', 'QTVHT1101', 'e10adc3949ba59abbe56e057f20f883e', 'Quản Trị Viên', 'holyminhlu1@gmail.com', '0983149203', 1101, NULL, NULL, 1, '2026-01-02 22:39:42', '2026-01-02 22:39:42', 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `nhatkyhethong`
--

CREATE TABLE `nhatkyhethong` (
  `MaNhatKy` varchar(20) NOT NULL,
  `ThucThe` varchar(100) NOT NULL,
  `MaThucThe` varchar(20) NOT NULL,
  `HanhDong` varchar(50) NOT NULL,
  `ThucHienBoi` varchar(20) NOT NULL,
  `ThoiGian` datetime DEFAULT current_timestamp(),
  `DuLieuTruoc` text DEFAULT NULL,
  `DuLieuSau` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `nhatkyhethong`
--

INSERT INTO `nhatkyhethong` (`MaNhatKy`, `ThucThe`, `MaThucThe`, `HanhDong`, `ThucHienBoi`, `ThoiGian`, `DuLieuTruoc`, `DuLieuSau`) VALUES
('NK0001', 'DatTruoc', 'DT006D4-TB014', 'CREATE', 'ND00000006', '2026-01-03 15:54:05', NULL, '{\"MaDatTruoc\":\"DT006D4-TB014\",\"MaNguoiYeuCau\":\"ND00000006\",\"MaLoaiThietBi\":\"LTB005\",\"NgayBatDau\":\"2026-01-04 08:00:00\",\"NgayKetThuc\":\"2026-01-04 17:00:00\",\"TrangThai\":\"Chờ duyệt\",\"NgayTao\":\"2026-01-03 15:54:05\",\"IsDeleted\":0,\"DeletedAt\":null,\"DeletedBy\":null}'),
('NK0002', 'YeuCauMuon', 'YC009', 'CREATE', 'ND00000006', '2026-01-03 15:55:17', NULL, '{\"MaYeuCau\":\"YC009\",\"MaNguoiYeuCau\":\"ND00000006\",\"NgayGui\":\"2026-01-03 15:55:17\",\"TrangThai\":\"Chờ duyệt\",\"MucDich\":\"Phục vụ giảng dạy\",\"ThoiGianBatDau\":\"2026-01-03 15:55:00\",\"ThoiGianKetThuc\":\"2026-01-03 16:55:00\",\"NguoiDuyet\":null,\"NgayDuyet\":null,\"GhiChu\":\"DS_TB:TB014\\nDD:7\",\"IsDeleted\":0,\"DeletedAt\":null,\"DeletedBy\":null}'),
('NK0003', 'BaoTri', 'BT004', 'CREATE', 'ND001', '2026-01-03 15:56:05', NULL, '{\"MaBaoTri\":\"BT004\",\"MaThietBi\":\"TB008\",\"NgayBao\":\"2026-01-03 15:56:05\",\"NgaySua\":null,\"TrangThai\":\"Đang bảo trì\",\"MaNhaCungCap\":\"Phú Diễn\",\"ChiPhi\":null,\"MoTa\":\"Loa không phát\",\"IsDeleted\":0,\"DeletedAt\":null,\"DeletedBy\":null}'),
('NK0004', 'ThietBi', 'TB008', 'UPDATE', 'ND001', '2026-01-03 15:56:05', '{\"MaThietBi\":\"TB008\",\"MaTrangThai\":1}', '{\"MaThietBi\":\"TB008\",\"MaLoaiThietBi\":\"LTB004\",\"MaTaiSan\":\"TS-LOA-002\",\"SoSerial\":\"SN-TKS-E17-002\",\"MaDiaDiem\":3,\"MaTrangThai\":3,\"NgayMua\":\"2023-02-05\",\"HanBaoHanh\":\"2025-02-05\",\"GiaMua\":\"2500000.00\",\"GhiChu\":\"Takstar E17\",\"IsDeleted\":0,\"DeletedAt\":null,\"DeletedBy\":null}');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `phieumuon`
--

CREATE TABLE `phieumuon` (
  `MaPhieu` varchar(20) NOT NULL,
  `SoPhieu` varchar(50) NOT NULL,
  `MaYeuCau` varchar(20) DEFAULT NULL,
  `MaNguoiMuon` varchar(20) NOT NULL,
  `NguoiPhatThietBi` varchar(20) NOT NULL,
  `NgayPhat` datetime DEFAULT current_timestamp(),
  `NgayPhaiTra` datetime NOT NULL,
  `NgayTraThucTe` datetime DEFAULT NULL,
  `TrangThai` varchar(50) DEFAULT 'Đang mượn',
  `TongTienPhat` decimal(10,2) DEFAULT 0.00,
  `IsDeleted` tinyint(1) NOT NULL DEFAULT 0,
  `DeletedAt` datetime DEFAULT NULL,
  `DeletedBy` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `phieumuon`
--

INSERT INTO `phieumuon` (`MaPhieu`, `SoPhieu`, `MaYeuCau`, `MaNguoiMuon`, `NguoiPhatThietBi`, `NgayPhat`, `NgayPhaiTra`, `NgayTraThucTe`, `TrangThai`, `TongTienPhat`, `IsDeleted`, `DeletedAt`, `DeletedBy`) VALUES
('PM001', 'SP001', 'YC003', 'ND00000006', 'ND001', '2026-01-02 13:41:13', '2026-01-02 20:20:00', '2026-01-02 20:41:09', 'Hoàn thành', 50000.00, 0, NULL, NULL),
('PM002', 'SP002', 'YC002', 'ND004', 'ND001', '2026-01-02 14:14:59', '2026-01-02 17:00:00', '2026-01-02 20:41:06', 'Hoàn thành', 0.00, 0, NULL, NULL),
('PM003', 'SP003', 'YC001', 'ND004', 'ND001', '2026-01-02 14:18:03', '2026-01-02 10:00:00', '2026-01-02 20:41:01', 'Hoàn thành', 0.00, 0, NULL, NULL),
('PM004', 'SP004', 'YC004', 'ND00000006', 'ND001', '2026-01-02 14:41:19', '2026-01-02 21:20:00', '2026-01-02 20:42:46', 'Hoàn thành', 0.00, 0, NULL, NULL),
('PM005', 'SP005', 'YC005', 'ND00000006', 'ND001', '2026-01-02 14:43:40', '2026-01-02 21:45:00', '2026-01-02 20:48:19', 'Hoàn thành', 50000.00, 0, NULL, NULL),
('PM006', 'SP006', 'YC006', 'ND001', 'ND001', '2026-01-02 16:31:43', '2026-01-02 23:35:00', '2026-01-02 22:31:57', 'Hoàn thành', 0.00, 0, NULL, NULL),
('PM007', 'SP007', 'DT004D5', 'ND00000006', 'ND001', '2026-01-02 16:56:26', '2026-01-03 17:00:00', '2026-01-02 22:57:09', 'Hoàn thành', 0.00, 0, NULL, NULL),
('PM008', 'SP008', 'YC007', 'ND00000006', 'ND001', '2026-01-03 06:43:41', '2026-01-02 23:55:00', '2026-01-03 12:45:26', 'Hoàn thành', 0.00, 0, NULL, NULL),
('PM009', 'SP009', 'YC008', 'ND00000006', 'ND001', '2026-01-03 06:46:37', '2026-01-03 13:50:00', '2026-01-03 15:34:35', 'Hoàn thành', 20000.00, 0, NULL, NULL),
('PM010', 'SP010', 'DT005D6', 'ND00001102', 'ND001', '2026-01-03 09:31:11', '2026-01-04 17:00:00', '2026-01-03 15:31:24', 'Hoàn thành', 0.00, 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `phieuphat`
--

CREATE TABLE `phieuphat` (
  `MaPhat` varchar(20) NOT NULL,
  `MaPhieu` varchar(20) NOT NULL,
  `MaNguoiDung` varchar(20) NOT NULL,
  `SoTien` decimal(10,2) NOT NULL,
  `LyDo` varchar(500) DEFAULT NULL,
  `DaThanhToan` tinyint(1) DEFAULT 0,
  `NgayThanhToan` datetime DEFAULT NULL,
  `NgayTao` datetime DEFAULT current_timestamp(),
  `IsDeleted` tinyint(1) NOT NULL DEFAULT 0,
  `DeletedAt` datetime DEFAULT NULL,
  `DeletedBy` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `phieuphat`
--

INSERT INTO `phieuphat` (`MaPhat`, `MaPhieu`, `MaNguoiDung`, `SoTien`, `LyDo`, `DaThanhToan`, `NgayThanhToan`, `NgayTao`, `IsDeleted`, `DeletedAt`, `DeletedBy`) VALUES
('PP-20260102-0001', 'PM001', 'ND00000006', 50000.00, 'Trả thiết bị quá hạn', 1, '2026-01-02 20:15:18', '2026-01-02 20:15:11', 0, NULL, NULL),
('PP-20260102-0002', 'PM005', 'ND00000006', 50000.00, 'Tự ý sửa chữa hoặc can thiệp thiết bị', 1, '2026-01-02 20:48:19', '2026-01-02 20:43:55', 0, NULL, NULL),
('PP-20260103-0001', 'PM009', 'ND00000006', 20000.00, 'Trả thiết bị quá hạn', 1, '2026-01-03 15:34:35', '2026-01-03 12:55:40', 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `thietbi`
--

CREATE TABLE `thietbi` (
  `MaThietBi` varchar(20) NOT NULL,
  `MaLoaiThietBi` varchar(20) NOT NULL,
  `MaTaiSan` varchar(100) DEFAULT NULL,
  `SoSerial` varchar(200) DEFAULT NULL,
  `MaDiaDiem` int(11) NOT NULL,
  `MaTrangThai` int(11) NOT NULL DEFAULT 1,
  `NgayMua` date DEFAULT NULL,
  `HanBaoHanh` date DEFAULT NULL,
  `GiaMua` decimal(15,2) DEFAULT NULL,
  `GhiChu` text DEFAULT NULL,
  `IsDeleted` tinyint(1) NOT NULL DEFAULT 0,
  `DeletedAt` datetime DEFAULT NULL,
  `DeletedBy` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `thietbi`
--

INSERT INTO `thietbi` (`MaThietBi`, `MaLoaiThietBi`, `MaTaiSan`, `SoSerial`, `MaDiaDiem`, `MaTrangThai`, `NgayMua`, `HanBaoHanh`, `GiaMua`, `GhiChu`, `IsDeleted`, `DeletedAt`, `DeletedBy`) VALUES
('TB001', 'LTB001', 'TS-MC-001', 'SN-BQ-MW612-001', 1, 3, '2023-01-15', '2026-01-15', 8500000.00, 'Máy chiếu BenQ MW612', 0, NULL, NULL),
('TB002', 'LTB001', 'TS-MC-002', 'SN-BQ-MW612-002', 1, 2, '2023-01-15', '2026-01-15', 8500000.00, 'Máy chiếu BenQ MW612', 0, NULL, NULL),
('TB003', 'LTB001', 'TS-MC-003', 'SN-BQ-MW612-003', 2, 2, '2023-01-15', '2026-01-15', 8500000.00, 'Máy chiếu BenQ MW612', 0, NULL, NULL),
('TB004', 'LTB002', 'TS-LT-001', 'SN-DELL-5520-001', 1, 2, '2023-03-20', '2026-03-20', 15000000.00, 'Dell Latitude 5520', 0, NULL, NULL),
('TB005', 'LTB002', 'TS-LT-002', 'SN-DELL-5520-002', 1, 2, '2023-03-20', '2026-03-20', 15000000.00, 'Dell Latitude 5520', 0, NULL, NULL),
('TB006', 'LTB003', 'TS-MA-001', 'SN-CANON-200D-001', 3, 2, '2022-06-10', '2024-06-10', 12000000.00, 'Canon EOS 200D', 0, NULL, NULL),
('TB007', 'LTB004', 'TS-LOA-001', 'SN-TKS-E17-001', 1, 2, '2023-02-05', '2025-02-05', 2500000.00, 'Takstar E17', 0, NULL, NULL),
('TB008', 'LTB004', 'TS-LOA-002', 'SN-TKS-E17-002', 3, 3, '2023-02-05', '2025-02-05', 2500000.00, 'Takstar E17', 0, NULL, NULL),
('TB009', 'LTB006', 'TS-CAP-001', 'SN-CAP-HDMI-001', 1, 2, '2023-05-01', '2025-05-01', 350000.00, 'Cáp HDMI 5m', 0, NULL, NULL),
('TB010', 'LTB006', 'TS-CAP-002', 'SN-CAP-MDPORT-001', 1, 2, '2023-05-01', '2025-05-01', 450000.00, 'Mini DisplayPort to HDMI', 0, NULL, NULL),
('TB011', 'LTB007', 'TS-TAI-001', 'SN-LOG-H390-001', 1, 2, '2023-04-15', '2025-04-15', 750000.00, 'Logitech H390', 0, NULL, NULL),
('TB012', 'LTB007', 'TS-TAI-002', 'SN-LOG-H390-002', 1, 2, '2023-04-15', '2025-04-15', 750000.00, 'Logitech H390', 0, NULL, NULL),
('TB013', 'LTB005', 'TS-MIC-001', 'SN-ZANSONG-M80-001', 1, 4, '2026-01-01', '2027-01-01', 795000.00, 'Loa trợ giảng không dây ZANSONG M80', 0, NULL, NULL),
('TB014', 'LTB005', 'TS-MIC-002', 'SN-ZANSONG-M80-002', 1, 1, '2026-01-01', '2027-01-01', 795000.00, 'Loa trợ giảng không dây ZANSONG M80', 0, NULL, NULL),
('TB015', 'LTB003', 'TS-MA-002', 'SN-CANON-200D-002', 5, 1, '2026-01-01', '2027-01-01', 12000000.00, 'Canon EOS 200D', 0, NULL, NULL),
('TB016', 'LTB003', 'TS-MA-003', 'SN-CANON-200D-003', 4, 1, '2026-01-01', '2027-01-01', 12000000.00, 'Canon EOS 200D', 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `thongbao`
--

CREATE TABLE `thongbao` (
  `MaThongBao` varchar(20) NOT NULL,
  `MaNguoiDung` varchar(20) NOT NULL,
  `TieuDe` varchar(200) NOT NULL,
  `NoiDung` text NOT NULL,
  `DaDoc` tinyint(1) DEFAULT 0,
  `NgayGui` datetime DEFAULT current_timestamp(),
  `Kenh` varchar(50) DEFAULT 'trong ứng dụng',
  `IsDeleted` tinyint(1) NOT NULL DEFAULT 0,
  `DeletedAt` datetime DEFAULT NULL,
  `DeletedBy` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `thongbao`
--

INSERT INTO `thongbao` (`MaThongBao`, `MaNguoiDung`, `TieuDe`, `NoiDung`, `DaDoc`, `NgayGui`, `Kenh`, `IsDeleted`, `DeletedAt`, `DeletedBy`) VALUES
('TB001', 'ND004', 'Chào mừng đến với hệ thống', 'Chào mừng bạn đến với Hệ thống mượn trả thiết bị giảng dạy Trường ĐH Trà Vinh. Vui lòng đọc kỹ quy định sử dụng thiết bị.', 0, '2026-01-02 11:47:16', 'trong ứng dụng', 0, NULL, NULL),
('TB002', 'ND005', 'Chào mừng đến với hệ thống', 'Chào mừng bạn đến với Hệ thống mượn trả thiết bị giảng dạy Trường ĐH Trà Vinh. Vui lòng đọc kỹ quy định sử dụng thiết bị.', 0, '2026-01-02 11:47:16', 'trong ứng dụng', 0, NULL, NULL),
('TB003', 'ND004', 'Yêu cầu mượn đã gửi', 'Yêu cầu mượn 2 thiết bị của bạn đã được gửi thành công. Vui lòng chờ quản trị viên phê duyệt.', 0, '2026-01-02 13:12:18', 'Hệ thống', 0, NULL, NULL),
('TB004', 'ND004', 'Yêu cầu mượn đã gửi', 'Yêu cầu mượn 1 thiết bị của bạn đã được gửi thành công. Vui lòng chờ quản trị viên phê duyệt.', 0, '2026-01-02 13:19:19', 'Hệ thống', 0, NULL, NULL),
('TB005', 'ND004', 'Đặt trước đã gửi', 'Bạn đã gửi yêu cầu đặt trước 1 thiết bị: TB002\nThời gian: 2026-01-03 08:00:00 → 2026-01-03 17:00:00\nTrạng thái: Chờ duyệt.\nĐịa điểm sử dụng: B31.101 (Khu 1)', 0, '2026-01-02 13:20:29', 'Hệ thống', 0, NULL, NULL),
('TB006', 'ND004', 'Đặt trước đã gửi', 'Bạn đã gửi yêu cầu đặt trước 1 thiết bị: TB008\nThời gian: 2026-01-03 08:00:00 → 2026-01-03 17:00:00\nTrạng thái: Chờ duyệt.\nĐịa điểm sử dụng: B31.101 (Khu 1)', 0, '2026-01-02 14:01:40', 'Hệ thống', 0, NULL, NULL),
('TB007', 'ND004', 'Đặt trước đã bị hủy', 'Bạn đã hủy yêu cầu đặt trước mã DT001D1-TB008 thành công.', 0, '2026-01-02 14:29:28', 'Hệ thống', 0, NULL, NULL),
('TB008', 'ND004', 'Đặt trước đã gửi', 'Bạn đã gửi yêu cầu đặt trước 1 thiết bị: TB011\nThời gian: 2026-01-03 07:00:00 → 2026-01-03 10:00:00\nTrạng thái: Chờ duyệt.\nĐịa điểm sử dụng: B31.101 (Khu 1)', 0, '2026-01-02 14:30:25', 'Hệ thống', 0, NULL, NULL),
('TB009', 'ND004', 'Đặt trước đã gửi', 'Bạn đã gửi yêu cầu đặt trước 2 thiết bị: TB011, TB010\nThời gian: 2026-01-03 13:00:00 → 2026-01-03 17:00:00\nTrạng thái: Chờ duyệt.\nĐịa điểm sử dụng: B31.101 (Khu 1)', 0, '2026-01-02 14:31:00', 'Hệ thống', 0, NULL, NULL),
('TB010', 'ND004', 'Đặt trước đã gửi', 'Bạn đã gửi yêu cầu đặt trước 2 thiết bị: TB012, TB010\nThời gian: 2026-01-03 07:00:00 → 2026-01-03 10:30:00\nTrạng thái: Chờ duyệt.\nĐịa điểm sử dụng: B31.101 (Khu 1)', 0, '2026-01-02 14:50:48', 'Hệ thống', 0, NULL, NULL),
('TB011', 'ND004', 'Đặt trước đã gửi', 'Bạn đã gửi yêu cầu đặt trước 2 thiết bị: TB012, TB010\nThời gian: 2026-01-03 07:00:00 → 2026-01-03 10:30:00\nTrạng thái: Chờ duyệt.\nĐịa điểm sử dụng: B31.101 (Khu 1)', 0, '2026-01-02 14:55:25', 'Hệ thống', 0, NULL, NULL),
('TB012', 'ND004', 'Đặt trước đã gửi', 'Bạn đã gửi yêu cầu đặt trước 2 thiết bị: TB012, TB010\nThời gian: 2026-01-03 13:00:00 → 2026-01-03 17:00:00\nTrạng thái: Chờ duyệt.\nĐịa điểm sử dụng: B31.101 (Khu 1)', 0, '2026-01-02 14:57:17', 'Hệ thống', 0, NULL, NULL),
('TB013', 'ND004', 'Đặt trước đã gửi', 'Bạn đã gửi yêu cầu đặt trước 2 thiết bị: TB012, TB010\nThời gian: 2026-01-03 10:00:00 → 2026-01-03 17:00:00\nTrạng thái: Chờ duyệt.\nĐịa điểm sử dụng: B31.101 (Khu 1)', 0, '2026-01-02 15:03:09', 'Hệ thống', 0, NULL, NULL),
('TB014', 'ND004', 'Đặt trước đã gửi', 'Bạn đã gửi yêu cầu đặt trước 2 thiết bị: TB012, TB010\nThời gian: 2026-01-03 10:00:00 → 2026-01-03 19:00:00\nTrạng thái: Chờ duyệt.\nĐịa điểm sử dụng: B31.101 (Khu 1)', 0, '2026-01-02 15:03:46', 'Hệ thống', 0, NULL, NULL),
('TB015', 'ND004', 'Đặt trước đã gửi', 'Bạn đã gửi yêu cầu đặt trước 2 thiết bị: TB012, TB010\nThời gian: 2026-01-03 10:00:00 → 2026-01-03 17:00:00\nTrạng thái: Chờ duyệt.\nĐịa điểm sử dụng: B31.101 (Khu 1)', 0, '2026-01-02 15:04:15', 'Hệ thống', 0, NULL, NULL),
('TB016', 'ND004', 'Đặt trước đã gửi', 'Bạn đã gửi yêu cầu đặt trước 2 thiết bị: TB012, TB010\nThời gian: 2026-01-03 10:00:00 → 2026-01-03 17:00:00\nTrạng thái: Chờ duyệt.\nĐịa điểm sử dụng: B31.101 (Khu 1)', 0, '2026-01-02 15:05:00', 'Hệ thống', 0, NULL, NULL),
('TB017', 'ND001', 'Đặt trước đã gửi', 'Bạn đã gửi yêu cầu đặt trước 2 thiết bị: TB012, TB010\nThời gian: 2026-01-03 07:00:00 → 2026-01-03 10:40:00\nTrạng thái: Chờ duyệt.\nĐịa điểm sử dụng: B31.101 (Khu 1)', 1, '2026-01-02 15:14:28', 'Hệ thống', 0, NULL, NULL),
('TB018', 'ND004', 'Yêu cầu mượn đã gửi', 'Yêu cầu mượn 2 thiết bị của bạn đã được gửi thành công. Vui lòng chờ quản trị viên phê duyệt.', 0, '2026-01-02 15:27:45', 'Hệ thống', 0, NULL, NULL),
('TB019', 'ND004', 'Yêu cầu YC001 đã được duyệt', 'Yêu cầu mượn của bạn đã được duyệt. Phiếu mượn: PM002. Vui lòng kiểm tra phần Phiếu mượn.', 0, '2026-01-02 09:33:56', 'trong ứng dụng', 0, NULL, NULL),
('TB020', 'ND004', 'Đặt trước đã gửi', 'Bạn đã gửi yêu cầu đặt trước 2 thiết bị: TB011, TB008\nThời gian: 2026-01-03 13:00:00 → 2026-01-03 17:00:00\nTrạng thái: Chờ duyệt.\nĐịa điểm sử dụng: B31.101 (Khu 1)', 0, '2026-01-02 15:37:55', 'Hệ thống', 0, NULL, NULL),
('TB021', 'ND004', 'Đặt trước đã gửi', 'Bạn đã gửi yêu cầu đặt trước 2 thiết bị: TB011, TB008\nThời gian: 2026-01-03 07:00:00 → 2026-01-03 10:00:00\nTrạng thái: Chờ duyệt.\nĐịa điểm sử dụng: B31.101 (Khu 1)', 0, '2026-01-02 15:50:56', 'Hệ thống', 0, NULL, NULL),
('TB022', 'ND004', 'Đặt trước đã gửi', 'Bạn đã gửi yêu cầu đặt trước 2 thiết bị: TB011, TB008\nThời gian: 2026-01-03 13:00:00 → 2026-01-03 17:00:00\nTrạng thái: Chờ duyệt.\nĐịa điểm sử dụng: B31.101 (Khu 1)', 0, '2026-01-02 15:51:59', 'Hệ thống', 0, NULL, NULL),
('TB023', 'ND004', 'Đặt trước đã gửi', 'Bạn đã gửi yêu cầu đặt trước 2 thiết bị: TB011, TB008\nThời gian: 2026-01-03 07:00:00 → 2026-01-03 10:30:00\nTrạng thái: Chờ duyệt.\nĐịa điểm sử dụng: B31.201 (Khu 1)', 0, '2026-01-02 15:52:36', 'Hệ thống', 0, NULL, NULL),
('TB024', 'ND004', 'Yêu cầu mượn đã gửi', 'Yêu cầu mượn 1 thiết bị của bạn đã được gửi thành công. Vui lòng chờ quản trị viên phê duyệt.', 0, '2026-01-02 15:53:30', 'Hệ thống', 0, NULL, NULL),
('TB025', 'ND004', 'Yêu cầu mượn đã gửi', 'Yêu cầu mượn 2 thiết bị của bạn đã được gửi thành công. Vui lòng chờ quản trị viên phê duyệt.', 0, '2026-01-02 15:54:36', 'Hệ thống', 0, NULL, NULL),
('TB026', 'ND004', 'Đặt trước đã được duyệt', 'Yêu cầu đặt trước mã DT003D2 đã được duyệt.', 0, '2026-01-02 19:19:14', 'Hệ thống', 0, NULL, NULL),
('TB027', 'ND00000006', 'Yêu cầu mượn đã gửi', 'Yêu cầu mượn 2 thiết bị của bạn đã được gửi thành công. Vui lòng chờ quản trị viên phê duyệt.', 1, '2026-01-02 19:21:03', 'Hệ thống', 0, NULL, NULL),
('TB028', 'ND00000006', 'Yêu cầu YC003 đã được duyệt', 'Yêu cầu mượn của bạn đã được duyệt. Phiếu mượn: PM001. Vui lòng kiểm tra phần Phiếu mượn.', 1, '2026-01-02 13:41:13', 'trong ứng dụng', 0, NULL, NULL),
('TB029', 'ND004', 'Đặt trước đã được duyệt', 'Yêu cầu đặt trước mã DT002D1 đã được duyệt.', 0, '2026-01-02 19:44:55', 'Hệ thống', 0, NULL, NULL),
('TB030', 'ND004', 'Đặt trước đã được duyệt', 'Yêu cầu đặt trước mã DT001D1 đã được duyệt.', 0, '2026-01-02 20:08:40', 'Hệ thống', 0, NULL, NULL),
('TB031', 'ND004', 'Yêu cầu YC002 đã được duyệt', 'Yêu cầu mượn của bạn đã được duyệt. Phiếu mượn: PM002. Vui lòng kiểm tra phần Phiếu mượn.', 0, '2026-01-02 14:14:59', 'trong ứng dụng', 0, NULL, NULL),
('TB032', 'ND004', 'Yêu cầu YC001 đã được duyệt', 'Yêu cầu mượn của bạn đã được duyệt. Phiếu mượn: PM003. Vui lòng kiểm tra phần Phiếu mượn.', 0, '2026-01-02 14:18:03', 'trong ứng dụng', 0, NULL, NULL),
('TB033', 'ND00000006', 'Yêu cầu mượn đã gửi', 'Yêu cầu mượn 2 thiết bị của bạn đã được gửi thành công. Vui lòng chờ quản trị viên phê duyệt.', 1, '2026-01-02 20:20:49', 'Hệ thống', 0, NULL, NULL),
('TB034', 'ND00000006', 'Yêu cầu YC004 đã được duyệt', 'Yêu cầu mượn của bạn đã được duyệt. Phiếu mượn: PM004. Vui lòng kiểm tra phần Phiếu mượn.', 1, '2026-01-02 14:41:19', 'trong ứng dụng', 0, NULL, NULL),
('TB035', 'ND00000006', 'Yêu cầu mượn đã gửi', 'Yêu cầu mượn 2 thiết bị của bạn đã được gửi thành công. Vui lòng chờ quản trị viên phê duyệt.', 1, '2026-01-02 20:43:11', 'Hệ thống', 0, NULL, NULL),
('TB036', 'ND00000006', 'Yêu cầu YC005 đã được duyệt', 'Yêu cầu mượn của bạn đã được duyệt. Phiếu mượn: PM005. Vui lòng kiểm tra phần Phiếu mượn.', 1, '2026-01-02 14:43:40', 'trong ứng dụng', 0, NULL, NULL),
('TB037', 'ND001', 'Yêu cầu mượn đã gửi', 'Yêu cầu mượn 2 thiết bị của bạn đã được gửi thành công. Vui lòng chờ quản trị viên phê duyệt.', 1, '2026-01-02 22:31:13', 'Hệ thống', 0, NULL, NULL),
('TB038', 'ND001', 'Yêu cầu YC006 đã được duyệt', 'Yêu cầu mượn của bạn đã được duyệt. Phiếu mượn: PM006. Vui lòng kiểm tra phần Phiếu mượn.', 1, '2026-01-02 16:31:43', 'trong ứng dụng', 0, NULL, NULL),
('TB039', 'ND00000006', 'Yêu cầu mượn đã gửi', 'Yêu cầu mượn 2 thiết bị của bạn đã được gửi thành công. Vui lòng chờ quản trị viên phê duyệt.', 1, '2026-01-02 22:55:47', 'Hệ thống', 0, NULL, NULL),
('TB040', 'ND00000006', 'Đặt trước đã gửi', 'Bạn đã gửi yêu cầu đặt trước 2 thiết bị: TB008, TB001\nThời gian: 2026-01-03 08:00:00 → 2026-01-03 17:00:00\nTrạng thái: Chờ duyệt.\nĐịa điểm sử dụng: D31.101 (Khu 1)', 1, '2026-01-02 22:56:18', 'Hệ thống', 0, NULL, NULL),
('TB041', 'ND00000006', 'Đặt trước đã được duyệt', 'Yêu cầu đặt trước mã DT004D5 đã được duyệt.', 1, '2026-01-02 22:56:26', 'Hệ thống', 0, NULL, NULL),
('TB042', 'ND00000006', 'Yêu cầu YC007 đã được duyệt', 'Yêu cầu mượn của bạn đã được duyệt. Phiếu mượn: PM008. Vui lòng kiểm tra phần Phiếu mượn.', 1, '2026-01-03 06:43:41', 'trong ứng dụng', 0, NULL, NULL),
('TB043', 'ND00000006', 'Yêu cầu mượn đã gửi', 'Yêu cầu mượn 1 thiết bị của bạn đã được gửi thành công. Vui lòng chờ quản trị viên phê duyệt.', 1, '2026-01-03 12:46:25', 'Hệ thống', 0, NULL, NULL),
('TB044', 'ND00000006', 'Yêu cầu YC008 đã được duyệt', 'Yêu cầu mượn của bạn đã được duyệt. Phiếu mượn: PM009. Vui lòng kiểm tra phần Phiếu mượn.', 1, '2026-01-03 06:46:37', 'trong ứng dụng', 0, NULL, NULL),
('TB045', 'ND00000006', 'Phiếu phạt mới', 'Bạn vừa bị lập phiếu phạt PP-20260103-0001 cho phiếu mượn SP009.\nThiết bị: TB001.\nSố tiền: 20.000 VNĐ.\nLý do: Trả thiết bị quá hạn.\nVui lòng thanh toán theo hướng dẫn của nhà trường/Phòng quản lý thiết bị.', 1, '2026-01-03 12:55:40', 'Hệ thống', 0, NULL, NULL),
('TB046', 'ND00001102', 'Đặt trước đã gửi', 'Bạn đã gửi yêu cầu đặt trước 2 thiết bị: TB008, TB014\nThời gian: 2026-01-04 08:00:00 → 2026-01-04 17:00:00\nTrạng thái: Chờ duyệt.\nĐịa điểm sử dụng: D31.103 (Khu 1)', 0, '2026-01-03 15:30:40', 'Hệ thống', 0, NULL, NULL),
('TB047', 'ND00001102', 'Đặt trước đã được duyệt', 'Yêu cầu đặt trước mã DT005D6 đã được duyệt.', 0, '2026-01-03 15:31:11', 'Hệ thống', 0, NULL, NULL),
('TB048', 'ND00000006', 'Đặt trước đã gửi', 'Bạn đã gửi yêu cầu đặt trước 1 thiết bị: TB014\nThời gian: 2026-01-04 08:00:00 → 2026-01-04 17:00:00\nTrạng thái: Chờ duyệt.\nĐịa điểm sử dụng: A42.201 (Khu 2)', 0, '2026-01-03 15:54:05', 'Hệ thống', 0, NULL, NULL),
('TB049', 'ND00000006', 'Yêu cầu mượn đã gửi', 'Yêu cầu mượn 1 thiết bị của bạn đã được gửi thành công. Vui lòng chờ quản trị viên phê duyệt.', 0, '2026-01-03 15:55:17', 'Hệ thống', 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `trangthaithietbi`
--

CREATE TABLE `trangthaithietbi` (
  `MaTrangThai` int(11) NOT NULL,
  `TenTrangThai` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `trangthaithietbi`
--

INSERT INTO `trangthaithietbi` (`MaTrangThai`, `TenTrangThai`) VALUES
(1, 'Khả dụng'),
(2, 'Đang được mượn'),
(3, 'Đang bảo trì'),
(4, 'Hỏng'),
(5, 'Đã thanh lý');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `vaitro`
--

CREATE TABLE `vaitro` (
  `MaVaiTro` int(11) NOT NULL,
  `TenVaiTro` varchar(50) NOT NULL,
  `IsDeleted` tinyint(1) NOT NULL DEFAULT 0,
  `DeletedAt` datetime DEFAULT NULL,
  `DeletedBy` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `vaitro`
--

INSERT INTO `vaitro` (`MaVaiTro`, `TenVaiTro`, `IsDeleted`, `DeletedAt`, `DeletedBy`) VALUES
(1, 'Admin', 0, NULL, NULL),
(2, 'Giảng viên', 0, NULL, NULL),
(3, 'Sinh viên', 0, NULL, NULL),
(1101, 'Quản trị hệ thống', 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `yeucaumuon`
--

CREATE TABLE `yeucaumuon` (
  `MaYeuCau` varchar(20) NOT NULL,
  `MaNguoiYeuCau` varchar(20) NOT NULL,
  `NgayGui` datetime DEFAULT current_timestamp(),
  `TrangThai` varchar(50) DEFAULT 'Chờ duyệt',
  `MucDich` text DEFAULT NULL,
  `ThoiGianBatDau` datetime DEFAULT NULL,
  `ThoiGianKetThuc` datetime DEFAULT NULL,
  `NguoiDuyet` varchar(20) DEFAULT NULL,
  `NgayDuyet` datetime DEFAULT NULL,
  `GhiChu` varchar(500) DEFAULT NULL,
  `IsDeleted` tinyint(1) NOT NULL DEFAULT 0,
  `DeletedAt` datetime DEFAULT NULL,
  `DeletedBy` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `yeucaumuon`
--

INSERT INTO `yeucaumuon` (`MaYeuCau`, `MaNguoiYeuCau`, `NgayGui`, `TrangThai`, `MucDich`, `ThoiGianBatDau`, `ThoiGianKetThuc`, `NguoiDuyet`, `NgayDuyet`, `GhiChu`, `IsDeleted`, `DeletedAt`, `DeletedBy`) VALUES
('DT004D5', 'ND00000006', '2026-01-02 16:56:26', 'Đã duyệt', 'Đặt trước thiết bị', '2026-01-03 08:00:00', '2026-01-03 17:00:00', 'ND001', '2026-01-02 16:56:26', 'DS_TB:TB001,TB008\nDD:5\nFROM_DT:1', 0, NULL, NULL),
('DT005D6', 'ND00001102', '2026-01-03 09:31:11', 'Đã duyệt', 'Đặt trước thiết bị', '2026-01-04 08:00:00', '2026-01-04 17:00:00', 'ND001', '2026-01-03 09:31:11', 'DS_TB:TB008,TB014\nDD:6\nFROM_DT:1', 0, NULL, NULL),
('YC001', 'ND004', '2026-01-02 15:53:30', 'Đã duyệt', 'Phục vụ giảng dạy', '2026-01-02 07:00:00', '2026-01-02 10:00:00', 'ND001', '2026-01-02 20:18:03', 'DS_TB:TB009\nDD:3', 0, NULL, NULL),
('YC002', 'ND004', '2026-01-02 15:54:36', 'Đã duyệt', 'Phục vụ giảng dạy', '2026-01-02 13:00:00', '2026-01-02 17:00:00', 'ND001', '2026-01-02 20:14:59', 'DS_TB:TB007,TB005\nDD:3', 0, NULL, NULL),
('YC003', 'ND00000006', '2026-01-02 19:21:03', 'Đã duyệt', 'Phục vụ giảng dạy', '2026-01-02 19:20:00', '2026-01-02 20:20:00', 'ND001', '2026-01-02 19:41:13', 'DS_TB:TB011,TB007\nDD:1', 0, NULL, NULL),
('YC004', 'ND00000006', '2026-01-02 20:20:49', 'Đã duyệt', 'Phục vụ giảng dạy', '2026-01-02 20:20:00', '2026-01-02 21:20:00', 'ND001', '2026-01-02 20:41:19', 'DS_TB:TB006,TB001\nDD:2', 0, NULL, NULL),
('YC005', 'ND00000006', '2026-01-02 20:43:11', 'Đã duyệt', 'Phục vụ giảng dạy', '2026-01-02 20:45:00', '2026-01-02 21:45:00', 'ND001', '2026-01-02 20:43:40', 'DS_TB:TB004,TB002\nDD:4', 0, NULL, NULL),
('YC006', 'ND001', '2026-01-02 22:31:13', 'Đã duyệt', 'Phục vụ giảng dạy', '2026-01-02 22:35:00', '2026-01-02 23:35:00', 'ND001', '2026-01-02 22:31:43', 'DS_TB:TB008,TB001\nDD:5', 0, NULL, NULL),
('YC007', 'ND00000006', '2026-01-02 22:55:47', 'Đã duyệt', 'Phục vụ giảng dạy', '2026-01-02 22:55:00', '2026-01-02 23:55:00', 'ND001', '2026-01-03 12:43:41', 'DS_TB:TB008,TB001\nDD:6', 0, NULL, NULL),
('YC008', 'ND00000006', '2026-01-03 12:46:25', 'Đã duyệt', 'Phục vụ giảng dạy', '2026-01-03 12:50:00', '2026-01-03 13:50:00', 'ND001', '2026-01-03 12:46:37', 'DS_TB:TB001\nDD:8', 0, NULL, NULL),
('YC009', 'ND00000006', '2026-01-03 15:55:17', 'Chờ duyệt', 'Phục vụ giảng dạy', '2026-01-03 15:55:00', '2026-01-03 16:55:00', NULL, NULL, 'DS_TB:TB014\nDD:7', 0, NULL, NULL);

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `baotri`
--
ALTER TABLE `baotri`
  ADD PRIMARY KEY (`MaBaoTri`),
  ADD KEY `MaThietBi` (`MaThietBi`);

--
-- Chỉ mục cho bảng `chitietmuon`
--
ALTER TABLE `chitietmuon`
  ADD PRIMARY KEY (`MaChiTiet`),
  ADD KEY `MaPhieu` (`MaPhieu`),
  ADD KEY `MaThietBi` (`MaThietBi`);

--
-- Chỉ mục cho bảng `dattruoc`
--
ALTER TABLE `dattruoc`
  ADD PRIMARY KEY (`MaDatTruoc`),
  ADD KEY `MaNguoiYeuCau` (`MaNguoiYeuCau`),
  ADD KEY `MaLoaiThietBi` (`MaLoaiThietBi`);

--
-- Chỉ mục cho bảng `diadiem`
--
ALTER TABLE `diadiem`
  ADD PRIMARY KEY (`MaDiaDiem`);

--
-- Chỉ mục cho bảng `khoaphongban`
--
ALTER TABLE `khoaphongban`
  ADD PRIMARY KEY (`MaKhoa`);

--
-- Chỉ mục cho bảng `loaithietbi`
--
ALTER TABLE `loaithietbi`
  ADD PRIMARY KEY (`MaLoaiThietBi`);

--
-- Chỉ mục cho bảng `nguoidung`
--
ALTER TABLE `nguoidung`
  ADD PRIMARY KEY (`MaNguoiDung`),
  ADD UNIQUE KEY `TenDangNhap` (`TenDangNhap`),
  ADD UNIQUE KEY `Email` (`Email`),
  ADD KEY `MaVaiTro` (`MaVaiTro`),
  ADD KEY `MaKhoa` (`MaKhoa`);

--
-- Chỉ mục cho bảng `nhatkyhethong`
--
ALTER TABLE `nhatkyhethong`
  ADD PRIMARY KEY (`MaNhatKy`),
  ADD KEY `ThucHienBoi` (`ThucHienBoi`);

--
-- Chỉ mục cho bảng `phieumuon`
--
ALTER TABLE `phieumuon`
  ADD PRIMARY KEY (`MaPhieu`),
  ADD UNIQUE KEY `SoPhieu` (`SoPhieu`),
  ADD KEY `MaYeuCau` (`MaYeuCau`),
  ADD KEY `MaNguoiMuon` (`MaNguoiMuon`),
  ADD KEY `NguoiPhatThietBi` (`NguoiPhatThietBi`);

--
-- Chỉ mục cho bảng `phieuphat`
--
ALTER TABLE `phieuphat`
  ADD PRIMARY KEY (`MaPhat`),
  ADD KEY `MaPhieu` (`MaPhieu`),
  ADD KEY `MaNguoiDung` (`MaNguoiDung`);

--
-- Chỉ mục cho bảng `thietbi`
--
ALTER TABLE `thietbi`
  ADD PRIMARY KEY (`MaThietBi`),
  ADD UNIQUE KEY `MaTaiSan` (`MaTaiSan`),
  ADD KEY `MaLoaiThietBi` (`MaLoaiThietBi`),
  ADD KEY `MaDiaDiem` (`MaDiaDiem`),
  ADD KEY `MaTrangThai` (`MaTrangThai`);

--
-- Chỉ mục cho bảng `thongbao`
--
ALTER TABLE `thongbao`
  ADD PRIMARY KEY (`MaThongBao`),
  ADD KEY `MaNguoiDung` (`MaNguoiDung`);

--
-- Chỉ mục cho bảng `trangthaithietbi`
--
ALTER TABLE `trangthaithietbi`
  ADD PRIMARY KEY (`MaTrangThai`);

--
-- Chỉ mục cho bảng `vaitro`
--
ALTER TABLE `vaitro`
  ADD PRIMARY KEY (`MaVaiTro`);

--
-- Chỉ mục cho bảng `yeucaumuon`
--
ALTER TABLE `yeucaumuon`
  ADD PRIMARY KEY (`MaYeuCau`),
  ADD KEY `MaNguoiYeuCau` (`MaNguoiYeuCau`),
  ADD KEY `NguoiDuyet` (`NguoiDuyet`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `diadiem`
--
ALTER TABLE `diadiem`
  MODIFY `MaDiaDiem` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT cho bảng `khoaphongban`
--
ALTER TABLE `khoaphongban`
  MODIFY `MaKhoa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `baotri`
--
ALTER TABLE `baotri`
  ADD CONSTRAINT `baotri_ibfk_1` FOREIGN KEY (`MaThietBi`) REFERENCES `thietbi` (`MaThietBi`);

--
-- Các ràng buộc cho bảng `chitietmuon`
--
ALTER TABLE `chitietmuon`
  ADD CONSTRAINT `chitietmuon_ibfk_1` FOREIGN KEY (`MaPhieu`) REFERENCES `phieumuon` (`MaPhieu`),
  ADD CONSTRAINT `chitietmuon_ibfk_2` FOREIGN KEY (`MaThietBi`) REFERENCES `thietbi` (`MaThietBi`);

--
-- Các ràng buộc cho bảng `dattruoc`
--
ALTER TABLE `dattruoc`
  ADD CONSTRAINT `dattruoc_ibfk_1` FOREIGN KEY (`MaNguoiYeuCau`) REFERENCES `nguoidung` (`MaNguoiDung`),
  ADD CONSTRAINT `dattruoc_ibfk_2` FOREIGN KEY (`MaLoaiThietBi`) REFERENCES `loaithietbi` (`MaLoaiThietBi`);

--
-- Các ràng buộc cho bảng `nguoidung`
--
ALTER TABLE `nguoidung`
  ADD CONSTRAINT `nguoidung_ibfk_1` FOREIGN KEY (`MaVaiTro`) REFERENCES `vaitro` (`MaVaiTro`),
  ADD CONSTRAINT `nguoidung_ibfk_2` FOREIGN KEY (`MaKhoa`) REFERENCES `khoaphongban` (`MaKhoa`);

--
-- Các ràng buộc cho bảng `nhatkyhethong`
--
ALTER TABLE `nhatkyhethong`
  ADD CONSTRAINT `nhatkyhethong_ibfk_1` FOREIGN KEY (`ThucHienBoi`) REFERENCES `nguoidung` (`MaNguoiDung`);

--
-- Các ràng buộc cho bảng `phieumuon`
--
ALTER TABLE `phieumuon`
  ADD CONSTRAINT `phieumuon_ibfk_1` FOREIGN KEY (`MaYeuCau`) REFERENCES `yeucaumuon` (`MaYeuCau`),
  ADD CONSTRAINT `phieumuon_ibfk_2` FOREIGN KEY (`MaNguoiMuon`) REFERENCES `nguoidung` (`MaNguoiDung`),
  ADD CONSTRAINT `phieumuon_ibfk_3` FOREIGN KEY (`NguoiPhatThietBi`) REFERENCES `nguoidung` (`MaNguoiDung`);

--
-- Các ràng buộc cho bảng `phieuphat`
--
ALTER TABLE `phieuphat`
  ADD CONSTRAINT `phieuphat_ibfk_1` FOREIGN KEY (`MaPhieu`) REFERENCES `phieumuon` (`MaPhieu`),
  ADD CONSTRAINT `phieuphat_ibfk_2` FOREIGN KEY (`MaNguoiDung`) REFERENCES `nguoidung` (`MaNguoiDung`);

--
-- Các ràng buộc cho bảng `thietbi`
--
ALTER TABLE `thietbi`
  ADD CONSTRAINT `thietbi_ibfk_1` FOREIGN KEY (`MaLoaiThietBi`) REFERENCES `loaithietbi` (`MaLoaiThietBi`),
  ADD CONSTRAINT `thietbi_ibfk_2` FOREIGN KEY (`MaDiaDiem`) REFERENCES `diadiem` (`MaDiaDiem`),
  ADD CONSTRAINT `thietbi_ibfk_3` FOREIGN KEY (`MaTrangThai`) REFERENCES `trangthaithietbi` (`MaTrangThai`);

--
-- Các ràng buộc cho bảng `thongbao`
--
ALTER TABLE `thongbao`
  ADD CONSTRAINT `thongbao_ibfk_1` FOREIGN KEY (`MaNguoiDung`) REFERENCES `nguoidung` (`MaNguoiDung`);

--
-- Các ràng buộc cho bảng `yeucaumuon`
--
ALTER TABLE `yeucaumuon`
  ADD CONSTRAINT `yeucaumuon_ibfk_1` FOREIGN KEY (`MaNguoiYeuCau`) REFERENCES `nguoidung` (`MaNguoiDung`),
  ADD CONSTRAINT `yeucaumuon_ibfk_2` FOREIGN KEY (`NguoiDuyet`) REFERENCES `nguoidung` (`MaNguoiDung`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
