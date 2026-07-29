<?php

namespace app\modules\laboratorium\controllers;

use Yii;
use app\controllers\BaseController;
use yii\data\SqlDataProvider;

class RerataRespontimeController extends BaseController
{
    public function actionIndex()
    {
        $date_from = Yii::$app->request->get('date_from', date('Y-m-01'));
        $date_to = Yii::$app->request->get('date_to', date('Y-m-t'));

        // Build the SQL query
        $sql = "
            SELECT
                pm.no_rekam_medik,
                pm.nama_pasien,
                pt.no_pendaftaran,
                pmt.no_masukpenunjang,
                pkt.tgl_kirimpasien AS tanggal_order,
                pmt.tglmasukpenunjang AS tgl_verif,
                (pmt.tglmasukpenunjang - pkt.tgl_kirimpasien) AS response_time_interval,
                EXTRACT(EPOCH FROM (pmt.tglmasukpenunjang - pkt.tgl_kirimpasien)) / 60 AS response_time_minutes
            FROM pasienkirimkeunitlain_t pkt
            JOIN pasienmasukpenunjang_t pmt
                ON pmt.pasienmasukpenunjang_id = pkt.pasienmasukpenunjang_id
            JOIN hasilpemeriksaanlab_t hpt
                ON pmt.pasienmasukpenunjang_id = hpt.pasienmasukpenunjang_id
            JOIN pendaftaran_t pt
                ON pt.pendaftaran_id = pkt.pendaftaran_id
            JOIN pasien_m pm
                ON pm.pasien_id = pt.pasien_id
            WHERE DATE(hpt.tglhasilpemeriksaanlab) BETWEEN :date_from AND :date_to
        ";

        $countSql = "
            SELECT COUNT(*) 
            FROM pasienkirimkeunitlain_t pkt
            JOIN pasienmasukpenunjang_t pmt
                ON pmt.pasienmasukpenunjang_id = pkt.pasienmasukpenunjang_id
            JOIN hasilpemeriksaanlab_t hpt
                ON pmt.pasienmasukpenunjang_id = hpt.pasienmasukpenunjang_id
            JOIN pendaftaran_t pt
                ON pt.pendaftaran_id = pkt.pendaftaran_id
            JOIN pasien_m pm
                ON pm.pasien_id = pt.pasien_id
            WHERE DATE(hpt.tglhasilpemeriksaanlab) BETWEEN :date_from AND :date_to
        ";

        $totalCount = Yii::$app->db->createCommand($countSql, [
            ':date_from' => $date_from,
            ':date_to' => $date_to,
        ])->queryScalar();

        $dataProvider = new SqlDataProvider([
            'sql' => $sql,
            'params' => [
                ':date_from' => $date_from,
                ':date_to' => $date_to,
            ],
            'totalCount' => $totalCount,
            'pagination' => [
                'pageSize' => 10,
            ],
            'sort' => [
                'attributes' => [
                    'tanggal_order',
                    'response_time_minutes',
                ],
                'defaultOrder' => ['tanggal_order' => SORT_DESC]
            ],
        ]);

        // Calculate average
        $avgSql = "
            SELECT AVG(EXTRACT(EPOCH FROM (pmt.tglmasukpenunjang - pkt.tgl_kirimpasien)) / 60) AS avg_response_time_minutes
            FROM pasienkirimkeunitlain_t pkt
            JOIN pasienmasukpenunjang_t pmt
                ON pmt.pasienmasukpenunjang_id = pkt.pasienmasukpenunjang_id
            JOIN hasilpemeriksaanlab_t hpt
                ON pmt.pasienmasukpenunjang_id = hpt.pasienmasukpenunjang_id
            WHERE DATE(hpt.tglhasilpemeriksaanlab) BETWEEN :date_from AND :date_to
        ";
        
        $averageMinutes = Yii::$app->db->createCommand($avgSql, [
            ':date_from' => $date_from,
            ':date_to' => $date_to,
        ])->queryScalar();

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'date_from' => $date_from,
            'date_to' => $date_to,
            'averageMinutes' => $averageMinutes,
        ]);
    }
}
