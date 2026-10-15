<?php
require __DIR__ . '/config/database.php';
require __DIR__ . '/includes/functions.php';

$user = wajib_login('user');
$peran = 'user';

$daftar_mobil   = ambil_semua_mobil($pdo);
$daftar_pesanan = ambil_pesanan_user($pdo, $user['id']);

$jumlah_unit   = count($daftar_mobil);
$unit_disewa   = count(array_filter($daftar_mobil, fn($m) => db_bool($m['sedang_disewa'])));
$unit_tersedia = count(array_filter($daftar_mobil, fn($m) => unit_tersedia_pada($m, date('Y-m-d'))));
$pesanan_aktif = count(array_filter($daftar_pesanan, fn($o) => in_array($o['status_sewa'], ['menunggu', 'berjalan'])));
$foto_armada = array_values(array_filter($daftar_mobil, fn($m) => !empty($m['foto'])));

require __DIR__ . '/partials/header.php';
?>
<section class="hero">
    <div class="container">
        <div class="hero-copy">
            <span class="eyebrow"><span></span> AUTORENT · RENTAL MOBIL</span>
            <h1>Mobil yang pas<br>untuk <em>perjalananmu.</em></h1>
            <p>Halo, <?= e(explode(' ', $user['nama'])[0]) ?>. Pilih mobil dan tanggal sewa. Kami akan mengabari setelah pesanan dikonfirmasi.</p>
            <div class="hero-points">
                <span><b>✓</b> Tarif per hari jelas</span>
                <span><b>✓</b> Jadwal bisa dicek</span>
                <span><b>✓</b> Konfirmasi admin</span>
            </div>
            <a href="#armada" class="btn btn-accent hero-cta">Lihat mobil <span aria-hidden="true">→</span></a>
        </div>
        <div class="hero-visual" aria-label="Contoh mobil di armada AutoRent">
            <div class="hero-photo-main">
                <?php if (!empty($foto_armada[0])): ?>
                    <img src="uploads/<?= e($foto_armada[0]['foto']) ?>" alt="<?= e($foto_armada[0]['merk_mobil']) ?>">
                <?php else: ?>
                    <svg class="hero-placeholder" viewBox="0 0 240 100" aria-hidden="true"><use href="#ic-car"/></svg>
                <?php endif; ?>
            </div>
            <?php if (!empty($foto_armada[1])): ?>
                <div class="hero-photo-small">
                    <img src="uploads/<?= e($foto_armada[1]['foto']) ?>" alt="<?= e($foto_armada[1]['merk_mobil']) ?>">
                    <div><strong><?= e($foto_armada[1]['merk_mobil']) ?></strong><span><?= e($foto_armada[1]['no_polisi']) ?></span></div>
                </div>
            <?php endif; ?>
            <div class="hero-badge"><strong><?= $unit_tersedia ?></strong><span>mobil siap<br>dipilih</span></div>
        </div>
    </div>
</section>

<main class="container content content-user">
    <?php tampil_flash(); ?>
    <div class="trust-strip">
        <div><strong>01</strong><span>Pilih mobil<br><small>Temukan yang cocok</small></span></div>
        <div><strong>02</strong><span>Tentukan jadwal<br><small>Sesuaikan dengan rencana</small></span></div>
        <div><strong>03</strong><span>Siap berangkat<br><small>Konfirmasi dari admin</small></span></div>
    </div>
    <?php require __DIR__ . '/views/armada.php'; ?>
    <?php require __DIR__ . '/views/pesanan_saya.php'; ?>
</main>
<?php
require __DIR__ . '/views/modal_pesan.php';
require __DIR__ . '/partials/footer.php';
