<?php
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/layout.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    if (!verifyCsrfToken() || !$id) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Permintaan tidak valid.'];
    } else {
        $statement = $pdo->prepare('DELETE FROM anggota WHERE id = ?');
        $statement->execute([$id]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Data anggota berhasil dihapus.'];
    }
    header('Location: list.php');
    exit;
}

$members = $pdo->query('SELECT id, nama, no_anggota, alamat, no_hp FROM anggota ORDER BY id DESC')->fetchAll();
renderHeader('Daftar Anggota', 'members', '../');
?>
<div class="d-flex justify-content-between align-items-center gap-3 mb-4">
  <h1 class="h3 fw-bold text-dark mb-0"><i class="bi bi-people text-primary me-2"></i>Daftar Anggota</h1>
  <a href="tambah.php" class="btn btn-primary"><i class="bi bi-person-plus me-1"></i>Anggota Baru</a>
</div>
<?php renderFlash(); ?>
<section class="card border-0 shadow-sm rounded-4 overflow-hidden">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light"><tr><th class="ps-4">No. Anggota</th><th>Nama Lengkap</th><th>Alamat</th><th>No. HP</th><th class="text-end pe-4">Aksi</th></tr></thead>
      <tbody>
        <?php if (!$members): ?><tr><td colspan="5" class="text-center text-secondary py-4">Belum ada data anggota.</td></tr><?php endif; ?>
        <?php foreach ($members as $member): ?>
          <tr>
            <td class="ps-4"><span class="badge bg-secondary-subtle text-secondary"><?= e($member['no_anggota']) ?></span></td>
            <td class="fw-medium"><?= e($member['nama']) ?></td>
            <td><?= e($member['alamat'] ?? '') ?></td>
            <td><?= e($member['no_hp'] ?? '') ?></td>
            <td class="text-end pe-4 text-nowrap">
              <a class="btn btn-outline-warning btn-sm" href="tambah.php?id=<?= (int) $member['id'] ?>" aria-label="Edit <?= e($member['nama']) ?>"><i class="bi bi-pencil"></i></a>
              <form class="d-inline" method="post" action="list.php" onsubmit="return confirm('Hapus anggota ini?')">
                <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                <input type="hidden" name="id" value="<?= (int) $member['id'] ?>">
                <button class="btn btn-outline-danger btn-sm" type="submit" aria-label="Hapus <?= e($member['nama']) ?>"><i class="bi bi-trash3"></i></button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>
<?php renderFooter(); ?>