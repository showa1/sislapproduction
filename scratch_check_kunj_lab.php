<?php
require __DIR__ . "/vendor/autoload.php";
require __DIR__ . "/vendor/yiisoft/yii2/Yii.php";
$config = require __DIR__ . "/config/web.php";
new yii\web\Application($config);
$db = Yii::$app->db;
$df = "2026-07-01"; $dt = "2026-07-08";

try {
    $c = $db->createCommand("
        SELECT COUNT(DISTINCT pp.pasienmasukpenunjang_id)
        FROM pasienmasukpenunjang_t pp
        JOIN pendaftaran_t pd ON pd.pendaftaran_id = pp.pendaftaran_id
        JOIN ruangan_m r_lab ON r_lab.ruangan_id = pp.ruangan_id
        WHERE r_lab.ruangan_nama ILIKE '%laborat%'
        AND pp.tglmasukpenunjang::date BETWEEN :df AND :dt
        AND pd.pasienbatalperiksa_id IS NULL
    ", [":df"=>$df,":dt"=>$dt])->queryScalar();
    echo "Total PasienMasukPenunjang (Lab): " . $c . "\n";
} catch(Exception $e) { echo $e->getMessage(); }
