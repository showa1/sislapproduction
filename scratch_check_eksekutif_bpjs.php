<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/config/web.php';
new yii\web\Application($config);

$db = Yii::$app->db;

echo "=== RUANGAN_M IN INSTALASI_ID = 2 ===\n";
$sql = "
    SELECT ruangan_id, ruangan_nama, is_eksekutif
    FROM ruangan_m
    WHERE instalasi_id = 2
    ORDER BY is_eksekutif DESC, ruangan_nama ASC
";
$rows = $db->createCommand($sql)->queryAll();
print_r($rows);

echo "\n=== PATIENT COUNTS PER DOCTOR BROKEN DOWN BY EKSUTIF VS REGULAR/BPJS IN AUGUST 2026 ===\n";
$sql2 = "
    SELECT 
        pm.pegawai_id,
        pm.gelardepan,
        pm.nama_pegawai,
        gb.gelarbelakang_nama,
        COUNT(pt.pendaftaran_id) FILTER (WHERE rm.is_eksekutif IS NOT TRUE) AS jumlah_regular,
        COUNT(pt.pendaftaran_id) FILTER (WHERE rm.is_eksekutif = true) AS jumlah_eksekutif,
        COUNT(pt.pendaftaran_id) AS jumlah_total
    FROM pendaftaran_t pt
    JOIN pegawai_m pm ON pm.pegawai_id = pt.pegawai_id
    JOIN ruangan_m rm ON rm.ruangan_id = pt.ruangan_id
    LEFT JOIN gelarbelakang_m gb ON gb.gelarbelakang_id = pm.gelarbelakang_id
    LEFT JOIN jabatan_m j ON j.jabatan_id = pm.jabatan_id
    WHERE pt.pasienbatalperiksa_id IS NULL
      AND rm.instalasi_id = 2
      AND DATE(pt.tgl_pendaftaran) BETWEEN '2026-08-01' AND '2026-08-31'
      AND (pm.kelompokpegawai_id = 1 OR j.jabatan_nama ILIKE '%dokter%')
    GROUP BY pm.pegawai_id, pm.gelardepan, pm.nama_pegawai, gb.gelarbelakang_nama
    ORDER BY jumlah_total DESC
";
$rows2 = $db->createCommand($sql2)->queryAll();
print_r(array_slice($rows2, 0, 15));
