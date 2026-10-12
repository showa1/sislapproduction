<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/config/web.php';
new yii\web\Application($config);

$db = Yii::$app->db;

// ===================================================
// QUERY PIVOT: Semua Paket MCU Perusahaan per Bulan
// ===================================================

// Daftar tipepaket_id perusahaan MCU
$paketMcu = [
    58  => 'PT. BUMI WIJAYA',
    65  => 'EDUJUKU',
    75  => 'MCU JAPFA',
];

// Query pivot yang benar: subquery distinct pendaftaran dulu
$pivotSql = "
    SELECT
        tp.tipepaket_id,
        tp.tipepaket_nama,
        SUM(CASE WHEN sub.bln = 1  THEN 1 ELSE 0 END) AS bln_01,
        SUM(CASE WHEN sub.bln = 2  THEN 1 ELSE 0 END) AS bln_02,
        SUM(CASE WHEN sub.bln = 3  THEN 1 ELSE 0 END) AS bln_03,
        SUM(CASE WHEN sub.bln = 4  THEN 1 ELSE 0 END) AS bln_04,
        SUM(CASE WHEN sub.bln = 5  THEN 1 ELSE 0 END) AS bln_05,
        SUM(CASE WHEN sub.bln = 6  THEN 1 ELSE 0 END) AS bln_06,
        SUM(CASE WHEN sub.bln = 7  THEN 1 ELSE 0 END) AS bln_07,
        SUM(CASE WHEN sub.bln = 8  THEN 1 ELSE 0 END) AS bln_08,
        SUM(CASE WHEN sub.bln = 9  THEN 1 ELSE 0 END) AS bln_09,
        SUM(CASE WHEN sub.bln = 10 THEN 1 ELSE 0 END) AS bln_10,
        SUM(CASE WHEN sub.bln = 11 THEN 1 ELSE 0 END) AS bln_11,
        SUM(CASE WHEN sub.bln = 12 THEN 1 ELSE 0 END) AS bln_12,
        COUNT(sub.pendaftaran_id) AS total
    FROM tipepaket_m tp
    LEFT JOIN (
        SELECT DISTINCT
            pm.tipepaket_id,
            pt.pendaftaran_id,
            EXTRACT(MONTH FROM pt.tgl_pendaftaran)::int AS bln
        FROM permintaanmcu_t pm
        JOIN pendaftaran_t pt ON pt.pendaftaran_id = pm.pendaftaran_id
        WHERE EXTRACT(YEAR FROM pt.tgl_pendaftaran) = 2026
          AND pm.tipepaket_id IN (58, 65, 75)
    ) sub ON sub.tipepaket_id = tp.tipepaket_id
    WHERE tp.tipepaket_id IN (58, 65, 75)
    GROUP BY tp.tipepaket_id, tp.tipepaket_nama
    ORDER BY tp.tipepaket_nama
";

$pivotRows = $db->createCommand($pivotSql)->queryAll();

echo "=== PIVOT: MCU Perusahaan per Bulan (2026) ===\n";
$bulan = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
printf("%-30s", "Nama Paket");
foreach ($bulan as $b) printf("%6s", $b);
printf("%8s\n", "TOTAL");
echo str_repeat('-', 110) . "\n";
foreach ($pivotRows as $r) {
    printf("%-30s", substr($r['tipepaket_nama'], 0, 29));
    for ($i = 1; $i <= 12; $i++) {
        $key = 'bln_' . str_pad($i, 2, '0', STR_PAD_LEFT);
        printf("%6d", (int)($r[$key] ?? 0));
    }
    printf("%8d\n", (int)($r['total'] ?? 0));
}
echo str_repeat('-', 110) . "\n";

// Juga tampilkan raw data
echo "\n=== RAW DATA ===\n";
print_r($pivotRows);
















