<?php

namespace app\modules\Pendaftaran\controllers;

use Yii;
use app\controllers\BaseController;
use yii\data\SqlDataProvider;
use DateTime;

class PasienMeninggalController extends BaseController
{
    public function actionIndex()
    {
        $dateFromRaw = Yii::$app->request->get('date_from');
        $dateToRaw = Yii::$app->request->get('date_to');

        if (empty($dateFromRaw)) {
            $dateFromRaw = date('01-m-Y');
        }
        if (empty($dateToRaw)) {
            $dateToRaw = date('t-m-Y');
        }

        $dateFrom = DateTime::createFromFormat('d-m-Y', $dateFromRaw)->format('Y-m-d 00:00:00');
        $dateTo = DateTime::createFromFormat('d-m-Y', $dateToRaw)->format('Y-m-d 23:59:59');

        $count = Yii::$app->db->createCommand("
            SELECT COUNT(DISTINCT pm.no_rekam_medik)
            FROM pendaftaran_t pt
            JOIN pasien_m pm ON pt.pasien_id = pm.pasien_id
            JOIN ruangan_m rm ON pt.ruangan_id = rm.ruangan_id
            LEFT JOIN carabayar_m cb ON pt.carabayar_id = cb.carabayar_id
            LEFT JOIN penjaminpasien_m pj ON pt.penjamin_id = pj.penjamin_id
            LEFT JOIN pasienpulang_t pp ON pt.pendaftaran_id = pp.pendaftaran_id 
            LEFT JOIN kondisikeluar_m kk ON pp.kondisikeluar_id = kk.kondisikeluar_id 
            WHERE 
                pm.tgl_meninggal IS NOT NULL
                AND (kk.kondisikeluar_nama ILIKE '%MENINGGAL%' OR kk.kondisikeluar_nama ILIKE '%DEATH%')
                AND pm.tgl_meninggal BETWEEN :dateFrom AND :dateTo
                AND pt.pasienbatalperiksa_id IS NULL
        ", [':dateFrom' => $dateFrom, ':dateTo' => $dateTo])->queryScalar();

        $sql = "
            SELECT DISTINCT ON (pm.no_rekam_medik)
                pt.tgl_pendaftaran AS \"Tanggal Pendaftaran\",
                pt.no_pendaftaran AS \"No. Pendaftaran\",
                pm.tgl_meninggal AS \"Tanggal Meninggal\",
                pm.no_rekam_medik AS \"No. Rekam Medik\",
                pm.nama_pasien AS \"Nama Pasien / Panggilan\",
                kk.kondisikeluar_nama AS \"Kondisi Keluar\",
                rm.ruangan_nama AS \"Instalasi/Ruangan\",
                cb.carabayar_nama AS \"Cara Bayar\",
                pj.penjamin_nama AS \"Penjamin\"
            FROM pendaftaran_t pt
            JOIN pasien_m pm ON pt.pasien_id = pm.pasien_id
            JOIN ruangan_m rm ON pt.ruangan_id = rm.ruangan_id
            LEFT JOIN carabayar_m cb ON pt.carabayar_id = cb.carabayar_id
            LEFT JOIN penjaminpasien_m pj ON pt.penjamin_id = pj.penjamin_id
            LEFT JOIN pasienpulang_t pp ON pt.pendaftaran_id = pp.pendaftaran_id 
            LEFT JOIN kondisikeluar_m kk ON pp.kondisikeluar_id = kk.kondisikeluar_id 
            WHERE 
                pm.tgl_meninggal IS NOT NULL
                AND (kk.kondisikeluar_nama ILIKE '%MENINGGAL%' OR kk.kondisikeluar_nama ILIKE '%DEATH%')
                AND pm.tgl_meninggal BETWEEN :dateFrom AND :dateTo
                AND pt.pasienbatalperiksa_id IS NULL 
            ORDER BY pm.no_rekam_medik, pt.tgl_pendaftaran DESC
        ";

        $dataProvider = new SqlDataProvider([
            'sql' => $sql,
            'params' => [':dateFrom' => $dateFrom, ':dateTo' => $dateTo],
            'totalCount' => $count,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'dateFrom' => $dateFromRaw,
            'dateTo' => $dateToRaw,
        ]);
    }

    public function actionExport()
    {
        // Simple export just to avoid error if clicked.
        // Or redirect to index for now if not implemented fully.
        Yii::$app->session->setFlash('warning', 'Export belum diimplementasikan untuk Data Pasien Meninggal.');
        return $this->redirect(['index', 'date_from' => Yii::$app->request->get('date_from'), 'date_to' => Yii::$app->request->get('date_to')]);
    }
}
