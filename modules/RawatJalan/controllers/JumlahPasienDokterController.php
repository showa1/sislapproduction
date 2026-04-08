<?php

namespace app\modules\rawatjalan\controllers;

use app\controllers\BaseController;
use yii\data\SqlDataProvider;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Yii;
use DateTime;

class JumlahPasienDokterController extends BaseController
{    

    public $dateFrom, $dateTo, $totalCount, $jenistarif;

    public $params = [];

    public $kelaspelayanan, $namatindakan, $statuscari;

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
        $sheet->setCellValue('A2', 'Laporan Jumlah Pasien Per Dokter');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(18);
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(14);

        $sheet->setCellValue('A4', 'No');
        $sheet->setCellValue('B4', 'Nama Pegawai');
        $sheet->setCellValue('C4', 'Jumlah Pasien');
        
        // Isi Data
        $row = 5; // Mulai dari baris kedua
        $i = 1;
        foreach ($models as $model) {
            $sheet->setCellValue('A' . $row, $i);
            $sheet->setCellValue('B' . $row, $model['nama_pegawai']);
            $sheet->setCellValue('C' . $row, $model['jumlahpasien']);

            $row++;
            $i++;
        }

        $sheet->getStyle('A4:C'.($row -1))->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        // Simpan file ke response
        
        $writer = new Xlsx($spreadsheet);
        $fileName = 'lap-jml-pasien-per-dokter.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), $fileName);
        $writer->save($tempFile);

        return Yii::$app->response->sendFile($tempFile, $fileName)->on(
            \yii\web\Response::EVENT_AFTER_SEND,
            function ($event) {
                unlink($event->data);
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
        $query =  "
            select nama_pegawai,
            count (pt.pasienadmisi_id) as Jumlahpasien
            from pasienadmisi_t pat
            join pendaftaran_t pt on pt.pasien_id = pat.pasien_id
            join pegawai_m pm on pm.pegawai_id = pt.pegawai_id 
        ";

        $query = $this->queryFilter($query);
       
        $query .= "
            group by nama_pegawai
        ";

        return $query;

    }

    public function queryFilter($query)
    {

        $query .= " where
                    pasienbatalperiksa_id is null and pt.pasienadmisi_id is not null
                    and date(tgl_pendaftaran)  between :datefrom and :dateto
                    and jabatan_id  = '23' 
                ";

        $this->params = array_merge($this->params, [':datefrom' => $this->dateFrom, ':dateto' => $this->dateTo]);

        return $query;
    }

    public function countQuery()
    {
        $query = "
            select count(*) as total from (
                select count(1)
                from pasienadmisi_t pat
                join pendaftaran_t pt on pt.pasien_id = pat.pasien_id
                join pegawai_m pm on pm.pegawai_id = pt.pegawai_id
        ";

        $query = $this->queryFilter($query);

        $query .= " 
                group by nama_pegawai
                ) as sub_total";


        $command = Yii::$app->db->createCommand($query);
        $command->bindValue(':datefrom', $this->dateFrom);
        $command->bindValue(':dateto', $this->dateTo);
        
        return $command->queryScalar();
    }

}
