<?php
require __DIR__ . "/vendor/autoload.php";
require __DIR__ . "/vendor/yiisoft/yii2/Yii.php";
$config = require __DIR__ . "/config/web.php";
new yii\web\Application($config);

$pgDb = Yii::$app->db;
$dateFrom = "2026-07-01";
$dateTo   = "2026-07-08";

// Test query sederhana dulu
try {
    $count = $pgDb->createCommand("SELECT COUNT(*) FROM pendaftaran_t WHERE tgl_pendaftaran::date BETWEEN :df AND :dt AND pasienbatalperiksa_id IS NULL", [":df"=>$dateFrom,":dt"=>$dateTo])->queryScalar();
    echo "Pendaftaran Juli: {$count}\n";
} catch (Exception $e) { echo "Error count: " . $e->getMessage() . "\n"; }

// Test apakah kolom status_pasien ada
try {
    $cols = $pgDb->createCommand("SELECT column_name FROM information_schema.columns WHERE table_name='pendaftaran_t' ORDER BY column_name")->queryColumn();
    echo "\nKolom pendaftaran_t:\n";
    foreach($cols as $c) echo "  {$c}\n";
} catch (Exception $e) { echo "Error cols: " . $e->getMessage() . "\n"; }
