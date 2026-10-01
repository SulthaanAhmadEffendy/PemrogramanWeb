<?php
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/layout.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: null;
$book = ['judul' => '', 'pengarang' => '', 'tahun' => (int) date('Y'), 'isbn' => '', 'stok' => 0, 'kategori' => 'fiksi'];

if ($id) {
    $statement = $pdo->prepare('SELECT * FROM buku WHERE id = ?');
    $statement->execute([$id]);
    $existingBook = $statement->fetch();
    if (!$existingBook) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Data buku tidak ditemukan.'];
        header('Location: list.php');
        exit;
    }
    $book = $existingBook;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken()) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Sesi formulir tidak valid. Silakan coba lagi.'];
    } else {
        $judul = trim($_POST['judul'] ?? '');
        $pengarang = trim($_POST['pengarang'] ?? '');
        $tahun = filter_input(INPUT_POST, 'tahun', FILTER_VALIDATE_INT);
        $stok = filter_input(INPUT_POST, 'stok', FILTER_VALIDATE_INT);
        $isbn = trim($_POST['isbn'] ?? '');
        $kategori = trim($_POST['kategori'] ?? '');
        $categories = ['fiksi', 'non-fiksi', 'referensi'];

        if ($judul === '' || $pengarang === '' || !$tahun || $tahun < 1900 || $tahun > (int) date('Y') || $stok === false || $stok < 0 || !in_array($kategori, $categories, true)) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Periksa kembali judul, pengarang, tahun, stok, dan kategori.'];
        } else {
            $values = [$judul, $pengarang, $tahun, $isbn !== '' ? $isbn : null, $stok, $kategori];
            if ($id) {
                $values[] = $id;
                $statement = $pdo->prepare('UPDATE buku SET judul = ?, pengarang = ?, tahun = ?, isbn = ?, stok = ?, kategori = ? WHERE id = ?');
            } else {
                $statement = $pdo->prepare('INSERT INTO buku (judul, pengarang, tahun, isbn, stok, kategori) VALUES (?, ?, ?, ?, ?, ?)');
            }
            $statement->execute($values);
            $_SESSION['flash'] = ['type' => 'success', 'message' => $id ? 'Data buku berhasil diperbarui.' : 'Data buku berhasil ditambahkan.'];
            header('Location: list.php');
            exit;
        }
    }
}

renderHeader($id ? 'Edit Buku' : 'Tambah Buku', 'add-book', '../');
?>
<h1 class="h3 fw-bold text-dark mb-4"><i class="bi bi-journal-plus text-primary me-2"></i><?= $id ? 'Edit Data Buku' : 'Tambah Data Buku' ?></h1>
<?php renderFlash(); ?>
<section class="card border-0 shadow-sm rounded-4 p-3 p-md-4" style="max-width: 800px">
  <form method="post" action="tambah.php<?= $id ? '?id=' . (int) $id : '' ?>">
    <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
    <div class="row g-3">
      <div class="col-12"><label class="form-label" for="judul">Judul Buku</label><input class="form-control" id="judul" name="judul" maxlength="255" value="<?= e($book['judul']) ?>" required></div>
      <div class="col-md-6"><label class="form-label" for="pengarang">Pengarang</label><input class="form-control" id="pengarang" name="pengarang" maxlength="255" value="<?= e($book['pengarang']) ?>" required></div>
      <div class="col-md-6"><label class="form-label" for="kategori">Kategori</label><select class="form-select" id="kategori" name="kategori" required><?php foreach (['fiksi' => 'Fiksi', 'non-fiksi' => 'Non-Fiksi', 'referensi' => 'Referensi'] as $value => $label): ?><option value="<?= e($value) ?>"<?= $book['kategori'] === $value ? ' selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select></div>
      <div class="col-md-4"><label class="form-label" for="tahun">Tahun Terbit</label><input class="form-control" type="number" id="tahun" name="tahun" min="1900" max="<?= date('Y') ?>" value="<?= (int) $book['tahun'] ?>" required></div>
      <div class="col-md-5"><label class="form-label" for="isbn">ISBN</label><input class="form-control" id="isbn" name="isbn" maxlength="32" value="<?= e($book['isbn'] ?? '') ?>"></div>
      <div class="col-md-3"><label class="form-label" for="stok">Jumlah Stok</label><input class="form-control" type="number" id="stok" name="stok" min="0" value="<?= (int) $book['stok'] ?>" required></div>
    </div>
    <div class="d-flex justify-content-end gap-2 mt-4"><a class="btn btn-light" href="list.php">Batal</a><button class="btn btn-primary" type="submit"><i class="bi bi-save me-2"></i>Simpan</button></div>
  </form>
</section>
<?php renderFooter(); ?>