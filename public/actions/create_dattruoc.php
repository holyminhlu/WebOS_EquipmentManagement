<?php
/**
 * Xử lý tạo đặt trước thiết bị (theo Loại thiết bị)
 */

session_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/user.php';
require_once __DIR__ . '/../../includes/audit.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Vui lòng đăng nhập để thực hiện chức năng này'
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Method không hợp lệ'
    ]);
    exit;
}

// Chặn tạo đặt trước nếu còn phiếu phạt chưa thanh toán
if (userHasUnpaidPhieuPhat($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Vui lòng thanh toán tất cả các phiếu phạt trước khi thực hiện thao tác'
    ]);
    exit;
}

$rawMaThietBi = $_POST['maThietBi'] ?? '';
$rawMaLoai = $_POST['maLoaiThietBi'] ?? '';
$ngayBatDau = isset($_POST['ngayBatDau']) ? trim((string)$_POST['ngayBatDau']) : '';
$ngayKetThuc = isset($_POST['ngayKetThuc']) ? trim((string)$_POST['ngayKetThuc']) : '';
$rawMaDiaDiemSuDung = $_POST['maDiaDiemSuDung'] ?? '';

$maDiaDiemSuDung = '';
if (!is_array($rawMaDiaDiemSuDung)) {
    $maDiaDiemSuDung = trim((string)$rawMaDiaDiemSuDung);
}

// Normalize device ids (support single value and array maThietBi[])
$deviceIds = [];
if (is_array($rawMaThietBi)) {
    foreach ($rawMaThietBi as $id) {
        $id = trim((string)$id);
        if ($id !== '') $deviceIds[] = $id;
    }
} else {
    $id = trim((string)$rawMaThietBi);
    if ($id !== '') $deviceIds[] = $id;
}
$deviceIds = array_values(array_unique($deviceIds));

// Normalize types (support single value and array maLoaiThietBi[])
$typeIds = [];
if (is_array($rawMaLoai)) {
    foreach ($rawMaLoai as $id) {
        $id = trim((string)$id);
        if ($id !== '') $typeIds[] = $id;
    }
} else {
    $id = trim((string)$rawMaLoai);
    if ($id !== '') $typeIds[] = $id;
}
$typeIds = array_values(array_unique($typeIds));

// Must provide at least one device or type
if ((empty($deviceIds) && empty($typeIds)) || $ngayBatDau === '' || $ngayKetThuc === '') {
    echo json_encode([
        'success' => false,
        'message' => 'Vui lòng nhập đầy đủ thông tin bắt buộc'
    ]);
    exit;
}

if (count($deviceIds) > 10) {
    echo json_encode([
        'success' => false,
        'message' => 'Chỉ được chọn tối đa 10 thiết bị cho mỗi lần đặt trước'
    ]);
    exit;
}

if (count($typeIds) > 10) {
    echo json_encode([
        'success' => false,
        'message' => 'Chỉ được chọn tối đa 10 loại thiết bị cho mỗi lần đặt trước'
    ]);
    exit;
}

$startTs = strtotime($ngayBatDau);
$endTs = strtotime($ngayKetThuc);
if ($startTs === false || $endTs === false) {
    echo json_encode([
        'success' => false,
        'message' => 'Định dạng thời gian không hợp lệ'
    ]);
    exit;
}

if ($startTs >= $endTs) {
    echo json_encode([
        'success' => false,
        'message' => 'Thời gian không hợp lệ: ngày kết thúc phải sau ngày bắt đầu'
    ]);
    exit;
}

// Chỉ cho phép đặt trong 1 ngày
if (date('Y-m-d', $startTs) !== date('Y-m-d', $endTs)) {
    echo json_encode([
        'success' => false,
        'message' => 'Thời gian đặt trước chỉ trong 1 ngày. Vui lòng chọn ngày kết thúc cùng ngày bắt đầu'
    ]);
    exit;
}

// Ngày mượn/đặt phải từ ngày mai trở đi
$minDateYmd = date('Y-m-d', strtotime('tomorrow'));
if (date('Y-m-d', $startTs) < $minDateYmd) {
    echo json_encode([
        'success' => false,
        'message' => 'Ngày bắt đầu phải từ ngày mai trở đi'
    ]);
    exit;
}

// If device-based reservation is provided, derive types from devices
$deviceTypeMap = []; // [MaThietBi] => [MaLoaiThietBi, TenLoai]
if (!empty($deviceIds)) {
    $placeholders = implode(',', array_fill(0, count($deviceIds), '?'));
    $devices = dbQuery(
        "SELECT tb.MaThietBi, tb.MaLoaiThietBi, tb.MaTrangThai, ltb.TenLoai
         FROM thietbi tb
         LEFT JOIN loaithietbi ltb ON tb.MaLoaiThietBi = ltb.MaLoaiThietBi
         WHERE tb.IsDeleted = 0 AND tb.MaThietBi IN ($placeholders)",
        $deviceIds
    );

    if (count($devices) !== count($deviceIds)) {
        echo json_encode([
            'success' => false,
            'message' => 'Có thiết bị không tồn tại hoặc đã bị xóa'
        ]);
        exit;
    }

    foreach ($devices as $d) {
        if ((int)$d['MaTrangThai'] !== 1) {
            echo json_encode([
                'success' => false,
                'message' => 'Có thiết bị không khả dụng để đặt trước'
            ]);
            exit;
        }
        $deviceTypeMap[$d['MaThietBi']] = [
            'MaLoaiThietBi' => $d['MaLoaiThietBi'],
            'TenLoai' => $d['TenLoai'] ?? $d['MaLoaiThietBi']
        ];
    }

    // For notifications, also build type name map
    $typeNameMap = [];
    foreach ($deviceTypeMap as $info) {
        $typeNameMap[$info['MaLoaiThietBi']] = $info['TenLoai'] ?? $info['MaLoaiThietBi'];
    }
} else {
    // Validate types exist
    $placeholders = implode(',', array_fill(0, count($typeIds), '?'));
    $types = dbQuery(
        "SELECT MaLoaiThietBi, TenLoai FROM loaithietbi WHERE IsDeleted = 0 AND MaLoaiThietBi IN ($placeholders)",
        $typeIds
    );

    if (count($types) !== count($typeIds)) {
        echo json_encode([
            'success' => false,
            'message' => 'Có loại thiết bị không tồn tại hoặc đã bị xóa'
        ]);
        exit;
    }

    $typeNameMap = [];
    foreach ($types as $t) {
        $typeNameMap[$t['MaLoaiThietBi']] = $t['TenLoai'] ?? $t['MaLoaiThietBi'];
    }
}

try {
    // Validate room/location if provided (optional; UI may require it)
    $ddRow = null;
    if ($maDiaDiemSuDung !== '') {
        if (!ctype_digit($maDiaDiemSuDung)) {
            echo json_encode([
                'success' => false,
                'message' => 'Phòng/địa điểm không hợp lệ'
            ]);
            exit;
        }
        $ddRow = dbQueryOne(
            "SELECT MaDiaDiem, TenDiaDiem, Khu
             FROM diadiem
             WHERE IsDeleted = 0 AND MaDiaDiem = ?
             LIMIT 1",
            [(int)$maDiaDiemSuDung]
        );
        if (!$ddRow) {
            echo json_encode([
                'success' => false,
                'message' => 'Phòng/địa điểm không tồn tại hoặc đã bị xóa'
            ]);
            exit;
        }
    }

    if ($maDiaDiemSuDung === '' || !$ddRow || !isset($ddRow['MaDiaDiem'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Vui lòng chọn phòng/địa điểm sử dụng'
        ]);
        exit;
    }

    // Prefer storing MaDiaDiem if schema supports it
    $hasMaDiaDiem = false;
    $col = dbQueryOne(
        "SELECT COUNT(*) AS cnt
         FROM INFORMATION_SCHEMA.COLUMNS
         WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'DatTruoc' AND COLUMN_NAME = 'MaDiaDiem'",
        [defined('DB_NAME') ? DB_NAME : '']
    );
    if ($col && isset($col['cnt']) && (int)$col['cnt'] > 0) {
        $hasMaDiaDiem = true;
    }

    // Fallback (no schema change): encode room into MaDatTruoc as DT###D<room>-...
    // This allows conflict checking by REGEXP on MaDatTruoc when DatTruoc.MaDiaDiem is missing.
    $useRoomInId = !$hasMaDiaDiem;

    // Conflict check: same room + same date + same half-day (morning/afternoon)
    $dateYmd = date('Y-m-d', $startTs);
    $noonStr = $dateYmd . ' 12:00:00';
    $noonTs = strtotime($noonStr);
    $coversMorning = ($noonTs !== false) ? ($startTs < $noonTs) : true;
    $coversAfternoon = ($noonTs !== false) ? ($endTs > $noonTs) : true;

    $sessionParts = [];
    $sessionClauses = [];
    $sessionParams = [];
    if ($coversMorning) {
        $sessionParts[] = 'buổi sáng';
        $sessionClauses[] = "NgayBatDau < ?";
        $sessionParams[] = $noonStr;
    }
    if ($coversAfternoon) {
        $sessionParts[] = 'buổi chiều';
        $sessionClauses[] = "NgayKetThuc > ?";
        $sessionParams[] = $noonStr;
    }
    if (empty($sessionClauses)) {
        $sessionClauses[] = "1=1";
    }

    $roomId = (int)$ddRow['MaDiaDiem'];
    if ($hasMaDiaDiem) {
        $conflictRow = dbQueryOne(
            "SELECT COUNT(*) AS cnt
             FROM dattruoc
             WHERE IsDeleted = 0
               AND TrangThai IN ('Chờ duyệt', 'Đã duyệt')
               AND MaDiaDiem = ?
               AND DATE(NgayBatDau) = ?
               AND (" . implode(' OR ', $sessionClauses) . ")",
            array_merge([$roomId, $dateYmd], $sessionParams)
        );
    } else {
        // Match ids like: DT001D12 or DT001D12-TB005
        $roomPattern = '^DT[0-9]+D' . $roomId . '($|-)';
        $conflictRow = dbQueryOne(
            "SELECT COUNT(*) AS cnt
             FROM dattruoc
             WHERE IsDeleted = 0
               AND TrangThai IN ('Chờ duyệt', 'Đã duyệt')
               AND MaDatTruoc REGEXP ?
               AND DATE(NgayBatDau) = ?
               AND (" . implode(' OR ', $sessionClauses) . ")",
            array_merge([$roomPattern, $dateYmd], $sessionParams)
        );
    }

    $conflictCount = ($conflictRow && isset($conflictRow['cnt'])) ? (int)$conflictRow['cnt'] : 0;
    if ($conflictCount > 0) {
        $dmy = date('d/m/Y', $startTs);
        $sessionText = !empty($sessionParts) ? implode(' và ', $sessionParts) : 'buổi này';
        echo json_encode([
            'success' => false,
            'message' => 'Phòng/địa điểm "' . ($ddRow['TenDiaDiem'] ?? ('#' . $roomId)) . '" đã có người đặt trước trong ' . $sessionText . ' ngày ' . $dmy . '. Vui lòng chọn phòng khác hoặc đổi thời gian.'
        ]);
        exit;
    }

    // Create multiple DatTruoc rows in one request
    // Find next sequence number by scanning recent rows (works even when MaDatTruoc has suffix like -TB001)
    $recent = dbQuery("SELECT MaDatTruoc FROM dattruoc ORDER BY NgayTao DESC LIMIT 50");
    $maxNum = 0;
    foreach ($recent as $r) {
        $id = $r['MaDatTruoc'] ?? '';
        if (preg_match('/^DT(\d+)/', $id, $m)) {
            $num = (int)$m[1];
            if ($num > $maxNum) $maxNum = $num;
        }
    }
    $nextNum = $maxNum + 1;

    // Use ONE group base per user submission so it appears as a single ticket in Dashboard
    $groupBase = 'DT' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);

    $buildId = function(string $base, int $roomId, string $suffix) use ($useRoomInId): string {
        $suffix = trim($suffix);
        $prefix = $useRoomInId ? ($base . 'D' . $roomId . '-') : ($base . '-');
        $maxSuffixLen = 20 - strlen($prefix);
        if ($maxSuffixLen <= 0) {
            return substr($prefix, 0, 20);
        }
        if (strlen($suffix) <= $maxSuffixLen) {
            return $prefix . $suffix;
        }
        // Shorten with a stable hash to avoid collisions when truncated
        $hash = substr(dechex(crc32($suffix)), 0, 6);
        $keep = max(1, $maxSuffixLen - 7);
        $short = substr($suffix, 0, $keep) . '-' . $hash;
        if (strlen($short) > $maxSuffixLen) {
            $short = substr($short, 0, $maxSuffixLen);
        }
        return $prefix . $short;
    };

    if ($hasMaDiaDiem) {
        $sql = "INSERT INTO dattruoc (MaDatTruoc, MaNguoiYeuCau, MaLoaiThietBi, MaDiaDiem, NgayBatDau, NgayKetThuc, TrangThai, NgayTao, IsDeleted)
                VALUES (?, ?, ?, ?, ?, ?, 'Chờ duyệt', NOW(), 0)";
    } else {
        $sql = "INSERT INTO dattruoc (MaDatTruoc, MaNguoiYeuCau, MaLoaiThietBi, NgayBatDau, NgayKetThuc, TrangThai, NgayTao, IsDeleted)
                VALUES (?, ?, ?, ?, ?, 'Chờ duyệt', NOW(), 0)";
    }

    $created = [];
    if (!empty($deviceIds)) {
        foreach ($deviceIds as $deviceId) {
            $typeId = $deviceTypeMap[$deviceId]['MaLoaiThietBi'] ?? null;
            if (!$typeId) continue;
            // One group base, unique per device via suffix
            $maDatTruoc = $buildId($groupBase, $roomId, $deviceId);
            if ($hasMaDiaDiem) {
                dbExecute($sql, [$maDatTruoc, $_SESSION['user_id'], $typeId, ($ddRow ? (int)$ddRow['MaDiaDiem'] : null), $ngayBatDau, $ngayKetThuc]);
            } else {
                dbExecute($sql, [$maDatTruoc, $_SESSION['user_id'], $typeId, $ngayBatDau, $ngayKetThuc]);
            }

            // Audit each created row (max 10)
            $afterDt = dbQueryOne("SELECT * FROM `dattruoc` WHERE MaDatTruoc = ? LIMIT 1", [$maDatTruoc]);
            auditLog('DatTruoc', $maDatTruoc, 'CREATE', null, $afterDt);
            $created[] = $maDatTruoc;
        }
    } else {
        foreach ($typeIds as $typeId) {
            // One group base, unique per type via suffix
            $safeType = preg_replace('/[^A-Za-z0-9]/', '', (string)$typeId);
            $suffix = 'L' . $safeType;
            $maDatTruoc = $buildId($groupBase, $roomId, $suffix);
            if ($hasMaDiaDiem) {
                dbExecute($sql, [$maDatTruoc, $_SESSION['user_id'], $typeId, ($ddRow ? (int)$ddRow['MaDiaDiem'] : null), $ngayBatDau, $ngayKetThuc]);
            } else {
                dbExecute($sql, [$maDatTruoc, $_SESSION['user_id'], $typeId, $ngayBatDau, $ngayKetThuc]);
            }

            // Audit
            $afterDt = dbQueryOne("SELECT * FROM `dattruoc` WHERE MaDatTruoc = ? LIMIT 1", [$maDatTruoc]);
            auditLog('DatTruoc', $maDatTruoc, 'CREATE', null, $afterDt);
            $created[] = $maDatTruoc;
        }
    }

    // Thông báo cho user
    $lastTb = dbQueryOne("SELECT MaThongBao FROM thongbao ORDER BY MaThongBao DESC LIMIT 1");
    $nextTbNum = 1;
    if ($lastTb && !empty($lastTb['MaThongBao']) && preg_match('/(\d+)$/', $lastTb['MaThongBao'], $m2)) {
        $nextTbNum = intval($m2[1]) + 1;
    }
    $maThongBao = 'TB' . str_pad($nextTbNum, 3, '0', STR_PAD_LEFT);

    if (!empty($deviceIds)) {
        $noiDung = 'Bạn đã gửi yêu cầu đặt trước ' . count($deviceIds) . ' thiết bị: ' . implode(', ', $deviceIds) .
                  "\nThời gian: $ngayBatDau → $ngayKetThuc\nTrạng thái: Chờ duyệt.";
    } else {
        $typeNames = array_map(function($id) use ($typeNameMap) {
            return $typeNameMap[$id] ?? $id;
        }, $typeIds);
        $noiDung = 'Bạn đã gửi yêu cầu đặt trước ' . count($typeIds) . ' loại thiết bị: ' . implode(', ', $typeNames) .
                  "\nThời gian: $ngayBatDau → $ngayKetThuc\nTrạng thái: Chờ duyệt.";
    }

    if ($ddRow && !empty($ddRow['TenDiaDiem'])) {
        $khuTxt = isset($ddRow['Khu']) && trim((string)$ddRow['Khu']) !== '' ? (' (Khu ' . trim((string)$ddRow['Khu']) . ')') : '';
        $noiDung .= "\nĐịa điểm sử dụng: " . $ddRow['TenDiaDiem'] . $khuTxt;
    }

    $sqlThongBao = "INSERT INTO thongbao (MaThongBao, MaNguoiDung, TieuDe, NoiDung, Kenh, NgayGui, DaDoc, IsDeleted)
                    VALUES (?, ?, 'Đặt trước đã gửi', ?, 'Hệ thống', NOW(), 0, 0)";

    dbExecute($sqlThongBao, [$maThongBao, $_SESSION['user_id'], $noiDung]);

    echo json_encode([
        'success' => true,
        'message' => 'Đã gửi đặt trước thành công! Vui lòng chờ quản trị viên phê duyệt.',
        'maDatTruoc' => $created
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi hệ thống: ' . $e->getMessage()
    ]);
}

