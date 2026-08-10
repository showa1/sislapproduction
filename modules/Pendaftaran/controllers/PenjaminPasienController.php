<?php

namespace app\modules\Pendaftaran\controllers;

use Yii;
use app\controllers\BaseController;
use yii\data\SqlDataProvider;
use yii\helpers\ArrayHelper;
use DateTime;

class PenjaminPasienController extends BaseController
{
    public function actionIndex()
    {
        $request = Yii::$app->request;
        
        // Get filter parameters
        $dateFromRaw = $request->get('date_from');
        $dateToRaw = $request->get('date_to');
        $carabayarId = $request->get('carabayar_id');
        $penjaminId = $request->get('penjamin_id');
        $instalasiId = $request->get('instalasi_id');
        $ruanganId = $request->get('ruangan_id');
        $namaPasien = $request->get('nama_pasien');
        $noRekamMedik = $request->get('no_rekam_medik');

        // Default dates to current month if empty
        if (empty($dateFromRaw)) {
            $dateFromRaw = date('01-m-Y');
        }
        if (empty($dateToRaw)) {
            $dateToRaw = date('t-m-Y');
        }

        $dateFrom = DateTime::createFromFormat('d-m-Y', $dateFromRaw)->format('Y-m-d 00:00:00');
        $dateTo = DateTime::createFromFormat('d-m-Y', $dateToRaw)->format('Y-m-d 23:59:59');

        // Build dynamic WHERE clause
        $where = [
            "pt.tgl_pendaftaran >= :dateFrom",
            "pt.tgl_pendaftaran <= :dateTo",
            "pt.pasienbatalperiksa_id IS NULL"
        ];
        
        $params = [
            ':dateFrom' => $dateFrom,
            ':dateTo' => $dateTo,
        ];

        if (!empty($carabayarId)) {
            $where[] = "COALESCE(pa.carabayar_id, pt.carabayar_id) = :carabayarId";
            $params[':carabayarId'] = $carabayarId;
        }

        if (!empty($penjaminId)) {
            $where[] = "COALESCE(pa.penjamin_id, pt.penjamin_id) = :penjaminId";
            $params[':penjaminId'] = $penjaminId;
        }

        if (!empty($instalasiId)) {
            $where[] = "COALESCE(insi.instalasi_id, ins.instalasi_id) = :instalasiId";
            $params[':instalasiId'] = $instalasiId;
        }

        if (!empty($ruanganId)) {
            $where[] = "COALESCE(rmi.ruangan_id, rm.ruangan_id) = :ruanganId";
            $params[':ruanganId'] = $ruanganId;
        }

        if (!empty($namaPasien)) {
            $where[] = "pm.nama_pasien ILIKE :namaPasien";
            $params[':namaPasien'] = "%" . $namaPasien . "%";
        }

        if (!empty($noRekamMedik)) {
            $where[] = "pm.no_rekam_medik ILIKE :noRekamMedik";
            $params[':noRekamMedik'] = "%" . $noRekamMedik . "%";
        }

        $whereSql = implode(' AND ', $where);

        // Subquery for total visit
        $subqueryVisit = "
            SELECT p.pasien_id, COUNT(*) as total_visit
            FROM pendaftaran_t p
            JOIN ruangan_m r ON r.ruangan_id = p.ruangan_id
            WHERE r.is_eksekutif = true
              AND p.pasienbatalperiksa_id IS NULL
            GROUP BY p.pasien_id
        ";

        // Query for counting total rows
        $countSql = "
            SELECT COUNT(*)
            FROM pendaftaran_t pt
            JOIN pasien_m pm ON pm.pasien_id = pt.pasien_id
            JOIN ruangan_m rm ON rm.ruangan_id = pt.ruangan_id
            JOIN instalasi_m ins ON ins.instalasi_id = rm.instalasi_id
            LEFT JOIN pasienadmisi_t pa ON pa.pendaftaran_id = pt.pendaftaran_id
            LEFT JOIN ruangan_m rmi ON rmi.ruangan_id = pa.ruangan_id
            LEFT JOIN instalasi_m insi ON insi.instalasi_id = rmi.instalasi_id
            WHERE $whereSql
        ";
        
        $count = Yii::$app->db->createCommand($countSql, $params)->queryScalar();

        // Main Query
        $sql = "
            SELECT 
                pt.tgl_pendaftaran AS \"Tanggal Pendaftaran\", 
                pt.no_pendaftaran AS \"No Pendaftaran\", 
                pm.no_rekam_medik AS \"No Rekam Medik\", 
                pm.nama_pasien AS \"Nama Pasien\", 
                cb.carabayar_nama AS \"Cara Bayar\", 
                pj.penjamin_nama AS \"Penjamin\",
                COALESCE(insi.instalasi_nama, ins.instalasi_nama) AS \"Instalasi\", 
                COALESCE(rmi.ruangan_nama, rm.ruangan_nama) AS \"Ruangan\", 
                COALESCE(pgi.nama_pegawai, pg.nama_pegawai) AS \"Dokter DPJP\", 
                COALESCE(vc.total_visit, 0) AS \"Total Visit\"
            FROM pendaftaran_t pt
            JOIN pasien_m pm ON pm.pasien_id = pt.pasien_id
            JOIN ruangan_m rm ON rm.ruangan_id = pt.ruangan_id
            JOIN instalasi_m ins ON ins.instalasi_id = rm.instalasi_id
            LEFT JOIN pegawai_m pg ON pg.pegawai_id = pt.pegawai_id 

            -- LEFT JOIN ke transaksi Admisi Rawat Inap
            LEFT JOIN pasienadmisi_t pa ON pa.pendaftaran_id = pt.pendaftaran_id
            LEFT JOIN ruangan_m rmi ON rmi.ruangan_id = pa.ruangan_id
            LEFT JOIN instalasi_m insi ON insi.instalasi_id = rmi.instalasi_id -- Instalasi dari Kamar Inap
            LEFT JOIN pegawai_m pgi ON pgi.pegawai_id = pa.pegawai_id -- DPJP Inap

            LEFT JOIN carabayar_m cb ON cb.carabayar_id = COALESCE(pa.carabayar_id, pt.carabayar_id)
            LEFT JOIN penjaminpasien_m pj ON pj.penjamin_id = COALESCE(pa.penjamin_id, pt.penjamin_id)

            LEFT JOIN ($subqueryVisit) vc ON vc.pasien_id = pt.pasien_id

            WHERE $whereSql
            ORDER BY pt.tgl_pendaftaran DESC
        ";

        $dataProvider = new SqlDataProvider([
            'sql' => $sql,
            'params' => $params,
            'totalCount' => $count,
            'pagination' => [
                'pageSize' => 10,
            ],
            'sort' => false,
        ]);

        // Dropdown Data
        $carabayarList = ArrayHelper::map(Yii::$app->db->createCommand("SELECT carabayar_id, carabayar_nama FROM carabayar_m ORDER BY carabayar_nama")->queryAll(), 'carabayar_id', 'carabayar_nama');
        $penjaminList = ArrayHelper::map(Yii::$app->db->createCommand("SELECT penjamin_id, penjamin_nama FROM penjaminpasien_m ORDER BY penjamin_nama")->queryAll(), 'penjamin_id', 'penjamin_nama');
        $instalasiList = ArrayHelper::map(Yii::$app->db->createCommand("SELECT instalasi_id, instalasi_nama FROM instalasi_m ORDER BY instalasi_nama")->queryAll(), 'instalasi_id', 'instalasi_nama');
        $ruanganList = ArrayHelper::map(Yii::$app->db->createCommand("SELECT ruangan_id, ruangan_nama FROM ruangan_m ORDER BY ruangan_nama")->queryAll(), 'ruangan_id', 'ruangan_nama');

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'dateFrom' => $dateFromRaw,
            'dateTo' => $dateToRaw,
            'carabayarId' => $carabayarId,
            'penjaminId' => $penjaminId,
            'instalasiId' => $instalasiId,
            'ruanganId' => $ruanganId,
            'namaPasien' => $namaPasien,
            'noRekamMedik' => $noRekamMedik,
            'carabayarList' => $carabayarList,
            'penjaminList' => $penjaminList,
            'instalasiList' => $instalasiList,
            'ruanganList' => $ruanganList,
        ]);
    }

    public function actionExport()
    {
        Yii::$app->session->setFlash('warning', 'Export belum diimplementasikan untuk Laporan Penjamin Pasien.');
        return $this->redirect(array_merge(['index'], Yii::$app->request->get()));
    }
}
