<?php
require __DIR__ . "/vendor/autoload.php";
require __DIR__ . "/vendor/yiisoft/yii2/Yii.php";
$config = require __DIR__ . "/config/web.php";
new yii\web\Application($config);
$db = Yii::$app->db;
$df = "2026-07-01"; $dt = "2026-07-08";

try {
    // Cari query yang menghasilkan 35 di pasienmasukpenunjang_t
    // Coba hitung berdasarkan p.tgl_pendaftaran
    $q1 = $db->createCommand("
        SELECT COUNT(pmp.pasienmasukpenunjang_id) 
        FROM pasienmasukpenunjang_t pmp
        JOIN pendaftaran_t p ON p.pendaftaran_id = pmp.pendaftaran_id
        JOIN ruangan_m rm ON rm.ruangan_id = pmp.ruangan_id
        WHERE p.tgl_pendaftaran::date BETWEEN :df AND :dt
          AND p.pasienbatalperiksa_id IS NULL
          AND rm.instalasi_id IN (5, 6)
    ", [":df"=>$df,":dt"=>$dt])->queryScalar();
    echo "Tgl Pendaftaran (1-8 Jul): {$q1}\n";
    
    // Coba hitung distinct no_rekam_medik
    $q2 = $db->createCommand("
        SELECT COUNT(DISTINCT p.no_rekam_medik) 
        FROM pasienmasukpenunjang_t pmp
        JOIN pendaftaran_t p ON p.pendaftaran_id = pmp.pendaftaran_id
        JOIN ruangan_m rm ON rm.ruangan_id = pmp.ruangan_id
        WHERE pmp.tglmasukpenunjang::date BETWEEN :df AND :dt
          AND p.pasienbatalperiksa_id IS NULL
          AND rm.instalasi_id IN (5, 6)
    ", [":df"=>$df,":dt"=>$dt])->queryScalar();
    echo "Distinct No RM (Tgl Masuk 1-8 Jul): {$q2}\n";

    // Coba cari apakah SIMRS query dari tabel lain?
    // SIMRS mungkin menggunakan "tindakanpelayanan_t"
    $q3 = $db->createCommand("
        SELECT COUNT(DISTINCT tp.tindakanpelayanan_id)
        FROM tindakanpelayanan_t tp
        JOIN ruangan_m rm ON rm.ruangan_id = tp.ruangan_id
        JOIN pendaftaran_t p ON p.pendaftaran_id = tp.pendaftaran_id
        WHERE tp.tgl_tindakan::date BETWEEN :df AND :dt
          AND rm.instalasi_id IN (5, 6)
    ", [":df"=>$df,":dt"=>$dt])->queryScalar();
    echo "Tindakan Pelayanan (Tgl Tindakan 1-8 Jul): {$q3}\n";

    // Coba cari pasienmasukpenunjang_t dengan filter tgl pendaftaran dan tglmasukpenunjang
    // Tapi user filter nya "01 Jul 2026 - 08 Jul 2026". 

} catch(Exception $e) { echo $e->getMessage(); }
