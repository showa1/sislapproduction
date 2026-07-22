<?php

namespace app\modules\Pendaftaran\controllers;

use Yii;
use app\controllers\BaseController;

class DashboardController extends BaseController
{
    public function actionIndex()
    {
        $db = Yii::$app->db;
        $dateFrom = date('Y-m-01');
        $dateTo = date('Y-m-t');

        // Simple KPIs for Pendaftaran Dashboard
        $totalPasien = $db->createCommand("
            SELECT COUNT(DISTINCT pasien_id) 
            FROM pendaftaran_t 
            WHERE tgl_pendaftaran::date BETWEEN :df AND :dt
              AND pasienbatalperiksa_id IS NULL
        ", [':df' => $dateFrom, ':dt' => $dateTo])->queryScalar();

        $totalKunjungan = $db->createCommand("
            SELECT COUNT(pendaftaran_id) 
            FROM pendaftaran_t 
            WHERE tgl_pendaftaran::date BETWEEN :df AND :dt
              AND pasienbatalperiksa_id IS NULL
        ", [':df' => $dateFrom, ':dt' => $dateTo])->queryScalar();

        return $this->render('index', [
            'totalPasien' => $totalPasien,
            'totalKunjungan' => $totalKunjungan,
            'monthName' => date('F Y')
        ]);
    }
}
