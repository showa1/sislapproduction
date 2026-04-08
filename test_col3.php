<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/config/web.php';
new yii\web\Application($config);

$db = Yii::$app->db;

ob_start();

echo "--- STATUS PASIEN ---\n";
print_r($db->createCommand("SELECT DISTINCT statuspasien FROM pendaftaran_t LIMIT 10")->queryAll());

echo "\n--- BED_M COUNT ---\n";
try {
    print_r($db->createCommand("SELECT COUNT(*) FROM bed_m")->queryScalar());
} catch (Exception $e) { echo $e->getMessage(); }

echo "\n--- KAMAR_M INFO ---\n";
try {
    print_r($db->createCommand("SELECT kamar_id, kamar_nama, kelaskamar_id FROM kamar_m LIMIT 1")->queryOne());
} catch (Exception $e) { echo $e->getMessage(); }

$out = ob_get_clean();
file_put_contents('db_test_out.txt', $out);
echo "DONE";
