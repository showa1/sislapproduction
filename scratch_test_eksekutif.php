<?php
require __DIR__ . "/vendor/autoload.php";
require __DIR__ . "/vendor/yiisoft/yii2/Yii.php";
$config = require __DIR__ . "/config/web.php";
new yii\web\Application($config);

$db = Yii::$app->db;
$df = "2026-07-01"; $dt = "2026-07-08";

$cbCase = "CASE
    WHEN UPPER(cb.carabayar_nama) LIKE '%BPJS KES%'  THEN 'BPJS Kesehatan'
    WHEN UPPER(cb.carabayar_nama) LIKE '%JASA RAH%'  THEN 'Jasa Raharja'
    WHEN UPPER(cb.carabayar_nama) LIKE '%ASURANSI%'  THEN 'Asuransi Komersial'
    ELSE 'Umum'
END";

echo "=== KPI Test Juli 1-8 2026 ===\n";

// RJ
$rj = $db->createCommand("SELECT COUNT(p.pendaftaran_id) FROM pendaftaran_t p JOIN ruangan_m rm ON rm.ruangan_id = p.ruangan_id WHERE rm.instalasi_id = 2 AND p.tgl_pendaftaran::date BETWEEN :df AND :dt AND p.pasienbatalperiksa_id IS NULL", [":df"=>$df,":dt"=>$dt])->queryScalar();
echo "Rawat Jalan  : {$rj}\n";

// IGD
$igd = $db->createCommand("SELECT COUNT(p.pendaftaran_id) FROM pendaftaran_t p JOIN ruangan_m rm ON rm.ruangan_id = p.ruangan_id WHERE rm.instalasi_id = 3 AND p.tgl_pendaftaran::date BETWEEN :df AND :dt AND p.pasienbatalperiksa_id IS NULL", [":df"=>$df,":dt"=>$dt])->queryScalar();
echo "IGD          : {$igd}\n";

// Ranap
$ranap = $db->createCommand("SELECT COUNT(p.pendaftaran_id) FROM pendaftaran_t p JOIN ruangan_m rm ON rm.ruangan_id = p.ruangan_id WHERE rm.instalasi_id IN (4,38) AND p.tgl_pendaftaran::date BETWEEN :df AND :dt AND p.pasienbatalperiksa_id IS NULL", [":df"=>$df,":dt"=>$dt])->queryScalar();
echo "Rawat Inap   : {$ranap}\n";

// Lab+Rad
$lr = $db->createCommand("SELECT COUNT(p.pendaftaran_id) FROM pendaftaran_t p JOIN ruangan_m rm ON rm.ruangan_id = p.ruangan_id WHERE rm.instalasi_id IN (5,6) AND p.tgl_pendaftaran::date BETWEEN :df AND :dt AND p.pasienbatalperiksa_id IS NULL", [":df"=>$df,":dt"=>$dt])->queryScalar();
echo "Lab & Rad    : {$lr}\n";

// Cara bayar RJ
echo "\n--- Penjamin RJ ---\n";
$pj = $db->createCommand("SELECT {$cbCase} AS cb, COUNT(*) AS n FROM pendaftaran_t p JOIN ruangan_m rm ON rm.ruangan_id = p.ruangan_id LEFT JOIN carabayar_m cb ON cb.carabayar_id = p.carabayar_id WHERE rm.instalasi_id = 2 AND p.tgl_pendaftaran::date BETWEEN :df AND :dt AND p.pasienbatalperiksa_id IS NULL GROUP BY cb ORDER BY n DESC", [":df"=>$df,":dt"=>$dt])->queryAll();
foreach($pj as $r) echo "  {$r['cb']}: {$r['n']}\n";
