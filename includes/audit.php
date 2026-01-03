<?php
/**
 * Audit logging helper for NhatKyHeThong
 *
 * Table: NhatKyHeThong(MaNhatKy, ThucThe, MaThucThe, HanhDong, ThucHienBoi, ThoiGian, DuLieuTruoc, DuLieuSau)
 *
 * Usage:
 *   auditLog('ThietBi', 'TB001', 'UPDATE', $beforeArray, $afterArray);
 */

require_once __DIR__ . '/db.php';

function auditLog($thucThe, $maThucThe, $hanhDong, $duLieuTruoc = null, $duLieuSau = null, $thucHienBoi = null)
{
    try {
        if ($thucHienBoi === null) {
            if (session_status() !== PHP_SESSION_ACTIVE) {
                // If session not started, we can't resolve actor reliably
                return false;
            }
            if (!isset($_SESSION['user_id'])) {
                return false;
            }
            $thucHienBoi = (string)$_SESSION['user_id'];
        }

        $thucThe = trim((string)$thucThe);
        $maThucThe = trim((string)$maThucThe);
        $hanhDong = trim((string)$hanhDong);
        if ($thucThe === '' || $maThucThe === '' || $hanhDong === '' || trim((string)$thucHienBoi) === '') {
            return false;
        }

        $before = $duLieuTruoc !== null ? json_encode($duLieuTruoc, JSON_UNESCAPED_UNICODE) : null;
        $after = $duLieuSau !== null ? json_encode($duLieuSau, JSON_UNESCAPED_UNICODE) : null;

        // Generate MaNhatKy (NK###)
        $last = dbQueryOne("SELECT MaNhatKy FROM `nhatkyhethong` ORDER BY ThoiGian DESC, MaNhatKy DESC LIMIT 1");
        $nextNum = 1;
        if ($last && !empty($last['MaNhatKy']) && preg_match('/(\d+)$/', (string)$last['MaNhatKy'], $m)) {
            $nextNum = intval($m[1]) + 1;
        }
        $maNhatKy = 'NK' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

        $ok = dbExecute(
            "INSERT INTO `nhatkyhethong` (MaNhatKy, ThucThe, MaThucThe, HanhDong, ThucHienBoi, ThoiGian, DuLieuTruoc, DuLieuSau)
             VALUES (?, ?, ?, ?, ?, NOW(), ?, ?)",
            [$maNhatKy, $thucThe, $maThucThe, $hanhDong, (string)$thucHienBoi, $before, $after]
        );

        return $ok !== false;
    } catch (Exception $e) {
        // Never break main flows because of logging
        return false;
    }
}
