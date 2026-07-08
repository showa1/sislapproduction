<?php
require __DIR__ . "/vendor/autoload.php";
require __DIR__ . "/vendor/yiisoft/yii2/Yii.php";
$config = require __DIR__ . "/config/web.php";
new yii\web\Application($config);

$db = Yii::$app->db;

// 1. Cek apakah ada data Juli di tabel utama
$juliCount = $db->createCommand("
    SELECT COUNT(*) FROM pendaftaran_t 
    WHERE tgl_pendaftaran::date BETWEEN '2026-07-01' AND '2026-07-08'
    AND pasienbatalperiksa_id IS NULL
")->queryScalar();
echo "Pendaftaran Juli 1-8: {$juliCount}\n";

// 2. Cek mapping instalasi -> unit_bisnis
$instalasi = $db->createCommand("
    SELECT instalasi_id, instalasi_nama FROM instalasi_m ORDER BY instalasi_id
")->queryAll();
echo "\n--- Instalasi ---\n";
foreach ($instalasi as $r) echo "  [{$r['instalasi_id']}] {$r['instalasi_nama']}\n";

// 3. Cek cara_bayar yang tersedia
$caraBayar = $db->createCommand("
    SELECT cb.carabayar_id, cb.carabayar_nama, COUNT(p.pendaftaran_id) as jml
    FROM carabayar_m cb
    LEFT JOIN pendaftaran_t p ON p.carabayar_id = cb.carabayar_id
        AND p.tgl_pendaftaran::date >= '2026-07-01'
    GROUP BY cb.carabayar_id, cb.carabayar_nama
    ORDER BY jml DESC
    LIMIT 15
")->queryAll();
echo "\n--- Cara Bayar (Juli) ---\n";
foreach ($caraBayar as $r) echo "  [{$r['carabayar_id']}] {$r['carabayar_nama']} => {$r['jml']}\n";
