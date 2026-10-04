<?php
// Pintu masuk: arahkan ke beranda sesuai peran
require __DIR__ . '/config/database.php';
require __DIR__ . '/includes/functions.php';

$p = pengguna();
if (!$p) arahkan('login.php');
arahkan($p['role'] === 'admin' ? 'admin.php' : 'user.php');
