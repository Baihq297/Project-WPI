<?php
session_start();

// 1. Hapus Session
$_SESSION = [];
session_unset();
session_destroy();

// 2. Hapus Cookie Session (Penting untuk keamanan)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Header Anti-Cache (Supaya halaman logout ini tidak disimpan browser)
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// 4. Redirect ke halaman LOGIN / INDEX
header("Location: ../index.php"); 
exit;
?>
