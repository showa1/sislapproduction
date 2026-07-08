<?php
require 'vendor/autoload.php';
require 'vendor/yiisoft/yii2/Yii.php';
$config = require 'config/web.php';
(new yii\web\Application($config));

try {
    Yii::$app->db->createCommand("
        CREATE TABLE IF NOT EXISTS sislap_log_akses_keuangan (
            id SERIAL PRIMARY KEY,
            id_pemakai INTEGER,
            nama_pemakai VARCHAR(100),
            ip_address VARCHAR(50),
            waktu_akses TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            keterangan TEXT
        )
    ")->execute();
    echo "Table sislap_log_akses_keuangan created successfully.\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
