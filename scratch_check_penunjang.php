<?php
require __DIR__ . "/vendor/autoload.php";
require __DIR__ . "/vendor/yiisoft/yii2/Yii.php";
$config = require __DIR__ . "/config/web.php";
new yii\web\Application($config);
$db = Yii::$app->db;
$df = "2026-07-01"; $dt = "2026-07-08";

echo "=== Cek Kunjungan Penunjang (Lab & Rad) ===\n";

// 1. Cek tabel pasienmasukpenunjang_t
try {
    $count = $db->createCommand("
        SELECT COUNT(pmp.pasienmasukpenunjang_id) 
        FROM pasienmasukpenunjang_t pmp
        JOIN ruangan_m rm ON rm.ruangan_id = pmp.ruangan_id
        WHERE pmp.tglmasukpenunjang::date BETWEEN :df AND :dt
          AND rm.instalasi_id IN (5, 6)
    ", [":df"=>$df,":dt"=>$dt])->queryScalar();
    echo "Total dari pasienmasukpenunjang_t (Instalasi 5, 6): {$count}\n";
} catch(Exception $e) { echo "Tabel pasienmasukpenunjang_t error: " . $e->getMessage() . "\n"; }

// 2. Cek kolom di pasienmasukpenunjang_t
try {
    $cols = $db->createCommand("SELECT column_name FROM information_schema.columns WHERE table_name='pasienmasukpenunjang_t' ORDER BY column_name")->queryColumn();
    echo "\nKolom pasienmasukpenunjang_t:\n" . implode(", ", $cols) . "\n";
} catch(Exception $e) {}

// 3. Lihat contoh datanya
try {
    $contoh = $db->createCommand("
        SELECT pmp.no_masukpenunjang, pmp.tglmasukpenunjang, rm.ruangan_nama, pmp.pembayaranpelayanan_id
        FROM pasienmasukpenunjang_t pmp
        JOIN ruangan_m rm ON rm.ruangan_id = pmp.ruangan_id
        WHERE rm.instalasi_id IN (5, 6)
        ORDER BY pmp.tglmasukpenunjang DESC LIMIT 5
    ")->queryAll();
    echo "\nContoh Data Penunjang:\n";
    foreach($contoh as $c) echo "  {$c['no_masukpenunjang']} | {$c['tglmasukpenunjang']} | {$c['ruangan_nama']} | Bayar ID: {$c['pembayaranpelayanan_id']}\n";
} catch(Exception $e) {}

