<?php
$host = "localhost";
$db_adi = "yeni_kutuphane"; // SQL'de oluşturduğumuz isme göre güncellendi
$kullanici = "root";
$sifre = ""; // Yeni XAMPP kurulumlarında şifre varsayılan olarak boştur

try {
    // Tüm sistemin aradığı o kritik $db değişkeni işte burada oluşturuluyor!
    $db = new PDO("mysql:host=$host;dbname=$db_adi;charset=utf8", $kullanici, $sifre);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Veritabanı Bağlantı Hatası: " . $e->getMessage());
}
?>