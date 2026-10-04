<?php $p = pengguna(); ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AutoRent</title>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600&family=Schibsted+Grotesk:wght@600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/style.css" rel="stylesheet">
</head>
<body>

<!-- Ikon mobil (dipakai ulang di logo dan kartu armada) -->
<svg width="0" height="0" style="position:absolute" aria-hidden="true">
    <symbol id="ic-car" viewBox="0 0 240 100">
        <path fill="currentColor" d="M8 72V60q0-9 10-11l34-7 24-25q4-5 11-5h66q7 0 12 5l26 25 32 7q9 2 9 11v12q0 5-5 5H12q-4 0-4-5z"/>
        <path fill="#fff" fill-opacity=".38" d="M86 24h30v18H66zM124 24h28l20 18h-48z"/>
        <circle cx="64" cy="78" r="18" fill="#12161c"/><circle cx="64" cy="78" r="8" fill="#c5ccd4"/>
        <circle cx="182" cy="78" r="18" fill="#12161c"/><circle cx="182" cy="78" r="8" fill="#c5ccd4"/>
    </symbol>
</svg>

<?php if (empty($tanpa_nav) && $p): ?>
<nav class="navbar navbar-expand-md navbar-dark topnav">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="index.php">
            <span class="logo"><svg width="26" height="11"><use href="#ic-car"/></svg></span>
            AutoRent
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#menu" aria-label="Buka menu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="menu">
            <ul class="navbar-nav me-auto ms-md-4">
                <?php if ($p['role'] === 'admin'): ?>
                    <li class="nav-item"><a class="nav-link" href="#armada">Armada</a></li>
                    <li class="nav-item"><a class="nav-link" href="#sewa">Sewa berjalan</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="#armada">Pilih mobil</a></li>
                    <li class="nav-item"><a class="nav-link" href="#pesanan">Pesanan saya</a></li>
                <?php endif; ?>
            </ul>
            <div class="d-flex align-items-center gap-3 mt-2 mt-md-0">
                <span class="who">
                    <?= e($p['nama']) ?>
                    <span class="role-chip"><?= $p['role'] === 'admin' ? 'Admin' : 'Pelanggan' ?></span>
                </span>
                <form method="POST" action="proses.php" class="m-0">
                    <?= csrf_field() ?>
                    <input type="hidden" name="aksi" value="keluar">
                    <button type="submit" class="btn btn-sm btn-outline-light">Keluar</button>
                </form>
            </div>
        </div>
    </div>
</nav>
<?php endif; ?>
