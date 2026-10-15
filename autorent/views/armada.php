<section id="armada" class="mb-5">
    <div class="fleet-heading d-flex justify-content-between align-items-end flex-wrap gap-3 mb-4">
        <div>
            <span class="eyebrow eyebrow-dark">TEMUKAN PERJALANAN BERIKUTNYA</span>
            <h2 class="section-title"><?= $peran === 'admin' ? 'Kelola armada' : 'Mobil pilihan untukmu' ?></h2>
            <p class="fleet-subtitle mb-0"><?= $peran === 'admin' ? 'Pantau ketersediaan dan catat penyewaan kendaraan.' : 'Pilih kendaraan yang pas, lalu pesan sesuai jadwalmu.' ?></p>
        </div>
        <div class="btn-group btn-group-sm filter" role="group" aria-label="Filter armada">
            <button type="button" class="btn active" data-filter="semua">Semua</button>
            <button type="button" class="btn" data-filter="tersedia">Tersedia</button>
            <button type="button" class="btn" data-filter="disewa">Jadwal penuh</button>
        </div>
    </div>

    <?php if ($jumlah_unit > 0): ?>
    <form id="fleetSearch" class="fleet-search mb-4" role="search" onsubmit="return false">
        <label class="search-field search-keyword">
            <span>Mobil apa yang kamu cari?</span>
            <input type="search" id="cariMobil" placeholder="Cari nama mobil atau nomor polisi" autocomplete="off">
        </label>
        <label class="search-field">
            <span>Transmisi</span>
            <select id="filterTransmisi">
                <option value="">Semua transmisi</option>
                <option value="auto">Otomatis</option>
                <option value="manual">Manual</option>
            </select>
        </label>
        <label class="search-field">
            <span>Tanggal mulai</span>
            <input type="date" id="tanggalCari" value="<?= date('Y-m-d') ?>" min="<?= date('Y-m-d') ?>">
        </label>
        <label class="search-field search-duration">
            <span>Durasi</span>
            <select id="durasiCari">
                <?php for ($hari = 1; $hari <= 14; $hari++): ?>
                    <option value="<?= $hari ?>" <?= $hari === 1 ? 'selected' : '' ?>><?= $hari ?> hari</option>
                <?php endfor; ?>
            </select>
        </label>
        <label class="search-field search-budget">
            <span>Harga per hari</span>
            <select id="filterHarga">
                <option value="">Semua harga</option>
                <option value="low">Di bawah Rp300 ribu</option>
                <option value="mid">Rp300–750 ribu</option>
                <option value="high">Di atas Rp750 ribu</option>
            </select>
        </label>
        <button type="button" class="btn btn-primary search-submit" id="cariSubmit">Cari mobil <span aria-hidden="true">→</span></button>
    </form>
    <div class="fleet-result small text-secondary mb-3" id="hasilArmada" aria-live="polite"></div>
    <?php endif; ?>

    <?php if ($jumlah_unit === 0): ?>
        <div class="panel text-center py-5">
            <div class="fw-semibold fs-5">Belum ada unit di armada.</div>
            <?php if ($peran === 'admin'): ?>
                <p class="text-secondary">Tambahkan mobil pertama beserta fotonya untuk mulai mencatat sewa.</p>
                <button class="btn btn-accent" data-bs-toggle="modal" data-bs-target="#modalUnit">Tambah unit</button>
            <?php else: ?>
                <p class="text-secondary mb-0">Armada belum tersedia. Silakan kembali lagi nanti.</p>
            <?php endif; ?>
        </div>
    <?php else: ?>
    <div class="row row-cols-1 row-cols-sm-2 row-cols-xl-3 g-4" id="daftarArmada">
        <?php foreach ($daftar_mobil as $m):
            $disewa = db_bool($m['sedang_disewa']);
        ?>
        <div class="col fleet-item" data-status="<?= $disewa ? 'disewa' : 'tersedia' ?>"
             data-name="<?= e($m['merk_mobil'] . ' ' . $m['no_polisi']) ?>"
             data-transmission="<?= e(strtolower($m['tipe_transmisi'])) ?>"
             data-price="<?= (int)$m['harga_per_hari'] ?>"
             data-periods="<?= e($m['periods'] ?? '[]') ?>">
            <article class="unit <?= $disewa ? 'is-out' : '' ?>">
                <div class="unit-pic tone-<?= (int)$m['id'] % 6 ?>">
                    <?php if (!empty($m['foto'])): ?>
                        <img class="foto" src="uploads/<?= e($m['foto']) ?>" alt="<?= e($m['merk_mobil']) ?>" loading="lazy">
                    <?php else: ?>
                        <svg class="car" viewBox="0 0 240 100"><use href="#ic-car"/></svg>
                    <?php endif; ?>
                    <span class="status <?= $disewa ? 'status-out' : 'status-in' ?> availability-badge"><?= $disewa ? 'Disewa' : 'Tersedia' ?></span>

                    <?php if ($peran === 'admin'): ?>
                    <form method="POST" action="proses.php" enctype="multipart/form-data" class="photo-form">
                        <?= csrf_field() ?>
                        <input type="hidden" name="aksi" value="ganti_foto">
                        <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
                        <label class="photo-btn">
                            <?= empty($m['foto']) ? 'Tambah foto' : 'Ubah foto' ?>
                            <input type="file" name="foto" accept="image/jpeg,image/png,image/webp" hidden onchange="this.form.submit()">
                        </label>
                    </form>
                    <?php endif; ?>
                </div>
                <div class="unit-body">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <h3><?= e($m['merk_mobil']) ?></h3>
                        <span class="trans"><?= $m['tipe_transmisi'] === 'Auto' ? 'Otomatis' : 'Manual' ?></span>
                    </div>
                    <span class="plate"><?= e($m['no_polisi']) ?></span>
                    <div class="unit-foot">
                        <div class="price"><?= rupiah($m['harga_per_hari']) ?><small> / hari</small></div>
                        <div class="unit-actions d-flex align-items-center gap-2">
                            <?php if ($peran === 'admin'): ?>
                            <button type="button" class="btn btn-outline-primary btn-sm"
                                    data-bs-toggle="modal" data-bs-target="#modalEditUnit"
                                    data-id="<?= (int)$m['id'] ?>"
                                    data-nopol="<?= e($m['no_polisi']) ?>"
                                    data-merk="<?= e($m['merk_mobil']) ?>"
                                    data-transmisi="<?= e($m['tipe_transmisi']) ?>"
                                    data-harga="<?= (int)$m['harga_per_hari'] ?>">Edit</button>
                            <?php endif; ?>
                            <button type="button" class="btn btn-accent btn-sm px-3" data-bs-toggle="modal"
                                    data-bs-target="<?= $peran === 'admin' ? '#modalSewa' : '#modalPesan' ?>"
                                    data-mobil="<?= (int)$m['id'] ?>"
                                    data-label-available="<?= $peran === 'admin' ? 'Sewakan' : 'Pesan' ?>"
                                    <?= $disewa ? 'disabled' : '' ?>><?= $peran === 'admin' ? 'Sewakan' : 'Pesan' ?></button>
                        </div>
                    </div>
                </div>
            </article>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</section>
