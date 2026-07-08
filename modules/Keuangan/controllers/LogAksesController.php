<?php

namespace app\modules\keuangan\controllers;

use Yii;
use app\controllers\BaseController;
use yii\data\ArrayDataProvider;

class LogAksesController extends BaseController
{
    public function actionIndex()
    {
        $logFile = Yii::getAlias('@runtime/keuangan_access.log');
        $logs = [];

        if (file_exists($logFile)) {
            $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $decoded = json_encode(json_decode($line, true)); // Validasi JSON
                if ($decoded) {
                    $logs[] = json_decode($line, true);
                }
            }
        }

        // Urutkan dari yang terbaru
        usort($logs, function($a, $b) {
            return strcmp($b['waktu'], $a['waktu']);
        });

        $dataProvider = new ArrayDataProvider([
            'allModels' => $logs,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }
}
