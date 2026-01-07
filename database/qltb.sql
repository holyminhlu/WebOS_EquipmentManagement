-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th1 07, 2026 lúc 04:07 AM
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
('DT001D1-TB008', 'ND004', 'LTB004', '2026-01-03 07:00:00', '2026-01-03 10:00:00', 'Chờ duyệt', '2026-01-02 15:50:56', 0, NULL, NULL),
('DT001D1-TB011', 'ND004', 'LTB007', '2026-01-03 07:00:00', '2026-01-03 10:00:00', 'Chờ duyệt', '2026-01-02 15:50:56', 0, NULL, NULL),
('DT002D1-TB008', 'ND004', 'LTB004', '2026-01-03 13:00:00', '2026-01-03 17:00:00', 'Chờ duyệt', '2026-01-02 15:51:59', 0, NULL, NULL),
('DT002D1-TB011', 'ND004', 'LTB007', '2026-01-03 13:00:00', '2026-01-03 17:00:00', 'Chờ duyệt', '2026-01-02 15:51:59', 0, NULL, NULL),
('DT003D2-TB008', 'ND004', 'LTB004', '2026-01-03 07:00:00', '2026-01-03 10:30:00', 'Chờ duyệt', '2026-01-02 15:52:36', 0, NULL, NULL),
('DT003D2-TB011', 'ND004', 'LTB007', '2026-01-03 07:00:00', '2026-01-03 10:30:00', 'Chờ duyệt', '2026-01-02 15:52:36', 0, NULL, NULL);

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
(5, 'A42.102', 'Dãy A4, Tầng 1, Phòng 2', 'Trần Minh D', 0, NULL, NULL, '2'),
(6, 'A42.103', 'Dãy A4, Tầng 1, Phòng 3', 'Trần Minh D', 0, NULL, NULL, '2'),
(7, 'A42.104', 'Dãy A4, Tầng 1, Phòng 4', 'Trần Minh D', 0, NULL, NULL, '2'),
(8, 'A42.105', 'Dãy A4, Tầng 1, Phòng 5', 'Trần Minh D', 0, NULL, NULL, '2'),
(9, 'A42.106', 'Dãy A4, Tầng 1, Phòng 6', 'Trần Minh D', 0, NULL, NULL, '2'),
(10, 'A42.107', 'Dãy A4, Tầng 1, Phòng 7', 'Trần Minh D', 0, NULL, NULL, '2'),
(11, 'A42.108', 'Dãy A4, Tầng 1, Phòng 8', 'Trần Minh D', 0, NULL, NULL, '2'),
(12, 'A42.109', 'Dãy A4, Tầng 1, Phòng 9', 'Trần Minh D', 0, NULL, NULL, '2'),
(13, 'A42.110', 'Dãy A4, Tầng 1, Phòng 10', 'Trần Minh D', 0, NULL, NULL, '2'),
(15, 'A42.111', 'Dãy A4, Tầng 1, Phòng 11', 'Trần Minh D', 0, NULL, NULL, '2'),
(16, 'A42.112', 'Dãy A4, Tầng 1, Phòng 12', 'Trần Minh D', 0, NULL, NULL, '2'),
(17, 'A42.113', 'Dãy A4, Tầng 1, Phòng 13', 'Trần Minh D', 0, NULL, NULL, '2'),
(18, 'B21.101', 'Dãy B2, Tầng 1, Phòng 1', 'Nguyễn Văn A', 0, NULL, NULL, '1'),
(19, 'B21.102', 'Dãy B2, Tầng 1, Phòng 2', 'Nguyễn Văn A', 0, NULL, NULL, '1'),
(20, 'B21.103', 'Dãy B2, Tầng 1, Phòng 3', 'Nguyễn Văn A', 0, NULL, NULL, '1'),
(21, 'B21.104', 'Dãy B2, Tầng 1, Phòng 4', 'Nguyễn Văn A', 0, NULL, NULL, '1'),
(22, 'B21.105', 'Dãy B2, Tầng 1, Phòng 5', 'Nguyễn Văn A', 0, NULL, NULL, '1'),
(23, 'B21.106', 'Dãy B2, Tầng 1, Phòng 6', 'Nguyễn Văn A', 0, NULL, NULL, '1'),
(24, 'B21.107', 'Dãy B2, Tầng 1, Phòng 7', 'Nguyễn Văn A', 0, NULL, NULL, '1'),
(25, 'B21.108', 'Dãy B2, Tầng 1, Phòng 8', 'Nguyễn Văn A', 0, NULL, NULL, '1'),
(26, 'B21.201', 'Dãy B2, Tầng 1, Phòng 1', 'Nguyễn Văn A', 0, NULL, NULL, '1'),
(27, 'B21.202', 'Dãy B2, Tầng 1, Phòng 2', 'Nguyễn Văn A', 0, NULL, NULL, '1'),
(28, 'B21.203', 'Dãy B2, Tầng 1, Phòng 3', 'Nguyễn Văn A', 0, NULL, NULL, '1'),
(29, 'B21.204', 'Dãy B2, Tầng 1, Phòng 4', 'Nguyễn Văn A', 0, NULL, NULL, '1'),
(30, 'B21.205', 'Dãy B2, Tầng 1, Phòng 5', 'Nguyễn Văn A', 0, NULL, NULL, '1'),
(31, 'B21.206', 'Dãy B2, Tầng 1, Phòng 6', 'Nguyễn Văn A', 0, NULL, NULL, '1'),
(32, 'B21.207', 'Dãy B2, Tầng 1, Phòng 7', 'Nguyễn Văn A', 0, NULL, NULL, '1'),
(33, 'B21.208', 'Dãy B2, Tầng 1, Phòng 8', 'Nguyễn Văn A', 0, NULL, NULL, '1');

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
('ND001', 'admin', '827ccb0eea8a706c4c34a16891f84e7b', 'Quản trị viên', 'admin@tvu.edu.vn', '0123456789', 1, 5, NULL, 1, '2026-01-02 11:47:16', '2026-01-02 15:13:17', 0, NULL, NULL),
('ND002', 'gv001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nguyễn Văn Giảng', 'giang.nv@tvu.edu.vn', '0987654321', 2, 1, NULL, 1, '2026-01-02 11:47:16', '2026-01-02 11:47:16', 0, NULL, NULL),
('ND003', 'gv002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Trần Thị Hoa', 'hoa.tt@tvu.edu.vn', '0912345678', 2, 4, NULL, 1, '2026-01-02 11:47:16', '2026-01-02 11:47:16', 0, NULL, NULL),
('ND004', 'sv001', '827ccb0eea8a706c4c34a16891f84e7b', 'Lê Văn Sinh', '2151120345@student.tvu.edu.vn', '0909123456', 3, 1, '2151120345', 1, '2026-01-02 11:47:16', '2026-01-02 11:49:09', 0, NULL, NULL),
('ND005', 'sv002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Phạm Thị Mai', '2151120346@student.tvu.edu.vn', '0908765432', 3, 1, '2151120346', 1, '2026-01-02 11:47:16', '2026-01-02 11:47:16', 0, NULL, NULL);

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
('TB001', 'LTB001', 'TS-MC-001', 'SN-BQ-MW612-001', 1, 1, '2023-01-15', '2026-01-15', 8500000.00, 'Máy chiếu BenQ MW612', 0, NULL, NULL),
('TB002', 'LTB001', 'TS-MC-002', 'SN-BQ-MW612-002', 1, 1, '2023-01-15', '2026-01-15', 8500000.00, 'Máy chiếu BenQ MW612', 0, NULL, NULL),
('TB003', 'LTB001', 'TS-MC-003', 'SN-BQ-MW612-003', 2, 2, '2023-01-15', '2026-01-15', 8500000.00, 'Máy chiếu BenQ MW612', 0, NULL, NULL),
('TB004', 'LTB002', 'TS-LT-001', 'SN-DELL-5520-001', 1, 1, '2023-03-20', '2026-03-20', 15000000.00, 'Dell Latitude 5520', 0, NULL, NULL),
('TB005', 'LTB002', 'TS-LT-002', 'SN-DELL-5520-002', 1, 1, '2023-03-20', '2026-03-20', 15000000.00, 'Dell Latitude 5520', 0, NULL, NULL),
('TB006', 'LTB003', 'TS-MA-001', 'SN-CANON-200D-001', 3, 1, '2022-06-10', '2024-06-10', 12000000.00, 'Canon EOS 200D', 0, NULL, NULL),
('TB007', 'LTB004', 'TS-LOA-001', 'SN-TKS-E17-001', 1, 1, '2023-02-05', '2025-02-05', 2500000.00, 'Takstar E17', 0, NULL, NULL),
('TB008', 'LTB004', 'TS-LOA-002', 'SN-TKS-E17-002', 3, 1, '2023-02-05', '2025-02-05', 2500000.00, 'Takstar E17', 0, NULL, NULL),
('TB009', 'LTB006', 'TS-CAP-001', 'SN-CAP-HDMI-001', 1, 1, '2023-05-01', '2025-05-01', 350000.00, 'Cáp HDMI 5m', 0, NULL, NULL),
('TB010', 'LTB006', 'TS-CAP-002', 'SN-CAP-MDPORT-001', 1, 2, '2023-05-01', '2025-05-01', 450000.00, 'Mini DisplayPort to HDMI', 0, NULL, NULL),
('TB011', 'LTB007', 'TS-TAI-001', 'SN-LOG-H390-001', 1, 1, '2023-04-15', '2025-04-15', 750000.00, 'Logitech H390', 0, NULL, NULL),
('TB012', 'LTB007', 'TS-TAI-002', 'SN-LOG-H390-002', 1, 2, '2023-04-15', '2025-04-15', 750000.00, 'Logitech H390', 0, NULL, NULL);

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
('TB017', 'ND001', 'Đặt trước đã gửi', 'Bạn đã gửi yêu cầu đặt trước 2 thiết bị: TB012, TB010\nThời gian: 2026-01-03 07:00:00 → 2026-01-03 10:40:00\nTrạng thái: Chờ duyệt.\nĐịa điểm sử dụng: B31.101 (Khu 1)', 0, '2026-01-02 15:14:28', 'Hệ thống', 0, NULL, NULL),
('TB018', 'ND004', 'Yêu cầu mượn đã gửi', 'Yêu cầu mượn 2 thiết bị của bạn đã được gửi thành công. Vui lòng chờ quản trị viên phê duyệt.', 0, '2026-01-02 15:27:45', 'Hệ thống', 0, NULL, NULL),
('TB019', 'ND004', 'Yêu cầu YC001 đã được duyệt', 'Yêu cầu mượn của bạn đã được duyệt. Phiếu mượn: PM002. Vui lòng kiểm tra phần Phiếu mượn.', 0, '2026-01-02 09:33:56', 'trong ứng dụng', 0, NULL, NULL),
('TB020', 'ND004', 'Đặt trước đã gửi', 'Bạn đã gửi yêu cầu đặt trước 2 thiết bị: TB011, TB008\nThời gian: 2026-01-03 13:00:00 → 2026-01-03 17:00:00\nTrạng thái: Chờ duyệt.\nĐịa điểm sử dụng: B31.101 (Khu 1)', 0, '2026-01-02 15:37:55', 'Hệ thống', 0, NULL, NULL),
('TB021', 'ND004', 'Đặt trước đã gửi', 'Bạn đã gửi yêu cầu đặt trước 2 thiết bị: TB011, TB008\nThời gian: 2026-01-03 07:00:00 → 2026-01-03 10:00:00\nTrạng thái: Chờ duyệt.\nĐịa điểm sử dụng: B31.101 (Khu 1)', 0, '2026-01-02 15:50:56', 'Hệ thống', 0, NULL, NULL),
('TB022', 'ND004', 'Đặt trước đã gửi', 'Bạn đã gửi yêu cầu đặt trước 2 thiết bị: TB011, TB008\nThời gian: 2026-01-03 13:00:00 → 2026-01-03 17:00:00\nTrạng thái: Chờ duyệt.\nĐịa điểm sử dụng: B31.101 (Khu 1)', 0, '2026-01-02 15:51:59', 'Hệ thống', 0, NULL, NULL),
('TB023', 'ND004', 'Đặt trước đã gửi', 'Bạn đã gửi yêu cầu đặt trước 2 thiết bị: TB011, TB008\nThời gian: 2026-01-03 07:00:00 → 2026-01-03 10:30:00\nTrạng thái: Chờ duyệt.\nĐịa điểm sử dụng: B31.201 (Khu 1)', 0, '2026-01-02 15:52:36', 'Hệ thống', 0, NULL, NULL),
('TB024', 'ND004', 'Yêu cầu mượn đã gửi', 'Yêu cầu mượn 1 thiết bị của bạn đã được gửi thành công. Vui lòng chờ quản trị viên phê duyệt.', 0, '2026-01-02 15:53:30', 'Hệ thống', 0, NULL, NULL),
('TB025', 'ND004', 'Yêu cầu mượn đã gửi', 'Yêu cầu mượn 2 thiết bị của bạn đã được gửi thành công. Vui lòng chờ quản trị viên phê duyệt.', 0, '2026-01-02 15:54:36', 'Hệ thống', 0, NULL, NULL);

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
(3, 'Sinh viên', 0, NULL, NULL);

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
('YC001', 'ND004', '2026-01-02 15:53:30', 'Chờ duyệt', 'Phục vụ giảng dạy', '2026-01-02 07:00:00', '2026-01-02 10:00:00', NULL, NULL, 'DS_TB:TB009\nDD:3', 0, NULL, NULL),
('YC002', 'ND004', '2026-01-02 15:54:36', 'Chờ duyệt', 'Phục vụ giảng dạy', '2026-01-02 13:00:00', '2026-01-02 17:00:00', NULL, NULL, 'DS_TB:TB007,TB005\nDD:3', 0, NULL, NULL);

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
  MODIFY `MaDiaDiem` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

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
