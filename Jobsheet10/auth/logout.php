<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../includes/remember_me.php';
$rememberCookie = $_COOKIE['remember_me'] ?? null;
if ($rememberCookie !== null) {
    clearRememberCookie();
}
$_SESSION = [];
session_destroy();
if ($rememberCookie !== null) {
    require __DIR__ . '/../includes/koneksi.php';
    revokeRememberToken($pdo, $rememberCookie);
}
header('Location: login.php');
exit;
