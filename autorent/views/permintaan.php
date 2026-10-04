<section id="permintaan" class="mb-5">
    <h2 class="section-title mb-3">Permintaan sewa dari pelanggan</h2>
    <div class="panel p-0 panel-wait">
        <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr><th>Pelanggan</th><th>Unit</th><th>Periode</th><th class="text-end">Biaya</th><th></th></tr>
            </thead>
            <tbody>
            <?php foreach ($daftar_permintaan as $r):
                $kembali = tgl_kembali($r['tanggal_pinjam'], $r['lama_hari']);
            ?>
                <tr>
                    <td class="fw-semibold"><?= e($r['nama_penyewa']) ?></td>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <?php if (!empty($r['foto'])): ?><img class="thumb" src="uploads/<?= e($r['foto']) ?>" alt=""><?php endif; ?>
                            <div>
                                <span class="plate"><?= e($r['no_polisi']) ?></span>
                                <div class="text-secondary small mt-1"><?= e($r['merk_mobil']) ?></div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <?= tanggal_indo($r['tanggal_pinjam']) ?> - <?= tanggal_indo($kembali) ?>
                        <div class="small text-secondary"><?= (int)$r['lama_hari'] ?> hari</div>
                    </td>
                    <td class="text-end fw-semibold text-nowrap"><?= rupiah($r['total_biaya']) ?></td>
                    <td class="text-end text-nowrap">
                        <?php if ($r['unit_sibuk']): ?>
                            <span class="small text-secondary me-2">Unit masih disewa</span>
                        <?php endif; ?>
                        <form method="POST" action="proses.php" class="d-inline">
                            <?= csrf_field() ?><input type="hidden" name="aksi" value="setujui">
                            <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-primary" <?= $r['unit_sibuk'] ? 'disabled' : '' ?>>Setujui</button>
                        </form>
                        <form method="POST" action="proses.php" class="d-inline" onsubmit="return confirm('Tolak permintaan ini?');">
                            <?= csrf_field() ?><input type="hidden" name="aksi" value="tolak">
                            <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-outline-primary">Tolak</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
</section>
