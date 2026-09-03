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
        $sheet->setCellValue('C4', 'Poliklinik / Ruangan');
        $sheet->setCellValue('D4', 'Jumlah Pasien');
        
        // Isi Data
        $row = 5; // Mulai dari baris kedua
        $i = 1;
        foreach ($models as $model) {
            $gelardepan = !empty($model['gelardepan']) ? trim($model['gelardepan']) : '';
            $gelarbelakang = !empty($model['gelarbelakang_nama']) ? trim($model['gelarbelakang_nama']) : '';
            $namaPegawai = !empty($model['nama_pegawai']) ? trim($gelardepan . ' ' . $model['nama_pegawai'] . ' ' . $gelarbelakang) : '';

            $sheet->setCellValue('A' . $row, $i);
            $sheet->setCellValue('B' . $row, $namaPegawai);
            $sheet->setCellValue('C' . $row, $model['ruangan_nama'] ?? '-');
            $sheet->setCellValue('D' . $row, (int)$model['jumlahpasien']);

            $row++;
            $i++;
        }

        $sheet->getStyle('A4:D'.($row -1))->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
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
        return "SELECT tariftindakan_id FROM tariftindakan_m WHERE 1=0";
    }

    public function baseQuery()
    {
        $query = "
            SELECT 
                pm.pegawai_id,
                pm.gelardepan,
                pm.nama_pegawai,
                gb.gelarbelakang_nama,
                STRING_AGG(DISTINCT rm.ruangan_nama, ', ') AS ruangan_nama,
                COUNT(pt.pendaftaran_id) AS jumlahpasien
            FROM pendaftaran_t pt
            JOIN pegawai_m pm ON pm.pegawai_id = pt.pegawai_id
            JOIN ruangan_m rm ON rm.ruangan_id = pt.ruangan_id
            LEFT JOIN gelarbelakang_m gb ON gb.gelarbelakang_id = pm.gelarbelakang_id
            LEFT JOIN jabatan_m j ON j.jabatan_id = pm.jabatan_id
        ";

        $query = $this->queryFilter($query);
       
        $query .= "
            GROUP BY pm.pegawai_id, pm.gelardepan, pm.nama_pegawai, gb.gelarbelakang_nama
            ORDER BY jumlahpasien DESC
        ";

        return $query;
    }

    public function queryFilter($query)
    {
        $query .= " WHERE pt.pasienbatalperiksa_id IS NULL
                    AND DATE(pt.tgl_pendaftaran) BETWEEN :datefrom AND :dateto
                    AND (pm.kelompokpegawai_id = 1 OR j.jabatan_nama ILIKE '%dokter%')
                ";

        $this->params = [
            ':datefrom' => $this->dateFrom,
            ':dateto' => $this->dateTo,
        ];

        return $query;
    }

    public function countQuery()
    {
        $query = "
            SELECT COUNT(*) AS total FROM (
                SELECT pm.pegawai_id
                FROM pendaftaran_t pt
                JOIN pegawai_m pm ON pm.pegawai_id = pt.pegawai_id
                JOIN ruangan_m rm ON rm.ruangan_id = pt.ruangan_id
                LEFT JOIN jabatan_m j ON j.jabatan_id = pm.jabatan_id
        ";

        $query = $this->queryFilter($query);

        $query .= " 
                GROUP BY pm.pegawai_id
            ) AS sub_total";

        $command = Yii::$app->db->createCommand($query);
        $command->bindValue(':datefrom', $this->dateFrom);
        $command->bindValue(':dateto', $this->dateTo);
        
        return $command->queryScalar();
    }
}
