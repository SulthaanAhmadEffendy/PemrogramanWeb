<section id="sewa">
    <h2 class="section-title mb-3">Sewa berjalan</h2>
    <div class="panel p-0">
    <?php if ($jumlah_aktif > 0): ?>
        <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr><th>Penyewa</th><th>Unit</th><th>Periode</th><th class="text-end">Biaya</th><th><span class="visually-hidden">Aksi</span></th></tr>
            </thead>
            <tbody>
            <?php foreach ($daftar_rental as $row):
                $kembali = tgl_kembali($row['tanggal_pinjam'], $row['lama_hari']);
                $telat   = $kembali < date('Y-m-d');
            ?>
                <tr>
                    <td>
                        <div class="fw-semibold"><?= e($row['nama_penyewa']) ?></div>
                        <div class="small text-secondary"><?= $row['pengguna_id'] ? 'Pesan online' : 'Sewa di kantor' ?></div>
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <?php if (!empty($row['foto'])): ?><img class="thumb" src="uploads/<?= e($row['foto']) ?>" alt=""><?php endif; ?>
                            <div>
                                <span class="plate"><?= e($row['no_polisi']) ?></span>
                                <div class="text-secondary small mt-1"><?= e($row['merk_mobil']) ?></div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <?= tanggal_indo($row['tanggal_pinjam']) ?> - <?= tanggal_indo($kembali) ?>
                        <div class="small">
                            <span class="text-secondary"><?= (int)$row['lama_hari'] ?> hari</span>
                            <?php if ($telat): ?><span class="status status-late ms-1">Lewat jadwal</span><?php endif; ?>
                        </div>
                    </td>
                    <td class="text-end fw-semibold text-nowrap"><?= rupiah($row['total_biaya']) ?></td>
                    <td class="text-end">
                        <form method="POST" action="proses.php" onsubmit="return confirm('Mobil <?= e($row['no_polisi']) ?> sudah kembali?');">
                            <?= csrf_field() ?><input type="hidden" name="aksi" value="selesai">
                            <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-outline-primary text-nowrap">Tandai kembali</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    <?php else: ?>
        <div class="text-center text-secondary py-5 px-3">
            <div class="fw-semibold text-dark fs-5">Semua unit ada di garasi.</div>
            <div>Pilih unit di atas lalu tekan "Sewakan" untuk mencatat sewa baru.</div>
        </div>
    <?php endif; ?>
    </div>
</section>
