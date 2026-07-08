<?php
require 'vendor/autoload.php';
require 'vendor/yiisoft/yii2/Yii.php';
$config = require 'config/web.php';
(new yii\web\Application($config));

$tables = Yii::$app->db->getSchema()->getTableNames();
foreach ($tables as $table) {
    if (strpos($table, 'log') !== false || strpos($table, 'audit') !== false) {
        echo $table . "\n";
    }
}
