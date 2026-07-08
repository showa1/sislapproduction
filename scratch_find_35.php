<?php
require __DIR__ . "/vendor/autoload.php";
require __DIR__ . "/vendor/yiisoft/yii2/Yii.php";
$config = require __DIR__ . "/config/web.php";
new yii\web\Application($config);
$db = Yii::$app->db;
$df = "2026-07-01"; $dt = "2026-07-08";

// Test 1: pendaftaran_t dengan instalasi_id IN (5,6) 
// (Tadi ini ada 6, jadi bukan)

// Test 2: pasienmasukpenunjang_t dimana tindakanpelayanan_t HANYA untuk tgl tertentu?
// Atau mungkin filter p.tgl_pendaftaran antara 1-8 Juli, BUKAN pmp.tglmasukpenunjang?
$tglDaftar = $db->createCommand("
    SELECT COUNT(*) FROM pasienmasukpenunjang_t pmp
    JOIN ruangan_m rm ON rm.ruangan_id = pmp.ruangan_id
    JOIN pendaftaran_t p ON p.pendaftaran_id = pmp.pendaftaran_id
    WHERE p.tgl_pendaftaran::date BETWEEN :df AND :dt
      AND rm.instalasi_id IN (5, 6)
")->queryScalar();
echo "Filter tgl pendaftaran : {$tglDaftar}\n";

// Test 3: Mungkin hanya status tertentu? SUDAH DI PERIKSA?
$sudahDiPeriksa = $db->createCommand("
    SELECT COUNT(*) FROM pasienmasukpenunjang_t pmp
    JOIN ruangan_m rm ON rm.ruangan_id = pmp.ruangan_id
    WHERE pmp.tglmasukpenunjang::date BETWEEN :df AND :dt
      AND rm.instalasi_id IN (5, 6)
      AND pmp.statusperiksa = 'SUDAH DI PERIKSA'
")->queryScalar();
echo "Filter SUDAH DI PERIKSA : {$sudahDiPeriksa}\n";

// Test 4: Laporan mungkin menggunakan date(tglmasukpenunjang) tapi instalasi_id yang dipilih HANYA laboratorium (5)?
$lab = $db->createCommand("
    SELECT COUNT(*) FROM pasienmasukpenunjang_t pmp
    JOIN ruangan_m rm ON rm.ruangan_id = pmp.ruangan_id
    WHERE pmp.tglmasukpenunjang::date BETWEEN :df AND :dt
      AND rm.instalasi_id = 5
")->queryScalar();
echo "Lab saja : {$lab}\n";

// Test 5: Rad saja
$rad = $db->createCommand("
    SELECT COUNT(*) FROM pasienmasukpenunjang_t pmp
    JOIN ruangan_m rm ON rm.ruangan_id = pmp.ruangan_id
    WHERE pmp.tglmasukpenunjang::date BETWEEN :df AND :dt
      AND rm.instalasi_id = 6
")->queryScalar();
echo "Rad saja : {$rad}\n";

// Test 6: Cari tabel yang menghasilkan persis 35
$query35 = $db->createCommand("
    SELECT COUNT(DISTINCT p.pendaftaran_id) FROM pendaftaran_t p
    JOIN pasienmasukpenunjang_t pmp ON pmp.pendaftaran_id = p.pendaftaran_id
    JOIN ruangan_m rm ON rm.ruangan_id = pmp.ruangan_id
    WHERE pmp.tglmasukpenunjang::date BETWEEN :df AND :dt
      AND rm.instalasi_id IN (5, 6)
")->queryScalar();
echo "DISTINCT pendaftaran_id (Tgl Masuk) : {$query35}\n";

$query35_2 = $db->createCommand("
    SELECT COUNT(DISTINCT p.pendaftaran_id) FROM pendaftaran_t p
    JOIN pasienmasukpenunjang_t pmp ON pmp.pendaftaran_id = p.pendaftaran_id
    JOIN ruangan_m rm ON rm.ruangan_id = pmp.ruangan_id
    WHERE p.tgl_pendaftaran::date BETWEEN :df AND :dt
      AND rm.instalasi_id IN (5, 6)
")->queryScalar();
echo "DISTINCT pendaftaran_id (Tgl Daftar) : {$query35_2}\n";

