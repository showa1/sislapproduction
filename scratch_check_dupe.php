<?php
require __DIR__ . "/vendor/autoload.php";
require __DIR__ . "/vendor/yiisoft/yii2/Yii.php";
$config = require __DIR__ . "/config/web.php";
new yii\web\Application($config);
$db = Yii::$app->db;

try {
    $c = $db->createCommand("
        SELECT daftartindakan_id, COUNT(*) 
        FROM pemeriksaanlab_m 
        GROUP BY daftartindakan_id 
        HAVING COUNT(*) > 1
    ")->queryAll();
    print_r($c);
} catch(Exception $e) { echo $e->getMessage(); }
