<?php
require __DIR__ . "/vendor/autoload.php";
require __DIR__ . "/vendor/yiisoft/yii2/Yii.php";
$config = require __DIR__ . "/config/web.php";
new yii\web\Application($config);
$db = Yii::$app->db;
try {
    $c = $db->createCommand("
        SELECT tp.pasienmasukpenunjang_id, dt.daftartindakan_nama, tp.tarif_tindakan
        FROM tindakanpelayanan_t tp
        JOIN daftartindakan_m dt ON dt.daftartindakan_id = tp.daftartindakan_id
        WHERE tp.pasienmasukpenunjang_id = (
            SELECT tp2.pasienmasukpenunjang_id 
            FROM tindakanpelayanan_t tp2 
            GROUP BY tp2.pasienmasukpenunjang_id 
            HAVING SUM(tp2.tarif_tindakan) = 246000 
            LIMIT 1
        )
    ")->queryAll();
    print_r($c);
} catch(Exception $e) { echo $e->getMessage(); }
