<?php
function arahkan($url) {
    header("Location: $url");
    exit;
}

function pengguna() {
    return $_SESSION['pengguna'] ?? null;
}


function wajib_login($role) {
    $p = pengguna();
    if (!$p) arahkan('login.php');
    if ($p['role'] !== $role) arahkan('index.php');
    return $p;
}

function csrf_token() {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return $_SESSION['csrf'];
}

function csrf_field() {
    return '<input type="hidden" name="csrf" value="' . csrf_token() . '">';
}

function cek_csrf() {
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        flash('Sesi formulir kedaluwarsa. Silakan ulangi.', 'err');
        arahkan('index.php');
    }
}
