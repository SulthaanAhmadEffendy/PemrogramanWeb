<?php
session_start();
require_once __DIR__ . '/../includes/koneksi.php';

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: null;
$nama = trim($_POST['nama'] ?? '');
$noAnggota = trim($_POST['no_anggota'] ?? '');
$alamat = trim($_POST['alamat'] ?? '');
$noHp = trim($_POST['no_hp'] ?? '');

$errors = [];
if ($nama === '') {
    $errors[] = "Nama wajib diisi.";
}
if ($noAnggota === '') {
    $errors[] = "No. Anggota wajib diisi.";
}
if ($noHp !== '' && !preg_match('/^[0-9+() -]+$/', $noHp)) {
    $errors[] = "No. HP hanya boleh berisi angka, spasi, dan tanda baca nomor telepon.";
}

if ($noAnggota !== '') {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM anggota WHERE LOWER(no_anggota) = LOWER(?) AND id <> COALESCE(?, 0)');
    $stmt->execute([$noAnggota, $id]);
    if ((int) $stmt->fetchColumn() > 0) {
        $errors[] = "No. Anggota sudah digunakan.";
    }
}

if (!empty($errors)) {
    $_SESSION['flash'] = ['type' => 'error', 'pesan' => implode(' ', $errors)];
    header('Location: ' . ($id ? 'tambah.php?id=' . $id : 'tambah.php'));
    exit;
}

if ($id) {
    $stmt = $pdo->prepare('UPDATE anggota SET nama = ?, no_anggota = ?, alamat = ?, no_hp = ? WHERE id = ?');
    $stmt->execute([$nama, $noAnggota, $alamat ?: null, $noHp ?: null, $id]);
    $_SESSION['flash'] = ['type' => 'success', 'pesan' => 'Anggota berhasil diperbarui.'];
} else {
    $stmt = $pdo->prepare('INSERT INTO anggota (nama, no_anggota, alamat, no_hp) VALUES (?, ?, ?, ?)');
    $stmt->execute([$nama, $noAnggota, $alamat ?: null, $noHp ?: null]);
    $_SESSION['flash'] = ['type' => 'success', 'pesan' => 'Anggota berhasil ditambahkan.'];
}
header('Location: list.php');
exit;