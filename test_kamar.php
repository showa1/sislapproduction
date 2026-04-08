<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/config/web.php';
new yii\web\Application($config);

$db = Yii::$app->db;

ob_start();

echo "--- KAMAR_A_R INFO ---\n";
try {
    print_r($db->createCommand("SELECT SUM(kapasitas) as total_bed, SUM(tersedia) as bed_kosong FROM kamaraplicare_r")->queryOne());
} catch (Exception $e) { echo $e->getMessage(); }

$out = ob_get_clean();
file_put_contents('db_test_out2.txt', $out);
echo "DONE";
