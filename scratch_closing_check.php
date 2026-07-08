<?php
require __DIR__ . "/vendor/autoload.php";
require __DIR__ . "/vendor/yiisoft/yii2/Yii.php";
$config = require __DIR__ . "/config/web.php";
new yii\web\Application($config);
$db = Yii::$app->db;
$df = "2026-07-01"; $dt = "2026-07-08";

// Test query closing status per unit
$units = [
    "Rawat Jalan"   => [2],
    "IGD"           => [3],
    "Rawat Inap"    => [4, 38],
    "Lab & Rad"     => [5, 6],
];

echo "=== Test Closing Status per Unit ===\n";
foreach ($units as $label => $ids) {
    $insSql = implode(",", $ids);
    
    $total = (int)$db->createCommand("
        SELECT COUNT(p.pendaftaran_id) FROM pendaftaran_t p
        JOIN ruangan_m rm ON rm.ruangan_id = p.ruangan_id
        WHERE rm.instalasi_id IN ({$insSql})
          AND p.tgl_pendaftaran::date BETWEEN :df AND :dt
          AND p.pasienbatalperiksa_id IS NULL
    ", [":df"=>$df,":dt"=>$dt])->queryScalar();
    
    $belumBayar = (int)$db->createCommand("
        SELECT COUNT(p.pendaftaran_id) FROM pendaftaran_t p
        JOIN ruangan_m rm ON rm.ruangan_id = p.ruangan_id
        WHERE rm.instalasi_id IN ({$insSql})
          AND p.tgl_pendaftaran::date BETWEEN :df AND :dt
          AND p.pasienbatalperiksa_id IS NULL
          AND p.pembayaranpelayanan_id IS NULL
    ", [":df"=>$df,":dt"=>$dt])->queryScalar();
    
    $sudahBayar = $total - $belumBayar;
    $pct = $total > 0 ? round($sudahBayar / $total * 100) : 0;
    echo "{$label}: total={$total}, closing={$sudahBayar}, pending={$belumBayar} ({$pct}%)\n";
}
