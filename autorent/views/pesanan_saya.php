<section id="pesanan">
    <h2 class="section-title mb-3">Pesanan saya</h2>
    <div class="panel p-0">
    <?php if ($daftar_pesanan): ?>
        <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr><th>Unit</th><th>Periode</th><th class="text-end">Biaya</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
            <?php foreach ($daftar_pesanan as $o):
                [$label, $kelas] = label_status($o['status_sewa']);
                $kembali = tgl_kembali($o['tanggal_pinjam'], $o['lama_hari']);
            ?>
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <?php if (!empty($o['foto'])): ?><img class="thumb" src="uploads/<?= e($o['foto']) ?>" alt=""><?php endif; ?>
                            <div>
                                <div class="fw-semibold"><?= e($o['merk_mobil']) ?></div>
                                <span class="plate"><?= e($o['no_polisi']) ?></span>
                            </div>
                        </div>
                    </td>
                    <td>
                        <?= tanggal_indo($o['tanggal_pinjam']) ?> - <?= tanggal_indo($kembali) ?>
                        <div class="small text-secondary"><?= (int)$o['lama_hari'] ?> hari</div>
                    </td>
                    <td class="text-end fw-semibold text-nowrap"><?= rupiah($o['total_biaya']) ?></td>
                    <td><span class="status <?= $kelas ?>"><?= $label ?></span></td>
                    <td class="text-end">
                        <?php if ($o['status_sewa'] === 'menunggu'): ?>
                        <form method="POST" action="proses.php" onsubmit="return confirm('Batalkan pesanan ini?');">
                            <?= csrf_field() ?><input type="hidden" name="aksi" value="batal">
                            <input type="hidden" name="id" value="<?= (int)$o['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-outline-primary">Batalkan</button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    <?php else: ?>
        <div class="text-center text-secondary py-5 px-3">
            <div class="fw-semibold text-dark fs-5">Belum ada pesanan.</div>
            <div>Pilih mobil di atas lalu tekan "Pesan".</div>
        </div>
    <?php endif; ?>
    </div>
</section>
