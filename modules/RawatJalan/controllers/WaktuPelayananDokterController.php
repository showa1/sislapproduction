<?php

namespace app\modules\rawatjalan\controllers;

use app\controllers\BaseController;
use yii\data\SqlDataProvider;
use app\models\JenistarifM;
use app\models\KelaspelayananM;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Yii;
use DateTime;

class WaktuPelayananDokterController extends BaseController
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
        
        if (empty($this->dateFrom)) {
            $this->dateFrom = date('d-m-Y');
        }
        $this->dateFrom = DateTime::createFromFormat('d-m-Y', $this->dateFrom)->format('Y-m-d') . ' 00:00:00';      
        
        if (empty($this->dateTo)) {
            $this->dateTo = date('d-m-Y');
        }
        $this->dateTo = DateTime::createFromFormat('d-m-Y', $this->dateTo)->format('Y-m-d') . ' 23:59:59';      

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
            'pagination' => false,  // Disable pagination untuk ekspor semua data
        ]);
        
        $models = $dataProvider->getModels();
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'Rumah Sakit Priscilla Medical Center');
        $sheet->setCellValue('A2', 'Laporan Waktu Pelayanan Dokter Per Pasien');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(18);
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(14);

        $sheet->setCellValue('A4', 'No');
        $sheet->setCellValue('B4', 'Tanggal Pendaftaran');
        $sheet->setCellValue('C4', 'No Pendaftaran');
        $sheet->setCellValue('D4', 'No Rekam medik');
        $sheet->setCellValue('E4', 'Nama Pasien');
        $sheet->setCellValue('F4', 'Nama Pegawai');
        $sheet->setCellValue('G4', 'Mulai Anamnesa');
        $sheet->setCellValue('H4', 'Selesai Pelayanan');
        $sheet->setCellValue('I4', 'Waktu Pelayanan');
        
        // Isi Data
        $row = 5; // Mulai dari baris kedua
        $i = 1;
        foreach ($models as $model) {
            $sheet->setCellValue('A' . $row, $i);
            $sheet->setCellValue('B' . $row, $model['tgl_pendaftaran']);
            $sheet->setCellValue('C' . $row, $model['no_pendaftaran']);
            $sheet->setCellValue('D' . $row, $model['no_rekam_medik']);
            $sheet->setCellValue('E' . $row, $model['nama_pasien']);
            $sheet->setCellValue('F' . $row, $model['gelardepan']. ' ' . $model['nama_pegawai'] . ' '. $model['gelarbelakang_nama']);
            $sheet->setCellValue('G' . $row, !empty($model['mulai_anamnesa']) ? $model['mulai_anamnesa'] : "");
            $sheet->setCellValue('H' . $row, !empty($model['selesai_pelayanan_poli']) ? $model['selesai_pelayanan_poli'] : "");
            // $sheet->setCellValue('I' . $row, !empty($model['waktu_pelayanan']) ? date('H:i:s', strtotime($model['waktu_pelayanan'])) : "");
            
            if(!empty($model['waktu_pelayanan'])) {
                list($hours, $minutes, $seconds) = explode(':', $model['waktu_pelayanan']);                
                $excelTime = Date::formattedPHPToExcel(1970, 1, 1, (int) $hours, (int) $minutes, (int) $seconds);
                $sheet->setCellValue("I{$row}", $excelTime);    
            }

            $row++;
            $i++;
        }

        $sheet->getStyle("I5:I".($row -1))->getNumberFormat()->setFormatCode('hh:mm:ss');

        $sheet->getStyle('A4:I'.($row -1))->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        // Simpan file ke response
        
        $writer = new Xlsx($spreadsheet);
        $fileName = 'laporan-penggunaan-obat-per-pasien.xlsx';
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
        $query =  "
            WITH Anamnesa AS (
                SELECT 
                    pendaftaran_id, 
                    MAX(waktutunggu_rs) AS Mulai_Anamnesa
                FROM 
                    waktutunggupelayanan_t
                WHERE 
                    task_id = 4
                GROUP BY 
                    pendaftaran_id
            ),
            PelayananPoli AS (
                SELECT 
                    pendaftaran_id, 
                    MAX(waktutunggu_rs) AS Selesai_Pelayanan_Poli
                FROM 
                    waktutunggupelayanan_t
                WHERE 
                    task_id = 5
                GROUP BY 
                    pendaftaran_id
            )
            SELECT
                p.pendaftaran_id, 
                no_pendaftaran, 
                tgl_pendaftaran, 
                no_rekam_medik, 
                nama_pasien, 
                nama_pegawai,
                gelardepan,
                gelarbelakang_nama,
                a.Mulai_Anamnesa,
                pp.Selesai_Pelayanan_Poli,
                (pp.Selesai_Pelayanan_Poli - a.Mulai_Anamnesa) AS waktu_pelayanan
            FROM 
                pendaftaran_t p
            JOIN 
                waktutunggupelayanan_t w ON w.pendaftaran_id = p.pendaftaran_id
            JOIN 
                pasien_m ps ON ps.pasien_id = p.pasien_id
            JOIN 
                pegawai_m pg ON pg.pegawai_id = p.pegawai_id
            JOIN 
                ruangan_m rm on rm.ruangan_id = p.ruangan_id
            LEFT JOIN 
                Anamnesa a ON a.pendaftaran_id = p.pendaftaran_id
            LEFT JOIN 
                PelayananPoli pp ON pp.pendaftaran_id = p.pendaftaran_id
            LEFT JOIN
                gelarbelakang_m gb ON gb.gelarbelakang_id = pg.gelarbelakang_id
            WHERE             
        ";

        $query = $this->queryFilter($query);

        $query .= "
            GROUP BY 
                p.pendaftaran_id, 
                no_pendaftaran, 
                tgl_pendaftaran, 
                no_rekam_medik, 
                nama_pasien, 
                nama_pegawai, 
                a.Mulai_Anamnesa, 
                pp.Selesai_Pelayanan_Poli,
                gelardepan,
                gelarbelakang_nama
        ";

        return $query;

    }

    public function queryFilter($query)
    {

        $query .= " tgl_pendaftaran BETWEEN :datefrom and :dateto AND p.pasienbatalperiksa_id is null and rm.instalasi_id  = 2 ";
        $this->params = array_merge($this->params, [':datefrom' => $this->dateFrom, ':dateto' => $this->dateTo]);

        return $query;
    }

    public function countQuery()
    {
        $query = "
            WITH Anamnesa AS (
                SELECT 
                    pendaftaran_id, 
                    MAX(waktutunggu_rs) AS Mulai_Anamnesa
                FROM 
                    waktutunggupelayanan_t
                WHERE 
                    task_id = 4
                GROUP BY 
                    pendaftaran_id
            ),
            PelayananPoli AS (
                SELECT 
                    pendaftaran_id, 
                    MAX(waktutunggu_rs) AS Selesai_Pelayanan_Poli
                FROM 
                    waktutunggupelayanan_t
                WHERE 
                    task_id = 5
                GROUP BY 
                    pendaftaran_id
            )
            select count(*) as total from (
                select count(*)
                FROM 
                    pendaftaran_t p
                JOIN 
                    waktutunggupelayanan_t w ON w.pendaftaran_id = p.pendaftaran_id
                JOIN 
                    pasien_m ps ON ps.pasien_id = p.pasien_id
                JOIN 
                    pegawai_m pg ON pg.pegawai_id = p.pegawai_id
                JOIN 
                    ruangan_m rm on rm.ruangan_id = p.ruangan_id
                LEFT JOIN 
                    Anamnesa a ON a.pendaftaran_id = p.pendaftaran_id
                LEFT JOIN 
                    PelayananPoli pp ON pp.pendaftaran_id = p.pendaftaran_id
                LEFT JOIN
                    gelarbelakang_m gb ON gb.gelarbelakang_id = pg.gelarbelakang_id
                WHERE
        ";

        $query = $this->queryFilter($query);

        $query .= " 
                GROUP BY 
                p.pendaftaran_id, 
                no_pendaftaran, 
                tgl_pendaftaran, 
                no_rekam_medik, 
                nama_pasien, 
                nama_pegawai, 
                a.Mulai_Anamnesa, 
                pp.Selesai_Pelayanan_Poli,
                gelardepan,
                gelarbelakang_nama
                ) as sub_total";


        $command = Yii::$app->db->createCommand($query);
        $command->bindValue(':datefrom', $this->dateFrom);
        $command->bindValue(':dateto', $this->dateTo);
        
        return $command->queryScalar();
    }

}
