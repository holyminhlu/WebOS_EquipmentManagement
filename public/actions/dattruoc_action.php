<?php
/**
 * Handle approve action for DatTruoc (admin only)
 * - Approves a grouped reservation (MaDatTruoc group key)
 * - Marks all rows in the group as 'Đã duyệt'
 * - Inserts a thongbao for the requester
 */

session_start();

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/user.php';
require_once __DIR__ . '/../../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../dashboard.php');
    exit;
}

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$user = getUserInfo($_SESSION['user_id']);

$isAdmin = false;
if ($user && isset($user['MaVaiTro']) && (int)$user['MaVaiTro'] === 1) {
    $isAdmin = true;
} elseif ($user && !empty($user['TenVaiTro'])) {
    $tenVaiTro = mb_strtolower(trim((string)$user['TenVaiTro']), 'UTF-8');
    if ($tenVaiTro === 'admin' || $tenVaiTro === 'quản trị' || $tenVaiTro === 'quan tri' || str_contains($tenVaiTro, 'admin')) {
        $isAdmin = true;
    }
}

if (!$user || !$isAdmin) {
    header('Location: ../dashboard.php');
    exit;
}

$action = $_POST['action'] ?? '';
$maDatTruoc = isset($_POST['MaDatTruoc']) ? trim((string)$_POST['MaDatTruoc']) : '';

if ($action !== 'approve' || $maDatTruoc === '') {
    header('Location: ../dashboard.php');
    exit;
}

try {
    // Approve grouped ticket: group key is exact id prefix before '-' (e.g. DT001 or DT001D12)
    $like = $maDatTruoc . '-%';

    // Detect optional column MaDiaDiem on dattruoc (some schemas add it)
    $hasMaDiaDiem = false;
    try {
        $col = dbQueryOne(
            "SELECT COUNT(*) AS cnt
             FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = ?
               AND TABLE_NAME IN ('dattruoc','DatTruoc')
               AND COLUMN_NAME = 'MaDiaDiem'",
            [defined('DB_NAME') ? DB_NAME : '']
        );
        if ($col && isset($col['cnt']) && (int)$col['cnt'] > 0) {
            $hasMaDiaDiem = true;
        }
    } catch (Exception $e) {
        $hasMaDiaDiem = false;
    }

    $selectCols = "MaDatTruoc, TrangThai, MaNguoiYeuCau, MaLoaiThietBi, NgayBatDau, NgayKetThuc";
    if ($hasMaDiaDiem) {
        $selectCols .= ", MaDiaDiem";
    }

    $rows = dbQuery(
        "SELECT $selectCols\n         FROM dattruoc\n         WHERE IsDeleted = 0\n           AND (MaDatTruoc = ? OR MaDatTruoc LIKE ?)",
        [$maDatTruoc, $like]
    );

    if (empty($rows)) {
        header('Location: ../dashboard.php');
        exit;
    }

    foreach ($rows as $r) {
        if (($r['TrangThai'] ?? '') !== 'Chờ duyệt') {
            header('Location: ../dashboard.php');
            exit;
        }
    }

    $maNguoiYeuCau = (string)($rows[0]['MaNguoiYeuCau'] ?? '');
    if ($maNguoiYeuCau === '') {
        header('Location: ../dashboard.php');
        exit;
    }

    dbExecute(
        "UPDATE dattruoc\n         SET TrangThai = 'Đã duyệt'\n         WHERE IsDeleted = 0\n           AND TrangThai = 'Chờ duyệt'\n           AND (MaDatTruoc = ? OR MaDatTruoc LIKE ?)",
        [$maDatTruoc, $like]
    );

    // Create a matching YeuCauMuon + PhieuMuon so it appears under Phiếu mượn like an approved request.
    // Idempotent: if a slip already exists for this group key, do nothing.
    $existingPm = dbQueryOne("SELECT MaPhieu FROM `phieumuon` WHERE IsDeleted = 0 AND MaYeuCau = ? LIMIT 1", [$maDatTruoc]);
    if (!$existingPm) {
        // Determine time range (shared across the group)
        $ngayBatDau = $rows[0]['NgayBatDau'] ?? null;
        $ngayKetThuc = $rows[0]['NgayKetThuc'] ?? null;

        // Determine room/location
        $roomId = 0;
        if ($hasMaDiaDiem && isset($rows[0]['MaDiaDiem']) && $rows[0]['MaDiaDiem'] !== null && $rows[0]['MaDiaDiem'] !== '') {
            $roomId = (int)$rows[0]['MaDiaDiem'];
        }
        if ($roomId <= 0) {
            $mRoom = [];
            if (preg_match('/^DT\d+D(\d+)/', $maDatTruoc, $mRoom)) {
                $roomId = (int)$mRoom[1];
            }
        }

        // Parse requested device ids from MaDatTruoc suffix (-TB###)
        $requestedDeviceIds = [];
        $requestedTypeIds = [];
        foreach ($rows as $r) {
            $id = isset($r['MaDatTruoc']) ? trim((string)$r['MaDatTruoc']) : '';
            if ($id !== '' && preg_match('/^(DT\d+(?:D\d+)?)-(.+)$/', $id, $mId)) {
                $suffix = trim((string)$mId[2]);
                if ($suffix !== '' && preg_match('/^TB\d+$/', $suffix)) {
                    $requestedDeviceIds[$suffix] = true;
                }
            }
            $t = isset($r['MaLoaiThietBi']) ? trim((string)$r['MaLoaiThietBi']) : '';
            if ($t !== '') {
                $requestedTypeIds[$t] = true;
            }
        }
        $requestedDeviceIds = array_keys($requestedDeviceIds);
        $requestedTypeIds = array_keys($requestedTypeIds);

        // Ensure YeuCauMuon exists for this group key (used for room parsing on dashboard)
        $existingYc = dbQueryOne("SELECT MaYeuCau FROM `yeucaumuon` WHERE MaYeuCau = ? LIMIT 1", [$maDatTruoc]);
        if (!$existingYc) {
            // Detect YeuCauMuon schema columns to stay compatible
            $cols = dbQuery(
                "SELECT COLUMN_NAME
                 FROM INFORMATION_SCHEMA.COLUMNS
                 WHERE TABLE_SCHEMA = ?
                   AND TABLE_NAME IN ('yeucaumuon','YeuCauMuon')",
                [defined('DB_NAME') ? DB_NAME : '']
            );
            $has = [];
            foreach ($cols as $c) {
                $name = isset($c['COLUMN_NAME']) ? strtolower((string)$c['COLUMN_NAME']) : '';
                if ($name !== '') $has[$name] = true;
            }

            $ghiChuParts = [];
            if (!empty($requestedDeviceIds)) {
                $ghiChuParts[] = 'DS_TB:' . implode(',', $requestedDeviceIds);
            }
            if ($roomId > 0) {
                $ghiChuParts[] = 'DD:' . $roomId;
            }
            $ghiChuParts[] = 'FROM_DT:1';
            $ghiChu = implode("\n", $ghiChuParts);

            $ycCols = ['MaYeuCau', 'MaNguoiYeuCau', 'TrangThai', 'GhiChu', 'IsDeleted'];
            $ycVals = [$maDatTruoc, $maNguoiYeuCau, 'Đã duyệt', $ghiChu, 0];

            if (!empty($has['ngaygui'])) {
                $ycCols[] = 'NgayGui';
                $ycVals[] = date('Y-m-d H:i:s');
            }
            if (!empty($has['mucdich'])) {
                $ycCols[] = 'MucDich';
                $ycVals[] = 'Đặt trước thiết bị';
            }
            if (!empty($has['nguoiduyet'])) {
                $ycCols[] = 'NguoiDuyet';
                $ycVals[] = $_SESSION['user_id'];
            }
            if (!empty($has['ngayduyet'])) {
                $ycCols[] = 'NgayDuyet';
                $ycVals[] = date('Y-m-d H:i:s');
            }
            if (!empty($has['thoigianbatdau'])) {
                $ycCols[] = 'ThoiGianBatDau';
                $ycVals[] = $ngayBatDau;
            } elseif (!empty($has['ngaydukienbatdau'])) {
                $ycCols[] = 'NgayDuKienBatDau';
                $ycVals[] = $ngayBatDau;
            }
            if (!empty($has['thoigianketthuc'])) {
                $ycCols[] = 'ThoiGianKetThuc';
                $ycVals[] = $ngayKetThuc;
            } elseif (!empty($has['ngaydukienketthuc'])) {
                $ycCols[] = 'NgayDuKienKetThuc';
                $ycVals[] = $ngayKetThuc;
            }

            $ph = implode(',', array_fill(0, count($ycCols), '?'));
            $sqlYc = "INSERT INTO `yeucaumuon` (" . implode(',', $ycCols) . ") VALUES ($ph)";
            dbExecute($sqlYc, $ycVals);
        }

        // Generate new MaPhieu (PM###) and SoPhieu (SP###)
        $last = dbQueryOne("SELECT MaPhieu FROM `phieumuon` ORDER BY MaPhieu DESC LIMIT 1");
        $nextNum = 1;
        if ($last && !empty($last['MaPhieu']) && preg_match('/(\d+)$/', (string)$last['MaPhieu'], $m)) {
            $nextNum = intval($m[1]) + 1;
        }
        $maPhieu = 'PM' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);
        $soPhieu = 'SP' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);

        $ngayPhat = date('Y-m-d H:i:s');
        $ngayPhaiTra = $ngayKetThuc;
        $trangThai = 'Đang mượn';

        $sqlInsert = "INSERT INTO `phieumuon` (MaPhieu, SoPhieu, MaYeuCau, MaNguoiMuon, NgayPhat, NgayPhaiTra, TrangThai, NguoiPhatThietBi) VALUES (?,?,?,?,?,?,?,?)";
        $ins = dbExecute($sqlInsert, [$maPhieu, $soPhieu, $maDatTruoc, $maNguoiYeuCau, $ngayPhat, $ngayPhaiTra, $trangThai, $_SESSION['user_id']]);

        if ($ins !== false) {
            // Generate base CTM number once
            $lastCt = dbQueryOne("SELECT MaChiTiet FROM `chitietmuon` ORDER BY MaChiTiet DESC LIMIT 1");
            $nextCtNum = 1;
            if ($lastCt && !empty($lastCt['MaChiTiet']) && preg_match('/(\d+)$/', (string)$lastCt['MaChiTiet'], $mct)) {
                $nextCtNum = intval($mct[1]) + 1;
            }

            // Select devices: prefer explicit TB ids, otherwise pick one available per reserved type
            $finalDeviceIds = [];

            foreach ($requestedDeviceIds as $deviceId) {
                $ok = dbQueryOne("SELECT MaThietBi FROM `thietbi` WHERE IsDeleted = 0 AND MaTrangThai = 1 AND MaThietBi = ? LIMIT 1", [$deviceId]);
                if ($ok && !empty($ok['MaThietBi'])) {
                    $finalDeviceIds[$deviceId] = true;
                }
            }

            if (empty($finalDeviceIds) && !empty($requestedTypeIds)) {
                foreach ($requestedTypeIds as $typeId) {
                    $pick = dbQueryOne(
                        "SELECT MaThietBi FROM `thietbi` WHERE IsDeleted = 0 AND MaTrangThai = 1 AND MaLoaiThietBi = ? LIMIT 1",
                        [$typeId]
                    );
                    if ($pick && !empty($pick['MaThietBi'])) {
                        $finalDeviceIds[(string)$pick['MaThietBi']] = true;
                    }
                }
            }

            $finalDeviceIds = array_keys($finalDeviceIds);

            foreach ($finalDeviceIds as $deviceId) {
                $maChiTiet = 'CTM' . str_pad($nextCtNum, 3, '0', STR_PAD_LEFT);
                $nextCtNum++;
                dbExecute(
                    "INSERT INTO `chitietmuon` (MaChiTiet, MaPhieu, MaThietBi, SoLuong, TinhTrangLucMuon, GhiChu, IsDeleted) VALUES (?,?,?,?,?,?,0)",
                    [$maChiTiet, $maPhieu, $deviceId, 1, 'Tốt', 'Tạo khi duyệt đặt trước']
                );
                // lock device as borrowed/reserved
                dbExecute("UPDATE `thietbi` SET MaTrangThai = 2 WHERE MaThietBi = ?", [$deviceId]);
            }
        }
    }

    // Thông báo cho người yêu cầu
    $lastTb = dbQueryOne("SELECT MaThongBao FROM thongbao ORDER BY MaThongBao DESC LIMIT 1");
    $nextTbNum = 1;
    if ($lastTb && !empty($lastTb['MaThongBao']) && preg_match('/(\d+)$/', $lastTb['MaThongBao'], $m)) {
        $nextTbNum = intval($m[1]) + 1;
    }
    $maThongBao = 'TB' . str_pad($nextTbNum, 3, '0', STR_PAD_LEFT);

    $noiDung = "Yêu cầu đặt trước mã {$maDatTruoc} đã được duyệt.";
    $sqlThongBao = "INSERT INTO thongbao (MaThongBao, MaNguoiDung, TieuDe, NoiDung, Kenh, NgayGui, DaDoc, IsDeleted)
                    VALUES (?, ?, 'Đặt trước đã được duyệt', ?, 'Hệ thống', NOW(), 0, 0)";
    dbExecute($sqlThongBao, [$maThongBao, $maNguoiYeuCau, $noiDung]);
} catch (Exception $e) {
    // Fail closed: just return to dashboard
}

header('Location: ../dashboard.php');
exit;
