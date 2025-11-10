<?php
session_start();
unset($_SESSION['keranjang']); // kosongkan
header('Location: ../index.php?page=menu');
exit;
