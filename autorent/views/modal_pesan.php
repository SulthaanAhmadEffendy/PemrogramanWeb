<!-- PELANGGAN: pesan mobil -->
<div class="modal fade" id="modalPesan" tabindex="-1" aria-labelledby="judulPesan" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" method="POST" action="proses.php">
            <?= csrf_field() ?>
            <input type="hidden" name="aksi" value="pesan">
            <div class="modal-header">
                <h2 class="modal-title" id="judulPesan">Pesan mobil</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <label for="mobil_id" class="form-label">Mobil</label>
                <select id="mobil_id" name="mobil_id" class="form-select mb-3" required>
                    <option value="" disabled selected>Pilih mobil</option>
                    <?php foreach ($daftar_mobil as $m): if ($m['sedang_disewa']) continue; ?>
                        <option value="<?= (int)$m['id'] ?>" data-tarif="<?= (int)$m['harga_per_hari'] ?>">
                            <?= e($m['merk_mobil']) ?> (<?= e($m['no_polisi']) ?>) - <?= rupiah($m['harga_per_hari']) ?>/hari
                        </option>
                    <?php endforeach; ?>
                </select>

                <div class="row g-3">
                    <div class="col-7">
                        <label for="tanggal_pinjam" class="form-label">Tanggal pinjam</label>
                        <input type="date" id="tanggal_pinjam" name="tanggal_pinjam" class="form-control"
                               value="<?= date('Y-m-d') ?>" min="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="col-5">
                        <label for="lama_hari" class="form-label">Lama (hari)</label>
                        <input type="number" id="lama_hari" name="lama_hari" class="form-control" min="1" value="1" required>
                    </div>
                </div>
                <p class="small text-secondary mt-3 mb-0">Pesanan Anda akan dikonfirmasi oleh admin. Status bisa dilihat di "Pesanan saya".</p>
            </div>
            <div class="modal-footer justify-content-between">
                <div><span class="text-secondary small d-block">Perkiraan total</span><strong class="total" id="estimasi">-</strong></div>
                <button type="submit" class="btn btn-primary px-4">Kirim pesanan</button>
            </div>
        </form>
    </div>
</div>
