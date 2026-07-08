<?php
require __DIR__ . "/vendor/autoload.php";
require __DIR__ . "/vendor/yiisoft/yii2/Yii.php";
$config = require __DIR__ . "/config/web.php";
new yii\web\Application($config);
$db = Yii::$app->db;
$df = "2026-07-01"; $dt = "2026-07-08";

// Coba lihat table pendaftaran_t di filter ke instalasi penunjang dengan tanggal yang benar
// Sebelumnya, count instalasi langsung adalah 13.
// Bagaimana kalau tanggal tgl_pendaftaran = 1-8 Juli dan pasien masuk penunjang tanpa pendaftaran di depan?

$count35 = $db->createCommand("
    SELECT COUNT(p.pendaftaran_id) FROM pendaftaran_t p
    JOIN ruangan_m rm ON rm.ruangan_id = p.ruangan_id
    JOIN instalasi_m im ON im.instalasi_id = rm.instalasi_id
    WHERE p.tgl_pendaftaran::date BETWEEN :df AND :dt
      AND p.pasienbatalperiksa_id IS NULL
      AND (
         im.instalasi_nama ILIKE '%penunjang%' 
         OR im.instalasi_nama ILIKE '%lab%' 
         OR im.instalasi_nama ILIKE '%rad%'
         OR im.instalasi_id IN (5,6,9,10,72,74,76)
      )
")->queryScalar();
echo "Pendaftaran Langsung Semua Penunjang (Lab, Rad, Ambulans, Hemodialisa, dll): {$count35}\n";

