<?php
session_start();
require __DIR__ . '/auth.php';


function rupiah($angka) {
    return 'Rp' . number_format((float)$angka, 0, ',', '.');
}

function e($teks) {
    return htmlspecialchars((string)$teks, ENT_QUOTES, 'UTF-8');
}

function tanggal_indo($tgl) {
    $bulan = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    $t = strtotime($tgl);
    return date('j', $t) . ' ' . $bulan[date('n', $t) - 1] . ' ' . date('Y', $t);
}

function tgl_kembali($tanggal_pinjam, $lama_hari) {
    return date('Y-m-d', strtotime($tanggal_pinjam . ' +' . (int)$lama_hari . ' days'));
}


function flash($pesan = null, $tipe = 'ok') {
    if ($pesan !== null) {
        $_SESSION['flash'] = ['pesan' => $pesan, 'tipe' => $tipe];
        return null;
    }
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

function tampil_flash() {
    $f = flash();
    if (!$f) return;
    $kelas = $f['tipe'] === 'err' ? 'alert-danger' : 'alert-success';
    echo '<div class="alert ' . $kelas . ' notice" role="status">' . e($f['pesan']) . '</div>';
}


function label_status($s) {
    $peta = [
        'menunggu'   => ['Menunggu persetujuan', 'status-wait'],
        'berjalan'   => ['Berjalan',             'status-out'],
        'selesai'    => ['Selesai',              'status-done'],
        'ditolak'    => ['Ditolak',              'status-late'],
        'dibatalkan' => ['Dibatalkan',           'status-done'],
    ];
    return $peta[$s] ?? [$s, 'status-done'];
}


function ambil_semua_mobil(PDO $pdo) {
    $sql = "SELECT m.*, (r.id IS NOT NULL) AS sedang_disewa,
                   r.nama_penyewa, (r.tanggal_pinjam + r.lama_hari) AS tgl_kembali
            FROM armada_mobil m
            LEFT JOIN LATERAL (
                SELECT * FROM transaksi_rental t
                WHERE t.mobil_id = m.id AND t.status_sewa = 'berjalan'
                ORDER BY t.tanggal_pinjam DESC LIMIT 1
            ) r ON true
            ORDER BY m.merk_mobil ASC";
    return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}

function ambil_rental_aktif(PDO $pdo) {
    $sql = "SELECT t.*, m.merk_mobil, m.no_polisi, m.foto
            FROM transaksi_rental t JOIN armada_mobil m ON t.mobil_id = m.id
            WHERE t.status_sewa = 'berjalan'
            ORDER BY t.tanggal_pinjam DESC";
    return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}

function ambil_permintaan(PDO $pdo) {
    $sql = "SELECT t.*, m.merk_mobil, m.no_polisi, m.foto,
                   EXISTS (SELECT 1 FROM transaksi_rental b
                           WHERE b.mobil_id = t.mobil_id AND b.status_sewa = 'berjalan') AS unit_sibuk
            FROM transaksi_rental t JOIN armada_mobil m ON t.mobil_id = m.id
            WHERE t.status_sewa = 'menunggu'
            ORDER BY t.id ASC";
    return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}

function ambil_pesanan_user(PDO $pdo, $uid) {
    $stmt = $pdo->prepare("SELECT t.*, m.merk_mobil, m.no_polisi, m.foto
            FROM transaksi_rental t JOIN armada_mobil m ON t.mobil_id = m.id
            WHERE t.pengguna_id = ? ORDER BY t.id DESC");
    $stmt->execute([$uid]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function unit_sedang_disewa(PDO $pdo, $mobil_id) {
    $stmt = $pdo->prepare("SELECT 1 FROM transaksi_rental WHERE mobil_id = ? AND status_sewa = 'berjalan'");
    $stmt->execute([$mobil_id]);
    return (bool)$stmt->fetchColumn();
}


function simpan_foto($file) {
    if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return [null, null];
    }
    if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] > 2 * 1024 * 1024) {
        return [null, 'Foto gagal diunggah. Ukuran maksimal 2 MB.'];
    }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $ext  = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$mime] ?? null;
    if (!$ext) {
        return [null, 'Format foto harus JPG, PNG, atau WebP.'];
    }
    $dir = __DIR__ . '/../uploads/';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $nama = bin2hex(random_bytes(8)) . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], $dir . $nama)) {
        return [null, 'Foto tidak bisa disimpan. Cek izin tulis folder uploads.'];
    }
    return [$nama, null];
}
