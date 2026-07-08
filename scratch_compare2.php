<?php
require __DIR__ . "/vendor/autoload.php";
require __DIR__ . "/vendor/yiisoft/yii2/Yii.php";
$config = require __DIR__ . "/config/web.php";
new yii\web\Application($config);
$db = Yii::$app->db;
$df = "2026-07-01"; $dt = "2026-07-08";

echo "=== Perbandingan Logika Filter ===\n\n";

// 1. Dashboard: tgl_pendaftaran
$dashboard = (int)$db->createCommand("
    SELECT COUNT(p.pendaftaran_id)
    FROM pendaftaran_t p
    JOIN ruangan_m rm ON rm.ruangan_id = p.ruangan_id
    WHERE rm.instalasi_id = 2
      AND p.tgl_pendaftaran::date BETWEEN :df AND :dt
      AND p.pasienbatalperiksa_id IS NULL
", [":df"=>$df,":dt"=>$dt])->queryScalar();
echo "DASHBOARD (filter: tgl_pendaftaran)     : {$dashboard}\n";

// 2. Laporan: tglclosingkasir - langsung dari pembayaranpelayanan_t
try {
    $laporan = (int)$db->createCommand("
        SELECT COUNT(DISTINCT pt.pendaftaran_id)
        FROM pembayaranpelayanan_t ppt
        JOIN pendaftaran_t pt ON pt.pendaftaran_id = ppt.pendaftaran_id
        JOIN tandabuktibayar_t tbt ON tbt.tandabuktibayar_id = ppt.tandabuktibayar_id
        JOIN closingkasir_t ct ON ct.closingkasir_id = tbt.closingkasir_id
        JOIN ruangan_m rm ON rm.ruangan_id = pt.ruangan_id
        WHERE DATE(ct.tglclosingkasir) BETWEEN :df AND :dt
          AND rm.instalasi_id = 2
    ", [":df"=>$df,":dt"=>$dt])->queryScalar();
    echo "LAPORAN   (filter: tglclosingkasir)     : {$laporan}\n";
} catch (Exception $e) {
    // coba kolom lain
    $cols = $db->createCommand("SELECT column_name FROM information_schema.columns WHERE table_name='closingkasir_t' ORDER BY column_name")->queryColumn();
    echo "Kolom closingkasir_t: " . implode(", ", $cols) . "\n";
}

// 3. Kolom closing kasir
try {
    $cols = $db->createCommand("SELECT column_name FROM information_schema.columns WHERE table_name='closingkasir_t' ORDER BY column_name")->queryColumn();
    echo "\nKolom closingkasir_t: " . implode(", ", $cols) . "\n";
} catch(Exception $e) { echo $e->getMessage() . "\n"; }

// 4. Belum bayar (pendaftaran Juli tapi belum ada di pembayaranpelayanan_t)
$belumBayar = (int)$db->createCommand("
    SELECT COUNT(p.pendaftaran_id)
    FROM pendaftaran_t p
    JOIN ruangan_m rm ON rm.ruangan_id = p.ruangan_id
    WHERE rm.instalasi_id = 2
      AND p.tgl_pendaftaran::date BETWEEN :df AND :dt
      AND p.pasienbatalperiksa_id IS NULL
      AND p.pembayaranpelayanan_id IS NULL
", [":df"=>$df,":dt"=>$dt])->queryScalar();
echo "\nJuli daftar, BELUM bayar              : {$belumBayar}\n";

// 5. Sudah bayar
$sudahBayar = $dashboard - $belumBayar;
echo "Juli daftar, SUDAH bayar              : {$sudahBayar}\n";
