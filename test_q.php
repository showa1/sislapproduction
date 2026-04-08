<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/config/web.php';
new yii\web\Application($config);

$db = Yii::$app->db;

function getTop($sql) {
    global $db;
    try {
        return print_r($db->createCommand($sql)->queryOne(), true);
    } catch (\Exception $e) {
        return $e->getMessage();
    }
}

echo "PASIENADM_T: \n" . getTop("SELECT * FROM pasienadmisi_t LIMIT 1") . "\n\n";
echo "PENUNJANG_T: \n" . getTop("SELECT * FROM pasienmasukpenunjang_t LIMIT 1") . "\n\n";
echo "TINDAK_M: \n" . getTop("SELECT * FROM tindakanpelayanan_t LIMIT 1") . "\n\n";
echo "BED_M: \n" . getTop("SELECT * FROM bed_m LIMIT 1") . "\n\n";
echo "KAMAR_M: \n" . getTop("SELECT * FROM kamar_m LIMIT 1") . "\n\n";

