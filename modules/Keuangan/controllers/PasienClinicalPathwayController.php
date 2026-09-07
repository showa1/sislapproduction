<?php

namespace app\modules\keuangan\controllers;

use app\controllers\BaseController;
use Yii;
use DateTime;
use yii\data\SqlDataProvider;

class PasienClinicalPathwayController extends BaseController
{
    public function actionIndex()
    {
        $request = Yii::$app->request;
        
        $dateFromRaw = $request->get('date_from');
        $dateToRaw = $request->get('date_to');
        $keyword = trim($request->get('q', ''));
        $pageSize = (int) $request->get('limit', 10);
        if (!in_array($pageSize, [10, 25, 50, 100])) {
            $pageSize = 10;
        }

        // Format tanggal jika diisi (d-m-Y atau Y-m-d)
        $dateFrom = null;
        $dateTo = null;

        if (!empty($dateFromRaw)) {
            $dt = DateTime::createFromFormat('d-m-Y', $dateFromRaw);
            $dateFrom = $dt ? $dt->format('Y-m-d') : $dateFromRaw;
        }
        if (!empty($dateToRaw)) {
            $dt = DateTime::createFromFormat('d-m-Y', $dateToRaw);
            $dateTo = $dt ? $dt->format('Y-m-d') : $dateToRaw;
        }

        // Parameter query
        $params = [];
        $whereConditions = ["pt.pasienbatalperiksa_id IS NULL"];

        if (!empty($dateFrom)) {
            $whereConditions[] = "DATE(pt.tgl_pendaftaran) >= :dateFrom";
            $params[':dateFrom'] = $dateFrom;
        }
        if (!empty($dateTo)) {
            $whereConditions[] = "DATE(pt.tgl_pendaftaran) <= :dateTo";
            $params[':dateTo'] = $dateTo;
        }
        if (!empty($keyword)) {
            $whereConditions[] = "(
                pm.no_rekam_medik ILIKE :kw 
                OR pm.nama_pasien ILIKE :kw 
                OR pt.no_pendaftaran ILIKE :kw
                OR kr.kamarruangan_nokamar ILIKE :kw
            )";
            $params[':kw'] = '%' . $keyword . '%';
        }

        $whereClause = implode(" AND ", $whereConditions);

        $db = Yii::$app->db;

        // Count Total Records
        $countSql = "
            SELECT COUNT(DISTINCT pt.pendaftaran_id)
            FROM pendaftaran_t pt
            JOIN pasien_m pm ON pm.pasien_id = pt.pasien_id
            LEFT JOIN pasienadmisi_t pat ON pat.pendaftaran_id = pt.pendaftaran_id
            LEFT JOIN masukkamar_t mk ON mk.pasienadmisi_id = pat.pasienadmisi_id
            LEFT JOIN kamarruangan_m kr ON kr.kamarruangan_id = mk.kamarruangan_id
            WHERE $whereClause
        ";
        $totalCount = (int) $db->createCommand($countSql, $params)->queryScalar();

        // Main SQL Query
        $sql = "
            SELECT 
                pt.pendaftaran_id,
                pat.tgladmisi,
                mk.tglmasukkamar,
                cmk.caramasuk_nama AS cara_masuk,
                pt.tgl_pendaftaran,
                pt.no_pendaftaran,
                pm.no_rekam_medik,
                pm.namadepan,
                pm.nama_pasien,
                pm.tanggal_lahir,
                cb.carabayar_nama,
                pj.penjamin_nama,
                doc_penerima.nama_pegawai AS dokter_penerima,
                dpjp.nama_pegawai AS dpjp_nama,
                kls.kelaspelayanan_nama,
                kp.jeniskasuspenyakit_nama,
                kr.kamarruangan_nokamar,
                kr.kamarruangan_nobed,
                COALESCE(tagihan.total_tagihan, 0) AS total_tagihan,
                COALESCE(ek.totalklaim, 0) AS total_klaim,
                80 AS persentase,
                ROUND(COALESCE(ek.totalklaim, 0) * 0.8, 2) AS total_pagu,
                ROUND((COALESCE(ek.totalklaim, 0) * 0.8) - COALESCE(tagihan.total_tagihan, 0), 2) AS selisih
            FROM pendaftaran_t pt
            JOIN pasien_m pm ON pm.pasien_id = pt.pasien_id
            LEFT JOIN pasienadmisi_t pat ON pat.pendaftaran_id = pt.pendaftaran_id
            LEFT JOIN (
                SELECT DISTINCT ON (pasienadmisi_id) pasienadmisi_id, tglmasukkamar, kamarruangan_id
                FROM masukkamar_t
                ORDER BY pasienadmisi_id, tglmasukkamar DESC
            ) mk ON mk.pasienadmisi_id = pat.pasienadmisi_id
            LEFT JOIN kamarruangan_m kr ON kr.kamarruangan_id = mk.kamarruangan_id
            LEFT JOIN caramasuk_m cmk ON cmk.caramasuk_id = pt.caramasuk_id
            LEFT JOIN carabayar_m cb ON cb.carabayar_id = COALESCE(pat.carabayar_id, pt.carabayar_id)
            LEFT JOIN penjaminpasien_m pj ON pj.penjamin_id = COALESCE(pat.penjamin_id, pt.penjamin_id)
            LEFT JOIN pegawai_m doc_penerima ON doc_penerima.pegawai_id = pat.dokterpenerima_id
            LEFT JOIN pegawai_m dpjp ON dpjp.pegawai_id = COALESCE(pat.pegawai_id, pt.pegawai_id)
            LEFT JOIN kelaspelayanan_m kls ON kls.kelaspelayanan_id = COALESCE(pat.kelaspelayanan_id, pt.kelaspelayanan_id)
            LEFT JOIN jeniskasuspenyakit_m kp ON kp.jeniskasuspenyakit_id = pt.jeniskasuspenyakit_id
            LEFT JOIN (
                SELECT pendaftaran_id, SUM(total_tarifakhir) AS total_tagihan
                FROM tindakanpelayanan_t
                GROUP BY pendaftaran_id
            ) tagihan ON tagihan.pendaftaran_id = pt.pendaftaran_id
            LEFT JOIN (
                SELECT pendaftaran_id, MAX(totalklaim) AS totalklaim
                FROM eklaim_v
                GROUP BY pendaftaran_id
            ) ek ON ek.pendaftaran_id = pt.pendaftaran_id
            WHERE $whereClause
            ORDER BY pt.tgl_pendaftaran DESC
        ";

        $dataProvider = new SqlDataProvider([
            'sql' => $sql,
            'params' => $params,
            'totalCount' => $totalCount,
            'pagination' => [
                'pageSize' => $pageSize,
                'pageParam' => 'page',
                'pageSizeParam' => 'limit',
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'totalCount' => $totalCount,
            'pageSize' => $pageSize,
            'keyword' => $keyword,
            'dateFrom' => $dateFromRaw,
            'dateTo' => $dateToRaw,
        ]);
    }
}
