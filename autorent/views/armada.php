<section id="armada" class="mb-5">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <h2 class="section-title"><?= $peran === 'admin' ? 'Armada' : 'Pilih mobil' ?></h2>
        <div class="btn-group btn-group-sm filter" role="group" aria-label="Filter armada">
            <button type="button" class="btn active" data-filter="semua">Semua (<?= $jumlah_unit ?>)</button>
            <button type="button" class="btn" data-filter="tersedia">Tersedia (<?= $unit_tersedia ?>)</button>
            <button type="button" class="btn" data-filter="disewa">Disewa (<?= $unit_disewa ?>)</button>
        </div>
    </div>

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
    <div class="row row-cols-1 row-cols-sm-2 row-cols-xl-3 g-4">
        <?php foreach ($daftar_mobil as $m):
            $disewa = (bool)$m['sedang_disewa'];
        ?>
        <div class="col" data-status="<?= $disewa ? 'disewa' : 'tersedia' ?>">
            <article class="unit <?= $disewa ? 'is-out' : '' ?>">
                <div class="unit-pic tone-<?= (int)$m['id'] % 6 ?>">
                    <?php if (!empty($m['foto'])): ?>
                        <img class="foto" src="uploads/<?= e($m['foto']) ?>" alt="<?= e($m['merk_mobil']) ?>" loading="lazy">
                    <?php else: ?>
                        <svg class="car" viewBox="0 0 240 100"><use href="#ic-car"/></svg>
                    <?php endif; ?>
                    <span class="status <?= $disewa ? 'status-out' : 'status-in' ?>"><?= $disewa ? 'Disewa' : 'Tersedia' ?></span>

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
                        <?php if ($disewa): ?>
                            <span class="back-info">Kembali <?= tanggal_indo($m['tgl_kembali']) ?></span>
                        <?php else: ?>
                            <button class="btn btn-accent btn-sm px-3" data-bs-toggle="modal"
                                    data-bs-target="<?= $peran === 'admin' ? '#modalSewa' : '#modalPesan' ?>"
                                    data-mobil="<?= (int)$m['id'] ?>"><?= $peran === 'admin' ? 'Sewakan' : 'Pesan' ?></button>
                        <?php endif; ?>
                    </div>
                </div>
            </article>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</section>
