<?php

namespace app\modules\Pendaftaran\controllers;

use Yii;
use app\controllers\BaseController;
use yii\data\SqlDataProvider;
use DateTime;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use yii\web\Response;

class PasienMeninggalController extends BaseController
{
    private function parseDateRange($dateFromRaw, $dateToRaw)
    {
        if (empty($dateFromRaw)) {
            $dateFromRaw = date('01-m-Y');
        }
        if (empty($dateToRaw)) {
            $dateToRaw = date('t-m-Y');
        }

        $dtFrom = DateTime::createFromFormat('d-m-Y', $dateFromRaw);
        if (!$dtFrom) {
            $dtFrom = DateTime::createFromFormat('Y-m-d', $dateFromRaw);
        }
        if (!$dtFrom) {
            $dtFrom = DateTime::createFromFormat('Y-m-d', date('Y-m-01'));
            $dateFromRaw = date('01-m-Y');
        }

        $dtTo = DateTime::createFromFormat('d-m-Y', $dateToRaw);
        if (!$dtTo) {
            $dtTo = DateTime::createFromFormat('Y-m-d', $dateToRaw);
        }
        if (!$dtTo) {
            $dtTo = DateTime::createFromFormat('Y-m-d', date('Y-m-t'));
            $dateToRaw = date('t-m-Y');
        }

        return [
            'dateFromRaw' => $dateFromRaw,
            'dateToRaw' => $dateToRaw,
            'dateFromDb' => $dtFrom->format('Y-m-d 00:00:00'),
            'dateToDb' => $dtTo->format('Y-m-d 23:59:59'),
        ];
    }

    public function actionIndex()
    {
        $dates = $this->parseDateRange(
            Yii::$app->request->get('date_from'),
            Yii::$app->request->get('date_to')
        );

        $count = Yii::$app->db->createCommand("
            SELECT COUNT(DISTINCT pm.no_rekam_medik)
            FROM pendaftaran_t pt
            JOIN pasien_m pm ON pt.pasien_id = pm.pasien_id
            LEFT JOIN pasienpulang_t pp ON pt.pendaftaran_id = pp.pendaftaran_id 
            LEFT JOIN kondisikeluar_m kk ON pp.kondisikeluar_id = kk.kondisikeluar_id 
            WHERE 
                pm.tgl_meninggal IS NOT NULL
                AND (kk.kondisikeluar_nama ILIKE '%MENINGGAL%' OR kk.kondisikeluar_nama ILIKE '%DEATH%')
                AND pm.tgl_meninggal BETWEEN :dateFrom AND :dateTo
                AND pt.pasienbatalperiksa_id IS NULL
        ", [':dateFrom' => $dates['dateFromDb'], ':dateTo' => $dates['dateToDb']])->queryScalar();

        $sql = "
            SELECT DISTINCT ON (pm.no_rekam_medik)
                pt.tgl_pendaftaran AS \"Tanggal Pendaftaran\",
                pt.no_pendaftaran AS \"No. Pendaftaran\",
                pm.tgl_meninggal AS \"Tanggal Meninggal\",
                pm.no_rekam_medik AS \"No. Rekam Medik\",
                pm.nama_pasien AS \"Nama Pasien / Panggilan\",
                kk.kondisikeluar_nama AS \"Kondisi Keluar\",
                COALESCE(rm_pp.ruangan_nama, rm_pa.ruangan_nama, rm_pt.ruangan_nama) AS \"Instalasi/Ruangan\",
                cb.carabayar_nama AS \"Cara Bayar\",
                pj.penjamin_nama AS \"Penjamin\"
            FROM pendaftaran_t pt
            JOIN pasien_m pm ON pt.pasien_id = pm.pasien_id
            LEFT JOIN pasienpulang_t pp ON pt.pendaftaran_id = pp.pendaftaran_id 
            LEFT JOIN kondisikeluar_m kk ON pp.kondisikeluar_id = kk.kondisikeluar_id 
            LEFT JOIN ruangan_m rm_pp ON pp.ruanganakhir_id = rm_pp.ruangan_id
            LEFT JOIN pasienadmisi_t pa ON pa.pendaftaran_id = pt.pendaftaran_id
            LEFT JOIN ruangan_m rm_pa ON pa.ruangan_id = rm_pa.ruangan_id
            LEFT JOIN ruangan_m rm_pt ON pt.ruangan_id = rm_pt.ruangan_id
            LEFT JOIN carabayar_m cb ON pt.carabayar_id = cb.carabayar_id
            LEFT JOIN penjaminpasien_m pj ON pt.penjamin_id = pj.penjamin_id
            WHERE 
                pm.tgl_meninggal IS NOT NULL
                AND (kk.kondisikeluar_nama ILIKE '%MENINGGAL%' OR kk.kondisikeluar_nama ILIKE '%DEATH%')
                AND pm.tgl_meninggal BETWEEN :dateFrom AND :dateTo
                AND pt.pasienbatalperiksa_id IS NULL 
            ORDER BY pm.no_rekam_medik, pt.tgl_pendaftaran DESC
        ";

        $dataProvider = new SqlDataProvider([
            'sql' => $sql,
            'params' => [':dateFrom' => $dates['dateFromDb'], ':dateTo' => $dates['dateToDb']],
            'totalCount' => $count,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'dateFrom' => $dates['dateFromRaw'],
            'dateTo' => $dates['dateToRaw'],
        ]);
    }

    public function actionExport()
    {
        $dates = $this->parseDateRange(
            Yii::$app->request->get('date_from'),
            Yii::$app->request->get('date_to')
        );

        $sql = "
            SELECT DISTINCT ON (pm.no_rekam_medik)
                pt.tgl_pendaftaran AS \"Tanggal Pendaftaran\",
                pt.no_pendaftaran AS \"No. Pendaftaran\",
                pm.tgl_meninggal AS \"Tanggal Meninggal\",
                pm.no_rekam_medik AS \"No. Rekam Medik\",
                pm.nama_pasien AS \"Nama Pasien / Panggilan\",
                kk.kondisikeluar_nama AS \"Kondisi Keluar\",
                COALESCE(rm_pp.ruangan_nama, rm_pa.ruangan_nama, rm_pt.ruangan_nama) AS \"Instalasi/Ruangan\",
                cb.carabayar_nama AS \"Cara Bayar\",
                pj.penjamin_nama AS \"Penjamin\"
            FROM pendaftaran_t pt
            JOIN pasien_m pm ON pt.pasien_id = pm.pasien_id
            LEFT JOIN pasienpulang_t pp ON pt.pendaftaran_id = pp.pendaftaran_id 
            LEFT JOIN kondisikeluar_m kk ON pp.kondisikeluar_id = kk.kondisikeluar_id 
            LEFT JOIN ruangan_m rm_pp ON pp.ruanganakhir_id = rm_pp.ruangan_id
            LEFT JOIN pasienadmisi_t pa ON pa.pendaftaran_id = pt.pendaftaran_id
            LEFT JOIN ruangan_m rm_pa ON pa.ruangan_id = rm_pa.ruangan_id
            LEFT JOIN ruangan_m rm_pt ON pt.ruangan_id = rm_pt.ruangan_id
            LEFT JOIN carabayar_m cb ON pt.carabayar_id = cb.carabayar_id
            LEFT JOIN penjaminpasien_m pj ON pt.penjamin_id = pj.penjamin_id
            WHERE 
                pm.tgl_meninggal IS NOT NULL
                AND (kk.kondisikeluar_nama ILIKE '%MENINGGAL%' OR kk.kondisikeluar_nama ILIKE '%DEATH%')
                AND pm.tgl_meninggal BETWEEN :dateFrom AND :dateTo
                AND pt.pasienbatalperiksa_id IS NULL 
            ORDER BY pm.no_rekam_medik, pt.tgl_pendaftaran DESC
        ";

        $rows = Yii::$app->db->createCommand($sql, [
            ':dateFrom' => $dates['dateFromDb'],
            ':dateTo' => $dates['dateToDb'],
        ])->queryAll();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'Rumah Sakit Priscilla Medical Center');
        $sheet->setCellValue('A2', 'Data Pasien Meninggal');
        $sheet->setCellValue('A3', 'Periode: ' . $dates['dateFromRaw'] . ' s/d ' . $dates['dateToRaw']);
        
        $sheet->getStyle('A1:A3')->getFont()->setBold(true);
        $sheet->getStyle('A1')->getFont()->setSize(16);
        $sheet->getStyle('A2')->getFont()->setSize(14);

        $headers = [
            'No',
            'Tanggal Pendaftaran',
            'No. Pendaftaran',
            'Tanggal Meninggal',
            'No. Rekam Medik',
            'Nama Pasien / Panggilan',
            'Kondisi Keluar',
            'Instalasi/Ruangan',
            'Cara Bayar',
            'Penjamin'
        ];

        $col = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($col . '5', $h);
            $col++;
        }

        $sheet->getStyle('A5:J5')->getFont()->setBold(true);
        $sheet->getStyle('A5:J5')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF002D72');
        $sheet->getStyle('A5:J5')->getFont()->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle('A5:J5')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $rowIdx = 6;
        $i = 1;
        foreach ($rows as $r) {
            $sheet->setCellValue('A' . $rowIdx, $i);
            $sheet->setCellValue('B' . $rowIdx, !empty($r['Tanggal Pendaftaran']) ? date('Y-m-d H:i:s', strtotime($r['Tanggal Pendaftaran'])) : '-');
            $sheet->setCellValueExplicit('C' . $rowIdx, $r['No. Pendaftaran'] ?? '-', DataType::TYPE_STRING);
            $sheet->setCellValue('D' . $rowIdx, !empty($r['Tanggal Meninggal']) ? date('Y-m-d H:i:s', strtotime($r['Tanggal Meninggal'])) : '-');
            $sheet->setCellValueExplicit('E' . $rowIdx, $r['No. Rekam Medik'] ?? '-', DataType::TYPE_STRING);
            $sheet->setCellValue('F' . $rowIdx, $r['Nama Pasien / Panggilan'] ?? '-');
            $sheet->setCellValue('G' . $rowIdx, $r['Kondisi Keluar'] ?? '-');
            $sheet->setCellValue('H' . $rowIdx, $r['Instalasi/Ruangan'] ?? '-');
            $sheet->setCellValue('I' . $rowIdx, $r['Cara Bayar'] ?? '-');
            $sheet->setCellValue('J' . $rowIdx, $r['Penjamin'] ?? '-');

            $rowIdx++;
            $i++;
        }

        $lastRow = max(5, $rowIdx - 1);
        $sheet->getStyle('A5:J' . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        foreach (range('A', 'J') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'Data_Pasien_Meninggal_' . date('Ymd_His') . '.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), 'export_pasien_meninggal');
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

