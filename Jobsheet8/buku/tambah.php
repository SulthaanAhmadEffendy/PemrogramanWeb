<?php
$page_title = "Tambah Buku";
require_once __DIR__ . '/../includes/koneksi.php';
include __DIR__ . '/../includes/header.php';

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: null;
$buku = null;
if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM buku WHERE id = ?');
    $stmt->execute([$id]);
    $buku = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$buku) {
        $_SESSION['flash'] = ['type' => 'error', 'pesan' => 'Data buku tidak ditemukan.'];
        header('Location: list.php');
        exit;
    }
}
$nilai = static function (string $kolom) use ($buku): string {
    return htmlspecialchars((string) ($buku[$kolom] ?? ''), ENT_QUOTES, 'UTF-8');
};
?>
        <section>
            <h2><?php echo $id ? 'Edit Buku' : 'Tambah Buku'; ?></h2>

            <?php if ($flash): ?>
                <p class="flash flash-<?php echo $flash['type']; ?>"><?php echo $flash['pesan']; ?></p>
            <?php endif; ?>

            <form id="form-tambah" method="post" action="proses_tambah.php">
                <?php if ($id): ?><input type="hidden" name="id" value="<?php echo $id; ?>"><?php endif; ?>
                <p>
                    <label for="judul">Judul</label><br>
                    <input type="text" id="judul" name="judul" value="<?php echo $nilai('judul'); ?>" required>
                </p>
                <p>
                    <label for="pengarang">Pengarang</label><br>
                    <input type="text" id="pengarang" name="pengarang" value="<?php echo $nilai('pengarang'); ?>" required>
                </p>
                <p>
                    <label for="tahun">Tahun Terbit</label><br>
                    <input type="number" id="tahun" name="tahun" min="1900" max="2026" value="<?php echo $nilai('tahun'); ?>" required>
                </p>
                <p>
                    <label for="isbn">ISBN</label><br>
                    <input type="text" id="isbn" name="isbn" value="<?php echo $nilai('isbn'); ?>">
                </p>
                <p>
                    <label for="stok">Stok</label><br>
                    <input type="number" id="stok" name="stok" min="0" value="<?php echo $nilai('stok'); ?>" required>
                </p>
                <p>
                    <label for="kategori">Kategori</label><br>
                    <select id="kategori" name="kategori">
                        <option value="fiksi"<?php echo $nilai('kategori') === 'fiksi' ? ' selected' : ''; ?>>Fiksi</option>
                        <option value="non-fiksi"<?php echo $nilai('kategori') === 'non-fiksi' ? ' selected' : ''; ?>>Non-Fiksi</option>
                        <option value="referensi"<?php echo $nilai('kategori') === 'referensi' ? ' selected' : ''; ?>>Referensi</option>
                    </select>
                </p>
                <p>
                    <button type="submit"><?php echo $id ? 'Perbarui' : 'Simpan'; ?></button>
                </p>
            </form>
        </section>
<?php include __DIR__ . '/../includes/footer.php'; ?>