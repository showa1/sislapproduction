<?php

namespace app\modules\Pendaftaran\controllers;

use Yii;
use app\controllers\BaseController;
use yii\data\SqlDataProvider;
use yii\helpers\ArrayHelper;
use yii\web\Response;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use DateTime;

class PenjaminPasienController extends BaseController
{
    private function buildQueryAndParams($request)
    {
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

        $dateFromObj = DateTime::createFromFormat('d-m-Y', $dateFromRaw);
        $dateToObj = DateTime::createFromFormat('d-m-Y', $dateToRaw);

        $dateFrom = $dateFromObj ? $dateFromObj->format('Y-m-d 00:00:00') : date('Y-m-01 00:00:00');
        $dateTo = $dateToObj ? $dateToObj->format('Y-m-d 23:59:59') : date('Y-m-t 23:59:59');

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

        return [
            'sql' => $sql,
            'countSql' => $countSql,
            'params' => $params,
            'dateFromRaw' => $dateFromRaw,
            'dateToRaw' => $dateToRaw,
            'carabayarId' => $carabayarId,
            'penjaminId' => $penjaminId,
            'instalasiId' => $instalasiId,
            'ruanganId' => $ruanganId,
            'namaPasien' => $namaPasien,
            'noRekamMedik' => $noRekamMedik,
        ];
    }

    public function actionIndex()
    {
        $request = Yii::$app->request;
        $q = $this->buildQueryAndParams($request);
        
        $count = Yii::$app->db->createCommand($q['countSql'], $q['params'])->queryScalar();

        $dataProvider = new SqlDataProvider([
            'sql' => $q['sql'],
            'params' => $q['params'],
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
            'dateFrom' => $q['dateFromRaw'],
            'dateTo' => $q['dateToRaw'],
            'carabayarId' => $q['carabayarId'],
            'penjaminId' => $q['penjaminId'],
            'instalasiId' => $q['instalasiId'],
            'ruanganId' => $q['ruanganId'],
            'namaPasien' => $q['namaPasien'],
            'noRekamMedik' => $q['noRekamMedik'],
            'carabayarList' => $carabayarList,
            'penjaminList' => $penjaminList,
            'instalasiList' => $instalasiList,
            'ruanganList' => $ruanganList,
        ]);
    }

    public function actionExport()
    {
        $request = Yii::$app->request;
        $q = $this->buildQueryAndParams($request);

        $data = Yii::$app->db->createCommand($q['sql'], $q['params'])->queryAll();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'Rumah Sakit Priscilla Medical Center');
        $sheet->setCellValue('A2', 'Laporan Penjamin Pasien');
        $sheet->setCellValue('A3', 'Periode: ' . $q['dateFromRaw'] . ' s/d ' . $q['dateToRaw']);

        $sheet->getStyle('A1:A3')->getFont()->setBold(true);
        $sheet->getStyle('A1')->getFont()->setSize(16);
        $sheet->getStyle('A2')->getFont()->setSize(14);

        $headers = [
            'No',
            'Tanggal Pendaftaran',
            'No Pendaftaran',
            'No Rekam Medik',
            'Nama Pasien',
            'Cara Bayar',
            'Penjamin',
            'Instalasi',
            'Ruangan',
            'Dokter DPJP',
            'Total Visit'
        ];

        $col = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($col . '5', $h);
            $col++;
        }

        $sheet->getStyle('A5:K5')->getFont()->setBold(true);
        $sheet->getStyle('A5:K5')->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF002D72');
        $sheet->getStyle('A5:K5')->getFont()->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle('A5:K5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $rowIdx = 6;
        $i = 1;
        foreach ($data as $r) {
            $sheet->setCellValue('A' . $rowIdx, $i);
            $sheet->setCellValue('B' . $rowIdx, !empty($r['Tanggal Pendaftaran']) ? date('d/m/Y H:i:s', strtotime($r['Tanggal Pendaftaran'])) : '-');
            $sheet->setCellValueExplicit('C' . $rowIdx, $r['No Pendaftaran'] ?? '-', DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('D' . $rowIdx, $r['No Rekam Medik'] ?? '-', DataType::TYPE_STRING);
            $sheet->setCellValue('E' . $rowIdx, $r['Nama Pasien'] ?? '-');
            $sheet->setCellValue('F' . $rowIdx, $r['Cara Bayar'] ?? '-');
            $sheet->setCellValue('G' . $rowIdx, $r['Penjamin'] ?? '-');
            $sheet->setCellValue('H' . $rowIdx, $r['Instalasi'] ?? '-');
            $sheet->setCellValue('I' . $rowIdx, $r['Ruangan'] ?? '-');
            $sheet->setCellValue('J' . $rowIdx, $r['Dokter DPJP'] ?? '-');
            $sheet->setCellValue('K' . $rowIdx, (int)($r['Total Visit'] ?? 0));

            $rowIdx++;
            $i++;
        }

        $lastRow = max(5, $rowIdx - 1);
        $sheet->getStyle('A5:K' . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        foreach (range('A', 'K') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'Laporan_Penjamin_Pasien_' . date('Ymd_His') . '.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), 'export_penjamin_pasien');
        $writer->save($tempFile);

        return Yii::$app->response->sendFile($tempFile, $fileName)->on(
            Response::EVENT_AFTER_SEND,
            function ($event) {
                if (file_exists($event->data)) {
                    @unlink($event->data);
                }
            },
            $tempFile
        );
    }
}

