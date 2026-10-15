<?php
$host = 'localhost';
$port = '5432';
$dbname = 'db_rental';
$user = 'postgres';
$pass = '123456';

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}


$pdo->exec("
    CREATE TABLE IF NOT EXISTS pengguna (
        id       SERIAL PRIMARY KEY,
        nama     VARCHAR(100) NOT NULL,
        email    VARCHAR(120) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role     VARCHAR(10)  NOT NULL DEFAULT 'user' CHECK (role IN ('admin', 'user')),
        dibuat   TIMESTAMP    NOT NULL DEFAULT now()
    );
    CREATE TABLE IF NOT EXISTS armada_mobil (
        id             SERIAL PRIMARY KEY,
        no_polisi      VARCHAR(15)  NOT NULL UNIQUE,
        merk_mobil     VARCHAR(80)  NOT NULL,
        tipe_transmisi VARCHAR(10)  NOT NULL CHECK (tipe_transmisi IN ('Manual', 'Auto')),
        harga_per_hari INTEGER      NOT NULL CHECK (harga_per_hari >= 0),
        foto           VARCHAR(255)
    );
    CREATE TABLE IF NOT EXISTS transaksi_rental (
        id             SERIAL PRIMARY KEY,
        mobil_id       INTEGER      NOT NULL REFERENCES armada_mobil(id),
        pengguna_id    INTEGER      REFERENCES pengguna(id) ON DELETE SET NULL,
        nama_penyewa   VARCHAR(100) NOT NULL,
        tanggal_pinjam DATE         NOT NULL,
        lama_hari      INTEGER      NOT NULL CHECK (lama_hari >= 1),
        total_biaya    BIGINT       NOT NULL,
        status_sewa    VARCHAR(12)  NOT NULL DEFAULT 'berjalan'
    );
    ALTER TABLE armada_mobil     ADD COLUMN IF NOT EXISTS foto VARCHAR(255);
    ALTER TABLE transaksi_rental ADD COLUMN IF NOT EXISTS pengguna_id INTEGER REFERENCES pengguna(id) ON DELETE SET NULL;
    ALTER TABLE transaksi_rental ADD COLUMN IF NOT EXISTS status_sewa VARCHAR(12) NOT NULL DEFAULT 'berjalan';
");

$tipe_total = $pdo->query("SELECT data_type FROM information_schema.columns
                           WHERE table_schema = current_schema()
                             AND table_name = 'transaksi_rental'
                             AND column_name = 'total_biaya'")->fetchColumn();
if ($tipe_total !== 'bigint') {
    $pdo->exec("ALTER TABLE transaksi_rental ALTER COLUMN total_biaya TYPE BIGINT");
}

if (!$pdo->query("SELECT 1 FROM pengguna WHERE role = 'admin' LIMIT 1")->fetchColumn()) {
    $pdo->prepare("INSERT INTO pengguna (nama, email, password, role) VALUES (?, ?, ?, 'admin')")
        ->execute(['Administrator', 'admin@autorent.local', password_hash('admin123', PASSWORD_DEFAULT)]);
}

if (!$pdo->query("SELECT 1 FROM armada_mobil LIMIT 1")->fetchColumn()) {
    $pdo->prepare("INSERT INTO armada_mobil
        (no_polisi, merk_mobil, tipe_transmisi, harga_per_hari, foto)
        VALUES (?, ?, ?, ?, ?), (?, ?, ?, ?, ?)")
        ->execute([
            'B 1 ARD', 'Ferrari SF90 Stradale', 'Auto', 8500000, '029e03d1a729428f.jpg',
            'B 2 ARD', 'Toyota GR Supra', 'Auto', 2750000, 'b7c3aa96b5e20c74.jpg',
        ]);
}
