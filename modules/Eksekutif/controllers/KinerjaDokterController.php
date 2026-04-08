<?php

namespace app\modules\eksekutif\controllers;

use app\controllers\BaseController;
use yii\data\SqlDataProvider;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use app\models\PegawaiM;
use Yii;
use DateTime;
use DateInterval;

class KinerjaDokterController extends BaseController
{    

    public $dateFrom, $dateTo, $totalCount, $dokter;

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
            'dokter' =>  $this->dokter,
        ];

        return $this->render('index', [
            'dataProvider' => $this->dataprovider(),
            'listdokter' => PegawaiM::dropdown(),
            'dropdownselect' => $dropdownselect
        ]);

    }

    public function setupSearch()
    {
        $this->dateFrom = Yii::$app->request->get('date_from');
        $this->dateTo = Yii::$app->request->get('date_to');
        $this->dokter = Yii::$app->request->get('dokter');

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
        $sheet->setCellValue('A2', 'Laporan Kinerja Dokter');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(18);
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(14);

        $sheet->setCellValue('A4', 'No');
        $sheet->setCellValue('B4', 'Pegawai ID');
        $sheet->setCellValue('C4', 'Nama Pegawai');
        $sheet->setCellValue('D4', 'Tanggal');
        $sheet->setCellValue('E4', 'Jumlah Pagi');
        $sheet->setCellValue('F4', 'Jumlah Siang');
        $sheet->setCellValue('G4', 'Jumlah Malam');
        $sheet->setCellValue('H4', 'Jumlah Pasien');
        $sheet->setCellValue('I4', 'Waktu Awal');
        $sheet->setCellValue('J4', 'Waktu Akhir');
        $sheet->setCellValue('K4', 'Durasi');

        // Isi Data
        $row = 5; // Mulai dari baris kedua
        $i = 1;
        foreach ($models as $model) {
            $sheet->setCellValue('A' . $row, $i);
            $sheet->setCellValue('B' . $row, $model['pegawai_id']);
            $sheet->setCellValue('C' . $row, $model['nama_pegawai']);
            $sheet->setCellValue('D' . $row, $model['tanggal']);
            $sheet->setCellValue('E' . $row, $model['jumlah_pagi']);
            $sheet->setCellValue('F' . $row, $model['jumlah_siang']);
            $sheet->setCellValue('G' . $row, $model['jumlah_malam']);
            $sheet->setCellValue('H' . $row, $model['jumlah_pasien']);
            $sheet->setCellValue('I' . $row, $model['waktu_awal']);
            $sheet->setCellValue('J' . $row, $model['waktu_akhir']);
            $sheet->setCellValue('K' . $row, $this->durationToExcelTime($model['durasi']));
            
            $sheet->getStyle("K". $row)
                ->getNumberFormat()
                ->setFormatCode('[h]:mm:ss');

            $row++;
            $i++;
        }

        $sheet->getStyle('A4:K'.($row -1))->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $fileName = 'Lap-Kinerja-Dokter.xlsx';
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

    public function durationToExcelTime($duration) {
        if (preg_match('/(\d+)\s+days?\s+(\d{2}):(\d{2}):(\d{2})/', $duration, $m)) {
            $days    = (int)$m[1];
            $hours   = (int)$m[2];
            $minutes = (int)$m[3];
            $seconds = (int)$m[4];
            $totalSeconds = $days * 86400 + $hours * 3600 + $minutes * 60 + $seconds;
        } elseif (preg_match('/^(\d{2}):(\d{2}):(\d{2})$/', $duration, $m)) {
            $hours   = (int)$m[1];
            $minutes = (int)$m[2];
            $seconds = (int)$m[3];
            $totalSeconds = $hours * 3600 + $minutes * 60 + $seconds;
        } else {
            $totalSeconds = 0;
        }

        return $totalSeconds / 86400;
    }

    public function baseQuery()
    {
        $query = "                
            SELECT 
                pm.pegawai_id,
                pm.nama_pegawai,
                pp.tgl_pendaftaran::date AS tanggal,
                COUNT(DISTINCT pp.pendaftaran_id) FILTER (
                    WHERE pp.tgl_pendaftaran::time BETWEEN '07:00:00' AND '12:00:00'
                ) AS jumlah_pagi,
                COUNT(DISTINCT pp.pendaftaran_id) FILTER (
                    WHERE pp.tgl_pendaftaran::time BETWEEN '12:00:01' AND '17:00:00'
                ) AS jumlah_siang,
                COUNT(DISTINCT pp.pendaftaran_id) FILTER (
                    WHERE pp.tgl_pendaftaran::time BETWEEN '17:00:01' AND '22:00:00'
                ) AS jumlah_malam,
                COUNT(DISTINCT pp.pendaftaran_id) AS jumlah_pasien,
                MIN(t.create_time) AS waktu_awal,
                MAX(t.create_time) AS waktu_akhir,
                (MAX(t.create_time) - MIN(t.create_time)) AS durasi
            FROM waktutunggupelayanan_t t
            JOIN pendaftaran_t pp 
                ON pp.pendaftaran_id = t.pendaftaran_id
            JOIN pasien_m ps 
                ON ps.pasien_id = t.pasien_id
            JOIN pegawai_m pm 
                ON pm.pegawai_id = pp.pegawai_id
        ";

        $query =  $this->queryFilter($query);

        $query .= "
            GROUP BY 
                pm.pegawai_id, 
                pm.nama_pegawai,
                pp.tgl_pendaftaran::date
            ORDER BY 
                tanggal ASC
        ";

        return $query;
    }

    public function queryFilter($query)
    {
        $query .= " WHERE pp.tgl_pendaftaran::date 
                BETWEEN :datefrom AND :dateto
                AND task_id = '5' 
                AND pp.pasienbatalperiksa_id IS NULL ";
        
        if(!empty($this->dokter)) {
            $query .= " AND pm.pegawai_id = :dokter ";
        }

        $this->params = array_merge($this->params, [':datefrom' => $this->dateFrom, ':dateto' => $this->dateTo]);

        if (!empty($this->dokter)) {
            $this->params = array_merge($this->params, [':dokter' => $this->dokter]);
        }

        return $query;
    }

    public function countQuery()
    {
        $query = "
                select count(*) as total from (
                    select count(1)
                    FROM waktutunggupelayanan_t t
                    JOIN pendaftaran_t pp 
                        ON pp.pendaftaran_id = t.pendaftaran_id
                    JOIN pasien_m ps 
                        ON ps.pasien_id = t.pasien_id
                    JOIN pegawai_m pm 
                        ON pm.pegawai_id = pp.pegawai_id
        ";

        $query = $this->queryFilter($query);

        $query .= "
            GROUP BY 
                pm.pegawai_id, 
                pm.nama_pegawai,
                pp.tgl_pendaftaran::date
            ) as sub_total
        ";

        $command = Yii::$app->db->createCommand($query);
        $command->bindValue(':datefrom', $this->dateFrom);
        $command->bindValue(':dateto', $this->dateTo);

        if (!empty($this->dokter)) {
            $command->bindValue(':dokter', $this->dokter);
        }
        
        return $command->queryScalar();
    }

}
