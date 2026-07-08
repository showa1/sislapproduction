<?php
/**
 * FINAL QUERY: Pasien + Pemeriksaan Lab + Penjamin + Asal Rujukan
 * 
 * Sumber data utama:
 *   - rincianpemeriksaanlabrad_v  → data pasien + lab + penjamin (sudah terintegrasi)
 *   - rujukan_t                   → nomor & tanggal surat rujukan
 *   - asalrujukan_m               → nama institusi perujuk (Puskesmas, RS, Klinik, dll)
 *   - pendaftaran_t               → asal rujukan via rujukan_id
 *
 * Jalankan: php scratch_lab_final.php
 */
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';
$config = require __DIR__ . '/config/web.php';
new yii\web\Application($config);
$db = Yii::$app->db;

ob_start();

$bulan = date('m');
$tahun = date('Y');

// ============================================================
// QUERY UTAMA: Per-item pemeriksaan lab
// ============================================================
$sql_detail = "
SELECT
    -- ============== DATA PASIEN ==============
    v.pasien_id,
    v.no_rekam_medik                                AS no_rekam_medis,
    v.nama_pasien,
    v.jeniskelamin                                  AS jenis_kelamin,
    v.tanggal_lahir,
    v.umur,
    v.alamat_pasien,
    v.agama,
    v.statusperkawinan,
    v.no_telepon_pasien,
    v.no_identitas_pasien,
    v.jenisidentitas,
    v.warga_negara,

    -- ============== DATA KUNJUNGAN ==============
    v.pendaftaran_id,
    v.no_pendaftaran,
    v.tgl_pendaftaran,
    v.kunjungan,
    v.statusperiksa                                 AS status_periksa,

    -- ============== DATA PENJAMIN / ASURANSI ==============
    v.carabayar_id,
    v.carabayar_nama                                AS cara_bayar,
    v.penjamin_id,
    v.penjamin_nama,
    v.kelaspelayanan_nama                           AS kelas_pelayanan,
    v.no_asuransi,
    v.namapemilik_asuransi,
    v.namaperusahaan                                AS nama_perusahaan_asuransi,

    -- ============== ASAL RUJUKAN ==============
    v.no_masukpenunjang                             AS no_order_lab,
    pd.rujukan_id,
    r.no_rujukan                                    AS no_surat_rujukan,
    r.tanggal_rujukan                               AS tgl_surat_rujukan,
    r.nama_perujuk,
    r.diagnosa_rujukan,
    ar.asalrujukan_nama                             AS asal_rujukan,
    ar.asalrujukan_institusi                        AS jenis_institusi_perujuk,
    pd.jenis_rujukan,

    -- ============== DATA PEMERIKSAAN LAB ==============
    v.pasienmasukpenunjang_id,
    v.tglmasukpenunjang                             AS tgl_masuk_lab,
    v.tindakanpelayanan_id,
    v.tgl_tindakan,
    v.daftartindakan_kode                           AS kode_pemeriksaan,
    v.daftartindakan_nama                           AS nama_pemeriksaan,
    v.jenispemeriksaanlab_nama                      AS jenis_pemeriksaan,
    v.pemeriksaanlab_kode                           AS kode_item_lab,
    v.pemeriksaanlab_nama                           AS nama_item_lab,
    v.qty_tindakan,
    v.tarif_satuan,
    v.tarif_tindakan,
    v.iurbiaya_tindakan                             AS iur_biaya,
    v.subsidiasuransi_tindakan                      AS subsidi_asuransi,
    v.instalasi_nama,
    v.ruangan_nama,

    -- ============== DOKTER PEMERIKSA ==============
    CONCAT(v.gelardepan, ' ', v.nama_pegawai, ' ', v.gelarbelakang_nama) AS dokter_pemeriksa

FROM rincianpemeriksaanlabrad_v v

JOIN pendaftaran_t pd
    ON pd.pendaftaran_id = v.pendaftaran_id

LEFT JOIN rujukan_t r
    ON r.rujukan_id = pd.rujukan_id

LEFT JOIN asalrujukan_m ar
    ON ar.asalrujukan_id = r.asalrujukan_id

WHERE
    v.instalasi_nama ILIKE '%laborat%'
    AND EXTRACT(MONTH FROM v.tgl_tindakan) = :bulan
    AND EXTRACT(YEAR  FROM v.tgl_tindakan) = :tahun

ORDER BY v.tgl_tindakan DESC, v.pasienmasukpenunjang_id, v.pemeriksaanlab_urutan
";

echo "=== QUERY DETAIL (per-item pemeriksaan) ===\n";
echo "Filter: Bulan {$bulan} / {$tahun}\n\n";
try {
    $rows = $db->createCommand($sql_detail)
        ->bindValue(':bulan', $bulan)
        ->bindValue(':tahun', $tahun)
        ->queryAll();

    echo "Total rows: " . count($rows) . "\n\n";
    if (!empty($rows)) {
        echo "-- Sample row pertama --\n";
        print_r($rows[0]);
    }
} catch(\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

// ============================================================
// QUERY RINGKASAN: Per-pasien per-kunjungan lab (aggregat)
// ============================================================
$sql_ringkasan = "
SELECT
    -- Data Pasien
    v.no_rekam_medik                                AS no_rekam_medis,
    v.nama_pasien,
    v.jeniskelamin                                  AS jenis_kelamin,
    v.umur,

    -- Kunjungan
    v.no_pendaftaran,
    v.tgl_pendaftaran,
    v.no_masukpenunjang                             AS no_order_lab,
    v.tglmasukpenunjang                             AS tgl_masuk_lab,
    v.kunjungan,
    v.statusperiksa                                 AS status_periksa,

    -- Penjamin
    v.cara_bayar,
    v.penjamin_nama,
    v.kelas_pelayanan,
    v.no_asuransi,

    -- Asal Rujukan
    r.no_rujukan                                    AS no_surat_rujukan,
    ar.asalrujukan_nama                             AS asal_rujukan,
    pd.jenis_rujukan,

    -- Dokter
    CONCAT(v.gelardepan, ' ', v.nama_pegawai, ' ', v.gelarbelakang_nama) AS dokter_pemeriksa,

    -- Agregat Pemeriksaan
    COUNT(DISTINCT v.tindakanpelayanan_id)          AS jumlah_item_pemeriksaan,
    STRING_AGG(DISTINCT v.daftartindakan_nama, ', ' ORDER BY v.daftartindakan_nama) AS daftar_pemeriksaan,
    SUM(v.tarif_tindakan)                           AS total_tarif,
    SUM(v.iurbiaya_tindakan)                        AS total_iur_biaya,
    SUM(v.subsidiasuransi_tindakan)                 AS total_subsidi_asuransi

FROM (
    SELECT
        v.*,
        v.carabayar_nama AS cara_bayar,
        v.kelaspelayanan_nama AS kelas_pelayanan
    FROM rincianpemeriksaanlabrad_v v
    WHERE v.instalasi_nama ILIKE '%laborat%'
) v

JOIN pendaftaran_t pd ON pd.pendaftaran_id = v.pendaftaran_id

LEFT JOIN rujukan_t r ON r.rujukan_id = pd.rujukan_id

LEFT JOIN asalrujukan_m ar ON ar.asalrujukan_id = r.asalrujukan_id

WHERE
    EXTRACT(MONTH FROM v.tglmasukpenunjang) = :bulan
    AND EXTRACT(YEAR  FROM v.tglmasukpenunjang) = :tahun

GROUP BY
    v.pasien_id, v.no_rekam_medik, v.nama_pasien, v.jeniskelamin, v.umur,
    v.pendaftaran_id, v.no_pendaftaran, v.tgl_pendaftaran,
    v.no_masukpenunjang, v.tglmasukpenunjang, v.kunjungan, v.statusperiksa,
    v.carabayar_nama, v.penjamin_nama, v.kelaspelayanan_nama, v.no_asuransi,
    r.no_rujukan, ar.asalrujukan_nama, pd.jenis_rujukan, pd.rujukan_id,
    v.gelardepan, v.nama_pegawai, v.gelarbelakang_nama

ORDER BY v.tglmasukpenunjang DESC
";

echo "\n\n=== QUERY RINGKASAN (per-kunjungan lab) ===\n";
echo "Filter: Bulan {$bulan} / {$tahun}\n\n";
try {
    $rows2 = $db->createCommand($sql_ringkasan)
        ->bindValue(':bulan', $bulan)
        ->bindValue(':tahun', $tahun)
        ->queryAll();

    echo "Total kunjungan lab: " . count($rows2) . "\n\n";
    if (!empty($rows2)) {
        echo "-- Sample row pertama --\n";
        print_r($rows2[0]);
        echo "\n-- Beberapa baris teratas --\n";
        $headers = array_keys($rows2[0]);
        $show = ['no_rekam_medis','nama_pasien','no_order_lab','tgl_masuk_lab','cara_bayar','penjamin_nama','asal_rujukan','jenis_rujukan','daftar_pemeriksaan','total_tarif'];
        echo implode(' | ', $show) . "\n";
        echo str_repeat('-', 120) . "\n";
        foreach (array_slice($rows2, 0, 5) as $r) {
            $line = [];
            foreach ($show as $k) {
                $val = $r[$k] ?? '-';
                $line[] = substr((string)$val, 0, 20);
            }
            echo implode(' | ', $line) . "\n";
        }
    }
} catch(\Exception $e) {
    echo "ERROR Ringkasan: " . $e->getMessage() . "\n";
}

// ============================================================
// STATISTIK: Distribusi per Asal Rujukan
// ============================================================
$sql_stat = "
SELECT
    COALESCE(ar.asalrujukan_nama, 'Tanpa Rujukan')  AS asal_rujukan,
    pd.jenis_rujukan,
    v.carabayar_nama                                 AS cara_bayar,
    COUNT(DISTINCT v.pasienmasukpenunjang_id)         AS jumlah_kunjungan,
    COUNT(DISTINCT v.tindakanpelayanan_id)            AS jumlah_item,
    SUM(v.tarif_tindakan)                             AS total_tarif
FROM rincianpemeriksaanlabrad_v v
JOIN pendaftaran_t pd ON pd.pendaftaran_id = v.pendaftaran_id
LEFT JOIN rujukan_t r ON r.rujukan_id = pd.rujukan_id
LEFT JOIN asalrujukan_m ar ON ar.asalrujukan_id = r.asalrujukan_id
WHERE
    v.instalasi_nama ILIKE '%laborat%'
    AND EXTRACT(MONTH FROM v.tgl_tindakan) = :bulan
    AND EXTRACT(YEAR  FROM v.tgl_tindakan) = :tahun
GROUP BY ar.asalrujukan_nama, pd.jenis_rujukan, v.carabayar_nama
ORDER BY jumlah_kunjungan DESC
";

echo "\n\n=== STATISTIK: Distribusi Asal Rujukan Lab ===\n";
try {
    $stats = $db->createCommand($sql_stat)
        ->bindValue(':bulan', $bulan)
        ->bindValue(':tahun', $tahun)
        ->queryAll();
    printf("%-25s %-15s %-20s %15s %12s %15s\n",
        'Asal Rujukan', 'Jenis Rujukan', 'Cara Bayar', 'Kunjungan', 'Item', 'Total Tarif');
    echo str_repeat('-', 100) . "\n";
    foreach ($stats as $s) {
        printf("%-25s %-15s %-20s %15d %12d %15s\n",
            substr($s['asal_rujukan'] ?? '-', 0, 24),
            substr($s['jenis_rujukan'] ?? '-', 0, 14),
            substr($s['cara_bayar'] ?? '-', 0, 19),
            $s['jumlah_kunjungan'],
            $s['jumlah_item'],
            number_format($s['total_tarif'])
        );
    }
} catch(\Exception $e) {
    echo "ERROR Statistik: " . $e->getMessage() . "\n";
}

$out = ob_get_clean();
file_put_contents('scratch_lab_final_out.txt', $out);
echo "DONE. Output di scratch_lab_final_out.txt\n";
