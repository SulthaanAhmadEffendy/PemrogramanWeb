<?php
session_start();

// session_destroy() tidak mengosongkan $_SESSION pada request yang sedang berjalan.
$_SESSION = [];
session_destroy();

header('Location: debug_session.php');
exit;