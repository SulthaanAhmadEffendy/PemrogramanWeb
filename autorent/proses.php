<?php
// Satu pintu untuk semua form. Setiap aksi dicek perannya.
require __DIR__ . '/config/database.php';
require __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') arahkan('index.php');
cek_csrf();
$aksi = $_POST['aksi'] ?? '';

/* ---------- Publik: masuk, daftar, keluar ---------- */
if ($aksi === 'masuk') {
    $stmt = $pdo->prepare("SELECT * FROM pengguna WHERE email = ?");
    $stmt->execute([strtolower(trim($_POST['email'] ?? ''))]);
    $u = $stmt->fetch();
    if (!$u || !password_verify($_POST['password'] ?? '', $u['password'])) {
        flash('Email atau password salah.', 'err');
        arahkan('login.php');
    }
    session_regenerate_id(true);
    $_SESSION['pengguna'] = ['id' => $u['id'], 'nama' => $u['nama'], 'role' => $u['role']];
    arahkan('index.php');
}

if ($aksi === 'daftar') {
    $nama  = trim($_POST['nama'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $pass  = $_POST['password'] ?? '';
    $galat = null;
    if ($nama === '')                                  $galat = 'Nama wajib diisi.';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $galat = 'Format email tidak valid.';
    elseif (strlen($pass) < 6)                         $galat = 'Password minimal 6 karakter.';
    else {
        $ada = $pdo->prepare("SELECT 1 FROM pengguna WHERE email = ?");
        $ada->execute([$email]);
        if ($ada->fetchColumn()) $galat = 'Email sudah terdaftar. Silakan masuk.';
    }
    if ($galat) {
        flash($galat, 'err');
        arahkan('login.php?daftar=1');
    }
    // Pendaftaran publik selalu menjadi "user", tidak pernah admin
    $stmt = $pdo->prepare("INSERT INTO pengguna (nama, email, password, role) VALUES (?, ?, ?, 'user') RETURNING id");
    $stmt->execute([$nama, $email, password_hash($pass, PASSWORD_DEFAULT)]);
    session_regenerate_id(true);
    $_SESSION['pengguna'] = ['id' => $stmt->fetchColumn(), 'nama' => $nama, 'role' => 'user'];
    flash("Selamat datang, $nama. Akun Anda sudah dibuat.");
    arahkan('index.php');
}

if ($aksi === 'keluar') {
    $_SESSION = [];
    session_destroy();
    arahkan('login.php');
}

/* ---------- Harus login ---------- */
$p = pengguna();
if (!$p) arahkan('login.php');
$beranda = $p['role'] === 'admin' ? 'admin.php' : 'user.php';

/* ---------- Aksi USER ---------- */
if ($p['role'] === 'user') {

    if ($aksi === 'pesan') {
        $cek = $pdo->prepare("SELECT harga_per_hari FROM armada_mobil WHERE id = ?");
        $cek->execute([$_POST['mobil_id'] ?? 0]);
        $mobil = $cek->fetch();
        $tgl   = $_POST['tanggal_pinjam'] ?? '';
        $lama  = max(1, (int)($_POST['lama_hari'] ?? 1));

        if (!$mobil)                                      flash('Unit tidak ditemukan.', 'err');
        elseif ($tgl < date('Y-m-d'))                     flash('Tanggal pinjam tidak boleh sebelum hari ini.', 'err');
        elseif (unit_sedang_disewa($pdo, $_POST['mobil_id'])) flash('Unit ini sedang disewa. Pilih unit lain.', 'err');
        else {
            $total = $lama * $mobil['harga_per_hari'];
            $pdo->prepare("INSERT INTO transaksi_rental
                    (mobil_id, pengguna_id, nama_penyewa, tanggal_pinjam, lama_hari, total_biaya, status_sewa)
                    VALUES (?, ?, ?, ?, ?, ?, 'menunggu')")
                ->execute([$_POST['mobil_id'], $p['id'], $p['nama'], $tgl, $lama, $total]);
            flash('Pesanan terkirim, total ' . rupiah($total) . '. Admin akan mengonfirmasi.');
        }
    }

    if ($aksi === 'batal') {
        $pdo->prepare("UPDATE transaksi_rental SET status_sewa = 'dibatalkan'
                       WHERE id = ? AND pengguna_id = ? AND status_sewa = 'menunggu'")
            ->execute([$_POST['id'] ?? 0, $p['id']]);
        flash('Pesanan dibatalkan.');
    }
    arahkan($beranda);
}

/* ---------- Aksi ADMIN ---------- */
if ($aksi === 'tambah_mobil') {
    $nopol = strtoupper(trim($_POST['no_polisi']));
    [$foto, $err] = simpan_foto($_FILES['foto'] ?? null);
    if ($err) {
        flash($err, 'err');
    } else {
        $pdo->prepare("INSERT INTO armada_mobil (no_polisi, merk_mobil, tipe_transmisi, harga_per_hari, foto) VALUES (?, ?, ?, ?, ?)")
            ->execute([$nopol, trim($_POST['merk_mobil']), $_POST['tipe_transmisi'], (int)$_POST['harga_per_hari'], $foto]);
        flash("Unit $nopol sudah masuk daftar armada.");
    }
}

if ($aksi === 'ganti_foto') {
    [$foto, $err] = simpan_foto($_FILES['foto'] ?? null);
    if ($err || !$foto) {
        flash($err ?: 'Pilih file foto terlebih dahulu.', 'err');
    } else {
        $lama = $pdo->prepare("SELECT foto FROM armada_mobil WHERE id = ?");
        $lama->execute([$_POST['id']]);
        $fotoLama = $lama->fetchColumn();
        $pdo->prepare("UPDATE armada_mobil SET foto = ? WHERE id = ?")->execute([$foto, $_POST['id']]);
        if ($fotoLama && is_file(__DIR__ . '/uploads/' . basename($fotoLama))) {
            unlink(__DIR__ . '/uploads/' . basename($fotoLama));
        }
        flash('Foto unit diperbarui.');
    }
}

// Sewa langsung di kantor (tanpa akun pelanggan)
if ($aksi === 'tambah_rental') {
    $cek = $pdo->prepare("SELECT harga_per_hari FROM armada_mobil WHERE id = ?");
    $cek->execute([$_POST['mobil_id']]);
    $mobil = $cek->fetch();
    if (!$mobil) {
        flash('Unit tidak ditemukan. Pilih unit dari daftar.', 'err');
    } elseif (unit_sedang_disewa($pdo, $_POST['mobil_id'])) {
        flash('Unit ini sedang disewa.', 'err');
    } else {
        $lama  = max(1, (int)$_POST['lama_hari']);
        $total = $lama * $mobil['harga_per_hari'];
        $pdo->prepare("INSERT INTO transaksi_rental (mobil_id, nama_penyewa, tanggal_pinjam, lama_hari, total_biaya, status_sewa)
                       VALUES (?, ?, ?, ?, ?, 'berjalan')")
            ->execute([$_POST['mobil_id'], trim($_POST['nama_penyewa']), $_POST['tanggal_pinjam'], $lama, $total]);
        flash('Sewa atas nama ' . trim($_POST['nama_penyewa']) . ' tercatat, total ' . rupiah($total) . '.');
    }
}

if ($aksi === 'setujui') {
    $q = $pdo->prepare("SELECT mobil_id FROM transaksi_rental WHERE id = ? AND status_sewa = 'menunggu'");
    $q->execute([$_POST['id']]);
    $mobil_id = $q->fetchColumn();
    if (!$mobil_id) {
        flash('Permintaan tidak ditemukan atau sudah diproses.', 'err');
    } elseif (unit_sedang_disewa($pdo, $mobil_id)) {
        flash('Unit masih disewa orang lain. Tolak permintaan atau tunggu sampai unit kembali.', 'err');
    } else {
        $pdo->prepare("UPDATE transaksi_rental SET status_sewa = 'berjalan' WHERE id = ?")->execute([$_POST['id']]);
        flash('Permintaan disetujui. Sewa mulai berjalan.');
    }
}

if ($aksi === 'tolak') {
    $pdo->prepare("UPDATE transaksi_rental SET status_sewa = 'ditolak' WHERE id = ? AND status_sewa = 'menunggu'")
        ->execute([$_POST['id']]);
    flash('Permintaan ditolak.');
}

if ($aksi === 'selesai') {
    $pdo->prepare("UPDATE transaksi_rental SET status_sewa = 'selesai' WHERE id = ? AND status_sewa = 'berjalan'")
        ->execute([$_POST['id']]);
    flash('Mobil sudah dikembalikan dan sewa ditutup.');
}

// Aksi admin hanya boleh dijalankan admin
if ($p['role'] !== 'admin') arahkan('index.php');
arahkan($beranda);
