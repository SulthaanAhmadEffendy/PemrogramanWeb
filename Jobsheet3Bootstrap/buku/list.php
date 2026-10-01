<?php
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/layout.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    if (!verifyCsrfToken() || !$id) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Permintaan tidak valid.'];
    } else {
        $statement = $pdo->prepare('DELETE FROM buku WHERE id = ?');
        $statement->execute([$id]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Data buku berhasil dihapus.'];
    }
    header('Location: list.php');
    exit;
}

$books = $pdo->query('SELECT id, judul, pengarang, tahun, stok FROM buku ORDER BY id DESC')->fetchAll();
renderHeader('Daftar Buku', 'books', '../');
?>
<div class="d-flex justify-content-between align-items-center gap-3 mb-4">
  <h1 class="h3 fw-bold text-dark mb-0"><i class="bi bi-journal-text text-primary me-2"></i>Daftar Buku</h1>
  <a href="tambah.php" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Tambah Buku</a>
</div>
<?php renderFlash(); ?>
<section class="card border-0 shadow-sm rounded-4 overflow-hidden">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light"><tr><th class="ps-4">Judul</th><th>Pengarang</th><th>Tahun</th><th>Stok</th><th class="text-end pe-4">Aksi</th></tr></thead>
      <tbody>
        <?php if (!$books): ?>
          <tr><td colspan="5" class="text-center text-secondary py-4">Belum ada data buku.</td></tr>
        <?php endif; ?>
        <?php foreach ($books as $book): ?>
          <tr>
            <td class="ps-4 fw-medium"><?= e($book['judul']) ?></td>
            <td><?= e($book['pengarang']) ?></td>
            <td><?= (int) $book['tahun'] ?></td>
            <td><?= (int) $book['stok'] ?></td>
            <td class="text-end pe-4 text-nowrap">
              <a class="btn btn-outline-warning btn-sm" href="tambah.php?id=<?= (int) $book['id'] ?>" aria-label="Edit <?= e($book['judul']) ?>"><i class="bi bi-pencil"></i></a>
              <form class="d-inline" method="post" action="list.php" onsubmit="return confirm('Hapus buku ini?')">
                <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                <input type="hidden" name="id" value="<?= (int) $book['id'] ?>">
                <button class="btn btn-outline-danger btn-sm" type="submit" aria-label="Hapus <?= e($book['judul']) ?>"><i class="bi bi-trash3"></i></button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>
<?php renderFooter(); ?>