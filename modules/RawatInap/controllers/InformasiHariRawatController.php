<?php

namespace app\modules\RawatInap\controllers;

use app\controllers\BaseController;
use Yii;

class InformasiHariRawatController extends BaseController
{
    public function actionIndex()
    {
        $dateFrom = Yii::$app->request->get('date_from', date('Y-m-01'));
        $dateTo = Yii::$app->request->get('date_to', date('Y-m-d'));
        $caraBayar = Yii::$app->request->get('cara_bayar', '');
        $diagnosaFilter = Yii::$app->request->get('diagnosa', '');

        $sql = "
            SELECT
                pt.tgl_pendaftaran,
                pt.no_pendaftaran,
                cm.carabayar_nama,
                pm.no_rekam_medik,
                pm.nama_pasien,

                STRING_AGG(
                    DISTINCT dm.diagnosa_kode || ' - ' || dm.diagnosa_nama,
                    E'\n'
                ) AS diagnosa,

                STRING_AGG(
                    DISTINCT kr.kamarruangan_nokamar,
                    ' -> '
                    ORDER BY kr.kamarruangan_nokamar
                ) AS riwayat_kamar,

                pat.tgladmisi AS tgl_nginap,
                NOW() AS sampai_hari_ini,
                (NOW() - pat.tgladmisi) AS lama_dirawat

            FROM pendaftaran_t pt

            JOIN pasienadmisi_t pat
                ON pat.pasienadmisi_id = pt.pasienadmisi_id

            JOIN pasien_m pm
                ON pm.pasien_id = pt.pasien_id

            JOIN carabayar_m cm
                ON cm.carabayar_id = pt.carabayar_id

            LEFT JOIN pasienmorbiditas_t pst
                ON pst.pendaftaran_id = pt.pendaftaran_id

            LEFT JOIN diagnosa_m dm
                ON dm.diagnosa_id = pst.diagnosa_id

            LEFT JOIN masukkamar_t mk
                ON mk.pasienadmisi_id = pat.pasienadmisi_id

            LEFT JOIN kamarruangan_m kr
                ON kr.kamarruangan_id = mk.kamarruangan_id

            WHERE pat.tglpulang IS NULL
              AND pt.pasienbatalperiksa_id IS NULL
              AND pat.tgladmisi >= :df
              AND pat.tgladmisi <= :dt
        ";

        $params = [
            ':df' => $dateFrom . ' 00:00:00',
            ':dt' => $dateTo . ' 23:59:59'
        ];

        if (!empty($caraBayar)) {
            $sql .= " AND cm.carabayar_nama = :cb ";
            $params[':cb'] = $caraBayar;
        }

        $sql .= "
            GROUP BY
                pt.tgl_pendaftaran,
                pt.no_pendaftaran,
                cm.carabayar_nama,
                pm.no_rekam_medik,
                pm.nama_pasien,
                pat.tgladmisi
        ";

        if (!empty($diagnosaFilter)) {
            $sql .= " HAVING STRING_AGG(DISTINCT dm.diagnosa_kode || ' - ' || dm.diagnosa_nama, E'\n') ILIKE :diag ";
            $params[':diag'] = '%' . $diagnosaFilter . '%';
        }

        $sql .= " ORDER BY pat.tgladmisi ";

        $data = Yii::$app->db->createCommand($sql, $params)->queryAll();

        // Get options for Cara Bayar dropdown
        $optCaraBayar = Yii::$app->db->createCommand("
            SELECT DISTINCT carabayar_nama 
            FROM carabayar_m 
            ORDER BY carabayar_nama
        ")->queryColumn();

        return $this->render('index', [
            'data' => $data,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'caraBayar' => $caraBayar,
            'diagnosaFilter' => $diagnosaFilter,
            'optCaraBayar' => $optCaraBayar
        ]);
    }
}
