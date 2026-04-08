<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/config/web.php';
new yii\web\Application($config);

$db = Yii::$app->db;

print_r($db->getTableSchema('pendaftaran_t')->getColumnNames());
echo "\n====\n";
print_r($db->getTableSchema('pasienadmisi_t')->getColumnNames());
echo "\n====\n";
print_r($db->getTableSchema('tindakanpelayanan_t')->getColumnNames());
echo "\n====\n";
print_r($db->getTableSchema('kamar_m')->getColumnNames());
echo "\n====\n";
