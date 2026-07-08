<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';
$config = require __DIR__ . '/config/web.php';
(new yii\web\Application($config));
$db = Yii::$app->db;
$res = $db->createCommand("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'pasienadmisi_t'")->queryAll();
print_r($res);
