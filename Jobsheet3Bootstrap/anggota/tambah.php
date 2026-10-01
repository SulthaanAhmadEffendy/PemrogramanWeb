<?php
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/layout.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: null;
$member = ['nama' => '', 'no_anggota' => '', 'alamat' => '', 'no_hp' => ''];

if ($id) {
    $statement = $pdo->prepare('SELECT * FROM anggota WHERE id = ?');
    $statement->execute([$id]);
    $existingMember = $statement->fetch();
    if (!$existingMember) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Data anggota tidak ditemukan.'];
        header('Location: list.php');
        exit;
    }
    $member = $existingMember;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken()) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Sesi formulir tidak valid. Silakan coba lagi.'];
    } else {
        $nama = trim($_POST['nama'] ?? '');
        $noAnggota = trim($_POST['no_anggota'] ?? '');
        $alamat = trim($_POST['alamat'] ?? '');
        $noHp = trim($_POST['no_hp'] ?? '');

        if ($nama === '' || $noAnggota === '') {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Nama dan nomor anggota wajib diisi.'];
        } elseif ($noHp !== '' && !preg_match('/^[0-9+() -]+$/', $noHp)) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Format nomor HP tidak valid.'];
        } else {
            try {
                $values = [$nama, $noAnggota, $alamat !== '' ? $alamat : null, $noHp !== '' ? $noHp : null];
                if ($id) {
                    $values[] = $id;
                    $statement = $pdo->prepare('UPDATE anggota SET nama = ?, no_anggota = ?, alamat = ?, no_hp = ? WHERE id = ?');
                } else {
                    $statement = $pdo->prepare('INSERT INTO anggota (nama, no_anggota, alamat, no_hp) VALUES (?, ?, ?, ?)');
                }
                $statement->execute($values);
                $_SESSION['flash'] = ['type' => 'success', 'message' => $id ? 'Data anggota berhasil diperbarui.' : 'Data anggota berhasil ditambahkan.'];
                header('Location: list.php');
                exit;
            } catch (PDOException $exception) {
                if ($exception->getCode() === '23000') {
                    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Nomor anggota tersebut sudah digunakan.'];
                } else {
                    throw $exception;
                }
            }
        }
    }
}

renderHeader($id ? 'Edit Anggota' : 'Tambah Anggota', 'add-member', '../');
?>
<h1 class="h3 fw-bold text-dark mb-4"><i class="bi bi-person-plus text-primary me-2"></i><?= $id ? 'Edit Data Anggota' : 'Pendaftaran Anggota' ?></h1>
<?php renderFlash(); ?>
<section class="card border-0 shadow-sm rounded-4 p-3 p-md-4" style="max-width: 800px">
  <form method="post" action="tambah.php<?= $id ? '?id=' . (int) $id : '' ?>">
    <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
    <div class="row g-3">
      <div class="col-md-8"><label class="form-label" for="nama">Nama Lengkap</label><input class="form-control" id="nama" name="nama" maxlength="255" value="<?= e($member['nama']) ?>" required></div>
      <div class="col-md-4"><label class="form-label" for="no_anggota">No. Anggota</label><input class="form-control" id="no_anggota" name="no_anggota" maxlength="100" value="<?= e($member['no_anggota']) ?>" required></div>
      <div class="col-12"><label class="form-label" for="alamat">Alamat Domisili</label><textarea class="form-control" id="alamat" name="alamat" rows="3"><?= e($member['alamat'] ?? '') ?></textarea></div>
      <div class="col-md-6"><label class="form-label" for="no_hp">Nomor HP / WhatsApp</label><input class="form-control" type="tel" id="no_hp" name="no_hp" maxlength="30" value="<?= e($member['no_hp'] ?? '') ?>"></div>
    </div>
    <div class="d-flex justify-content-end gap-2 mt-4"><a class="btn btn-light" href="list.php">Batal</a><button class="btn btn-primary" type="submit"><i class="bi bi-save me-2"></i>Simpan Anggota</button></div>
  </form>
</section>
<?php renderFooter(); ?>