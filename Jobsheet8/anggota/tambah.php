<?php
$page_title = "Tambah Anggota";
require_once __DIR__ . '/../includes/koneksi.php';
include __DIR__ . '/../includes/header.php';

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: null;
$anggota = null;
if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM anggota WHERE id = ?');
    $stmt->execute([$id]);
    $anggota = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$anggota) {
        $_SESSION['flash'] = ['type' => 'error', 'pesan' => 'Data anggota tidak ditemukan.'];
        header('Location: list.php');
        exit;
    }
}
$nilai = static function (string $kolom) use ($anggota): string {
    return htmlspecialchars((string) ($anggota[$kolom] ?? ''), ENT_QUOTES, 'UTF-8');
};
?>
        <section>
            <h2><?php echo $id ? 'Edit Anggota' : 'Tambah Anggota'; ?></h2>

            <?php if ($flash): ?>
                <p class="flash flash-<?php echo $flash['type']; ?>"><?php echo $flash['pesan']; ?></p>
            <?php endif; ?>

            <form id="form-tambah" method="post" action="proses_tambah.php">
                <?php if ($id): ?><input type="hidden" name="id" value="<?php echo $id; ?>"><?php endif; ?>
                <p>
                    <label for="nama">Nama</label><br>
                    <input type="text" id="nama" name="nama" value="<?php echo $nilai('nama'); ?>" required>
                </p>
                <p>
                    <label for="no_anggota">No. Anggota</label><br>
                    <input type="text" id="no_anggota" name="no_anggota" value="<?php echo $nilai('no_anggota'); ?>" required>
                </p>
                <p>
                    <label for="alamat">Alamat</label><br>
                    <input type="text" id="alamat" name="alamat" value="<?php echo $nilai('alamat'); ?>">
                </p>
                <p>
                    <label for="no_hp">No. HP</label><br>
                    <input type="text" id="no_hp" name="no_hp" value="<?php echo $nilai('no_hp'); ?>">
                </p>
                <p>
                    <button type="submit"><?php echo $id ? 'Perbarui' : 'Simpan'; ?></button>
                </p>
            </form>
        </section>
<?php include __DIR__ . '/../includes/footer.php'; ?>