<?php
function renderHeader(string $title, string $activePage, string $basePath): void
{
    $links = [
        'home' => ['Beranda', 'index.php'],
        'books' => ['Daftar Buku', 'buku/list.php'],
        'add-book' => ['Tambah Buku', 'buku/tambah.php'],
        'members' => ['Daftar Anggota', 'anggota/list.php'],
        'add-member' => ['Tambah Anggota', 'anggota/tambah.php'],
    ];
    ?>
    <!doctype html>
    <html lang="id">
      <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?= e($title) ?> | SIMPUS-Mini</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
        <link href="<?= e($basePath) ?>assets/css/style.css" rel="stylesheet">
      </head>
      <body class="bg-light">
        <header class="navbar navbar-expand-md navbar-light bg-white shadow-sm sticky-top">
          <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="<?= e($basePath) ?>index.php">
              <i class="bi bi-book-half me-2"></i>SIMPUS-Mini
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu" aria-controls="navMenu" aria-expanded="false" aria-label="Buka navigasi">
              <span class="navbar-toggler-icon"></span>
            </button>
            <nav class="collapse navbar-collapse" id="navMenu">
              <ul class="navbar-nav ms-auto gap-1">
                <?php foreach ($links as $key => [$label, $href]): ?>
                  <li class="nav-item">
                    <a class="nav-link<?= $activePage === $key ? ' active fw-medium' : '' ?>" href="<?= e($basePath . $href) ?>"><?= e($label) ?></a>
                  </li>
                <?php endforeach; ?>
              </ul>
            </nav>
          </div>
        </header>
        <main class="container my-5">
    <?php
}

function renderFooter(): void
{
    ?>
        </main>
        <footer class="text-center text-secondary py-4 border-top bg-white small">
          <p class="mb-0">&copy; <?= date('Y') ?> SIMPUS-Mini</p>
        </footer>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
      </body>
    </html>
    <?php
}

function renderFlash(): void
{
    if (empty($_SESSION['flash'])) {
        return;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    $type = $flash['type'] === 'success' ? 'success' : 'danger';
    ?>
    <div class="alert alert-<?= e($type) ?> alert-dismissible fade show" role="alert">
      <?= e($flash['message']) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
    </div>
    <?php
}