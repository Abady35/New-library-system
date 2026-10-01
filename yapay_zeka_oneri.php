<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'db_baglanti.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_GET['ogrenci_id']) || empty($_GET['ogrenci_id'])) {
    echo json_encode(["durum" => "hata", "mesaj" => "Lütfen geçerli bir Öğrenci ID girin."]); exit;
}

$ogrenci_id = intval($_GET['ogrenci_id']);

try {
    // 1. Öğrencinin bölümünü bul
    $ogrSorgu = $db->prepare("SELECT ad, bolum FROM ogrenci WHERE id = :id");
    $ogrSorgu->bindParam(':id', $ogrenci_id, PDO::PARAM_INT);
    $ogrSorgu->execute();
    $ogrenci = $ogrSorgu->fetch(PDO::FETCH_ASSOC);

    if (!$ogrenci) {
        echo json_encode(["durum" => "hata", "mesaj" => "Öğrenci bulunamadı."]); exit;
    }

    $ogrenci_bolum = $ogrenci['bolum'];

    // 2. İŞ KURALI 1: Bölümlere Göre İlgi Alanı Matrisi (Mapping)
    // Öğrencinin bölümündeki kelimelere bakarak ona uygun KATEGORİLERİ belirliyoruz.
    $ilgi_alanlari = [];
    $bolum_kucuk = mb_strtolower($ogrenci_bolum, 'UTF-8');
    
    if (strpos($bolum_kucuk, 'bilgisayar') !== false || strpos($bolum_kucuk, 'yazılım') !== false) {
        $ilgi_alanlari = ['Bilgisayar Mühendisliği', 'Mühendislik & Yazılım', 'Yapay Zeka', 'Temel Bilimler'];
    } elseif (strpos($bolum_kucuk, 'ekonomi') !== false || strpos($bolum_kucuk, 'işletme') !== false) {
        $ilgi_alanlari = ['Ekonomi', 'Yönetim Bilişim', 'Felsefe'];
    } elseif (strpos($bolum_kucuk, 'tıp') !== false) {
        $ilgi_alanlari = ['Tıp', 'Temel Bilimler'];
    } else {
        // Tanımsız bir bölümse genel kültür öner
        $ilgi_alanlari = ['Edebiyat', 'Tarih', 'Felsefe'];
    }

    // PHP Dizisini SQL'in anlayacağı " 'Yapay Zeka', 'Mühendislik' " formatına çevir
    $in_kategoriler = "'" . implode("','", $ilgi_alanlari) . "'";

    // 3. İŞ KURALI 2: Filtreli Akıllı Sorgu
    // - Sadece ilgi alanlarındaki kategorilerden getir.
    // - NOT IN (...) komutu ile öğrencinin şuan OKUDUĞU kitapları önerilerden DIŞLA!
    $oneriSorguStr = "
        SELECT * FROM katalog 
        WHERE kategori IN ($in_kategoriler)
        AND id NOT IN (SELECT katalog_id FROM odunc_islemi WHERE ogrenci_id = :ogr_id)
        ORDER BY RAND() LIMIT 4
    ";
    
    $oneriSorgu = $db->prepare($oneriSorguStr);
    $oneriSorgu->bindParam(':ogr_id', $ogrenci_id, PDO::PARAM_INT);
    $oneriSorgu->execute();
    $oneriler = $oneriSorgu->fetchAll(PDO::FETCH_ASSOC);

    // 4. İŞ KURALI 3: Yedek Plan (Fallback)
    // Eğer öğrenci kendi bölümündeki tüm kitapları okumuşsa sistem boş kalmasın, genel arşivden versin.
    if (count($oneriler) == 0) {
         $yedekSorgu = $db->prepare("SELECT * FROM katalog WHERE id NOT IN (SELECT katalog_id FROM odunc_islemi WHERE ogrenci_id = :ogr_id) ORDER BY RAND() LIMIT 3");
         $yedekSorgu->bindParam(':ogr_id', $ogrenci_id, PDO::PARAM_INT);
         $yedekSorgu->execute();
         $oneriler = $yedekSorgu->fetchAll(PDO::FETCH_ASSOC);
         $mesaj = "Bölümünüzle ilgili kaynakların çoğunu incelemişsiniz! İlginizi çekebilecek diğer eserler:";
    } else {
         $mesaj = "Algoritmamız, <b>" . $ogrenci_bolum . "</b> müfredatını ve okuma geçmişinizi analiz ederek size en uygun kaynakları hazırladı:";
    }

    // Sonuçları Gönder
    echo json_encode([
        "durum" => "basarili", 
        "mesaj" => $mesaj, 
        "oneri_listesi" => $oneriler
    ]);

} catch (Exception $e) {
    echo json_encode(["durum" => "hata", "mesaj" => "Sistem hatası: " . $e->getMessage()]);
}
?>