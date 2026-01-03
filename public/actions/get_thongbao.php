<?php
/**
 * Fetch notifications (paged)
 * GET: offset, limit
 * Output: JSON { success, items: [{MaThongBao,TieuDe,NoiDung,DaDoc,NgayGui}], hasMore, nextOffset }
 */

session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'message' => 'Method không hợp lệ']);
    exit;
}

$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 4;
if ($offset < 0) $offset = 0;
if ($limit <= 0) $limit = 4;
if ($limit > 20) $limit = 20;

$userId = (string)$_SESSION['user_id'];

try {
    $totalRow = dbQueryOne(
        "SELECT COUNT(*) AS cnt FROM `thongbao` WHERE MaNguoiDung = ? AND IsDeleted = 0",
        [$userId]
    );
    $total = ($totalRow && isset($totalRow['cnt'])) ? (int)$totalRow['cnt'] : 0;

    // LIMIT/OFFSET are ints => safe to inline
    $items = dbQuery(
        "SELECT MaThongBao, TieuDe, NoiDung, DaDoc, NgayGui
         FROM `thongbao`
         WHERE MaNguoiDung = ? AND IsDeleted = 0
         ORDER BY NgayGui DESC
         LIMIT $limit OFFSET $offset",
        [$userId]
    );

    // Add formatted date to match dashboard UI
    foreach ($items as &$it) {
        $raw = $it['NgayGui'] ?? null;
        $ts = $raw ? strtotime($raw) : false;
        $it['NgayGuiFormatted'] = ($ts !== false) ? date('d/m/Y', $ts) : 'N/A';
    }
    unset($it);

    $nextOffset = $offset + count($items);
    $hasMore = $nextOffset < $total;

    echo json_encode([
        'success' => true,
        'items' => $items,
        'hasMore' => $hasMore,
        'nextOffset' => $nextOffset,
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
}
