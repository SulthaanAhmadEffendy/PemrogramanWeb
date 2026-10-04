<?php
require __DIR__ . '/config/database.php';
require __DIR__ . '/includes/functions.php';

$user = wajib_login('user');
$peran = 'user';

$daftar_mobil   = ambil_semua_mobil($pdo);
$daftar_pesanan = ambil_pesanan_user($pdo, $user['id']);

$jumlah_unit   = count($daftar_mobil);
$unit_disewa   = count(array_filter($daftar_mobil, fn($m) => $m['sedang_disewa']));
$unit_tersedia = $jumlah_unit - $unit_disewa;
$pesanan_aktif = count(array_filter($daftar_pesanan, fn($o) => in_array($o['status_sewa'], ['menunggu', 'berjalan'])));

require __DIR__ . '/partials/header.php';
?>
<section class="hero">
    <div class="container d-flex justify-content-between align-items-end flex-wrap gap-3">
        <div>
            <h1>Halo, <?= e(explode(' ', $user['nama'])[0]) ?></h1>
            <p class="mb-0"><?= $unit_tersedia ?> mobil siap disewa. <?= $pesanan_aktif ? "Anda punya $pesanan_aktif pesanan aktif." : 'Pilih mobil dan ajukan sewa.' ?></p>
        </div>
        <a href="#armada" class="btn btn-accent">Lihat mobil</a>
    </div>
</section>

<main class="container content content-user">
    <?php tampil_flash(); ?>
    <?php require __DIR__ . '/views/armada.php'; ?>
    <?php require __DIR__ . '/views/pesanan_saya.php'; ?>
</main>
<?php
require __DIR__ . '/views/modal_pesan.php';
require __DIR__ . '/partials/footer.php';
