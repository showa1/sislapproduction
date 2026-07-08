<?php
require __DIR__ . "/vendor/autoload.php";
require __DIR__ . "/vendor/yiisoft/yii2/Yii.php";
$config = require __DIR__ . "/config/web.php";
new yii\web\Application($config);
$db = Yii::$app->db;
$df = "2026-07-01"; $dt = "2026-07-08";

try {
    // 1. Cek jumlah distinct pendaftaran_id untuk Lab & Rad
    $dist = $db->createCommand("
        SELECT COUNT(DISTINCT pmp.pendaftaran_id)
        FROM pasienmasukpenunjang_t pmp
        JOIN ruangan_m rm ON rm.ruangan_id = pmp.ruangan_id
        JOIN pendaftaran_t p ON p.pendaftaran_id = pmp.pendaftaran_id
        WHERE pmp.tglmasukpenunjang::date BETWEEN :df AND :dt
          AND rm.instalasi_id IN (5, 6)
          AND p.pasienbatalperiksa_id IS NULL
    ", [":df"=>$df,":dt"=>$dt])->queryScalar();
    echo "Distinct Pendaftaran (Lab & Rad gabungan): {$dist}\n";
    
    // Tapi jika KPI adalah penjumlahan Lab + Rad, 
    // jika 1 pasien masuk Lab dan Rad, apakah dihitung 2 (1 Lab, 1 Rad) atau 1?
    $distLab = $db->createCommand("
        SELECT COUNT(DISTINCT pmp.pendaftaran_id)
        FROM pasienmasukpenunjang_t pmp
        JOIN ruangan_m rm ON rm.ruangan_id = pmp.ruangan_id
        JOIN pendaftaran_t p ON p.pendaftaran_id = pmp.pendaftaran_id
        WHERE pmp.tglmasukpenunjang::date BETWEEN :df AND :dt
          AND rm.instalasi_id = 5
          AND p.pasienbatalperiksa_id IS NULL
    ", [":df"=>$df,":dt"=>$dt])->queryScalar();
    echo "Distinct Pendaftaran (Lab): {$distLab}\n";

    $distRad = $db->createCommand("
        SELECT COUNT(DISTINCT pmp.pendaftaran_id)
        FROM pasienmasukpenunjang_t pmp
        JOIN ruangan_m rm ON rm.ruangan_id = pmp.ruangan_id
        JOIN pendaftaran_t p ON p.pendaftaran_id = pmp.pendaftaran_id
        WHERE pmp.tglmasukpenunjang::date BETWEEN :df AND :dt
          AND rm.instalasi_id = 6
          AND p.pasienbatalperiksa_id IS NULL
    ", [":df"=>$df,":dt"=>$dt])->queryScalar();
    echo "Distinct Pendaftaran (Rad): {$distRad}\n";
    
} catch(Exception $e) { echo $e->getMessage(); }
