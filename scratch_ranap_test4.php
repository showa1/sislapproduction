<?php
require __DIR__ . "/vendor/autoload.php";
require __DIR__ . "/vendor/yiisoft/yii2/Yii.php";
$config = require __DIR__ . "/config/web.php";
new yii\web\Application($config);
$db = Yii::$app->db;
$df = "2026-07-01"; $dt = "2026-07-08";

try {
    $count = $db->createCommand("
        SELECT COUNT(pa.pasienadmisi_id)
        FROM pasienadmisi_t pa
        JOIN ruangan_m rm ON rm.ruangan_id = pa.ruangan_id
        WHERE pa.tgladmisi::date BETWEEN :df AND :dt
          AND rm.instalasi_id IN (4, 38)
    ", [":df"=>$df,":dt"=>$dt])->queryScalar();
    echo "Admisi ke Ranap (Instalasi 4, 38) : {$count}\n";

    $closing = (int)$db->createCommand("
        SELECT COUNT(pa.pasienadmisi_id)
        FROM pasienadmisi_t pa
        JOIN ruangan_m rm ON rm.ruangan_id = pa.ruangan_id
        WHERE pa.tgladmisi::date BETWEEN :df AND :dt
          AND rm.instalasi_id IN (4, 38)
          AND pa.pembayaranpelayanan_id IS NOT NULL
    ", [":df"=>$df,":dt"=>$dt])->queryScalar();
    echo "Admisi yg Sudah Bayar : {$closing}\n";

    $penjamin = $db->createCommand("
        SELECT CASE
            WHEN UPPER(cb.carabayar_nama) LIKE '%BPJS KES%'  THEN 'BPJS Kesehatan'
            WHEN UPPER(cb.carabayar_nama) LIKE '%JASA RAH%'  THEN 'Jasa Raharja'
            WHEN UPPER(cb.carabayar_nama) LIKE '%ASURANSI%'  THEN 'Asuransi Komersial'
            ELSE 'Umum'
        END AS cara_bayar, COUNT(pa.pasienadmisi_id) as n
        FROM pasienadmisi_t pa
        JOIN ruangan_m rm ON rm.ruangan_id = pa.ruangan_id
        LEFT JOIN carabayar_m cb ON cb.carabayar_id = pa.carabayar_id
        WHERE pa.tgladmisi::date BETWEEN :df AND :dt
          AND rm.instalasi_id IN (4, 38)
        GROUP BY cara_bayar
    ", [":df"=>$df,":dt"=>$dt])->queryAll();
    
    echo "\nPenjamin:\n";
    foreach($penjamin as $r) echo "  {$r['cara_bayar']}: {$r['n']}\n";

} catch(Exception $e) { echo $e->getMessage() . "\n"; }
