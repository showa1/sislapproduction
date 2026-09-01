<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';
$config = require __DIR__ . '/../config/web.php';
new yii\web\Application($config);
$db = Yii::$app->db;

$sql = "
    SELECT 
        om.obatalkes_nama, 
        om.obatalkes_kategori,
        COALESCE(SUM(st.qtystok_in - st.qtystok_out), 0) AS stok,
        MAX(st.create_time) AS last_trx
    FROM obatalkes_m om
    LEFT JOIN stokobatalkes_t st ON st.obatalkes_id = om.obatalkes_id
    WHERE om.obatalkes_aktif = true
    GROUP BY om.obatalkes_id, om.obatalkes_nama, om.obatalkes_kategori
    HAVING COALESCE(SUM(st.qtystok_in - st.qtystok_out), 0) = 0
    ORDER BY om.obatalkes_nama ASC
    LIMIT 20
";

try {
    $res = $db->createCommand($sql)->queryAll();
    echo "Found " . count($res) . " stock out items:\n";
    print_r(array_slice($res, 0, 5));
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
