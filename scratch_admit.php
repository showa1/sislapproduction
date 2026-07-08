<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';
$config = require __DIR__ . '/config/web.php';
(new yii\web\Application($config));
$db = Yii::$app->db;
$res = $db->createCommand("SELECT table_name FROM information_schema.tables WHERE table_schema='public' AND (table_name LIKE '%admisi%' OR table_name LIKE '%inap%')")->queryAll();
print_r($res);
