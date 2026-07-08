<?php
require __DIR__ . "/vendor/autoload.php";
require __DIR__ . "/vendor/yiisoft/yii2/Yii.php";
$config = require __DIR__ . "/config/web.php";
new yii\web\Application($config);
$db = Yii::$app->db;

$nos = ['MC2607070003', 'RI2607010001'];

foreach ($nos as $no) {
    echo "Pendaftaran: {$no}\n";
    $p = $db->createCommand("SELECT * FROM pendaftaran_t WHERE no_pendaftaran = :no", [":no" => $no])->queryOne();
    if ($p) {
        $rm = $db->createCommand("SELECT instalasi_id FROM ruangan_m WHERE ruangan_id = :r", [":r" => $p['ruangan_id']])->queryScalar();
        echo "  - Pendaftaran: {$p['tgl_pendaftaran']} (Instalasi Asal: {$rm})\n";
    }
    
    $pmp = $db->createCommand("
        SELECT pmp.*, rm.instalasi_id 
        FROM pasienmasukpenunjang_t pmp 
        JOIN pendaftaran_t p ON p.pendaftaran_id = pmp.pendaftaran_id
        JOIN ruangan_m rm ON rm.ruangan_id = pmp.ruangan_id
        WHERE p.no_pendaftaran = :no
    ", [":no" => $no])->queryAll();
    
    foreach ($pmp as $m) {
        echo "  - Masuk Penunjang: {$m['tglmasukpenunjang']} (Instalasi Penunjang: {$m['instalasi_id']}) Status: {$m['statusperiksa']}\n";
    }
}
