<?php
session_start();
require_once 'db_baglanti.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['ogrenci_id'])) {
    echo json_encode(["durum" => "hata", "mesaj" => "Ödünç almak için giriş yapmalısınız."]); exit;
}

if (!isset($_POST['kitap_id'])) {
    echo json_encode(["durum" => "hata", "mesaj" => "Sistem hatası: Kitap seçilemedi."]); exit;
}

$ogrenci_id = $_SESSION['ogrenci_id'];
$kitap_id = intval($_POST['kitap_id']);
$verilis = date('Y-m-d');
$iade = date('Y-m-d', strtotime('+15 days')); // İş Kuralı: 15 Gün Süre

try {
    // KONTROL: Öğrenci bu kitabı zaten almış mı? (Mantıksal Hata Önleyici)
    $kontrol = $db->prepare("SELECT id FROM odunc_islemi WHERE ogrenci_id = :ogr AND katalog_id = :kitap");
    $kontrol->execute([':ogr' => $ogrenci_id, ':kitap' => $kitap_id]);
    
    if ($kontrol->rowCount() > 0) {
        echo json_encode(["durum" => "hata", "mesaj" => "Bu kitap zaten üzerinizde kayıtlı!"]); exit;
    }

    // Kitabı Öğrenciye Zimmetle
    $ekle = $db->prepare("INSERT INTO odunc_islemi (ogrenci_id, katalog_id, verilis_tarihi, beklenen_iade_tarihi) VALUES (:ogr, :kitap, :ver, :iade)");
    $ekle->execute([':ogr' => $ogrenci_id, ':kitap' => $kitap_id, ':ver' => $verilis, ':iade' => $iade]);

    echo json_encode(["durum" => "basarili", "mesaj" => "Kitap başarıyla ödünç alındı. İade tarihiniz: " . date('d.m.Y', strtotime($iade))]);

} catch (PDOException $e) {
    echo json_encode(["durum" => "hata", "mesaj" => "Veritabanı Hatası."]);
}
?>