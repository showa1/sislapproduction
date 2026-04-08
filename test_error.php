<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/config/web.php';

// disable web request requirement
$config['components']['request']['class'] = '\yii\console\Request';
$config['components']['response']['class'] = '\yii\console\Response';

// Initialize the application
$app = new yii\web\Application($config);

// Mock the query parameters
$_GET['filter_type'] = 'custom';
$_GET['start_date'] = '2026-03-01';
$_GET['end_date'] = '2026-03-10';

try {
    $controller = new \app\modules\keuangan\controllers\AnalisisKunjunganController('analisis-kunjungan', $app->getModule('keuangan'));
    $controller->actionIndex();
    echo "SUCCESS\n";
} catch (\Exception $e) {
    echo "EXCEPTION CAUGHT:\n";
    echo $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
} catch (\Throwable $e) {
    echo "ERROR CAUGHT:\n";
    echo $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
