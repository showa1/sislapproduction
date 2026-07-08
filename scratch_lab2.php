<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';
$config = require __DIR__ . '/config/web.php';
new yii\web\Application($config);
$db = Yii::$app->db;

ob_start();

// Cari semua tabel yang relevan
echo "=== TABEL: penjamin / rujukan / asuransi ===\n";
$tables = $db->createCommand(
    "SELECT table_name FROM information_schema.tables 
     WHERE table_schema='public' 
     AND (table_name LIKE '%penjamin%' OR table_name LIKE '%rujukan%' OR table_name LIKE '%asuransi%')
     ORDER BY table_name"
)->queryAll();
print_r($tables);

// Cek asal rujukan
echo "\n=== asalrujukan (dari rujukan_t.asalrujukan_id) ===\n";
$asalTables = $db->createCommand(
    "SELECT table_name FROM information_schema.tables 
     WHERE table_schema='public' 
     AND (table_name LIKE '%asal%' OR table_name LIKE '%faskes%' OR table_name LIKE '%ppk%')
     ORDER BY table_name"
)->queryAll();
print_r($asalTables);

echo "\n=== Sample asalrujukan_m ===\n";
try { print_r($db->createCommand("SELECT * FROM asalrujukan_m LIMIT 5")->queryAll()); }
catch(\Exception $e) { echo $e->getMessage()."\n"; }

echo "\n=== Sample penjamin_t ===\n";
try { print_r($db->createCommand("SELECT * FROM penjamin_t LIMIT 3")->queryOne()); }
catch(\Exception $e) { echo $e->getMessage()."\n"; }

echo "\n=== Sample asuransipasien_t ===\n";
try { print_r($db->createCommand("SELECT * FROM asuransipasien_t LIMIT 1")->queryOne()); }
catch(\Exception $e) { echo $e->getMessage()."\n"; }

echo "\n=== Sample asuransi_m ===\n";
try { print_r($db->createCommand("SELECT * FROM asuransi_m LIMIT 3")->queryAll()); }
catch(\Exception $e) { echo $e->getMessage()."\n"; }

// Cek kolom di rujukan_t
echo "\n=== Kolom rujukan_t ===\n";
try {
    $cols = $db->createCommand(
        "SELECT column_name, data_type FROM information_schema.columns 
         WHERE table_schema='public' AND table_name='rujukan_t' ORDER BY ordinal_position"
    )->queryAll();
    print_r($cols);
} catch(\Exception $e) { echo $e->getMessage()."\n"; }

// Cek kolom di pasien_m
echo "\n=== Kolom pasien_m ===\n";
try {
    $cols = $db->createCommand(
        "SELECT column_name FROM information_schema.columns 
         WHERE table_schema='public' AND table_name='pasien_m' ORDER BY ordinal_position"
    )->queryAll();
    foreach ($cols as $c) echo "  " . $c['column_name'] . "\n";
} catch(\Exception $e) { echo $e->getMessage()."\n"; }

// Cek kolom di pendaftaran_t
echo "\n=== Kolom pendaftaran_t ===\n";
try {
    $cols = $db->createCommand(
        "SELECT column_name FROM information_schema.columns 
         WHERE table_schema='public' AND table_name='pendaftaran_t' ORDER BY ordinal_position"
    )->queryAll();
    foreach ($cols as $c) echo "  " . $c['column_name'] . "\n";
} catch(\Exception $e) { echo $e->getMessage()."\n"; }

// Cek kolom di tindakanpelayanan_t (khusus yang berkaitan penjamin)
echo "\n=== Cek penjamin_id di tindakanpelayanan_t ===\n";
try {
    $sample = $db->createCommand("SELECT DISTINCT penjamin_id FROM tindakanpelayanan_t WHERE penjamin_id IS NOT NULL LIMIT 5")->queryAll();
    print_r($sample);
} catch(\Exception $e) { echo $e->getMessage()."\n"; }

// Cek tabel dengan kolom penjamin_nama atau penjamin_id
echo "\n=== Tabel yang mengandung kolom 'penjamin_nama' ===\n";
try {
    $t = $db->createCommand(
        "SELECT table_name, column_name FROM information_schema.columns 
         WHERE table_schema='public' AND (column_name LIKE '%penjamin%')
         ORDER BY table_name, column_name"
    )->queryAll();
    print_r($t);
} catch(\Exception $e) { echo $e->getMessage()."\n"; }

$out = ob_get_clean();
file_put_contents('scratch_lab2_out.txt', $out);
echo "DONE. Lihat scratch_lab2_out.txt\n";
