<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'db_baglanti.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_GET['arama_sorgusu'])) {
    echo json_encode(["durum" => "hata", "mesaj" => "Arama sorgusu eksik."]); exit;
}

// 1. Türkçe karakterleri düzgün küçük harfe çevirme fonksiyonu
$arama = mb_strtolower(trim($_GET['arama_sorgusu']), 'UTF-8');

if (mb_strlen($arama, 'UTF-8') < 3) {
    echo json_encode(["durum" => "hata", "mesaj" => "Arama yapmak için en az 3 harf girmelisiniz."]); exit;
}

// 2. Benzerlik analizi yaparken Türkçe harfleri İngilizceye indirgeyen özel fonksiyon
function turkce_karsilastirma($metin) {
    $aranan = array('ç','ğ','ı','i','ö','ş','ü');
    $yerine = array('c','g','i','i','o','s','u');
    return str_replace($aranan, $yerine, $metin);
}

try {
    $sorgu = $db->query("SELECT * FROM katalog");
    $tum_kitaplar = $sorgu->fetchAll(PDO::FETCH_ASSOC);

    $bulunan_kitaplar = [];

    foreach ($tum_kitaplar as $kitap) {
        $baslik_kucuk = mb_strtolower($kitap['baslik'], 'UTF-8');
        $yazar_kucuk = mb_strtolower($kitap['yazar'], 'UTF-8');
        
        // A) KESİN EŞLEŞME (Doğru yazıldıysa doğrudan bulur)
        if (mb_strpos($baslik_kucuk, $arama, 0, 'UTF-8') !== false || mb_strpos($yazar_kucuk, $arama, 0, 'UTF-8') !== false) {
            $kitap['skor'] = 100; // Yüzde 100 eşleşme
            $bulunan_kitaplar[] = $kitap;
            continue; 
        }

        // B) TÜRKÇE DİLİNE UYGUN YZ BENZERLİK ALGORİTMASI
        $kelimeler = explode(" ", $baslik_kucuk . " " . $yazar_kucuk);
        $en_iyi_benzerlik = 0;

        foreach ($kelimeler as $kelime) {
            if (mb_strlen($kelime, 'UTF-8') < 3) continue;
            
            // KÖK ANALİZİ: Kitaptaki kelimenin, sadece kullanıcının aradığı kelimenin uzunluğu kadar BAŞ KISMINI kesip alıyoruz.
            // Örn: "Algoritmalara" kelimesinin ilk 9 harfini keser ("Algoritma") ve "Algorutma" ile kıyaslar. Ekleri umursamaz!
            $kesilmis_kelime = mb_substr($kelime, 0, mb_strlen($arama, 'UTF-8'), 'UTF-8');
            
            // Harfleri temizleyip yüzdelik olarak (similar_text) karşılaştırıyoruz
            $temiz_arama = turkce_karsilastirma($arama);
            $temiz_kesilmis = turkce_karsilastirma($kesilmis_kelime);
            
            similar_text($temiz_arama, $temiz_kesilmis, $benzerlik_yuzdesi);

            // Eğer kelimeler %75 ve üzeri oranda benziyorsa bu yazım hatasıdır, kabul ediyoruz
            if ($benzerlik_yuzdesi > 75 && $benzerlik_yuzdesi > $en_iyi_benzerlik) {
                $en_iyi_benzerlik = $benzerlik_yuzdesi;
            }
        }

        // Eğer yazım hatası tolere edildiyse kitaba etiket koy
        if ($en_iyi_benzerlik > 0) {
            $kitap['skor'] = $en_iyi_benzerlik;
            $kitap['baslik'] = $kitap['baslik'] . ' <span style="font-size:11px; color:#ef4444; font-weight:bold;">(Bunu mu demek istediniz?)</span>';
            $bulunan_kitaplar[] = $kitap;
        }
    }

    // Sonuçları benzerlik oranına göre sırala (En çok benzeyen en üstte çıkar)
    usort($bulunan_kitaplar, function($a, $b) {
        return $b['skor'] <=> $a['skor'];
    });

    $bulunan_kitaplar = array_slice($bulunan_kitaplar, 0, 10);

    if (count($bulunan_kitaplar) > 0) {
        echo json_encode(["durum" => "basarili", "sonuclar" => $bulunan_kitaplar]);
    } else {
        echo json_encode(["durum" => "hata", "mesaj" => "Girdiğiniz terime veya benzerine uygun bir kaynak bulunamadı."]);
    }

} catch (Exception $e) {
    echo json_encode(["durum" => "hata", "mesaj" => "Sistem Hatası: " . $e->getMessage()]);
}
?>