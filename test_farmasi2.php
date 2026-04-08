<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';
$config = require __DIR__ . '/config/web.php';
new yii\web\Application($config);
$db = Yii::$app->db;
ob_start();

echo "--- STOCK OUT (Stok = 0) ---\n";
try {
    $q = "SELECT COUNT(*) FROM (
        SELECT om.obatalkes_id, COALESCE(SUM(st.qtystok_in - st.qtystok_out), 0) AS stok
        FROM obatalkes_m om
        LEFT JOIN stokobatalkes_t st ON st.obatalkes_id = om.obatalkes_id
        WHERE om.obatalkes_aktif = true
        GROUP BY om.obatalkes_id
        HAVING COALESCE(SUM(st.qtystok_in - st.qtystok_out), 0) = 0
    ) sub";
    echo $db->createCommand($q)->queryScalar();
} catch(Exception $e) { echo $e->getMessage(); }

echo "\n--- DEAD STOCK (Tidak bergerak 180 hari) ---\n";
try {
    $q = "SELECT COUNT(DISTINCT st.obatalkes_id)
        FROM stokobatalkes_t st
        WHERE st.obatalkes_id NOT IN (
            SELECT DISTINCT obatalkes_id FROM stokobatalkes_t
            WHERE tgl_tr >= NOW() - INTERVAL '180 days'
        )";
    echo $db->createCommand($q)->queryScalar();
} catch(Exception $e) { echo "ERR: " . $e->getMessage(); }

// Try alternative column name for date
echo "\n--- DEAD STOCK (try with create_time) ---\n";
try {
    $q = "SELECT COUNT(DISTINCT st.obatalkes_id)
        FROM stokobatalkes_t st
        WHERE st.obatalkes_id NOT IN (
            SELECT DISTINCT obatalkes_id FROM stokobatalkes_t
            WHERE create_time >= NOW() - INTERVAL '180 days'
        )";
    echo $db->createCommand($q)->queryScalar();
} catch(Exception $e) { echo "ERR: " . $e->getMessage(); }

echo "\n--- STOKOBATALKES_T COLUMNS ---\n";
try {
    $cols = $db->createCommand("SELECT column_name FROM information_schema.columns WHERE table_name = 'stokobatalkes_t' ORDER BY ordinal_position")->queryAll();
    foreach($cols as $c) echo $c['column_name'] . "\n";
} catch(Exception $e) { echo $e->getMessage(); }

echo "\n--- NILAI ASET GUDANG ---\n";
try {
    $q = "SELECT SUM((st.qtystok_in - st.qtystok_out) * om.harga_satuan) 
            FROM stokobatalkes_t st
            JOIN obatalkes_m om ON om.obatalkes_id = st.obatalkes_id
            WHERE om.obatalkes_aktif = true";
    echo $db->createCommand($q)->queryScalar();
} catch(Exception $e) { echo "ERR: " . $e->getMessage(); }

echo "\n--- OBATALKES_M COLUMNS (looking for harga) ---\n";
try {
    $cols = $db->createCommand("SELECT column_name FROM information_schema.columns WHERE table_name = 'obatalkes_m' AND column_name LIKE '%harga%' ORDER BY ordinal_position")->queryAll();
    foreach($cols as $c) echo $c['column_name'] . "\n";
} catch(Exception $e) { echo $e->getMessage(); }

echo "\n--- DISTRIBUSI KATEGORI STOK ---\n";
try {
    $q = "SELECT om.obatalkes_kategori, COUNT(*) as jumlah_item
          FROM obatalkes_m om WHERE om.obatalkes_aktif = true
          GROUP BY om.obatalkes_kategori ORDER BY jumlah_item DESC LIMIT 10";
    print_r($db->createCommand($q)->queryAll());
} catch(Exception $e) { echo $e->getMessage(); }

echo "\n--- STATUS PO PENDING ---\n";
try {
    $q = "SELECT COUNT(*) FROM permintaanpembelian_t 
          WHERE batalpermintaanpembelian_id IS NULL 
          AND penerimaanbarang_id IS NULL";
    echo $db->createCommand($q)->queryScalar();
} catch(Exception $e) { echo "ERR: " . $e->getMessage(); }

echo "\n--- LEAD TIME (days avg) ---\n";
try {
    $q = "SELECT AVG(EXTRACT(DAY FROM (pb.tglterima - pp.tglpermintaanpembelian)))
          FROM permintaanpembelian_t pp
          JOIN penerimaanbarang_t pb ON pb.penerimaanbarang_id = pp.penerimaanbarang_id
          WHERE pp.tglpermintaanpembelian >= NOW() - INTERVAL '1 year'";
    echo round($db->createCommand($q)->queryScalar(), 1);
} catch(Exception $e) { echo "ERR: " . $e->getMessage(); }

echo "\n--- ACTIVITY LOG (last 5) ---\n";
try {
    $q = "SELECT st.create_time, om.obatalkes_nama, st.qtystok_in, st.qtystok_out
          FROM stokobatalkes_t st
          JOIN obatalkes_m om ON om.obatalkes_id = st.obatalkes_id
          ORDER BY st.create_time DESC LIMIT 5";
    print_r($db->createCommand($q)->queryAll());
} catch(Exception $e) { echo "ERR: " . $e->getMessage(); }

echo "\n--- STOK MENIPIS DETAIL (Top 10) ---\n";
try {
    $q = "SELECT om.obatalkes_nama, om.obatalkes_kategori,
                  sum(COALESCE(st.qtystok_in,0) - COALESCE(st.qtystok_out,0)) AS stok,
                  stm.jmlminimalstok
          FROM obatalkes_m om
          JOIN stokminimal_t stm ON stm.obatalkes_id = om.obatalkes_id
          LEFT JOIN stokobatalkes_t st ON om.obatalkes_id = st.obatalkes_id AND st.ruangan_id = stm.ruangan_id
          WHERE om.obatalkes_aktif = true
          GROUP BY om.obatalkes_nama, om.obatalkes_kategori, stm.jmlminimalstok
          HAVING sum(COALESCE(st.qtystok_in,0) - COALESCE(st.qtystok_out,0)) <= stm.jmlminimalstok
          ORDER BY stok ASC LIMIT 10";
    print_r($db->createCommand($q)->queryAll());
} catch(Exception $e) { echo "ERR: " . $e->getMessage(); }

$out = ob_get_clean();
file_put_contents('db_test_farmasi2.txt', $out);
echo "DONE";
