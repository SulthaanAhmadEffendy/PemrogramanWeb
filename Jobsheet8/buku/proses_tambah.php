<?php
session_start();
require_once __DIR__ . '/../includes/koneksi.php';

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: null;
$judul = trim($_POST['judul'] ?? '');
$pengarang = trim($_POST['pengarang'] ?? '');
$tahun = $_POST['tahun'] ?? '';
$isbn = trim($_POST['isbn'] ?? '');
$stok = $_POST['stok'] ?? '';
$kategori = trim($_POST['kategori'] ?? '');

// Validasi server-side — wajib ada meski sudah divalidasi JS di Jobsheet 5,
// karena validasi client bisa dilewati (nonaktifkan JS / kirim request manual).
$errors = [];
if ($judul === '') {
    $errors[] = "Judul wajib diisi.";
}
if ($pengarang === '') {
    $errors[] = "Pengarang wajib diisi.";
}
if (!is_numeric($tahun) || $tahun < 1900 || $tahun > 2026) {
    $errors[] = "Tahun harus di antara 1900-2026.";
}
if (!is_numeric($stok) || $stok < 0) {
    $errors[] = "Stok tidak boleh negatif.";
}
if ($isbn !== '' && !preg_match('/^[0-9-]+$/', $isbn)) {
    $errors[] = "ISBN hanya boleh berisi angka dan tanda hubung.";
}

if (!empty($errors)) {
    $_SESSION['flash'] = ['type' => 'error', 'pesan' => implode(' ', $errors)];
    header('Location: ' . ($id ? 'tambah.php?id=' . $id : 'tambah.php'));
    exit;
}

if ($id) {
    $stmt = $pdo->prepare('UPDATE buku SET judul = ?, pengarang = ?, tahun = ?, isbn = ?, stok = ?, kategori = ? WHERE id = ?');
    $stmt->execute([$judul, $pengarang, (int) $tahun, $isbn ?: null, (int) $stok, $kategori, $id]);
    $_SESSION['flash'] = ['type' => 'success', 'pesan' => 'Buku berhasil diperbarui.'];
} else {
    $stmt = $pdo->prepare('INSERT INTO buku (judul, pengarang, tahun, isbn, stok, kategori) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->execute([$judul, $pengarang, (int) $tahun, $isbn ?: null, (int) $stok, $kategori]);
    $_SESSION['flash'] = ['type' => 'success', 'pesan' => 'Buku berhasil ditambahkan.'];
}
header('Location: list.php');
exit;