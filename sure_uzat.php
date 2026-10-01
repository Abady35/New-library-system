<?php
session_start();
require_once 'db_baglanti.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['ogrenci_id'])) {
    echo json_encode(["durum" => "hata", "mesaj" => "Oturum süreniz dolmuş."]); exit;
}

$ogrenci_id = $_SESSION['ogrenci_id'];
$kitap_id = intval($_POST['kitap_id']);

try {
    // İş Kuralı: Mevcut iade tarihine +7 gün ekle
    $guncelle = $db->prepare("UPDATE odunc_islemi SET beklenen_iade_tarihi = DATE_ADD(beklenen_iade_tarihi, INTERVAL 7 DAY) WHERE ogrenci_id = :ogr AND katalog_id = :kitap");
    $guncelle->execute([':ogr' => $ogrenci_id, ':kitap' => $kitap_id]);

    if ($guncelle->rowCount() > 0) {
        echo json_encode(["durum" => "basarili", "mesaj" => "Süre 7 gün uzatıldı! Güncel durumu panelden görebilirsiniz."]);
    } else {
        echo json_encode(["durum" => "hata", "mesaj" => "İşlem yapılamadı. Bu kitap sizde olmayabilir."]);
    }
} catch (PDOException $e) {
    echo json_encode(["durum" => "hata", "mesaj" => "Sistem Hatası."]);
}
?>