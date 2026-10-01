<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// 1. KORUMA: Eğer kişi zaten giriş yapmışsa, onu tekrar giriş ekranında tutma, anasayfaya gönder!
if (isset($_SESSION['ogrenci_id'])) {
    header("Location: index.php");
    exit;
}

require_once 'db_baglanti.php';
$hata_mesaji = "";

try {
    $db->query("SELECT 1 FROM ogrenci LIMIT 1");
} catch (Exception $e) {
    $hata_mesaji = "SİSTEM UYARISI: Veritabanında 'ogrenci' tablosu bulunamadı!";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && empty($hata_mesaji)) {
    $ogrenci_no = trim($_POST['ogrenci_no']);
    $sifre = trim($_POST['sifre']);

    try {
        $sorgu = $db->prepare("SELECT * FROM ogrenci WHERE ogrenci_no = :no");
        $sorgu->bindParam(':no', $ogrenci_no, PDO::PARAM_STR);
        $sorgu->execute();
        $ogrenci = $sorgu->fetch(PDO::FETCH_ASSOC);

        if ($ogrenci && $sifre === "123456") {
            $_SESSION['ogrenci_id'] = $ogrenci['id'];
            $_SESSION['ad_soyad'] = $ogrenci['ad'] . " " . $ogrenci['soyad'];
            $_SESSION['bolum'] = $ogrenci['bolum'];
            $_SESSION['ogrenci_no'] = $ogrenci['ogrenci_no'];
            
            // 2. YÖNLENDİRME: Başarılı girişten sonra ana sayfaya dön
            header("Location: index.php");
            exit;
        } else {
            $hata_mesaji = "Öğrenci numarası veya şifre hatalı!";
        }
    } catch (Exception $e) {
        $hata_mesaji = "Sorgu Hatası: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Mudanya OİS | Güvenli Giriş</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Montserrat', sans-serif; background: #002B5C; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; color:#333; }
        .login-box { background: white; padding: 40px; border-radius: 10px; width: 350px; text-align: center; box-shadow: 0 10px 25px rgba(0,0,0,0.3); border-top: 5px solid #FDB913;}
        .login-box h2 { color: #0056A4; margin-bottom: 20px; }
        input { width: 90%; padding: 12px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 5px; font-size: 14px; }
        button { width: 100%; padding: 12px; background: #FDB913; color: #002B5C; border: none; border-radius: 5px; font-weight: bold; cursor: pointer; font-size: 16px; margin-bottom: 15px;}
        .alert { background: #fee2e2; color: #991b1b; padding: 10px; border-radius: 5px; margin-bottom: 15px; font-size: 13px; text-align: left;}
        .back-link { display: block; color: #666; text-decoration: none; font-size: 14px; margin-top: 10px;}
        .back-link:hover { color: #0056A4; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>OİS Öğrenci Girişi</h2>
        
        <?php if($hata_mesaji != ""): ?>
            <div class="alert"><?php echo $hata_mesaji; ?></div>
        <?php endif; ?>

        <form action="giris.php" method="POST">
            <input type="text" name="ogrenci_no" placeholder="Öğrenci No (Örn: 1234567)" required>
            <input type="password" name="sifre" placeholder="Şifre (123456)" required>
            <button type="submit">Sisteme Gir</button>
        </form>
        
        <!-- BAĞLANTI HATASI DÜZELTİLDİ: index.html yerine index.php -->
        <a href="index.php" class="back-link">← Anasayfaya Dön</a>
    </div>
</body>
</html>