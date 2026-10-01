<?php
session_start();
if (!isset($_SESSION['admin_giris'])) { header("Location: admin_giris.php"); exit; }
require_once 'db_baglanti.php';

$mesaj = "";

// --- İŞLEM: KİTAP EKLEME & GÜNCELLEME ---
if (isset($_POST['kitap_ekle'])) {
    $baslik = $_POST['baslik'];
    $yazar = $_POST['yazar'];
    $kategori = $_POST['kategori'];
    $yasin = !empty($_POST['yayin_yili']) ? $_POST['yayin_yili'] : null;
    $isbn = $_POST['isbn'];

    $sql = "INSERT INTO katalog (baslik, yazar, kategori, yayin_yili, isbn) VALUES (?, ?, ?, ?, ?)";
    $stmt = $db->prepare($sql);
    if($stmt->execute([$baslik, $yazar, $kategori, $yasin, $isbn])) {
        $mesaj = "Yeni kitap başarıyla eklendi!";
    }
} elseif (isset($_POST['kitap_guncelle'])) {
    $id = $_POST['kitap_id'];
    $baslik = $_POST['baslik'];
    $yazar = $_POST['yazar'];
    $kategori = $_POST['kategori'];
    $yasin = !empty($_POST['yayin_yili']) ? $_POST['yayin_yili'] : null;
    $isbn = $_POST['isbn'];

    $sql = "UPDATE katalog SET baslik = ?, yazar = ?, kategori = ?, yayin_yili = ?, isbn = ? WHERE id = ?";
    $stmt = $db->prepare($sql);
    if($stmt->execute([$baslik, $yazar, $kategori, $yasin, $isbn, $id])) {
        $mesaj = "Kitap başarıyla güncellendi!";
    }
}

// --- İŞLEM: ÖĞRENCİ EKLEME & GÜNCELLEME ---
if (isset($_POST['ogrenci_ekle'])) {
    $ogrenci_no = trim($_POST['ogrenci_no']);
    $ad = $_POST['ad'];
    $soyad = $_POST['soyad'];
    $bolum = $_POST['bolum'];

    try {
        $sql = "INSERT INTO ogrenci (ogrenci_no, ad, soyad, bolum) VALUES (?, ?, ?, ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$ogrenci_no, $ad, $soyad, $bolum]);
        $mesaj = "Yeni öğrenci başarıyla eklendi!";
    } catch (PDOException $e) {
        $mesaj = "HATA: Öğrenci eklenemedi! Öğrenci numarası zaten kullanılıyor olabilir.";
    }
} elseif (isset($_POST['ogrenci_guncelle'])) {
    $id = $_POST['ogrenci_id'];
    $ogrenci_no = trim($_POST['ogrenci_no']);
    $ad = $_POST['ad'];
    $soyad = $_POST['soyad'];
    $bolum = $_POST['bolum'];

    try {
        $sql = "UPDATE ogrenci SET ogrenci_no = ?, ad = ?, soyad = ?, bolum = ? WHERE id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$ogrenci_no, $ad, $soyad, $bolum, $id]);
        $mesaj = "Öğrenci başarıyla güncellendi!";
    } catch (PDOException $e) {
        $mesaj = "HATA: Öğrenci güncellenemedi! Numara başka birine ait olabilir.";
    }
}

// --- İŞLEM: KİTAP SİLME ---
if (isset($_GET['sil_kitap'])) {
    $id = intval($_GET['sil_kitap']);
    try {
        $stmt = $db->prepare("DELETE FROM katalog WHERE id = ?");
        $stmt->execute([$id]);
        $mesaj = "Kitap sistemden silindi.";
    } catch (Exception $e) {
        $mesaj = "HATA: Bu kitap şu an bir öğrencide ödünçte olduğu için silinemez!";
    }
}

// --- İŞLEM: ÖĞRENCİ SİLME ---
if (isset($_GET['sil_ogrenci'])) {
    $id = intval($_GET['sil_ogrenci']);
    try {
        $stmt = $db->prepare("DELETE FROM ogrenci WHERE id = ?");
        $stmt->execute([$id]);
        $mesaj = "Öğrenci sistemden silindi.";
    } catch (Exception $e) {
        $mesaj = "HATA: Bu öğrencinin aktif ödünç aldığı kitaplar olduğu için silinemez!";
    }
}

// --- DÜZENLEME İÇİN VERİ ÇEKME ---
$duzenle_kitap = null;
if (isset($_GET['duzenle_kitap'])) {
    $stmt = $db->prepare("SELECT * FROM katalog WHERE id = ?");
    $stmt->execute([$_GET['duzenle_kitap']]);
    $duzenle_kitap = $stmt->fetch(PDO::FETCH_ASSOC);
}

$duzenle_ogrenci = null;
if (isset($_GET['duzenle_ogrenci'])) {
    $stmt = $db->prepare("SELECT * FROM ogrenci WHERE id = ?");
    $stmt->execute([$_GET['duzenle_ogrenci']]);
    $duzenle_ogrenci = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Verileri Çek
$kitaplar = $db->query("SELECT * FROM katalog ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
$ogrenciler = $db->query("SELECT * FROM ogrenci ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
$odunc_liste = $db->query("SELECT o.*, k.baslik, ogr.ad, ogr.soyad FROM odunc_islemi o 
                           JOIN katalog k ON o.katalog_id = k.id 
                           JOIN ogrenci ogr ON o.ogrenci_id = ogr.id")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Sistem Yönetimi | Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: sans-serif; background: #f4f7f6; margin: 0; display: flex; }
        .sidebar { width: 250px; background: #2c3e50; color: white; min-height: 100vh; padding: 20px; box-sizing: border-box; }
        .sidebar h3 { border-bottom: 1px solid #555; padding-bottom: 10px; }
        .sidebar a { display: block; color: #bdc3c7; padding: 12px 0; text-decoration: none; border-bottom: 1px solid #34495e; }
        .sidebar a:hover { color: white; }
        .main { flex: 1; padding: 30px; box-sizing: border-box; }
        .card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 30px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 12px; border-bottom: 1px solid #eee; text-align: left; }
        th { background: #f8f9fa; }
        .btn-sil { color: #dc2626; cursor: pointer; text-decoration: none; font-weight: bold; }
        .btn-duzenle { color: #0284c7; cursor: pointer; text-decoration: none; font-weight: bold; margin-right: 10px; }
        .form-row input { padding: 10px; margin-right: 10px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; width: calc(20% - 15px); min-width: 150px; }
        .btn-ekle { background: #27ae60; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; }
        .btn-iptal { background: #64748b; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; text-decoration: none; }
        .alert { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px; font-weight: bold; border: 1px solid #c3e6cb; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h3><i class="fas fa-tools"></i> Admin Panel</h3>
        <a href="#kitaplar"><i class="fas fa-book"></i> Kitap Yönetimi</a>
        <a href="#ogrenciler"><i class="fas fa-users"></i> Öğrenci Listesi</a>
        <a href="#odunc"><i class="fas fa-exchange-alt"></i> Ödünç İşlemleri</a>
        <a href="cikis.php" style="margin-top: 50px; color: #e74c3c;"><i class="fas fa-power-off"></i> Çıkış Yap</a>
    </div>

    <div class="main">
        <h2>Kütüphane Yönetim Sistemi</h2>
        <?php if($mesaj) echo "<div class='alert'>$mesaj</div>"; ?>

        <!-- KİTAP YÖNETİMİ -->
        <div class="card" id="kitaplar">
            <h3><i class="fas fa-book"></i> Kitap Yönetimi (<?php echo $duzenle_kitap ? 'Düzenle' : 'Ekle'; ?>)</h3>
            <form method="POST" class="form-row">
                <?php if($duzenle_kitap): ?>
                    <input type="hidden" name="kitap_id" value="<?php echo $duzenle_kitap['id']; ?>">
                <?php endif; ?>
                <input type="text" name="baslik" placeholder="Kitap Adı" value="<?php echo $duzenle_kitap ? htmlspecialchars($duzenle_kitap['baslik']) : ''; ?>" required>
                <input type="text" name="yazar" placeholder="Yazar" value="<?php echo $duzenle_kitap ? htmlspecialchars($duzenle_kitap['yazar']) : ''; ?>" required>
                <input type="text" name="kategori" placeholder="Kategori" value="<?php echo $duzenle_kitap ? htmlspecialchars($duzenle_kitap['kategori']) : ''; ?>">
                <input type="number" name="yayin_yili" placeholder="Yıl" value="<?php echo $duzenle_kitap ? htmlspecialchars($duzenle_kitap['yayin_yili']) : ''; ?>">
                <input type="text" name="isbn" placeholder="ISBN" value="<?php echo $duzenle_kitap ? htmlspecialchars($duzenle_kitap['isbn']) : ''; ?>">
                
                <?php if($duzenle_kitap): ?>
                    <button type="submit" name="kitap_guncelle" class="btn-ekle">Güncelle</button>
                    <a href="admin_paneli.php#kitaplar" class="btn-iptal">İptal</a>
                <?php else: ?>
                    <button type="submit" name="kitap_ekle" class="btn-ekle">Ekle</button>
                <?php endif; ?>
            </form>

            <h3 style="margin-top: 30px;"><i class="fas fa-list"></i> Mevcut Kitaplar</h3>
            <table>
                <tr><th>ID</th><th>Kitap Adı</th><th>Yazar</th><th>İşlem</th></tr>
                <?php foreach($kitaplar as $k): ?>
                <tr>
                    <td><?php echo $k['id']; ?></td>
                    <td><?php echo htmlspecialchars($k['baslik']); ?></td>
                    <td><?php echo htmlspecialchars($k['yazar']); ?></td>
                    <td>
                        <a href="?duzenle_kitap=<?php echo $k['id']; ?>#kitaplar" class="btn-duzenle">Düzenle</a>
                        <a href="?sil_kitap=<?php echo $k['id']; ?>" class="btn-sil" onclick="return confirm('Silmek istediğine emin misin?')">Sil</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <!-- ÖĞRENCİ YÖNETİMİ -->
        <div class="card" id="ogrenciler">
            <h3><i class="fas fa-user-graduate"></i> Öğrenci Yönetimi (<?php echo $duzenle_ogrenci ? 'Düzenle' : 'Ekle'; ?>)</h3>
            <form method="POST" class="form-row">
                <?php if($duzenle_ogrenci): ?>
                    <input type="hidden" name="ogrenci_id" value="<?php echo $duzenle_ogrenci['id']; ?>">
                <?php endif; ?>
                <input type="text" name="ogrenci_no" placeholder="Öğrenci No" value="<?php echo $duzenle_ogrenci ? htmlspecialchars($duzenle_ogrenci['ogrenci_no']) : ''; ?>" required>
                <input type="text" name="ad" placeholder="Ad" value="<?php echo $duzenle_ogrenci ? htmlspecialchars($duzenle_ogrenci['ad']) : ''; ?>" required>
                <input type="text" name="soyad" placeholder="Soyad" value="<?php echo $duzenle_ogrenci ? htmlspecialchars($duzenle_ogrenci['soyad']) : ''; ?>" required>
                <input type="text" name="bolum" placeholder="Bölüm" value="<?php echo $duzenle_ogrenci ? htmlspecialchars($duzenle_ogrenci['bolum']) : ''; ?>">
                
                <?php if($duzenle_ogrenci): ?>
                    <button type="submit" name="ogrenci_guncelle" class="btn-ekle">Güncelle</button>
                    <a href="admin_paneli.php#ogrenciler" class="btn-iptal">İptal</a>
                <?php else: ?>
                    <button type="submit" name="ogrenci_ekle" class="btn-ekle">Ekle</button>
                <?php endif; ?>
            </form>

            <h3 style="margin-top: 30px;"><i class="fas fa-users"></i> Kayıtlı Öğrenciler</h3>
            <table>
                <tr><th>Öğrenci No</th><th>Ad Soyad</th><th>Bölüm</th><th>İşlem</th></tr>
                <?php foreach($ogrenciler as $o): ?>
                <tr>
                    <td><?php echo htmlspecialchars($o['ogrenci_no']); ?></td>
                    <td><?php echo htmlspecialchars($o['ad']." ".$o['soyad']); ?></td>
                    <td><?php echo htmlspecialchars($o['bolum']); ?></td>
                    <td>
                        <a href="?duzenle_ogrenci=<?php echo $o['id']; ?>#ogrenciler" class="btn-duzenle">Düzenle</a>
                        <a href="?sil_ogrenci=<?php echo $o['id']; ?>" class="btn-sil" onclick="return confirm('Öğrenciyi silmek istediğine emin misin?')">Sil</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <!-- ÖDÜNÇ İŞLEMLERİ -->
        <div class="card" id="odunc">
            <h3><i class="fas fa-history"></i> Aktif Ödünç İşlemleri</h3>
            <table>
                <tr><th>Öğrenci</th><th>Kitap</th><th>İade Tarihi</th></tr>
                <?php foreach($odunc_liste as $od): ?>
                <tr>
                    <td><?php echo htmlspecialchars($od['ad']." ".$od['soyad']); ?></td>
                    <td><?php echo htmlspecialchars($od['baslik']); ?></td>
                    <td><?php echo date('d.m.Y', strtotime($od['beklenen_iade_tarihi'])); ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>

</body>
</html>