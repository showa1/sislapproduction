<?php
require __DIR__ . "/vendor/autoload.php";
require __DIR__ . "/vendor/yiisoft/yii2/Yii.php";
$config = require __DIR__ . "/config/web.php";
new yii\web\Application($config);
$db = Yii::$app->db;
$df = "2026-07-01"; $dt = "2026-07-08";

try {
    $count = $db->createCommand("
        SELECT COUNT(p.pendaftaran_id) FROM pendaftaran_t p
        JOIN ruangan_m rm ON rm.ruangan_id = p.ruangan_id
        WHERE p.tgl_pendaftaran::date BETWEEN :df AND :dt
          AND p.pasienbatalperiksa_id IS NULL
          AND rm.instalasi_id IN (5, 6, 9, 72, 74, 10)
    ", [":df"=>$df,":dt"=>$dt])->queryScalar();
    echo "Total instalasi penunjang langsung: {$count}\n";

    $count56 = $db->createCommand("
        SELECT COUNT(p.pendaftaran_id) FROM pendaftaran_t p
        JOIN ruangan_m rm ON rm.ruangan_id = p.ruangan_id
        WHERE p.tgl_pendaftaran::date BETWEEN :df AND :dt
          AND p.pasienbatalperiksa_id IS NULL
          AND rm.instalasi_id IN (5, 6)
    ", [":df"=>$df,":dt"=>$dt])->queryScalar();
    echo "Total Instalasi 5 dan 6 (Lab, Rad): {$count56}\n";

    // Cek pasienmasukpenunjang_t yang tidak terkait pendaftaran lain?
    // Atau pasienmasukpenunjang_t dengan filter tgl_pendaftaran?
    
} catch(Exception $e) {}
