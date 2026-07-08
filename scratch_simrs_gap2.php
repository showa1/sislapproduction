<?php
require __DIR__ . "/vendor/autoload.php";
require __DIR__ . "/vendor/yiisoft/yii2/Yii.php";
$config = require __DIR__ . "/config/web.php";
new yii\web\Application($config);
$db = Yii::$app->db;
$df = "2026-07-01"; $dt = "2026-07-08";

// Test A: tgl_pendaftaran (dari pendaftaran_t) di antara 1-8 Juli
$testA = $db->createCommand("
    SELECT COUNT(*) FROM pasienmasukpenunjang_t pmp
    JOIN pendaftaran_t p ON p.pendaftaran_id = pmp.pendaftaran_id
    WHERE p.tgl_pendaftaran::date BETWEEN :df AND :dt
")->queryScalar();
echo "Filter by p.tgl_pendaftaran : {$testA}\n";

// Test B: tgl_pendaftaran (dari pendaftaran_t) di antara 1-8 Juli dan pasien masuk penunjang TIDAK BATAL
$testB = $db->createCommand("
    SELECT COUNT(*) FROM pasienmasukpenunjang_t pmp
    JOIN pendaftaran_t p ON p.pendaftaran_id = pmp.pendaftaran_id
    LEFT JOIN pasienbatalperiksa_r batal ON batal.pasienmasukpenunjang_id = pmp.pasienmasukpenunjang_id
    WHERE p.tgl_pendaftaran::date BETWEEN :df AND :dt
      AND batal.pasienbatalperiksa_id IS NULL
")->queryScalar();
echo "Filter by p.tgl_pendaftaran (tidak batal) : {$testB}\n";

// Cek MC2607070003 dan RI2607010001
$nos = ['MC2607070003', 'RI2607010001'];
foreach ($nos as $no) {
    $rows = $db->createCommand("
        SELECT pmp.tglmasukpenunjang, p.tgl_pendaftaran 
        FROM pasienmasukpenunjang_t pmp
        JOIN pendaftaran_t p ON p.pendaftaran_id = pmp.pendaftaran_id
        WHERE p.no_pendaftaran = :no
    ", [":no" => $no])->queryAll();
    echo "\nNo Pendaftaran: {$no}\n";
    foreach($rows as $r) echo "  tgl_daftar: {$r['tgl_pendaftaran']} | tgl_penunjang: {$r['tglmasukpenunjang']}\n";
}
