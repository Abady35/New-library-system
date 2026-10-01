<?php
session_start();

// Tüm oturum değişkenlerini (session) boşalt
$_SESSION = array();

// Oturumu tamamen imha et
session_destroy();

// Kullanıcıyı güvenli bir şekilde anasayfaya yönlendir
header("Location: index.php");
exit;
?>