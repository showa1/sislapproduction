<?php
require __DIR__ . "/vendor/autoload.php";
require __DIR__ . "/vendor/yiisoft/yii2/Yii.php";
$config = require __DIR__ . "/config/web.php";
new yii\web\Application($config);
$db = Yii::$app->db;
$df = "2026-07-01"; $dt = "2026-07-08";

try {
    $q = $db->createCommand("
        SELECT COUNT(DISTINCT pmp.pasienmasukpenunjang_id) 
        FROM pasienmasukpenunjang_t pmp
        JOIN ruangan_m rm ON rm.ruangan_id = pmp.ruangan_id
        JOIN pendaftaran_t p ON p.pendaftaran_id = pmp.pendaftaran_id
        JOIN hasilpemeriksaanlab_t hl ON hl.pasienmasukpenunjang_id = pmp.pasienmasukpenunjang_id
        WHERE pmp.tglmasukpenunjang::date BETWEEN :df AND :dt
          AND rm.instalasi_id = 5
          AND p.pasienbatalperiksa_id IS NULL
    ", [":df"=>$df,":dt"=>$dt])->queryScalar();
    echo "Has hasilpemeriksaanlab_t (Lab): {$q}\n";

    $q2 = $db->createCommand("
        SELECT COUNT(DISTINCT pmp.pasienmasukpenunjang_id) 
        FROM pasienmasukpenunjang_t pmp
        JOIN ruangan_m rm ON rm.ruangan_id = pmp.ruangan_id
        JOIN pendaftaran_t p ON p.pendaftaran_id = pmp.pendaftaran_id
        JOIN hasilpemeriksaanrad_t hr ON hr.pasienmasukpenunjang_id = pmp.pasienmasukpenunjang_id
        WHERE pmp.tglmasukpenunjang::date BETWEEN :df AND :dt
          AND rm.instalasi_id = 6
          AND p.pasienbatalperiksa_id IS NULL
    ", [":df"=>$df,":dt"=>$dt])->queryScalar();
    echo "Has hasilpemeriksaanrad_t (Rad): {$q2}\n";

    // Coba hitung distinct no_pendaftaran dengan INNER JOIN closing kasir
    $q3 = $db->createCommand("
        SELECT COUNT(DISTINCT pmp.pasienmasukpenunjang_id) 
        FROM pasienmasukpenunjang_t pmp
        JOIN ruangan_m rm ON rm.ruangan_id = pmp.ruangan_id
        JOIN pendaftaran_t p ON p.pendaftaran_id = pmp.pendaftaran_id
        JOIN pembayaranpelayanan_t pp ON pp.pendaftaran_id = p.pendaftaran_id
        JOIN tandabuktibayar_t tbb ON tbb.pembayaranpelayanan_id = pp.pembayaranpelayanan_id
        JOIN closingkasir_t ck ON ck.closingkasir_id = tbb.closingkasir_id
        WHERE pmp.tglmasukpenunjang::date BETWEEN :df AND :dt
          AND rm.instalasi_id IN (5, 6)
          AND p.pasienbatalperiksa_id IS NULL
    ", [":df"=>$df,":dt"=>$dt])->queryScalar();
    echo "Has closingkasir_t: {$q3}\n";
    
} catch(Exception $e) { echo $e->getMessage(); }
