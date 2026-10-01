<?php
require_once 'c:\Moodle404\server\moodle\yeni_kutuphane\db_baglanti.php';

$output = [];
$stmt = $db->query("SHOW COLUMNS FROM katalog");
$output['katalog'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $db->query("SHOW COLUMNS FROM ogrenci");
$output['ogrenci'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

file_put_contents('c:\Moodle404\server\moodle\yeni_kutuphane\schema.json', json_encode($output, JSON_PRETTY_PRINT));
