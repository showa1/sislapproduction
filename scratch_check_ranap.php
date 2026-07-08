<?php
require __DIR__ . "/vendor/autoload.php";
require __DIR__ . "/vendor/yiisoft/yii2/Yii.php";
$config = require __DIR__ . "/config/web.php";
new yii\web\Application($config);
$db = Yii::$app->db;
$df = "2026-07-01"; $dt = "2026-07-08";

echo "=== Cek Kunjungan Rawat Inap ===\n";

// 1. Hitung pendaftaran langsung di instalasi 4 (Dashboard saat ini)
$daftarLangsung = $db->createCommand("
    SELECT COUNT(p.pendaftaran_id) FROM pendaftaran_t p
    JOIN ruangan_m rm ON rm.ruangan_id = p.ruangan_id
    WHERE rm.instalasi_id = 4
      AND p.tgl_pendaftaran::date BETWEEN :df AND :dt
      AND p.pasienbatalperiksa_id IS NULL
", [":df"=>$df,":dt"=>$dt])->queryScalar();
echo "Pendaftaran LANGSUNG ke Instalasi 4 (Dashboard): {$daftarLangsung}\n";

// 2. Cek tabel pasienadmisi_t (Admissions)
try {
    $admisi = $db->createCommand("
        SELECT COUNT(pa.pasienadmisi_id) FROM pasienadmisi_t pa
        WHERE pa.tgladmisi::date BETWEEN :df AND :dt
    ", [":df"=>$df,":dt"=>$dt])->queryScalar();
    echo "Pasien Admisi (pasienadmisi_t tgladmisi): {$admisi}\n";
} catch(Exception $e) { echo "Tabel pasienadmisi_t tidak ada/error: " . $e->getMessage() . "\n"; }

// 3. Cek pasien yang dipindahkan ke rawat inap (pendaftaran_t -> pasienadmisi_t)
try {
    $admisiTotal = $db->createCommand("
        SELECT COUNT(p.pendaftaran_id) FROM pendaftaran_t p
        JOIN pasienadmisi_t pa ON pa.pendaftaran_id = p.pendaftaran_id
        WHERE pa.tgladmisi::date BETWEEN :df AND :dt
          AND p.pasienbatalperiksa_id IS NULL
    ", [":df"=>$df,":dt"=>$dt])->queryScalar();
    echo "Pendaftaran dengan record admisi di Juli: {$admisiTotal}\n";
} catch(Exception $e) { echo "Error admisi pendaftaran: " . $e->getMessage() . "\n"; }

// 4. Cek struktur tabel pasienadmisi_t
try {
    $cols = $db->createCommand("SELECT column_name FROM information_schema.columns WHERE table_name='pasienadmisi_t' ORDER BY column_name")->queryColumn();
    echo "\nKolom pasienadmisi_t:\n" . implode(", ", $cols) . "\n";
} catch(Exception $e) {}

// 5. Cek data pasien admisi (5 data terakhir)
try {
    $contoh = $db->createCommand("
        SELECT p.no_pendaftaran, pa.tgladmisi, rm_asal.ruangan_nama as asal, rm_tujuan.ruangan_nama as tujuan
        FROM pasienadmisi_t pa
        JOIN pendaftaran_t p ON p.pendaftaran_id = pa.pendaftaran_id
        LEFT JOIN ruangan_m rm_asal ON rm_asal.ruangan_id = p.ruangan_id
        LEFT JOIN ruangan_m rm_tujuan ON rm_tujuan.ruangan_id = pa.ruangan_id
        ORDER BY pa.tgladmisi DESC LIMIT 5
    ")->queryAll();
    echo "\nContoh Admisi Terbaru:\n";
    foreach($contoh as $c) echo "  {$c['no_pendaftaran']} | {$c['tgladmisi']} | {$c['asal']} -> {$c['tujuan']}\n";
} catch(Exception $e) {}

