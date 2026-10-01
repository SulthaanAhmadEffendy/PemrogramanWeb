# MySQL dan hosting SIMPUS-Mini

## Lokal dengan XAMPP

1. Pindahkan folder proyek ke `C:\xampp\htdocs\Pweb` (atau gunakan folder `htdocs` XAMPP yang sudah ada).
2. Jalankan Apache dan MySQL melalui XAMPP Control Panel.
3. Buka phpMyAdmin di `http://localhost/phpmyadmin`, buat database `simpus_mini`, pilih database itu, lalu impor file `Jobsheet3Bootstrap/database.sql`.
4. Salin `Jobsheet3Bootstrap/includes/config.local.php.example` menjadi `Jobsheet3Bootstrap/includes/config.local.php`. Untuk XAMPP standar, konfigurasi contoh (`127.0.0.1`, port `3306`, user `root`, password kosong) dapat dipakai.
5. Buka `http://localhost/Pweb/Jobsheet3Bootstrap/`. PHP akan menjalankan `index.php`; beranda, daftar buku/anggota, tambah, edit, dan hapus kini memakai MySQL.

Jika proyek dibuka melalui Live Server atau dengan membuka file `.html`, PHP tidak akan dijalankan. Gunakan URL localhost melalui Apache.

## Hosting dengan cPanel

1. Pilih hosting yang mendukung PHP 8.x, MySQL/MariaDB, dan ekstensi PDO MySQL.
2. Di cPanel, buat database dan user melalui **MySQL Databases**, lalu beri user hak akses ke database. Catat nama database, username, password, dan host MySQL yang disediakan.
3. Buka phpMyAdmin dari cPanel, pilih database tersebut, lalu impor `database.sql` dari folder `Jobsheet3Bootstrap`.
4. Upload isi folder `Jobsheet3Bootstrap` ke document root domain/subdomain, misalnya `public_html`. Unggah PHP dan folder `assets`; jangan hanya mengunggah file HTML.
5. Buat `includes/config.local.php` di hosting berdasarkan `includes/config.local.php.example`, lalu isi kredensial yang diberikan panel. Nama database dan user pada shared hosting sering memiliki prefix akun.
6. Buka domain, lalu uji tambah, ubah, dan hapus buku serta anggota.

Jangan commit atau membagikan `config.local.php`; file ini dikecualikan oleh `.gitignore`.

## Vercel

`vercel.json` di root repositori saat ini mengarahkan semua request ke `Jobsheet3Bootstrap`, tetapi tidak menyediakan runtime PHP atau MySQL. Versi database ini tidak dapat langsung dijalankan dengan konfigurasi tersebut. Untuk deploy paling sederhana, gunakan shared hosting PHP/MySQL; jika harus memakai Vercel, perlu runtime PHP serverless dan layanan MySQL eksternal yang dikonfigurasi terpisah.