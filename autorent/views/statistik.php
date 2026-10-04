<section class="hero">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end flex-wrap gap-3">
            <div>
                <h1>Meja operator</h1>
                <p class="mb-0"><?= date('j F Y') ?>. <?= $unit_tersedia ?> unit siap disewa hari ini.</p>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-accent" data-bs-toggle="modal" data-bs-target="#modalSewa">Catat sewa</button>
                <button class="btn btn-outline-light" data-bs-toggle="modal" data-bs-target="#modalUnit">Tambah unit</button>
            </div>
        </div>
    </div>
</section>

<div class="container stats">
    <div class="row g-3">
        <div class="col-6 col-lg-3"><div class="stat"><span>Unit tersedia</span><strong><?= $unit_tersedia ?></strong></div></div>
        <div class="col-6 col-lg-3"><div class="stat"><span>Sedang disewa</span><strong><?= $unit_disewa ?></strong></div></div>
        <div class="col-6 col-lg-3"><div class="stat <?= $jumlah_menunggu ? 'warn' : '' ?>"><span>Permintaan menunggu</span><strong><?= $jumlah_menunggu ?></strong></div></div>
        <div class="col-6 col-lg-3"><div class="stat"><span>Nilai sewa berjalan</span><strong class="money"><?= rupiah($pendapatan) ?></strong></div></div>
    </div>
</div>
