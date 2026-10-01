<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once 'db_baglanti.php';

// Güvenlik kontrolü
if (!isset($_SESSION['ogrenci_id'])) {
    header("Location: giris.php");
    exit;
}

$alinan_kitaplar = [];
$sistem_mesaji = "";

try {
    $tabloKontrol = $db->query("SHOW TABLES LIKE 'odunc_islemi'");
    if ($tabloKontrol->rowCount() > 0) {
        $oduncSorgu = $db->prepare("
            SELECT k.id as kitap_id, k.baslik, k.yazar, o.verilis_tarihi, o.beklenen_iade_tarihi 
    FROM odunc_islemi o
    LEFT JOIN katalog k ON o.katalog_id = k.id
    WHERE o.ogrenci_id = :ogr_id
        ");
        $oduncSorgu->bindParam(':ogr_id', $_SESSION['ogrenci_id'], PDO::PARAM_INT);
        $oduncSorgu->execute();
        $alinan_kitaplar = $oduncSorgu->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $sistem_mesaji = "Sistem Bilgisi: Henüz kütüphaneden ödünç kitap alma işleminiz kaydedilmedi.";
    }
} catch (Exception $e) {
    $sistem_mesaji = "Veri çekilirken sorun oluştu: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Öğrenci Paneli | OİS</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Montserrat', sans-serif; background-color: #F5F7FA; margin: 0; color: #333; }
        .header { background: #002B5C; color: white; padding: 20px 50px; display: flex; justify-content: space-between; align-items: center; border-bottom: 4px solid #FDB913; }
        .header-links a { color: white; text-decoration: none; font-weight: bold; margin-left: 20px; transition: 0.3s; }
        .header-links a:hover { color: #FDB913; }
        .container { max-width: 900px; margin: 40px auto; padding: 20px; }
        .card { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); margin-bottom: 20px; }
        .card h2 { color: #0056A4; margin-top:0; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { background: #0056A4; color: white; padding: 12px; text-align: left; }
        td { padding: 12px; border-bottom: 1px solid #ddd; }
        .footer { text-align: center; margin-top: 50px; font-size: 13px; color: #666; }
    </style>
</head>
<body>

    <div class="header">
        <div><i class="fas fa-university"></i> Mudanya Üniversitesi OİS</div>
        <div class="header-links">
            Oturum: <?php echo $_SESSION['ad_soyad']; ?>
            <!-- BAĞLANTI HATALARI DÜZELTİLDİ -->
            <a href="index.php"><i class="fas fa-home"></i> Anasayfa</a>
            <a href="cikis.php" style="color: #FDB913;"><i class="fas fa-sign-out-alt"></i> Çıkış Yap</a>
        </div>
    </div>

    <div class="container">
        <div class="card" style="border-left: 5px solid #0056A4;">
            <h2>Hoş Geldin, <?php echo $_SESSION['ad_soyad']; ?></h2>
            <p><strong>Öğrenci Numaran:</strong> <?php echo $_SESSION['ogrenci_no']; ?></p>
            <p><strong>Bölümün:</strong> <?php echo $_SESSION['bolum']; ?></p>
        </div>

        <div class="card">
            <h3 style="color:#002B5C; border-bottom:2px solid #FDB913; padding-bottom:10px; display:inline-block;"><i class="fas fa-book-reader"></i> Ödünç Alınan Kaynaklar</h3>
            
            <?php if($sistem_mesaji != ""): ?>
                <p style="color: #d97706; padding: 10px; background: #fef3c7; border-radius: 5px;"><?php echo $sistem_mesaji; ?></p>
            <?php endif; ?>

            <?php if(count($alinan_kitaplar) > 0): ?>
                <table>
                    <tr><th>Kitap Adı</th><th>İade Tarihi</th><th>İşlem</th></tr>
                    <?php foreach($alinan_kitaplar as $kitap): ?>
                    <tr>
                        <td><strong><?php echo $kitap['baslik'] ? $kitap['baslik'] : 'Bilinmeyen Kitap'; ?></strong></td>
                        <td style="color: #d97706; font-weight:bold;"><?php echo date('d.m.Y', strtotime($kitap['beklenen_iade_tarihi'])); ?></td>
                        <td>
                            <button onclick="sureUzat(<?php echo $kitap['kitap_id']; ?>)" style="background:#0056A4; color:white; border:none; padding:8px 12px; border-radius:5px; cursor:pointer; font-size:12px;">
                                <i class="fas fa-clock"></i> +7 Gün Uzat
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            <?php else: ?>
                <p style="color:#666;">Şu anda üzerinizde kayıtlı bir kütüphane materyali bulunmuyor.</p>
            <?php endif; ?>

            <script>
            function sureUzat(kitapId) {
                if(!confirm("Bu kitabın süresini 7 gün uzatmak istediğinize emin misiniz?")) return;
                
                let formData = new FormData();
                formData.append('kitap_id', kitapId);

                fetch('sure_uzat.php', { method: 'POST', body: formData })
                .then(response => response.json())
                .then(data => {
                    if(data.durum === 'basarili') {
                        alert("✅ " + data.mesaj);
                        location.reload(); // Tarihin güncellendiğini görmek için sayfayı yenile
                    } else {
                        alert("❌ HATA: " + data.mesaj);
                    }
                });
            }
            </script>
        </div>
    </div>

    <div class="footer">
        <p>© 2026 Mudanya Üniversitesi Kütüphane Bilgi Sistemi</p>
        <p>Sistem Analizi Projesi | Geliştiriciler: Abady & Engincan Hümaz</p>
    </div>

</body>
</html>