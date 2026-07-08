<?php
require __DIR__ . "/vendor/autoload.php";
require __DIR__ . "/vendor/yiisoft/yii2/Yii.php";
$config = require __DIR__ . "/config/web.php";
new yii\web\Application($config);
$db = Yii::$app->db;
$df = "2026-07-01"; $dt = "2026-07-08";

try {
    // 1. Cek berapa banyak yg ada di tabel pasienmasukpenunjang_t (365/362)
    $countMasuk = $db->createCommand("
        SELECT COUNT(*)
        FROM pasienmasukpenunjang_t pmp
        JOIN ruangan_m rm ON rm.ruangan_id = pmp.ruangan_id
        WHERE pmp.tglmasukpenunjang::date BETWEEN :df AND :dt
          AND rm.instalasi_id IN (5, 6)
    ", [":df"=>$df,":dt"=>$dt])->queryScalar();
    echo "pasienmasukpenunjang_t (TglMasuk 1-8 Jul) = {$countMasuk}\n";

    // 2. Cek apakah ada kolom khusus di pasienperiksa_t atau hasilpemeriksaan_t yang membedakan 35 vs 362?
    // Cek kolom statusperiksa di pasienmasukpenunjang_t
    $status = $db->createCommand("
        SELECT pmp.statusperiksa, COUNT(*) as n
        FROM pasienmasukpenunjang_t pmp
        JOIN ruangan_m rm ON rm.ruangan_id = pmp.ruangan_id
        WHERE pmp.tglmasukpenunjang::date BETWEEN :df AND :dt
          AND rm.instalasi_id IN (5, 6)
        GROUP BY pmp.statusperiksa
    ", [":df"=>$df,":dt"=>$dt])->queryAll();
    
    echo "\nStatus Periksa di pasienmasukpenunjang_t:\n";
    foreach($status as $s) echo "  " . ($s['statusperiksa'] ?: 'NULL') . ": {$s['n']}\n";
    
    // 3. Cek hasilpemeriksaan_t atau yg serupa (karena laporannya bilang 'hasil' atau 'sudah di periksa')
    // Coba join dengan pendaftaran_t untuk melihat status lain
    $batal = $db->createCommand("
        SELECT p.pasienbatalperiksa_id, COUNT(*) as n
        FROM pasienmasukpenunjang_t pmp
        JOIN ruangan_m rm ON rm.ruangan_id = pmp.ruangan_id
        JOIN pendaftaran_t p ON p.pendaftaran_id = pmp.pendaftaran_id
        WHERE pmp.tglmasukpenunjang::date BETWEEN :df AND :dt
          AND rm.instalasi_id IN (5, 6)
        GROUP BY p.pasienbatalperiksa_id
    ", [":df"=>$df,":dt"=>$dt])->queryAll();
    
    echo "\nBatal Periksa (Join pendaftaran_t):\n";
    foreach($batal as $b) echo "  " . ($b['pasienbatalperiksa_id'] ?: 'TIDAK BATAL') . ": {$b['n']}\n";

} catch(Exception $e) { echo $e->getMessage(); }
