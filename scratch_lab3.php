<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';
$config = require __DIR__ . '/config/web.php';
new yii\web\Application($config);
$db = Yii::$app->db;

ob_start();

// ============================================================
// Cek view-view yang sudah tersedia untuk lab
// ============================================================

echo "=== SAMPLE: rincianpemeriksaanlabrad_v ===\n";
try {
    $row = $db->createCommand("SELECT * FROM rincianpemeriksaanlabrad_v LIMIT 1")->queryOne();
    print_r($row);
    // Tampilkan kolom lengkap
    if ($row) {
        echo "\nKolom tersedia: " . implode(', ', array_keys($row)) . "\n";
    }
} catch(\Exception $e) { echo $e->getMessage()."\n"; }

echo "\n=== SAMPLE: rinciantagihapasienlaboratorium_v ===\n";
try {
    $row = $db->createCommand("SELECT * FROM rinciantagihapasienlaboratorium_v LIMIT 1")->queryOne();
    print_r($row);
    if ($row) {
        echo "\nKolom tersedia: " . implode(', ', array_keys($row)) . "\n";
    }
} catch(\Exception $e) { echo $e->getMessage()."\n"; }

echo "\n=== SAMPLE: rinciantagihapasienpenunjang_v ===\n";
try {
    $row = $db->createCommand("SELECT * FROM rinciantagihapasienpenunjang_v LIMIT 1")->queryOne();
    if ($row) echo "Kolom: " . implode(', ', array_keys($row)) . "\n";
} catch(\Exception $e) { echo $e->getMessage()."\n"; }

echo "\n=== SAMPLE: tanggunganpenjamin_m ===\n";
try {
    $row = $db->createCommand("SELECT * FROM tanggunganpenjamin_m LIMIT 3")->queryAll();
    print_r($row);
} catch(\Exception $e) { echo $e->getMessage()."\n"; }

echo "\n=== SAMPLE: asalrujukan_m ===\n";
try {
    $row = $db->createCommand("SELECT * FROM asalrujukan_m LIMIT 5")->queryAll();
    print_r($row);
} catch(\Exception $e) { echo $e->getMessage()."\n"; }

// Cek kolom di rujukan_t lebih detail
echo "\n=== Kolom rujukan_t ===\n";
try {
    $cols = $db->createCommand(
        "SELECT column_name, data_type FROM information_schema.columns 
         WHERE table_schema='public' AND table_name='rujukan_t' ORDER BY ordinal_position"
    )->queryAll();
    foreach ($cols as $c) echo "  {$c['column_name']} ({$c['data_type']})\n";
} catch(\Exception $e) { echo $e->getMessage()."\n"; }

// Cek sample full dari rincianpemeriksaanlabrad_v dengan filter bulan
echo "\n=== MAIN QUERY via VIEW rincianpemeriksaanlabrad_v ===\n";
$bulan = date('m');
$tahun = date('Y');
try {
    // Cek dulu kolom-kolomnya
    $cols = $db->createCommand(
        "SELECT column_name FROM information_schema.columns 
         WHERE table_schema='public' AND table_name='rincianpemeriksaanlabrad_v'
         ORDER BY ordinal_position"
    )->queryAll();
    $colNames = array_column($cols, 'column_name');
    echo "Semua kolom view: " . implode(', ', $colNames) . "\n\n";

    // Cek apakah ada kolom tanggal / tgl
    $tglCol = '';
    foreach (['tgl_tindakan','tgl_masuk','tanggal','tgl_pendaftaran','tglkunjungan'] as $c) {
        if (in_array($c, $colNames)) { $tglCol = $c; break; }
    }
    echo "Kolom tanggal ditemukan: {$tglCol}\n";

    if ($tglCol) {
        $sample = $db->createCommand(
            "SELECT * FROM rincianpemeriksaanlabrad_v 
             WHERE EXTRACT(MONTH FROM {$tglCol}) = :bulan
             AND EXTRACT(YEAR FROM {$tglCol}) = :tahun
             LIMIT 3"
        )->bindValue(':bulan', $bulan)->bindValue(':tahun', $tahun)->queryAll();
        echo "Sample rows: " . count($sample) . "\n";
        if (!empty($sample)) print_r($sample[0]);
    } else {
        $sample = $db->createCommand("SELECT * FROM rincianpemeriksaanlabrad_v LIMIT 2")->queryAll();
        print_r($sample);
    }
} catch(\Exception $e) { echo "ERROR: " . $e->getMessage()."\n"; }

$out = ob_get_clean();
file_put_contents('scratch_lab3_out.txt', $out);
echo "DONE. Lihat scratch_lab3_out.txt\n";
