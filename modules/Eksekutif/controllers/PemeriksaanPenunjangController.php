<?php

namespace app\modules\eksekutif\controllers;

use app\controllers\BaseController;
use yii\data\SqlDataProvider;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yii;
use DateTime;
use DateInterval;

class PemeriksaanPenunjangController extends BaseController
{    

    public $dateFrom, $dateTo, $totalCount, $ruangan;

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
            'ruangan' =>  Yii::$app->request->get('ruangan'),
        ];

        return $this->render('index', [
            'dataProvider' => $this->dataprovider(),
            'listruangan' => $this->listruangan(),
            'dropdownselect' => $dropdownselect
        ]);

    }

    public function listruangan()
    {
        return [
            53 => 'Laboratorium',
            56 => 'Radiologi',
        ];
    }

    public function setupSearch()
    {
        $this->dateFrom = Yii::$app->request->get('date_from');
        $this->dateTo = Yii::$app->request->get('date_to');
        $this->ruangan = Yii::$app->request->get('ruangan');
        
        if (!empty($this->dateFrom)) {
            $this->dateFrom = DateTime::createFromFormat('d-m-Y', $this->dateFrom)->format('Y-m-d');
        }
        
        if (!empty($this->dateTo)) {
            $this->dateTo = DateTime::createFromFormat('d-m-Y', $this->dateTo)->format('Y-m-d');
        }

        $cari = Yii::$app->request->get('cari');
        
        $this->statuscari = !empty($cari) ? true : false;
    }

    public function dataprovider()
    {
        return new SqlDataProvider([
            'sql' => $this->statuscari ? $this->baseQuery() : $this->queryKosong(),
            'params' => $this->params,
            'totalCount' => $this->statuscari ? $this->countQuery() : 0,
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);

        // $command = Yii::$app->db->createCommand($a->sql, $a->params);
        // echo "SQL Query: " . $command->getRawSql(); die;
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
        $sheet->setCellValue('A2', 'Laporan Pemeriksaan Penunjang');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(18);
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(14);

        $sheet->setCellValue('A4', 'No');
        $sheet->setCellValue('B4', 'Kode Tindakan');
        $sheet->setCellValue('C4', 'Nama Tindakan');
        $sheet->setCellValue('D4', 'Jumlah');
        
        // Isi Data
        $row = 5; // Mulai dari baris kedua
        $i = 1;
        foreach ($models as $model) {
            $sheet->setCellValue('A' . $row, $i);
            $sheet->setCellValue('B' . $row, $model['daftartindakan_kode']);
            $sheet->setCellValue('C' . $row, $model['daftartindakan_nama']);
            $sheet->setCellValue('D' . $row, $model['jumlah']);
            
            $row++;
            $i++;
        }

        $sheet->getStyle('A4:D'.($row -1))->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $fileName = 'lap-pemeriksaan-penunjang.xlsx';
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
                daftartindakan_m.daftartindakan_kode,
                daftartindakan_m.daftartindakan_nama,
                sum (tindakanpelayanan_t.qty_tindakan) as jumlah
            FROM 
                tindakanpelayanan_t
            JOIN daftartindakan_m ON tindakanpelayanan_t.daftartindakan_id = daftartindakan_m.daftartindakan_id
            JOIN pasienmasukpenunjang_t ON tindakanpelayanan_t.pasienmasukpenunjang_id = pasienmasukpenunjang_t.pasienmasukpenunjang_id
            LEFT JOIN pasienbatalperiksa_r ON pasienmasukpenunjang_t.pasienmasukpenunjang_id = pasienbatalperiksa_r.pasienmasukpenunjang_id
            join pendaftaran_t pt on pt.pendaftaran_id = pasienmasukpenunjang_t.pendaftaran_id
            join pembayaranpelayanan_t ppt on ppt.pendaftaran_id = pt.pendaftaran_id 
            join tandabuktibayar_t tt on tt.pembayaranpelayanan_id = ppt.pembayaranpelayanan_id
            join closingkasir_t cs on cs.closingkasir_id = tt.closingkasir_id 
        ";

        $query =  $this->queryFilter($query);

        $query .= "
            GROUP BY 
                daftartindakan_m.daftartindakan_kode, 
                daftartindakan_m.daftartindakan_nama
        ";

        return $query;
    }

    public function queryFilter($query)
    {
        $query .= " WHERE pasienbatalperiksa_r.pasienbatalperiksa_id IS NULL 
            and date(tglclosingkasir) between :datefrom and :dateto 
            and tindakanpelayanan_t.ruangan_id = :ruangan ";
        $this->params = array_merge($this->params, [
            ':datefrom' => $this->dateFrom,
            ':dateto' => $this->dateTo,
            ':ruangan' => $this->ruangan
        ]);

        return $query;
    }

    public function countQuery()
    {
        $query = "
                select count(*) as total from (
                    select count(1)
                    FROM 
                        tindakanpelayanan_t
                    JOIN daftartindakan_m ON tindakanpelayanan_t.daftartindakan_id = daftartindakan_m.daftartindakan_id
                    JOIN pasienmasukpenunjang_t ON tindakanpelayanan_t.pasienmasukpenunjang_id = pasienmasukpenunjang_t.pasienmasukpenunjang_id
                    LEFT JOIN pasienbatalperiksa_r ON pasienmasukpenunjang_t.pasienmasukpenunjang_id = pasienbatalperiksa_r.pasienmasukpenunjang_id
                    join pendaftaran_t pt on pt.pendaftaran_id = pasienmasukpenunjang_t.pendaftaran_id
                    join pembayaranpelayanan_t ppt on ppt.pendaftaran_id = pt.pendaftaran_id 
                    join tandabuktibayar_t tt on tt.pembayaranpelayanan_id = ppt.pembayaranpelayanan_id
                    join closingkasir_t cs on cs.closingkasir_id = tt.closingkasir_id  
        ";

        $query = $this->queryFilter($query);

        $query .= "
            GROUP BY 
                daftartindakan_m.daftartindakan_kode, 
                daftartindakan_m.daftartindakan_nama
            ) as sub_total
        ";

        $command = Yii::$app->db->createCommand($query);
        $command->bindValue(':datefrom', $this->dateFrom);
        $command->bindValue(':dateto', $this->dateTo);
        $command->bindValue(':ruangan', $this->ruangan);
        
        return $command->queryScalar();
    }

}
