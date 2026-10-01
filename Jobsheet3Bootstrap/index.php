<?php
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/layout.php';

$totalBuku = (int) $pdo->query('SELECT COUNT(*) FROM buku')->fetchColumn();
$totalAnggota = (int) $pdo->query('SELECT COUNT(*) FROM anggota')->fetchColumn();
renderHeader('Beranda', 'home', '');
?>
<section class="p-5 text-center bg-white shadow-sm rounded-4 mb-5">
  <h1 class="display-6 fw-bold text-primary mb-3">Selamat Datang di SIMPUS-Mini</h1>
  <p class="lead text-secondary mb-0">Kelola data buku dan anggota perpustakaan.</p>
</section>
<section>
  <div class="d-flex align-items-center mb-4">
    <i class="bi bi-bar-chart-fill fs-4 text-primary me-2"></i>
    <h2 class="h4 fw-bold mb-0">Ringkasan Sistem</h2>
  </div>
  <div class="row g-4 text-center">
    <div class="col-12 col-sm-6">
      <a class="card border-0 shadow-sm rounded-4 h-100 py-4 text-decoration-none" href="buku/list.php">
        <div class="card-body">
          <i class="bi bi-journals fs-1 text-primary mb-3 d-block"></i>
          <h3 class="h5 text-secondary fw-normal">Total Buku</h3>
          <p class="h2 fw-bold text-dark mb-0"><?= $totalBuku ?></p>
        </div>
      </a>
    </div>
    <div class="col-12 col-sm-6">
      <a class="card border-0 shadow-sm rounded-4 h-100 py-4 text-decoration-none" href="anggota/list.php">
        <div class="card-body">
          <i class="bi bi-people-fill fs-1 text-success mb-3 d-block"></i>
          <h3 class="h5 text-secondary fw-normal">Total Anggota</h3>
          <p class="h2 fw-bold text-dark mb-0"><?= $totalAnggota ?></p>
        </div>
      </a>
    </div>
  </div>
</section>
<?php renderFooter(); ?>