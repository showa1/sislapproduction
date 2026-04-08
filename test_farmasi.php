<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/config/web.php';
new yii\web\Application($config);

$db = Yii::$app->db;

ob_start();

$bulanIni = date('m');
$tahunIni = date('Y');

echo "--- TOTAL RESEP ---\n";
try {
    $q = "SELECT COUNT(DISTINCT pendaftaran_id) FROM obatalkespasien_t WHERE EXTRACT(MONTH FROM create_time) = :bulan AND EXTRACT(YEAR FROM create_time) = :tahun";
    print_r($db->createCommand($q)->bindValues([':bulan'=>$bulanIni, ':tahun'=>$tahunIni])->queryScalar());
} catch(Exception $e) { echo $e->getMessage(); }

echo "\n--- PENDAPATAN ---\n";
try {
    $q = "SELECT SUM(qty_oa * hargasatuan_oa) FROM obatalkespasien_t WHERE EXTRACT(MONTH FROM create_time) = :bulan AND EXTRACT(YEAR FROM create_time) = :tahun";
    print_r($db->createCommand($q)->bindValues([':bulan'=>$bulanIni, ':tahun'=>$tahunIni])->queryScalar());
} catch(Exception $e) { echo $e->getMessage(); }

echo "\n--- STOK MINIMAL ---\n";
try {
    $q = "SELECT COUNT(*) FROM (
            SELECT om.obatalkes_id, sum(COALESCE(st.qtystok_in,0) - COALESCE(st.qtystok_out,0)) AS stok, stm.jmlminimalstok
            FROM obatalkes_m om
            JOIN stokminimal_t stm ON stm.obatalkes_id = om.obatalkes_id
            LEFT JOIN stokobatalkes_t st ON om.obatalkes_id = st.obatalkes_id AND st.ruangan_id = stm.ruangan_id
            WHERE om.obatalkes_aktif = true
            GROUP BY om.obatalkes_id, stm.jmlminimalstok
          ) sub WHERE stok <= jmlminimalstok";
    print_r($db->createCommand($q)->queryScalar());
} catch(Exception $e) { echo $e->getMessage(); }

echo "\n--- OBAT EXPIRED < 90 HARI ---\n";
try {
    $q = "SELECT COUNT(*) FROM obatalkes_m WHERE obatalkes_aktif = true AND tglkadaluarsa IS NOT NULL AND tglkadaluarsa <= NOW() + INTERVAL '90 days'";
    print_r($db->createCommand($q)->queryScalar());
} catch(Exception $e) { echo $e->getMessage(); }

echo "\n--- TOP OBAT ---\n";
try {
    $q = "SELECT om.obatalkes_nama, SUM(opt.qty_oa) as total 
          FROM obatalkespasien_t opt 
          JOIN obatalkes_m om ON om.obatalkes_id = opt.obatalkes_id 
          WHERE EXTRACT(MONTH FROM opt.create_time) = :bulan AND EXTRACT(YEAR FROM opt.create_time) = :tahun 
          GROUP BY om.obatalkes_id, om.obatalkes_nama 
          ORDER BY total DESC LIMIT 5";
    print_r($db->createCommand($q)->bindValues([':bulan'=>$bulanIni, ':tahun'=>$tahunIni])->queryAll());
} catch(Exception $e) { echo $e->getMessage(); }

$out = ob_get_clean();
file_put_contents('db_test_farmasi.txt', $out);
echo "DONE";
