<?php

namespace app\modules\rawatjalan\controllers;

use app\controllers\BaseController;
use yii\data\SqlDataProvider;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Yii;
use DateTime;

class ResponsetimeEksekutifController extends BaseController
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
        $sheet->setCellValue('A2', 'Laporan Respontime Eksekutif Detail');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(18);
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(14);
  
        $sheet->setCellValue('A4', 'No');
        $sheet->setCellValue('B4', 'Tanggal Pendaftaran');
        $sheet->setCellValue('C4', 'Nama Ruangan');
        $sheet->setCellValue('D4', 'No Pendaftaran');
        $sheet->setCellValue('E4', 'Nama Pasien');
        $sheet->setCellValue('F4', 'No Rekam Medik');  
        $sheet->setCellValue('G4', 'Dokter');
        $sheet->setCellValue('H4', 'Akhir Waktu Layanan Admisi / Mulai Waktu Tunggu Poli');
        $sheet->setCellValue('I4', 'Akhir Waktu Tunggu Poli / Mulai Waktu Layan Poli');
        $sheet->setCellValue('J4', 'Akhir Waktu Layanan Poli / Mulai Waktu Tunggu Farmasi');
        $sheet->setCellValue('K4', 'Akhir Waktu Tunggu Farmasi / Mulai Waktu Layanan Farmasi Membuat Obat');
        $sheet->setCellValue('L4', 'Akhir Waktu Obat Selesai Dibuat');

        // Isi Data
        $row = 5; // Mulai dari baris kedua
        $i = 1;
        foreach ($models as $model) {
            $sheet->setCellValue('A' . $row, $i);
            $sheet->setCellValue('B' . $row, $model['tgl_pendaftaran']);
            $sheet->setCellValue('C' . $row, $model['ruangan_nama']);
            $sheet->setCellValue('D' . $row, $model['no_pendaftaran']);
            $sheet->setCellValue('E' . $row, $model['namadepan'] . ' ' . $model['nama_pasien']);
            $sheet->setCellValue('F' . $row, $model['no_rekam_medik']);
            $sheet->setCellValue('G' . $row, $model['gelardepan'] . ' ' . $model['nama_pegawai']);
            $sheet->setCellValue('H' . $row, isset($model['akhir_waktu_layanan_admisi_atau_mulai_waktu_tunggu_poli']) ? $model['akhir_waktu_layanan_admisi_atau_mulai_waktu_tunggu_poli'] : '');
            $sheet->setCellValue('I' . $row, isset($model['akhir_waktu_tunggu_poli_atau_mulai_waktu_layan_poli']) ? $model['akhir_waktu_tunggu_poli_atau_mulai_waktu_layan_poli'] : '');
            $sheet->setCellValue('J' . $row, isset($model['akhir_waktu_layanan_poli_atau_mulai_waktu_tunggu_farmasi']) ? $model['akhir_waktu_layanan_poli_atau_mulai_waktu_tunggu_farmasi'] : '');
            $sheet->setCellValue('K' . $row, isset($model['waktu_layanan_farmasi_membuat']) ? $model['waktu_layanan_farmasi_membuat'] : '');
            $sheet->setCellValue('L' . $row, isset($model['akhir_waktu_obat_selesai_dibuat']) ? $model['akhir_waktu_obat_selesai_dibuat'] : '');
            $row++;
            $i++;
        }

        $sheet->getStyle('A4:L'.($row -1))->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        // Simpan file ke response
        
        $writer = new Xlsx($spreadsheet);
        $fileName = 'lap-responsetime-eksekutif-detail.xlsx';
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
            SELECT 
                t.pendaftaran_id, 
                pp.tgl_pendaftaran, 
                rm.ruangan_nama,
                pp.no_pendaftaran, 
                ps.namadepan, 
                ps.nama_pasien, 
                ps.no_rekam_medik,
                pg.nama_pegawai,
                pg.gelardepan,

                MAX(t.waktutunggu_rs) FILTER (WHERE t.task_id = 3) AS akhir_waktu_layanan_admisi_atau_mulai_waktu_tunggu_poli,
                MAX(t.waktutunggu_rs) FILTER (WHERE t.task_id = 4) AS akhir_waktu_tunggu_poli_atau_mulai_waktu_layan_poli,
                MAX(t.waktutunggu_rs) FILTER (WHERE t.task_id = 5) AS akhir_waktu_layanan_poli_atau_mulai_waktu_tunggu_farmasi,
                MAX(t.waktutunggu_rs) FILTER (WHERE t.task_id = 6) AS waktu_layanan_farmasi_membuat,
                MAX(t.waktutunggu_rs) FILTER (WHERE t.task_id = 7) AS akhir_waktu_obat_selesai_dibuat

                FROM waktutunggupelayanan_t t
                JOIN pendaftaran_t pp ON pp.pendaftaran_id = t.pendaftaran_id 
                JOIN pasien_m ps ON ps.pasien_id = t.pasien_id 
                JOIN ruangan_m rm ON rm.ruangan_id = pp.ruangan_id 
                JOIN pegawai_m pg ON pg.pegawai_id = pp.pegawai_id
        ";

        $query = $this->queryFilter($query);
       
        $query .= "
            GROUP BY 
                t.pendaftaran_id, 
                pp.tgl_pendaftaran, 
                pp.no_pendaftaran, 
                ps.namadepan, 
                ps.nama_pasien, 
                ps.no_rekam_medik ,
                rm.ruangan_nama,
                pg.nama_pegawai,
                pg.gelardepan 
                ORDER BY 
                pp.tgl_pendaftaran DESC
        ";

        return $query;

    }

    public function queryFilter($query)
    {

        $query .= " 
                    WHERE 
                    pp.tgl_pendaftaran::date BETWEEN :datefrom and :dateto
                    AND rm.is_eksekutif = true
                    and pp.pasienbatalperiksa_id is null 
                ";

        $this->params = array_merge($this->params, [':datefrom' => $this->dateFrom, ':dateto' => $this->dateTo]);

        return $query;
    }

    public function countQuery()
    {
        $query = "
            select count(*) as total from (
                select count(1)
                FROM waktutunggupelayanan_t t
                JOIN pendaftaran_t pp ON pp.pendaftaran_id = t.pendaftaran_id 
                JOIN pasien_m ps ON ps.pasien_id = t.pasien_id 
                JOIN ruangan_m rm ON rm.ruangan_id = pp.ruangan_id
                JOIN pegawai_m pg ON pg.pegawai_id = pp.pegawai_id
        ";

        $query = $this->queryFilter($query);

        $query .= " 
                GROUP BY 
                t.pendaftaran_id, 
                pp.tgl_pendaftaran, 
                pp.no_pendaftaran, 
                ps.namadepan, 
                ps.nama_pasien, 
                ps.no_rekam_medik ,
                rm.ruangan_nama,
                pg.nama_pegawai,
                pg.gelardepan
                ) as sub_total";


        $command = Yii::$app->db->createCommand($query);
        $command->bindValue(':datefrom', $this->dateFrom);
        $command->bindValue(':dateto', $this->dateTo);
        
        return $command->queryScalar();
    }

}
