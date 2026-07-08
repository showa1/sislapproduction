<?php
// Simple script to create table using PDO directly
$dsn = 'pgsql:host=localhost;dbname=sislap;user=postgres;password=postgres'; // Fallback guest
// Let's try to get config from Yii to be sure
require 'vendor/autoload.php';
require 'vendor/yiisoft/yii2/Yii.php';
$config = require 'config/web.php';
(new yii\web\Application($config));

$db = Yii::$app->db;
echo "Attempting to create table sislap_log_akses_keuangan...\n";

try {
    $db->createCommand("
        CREATE TABLE sislap_log_akses_keuangan (
            id SERIAL PRIMARY KEY,
            id_pemakai INTEGER,
            nama_pemakai VARCHAR(100),
            ip_address VARCHAR(50),
            waktu_akses TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            keterangan TEXT
        )
    ")->execute();
    echo "Table created successfully.\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    if (strpos($e->getMessage(), 'already exists') !== false) {
        echo "Table already exists.\n";
    }
}
