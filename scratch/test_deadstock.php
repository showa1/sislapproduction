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
        SUM(st.qtystok_in - st.qtystok_out) as stok, 
        MAX(st.create_time) as last_trx,
        ROUND(EXTRACT(DAY FROM (NOW() - MAX(st.create_time)))) as hari_tidak_bergerak
    FROM stokobatalkes_t st 
    JOIN obatalkes_m om ON om.obatalkes_id = st.obatalkes_id 
    WHERE st.obatalkes_id NOT IN (
        SELECT DISTINCT obatalkes_id FROM stokobatalkes_t 
        WHERE create_time >= NOW() - INTERVAL '180 days'
    ) 
    GROUP BY om.obatalkes_id, om.obatalkes_nama, om.obatalkes_kategori 
    ORDER BY last_trx ASC 
    LIMIT 10
";

$res = $db->createCommand($sql)->queryAll();
foreach ($res as $i => $row) {
    echo sprintf(
        "%2d. %-35s | Kat: %-15s | Stok: %5d | Last: %s | Pasif: %4d hari\n",
        $i + 1,
        mb_strimwidth($row['obatalkes_nama'], 0, 35, '...'),
        mb_strimwidth($row['obatalkes_kategori'] ?? '-', 0, 15, '...'),
        $row['stok'],
        date('d/m/Y', strtotime($row['last_trx'])),
        $row['hari_tidak_bergerak']
    );
}
