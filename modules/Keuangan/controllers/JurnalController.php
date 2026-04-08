<?php

namespace app\modules\keuangan\controllers;

use app\controllers\BaseController;
use yii\data\SqlDataProvider;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yii;
use DateTime;
use DateInterval;

class JurnalController extends BaseController
{    

    public $dateFrom, $dateTo, $totalCount;

    public $params = [];

    public $statuscari;

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {   
        $this->setupSearch();

        $dropdownselect = [
            'start' =>  Yii::$app->request->get('date_from'),
            'to' =>  Yii::$app->request->get('date_to'),
        ];

        return $this->render('index', [
            'dataProvider' => $this->dataprovider(),
            'dropdownselect' => $dropdownselect
        ]);

    }

    public function setupSearch()
    {
        $this->dateFrom = Yii::$app->request->get('date_from');
        $this->dateTo = Yii::$app->request->get('date_to');
        if (!empty($this->dateFrom)) {
            $this->dateFrom = DateTime::createFromFormat('d-m-Y', $this->dateFrom)->format('Y-m-d') . ' 00:00:00';
        }
        
        if (!empty($this->dateTo)) {
            $this->dateTo = DateTime::createFromFormat('d-m-Y', $this->dateTo)->format('Y-m-d') . ' 23:59:59';
        }

        $cari = Yii::$app->request->get('cari');
        
        $this->statuscari = !empty($cari) ? true : false;
    }

    public function dataprovider()
    {
        return new SqlDataProvider([
            'sql' => $this->statuscari ? $this->baseQuery() : $this->queryKosong(),
            'params' => $this->params,
            'totalCount' => 0,
            'pagination' => false
        ]);
    }

    public function actionExport()
    {
        $this->setupSearch();
        $dataProvider = new SqlDataProvider([
            'sql' => $this->statuscari ? $this->baseQuery() : $this->queryKosong(),
            'params' => $this->params,
            'pagination' => false,  // Disable pagination untuk ekspor semua data
        ]);
        
        $models = $dataProvider->getModels();
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'Rumah Sakit Priscilla Medical Center');
        $sheet->setCellValue('A2', 'Laporan Jurnal');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(18);
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(14);

        $sheet->setCellValue('A4', 'No');
        $sheet->setCellValue('B4', 'Tgl Bukti');
        $sheet->setCellValue('C4', 'Uraian');
        $sheet->setCellValue('D4', 'Kode Jurnal');
        $sheet->setCellValue('E4', 'Jenis Jurnal');
        $sheet->setCellValue('F4', 'Debit');
        $sheet->setCellValue('G4', 'Kredit');
        $sheet->setCellValue('H4', 'Selisih');

        $i = 1;
        $row = 5;
        foreach ($models as $model) {
            $sheet->setCellValue('A' . $row, $i);
            $sheet->setCellValue('B' . $row, $model['tglbuktijurnal']);
            $sheet->setCellValue('C' . $row, $model['urianjurnal']);
            $sheet->setCellValue('D' . $row, $model['kodejurnal']);
            $sheet->setCellValue('E' . $row, $model['jenisjurnal_nama']);
            $sheet->setCellValue('F' . $row, (float) $model['total_debit']);
            $sheet->setCellValue('G' . $row, (float) $model['total_kredit']);
            $sheet->setCellValue('H' . $row, (float) $model['selisih']);

            $row++;
            $i++;
        }

        $sheet->getStyle('F5:H' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('A4:H'.($row -1))->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $fileName = 'laporan-jurnal.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), $fileName);
        $writer->save($tempFile);

        return Yii::$app->response->sendFile($tempFile, $fileName)->on(
            \yii\web\Response::EVENT_AFTER_SEND,
            function ($event) {
                unlink($event->data); // Hapus file sementara setelah diunduh
            },
            $tempFile
        );

    }

    public function queryKosong()
    {
        return "Select tariftindakan_id from tariftindakan_m where 1=0";
    }

    public function baseQuery()
    {
        $query = "
            SELECT 
                jr.tglbuktijurnal,
                jr.urianjurnal,
                jr.kodejurnal,
                jm.jenisjurnal_nama,
                ROUND(SUM(jd.saldodebit)::numeric,2) AS total_debit,
                ROUND(SUM(jd.saldokredit)::numeric,2) AS total_kredit,
                ROUND((SUM(jd.saldodebit) - SUM(jd.saldokredit))::numeric,2) AS selisih
            FROM jurnaldetail_t jd
            JOIN (
                SELECT jurnalrekening_id, urianjurnal, kodejurnal, tglbuktijurnal, jenisjurnal_id
                FROM jurnalrekening_t
            ) jr ON jr.jurnalrekening_id = jd.jurnalrekening_id
            JOIN jenisjurnal_m jm 
                ON jm.jenisjurnal_id = jr.jenisjurnal_id
            WHERE jr.tglbuktijurnal::date BETWEEN :datefrom AND :dateto
            GROUP BY 
                jr.tglbuktijurnal,
                jr.urianjurnal,
                jr.kodejurnal,
                jm.jenisjurnal_nama
            HAVING ROUND(SUM(jd.saldodebit)::numeric,2) <> ROUND(SUM(jd.saldokredit)::numeric,2)
        ";

        $query =  $this->queryFilter($query);

        return $query;
    }

    public function queryFilter($query)
    {
        $this->params = array_merge($this->params, [':datefrom' => $this->dateFrom, ':dateto' => $this->dateTo]);

        return $query;
    }

}
