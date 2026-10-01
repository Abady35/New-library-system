<?php
session_start();
// Kullanıcının giriş yapıp yapmadığını kontrol et
$giris_yapildi = isset($_SESSION['ogrenci_id']);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mudanya Üniversitesi | Kütüphane Bilgi Sistemi</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --mudanya-lacivert: #002B5C; 
            --mudanya-mavi: #0056A4;     
            --mudanya-sari: #FDB913;     
            --arkaplan: #F5F7FA;
            --yazi-koyu: #333333;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Montserrat', sans-serif; }
        body { background-color: var(--arkaplan); color: var(--yazi-koyu); }

        header { background-color: #ffffff; box-shadow: 0 2px 10px rgba(0,0,0,0.1); padding: 15px 50px; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 100; }
        .logo-container { display: flex; align-items: center; gap: 15px; }
        .logo-container img { height: 60px; }
        .logo-text h1 { color: var(--mudanya-lacivert); font-size: 20px; font-weight: 700; }
        .logo-text p { color: var(--mudanya-mavi); font-size: 12px; font-weight: 500; letter-spacing: 1px; }
        nav ul { list-style: none; display: flex; gap: 25px; align-items: center;}
        nav ul li a { text-decoration: none; color: var(--mudanya-lacivert); font-weight: 600; font-size: 14px; transition: 0.3s; }
        nav ul li a:hover { color: var(--mudanya-sari); }
        
        /* GERÇEKÇİ AÇILIR MENÜ (DROPDOWN) TASARIMI */
        .user-menu { position: relative; display: inline-block; padding: 10px 0; }
        .user-badge { background: #f0f5fa; padding: 10px 20px; border-radius: 30px; border: 1px solid var(--mudanya-mavi); cursor: pointer; color: var(--mudanya-lacivert); font-weight: bold; transition: 0.3s; }
        .user-badge:hover { background: var(--mudanya-mavi); color: white; }
        
        .user-dropdown { display: none; position: absolute; right: 0; top: 50px; background-color: #ffffff; min-width: 200px; box-shadow: 0px 10px 20px rgba(0,0,0,0.1); border-radius: 8px; overflow: hidden; border-top: 4px solid var(--mudanya-sari); z-index: 1; }
        .user-menu:hover .user-dropdown { display: block; } /* Üzerine gelince açılır */
        
        .user-dropdown a { display: block; padding: 15px 20px; text-decoration: none; color: var(--yazi-koyu) !important; border-bottom: 1px solid #f1f1f1; font-weight: 500; }
        .user-dropdown a:hover { background-color: #f9fafb; color: var(--mudanya-mavi) !important; padding-left: 25px; } /* Hover efekti */

        .hero { background: linear-gradient(rgba(0, 43, 92, 0.85), rgba(0, 86, 164, 0.85)), url('https://images.unsplash.com/photo-1507842217343-583bb7270b66?q=80&w=2000&auto=format&fit=crop') center/cover; height: 450px; display: flex; flex-direction: column; justify-content: center; align-items: center; color: white; text-align: center; padding: 0 20px; }
        .hero h2 { font-size: 36px; margin-bottom: 20px; font-weight: 700; }
        .hero p { font-size: 18px; margin-bottom: 40px; max-width: 600px; }
        
        .search-container { width: 100%; max-width: 800px; background: white; border-radius: 50px; padding: 10px 20px; display: flex; align-items: center; box-shadow: 0 10px 25px rgba(0,0,0,0.2); }
        .search-container input { flex: 1; border: none; outline: none; padding: 15px; font-size: 16px; color: var(--yazi-koyu); }
        .search-container button { background-color: var(--mudanya-sari); color: var(--mudanya-lacivert); border: none; padding: 15px 30px; border-radius: 30px; font-size: 16px; font-weight: 700; cursor: pointer; transition: 0.3s; }
        
        .content-section { padding: 60px 50px; max-width: 1200px; margin: 0 auto; }
        .section-title { color: var(--mudanya-lacivert); font-size: 28px; border-bottom: 3px solid var(--mudanya-sari); padding-bottom: 10px; margin-bottom: 30px; display: inline-block; }
        
        .ai-recommendation-box { background: white; border-left: 5px solid var(--mudanya-mavi); padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 40px; }
        .btn-ai { background: var(--mudanya-mavi); color: white; border: none; padding: 12px 25px; border-radius: 5px; cursor: pointer; font-weight: bold; font-size: 15px; transition: 0.3s; }
        .btn-ai:hover { background: var(--mudanya-lacivert); }

        #sonucAlani { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); margin-top: 20px; display: none; border-top: 4px solid var(--mudanya-sari); }
        .loading { text-align: center; color: var(--mudanya-mavi); font-weight: bold; display: none; margin: 20px 0;}
        .alert { padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; font-weight: 500; }
        .alert-hata { background-color: #fee2e2; border-left: 4px solid #ef4444; color: #991b1b; }
        .alert-bilgi { background-color: #e0f2fe; border-left: 4px solid var(--mudanya-mavi); color: #075985; }

        .book-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin-top: 20px; }
        .book-card { background: #fff; border: 1px solid #eaeaea; border-radius: 10px; padding: 20px; display: flex; gap: 15px; transition: transform 0.2s; }
        .book-icon { background: var(--arkaplan); color: var(--mudanya-mavi); width: 60px; height: 70px; border-radius: 5px; display: flex; align-items: center; justify-content: center; font-size: 28px; }
        .book-details h4 { color: var(--mudanya-lacivert); font-size: 16px; margin-bottom: 8px; }
        .book-details .author { color: #666; font-size: 13px; margin-bottom: 12px; }
        .category-badge { background: #f0f5fa; color: var(--mudanya-mavi); padding: 5px 12px; border-radius: 20px; font-size: 11px; font-weight: 600; text-transform: uppercase; }

        footer { background-color: var(--mudanya-lacivert); color: white; text-align: center; padding: 30px 20px; margin-top: 50px; font-size: 14px; }
    </style>
</head>
<body>

    <header>
        <div class="logo-container">
            <img src="#" alt="Mudanya Logosu">
            <div class="logo-text">
                <h1>MUDANYA ÜNİVERSİTESİ</h1>
                <p>KÜTÜPHANE VE DOKÜMANTASYON</p>
            </div>
        </div>
        <nav>
            <ul>
                <li><a href="index.php"><i class="fas fa-home"></i> Anasayfa</a></li>
                <li><a href="#"><i class="fas fa-search"></i> Katalog Tarama</a></li>
                
                <?php if($giris_yapildi): ?>
                    <!-- KULLANICI GİRİŞ YAPTIYSA GÖRÜNECEK MODERN AÇILIR MENÜ -->
                    <li class="user-menu">
                        <div class="user-badge">
                            <i class="fas fa-user-circle"></i> <?php echo $_SESSION['ad_soyad']; ?> <i class="fas fa-chevron-down" style="font-size: 12px; margin-left: 5px;"></i>
                        </div>
                        <div class="user-dropdown">
                            <a href="ogrenci_paneli.php"><i class="fas fa-id-badge"></i> Profilim / Üzerimdekiler</a>
                            <a href="cikis.php" style="color: #dc2626 !important;"><i class="fas fa-sign-out-alt"></i> Güvenli Çıkış Yap</a>
                        </div>
                    </li>
                <?php else: ?>
                    <!-- KULLANICI GİRİŞ YAPMADIYSA GÖRÜNECEK BUTON -->
                    <li><a href="giris.php" style="color: var(--mudanya-sari); font-weight: bold; padding: 10px 20px; border: 2px solid var(--mudanya-sari); border-radius: 30px;"><i class="fas fa-user-graduate"></i> Sisteme Giriş</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <section class="hero">
        <?php if($giris_yapildi): ?>
            <h2>Tekrar Hoş Geldin, <?php echo $_SESSION['ad_soyad']; ?>!</h2>
            <p>Bölümüne (<?php echo $_SESSION['bolum']; ?>) özel kaynakları ve tüm kütüphane arşivini hemen keşfet.</p>
        <?php else: ?>
            <h2>Kütüphane Koleksiyonunda Keşfe Çıkın</h2>
            <p>Akıllı arama motorumuz ile binlerce basılı ve elektronik kaynağa saniyeler içinde ulaşın.</p>
        <?php endif; ?>
        
        <div class="search-container">
            <i class="fas fa-search" style="color: #999; margin-left: 10px; font-size: 20px;"></i>
            <input type="text" id="aramaKutusu" placeholder="Kitap adı veya yazar yazın...">
            <button onclick="aramaYap()">Katalogda Ara</button>
        </div>
    </section>

    <section class="content-section">
        
        <div class="ai-recommendation-box">
            <h3 class="section-title" style="font-size: 20px; margin-bottom:15px; border:none;"><i class="fas fa-robot"></i> Size Özel Yapay Zeka Önerileri</h3>
            
            <?php if($giris_yapildi): ?>
                <p style="margin-bottom: 20px; color: #555;">Sistemimiz, <strong><?php echo $_SESSION['bolum']; ?></strong> müfredatını ve okuma geçmişinizi analiz ederek size en uygun kaynakları hazırladı.</p>
                <input type="hidden" id="ogrenciNo" value="<?php echo $_SESSION['ogrenci_id']; ?>">
                <button class="btn-ai" onclick="oneriGetir()">
                    Bana Özel Önerileri Getir <i class="fas fa-magic"></i>
                </button>
            <?php else: ?>
                <p style="margin-bottom: 20px; color: #555;">Bölümünüze ve ilgi alanlarınıza yönelik kişiselleştirilmiş kitap önerileri almak için lütfen sisteme giriş yapın.</p>
                <a href="giris.php"><button class="btn-ai" style="background-color: var(--mudanya-sari); color: var(--mudanya-lacivert);">Sisteme Giriş Yap</button></a>
            <?php endif; ?>
        </div>

        <div id="yukleniyor" class="loading"><i class="fas fa-spinner fa-spin fa-2x"></i> Algoritmalar çalışıyor...</div>

        <div id="sonucAlani">
            <h3 style="color: var(--mudanya-lacivert); margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom:10px;"><i class="fas fa-list-ul"></i> Sonuçlar</h3>
            <div id="sonucMetni"></div>
        </div>

    </section>

    <footer>
        <p>© 2026 Mudanya Üniversitesi Kütüphane Bilgi Sistemi</p>
        <p>Sistem Analizi Projesi | Geliştiriciler: Abady & Engincan Hümaz</p>
    </footer>

    <!-- (JavaScript KODLARI BURADA AYNI ŞEKİLDE KALACAK, DEĞİŞTİRMEYİN) -->
    <script>
        function jsonToHtml(data) {
            let html = "";
            if (data.durum === "hata") return `<div class="alert alert-hata"><i class="fas fa-exclamation-triangle"></i> ${data.mesaj}</div>`;
            if (data.mesaj) html += `<div class="alert alert-bilgi"><i class="fas fa-info-circle"></i> ${data.mesaj}</div>`;

            let kitapListesi = data.oneri_listesi || data.sonuclar || [];
            if (kitapListesi.length > 0) {
                html += `<div class="book-grid">`;
                kitapListesi.forEach(kitap => {
                    let baslik = kitap.baslik || kitap.kitap_adi || "Bilinmeyen Başlık";
                    let yazar = kitap.yazar || "Bilinmeyen Yazar";
                    let kategori = kitap.kategori || "Mühendislik & Yazılım";

                    html += `
                    <div class="book-card">
                        <div class="book-icon"><i class="fas fa-book"></i></div>
                        <div class="book-details">
                            <h4>${baslik}</h4>
                            <p class="author"><i class="fas fa-user-edit"></i> ${yazar}</p>
                            <span class="category-badge">${kategori}</span>
                        </div>
                    </div>`;
                });
                html += `</div>`;
            }
            return html;
        }

        function sonucuGoster(htmlIcerik) {
            document.getElementById('yukleniyor').style.display = 'none';
            document.getElementById('sonucAlani').style.display = 'block';
            document.getElementById('sonucMetni').innerHTML = htmlIcerik;
            document.getElementById('sonucAlani').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        function aramaYap() {
            let kelime = document.getElementById('aramaKutusu').value;
            if(kelime.trim() === '') { alert("Lütfen arama kutusunu doldurun."); return; }

            document.getElementById('sonucAlani').style.display = 'none';
            document.getElementById('yukleniyor').style.display = 'block';

            fetch(`akilli_arama.php?arama_sorgusu=${encodeURIComponent(kelime)}`)
                .then(response => response.text())
                .then(text => {
                    try {
                        let jsonData = JSON.parse(text);
                        sonucuGoster(jsonToHtml(jsonData));
                    } catch(e) {
                        sonucuGoster(`<div class="alert alert-hata">Sunucu Hatası: ${text}</div>`);
                    }
                });
        }

        function oneriGetir() {
            let ogrId = document.getElementById('ogrenciNo').value;

            document.getElementById('sonucAlani').style.display = 'none';
            document.getElementById('yukleniyor').style.display = 'block';

            fetch(`yapay_zeka_oneri.php?ogrenci_id=${encodeURIComponent(ogrId)}`)
                .then(response => response.text())
                .then(text => {
                    try {
                        let jsonData = JSON.parse(text);
                        sonucuGoster(jsonToHtml(jsonData));
                    } catch(e) {
                        sonucuGoster(`<div class="alert alert-hata">Sunucu Hatası: ${text}</div>`);
                    }
                });
        }
        
        // PHP'den giriş durumunu alıp JavaScript'e aktarıyoruz
        const oturumAcikMi = <?php echo $giris_yapildi ? 'true' : 'false'; ?>;

        function jsonToHtml(data) {
            let html = "";
            if (data.durum === "hata") return `<div class="alert alert-hata"><i class="fas fa-exclamation-triangle"></i> ${data.mesaj}</div>`;
            if (data.mesaj) html += `<div class="alert alert-bilgi"><i class="fas fa-info-circle"></i> ${data.mesaj}</div>`;

            let kitapListesi = data.oneri_listesi || data.sonuclar || [];
            if (kitapListesi.length > 0) {
                html += `<div class="book-grid">`;
                kitapListesi.forEach(kitap => {
                    let baslik = kitap.baslik || "Bilinmeyen Başlık";
                    let yazar = kitap.yazar || "-";
                    let kategori = kitap.kategori || "Genel";
                    
                    // Giriş durumuna göre buton veya uyarı oluştur
                    let islemButonu = oturumAcikMi ? 
                        `<button onclick="oduncAl(${kitap.id})" style="margin-top:15px; background:var(--mudanya-sari); color:var(--mudanya-lacivert); border:none; padding:8px 15px; border-radius:5px; cursor:pointer; font-weight:bold; width:100%;"><i class="fas fa-hand-holding"></i> Hemen Ödünç Al</button>` : 
                        `<p style="font-size:12px; color:#ef4444; margin-top:15px; font-weight:bold;"><i class="fas fa-lock"></i> Ödünç almak için giriş yapın</p>`;

                    html += `
                    <div class="book-card" style="flex-direction:column;">
                        <div style="display:flex; gap:15px;">
                            <div class="book-icon"><i class="fas fa-book"></i></div>
                            <div class="book-details">
                                <h4>${baslik}</h4>
                                <p class="author"><i class="fas fa-user-edit"></i> ${yazar}</p>
                                <span class="category-badge">${kategori}</span>
                            </div>
                        </div>
                        ${islemButonu}
                    </div>`;
                });
                html += `</div>`;
            }
            return html;
        }

        // Ödünç Alma Fonksiyonu (AJAX - Asenkron)
        function oduncAl(kitapId) {
            let formData = new FormData();
            formData.append('kitap_id', kitapId);

            fetch('odunc_al.php', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                if(data.durum === 'basarili') {
                    alert("✅ BAŞARILI: " + data.mesaj);
                } else {
                    alert("❌ HATA: " + data.mesaj);
                }
            })
            .catch(error => alert("Sunucu ile bağlantı kurulamadı!"));
        }

        function sonucuGoster(htmlIcerik) {
            document.getElementById('yukleniyor').style.display = 'none';
            document.getElementById('sonucAlani').style.display = 'block';
            document.getElementById('sonucMetni').innerHTML = htmlIcerik;
        }

        function aramaYap() {
            let kelime = document.getElementById('aramaKutusu').value;
            if(kelime.trim() === '') { alert("Lütfen arama kutusunu doldurun."); return; }
            document.getElementById('sonucAlani').style.display = 'none';
            document.getElementById('yukleniyor').style.display = 'block';
            fetch(`akilli_arama.php?arama_sorgusu=${encodeURIComponent(kelime)}`)
                .then(r => r.json()).then(data => sonucuGoster(jsonToHtml(data)));
        }

        function oneriGetir() {
            let ogrId = document.getElementById('ogrenciNo').value;
            document.getElementById('sonucAlani').style.display = 'none';
            document.getElementById('yukleniyor').style.display = 'block';
            fetch(`yapay_zeka_oneri.php?ogrenci_id=${encodeURIComponent(ogrId)}`)
                .then(r => r.json()).then(data => sonucuGoster(jsonToHtml(data)));
        }
    
    </script>
</body>
</html>