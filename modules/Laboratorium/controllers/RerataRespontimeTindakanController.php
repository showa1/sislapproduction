<?php

namespace app\modules\laboratorium\controllers;

use Yii;
use app\controllers\BaseController;
use yii\data\SqlDataProvider;

class RerataRespontimeTindakanController extends BaseController
{
    public function actionIndex()
    {
        $date_from = Yii::$app->request->get('date_from', date('Y-m-01'));
        $date_to = Yii::$app->request->get('date_to', date('Y-m-t'));

        $sql = "
            SELECT
                dm.daftartindakan_kode,
                dm.daftartindakan_nama,
                COUNT(*) AS jumlah_pemeriksaan,
                TO_CHAR(
                    AVG(pmt.tglmasukpenunjang - pkt.tgl_kirimpasien),
                    'HH24:MI:SS'
                ) AS rata_response_time
            FROM pasienkirimkeunitlain_t pkt
            JOIN pasienmasukpenunjang_t pmt
                ON pmt.pasienmasukpenunjang_id = pkt.pasienmasukpenunjang_id
            JOIN hasilpemeriksaanlab_t hpt
                ON pmt.pasienmasukpenunjang_id = hpt.pasienmasukpenunjang_id
            JOIN tindakanpelayanan_t td
                ON td.pasienmasukpenunjang_id = pmt.pasienmasukpenunjang_id
            JOIN daftartindakan_m dm
                ON dm.daftartindakan_id = td.daftartindakan_id
            WHERE DATE(hpt.tglhasilpemeriksaanlab) BETWEEN :date_from AND :date_to
            GROUP BY
                dm.daftartindakan_kode,
                dm.daftartindakan_nama
            ORDER BY
                dm.daftartindakan_kode
        ";

        $countSql = "
            SELECT COUNT(DISTINCT dm.daftartindakan_kode) 
            FROM pasienkirimkeunitlain_t pkt
            JOIN pasienmasukpenunjang_t pmt
                ON pmt.pasienmasukpenunjang_id = pkt.pasienmasukpenunjang_id
            JOIN hasilpemeriksaanlab_t hpt
                ON pmt.pasienmasukpenunjang_id = hpt.pasienmasukpenunjang_id
            JOIN tindakanpelayanan_t td
                ON td.pasienmasukpenunjang_id = pmt.pasienmasukpenunjang_id
            JOIN daftartindakan_m dm
                ON dm.daftartindakan_id = td.daftartindakan_id
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
                    'daftartindakan_kode' => [
                        'asc' => ['dm.daftartindakan_kode' => SORT_ASC],
                        'desc' => ['dm.daftartindakan_kode' => SORT_DESC],
                        'default' => SORT_ASC,
                    ],
                    'daftartindakan_nama',
                    'jumlah_pemeriksaan',
                    'rata_response_time',
                ],
                'defaultOrder' => ['daftartindakan_kode' => SORT_ASC]
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'date_from' => $date_from,
            'date_to' => $date_to,
        ]);
    }
}
