<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/config/web.php';
new yii\web\Application($config);

$db = Yii::$app->db;

echo "=== OPTION A: COUNT ALL PENDAFTARAN FOR DOCTORS IN RAWAT JALAN (instalasi_id = 2) BETWEEN 2026-08-01 AND 2026-08-31 ===\n";
$sqlA = "
    SELECT 
        pm.pegawai_id,
        pm.gelardepan,
        pm.nama_pegawai,
        gb.gelarbelakang_nama,
        j.jabatan_nama,
        COUNT(pt.pendaftaran_id) AS jumlahpasien
    FROM pendaftaran_t pt
    JOIN pegawai_m pm ON pm.pegawai_id = pt.pegawai_id
    JOIN ruangan_m rm ON rm.ruangan_id = pt.ruangan_id
    LEFT JOIN jabatan_m j ON j.jabatan_id = pm.jabatan_id
    LEFT JOIN gelarbelakang_m gb ON gb.gelarbelakang_id = pm.gelarbelakang_id
    WHERE pt.pasienbatalperiksa_id IS NULL
      AND rm.instalasi_id = 2
      AND DATE(pt.tgl_pendaftaran) BETWEEN '2026-08-01' AND '2026-08-31'
      AND (pm.kelompokpegawai_id = 1 OR j.jabatan_nama ILIKE '%dokter%')
    GROUP BY pm.pegawai_id, pm.gelardepan, pm.nama_pegawai, gb.gelarbelakang_nama, j.jabatan_nama
    ORDER BY jumlahpasien DESC
";
$rowsA = $db->createCommand($sqlA)->queryAll();
echo "Total Doctors: " . count($rowsA) . "\n";
print_r($rowsA);

echo "\n=== OPTION B: COUNT ALL PENDAFTARAN FOR DOCTORS (ANY INSTALASI) BETWEEN 2026-08-01 AND 2026-08-31 ===\n";
$sqlB = "
    SELECT 
        pm.pegawai_id,
        pm.gelardepan,
        pm.nama_pegawai,
        gb.gelarbelakang_nama,
        j.jabatan_nama,
        COUNT(pt.pendaftaran_id) AS jumlahpasien
    FROM pendaftaran_t pt
    JOIN pegawai_m pm ON pm.pegawai_id = pt.pegawai_id
    LEFT JOIN jabatan_m j ON j.jabatan_id = pm.jabatan_id
    LEFT JOIN gelarbelakang_m gb ON gb.gelarbelakang_id = pm.gelarbelakang_id
    WHERE pt.pasienbatalperiksa_id IS NULL
      AND DATE(pt.tgl_pendaftaran) BETWEEN '2026-08-01' AND '2026-08-31'
      AND (pm.kelompokpegawai_id = 1 OR j.jabatan_nama ILIKE '%dokter%')
    GROUP BY pm.pegawai_id, pm.gelardepan, pm.nama_pegawai, gb.gelarbelakang_nama, j.jabatan_nama
    ORDER BY jumlahpasien DESC
";
$rowsB = $db->createCommand($sqlB)->queryAll();
echo "Total Doctors: " . count($rowsB) . "\n";
print_r($rowsB);
