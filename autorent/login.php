<?php
require __DIR__ . '/config/database.php';
require __DIR__ . '/includes/functions.php';

if (pengguna()) arahkan('index.php');
$tanpa_nav = true;
$tab_daftar = isset($_GET['daftar']);
require __DIR__ . '/partials/header.php';
?>
<div class="auth">
    <div class="auth-side">
        <div class="d-flex align-items-center gap-2 brand-lg">
            <span class="logo"><svg width="26" height="11"><use href="#ic-car"/></svg></span> AutoRent
        </div>
        <div>
            <h1>Sewa mobil, tanpa antre.</h1>
            <p>Pilih unit, tentukan tanggal, dan pesanan Anda langsung masuk ke meja operator.</p>
        </div>
        <svg class="auth-car" viewBox="0 0 240 100"><use href="#ic-car"/></svg>
    </div>

    <div class="auth-form">
        <div class="auth-box">
            <?php tampil_flash(); ?>
            <ul class="nav nav-tabs auth-tabs nav-fill mb-4" role="tablist">
                <li class="nav-item"><button class="nav-link <?= $tab_daftar ? '' : 'active' ?>" data-bs-toggle="tab" data-bs-target="#tab-masuk" type="button">Masuk</button></li>
                <li class="nav-item"><button class="nav-link <?= $tab_daftar ? 'active' : '' ?>" data-bs-toggle="tab" data-bs-target="#tab-daftar" type="button">Daftar</button></li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane fade <?= $tab_daftar ? '' : 'show active' ?>" id="tab-masuk">
                    <form method="POST" action="proses.php">
                        <?= csrf_field() ?>
                        <input type="hidden" name="aksi" value="masuk">
                        <label for="m_email" class="form-label">Email</label>
                        <input type="email" id="m_email" name="email" class="form-control mb-3" autocomplete="username" required>
                        <label for="m_pass" class="form-label">Password</label>
                        <input type="password" id="m_pass" name="password" class="form-control mb-4" autocomplete="current-password" required>
                        <button type="submit" class="btn btn-primary w-100 py-2">Masuk</button>
                    </form>
                </div>

                <div class="tab-pane fade <?= $tab_daftar ? 'show active' : '' ?>" id="tab-daftar">
                    <form method="POST" action="proses.php">
                        <?= csrf_field() ?>
                        <input type="hidden" name="aksi" value="daftar">
                        <label for="d_nama" class="form-label">Nama lengkap</label>
                        <input type="text" id="d_nama" name="nama" class="form-control mb-3" autocomplete="name" required>
                        <label for="d_email" class="form-label">Email</label>
                        <input type="email" id="d_email" name="email" class="form-control mb-3" autocomplete="email" required>
                        <label for="d_pass" class="form-label">Password <span class="text-secondary fw-normal">(minimal 6 karakter)</span></label>
                        <input type="password" id="d_pass" name="password" class="form-control mb-4" minlength="6" autocomplete="new-password" required>
                        <button type="submit" class="btn btn-accent w-100 py-2">Buat akun</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/partials/footer.php'; ?>
