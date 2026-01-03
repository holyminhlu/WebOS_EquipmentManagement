<?php
/**
 * System Admin: Monthly statistics (Line Chart)
 * - Counts by day for a given month:
 *   + Borrow (PhieuMuon.NgayPhat)
 *   + Reservation (DatTruoc.NgayTao)
 *   + Fine (PhieuPhat.NgayTao)
 *   + Maintenance (BaoTri.NgayBao)
 */

require_once __DIR__ . '/_guard.php';
require_once __DIR__ . '/../../../includes/db.php';

$month = trim($_POST['month'] ?? ''); // YYYY-MM
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

function initSeries($daysInMonth) {
    $arr = [];
    for ($d = 1; $d <= $daysInMonth; $d++) $arr[$d] = 0;
    return $arr;
}

$borrowByDay = initSeries($daysInMonth);
$reserveByDay = initSeries($daysInMonth);
$fineByDay = initSeries($daysInMonth);
$maintByDay = initSeries($daysInMonth);

// Borrow: count PhieuMuon by NgayPhat
$rows = dbQuery(
    "SELECT DAY(NgayPhat) AS d, COUNT(*) AS c
     FROM `phieumuon`
     WHERE IsDeleted = 0
       AND NgayPhat >= ? AND NgayPhat <= ?
     GROUP BY DAY(NgayPhat)",
    [$start, $end]
);
foreach ($rows as $r) {
    $d = (int)($r['d'] ?? 0);
    if ($d >= 1 && $d <= $daysInMonth) $borrowByDay[$d] = (int)($r['c'] ?? 0);
}

// Reservation: count DatTruoc by NgayTao
$rows = dbQuery(
    "SELECT DAY(NgayTao) AS d, COUNT(*) AS c
     FROM `dattruoc`
     WHERE IsDeleted = 0
       AND NgayTao >= ? AND NgayTao <= ?
     GROUP BY DAY(NgayTao)",
    [$start, $end]
);
foreach ($rows as $r) {
    $d = (int)($r['d'] ?? 0);
    if ($d >= 1 && $d <= $daysInMonth) $reserveByDay[$d] = (int)($r['c'] ?? 0);
}

// Fine: count PhieuPhat by NgayTao
$rows = dbQuery(
    "SELECT DAY(NgayTao) AS d, COUNT(*) AS c
     FROM `phieuphat`
     WHERE IsDeleted = 0
       AND NgayTao >= ? AND NgayTao <= ?
     GROUP BY DAY(NgayTao)",
    [$start, $end]
);
foreach ($rows as $r) {
    $d = (int)($r['d'] ?? 0);
    if ($d >= 1 && $d <= $daysInMonth) $fineByDay[$d] = (int)($r['c'] ?? 0);
}

// Maintenance: count BaoTri by NgayBao
$rows = dbQuery(
    "SELECT DAY(NgayBao) AS d, COUNT(*) AS c
     FROM `baotri`
     WHERE IsDeleted = 0
       AND NgayBao >= ? AND NgayBao <= ?
     GROUP BY DAY(NgayBao)",
    [$start, $end]
);
foreach ($rows as $r) {
    $d = (int)($r['d'] ?? 0);
    if ($d >= 1 && $d <= $daysInMonth) $maintByDay[$d] = (int)($r['c'] ?? 0);
}

$labels = [];
$borrow = [];
$reserve = [];
$fine = [];
$maint = [];
for ($d = 1; $d <= $daysInMonth; $d++) {
    $labels[] = (string)$d;
    $borrow[] = (int)$borrowByDay[$d];
    $reserve[] = (int)$reserveByDay[$d];
    $fine[] = (int)$fineByDay[$d];
    $maint[] = (int)$maintByDay[$d];
}

echo json_encode([
    'success' => true,
    'month' => $month,
    'daysInMonth' => $daysInMonth,
    'labels' => $labels,
    'series' => [
        'borrow' => $borrow,
        'reserve' => $reserve,
        'fine' => $fine,
        'maint' => $maint,
    ],
], JSON_UNESCAPED_UNICODE);
