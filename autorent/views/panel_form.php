<div class="entry">
    <ul class="nav nav-tabs nav-fill" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-sewa" type="button" role="tab">Sewa baru</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-unit" type="button" role="tab">Tambah unit</button>
        </li>
    </ul>

    <div class="tab-content p-3 p-xl-4">

        <!-- Form sewa baru -->
        <div class="tab-pane fade show active" id="tab-sewa" role="tabpanel">
            <form method="POST" action="proses.php">
                <input type="hidden" name="aksi" value="tambah_rental">

                <label for="nama_penyewa" class="form-label">Nama penyewa</label>
                <input type="text" id="nama_penyewa" name="nama_penyewa" class="form-control mb-3" autocomplete="off" required>

                <label for="mobil_id" class="form-label">Unit</label>
                <select id="mobil_id" name="mobil_id" class="form-select mb-3" required>
                    <option value="" disabled selected>Pilih unit</option>
                    <?php foreach ($daftar_mobil as $m): ?>
                        <option value="<?= (int)$m['id'] ?>" data-tarif="<?= (int)$m['harga_per_hari'] ?>"
                                <?= $m['sedang_disewa'] ? 'disabled' : '' ?>>
                            <?= e($m['no_polisi']) ?> - <?= e($m['merk_mobil']) ?><?= $m['sedang_disewa'] ? ' (sedang disewa)' : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <div class="row g-2 mb-3">
                    <div class="col-7">
                        <label for="tanggal_pinjam" class="form-label">Tanggal pinjam</label>
                        <input type="date" id="tanggal_pinjam" name="tanggal_pinjam" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="col-5">
                        <label for="lama_hari" class="form-label">Lama (hari)</label>
                        <input type="number" id="lama_hari" name="lama_hari" class="form-control" min="1" value="1" required>
                    </div>
                </div>

                <div class="estimate d-flex justify-content-between align-items-baseline mb-3">
                    <span class="text-secondary small">Perkiraan total</span>
                    <strong id="estimasi">-</strong>
                </div>

                <button type="submit" class="btn btn-primary w-100">Simpan sewa</button>
            </form>
        </div>

        <!-- Form tambah unit -->
        <div class="tab-pane fade" id="tab-unit" role="tabpanel">
            <form method="POST" action="proses.php">
                <input type="hidden" name="aksi" value="tambah_mobil">

                <div class="row g-2 mb-3">
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
                <input type="number" id="harga_per_hari" name="harga_per_hari" class="form-control mb-4" min="0" required>

                <button type="submit" class="btn btn-primary w-100">Simpan unit</button>
            </form>
        </div>

    </div>
</div>
