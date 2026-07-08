<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';
$config = require __DIR__ . '/config/web.php';
(new yii\web\Application($config));
$db = Yii::$app->db;
$res = $db->createCommand("SELECT instalasi_id, instalasi_nama FROM instalasi_m ORDER BY instalasi_nama")->queryAll();
print_r($res);
