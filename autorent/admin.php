<?php
require __DIR__ . '/config/database.php';
require __DIR__ . '/includes/functions.php';

$admin = wajib_login('admin');
$peran = 'admin';

$daftar_mobil      = ambil_semua_mobil($pdo);
$daftar_rental     = ambil_rental_aktif($pdo);
$daftar_permintaan = ambil_permintaan($pdo);

$jumlah_unit     = count($daftar_mobil);
$jumlah_aktif    = count($daftar_rental);
$jumlah_menunggu = count($daftar_permintaan);
$unit_disewa     = count(array_filter($daftar_mobil, fn($m) => db_bool($m['sedang_disewa'])));
$unit_tersedia    = count(array_filter($daftar_mobil, fn($m) => unit_tersedia_pada($m, date('Y-m-d'))));
$pendapatan      = array_sum(array_column($daftar_rental, 'total_biaya'));

require __DIR__ . '/partials/header.php';
require __DIR__ . '/views/statistik.php';
?>
<main class="container content">
    <?php tampil_flash(); ?>
    <?php require __DIR__ . '/views/permintaan.php'; ?>
    <?php require __DIR__ . '/views/armada.php'; ?>
    <?php require __DIR__ . '/views/tabel_rental.php'; ?>
</main>
<?php
require __DIR__ . '/views/modal_form.php';
require __DIR__ . '/partials/footer.php';
