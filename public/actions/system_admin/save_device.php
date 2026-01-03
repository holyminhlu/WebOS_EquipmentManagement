<?php
/**
 * System admin: create/update device
 */

require_once __DIR__ . '/_guard.php';
require_once __DIR__ . '/../../../includes/db.php';

$mode = isset($_POST['mode']) ? trim((string)$_POST['mode']) : 'create';
$maThietBi = isset($_POST['maThietBi']) ? trim((string)$_POST['maThietBi']) : '';
$maLoaiThietBi = isset($_POST['maLoaiThietBi']) ? trim((string)$_POST['maLoaiThietBi']) : '';
$maTaiSan = isset($_POST['maTaiSan']) ? trim((string)$_POST['maTaiSan']) : '';
$soSerial = isset($_POST['soSerial']) ? trim((string)$_POST['soSerial']) : '';
$maDiaDiemRaw = isset($_POST['maDiaDiem']) ? trim((string)$_POST['maDiaDiem']) : '';
$maTrangThaiRaw = isset($_POST['maTrangThai']) ? trim((string)$_POST['maTrangThai']) : '';
$ngayMua = isset($_POST['ngayMua']) ? trim((string)$_POST['ngayMua']) : '';
$hanBaoHanh = isset($_POST['hanBaoHanh']) ? trim((string)$_POST['hanBaoHanh']) : '';
$giaMuaRaw = isset($_POST['giaMua']) ? trim((string)$_POST['giaMua']) : '';
$ghiChu = isset($_POST['ghiChu']) ? trim((string)$_POST['ghiChu']) : '';

if ($maThietBi === '' || $maLoaiThietBi === '' || $maDiaDiemRaw === '' || $maTrangThaiRaw === '') {
    echo json_encode(['success' => false, 'message' => 'Vui lòng nhập đầy đủ thông tin bắt buộc']);
    exit;
}

if (!ctype_digit($maDiaDiemRaw) || !ctype_digit($maTrangThaiRaw)) {
    echo json_encode(['success' => false, 'message' => 'Địa điểm hoặc trạng thái không hợp lệ']);
    exit;
}

$maDiaDiem = (int)$maDiaDiemRaw;
$maTrangThai = (int)$maTrangThaiRaw;

$giaMua = null;
if ($giaMuaRaw !== '') {
    if (!is_numeric($giaMuaRaw) || (float)$giaMuaRaw < 0) {
        echo json_encode(['success' => false, 'message' => 'Giá mua không hợp lệ']);
        exit;
    }
    $giaMua = (float)$giaMuaRaw;
}

$ngayMuaDb = ($ngayMua !== '') ? $ngayMua : null;
$hanBaoHanhDb = ($hanBaoHanh !== '') ? $hanBaoHanh : null;
$ghiChuDb = ($ghiChu !== '') ? mb_substr($ghiChu, 0, 500, 'UTF-8') : null;

try {
    // Validate referenced records exist
    $ltb = dbQueryOne("SELECT MaLoaiThietBi FROM `loaithietbi` WHERE IsDeleted = 0 AND MaLoaiThietBi = ? LIMIT 1", [$maLoaiThietBi]);
    if (!$ltb) {
        echo json_encode(['success' => false, 'message' => 'Loại thiết bị không tồn tại']);
        exit;
    }

    $dd = dbQueryOne("SELECT MaDiaDiem FROM `diadiem` WHERE IsDeleted = 0 AND MaDiaDiem = ? LIMIT 1", [$maDiaDiem]);
    if (!$dd) {
        echo json_encode(['success' => false, 'message' => 'Địa điểm không tồn tại']);
        exit;
    }

    $tt = dbQueryOne("SELECT MaTrangThai FROM `trangthaithietbi` WHERE MaTrangThai = ? LIMIT 1", [$maTrangThai]);
    if (!$tt) {
        echo json_encode(['success' => false, 'message' => 'Trạng thái thiết bị không tồn tại']);
        exit;
    }

    if ($mode === 'edit') {
        $existing = dbQueryOne("SELECT MaThietBi FROM `thietbi` WHERE IsDeleted = 0 AND MaThietBi = ? LIMIT 1", [$maThietBi]);
        if (!$existing) {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy thiết bị']);
            exit;
        }

        // Unique MaTaiSan if provided
        if ($maTaiSan !== '') {
            $dup = dbQueryOne(
                "SELECT MaThietBi FROM `thietbi` WHERE IsDeleted = 0 AND MaTaiSan = ? AND MaThietBi <> ? LIMIT 1",
                [$maTaiSan, $maThietBi]
            );
            if ($dup) {
                echo json_encode(['success' => false, 'message' => 'Mã tài sản đã tồn tại']);
                exit;
            }
        }

        $ok = dbExecute(
            "UPDATE `thietbi`
             SET MaLoaiThietBi = ?, MaTaiSan = ?, SoSerial = ?, MaDiaDiem = ?, MaTrangThai = ?, NgayMua = ?, HanBaoHanh = ?, GiaMua = ?, GhiChu = ?
             WHERE MaThietBi = ? AND IsDeleted = 0",
            [
                $maLoaiThietBi,
                ($maTaiSan !== '' ? $maTaiSan : null),
                ($soSerial !== '' ? $soSerial : null),
                $maDiaDiem,
                $maTrangThai,
                $ngayMuaDb,
                $hanBaoHanhDb,
                $giaMua,
                $ghiChuDb,
                $maThietBi
            ]
        );

        if ($ok === false) {
            echo json_encode(['success' => false, 'message' => 'Không thể cập nhật thiết bị']);
            exit;
        }

        echo json_encode(['success' => true, 'message' => 'Cập nhật thiết bị thành công']);
        exit;
    }

    // Create
    $exists = dbQueryOne("SELECT MaThietBi FROM `thietbi` WHERE MaThietBi = ? LIMIT 1", [$maThietBi]);
    if ($exists) {
        echo json_encode(['success' => false, 'message' => 'Mã thiết bị đã tồn tại']);
        exit;
    }

    if ($maTaiSan !== '') {
        $dup = dbQueryOne("SELECT MaThietBi FROM `thietbi` WHERE IsDeleted = 0 AND MaTaiSan = ? LIMIT 1", [$maTaiSan]);
        if ($dup) {
            echo json_encode(['success' => false, 'message' => 'Mã tài sản đã tồn tại']);
            exit;
        }
    }

    $ok = dbExecute(
        "INSERT INTO `thietbi` (MaThietBi, MaLoaiThietBi, MaTaiSan, SoSerial, MaDiaDiem, MaTrangThai, NgayMua, HanBaoHanh, GiaMua, GhiChu, IsDeleted)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0)",
        [
            $maThietBi,
            $maLoaiThietBi,
            ($maTaiSan !== '' ? $maTaiSan : null),
            ($soSerial !== '' ? $soSerial : null),
            $maDiaDiem,
            $maTrangThai,
            $ngayMuaDb,
            $hanBaoHanhDb,
            $giaMua,
            $ghiChuDb
        ]
    );

    if ($ok === false) {
        echo json_encode(['success' => false, 'message' => 'Không thể thêm thiết bị']);
        exit;
    }

    echo json_encode(['success' => true, 'message' => 'Thêm thiết bị thành công']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
}
