<?php
/**
 * System Admin: audit log list
 * - POST params:
 *   + offset (int)
 *   + limit (int)
 *   + q (string)
 *   + thucThe (string)
 *   + hanhDong (string)
 */

require_once __DIR__ . '/_guard.php';
require_once __DIR__ . '/../../../includes/db.php';

$offsetRaw = isset($_POST['offset']) ? trim((string)$_POST['offset']) : '0';
$limitRaw = isset($_POST['limit']) ? trim((string)$_POST['limit']) : '10';
$q = isset($_POST['q']) ? trim((string)$_POST['q']) : '';
$thucThe = isset($_POST['thucThe']) ? trim((string)$_POST['thucThe']) : '';
$hanhDong = isset($_POST['hanhDong']) ? trim((string)$_POST['hanhDong']) : '';

$offset = ctype_digit($offsetRaw) ? (int)$offsetRaw : 0;
$limit = ctype_digit($limitRaw) ? (int)$limitRaw : 10;
if ($offset < 0) $offset = 0;
if ($limit < 1) $limit = 10;
if ($limit > 50) $limit = 50;

$where = [];
$params = [];

if ($q !== '') {
    $where[] = "(nk.MaNhatKy LIKE ? OR nk.ThucThe LIKE ? OR nk.MaThucThe LIKE ? OR nk.HanhDong LIKE ? OR nk.ThucHienBoi LIKE ? OR nd.HoTen LIKE ?)";
    $like = '%' . $q . '%';
    $params = array_merge($params, [$like, $like, $like, $like, $like, $like]);
}

if ($thucThe !== '') {
    $where[] = "nk.ThucThe = ?";
    $params[] = $thucThe;
}

if ($hanhDong !== '') {
    $where[] = "nk.HanhDong = ?";
    $params[] = $hanhDong;
}

$whereSql = '';
if (!empty($where)) {
    $whereSql = 'WHERE ' . implode(' AND ', $where);
}

try {
    $sql =
        "SELECT nk.MaNhatKy, nk.ThucThe, nk.MaThucThe, nk.HanhDong, nk.ThucHienBoi, nk.ThoiGian,
                nd.HoTen
         FROM `nhatkyhethong` nk
         LEFT JOIN `nguoidung` nd ON nk.ThucHienBoi = nd.MaNguoiDung
         $whereSql
         ORDER BY nk.ThoiGian DESC, nk.MaNhatKy DESC
         LIMIT $limit OFFSET $offset";

    $rows = dbQuery($sql, $params);

    echo json_encode([
        'success' => true,
        'items' => $rows,
        'offset' => $offset,
        'limit' => $limit,
        'returned' => is_array($rows) ? count($rows) : 0,
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
