

CREATE TABLE IF NOT EXISTS armada_mobil (
    id             SERIAL PRIMARY KEY,
    no_polisi      VARCHAR(15)  NOT NULL UNIQUE,
    merk_mobil     VARCHAR(80)  NOT NULL,
    tipe_transmisi VARCHAR(10)  NOT NULL CHECK (tipe_transmisi IN ('Manual', 'Auto')),
    harga_per_hari INTEGER      NOT NULL CHECK (harga_per_hari >= 0)
);

CREATE TABLE IF NOT EXISTS transaksi_rental (
    id             SERIAL PRIMARY KEY,
    mobil_id       INTEGER      NOT NULL REFERENCES armada_mobil(id),
    nama_penyewa   VARCHAR(100) NOT NULL,
    tanggal_pinjam DATE         NOT NULL,
    lama_hari      INTEGER      NOT NULL CHECK (lama_hari >= 1),
    total_biaya    INTEGER      NOT NULL
);


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
