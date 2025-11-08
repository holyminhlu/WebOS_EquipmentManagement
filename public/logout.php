<?php
/**
 * Trang đăng xuất
 * 
 * @author System Development Team
 * @version 1.0
 */

session_start();

require_once __DIR__ . '/../includes/auth.php';

// Đăng xuất
logoutUser();

// Xóa cookie remember
if (isset($_COOKIE['remember_user'])) {
    setcookie('remember_user', '', time() - 3600, '/');
}

// Chuyển về trang chủ
header('Location: index.php');
exit;

