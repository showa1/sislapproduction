<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/config/web.php';
new yii\web\Application($config);

$db = Yii::$app->db;

echo "=== OPTION 1: BREAKDOWN PER DOKTER & RUANGAN ===\n";
$sql1 = "
    SELECT 
        pm.pegawai_id,
        pm.gelardepan,
        pm.nama_pegawai,
        gb.gelarbelakang_nama,
        rm.ruangan_nama,
        COUNT(pt.pendaftaran_id) AS jumlahpasien
    FROM pendaftaran_t pt
    JOIN pegawai_m pm ON pm.pegawai_id = pt.pegawai_id
    JOIN ruangan_m rm ON rm.ruangan_id = pt.ruangan_id
    LEFT JOIN gelarbelakang_m gb ON gb.gelarbelakang_id = pm.gelarbelakang_id
    LEFT JOIN jabatan_m j ON j.jabatan_id = pm.jabatan_id
    WHERE pt.pasienbatalperiksa_id IS NULL
      AND DATE(pt.tgl_pendaftaran) BETWEEN '2026-08-01' AND '2026-08-31'
      AND (pm.kelompokpegawai_id = 1 OR j.jabatan_nama ILIKE '%dokter%')
    GROUP BY pm.pegawai_id, pm.gelardepan, pm.nama_pegawai, gb.gelarbelakang_nama, rm.ruangan_id, rm.ruangan_nama
    ORDER BY jumlahpasien DESC
";
$rows1 = $db->createCommand($sql1)->queryAll();
echo "Total Rows: " . count($rows1) . "\n";
print_r(array_slice($rows1, 0, 10));

echo "\n=== OPTION 2: 1 ROW PER DOKTER WITH STRING_AGG RUANGAN ===\n";
$sql2 = "
    SELECT 
        pm.pegawai_id,
        pm.gelardepan,
        pm.nama_pegawai,
        gb.gelarbelakang_nama,
        STRING_AGG(DISTINCT rm.ruangan_nama, ', ') AS ruangan_nama,
        COUNT(pt.pendaftaran_id) AS jumlahpasien
    FROM pendaftaran_t pt
    JOIN pegawai_m pm ON pm.pegawai_id = pt.pegawai_id
    JOIN ruangan_m rm ON rm.ruangan_id = pt.ruangan_id
    LEFT JOIN gelarbelakang_m gb ON gb.gelarbelakang_id = pm.gelarbelakang_id
    LEFT JOIN jabatan_m j ON j.jabatan_id = pm.jabatan_id
    WHERE pt.pasienbatalperiksa_id IS NULL
      AND DATE(pt.tgl_pendaftaran) BETWEEN '2026-08-01' AND '2026-08-31'
      AND (pm.kelompokpegawai_id = 1 OR j.jabatan_nama ILIKE '%dokter%')
    GROUP BY pm.pegawai_id, pm.gelardepan, pm.nama_pegawai, gb.gelarbelakang_nama
    ORDER BY jumlahpasien DESC
";
$rows2 = $db->createCommand($sql2)->queryAll();
echo "Total Rows: " . count($rows2) . "\n";
print_r(array_slice($rows2, 0, 10));
