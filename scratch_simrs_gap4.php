<?php
require __DIR__ . "/vendor/autoload.php";
require __DIR__ . "/vendor/yiisoft/yii2/Yii.php";
$config = require __DIR__ . "/config/web.php";
new yii\web\Application($config);
$db = Yii::$app->db;
$df = "2026-07-01"; $dt = "2026-07-08";

try {
    $q = $db->createCommand("
        SELECT COUNT(pmp.pasienmasukpenunjang_id) 
        FROM pasienmasukpenunjang_t pmp
        JOIN ruangan_m rm ON rm.ruangan_id = pmp.ruangan_id
        JOIN pendaftaran_t p ON p.pendaftaran_id = pmp.pendaftaran_id
        LEFT JOIN tindakanpelayanan_t t ON t.pasienmasukpenunjang_id = pmp.pasienmasukpenunjang_id
        WHERE pmp.tglmasukpenunjang::date BETWEEN :df AND :dt
          AND rm.instalasi_id IN (5, 6)
          AND p.pasienbatalperiksa_id IS NULL
          AND t.tindakanpelayanan_id IS NOT NULL
    ", [":df"=>$df,":dt"=>$dt])->queryScalar();
    echo "Has tindakanpelayanan_t: {$q}\n";

    $q2 = $db->createCommand("
        SELECT COUNT(DISTINCT pmp.pasienmasukpenunjang_id) 
        FROM pasienmasukpenunjang_t pmp
        JOIN ruangan_m rm ON rm.ruangan_id = pmp.ruangan_id
        JOIN pendaftaran_t p ON p.pendaftaran_id = pmp.pendaftaran_id
        JOIN tindakanpelayanan_t t ON t.pasienmasukpenunjang_id = pmp.pasienmasukpenunjang_id
        WHERE pmp.tglmasukpenunjang::date BETWEEN :df AND :dt
          AND rm.instalasi_id IN (5, 6)
          AND p.pasienbatalperiksa_id IS NULL
    ", [":df"=>$df,":dt"=>$dt])->queryScalar();
    echo "DISTINCT Has tindakanpelayanan_t: {$q2}\n";
    
    // Check if the query is grouped by pendaftaran_id maybe?
    $q3 = $db->createCommand("
        SELECT COUNT(DISTINCT p.pendaftaran_id) 
        FROM pasienmasukpenunjang_t pmp
        JOIN ruangan_m rm ON rm.ruangan_id = pmp.ruangan_id
        JOIN pendaftaran_t p ON p.pendaftaran_id = pmp.pendaftaran_id
        WHERE pmp.tglmasukpenunjang::date BETWEEN :df AND :dt
          AND rm.instalasi_id IN (5, 6)
          AND p.pasienbatalperiksa_id IS NULL
    ", [":df"=>$df,":dt"=>$dt])->queryScalar();
    echo "DISTINCT Pendaftaran: {$q3}\n";
    
} catch(Exception $e) { echo $e->getMessage(); }
