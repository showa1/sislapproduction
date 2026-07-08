<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';
$config = require __DIR__ . '/config/web.php';
(new yii\web\Application($config));
$db = Yii::$app->db;
$res = $db->createCommand("SELECT instalasi_id, instalasi_nama FROM instalasi_m WHERE LOWER(instalasi_nama) LIKE '%igd%' OR LOWER(instalasi_nama) LIKE '%gawat%' OR LOWER(instalasi_nama) LIKE '%inap%'")->queryAll();
print_r($res);
