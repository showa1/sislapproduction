<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';
$config = require __DIR__ . '/config/web.php';
(new yii\web\Application($config));
$db = Yii::$app->db;
$res = $db->createCommand("SELECT ruangan_id, ruangan_nama, instalasi_id FROM ruangan_m WHERE LOWER(ruangan_nama) LIKE '%mcu%' OR LOWER(ruangan_nama) LIKE '%check%' OR LOWER(ruangan_nama) LIKE '%medical%'")->queryAll();
print_r($res);
$res2 = $db->createCommand("SELECT instalasi_id, instalasi_nama FROM instalasi_m WHERE LOWER(instalasi_nama) LIKE '%mcu%' OR LOWER(instalasi_nama) LIKE '%medical%'")->queryAll();
print_r($res2);
