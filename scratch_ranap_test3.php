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
} catch(Exception $e) { echo $e->getMessage() . "\n"; }
