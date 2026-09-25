<?php
$host = 'localhost';
$port = '5432';
$dbname = 'simpus_mini'; 
$user = 'postgres';            
$password = '123456';    

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->exec("CREATE TABLE IF NOT EXISTS buku (
        id SERIAL PRIMARY KEY,
        judul VARCHAR(255) NOT NULL,
        pengarang VARCHAR(255) NOT NULL,
        tahun INTEGER NOT NULL CHECK (tahun BETWEEN 1900 AND 2100),
        isbn VARCHAR(32),
        stok INTEGER NOT NULL CHECK (stok >= 0),
        kategori VARCHAR(50) NOT NULL
    )");
    $pdo->exec("CREATE TABLE IF NOT EXISTS anggota (
        id SERIAL PRIMARY KEY,
        nama VARCHAR(255) NOT NULL,
        no_anggota VARCHAR(100) NOT NULL UNIQUE,
        alamat TEXT,
        no_hp VARCHAR(30)
    )");
} catch (PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}
?>