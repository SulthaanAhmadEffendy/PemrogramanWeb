<?php
session_start();
require_once __DIR__ . '/../includes/koneksi.php';

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    $_SESSION['flash'] = ['type' => 'error', 'pesan' => 'ID anggota tidak valid.'];
    header('Location: list.php');
    exit;
}

$stmt = $pdo->prepare('DELETE FROM anggota WHERE id = ?');
$stmt->execute([$id]);
$_SESSION['flash'] = ['type' => 'success', 'pesan' => 'Anggota berhasil dihapus.'];
header('Location: list.php');
exit;