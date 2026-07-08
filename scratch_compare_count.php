<?php
require __DIR__ . "/vendor/autoload.php";
require __DIR__ . "/vendor/yiisoft/yii2/Yii.php";
$config = require __DIR__ . "/config/web.php";
new yii\web\Application($config);

$db = Yii::$app->db;
$df = "2026-07-01"; $dt = "2026-07-08";

echo "=== Perbandingan Logika Filter ===\n\n";

// 1. Dashboard: filter by tgl_pendaftaran (registrasi)
$dashboard = $db->createCommand("
    SELECT COUNT(p.pendaftaran_id)
    FROM pendaftaran_t p
    JOIN ruangan_m rm ON rm.ruangan_id = p.ruangan_id
    WHERE rm.instalasi_id = 2
      AND p.tgl_pendaftaran::date BETWEEN :df AND :dt
      AND p.pasienbatalperiksa_id IS NULL
", [":df"=>$df, ":dt"=>$dt])->queryScalar();
echo "DASHBOARD  (filter: tgl_pendaftaran)  : {$dashboard}\n";

// 2. Laporan: filter by tglclosingkasir (tanggal closing kasir)
$laporan = $db->createCommand("
    SELECT COUNT(*) FROM (
        SELECT COUNT(1)
        FROM pembayaranpelayanan_t ppt
        JOIN pendaftaran_t pt ON pt.pendaftaran_id = ppt.pendaftaran_id
        JOIN tandabuktibayar_t tbt ON tbt.tandabuktibayar_id = ppt.tandabuktibayar_id
        JOIN closingkasir_t ct ON ct.closingkasir_id = tbt.closingkasir_id
        JOIN ruangan_m rm ON rm.ruangan_id = pt.ruangan_id
        JOIN instalasi_m im ON im.instalasi_id = rm.instalasi_id
        WHERE DATE(tglclosingkasir) BETWEEN :df AND :dt
          AND im.instalasi_id = '2'
        GROUP BY tgl_pendaftaran, no_pendaftaran, tglpembayaran, nopembayaran,
                 tglclosingkasir, closingkasir_no, no_rekam_medik, nama_pasien,
                 carabayar_nama, totalbiayatindakan, totalbiayaoa,
                 totalbiayapelayanan, totalppnfarmasi
    ) AS sub
", [":df"=>$df, ":dt"=>$dt])->queryScalar();
echo "LAPORAN    (filter: tglclosingkasir)  : {$laporan}\n";

// 3. Cek: pendaftaran Juli belum closing kasir (belum bayar)
$belumBayar = $db->createCommand("
    SELECT COUNT(p.pendaftaran_id)
    FROM pendaftaran_t p
    JOIN ruangan_m rm ON rm.ruangan_id = p.ruangan_id
    WHERE rm.instalasi_id = 2
      AND p.tgl_pendaftaran::date BETWEEN :df AND :dt
      AND p.pasienbatalperiksa_id IS NULL
      AND p.pembayaranpelayanan_id IS NULL
", [":df"=>$df, ":dt"=>$dt])->queryScalar();
echo "\nPendaftaran Juli BELUM bayar/closing  : {$belumBayar}\n";

// 4. Pendaftaran sebelum Juli yg closing kasirnya di bulan Juli
$closingCrossMonth = $db->createCommand("
    SELECT COUNT(*) FROM (
        SELECT COUNT(1)
        FROM pembayaranpelayanan_t ppt
        JOIN pendaftaran_t pt ON pt.pendaftaran_id = ppt.pendaftaran_id
        JOIN tandabuktibayar_t tbt ON tbt.tandabuktibayar_id = ppt.tandabuktibayar_id
        JOIN closingkasir_t ct ON ct.closingkasir_id = tbt.closingkasir_id
        JOIN ruangan_m rm ON rm.ruangan_id = pt.ruangan_id
        WHERE DATE(tglclosingkasir) BETWEEN :df AND :dt
          AND rm.instalasi_id = 2
          AND pt.tgl_pendaftaran::date < :df
        GROUP BY tgl_pendaftaran, no_pendaftaran, tglpembayaran, nopembayaran,
                 tglclosingkasir, closingkasir_no, no_rekam_medik, nama_pasien,
                 carabayar_nama, totalbiayatindakan, totalbiayaoa,
                 totalbiayapelayanan, totalppnfarmasi
    ) sub
", [":df"=>$df, ":dt"=>$dt])->queryScalar();
echo "Daftar SEBELUM Juli, closing di Juli  : {$closingCrossMonth}\n";

echo "\n=== Kesimpulan ===\n";
echo "Dashboard ({$dashboard}) = Laporan ({$laporan}) + belum bayar ({$belumBayar}) - lintas bulan ({$closingCrossMonth})\n";
echo "Cek: " . ($laporan + $belumBayar - $closingCrossMonth) . " vs Dashboard: {$dashboard}\n";
