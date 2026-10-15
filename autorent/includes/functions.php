<?php
session_start();
require __DIR__ . '/auth.php';


function rupiah($angka) {
    return 'Rp' . number_format((float)$angka, 0, ',', '.');
}

function e($teks) {
    return htmlspecialchars((string)$teks, ENT_QUOTES, 'UTF-8');
}

function db_bool($nilai) {
    return $nilai === true || $nilai === 1 || in_array($nilai, ['1', 't', 'true'], true);
}

function tanggal_indo($tgl) {
    $bulan = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    $t = strtotime($tgl);
    return date('j', $t) . ' ' . $bulan[date('n', $t) - 1] . ' ' . date('Y', $t);
}

function tgl_kembali($tanggal_pinjam, $lama_hari) {
    return date('Y-m-d', strtotime($tanggal_pinjam . ' +' . (int)$lama_hari . ' days'));
}

function tanggal_valid($tanggal) {
    $nilai = DateTime::createFromFormat('!Y-m-d', (string)$tanggal);
    return $nilai && $nilai->format('Y-m-d') === $tanggal;
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
                   COALESCE(jadwal.periods, '[]'::json) AS periods,
                   r.nama_penyewa, (r.tanggal_pinjam + r.lama_hari) AS tgl_kembali
            FROM armada_mobil m
            LEFT JOIN LATERAL (
                SELECT * FROM transaksi_rental t
                WHERE t.mobil_id = m.id AND t.status_sewa = 'berjalan'
                  AND t.tanggal_pinjam <= CURRENT_DATE
                  AND (t.tanggal_pinjam + t.lama_hari) > CURRENT_DATE
                ORDER BY t.tanggal_pinjam DESC LIMIT 1
            ) r ON true
            LEFT JOIN LATERAL (
                SELECT json_agg(json_build_object(
                    'start', t.tanggal_pinjam::text,
                    'end', (t.tanggal_pinjam + t.lama_hari)::text,
                    'status', t.status_sewa
                )) AS periods
                FROM transaksi_rental t
                WHERE t.mobil_id = m.id AND t.status_sewa IN ('menunggu', 'berjalan')
                  AND (t.status_sewa = 'berjalan'
                       OR (t.tanggal_pinjam + t.lama_hari) > CURRENT_DATE)
            ) jadwal ON true
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
                           WHERE b.mobil_id = t.mobil_id AND b.id <> t.id
                             AND b.status_sewa IN ('menunggu', 'berjalan')
                             AND (b.status_sewa = 'berjalan'
                                  OR (b.tanggal_pinjam < (t.tanggal_pinjam + t.lama_hari)
                                      AND (b.tanggal_pinjam + b.lama_hari) > t.tanggal_pinjam))) AS unit_sibuk
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
    $stmt = $pdo->prepare("SELECT 1 FROM transaksi_rental
                           WHERE mobil_id = ? AND status_sewa = 'berjalan'
                             AND tanggal_pinjam <= CURRENT_DATE
                             AND (tanggal_pinjam + lama_hari) > CURRENT_DATE");
    $stmt->execute([$mobil_id]);
    return (bool)$stmt->fetchColumn();
}

function unit_bentrok_periode(PDO $pdo, $mobil_id, $mulai, $selesai, $abaikan_id = null) {
    $sql = "SELECT 1 FROM transaksi_rental
            WHERE mobil_id = ? AND status_sewa IN ('menunggu', 'berjalan')
              AND (status_sewa = 'berjalan'
                   OR (tanggal_pinjam < ? AND (tanggal_pinjam + lama_hari) > ?))";
    $parameter = [$mobil_id, $selesai, $mulai];
    if ($abaikan_id !== null) {
        $sql .= " AND id <> ?";
        $parameter[] = $abaikan_id;
    }
    $stmt = $pdo->prepare($sql . " LIMIT 1");
    $stmt->execute($parameter);
    return (bool)$stmt->fetchColumn();
}

function unit_tersedia_pada(array $mobil, $mulai, $lama_hari = 1) {
    $selesai = tgl_kembali($mulai, $lama_hari);
    $periode = json_decode($mobil['periods'] ?? '[]', true) ?: [];
    foreach ($periode as $pesanan) {
        if ($pesanan['status'] === 'berjalan') {
            return false;
        }
        if ($pesanan['start'] < $selesai && $pesanan['end'] > $mulai) {
            return false;
        }
    }
    return true;
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
