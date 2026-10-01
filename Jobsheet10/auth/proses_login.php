<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../includes/remember_me.php';

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$attemptKey = hash('sha256', strtolower($username));
$attempt = $_SESSION['login_attempts'][$attemptKey] ?? ['count' => 0, 'locked_until' => 0];

if ($attempt['locked_until'] > time()) {
    $_SESSION['flash'] = ['type' => 'error', 'pesan' => 'Terlalu banyak percobaan gagal. Coba lagi dalam 15 menit.'];
    header('Location: login.php');
    exit;
}

if ($attempt['locked_until'] !== 0) {
    unset($_SESSION['login_attempts'][$attemptKey]);
    $attempt = ['count' => 0, 'locked_until' => 0];
}

require __DIR__ . '/../includes/koneksi.php';

$stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
$stmt->execute(['username' => $username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && password_verify($password, $user['password'])) {
    unset($_SESSION['login_attempts'][$attemptKey]);
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['nama'] = $user['nama'];
    $_SESSION['role'] = $user['role'];
    if (($_POST['remember_me'] ?? '') === '1') {
        issueRememberToken($pdo, $user['id']);
    }
    header('Location: ../index.php');
    exit;
}

$attempt['count']++;
if ($attempt['count'] >= 5) {
    $attempt['locked_until'] = time() + 900;
    $pesan = 'Terlalu banyak percobaan gagal. Login untuk username ini ditunda selama 15 menit.';
} elseif ($attempt['count'] >= 3) {
    $pesan = 'Peringatan: sudah ' . $attempt['count'] . ' kali gagal. Maksimal 5 percobaan sebelum jeda 15 menit.';
} else {
    $pesan = 'Username atau password salah.';
}
$_SESSION['login_attempts'][$attemptKey] = $attempt;
$_SESSION['flash'] = ['type' => 'error', 'pesan' => $pesan];
header('Location: login.php');
exit;
