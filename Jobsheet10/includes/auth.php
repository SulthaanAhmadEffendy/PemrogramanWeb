<?php
// Guard clause: di-include di baris paling atas setiap halaman yang
// membutuhkan login (sebelum header.php mengeluarkan output apa pun),
// agar header('Location: ...') masih bisa dipanggil.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/remember_me.php';
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_me'])) {
    require __DIR__ . '/koneksi.php';
    restoreRememberedUser($pdo);
}

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}
