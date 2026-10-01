<?php
session_start();
// Eğer admin zaten giriş yapmışsa direkt panele at
if (isset($_SESSION['admin_giris'])) { header("Location: admin_paneli.php"); exit; }

$hata = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kullanici = $_POST['kullanici'];
    $sifre = $_POST['sifre'];

    // Proje için sabit admin bilgileri
    if ($kullanici === "admin" && $sifre === "mudanya123") {
        $_SESSION['admin_giris'] = true;
        header("Location: admin_paneli.php");
        exit;
    } else {
        $hata = "Yönetici bilgileri hatalı!";
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Admin Girişi | Mudanya Kütüphane</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Montserrat', sans-serif; background: #1a1a1a; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-box { background: #fff; padding: 40px; border-radius: 10px; width: 320px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); border-top: 5px solid #dc2626; }
        h2 { color: #dc2626; text-align: center; margin-bottom: 25px; }
        input { width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background: #dc2626; color: #fff; border: none; border-radius: 5px; font-weight: bold; cursor: pointer; }
        .error { color: #dc2626; font-size: 13px; margin-bottom: 15px; text-align: center; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>Admin Girişi</h2>
        <?php if($hata) echo "<div class='error'>$hata</div>"; ?>
        <form method="POST">
            <input type="text" name="kullanici" placeholder="Yönetici Adı" required>
            <input type="password" name="sifre" placeholder="Şifre" required>
            <button type="submit">Sisteme Eriş</button>
        </form>
    </div>
</body>
</html>