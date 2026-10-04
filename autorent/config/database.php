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
    ALTER TABLE armada_mobil     ADD COLUMN IF NOT EXISTS foto VARCHAR(255);
    ALTER TABLE transaksi_rental ADD COLUMN IF NOT EXISTS pengguna_id INTEGER REFERENCES pengguna(id) ON DELETE SET NULL;
    ALTER TABLE transaksi_rental ADD COLUMN IF NOT EXISTS status_sewa VARCHAR(12) NOT NULL DEFAULT 'berjalan';
");

if (!$pdo->query("SELECT 1 FROM pengguna WHERE role = 'admin' LIMIT 1")->fetchColumn()) {
    $pdo->prepare("INSERT INTO pengguna (nama, email, password, role) VALUES (?, ?, ?, 'admin')")
        ->execute(['Administrator', 'admin@autorent.local', password_hash('admin123', PASSWORD_DEFAULT)]);
}
