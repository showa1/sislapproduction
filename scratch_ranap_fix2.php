<?php
require __DIR__ . "/vendor/autoload.php";
require __DIR__ . "/vendor/yiisoft/yii2/Yii.php";
$config = require __DIR__ . "/config/web.php";
new yii\web\Application($config);
$db = Yii::$app->db;
$df = "2026-07-01"; $dt = "2026-07-08";

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
