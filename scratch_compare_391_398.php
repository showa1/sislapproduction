<?php
require __DIR__ . "/vendor/autoload.php";
require __DIR__ . "/vendor/yiisoft/yii2/Yii.php";
$config = require __DIR__ . "/config/web.php";
new yii\web\Application($config);
$db = Yii::$app->db;
$df = "2026-07-01"; $dt = "2026-07-08";

echo "=== Analisis 398 (Dashboard) vs 391 (Laporan) ===\n\n";

// 1. Dashboard "Closing": Daftar Juli, dan SUDAH bayar (pembayaranpelayanan_id is not null)
$dashboardClosing = (int)$db->createCommand("
    SELECT COUNT(p.pendaftaran_id) FROM pendaftaran_t p
    JOIN ruangan_m rm ON rm.ruangan_id = p.ruangan_id
    WHERE rm.instalasi_id = 2
      AND p.tgl_pendaftaran::date BETWEEN :df AND :dt
      AND p.pasienbatalperiksa_id IS NULL
      AND p.pembayaranpelayanan_id IS NOT NULL
", [":df"=>$df,":dt"=>$dt])->queryScalar();
echo "Dashboard (Daftar Juli, Sudah Bayar kapanpun) : {$dashboardClosing}\n";

// 2. Laporan "Detail": Closing Kasir Juli, Daftar Kapanpun
$laporanDetail = (int)$db->createCommand("
    SELECT COUNT(*) FROM (
        SELECT COUNT(1)
        FROM pembayaranpelayanan_t ppt
        JOIN pendaftaran_t pt ON pt.pendaftaran_id = ppt.pendaftaran_id
        JOIN tandabuktibayar_t tbt ON tbt.tandabuktibayar_id = ppt.tandabuktibayar_id
        JOIN closingkasir_t ct ON ct.closingkasir_id = tbt.closingkasir_id
        JOIN ruangan_m rm ON rm.ruangan_id = pt.ruangan_id
        JOIN instalasi_m im ON im.instalasi_id = rm.instalasi_id
        WHERE DATE(ct.tglclosingkasir) BETWEEN :df AND :dt
          AND im.instalasi_id = '2'
        GROUP BY pt.tgl_pendaftaran, pt.no_pendaftaran, ppt.tglpembayaran, ppt.nopembayaran,
                 ct.tglclosingkasir, ct.closingkasir_no, pt.no_rekam_medik
    ) sub
", [":df"=>$df,":dt"=>$dt])->queryScalar();
echo "Laporan (Closing Juli, Daftar kapanpun)       : {$laporanDetail}\n";

// 3. Irisan: Daftar Juli DAN Closing Juli
$irisan = (int)$db->createCommand("
    SELECT COUNT(*) FROM (
        SELECT COUNT(1)
        FROM pembayaranpelayanan_t ppt
        JOIN pendaftaran_t pt ON pt.pendaftaran_id = ppt.pendaftaran_id
        JOIN tandabuktibayar_t tbt ON tbt.tandabuktibayar_id = ppt.tandabuktibayar_id
        JOIN closingkasir_t ct ON ct.closingkasir_id = tbt.closingkasir_id
        JOIN ruangan_m rm ON rm.ruangan_id = pt.ruangan_id
        JOIN instalasi_m im ON im.instalasi_id = rm.instalasi_id
        WHERE DATE(ct.tglclosingkasir) BETWEEN :df AND :dt
          AND pt.tgl_pendaftaran::date BETWEEN :df AND :dt
          AND im.instalasi_id = '2'
        GROUP BY pt.tgl_pendaftaran, pt.no_pendaftaran, ppt.tglpembayaran, ppt.nopembayaran,
                 ct.tglclosingkasir, ct.closingkasir_no, pt.no_rekam_medik
    ) sub
", [":df"=>$df,":dt"=>$dt])->queryScalar();
echo "\nIrisan (Daftar Juli DAN Closing Juli)         : {$irisan}\n";

// 4. Ada di Dashboard (398), tapi TIDAK ada di Laporan (391)
// Ini artinya: Daftar di Juli, sudah bayar, TAPI closing kasirnya BUKAN tanggal 1-8 Juli 
// (Mungkin closingnya belum dilakukan / closing hari berikutnya yang lewat filter?)
$diDashboardBukanLaporan = (int)$db->createCommand("
    SELECT COUNT(p.pendaftaran_id) FROM pendaftaran_t p
    JOIN ruangan_m rm ON rm.ruangan_id = p.ruangan_id
    LEFT JOIN (
        SELECT ppt.pendaftaran_id 
        FROM pembayaranpelayanan_t ppt
        JOIN tandabuktibayar_t tbt ON tbt.tandabuktibayar_id = ppt.tandabuktibayar_id
        JOIN closingkasir_t ct ON ct.closingkasir_id = tbt.closingkasir_id
        WHERE DATE(ct.tglclosingkasir) BETWEEN :df AND :dt
    ) closed ON closed.pendaftaran_id = p.pendaftaran_id
    WHERE rm.instalasi_id = 2
      AND p.tgl_pendaftaran::date BETWEEN :df AND :dt
      AND p.pasienbatalperiksa_id IS NULL
      AND p.pembayaranpelayanan_id IS NOT NULL
      AND closed.pendaftaran_id IS NULL
", [":df"=>$df,":dt"=>$dt])->queryScalar();
echo "Di Dashboard (398) TAPI BUKAN Laporan (391)   : {$diDashboardBukanLaporan} (Daftar Juli, Bayar, tp Closing Kasir di luar Juli 1-8 / belum closing)\n";

// 5. Ada di Laporan (391), tapi TIDAK ada di Dashboard (398)
// Ini artinya: Closing Juli, TAPI daftar di BUKAN Juli (Misal daftar 30 Juni, closing 1 Juli)
$diLaporanBukanDashboard = (int)$db->createCommand("
    SELECT COUNT(*) FROM (
        SELECT COUNT(1)
        FROM pembayaranpelayanan_t ppt
        JOIN pendaftaran_t pt ON pt.pendaftaran_id = ppt.pendaftaran_id
        JOIN tandabuktibayar_t tbt ON tbt.tandabuktibayar_id = ppt.tandabuktibayar_id
        JOIN closingkasir_t ct ON ct.closingkasir_id = tbt.closingkasir_id
        JOIN ruangan_m rm ON rm.ruangan_id = pt.ruangan_id
        JOIN instalasi_m im ON im.instalasi_id = rm.instalasi_id
        WHERE DATE(ct.tglclosingkasir) BETWEEN :df AND :dt
          AND im.instalasi_id = '2'
          AND pt.tgl_pendaftaran::date NOT BETWEEN :df AND :dt
        GROUP BY pt.tgl_pendaftaran, pt.no_pendaftaran, ppt.tglpembayaran, ppt.nopembayaran,
                 ct.tglclosingkasir, ct.closingkasir_no, pt.no_rekam_medik
    ) sub
", [":df"=>$df,":dt"=>$dt])->queryScalar();
echo "Di Laporan (391) TAPI BUKAN Dashboard (398)   : {$diLaporanBukanDashboard} (Closing Juli, tp daftar bulan Juni)\n";

