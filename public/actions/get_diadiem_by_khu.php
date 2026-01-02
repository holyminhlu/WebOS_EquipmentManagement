<?php
/**
 * Return list of DiaDiem (rooms/locations) filtered by Khu.
 * Output: JSON { success: bool, items: [{MaDiaDiem, TenDiaDiem}] }
 */

session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode([
        'success' => false,
        'message' => 'Method không hợp lệ'
    ]);
    exit;
}

$khu = isset($_GET['khu']) ? trim((string)$_GET['khu']) : '';
if ($khu === '') {
    echo json_encode([
        'success' => false,
        'message' => 'Thiếu tham số khu'
    ]);
    exit;
}

try {
    // Keep it simple: match exact Khu value
    $items = dbQuery(
        "SELECT MaDiaDiem, TenDiaDiem
         FROM DiaDiem
         WHERE IsDeleted = 0 AND Khu = ?
         ORDER BY TenDiaDiem ASC",
        [$khu]
    );

    // Normalize output keys
    $out = [];
    foreach ($items as $row) {
        $out[] = [
            'MaDiaDiem' => isset($row['MaDiaDiem']) ? (int)$row['MaDiaDiem'] : null,
            'TenDiaDiem' => isset($row['TenDiaDiem']) ? (string)$row['TenDiaDiem'] : ''
        ];
    }

    echo json_encode([
        'success' => true,
        'items' => $out
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi hệ thống: ' . $e->getMessage()
    ]);
}
