<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/../config/web.php';
new yii\web\Application($config);

$db = Yii::$app->db;

$sql = "
    SELECT 
        pm.pegawai_id,
        pm.gelardepan,
        pm.nama_pegawai,
        gb.gelarbelakang_nama,
        STRING_AGG(DISTINCT rm.ruangan_nama, ', ') AS ruangan_nama,
        COUNT(pt.pendaftaran_id) FILTER (WHERE rm.is_eksekutif IS NOT TRUE) AS jumlah_bpjs,
        COUNT(pt.pendaftaran_id) FILTER (WHERE rm.is_eksekutif = true) AS jumlah_eksekutif,
        COUNT(pt.pendaftaran_id) AS jumlahpasien
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
    ORDER BY jumlahpasien DESC
";
$rows = $db->createCommand($sql)->queryAll();
echo "Total Rows: " . count($rows) . "\n\n";
foreach (array_slice($rows, 0, 10) as $i => $r) {
    $gelardepan = !empty($r['gelardepan']) ? trim($r['gelardepan']) : '';
    $gelarbelakang = !empty($r['gelarbelakang_nama']) ? trim($r['gelarbelakang_nama']) : '';
    $namaPegawai = trim($gelardepan . ' ' . $r['nama_pegawai'] . ' ' . $gelarbelakang);
    echo ($i + 1) . ". {$namaPegawai}\n";
    echo "   Poli: {$r['ruangan_nama']}\n";
    echo "   Klinik BPJS/Reguler: {$r['jumlah_bpjs']} | Klinik Eksekutif: {$r['jumlah_eksekutif']} | Total Pasien: {$r['jumlahpasien']}\n\n";
}
