<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/config/web.php';
(new yii\web\Application($config));

$schema = Yii::$app->db->schema;
$tables = ['pendaftaran_t', 'pasien_m', 'ruangan_m', 'instalasi_m', 'carabayar_m'];
foreach ($tables as $t) {
    echo "TABLE: $t\n";
    $s = $schema->getTableSchema($t);
    if ($s) {
        foreach($s->columns as $c) {
             echo " - " . $c->name . " \n";
        }
    }
}
