<?php
require __DIR__ . "/vendor/autoload.php";
require __DIR__ . "/vendor/yiisoft/yii2/Yii.php";
$config = require __DIR__ . "/config/web.php";
new yii\web\Application($config);
$db = Yii::$app->db;
$df = "2026-07-01"; $dt = "2026-07-08";

// Query 1: Total Admisi (Semua Instalasi tujuan)
$admisiAll = $db->createCommand("
    SELECT COUNT(pa.pasienadmisi_id)
    FROM pasienadmisi_t pa
    WHERE pa.tgladmisi::date BETWEEN :df AND :dt
")->queryScalar();
echo "Total Admisi (Semua tujuan) : {$admisiAll}\n";

// Query 2: Admisi ke Instalasi 4 (Ranap) dan 38 (Kebidanan)
$admisiRanap = $db->createCommand("
    SELECT COUNT(pa.pasienadmisi_id)
    FROM pasienadmisi_t pa
    JOIN ruangan_m rm ON rm.ruangan_id = pa.ruangan_id
    JOIN pendaftaran_t p ON p.pendaftaran_id = pa.pendaftaran_id
    WHERE pa.tgladmisi::date BETWEEN :df AND :dt
      AND rm.instalasi_id IN (4, 38)
      AND p.pasienbatalperiksa_id IS NULL
")->queryScalar();
echo "Admisi ke Ranap (Instalasi 4, 38) : {$admisiRanap}\n";

// Query 3: Status Closing untuk Pasien Admisi
$admisiClosing = (int)$db->createCommand("
    SELECT COUNT(pa.pasienadmisi_id)
    FROM pasienadmisi_t pa
    JOIN ruangan_m rm ON rm.ruangan_id = pa.ruangan_id
    JOIN pendaftaran_t p ON p.pendaftaran_id = pa.pendaftaran_id
    WHERE pa.tgladmisi::date BETWEEN :df AND :dt
      AND rm.instalasi_id IN (4, 38)
      AND p.pasienbatalperiksa_id IS NULL
      AND p.pembayaranpelayanan_id IS NOT NULL
")->queryScalar();
echo "Admisi ke Ranap yg Sudah Bayar : {$admisiClosing}\n";
echo "Admisi ke Ranap yg Belum Bayar : " . ($admisiRanap - $admisiClosing) . "\n";

// Query 4: Penjamin untuk Pasien Admisi
$cbCase = "CASE
    WHEN UPPER(cb.carabayar_nama) LIKE '%BPJS KES%'  THEN 'BPJS Kesehatan'
    WHEN UPPER(cb.carabayar_nama) LIKE '%JASA RAH%'  THEN 'Jasa Raharja'
    WHEN UPPER(cb.carabayar_nama) LIKE '%ASURANSI%'  THEN 'Asuransi Komersial'
    ELSE 'Umum'
END";

$penjamin = $db->createCommand("
    SELECT {$cbCase} AS cara_bayar, COUNT(pa.pasienadmisi_id) as n
    FROM pasienadmisi_t pa
    JOIN ruangan_m rm ON rm.ruangan_id = pa.ruangan_id
    JOIN pendaftaran_t p ON p.pendaftaran_id = pa.pendaftaran_id
    LEFT JOIN carabayar_m cb ON cb.carabayar_id = pa.carabayar_id
    WHERE pa.tgladmisi::date BETWEEN :df AND :dt
      AND rm.instalasi_id IN (4, 38)
      AND p.pasienbatalperiksa_id IS NULL
    GROUP BY cara_bayar
")->queryAll();

echo "\nPenjamin Admisi Ranap:\n";
foreach($penjamin as $r) echo "  {$r['cara_bayar']}: {$r['n']}\n";
