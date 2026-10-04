<!-- ADMIN: catat sewa langsung -->
<div class="modal fade" id="modalSewa" tabindex="-1" aria-labelledby="judulSewa" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" method="POST" action="proses.php">
            <?= csrf_field() ?>
            <input type="hidden" name="aksi" value="tambah_rental">
            <div class="modal-header">
                <h2 class="modal-title" id="judulSewa">Catat sewa di kantor</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <label for="nama_penyewa" class="form-label">Nama penyewa</label>
                <input type="text" id="nama_penyewa" name="nama_penyewa" class="form-control mb-3" autocomplete="off" required>

                <label for="mobil_id" class="form-label">Unit</label>
                <select id="mobil_id" name="mobil_id" class="form-select mb-3" required>
                    <option value="" disabled selected>Pilih unit</option>
                    <?php foreach ($daftar_mobil as $m): ?>
                        <option value="<?= (int)$m['id'] ?>" data-tarif="<?= (int)$m['harga_per_hari'] ?>" <?= $m['sedang_disewa'] ? 'disabled' : '' ?>>
                            <?= e($m['no_polisi']) ?> - <?= e($m['merk_mobil']) ?><?= $m['sedang_disewa'] ? ' (sedang disewa)' : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <div class="row g-3">
                    <div class="col-7">
                        <label for="tanggal_pinjam" class="form-label">Tanggal pinjam</label>
                        <input type="date" id="tanggal_pinjam" name="tanggal_pinjam" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="col-5">
                        <label for="lama_hari" class="form-label">Lama (hari)</label>
                        <input type="number" id="lama_hari" name="lama_hari" class="form-control" min="1" value="1" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <div><span class="text-secondary small d-block">Perkiraan total</span><strong class="total" id="estimasi">-</strong></div>
                <button type="submit" class="btn btn-primary px-4">Simpan sewa</button>
            </div>
        </form>
    </div>
</div>

<!-- ADMIN: tambah unit -->
<div class="modal fade" id="modalUnit" tabindex="-1" aria-labelledby="judulUnit" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" method="POST" action="proses.php" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" name="aksi" value="tambah_mobil">
            <div class="modal-header">
                <h2 class="modal-title" id="judulUnit">Tambah unit armada</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3 mb-3">
                    <div class="col-7">
                        <label for="no_polisi" class="form-label">Nomor polisi</label>
                        <input type="text" id="no_polisi" name="no_polisi" class="form-control" placeholder="N 1234 XX" required>
                    </div>
                    <div class="col-5">
                        <label for="tipe_transmisi" class="form-label">Transmisi</label>
                        <select id="tipe_transmisi" name="tipe_transmisi" class="form-select" required>
                            <option value="Manual">Manual</option>
                            <option value="Auto">Otomatis</option>
                        </select>
                    </div>
                </div>
                <label for="merk_mobil" class="form-label">Merk dan tipe</label>
                <input type="text" id="merk_mobil" name="merk_mobil" class="form-control mb-3" placeholder="Toyota Avanza" required>
                <label for="harga_per_hari" class="form-label">Tarif per hari (Rp)</label>
                <input type="number" id="harga_per_hari" name="harga_per_hari" class="form-control mb-3" min="0" required>
                <label for="foto" class="form-label">Foto mobil <span class="text-secondary fw-normal">(JPG, PNG, WebP, maks. 2 MB)</span></label>
                <input type="file" id="foto" name="foto" class="form-control" accept="image/jpeg,image/png,image/webp">
                <img id="previewFoto" class="preview" alt="" hidden>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary px-4">Simpan unit</button>
            </div>
        </form>
    </div>
</div>
