<?php
require __DIR__ . "/vendor/autoload.php";
require __DIR__ . "/vendor/yiisoft/yii2/Yii.php";
$config = require __DIR__ . "/config/web.php";
new yii\web\Application($config);

$db = Yii::$app->db;

echo "=== Informasi Koneksi Database ===\n";
echo "DSN     : " . $db->dsn . "\n";
echo "Host    : ";
preg_match('/host=([^;]+)/', $db->dsn, $m); echo ($m[1] ?? '?') . "\n";
echo "DB Name : ";
preg_match('/dbname=([^;]+)/', $db->dsn, $m); echo ($m[1] ?? '?') . "\n";

echo "\n=== Versi PostgreSQL ===\n";
echo $db->createCommand("SELECT version()")->queryScalar() . "\n";

echo "\n=== Data Terkini (update terakhir) ===\n";
$latest = $db->createCommand("SELECT MAX(tgl_pendaftaran) FROM pendaftaran_t WHERE pasienbatalperiksa_id IS NULL")->queryScalar();
echo "Pendaftaran terakhir : {$latest}\n";

echo "\n=== Total Data (semua waktu) ===\n";
$total = $db->createCommand("SELECT COUNT(*) FROM pendaftaran_t WHERE pasienbatalperiksa_id IS NULL")->queryScalar();
echo "Total kunjungan : " . number_format($total) . "\n";

echo "\n=== Data Hari Ini (" . date('Y-m-d') . ") ===\n";
$today = $db->createCommand("SELECT COUNT(*) FROM pendaftaran_t WHERE tgl_pendaftaran::date = CURRENT_DATE AND pasienbatalperiksa_id IS NULL")->queryScalar();
echo "Kunjungan hari ini : {$today}\n";

echo "\n=== Data 7 hari terakhir ===\n";
$rows = $db->createCommand("
    SELECT tgl_pendaftaran::date AS tgl, COUNT(*) AS n
    FROM pendaftaran_t
    WHERE tgl_pendaftaran::date >= CURRENT_DATE - INTERVAL '7 days'
      AND pasienbatalperiksa_id IS NULL
    GROUP BY tgl ORDER BY tgl DESC
")->queryAll();
foreach ($rows as $r) echo "  {$r['tgl']}: {$r['n']} kunjungan\n";

echo "\n=== Tabel utama yang tersedia ===\n";
$tables = $db->createCommand("
    SELECT table_name FROM information_schema.tables
    WHERE table_schema='public' AND table_type='BASE TABLE'
    ORDER BY table_name LIMIT 30
")->queryColumn();
foreach ($tables as $t) echo "  {$t}\n";
