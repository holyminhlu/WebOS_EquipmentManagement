<?php
/**
 * System Admin: Monthly textual report
 * Input: POST month (YYYY-MM)
 * Output: JSON totals + breakdown by Khu
 */

require_once __DIR__ . '/_guard.php';
require_once __DIR__ . '/../../../includes/db.php';

$month = trim($_POST['month'] ?? '');
if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
    echo json_encode(['success' => false, 'message' => 'Tháng không hợp lệ (YYYY-MM)'], JSON_UNESCAPED_UNICODE);
    exit;
}

$year = (int)substr($month, 0, 4);
$mon = (int)substr($month, 5, 2);
if ($year < 2000 || $year > 2100 || $mon < 1 || $mon > 12) {
    echo json_encode(['success' => false, 'message' => 'Tháng không hợp lệ'], JSON_UNESCAPED_UNICODE);
    exit;
}

$daysInMonth = (int)cal_days_in_month(CAL_GREGORIAN, $mon, $year);
$start = sprintf('%04d-%02d-01 00:00:00', $year, $mon);
$end = sprintf('%04d-%02d-%02d 23:59:59', $year, $mon, $daysInMonth);

function safeInt($v) { return (int)($v ?? 0); }
function safeFloat($v) { return (float)($v ?? 0); }

try {
    // 1) Borrow in month (count slips)
    $borrowTotal = dbQueryOne(
        "SELECT COUNT(*) AS c
         FROM `phieumuon`
         WHERE IsDeleted = 0
           AND NgayPhat >= ? AND NgayPhat <= ?",
        [$start, $end]
    );

    // 2) Borrowed device quantity by Khu (sum SoLuong)
    $borrowByKhu = dbQuery(
        "SELECT COALESCE(NULLIF(TRIM(dd.Khu), ''), 'Chưa phân khu') AS Khu,
                COALESCE(SUM(ct.SoLuong), 0) AS SoLuong
         FROM `phieumuon` pm
         INNER JOIN `chitietmuon` ct ON pm.MaPhieu = ct.MaPhieu AND ct.IsDeleted = 0
         INNER JOIN `thietbi` tb ON ct.MaThietBi = tb.MaThietBi AND tb.IsDeleted = 0
         LEFT JOIN `diadiem` dd ON tb.MaDiaDiem = dd.MaDiaDiem AND dd.IsDeleted = 0
         WHERE pm.IsDeleted = 0
           AND pm.NgayPhat >= ? AND pm.NgayPhat <= ?
         GROUP BY COALESCE(NULLIF(TRIM(dd.Khu), ''), 'Chưa phân khu')
         ORDER BY Khu ASC",
        [$start, $end]
    );

    // 3) Reservations in month
    $reserveTotal = dbQueryOne(
        "SELECT COUNT(*) AS c
         FROM `dattruoc`
         WHERE IsDeleted = 0
           AND NgayTao >= ? AND NgayTao <= ?",
        [$start, $end]
    );

    // 4) Reservation quantity by Khu (count reservations) - DatTruoc has MaDiaDiem in this project
    $reserveByKhu = [];
    try {
        $reserveByKhu = dbQuery(
            "SELECT COALESCE(NULLIF(TRIM(dd.Khu), ''), 'Chưa phân khu') AS Khu,
                    COUNT(*) AS SoLuong
             FROM `dattruoc` dt
             LEFT JOIN `diadiem` dd ON dt.MaDiaDiem = dd.MaDiaDiem AND dd.IsDeleted = 0
             WHERE dt.IsDeleted = 0
               AND dt.NgayTao >= ? AND dt.NgayTao <= ?
             GROUP BY COALESCE(NULLIF(TRIM(dd.Khu), ''), 'Chưa phân khu')
             ORDER BY Khu ASC",
            [$start, $end]
        );
    } catch (Exception $e) {
        // If column MaDiaDiem doesn't exist in some DBs, keep empty breakdown.
        $reserveByKhu = [];
    }

    // 5) Fines in month + total amount
    $fineTotal = dbQueryOne(
        "SELECT COUNT(*) AS c, COALESCE(SUM(SoTien), 0) AS s
         FROM `phieuphat`
         WHERE IsDeleted = 0
           AND NgayTao >= ? AND NgayTao <= ?",
        [$start, $end]
    );

    // 6) Fines by Khu (avoid double counting by grouping per fine)
    $fineByKhu = dbQuery(
        "SELECT x.Khu,
                COUNT(*) AS SoLuong,
                COALESCE(SUM(x.SoTien), 0) AS TongTien
         FROM (
            SELECT p.MaPhat,
                   p.SoTien,
                   COALESCE(NULLIF(TRIM(MIN(dd.Khu)), ''), 'Chưa phân khu') AS Khu
            FROM `phieuphat` p
            INNER JOIN `phieumuon` pm ON p.MaPhieu = pm.MaPhieu AND pm.IsDeleted = 0
            LEFT JOIN `chitietmuon` ct ON pm.MaPhieu = ct.MaPhieu AND ct.IsDeleted = 0
            LEFT JOIN `thietbi` tb ON ct.MaThietBi = tb.MaThietBi AND tb.IsDeleted = 0
            LEFT JOIN `diadiem` dd ON tb.MaDiaDiem = dd.MaDiaDiem AND dd.IsDeleted = 0
            WHERE p.IsDeleted = 0
              AND p.NgayTao >= ? AND p.NgayTao <= ?
            GROUP BY p.MaPhat, p.SoTien
         ) x
         GROUP BY x.Khu
         ORDER BY x.Khu ASC",
        [$start, $end]
    );

    // 7) Maintenance in month (count by NgayBao), total cost (sum by NgaySua)
    $maintCount = dbQueryOne(
        "SELECT COUNT(*) AS c
         FROM `baotri`
         WHERE IsDeleted = 0
           AND NgayBao >= ? AND NgayBao <= ?",
        [$start, $end]
    );

    $maintCost = dbQueryOne(
        "SELECT COALESCE(SUM(ChiPhi), 0) AS s
         FROM `baotri`
         WHERE IsDeleted = 0
           AND NgaySua IS NOT NULL
           AND NgaySua >= ? AND NgaySua <= ?",
        [$start, $end]
    );

    $maintByKhu = dbQuery(
        "SELECT COALESCE(NULLIF(TRIM(dd.Khu), ''), 'Chưa phân khu') AS Khu,
                COUNT(*) AS SoLan,
                COALESCE(SUM(CASE WHEN bt.NgaySua IS NOT NULL AND bt.NgaySua >= ? AND bt.NgaySua <= ? THEN bt.ChiPhi ELSE 0 END), 0) AS TongChiPhi
         FROM `baotri` bt
         INNER JOIN `thietbi` tb ON bt.MaThietBi = tb.MaThietBi AND tb.IsDeleted = 0
         LEFT JOIN `diadiem` dd ON tb.MaDiaDiem = dd.MaDiaDiem AND dd.IsDeleted = 0
         WHERE bt.IsDeleted = 0
           AND bt.NgayBao >= ? AND bt.NgayBao <= ?
         GROUP BY COALESCE(NULLIF(TRIM(dd.Khu), ''), 'Chưa phân khu')
         ORDER BY Khu ASC",
        [$start, $end, $start, $end]
    );

    // 8) Broken devices in month (based on maintenance decision)
    $brokenTotal = dbQueryOne(
        "SELECT COUNT(*) AS c
         FROM `baotri`
         WHERE IsDeleted = 0
           AND TrangThai = 'Thiết bị hỏng'
           AND NgaySua IS NOT NULL
           AND NgaySua >= ? AND NgaySua <= ?",
        [$start, $end]
    );

    $brokenByKhu = dbQuery(
        "SELECT COALESCE(NULLIF(TRIM(dd.Khu), ''), 'Chưa phân khu') AS Khu,
                COUNT(*) AS SoLuong
         FROM `baotri` bt
         INNER JOIN `thietbi` tb ON bt.MaThietBi = tb.MaThietBi AND tb.IsDeleted = 0
         LEFT JOIN `diadiem` dd ON tb.MaDiaDiem = dd.MaDiaDiem AND dd.IsDeleted = 0
         WHERE bt.IsDeleted = 0
           AND bt.TrangThai = 'Thiết bị hỏng'
           AND bt.NgaySua IS NOT NULL
           AND bt.NgaySua >= ? AND bt.NgaySua <= ?
         GROUP BY COALESCE(NULLIF(TRIM(dd.Khu), ''), 'Chưa phân khu')
         ORDER BY Khu ASC",
        [$start, $end]
    );

    // 9) New devices in month (no created-at column in schema; use NgayMua as proxy)
    $newDeviceTotal = dbQueryOne(
        "SELECT COUNT(*) AS c, COALESCE(SUM(GiaMua), 0) AS s
         FROM `thietbi`
         WHERE IsDeleted = 0
           AND NgayMua IS NOT NULL
           AND CONCAT(NgayMua, ' 00:00:00') >= ? AND CONCAT(NgayMua, ' 00:00:00') <= ?",
        [$start, $end]
    );

    $newDeviceByKhu = dbQuery(
        "SELECT COALESCE(NULLIF(TRIM(dd.Khu), ''), 'Chưa phân khu') AS Khu,
                COUNT(*) AS SoLuong,
                COALESCE(SUM(tb.GiaMua), 0) AS TongTien
         FROM `thietbi` tb
         LEFT JOIN `diadiem` dd ON tb.MaDiaDiem = dd.MaDiaDiem AND dd.IsDeleted = 0
         WHERE tb.IsDeleted = 0
           AND tb.NgayMua IS NOT NULL
           AND CONCAT(tb.NgayMua, ' 00:00:00') >= ? AND CONCAT(tb.NgayMua, ' 00:00:00') <= ?
         GROUP BY COALESCE(NULLIF(TRIM(dd.Khu), ''), 'Chưa phân khu')
         ORDER BY Khu ASC",
        [$start, $end]
    );

    echo json_encode([
        'success' => true,
        'month' => $month,
        'range' => ['start' => $start, 'end' => $end],
        'totals' => [
            'borrowCount' => safeInt($borrowTotal['c'] ?? 0),
            'reserveCount' => safeInt($reserveTotal['c'] ?? 0),
            'fineCount' => safeInt($fineTotal['c'] ?? 0),
            'fineSum' => safeFloat($fineTotal['s'] ?? 0),
            'maintCount' => safeInt($maintCount['c'] ?? 0),
            'maintSum' => safeFloat($maintCost['s'] ?? 0),
            'brokenCount' => safeInt($brokenTotal['c'] ?? 0),
            'newDeviceCount' => safeInt($newDeviceTotal['c'] ?? 0),
            'newDeviceSum' => safeFloat($newDeviceTotal['s'] ?? 0),
        ],
        'byKhu' => [
            'borrowDevices' => $borrowByKhu,
            'reserve' => $reserveByKhu,
            'fine' => $fineByKhu,
            'maint' => $maintByKhu,
            'broken' => $brokenByKhu,
            'newDevice' => $newDeviceByKhu,
        ]
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
