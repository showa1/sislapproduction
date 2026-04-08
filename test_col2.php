<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/config/web.php';
new yii\web\Application($config);

$db = Yii::$app->db;

print_r($db->createCommand("SELECT DISTINCT statuspasien FROM pendaftaran_t LIMIT 10")->queryAll());
print_r($db->createCommand("SELECT tindakanpelayanan_id, daftartindakan_id FROM tindakanpelayanan_t LIMIT 1")->queryAll());
print_r($db->createCommand("SELECT COUNT(*) FROM bed_m")->queryScalar());
