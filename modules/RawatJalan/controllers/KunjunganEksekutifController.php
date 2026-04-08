<?php

namespace app\modules\rawatjalan\controllers;

use app\controllers\BaseController;
use yii\data\SqlDataProvider;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yii;
use DateTime;

class KunjunganEksekutifController extends BaseController
{    

    public $dateFrom, $dateTo, $totalCount;

    public $params = [];

    public $statuscari;

    /**
     * Displays patient list.
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
        
        if (empty($this->dateFrom)) {
            $this->dateFrom = date('d-m-Y', strtotime('-1 month'));
        }
        $this->dateFrom = DateTime::createFromFormat('d-m-Y', $this->dateFrom)->format('Y-m-d');      
        
        if (empty($this->dateTo)) {
            $this->dateTo = date('d-m-Y');
        }
        $this->dateTo = DateTime::createFromFormat('d-m-Y', $this->dateTo)->format('Y-m-d');      

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
    }

    public function actionExport()
    {
        $this->setupSearch();

        $dataProvider = new SqlDataProvider([
            'sql' => $this->statuscari ? $this->baseQuery() : $this->queryKosong(),
            'params' => $this->params,
            'pagination' => false,
        ]);
        
        $models = $dataProvider->getModels();
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'Rumah Sakit Priscilla Medical Center');
        $sheet->setCellValue('A2', 'Laporan Kunjungan Pasien Eksekutif');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(18);
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(14);
  
        $sheet->setCellValue('A4', 'No');
        $sheet->setCellValue('B4', 'Tanggal Pendaftaran');
        $sheet->setCellValue('C4', 'No Pendaftaran');
        $sheet->setCellValue('D4', 'No Rekam Medik');
        $sheet->setCellValue('E4', 'Nama Pasien');
        $sheet->setCellValue('F4', 'L/P');
        $sheet->setCellValue('G4', 'Tanggal Lahir');
        $sheet->setCellValue('H4', 'Nama Ruangan');
        $sheet->setCellValue('I4', 'Dokter');
        $sheet->setCellValue('J4', 'Cara Bayar');
        $sheet->setCellValue('K4', 'Penjamin');

        // Isi Data
        $row = 5;
        $i = 1;
        foreach ($models as $model) {
            $sheet->setCellValue('A' . $row, $i);
            $sheet->setCellValue('B' . $row, $model['tgl_pendaftaran']);
            $sheet->setCellValue('C' . $row, $model['no_pendaftaran']);
            $sheet->setCellValue('D' . $row, $model['no_rekam_medik']);
            $sheet->setCellValue('E' . $row, $model['namadepan'] . ' ' . $model['nama_pasien']);
            $sheet->setCellValue('F' . $row, $model['jeniskelamin']);
            $sheet->setCellValue('G' . $row, $model['tanggal_lahir']);
            $sheet->setCellValue('H' . $row, $model['ruangan_nama']);
            $sheet->setCellValue('I' . $row, $model['gelardepan'] . ' ' . $model['nama_pegawai']);
            $sheet->setCellValue('J' . $row, $model['carabayar_nama']);
            $sheet->setCellValue('K' . $row, $model['penjamin_nama']);
            $row++;
            $i++;
        }

        $sheet->getStyle('A4:K'.($row -1))->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        
        $writer = new Xlsx($spreadsheet);
        $fileName = 'lap-kunjungan-eksekutif.xlsx';
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
        return "SELECT pendaftaran_id FROM pendaftaran_t WHERE 1=0";
    }

    public function baseQuery()
    {
        $query =  "
            SELECT 
                pt.tgl_pendaftaran, 
                pt.no_pendaftaran, 
                pt.pasien_id,
                pm.no_rekam_medik, 
                pm.namadepan, 
                pm.nama_pasien, 
                pm.jeniskelamin,
                pm.tanggal_lahir,
                rm.ruangan_nama, 
                pg.gelardepan, 
                pg.nama_pegawai, 
                cb.carabayar_nama, 
                pj.penjamin_nama,
                vc.total_visit
            FROM pendaftaran_t pt
            JOIN pasien_m pm ON pm.pasien_id = pt.pasien_id
            JOIN ruangan_m rm ON rm.ruangan_id = pt.ruangan_id
            JOIN pegawai_m pg ON pg.pegawai_id = pt.pegawai_id
            LEFT JOIN carabayar_m cb ON cb.carabayar_id = pt.carabayar_id
            LEFT JOIN penjaminpasien_m pj ON pj.penjamin_id = pt.penjamin_id
            LEFT JOIN (
                SELECT p.pasien_id, COUNT(*) as total_visit
                FROM pendaftaran_t p
                JOIN ruangan_m r ON r.ruangan_id = p.ruangan_id
                WHERE r.is_eksekutif = true
                  AND p.pasienbatalperiksa_id IS NULL
                GROUP BY p.pasien_id
            ) vc ON vc.pasien_id = pt.pasien_id
        ";

        $query = $this->queryFilter($query);
       
        $query .= " ORDER BY pt.tgl_pendaftaran DESC";

        return $query;
    }

    public function queryFilter($query)
    {
        $query .= " 
            WHERE 
            pt.tgl_pendaftaran::date BETWEEN :datefrom AND :dateto
            AND rm.is_eksekutif = true
            AND pt.pasienbatalperiksa_id IS NULL
        ";

        $this->params = array_merge($this->params, [':datefrom' => $this->dateFrom, ':dateto' => $this->dateTo]);

        return $query;
    }

    public function countQuery()
    {
        $query = "
            SELECT COUNT(*)
            FROM pendaftaran_t pt
            JOIN pasien_m pm ON pm.pasien_id = pt.pasien_id
            JOIN ruangan_m rm ON rm.ruangan_id = pt.ruangan_id
            JOIN pegawai_m pg ON pg.pegawai_id = pt.pegawai_id
            LEFT JOIN carabayar_m cb ON cb.carabayar_id = pt.carabayar_id
            LEFT JOIN penjaminpasien_m pj ON pj.penjamin_id = pt.penjamin_id
        ";

        $query = $this->queryFilter($query);

        $command = Yii::$app->db->createCommand($query);
        $command->bindValue(':datefrom', $this->dateFrom);
        $command->bindValue(':dateto', $this->dateTo);
        
        return $command->queryScalar();
    }

    public function actionHistory($pasien_id)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        $db = Yii::$app->db;
        $history = $db->createCommand("
            SELECT 
                pt.tgl_pendaftaran, 
                pt.no_pendaftaran, 
                rm.ruangan_nama, 
                pg.nama_pegawai
            FROM pendaftaran_t pt
            JOIN ruangan_m rm ON rm.ruangan_id = pt.ruangan_id
            JOIN pegawai_m pg ON pg.pegawai_id = pt.pegawai_id
            WHERE pt.pasien_id = :pid
              AND rm.is_eksekutif = true
            ORDER BY pt.tgl_pendaftaran DESC
        ", [':pid' => $pasien_id])->queryAll();

        return [
            'success' => true,
            'data' => $history
        ];
    }

}
