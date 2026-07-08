<?php
require __DIR__ . "/vendor/autoload.php";
require __DIR__ . "/vendor/yiisoft/yii2/Yii.php";
$config = require __DIR__ . "/config/web.php";
new yii\web\Application($config);
$db = Yii::$app->db;
$df = "2026-07-01"; $dt = "2026-07-08";

try {
    $contoh = $db->createCommand("
        SELECT pmp.no_masukpenunjang, pmp.tglmasukpenunjang, rm.ruangan_nama, p.pembayaranpelayanan_id, cb.carabayar_nama
        FROM pasienmasukpenunjang_t pmp
        JOIN ruangan_m rm ON rm.ruangan_id = pmp.ruangan_id
        JOIN pendaftaran_t p ON p.pendaftaran_id = pmp.pendaftaran_id
        LEFT JOIN carabayar_m cb ON cb.carabayar_id = p.carabayar_id
        WHERE rm.instalasi_id IN (5, 6)
          AND pmp.tglmasukpenunjang::date BETWEEN :df AND :dt
        ORDER BY pmp.tglmasukpenunjang DESC LIMIT 5
    ", [":df"=>$df,":dt"=>$dt])->queryAll();
    
    echo "\nContoh Data Penunjang (Join Pendaftaran):\n";
    foreach($contoh as $c) echo "  {$c['no_masukpenunjang']} | {$c['tglmasukpenunjang']} | {$c['ruangan_nama']} | Bayar ID: {$c['pembayaranpelayanan_id']} | CB: {$c['carabayar_nama']}\n";
    
    $sudahBayar = $db->createCommand("
        SELECT COUNT(pmp.pasienmasukpenunjang_id)
        FROM pasienmasukpenunjang_t pmp
        JOIN ruangan_m rm ON rm.ruangan_id = pmp.ruangan_id
        JOIN pendaftaran_t p ON p.pendaftaran_id = pmp.pendaftaran_id
        WHERE rm.instalasi_id IN (5, 6)
          AND pmp.tglmasukpenunjang::date BETWEEN :df AND :dt
          AND p.pembayaranpelayanan_id IS NOT NULL
    ", [":df"=>$df,":dt"=>$dt])->queryScalar();
    echo "\nSudah bayar: {$sudahBayar}\n";
} catch(Exception $e) { echo $e->getMessage(); }
