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
    $daftar_pelanggan = ($_POST['ke_akun_pelanggan'] ?? '') === '1';
    if ($daftar_pelanggan && (!pengguna() || pengguna()['role'] !== 'admin')) {
        arahkan('index.php');
    }

    $nama_cookie = session_name();
    $parameter_cookie = session_get_cookie_params();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        setcookie($nama_cookie, '', [
            'expires' => time() - 42000,
            'path' => $parameter_cookie['path'],
            'domain' => $parameter_cookie['domain'],
            'secure' => $parameter_cookie['secure'],
            'httponly' => $parameter_cookie['httponly'],
            'samesite' => $parameter_cookie['samesite'] ?? 'Lax',
        ]);
    }
    session_destroy();
    session_start();
    session_regenerate_id(true);
    flash('Anda berhasil keluar dari akun.');
    if ($daftar_pelanggan) {
        arahkan('login.php?daftar=1');
    }
    arahkan('login.php');
}

/* ---------- Harus login ---------- */
$p = pengguna();
if (!$p) arahkan('login.php');
$beranda = $p['role'] === 'admin' ? 'admin.php' : 'user.php';

/* ---------- Aksi USER ---------- */
if ($p['role'] === 'user') {

    if ($aksi === 'pesan') {
        $mobil_id = (int)($_POST['mobil_id'] ?? 0);
        $pdo->beginTransaction();
        $cek = $pdo->prepare("SELECT harga_per_hari FROM armada_mobil WHERE id = ? FOR UPDATE");
        $cek->execute([$mobil_id]);
        $mobil = $cek->fetch();
        $tgl   = $_POST['tanggal_pinjam'] ?? '';
        $lama  = filter_var($_POST['lama_hari'] ?? null, FILTER_VALIDATE_INT);

        $galat = null;
        if (!$mobil) $galat = 'Unit tidak ditemukan.';
        elseif (!$lama || $lama < 1) $galat = 'Lama sewa minimal 1 hari.';
        elseif ($lama > 365) $galat = 'Lama sewa maksimal 365 hari.';
        elseif (!tanggal_valid($tgl) || $tgl < date('Y-m-d')) $galat = 'Tanggal pinjam tidak valid atau sudah lewat.';
        elseif ($mobil['harga_per_hari'] > intdiv(PHP_INT_MAX, $lama)) $galat = 'Total biaya melebihi batas yang diizinkan.';
        elseif (unit_bentrok_periode($pdo, $mobil_id, $tgl, tgl_kembali($tgl, $lama))) $galat = 'Mobil sudah dipesan pada tanggal tersebut. Silakan pilih tanggal atau unit lain.';

        if ($galat) {
            $pdo->rollBack();
            flash($galat, 'err');
        } else {
            $total = $lama * $mobil['harga_per_hari'];
            $pdo->prepare("INSERT INTO transaksi_rental
                    (mobil_id, pengguna_id, nama_penyewa, tanggal_pinjam, lama_hari, total_biaya, status_sewa)
                    VALUES (?, ?, ?, ?, ?, ?, 'menunggu')")
                ->execute([$mobil_id, $p['id'], $p['nama'], $tgl, $lama, $total]);
            $pdo->commit();
            flash('Pesanan terkirim, total ' . rupiah($total) . '. Admin akan mengonfirmasi.');
        }
    }

    if ($aksi === 'batal') {
        $stmt = $pdo->prepare("UPDATE transaksi_rental SET status_sewa = 'dibatalkan'
                       WHERE id = ? AND pengguna_id = ? AND status_sewa = 'menunggu'");
        $stmt->execute([(int)($_POST['id'] ?? 0), $p['id']]);
        $berhasil = $stmt->rowCount() > 0;
        flash($berhasil ? 'Pesanan dibatalkan.' : 'Pesanan tidak ditemukan atau sudah diproses.', $berhasil ? 'ok' : 'err');
    }
    arahkan($beranda);
}

if ($p['role'] !== 'admin') arahkan('index.php');

/* ---------- Aksi ADMIN ---------- */
if ($aksi === 'tambah_mobil') {
    $nopol = strtoupper(trim($_POST['no_polisi'] ?? ''));
    $merk = trim($_POST['merk_mobil'] ?? '');
    $transmisi = $_POST['tipe_transmisi'] ?? '';
    $harga = filter_var($_POST['harga_per_hari'] ?? null, FILTER_VALIDATE_INT);
    if ($nopol === '' || strlen($nopol) > 15 || $merk === '' || strlen($merk) > 80
        || !in_array($transmisi, ['Manual', 'Auto'], true) || $harga === false || $harga < 0) {
        flash('Lengkapi data mobil dengan format yang benar.', 'err');
        arahkan($beranda);
    }
    [$foto, $err] = simpan_foto($_FILES['foto'] ?? null);
    if ($err) {
        flash($err, 'err');
    } else {
        $pdo->prepare("INSERT INTO armada_mobil (no_polisi, merk_mobil, tipe_transmisi, harga_per_hari, foto) VALUES (?, ?, ?, ?, ?)")
            ->execute([$nopol, $merk, $transmisi, $harga, $foto]);
        flash("Unit $nopol sudah masuk daftar armada.");
    }
}

if ($aksi === 'ubah_mobil') {
    $id = (int)($_POST['id'] ?? 0);
    $nopol = strtoupper(trim($_POST['no_polisi'] ?? ''));
    $merk = trim($_POST['merk_mobil'] ?? '');
    $transmisi = $_POST['tipe_transmisi'] ?? '';
    $harga = filter_var($_POST['harga_per_hari'] ?? null, FILTER_VALIDATE_INT);
    $ada = $pdo->prepare("SELECT id, foto FROM armada_mobil WHERE id = ?");
    $ada->execute([$id]);
    $unit = $ada->fetch();

    if (!$unit) {
        flash('Unit tidak ditemukan.', 'err');
    } elseif ($nopol === '' || strlen($nopol) > 15 || $merk === '' || strlen($merk) > 80
        || !in_array($transmisi, ['Manual', 'Auto'], true) || $harga === false || $harga < 0) {
        flash('Lengkapi data mobil dengan format yang benar.', 'err');
    } else {
        $duplikat = $pdo->prepare("SELECT 1 FROM armada_mobil WHERE no_polisi = ? AND id <> ?");
        $duplikat->execute([$nopol, $id]);
        if ($duplikat->fetchColumn()) {
            flash('Nomor polisi sudah digunakan oleh unit lain.', 'err');
        } else {
            [$foto, $err] = simpan_foto($_FILES['foto'] ?? null);
            if ($err) {
                flash($err, 'err');
            } else {
                $pdo->prepare("UPDATE armada_mobil
                               SET no_polisi = ?, merk_mobil = ?, tipe_transmisi = ?,
                                   harga_per_hari = ?, foto = COALESCE(?, foto)
                               WHERE id = ?")
                    ->execute([$nopol, $merk, $transmisi, $harga, $foto, $id]);
                $fotoLama = $unit['foto'];
                if ($foto && $fotoLama && is_file(__DIR__ . '/uploads/' . basename($fotoLama))) {
                    unlink(__DIR__ . '/uploads/' . basename($fotoLama));
                }
                flash("Data unit $nopol diperbarui.");
            }
        }
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
    $mobil_id = (int)($_POST['mobil_id'] ?? 0);
    $pdo->beginTransaction();
    $cek = $pdo->prepare("SELECT harga_per_hari FROM armada_mobil WHERE id = ? FOR UPDATE");
    $cek->execute([$mobil_id]);
    $mobil = $cek->fetch();
    $nama = trim($_POST['nama_penyewa'] ?? '');
    $tgl = $_POST['tanggal_pinjam'] ?? '';
    $lama = filter_var($_POST['lama_hari'] ?? null, FILTER_VALIDATE_INT);
    if (!$mobil) {
        $pdo->rollBack();
        flash('Unit tidak ditemukan. Pilih unit dari daftar.', 'err');
    } elseif ($nama === '' || strlen($nama) > 100) {
        $pdo->rollBack();
        flash('Nama penyewa wajib diisi (maksimal 100 karakter).', 'err');
    } elseif (!$lama || $lama < 1) {
        $pdo->rollBack();
        flash('Lama sewa minimal 1 hari.', 'err');
    } elseif ($lama > 365) {
        $pdo->rollBack();
        flash('Lama sewa maksimal 365 hari.', 'err');
    } elseif (!tanggal_valid($tgl) || $tgl < date('Y-m-d')) {
        $pdo->rollBack();
        flash('Tanggal pinjam tidak valid atau sudah lewat.', 'err');
    } elseif ($mobil['harga_per_hari'] > intdiv(PHP_INT_MAX, $lama)) {
        $pdo->rollBack();
        flash('Total biaya melebihi batas yang diizinkan.', 'err');
    } elseif (unit_bentrok_periode($pdo, $mobil_id, $tgl, tgl_kembali($tgl, $lama))) {
        $pdo->rollBack();
        flash('Mobil sudah dipesan pada tanggal tersebut.', 'err');
    } else {
        $total = $lama * $mobil['harga_per_hari'];
        $pdo->prepare("INSERT INTO transaksi_rental (mobil_id, nama_penyewa, tanggal_pinjam, lama_hari, total_biaya, status_sewa)
                       VALUES (?, ?, ?, ?, ?, 'berjalan')")
            ->execute([$mobil_id, $nama, $tgl, $lama, $total]);
        $pdo->commit();
        flash('Sewa atas nama ' . $nama . ' tercatat, total ' . rupiah($total) . '.');
    }
}

if ($aksi === 'setujui') {
    $id = (int)($_POST['id'] ?? 0);
    $pdo->beginTransaction();
    $q = $pdo->prepare("SELECT mobil_id FROM transaksi_rental WHERE id = ? AND status_sewa = 'menunggu'");
    $q->execute([$id]);
    $mobil_id = $q->fetchColumn();
    if (!$mobil_id) {
        $pdo->rollBack();
        flash('Permintaan tidak ditemukan atau sudah diproses.', 'err');
    } else {
        $kunci = $pdo->prepare("SELECT id FROM armada_mobil WHERE id = ? FOR UPDATE");
        $kunci->execute([$mobil_id]);
        $q->execute([$id]);
        if (!$q->fetchColumn()) {
            $pdo->rollBack();
            flash('Permintaan tidak ditemukan atau sudah diproses.', 'err');
            arahkan($beranda);
        }
        $pesanan = $pdo->prepare("SELECT tanggal_pinjam, lama_hari FROM transaksi_rental WHERE id = ?");
        $pesanan->execute([$id]);
        $periode = $pesanan->fetch();
        if (!$periode || tgl_kembali($periode['tanggal_pinjam'], $periode['lama_hari']) <= date('Y-m-d')) {
            $pdo->rollBack();
            flash('Periode permintaan sudah berakhir dan tidak dapat disetujui.', 'err');
        } elseif (unit_bentrok_periode($pdo, $mobil_id, $periode['tanggal_pinjam'], tgl_kembali($periode['tanggal_pinjam'], $periode['lama_hari']), $id)) {
            $pdo->rollBack();
            flash('Periode permintaan bertabrakan dengan pesanan lain untuk unit ini.', 'err');
        } else {
            $update = $pdo->prepare("UPDATE transaksi_rental SET status_sewa = 'berjalan' WHERE id = ? AND status_sewa = 'menunggu'");
            $update->execute([$id]);
            $pdo->commit();
            $berhasil = $update->rowCount() > 0;
            $pesan = $periode['tanggal_pinjam'] > date('Y-m-d')
                ? 'Permintaan disetujui. Mobil dijadwalkan mulai ' . tanggal_indo($periode['tanggal_pinjam']) . '.'
                : 'Permintaan disetujui. Sewa mulai berjalan.';
            flash($berhasil ? $pesan : 'Permintaan sudah diproses.', $berhasil ? 'ok' : 'err');
        }
    }
}

if ($aksi === 'tolak') {
    $tolak = $pdo->prepare("UPDATE transaksi_rental SET status_sewa = 'ditolak' WHERE id = ? AND status_sewa = 'menunggu'");
    $tolak->execute([$_POST['id']]);
    flash($tolak->rowCount() ? 'Permintaan ditolak.' : 'Permintaan tidak ditemukan atau sudah diproses.', $tolak->rowCount() ? 'ok' : 'err');
}

if ($aksi === 'selesai') {
    $selesai = $pdo->prepare("UPDATE transaksi_rental SET status_sewa = 'selesai'
                              WHERE id = ? AND status_sewa = 'berjalan' AND tanggal_pinjam <= CURRENT_DATE");
    $selesai->execute([$_POST['id']]);
    flash($selesai->rowCount() ? 'Mobil sudah dikembalikan dan sewa ditutup.' : 'Sewa belum dimulai atau sudah ditutup.', $selesai->rowCount() ? 'ok' : 'err');
}

arahkan($beranda);
