<?php
session_start();
require_once 'db_baglanti.php';

$hata_mesaji = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ogrenci_no = trim($_POST['ogrenci_no']);
    $sifre = trim($_POST['sifre']);

    try {
        // Veritabanında öğrenciyi ara
        $sorgu = $db->prepare("SELECT * FROM ogrenci WHERE ogrenci_no = :no");
        $sorgu->bindParam(':no', $ogrenci_no, PDO::PARAM_STR);
        $sorgu->execute();
        $ogrenci = $sorgu->fetch(PDO::FETCH_ASSOC);

        if ($ogrenci) {
            if ($sifre === "123456") {
                // Şifre doğruysa bilgileri al ve PANELE GİT
                $_SESSION['ogrenci_id'] = $ogrenci['id'];
                $_SESSION['ad_soyad'] = $ogrenci['ad'] . " " . $ogrenci['soyad'];
                $_SESSION['bolum'] = $ogrenci['bolum'];
                $_SESSION['ogrenci_no'] = $ogrenci['ogrenci_no'];
                
                header("Location: ogrenci_paneli.php");
                exit;
            } else {
                $hata_mesaji = "Numara bulundu ama Şifre Yanlış! (Şifreniz 123456 olmalı)";
            }
        } else {
            $hata_mesaji = "Sistemde " . $ogrenci_no . " numaralı bir öğrenci bulunamadı! Veritabanına eklenmemiş olabilir.";
        }
    } catch (PDOException $e) {
        $hata_mesaji = "Veritabanı Hatası: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Mudanya OİS | Öğrenci Girişi</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Montserrat', sans-serif; background: linear-gradient(135deg, #002B5C 0%, #0056A4 100%); display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-container { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 15px 30px rgba(0,0,0,0.2); width: 100%; max-width: 400px; text-align: center; border-top: 6px solid #FDB913; }
        .logo img { height: 70px; margin-bottom: 20px; }
        .login-container h2 { color: #002B5C; margin-bottom: 5px; }
        .login-container p { color: #666; font-size: 14px; margin-bottom: 25px; }
        .input-group { position: relative; margin-bottom: 20px; }
        .input-group i { position: absolute; left: 15px; top: 15px; color: #999; }
        .input-group input { width: 100%; padding: 15px 15px 15px 45px; border: 1px solid #ddd; border-radius: 8px; font-size: 15px; box-sizing: border-box; }
        .btn-login { width: 100%; padding: 15px; background: #0056A4; color: white; border: none; border-radius: 8px; font-size: 16px; font-weight: bold; cursor: pointer; }
        .alert { background: #fee2e2; color: #991b1b; padding: 15px; border-radius: 5px; margin-bottom: 20px; text-align: left; border-left: 4px solid #ef4444; font-weight:bold;}
    </style>
</head>
<body>

    <div class="login-container">
        <div class="logo">
            <img src="https://mudanya.edu.tr/assets/images/logo.png" alt="Mudanya Logo">
        </div>
        <h2>Yeni OİS Girişi</h2>
        <p>Tarayıcı önbelleği temizlendi</p>

        <?php if($hata_mesaji != ""): ?>
            <div class="alert"><i class="fas fa-exclamation-triangle"></i> <?php echo $hata_mesaji; ?></div>
        <?php endif; ?>

        <!-- Artık form kendini "giris.php" dosyasına gönderiyor -->
        <form action="giris.php" method="POST">
            <div class="input-group">
                <i class="fas fa-id-card"></i>
                <input type="text" name="ogrenci_no" placeholder="Öğrenci Numaranız" required>
            </div>
            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="sifre" placeholder="OİS Şifreniz" required>
            </div>
            <button type="submit" class="btn-login">Giriş Yap <i class="fas fa-sign-in-alt"></i></button>
        </form>
    </div>

</body>
</html>