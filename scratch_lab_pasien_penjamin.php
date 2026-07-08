<?php
/**
 * Scratch: Eksplorasi query pasien + pemeriksaan lab + penjamin/asal rujukan
 * Jalankan dari root project: php scratch_lab_pasien_penjamin.php
 */
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/config/web.php';
new yii\web\Application($config);

$db = Yii::$app->db;

ob_start();

// ============================================================
// 1. Cek tabel-tabel yang relevan
// ============================================================
echo "=== TABEL RELEVAN ===\n";
$tables = [
    'pasien_m',
    'pendaftaran_t',
    'pasienmasukpenunjang_t',
    'tindakanpelayanan_t',
    'hasilpemeriksaanlab_t',
    'detailhasilpemeriksaanlab_t',
    'penjamin_m',
    'carabayar_m',
    'rujukan_m',
    'instalasi_m',
    'ruangan_m',
    'asuransipasien_t',
];
foreach ($tables as $tbl) {
    try {
        $count = $db->createCommand("SELECT COUNT(*) FROM {$tbl}")->queryScalar();
        echo "  [{$tbl}] => {$count} rows\n";
    } catch (\Exception $e) {
        echo "  [{$tbl}] => ERROR: " . $e->getMessage() . "\n";
    }
}

// ============================================================
// 2. Sample struktur pasien_m
// ============================================================
echo "\n=== SAMPLE: pasien_m ===\n";
try {
    print_r($db->createCommand("SELECT * FROM pasien_m LIMIT 1")->queryOne());
} catch (\Exception $e) { echo $e->getMessage(); }

// ============================================================
// 3. Sample struktur pendaftaran_t
// ============================================================
echo "\n=== SAMPLE: pendaftaran_t ===\n";
try {
    print_r($db->createCommand("SELECT * FROM pendaftaran_t LIMIT 1")->queryOne());
} catch (\Exception $e) { echo $e->getMessage(); }

// ============================================================
// 4. Sample struktur pasienmasukpenunjang_t (lab masuk)
// ============================================================
echo "\n=== SAMPLE: pasienmasukpenunjang_t ===\n";
try {
    print_r($db->createCommand("SELECT * FROM pasienmasukpenunjang_t LIMIT 1")->queryOne());
} catch (\Exception $e) { echo $e->getMessage(); }

// ============================================================
// 5. Sample struktur hasilpemeriksaanlab_t
// ============================================================
echo "\n=== SAMPLE: hasilpemeriksaanlab_t ===\n";
try {
    print_r($db->createCommand("SELECT * FROM hasilpemeriksaanlab_t LIMIT 1")->queryOne());
} catch (\Exception $e) { echo $e->getMessage(); }

// ============================================================
// 6. Sample struktur tindakanpelayanan_t (order lab via tindakan)
// ============================================================
echo "\n=== SAMPLE: tindakanpelayanan_t (lab) ===\n";
try {
    $labInstalasi = $db->createCommand(
        "SELECT instalasi_id FROM instalasi_m WHERE LOWER(instalasi_nama) LIKE '%laborat%' LIMIT 1"
    )->queryScalar();
    echo "instalasi_id laboratorium: {$labInstalasi}\n";
    if ($labInstalasi) {
        print_r($db->createCommand(
            "SELECT * FROM tindakanpelayanan_t WHERE instalasi_id = :ins LIMIT 1"
        )->bindValue(':ins', $labInstalasi)->queryOne());
    }
} catch (\Exception $e) { echo $e->getMessage(); }

// ============================================================
// 7. Sample penjamin_m
// ============================================================
echo "\n=== SAMPLE: penjamin_m ===\n";
try {
    print_r($db->createCommand("SELECT * FROM penjamin_m LIMIT 5")->queryAll());
} catch (\Exception $e) { echo $e->getMessage(); }

// ============================================================
// 8. Sample carabayar_m
// ============================================================
echo "\n=== SAMPLE: carabayar_m ===\n";
try {
    print_r($db->createCommand("SELECT * FROM carabayar_m LIMIT 10")->queryAll());
} catch (\Exception $e) { echo $e->getMessage(); }

// ============================================================
// 9. Sample rujukan_m / rujukan_t
// ============================================================
echo "\n=== SAMPLE: rujukan_m ===\n";
try {
    print_r($db->createCommand("SELECT * FROM rujukan_m LIMIT 3")->queryOne());
} catch (\Exception $e) { echo $e->getMessage() . "\n"; }

echo "\n=== SAMPLE: rujukan_t ===\n";
try {
    print_r($db->createCommand("SELECT * FROM rujukan_t LIMIT 1")->queryOne());
} catch (\Exception $e) { echo $e->getMessage() . "\n"; }

// ============================================================
// 10. MAIN QUERY: Pasien + Pemeriksaan Lab + Penjamin + Rujukan
// ============================================================
echo "\n=== MAIN QUERY: Lab + Pasien + Penjamin + Rujukan ===\n";
$bulan = date('m');
$tahun = date('Y');

$sql = "
SELECT
    -- Data Pasien
    p.pasien_id,
    p.norm               AS no_rekam_medis,
    p.pasien_nama        AS nama_pasien,
    p.tgllahir           AS tgl_lahir,
    p.jeniskelamin       AS jenis_kelamin,
    p.alamat             AS alamat_pasien,

    -- Data Pendaftaran / Kunjungan
    pd.pendaftaran_id,
    pd.no_pendaftaran,
    pd.tgl_pendaftaran,
    pd.statusperiksa,
    pd.jenis_rujukan,

    -- Penjamin (Cara Bayar / Asuransi)
    cb.carabayar_nama    AS cara_bayar,
    pj.penjamin_nama     AS nama_penjamin,

    -- Asal Rujukan
    r.rujukan_id,
    r.asal_rujukan       AS faskes_perujuk,
    r.no_surat_rujukan,
    r.tgl_rujukan,

    -- Data Pemeriksaan Lab (via tindakan)
    tp.tindakanpelayanan_id,
    tp.tgl_tindakan,
    dt.daftartindakan_nama AS nama_pemeriksaan,
    tp.qty_tindakan,
    tp.tarif_satuan        AS tarif_pemeriksaan,
    tp.total_tarifakhir    AS total_tarif,

    -- Instalasi / Ruangan
    ins.instalasi_nama,
    ru.ruangan_nama        AS ruang_periksa

FROM tindakanpelayanan_t tp

JOIN pendaftaran_t pd
    ON pd.pendaftaran_id = tp.pendaftaran_id

JOIN pasien_m p
    ON p.pasien_id = pd.pasien_id

LEFT JOIN carabayar_m cb
    ON cb.carabayar_id = tp.carabayar_id

LEFT JOIN penjamin_m pj
    ON pj.penjamin_id = tp.penjamin_id

LEFT JOIN rujukan_t r
    ON r.rujukan_id = pd.rujukan_id

LEFT JOIN daftartindakan_m dt
    ON dt.daftartindakan_id = tp.daftartindakan_id

LEFT JOIN instalasi_m ins
    ON ins.instalasi_id = tp.instalasi_id

LEFT JOIN ruangan_m ru
    ON ru.ruangan_id = tp.ruangan_id

WHERE
    ins.instalasi_nama ILIKE '%laborat%'
    AND EXTRACT(MONTH FROM tp.tgl_tindakan) = :bulan
    AND EXTRACT(YEAR  FROM tp.tgl_tindakan) = :tahun

ORDER BY tp.tgl_tindakan DESC
LIMIT 10
";

try {
    $rows = $db->createCommand($sql)
        ->bindValue(':bulan', $bulan)
        ->bindValue(':tahun', $tahun)
        ->queryAll();
    echo "Total rows (sample): " . count($rows) . "\n";
    if (!empty($rows)) {
        print_r($rows[0]); // tampilkan 1 baris sebagai sampel struktur
    }
} catch (\Exception $e) {
    echo "ERROR main query: " . $e->getMessage() . "\n";

    // Fallback: coba via pasienmasukpenunjang_t
    echo "\n--- Fallback via pasienmasukpenunjang_t ---\n";
    $sql2 = "
    SELECT
        pm.pasienmasukpenunjang_id,
        pm.pendaftaran_id,
        pm.tgl_masuk,
        pm.statusperiksa,
        p.norm             AS no_rekam_medis,
        p.pasien_nama      AS nama_pasien,
        p.tgllahir,
        p.jeniskelamin,
        pd.no_pendaftaran,
        pd.tgl_pendaftaran,
        cb.carabayar_nama  AS cara_bayar,
        pj.penjamin_nama   AS nama_penjamin,
        r.asal_rujukan     AS faskes_perujuk,
        r.no_surat_rujukan,
        ins.instalasi_nama
    FROM pasienmasukpenunjang_t pm
    JOIN pendaftaran_t pd ON pd.pendaftaran_id = pm.pendaftaran_id
    JOIN pasien_m p ON p.pasien_id = pd.pasien_id
    LEFT JOIN carabayar_m cb ON cb.carabayar_id = pd.carabayar_id
    LEFT JOIN penjamin_m pj ON pj.penjamin_id = pd.penjamin_id
    LEFT JOIN rujukan_t r ON r.rujukan_id = pd.rujukan_id
    LEFT JOIN instalasi_m ins ON ins.instalasi_id = pm.instalasi_id
    WHERE ins.instalasi_nama ILIKE '%laborat%'
    AND EXTRACT(MONTH FROM pm.tgl_masuk) = :bulan
    AND EXTRACT(YEAR  FROM pm.tgl_masuk) = :tahun
    LIMIT 10
    ";
    try {
        $rows2 = $db->createCommand($sql2)
            ->bindValue(':bulan', $bulan)
            ->bindValue(':tahun', $tahun)
            ->queryAll();
        echo "Total rows fallback: " . count($rows2) . "\n";
        if (!empty($rows2)) print_r($rows2[0]);
    } catch (\Exception $e2) {
        echo "ERROR fallback: " . $e2->getMessage() . "\n";
    }
}

$out = ob_get_clean();
file_put_contents('scratch_lab_pasien_penjamin_out.txt', $out);
echo "DONE. Output di scratch_lab_pasien_penjamin_out.txt\n";
