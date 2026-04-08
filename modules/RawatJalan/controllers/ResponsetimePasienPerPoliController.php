<?php

namespace app\modules\rawatjalan\controllers;

use app\controllers\BaseController;
use yii\data\SqlDataProvider;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Yii;
use DateTime;

class ResponsetimePasienPerPoliController extends BaseController
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
            'pagination' => false,
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
        $sheet->setCellValue('A2', 'Laporan Respontime Pasien Eksekutif Per Poli');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(18);
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(14);

        $sheet->setCellValue('A4', 'Avg 3-4');
        $sheet->setCellValue('B4', 'Avg 4-5');
        $sheet->setCellValue('C4', 'Avg 5-6');
        $sheet->setCellValue('D4', 'Avg 6-7');
        $sheet->setCellValue('E4', 'Avg Total 3-7');
        $sheet->setCellValue('F4', 'Ruangan');

        
        // Isi Data
        $row = 5; // Mulai dari baris kedua
        $i = 1;
        foreach ($models as $model) {
            $sheet->setCellValue('A' . $row, $model['avg_3_4']);
            $sheet->setCellValue('B' . $row, $model['avg_4_5']);
            $sheet->setCellValue('C' . $row, $model['avg_5_6']);
            $sheet->setCellValue('D' . $row, $model['avg_6_7']);
            $sheet->setCellValue('E' . $row, $model['avg_total_3_7']);
            $sheet->setCellValue('F' . $row, $model['ruangan_nama']);
            $row++;
            $i++;
        }

        $sheet->getStyle('A4:F'.($row -1))->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        // Simpan file ke response
        
        $writer = new Xlsx($spreadsheet);
        $fileName = 'lap-respon-time-pasien-eksekutif-poli.xlsx';
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
            WITH data_pasien AS (
                SELECT 
                t.pendaftaran_id, 
                pp.ruangan_id,
                rm.ruangan_nama,

                MAX(t.waktutunggu_rs) FILTER (WHERE t.task_id = 3) AS t3,
                MAX(t.waktutunggu_rs) FILTER (WHERE t.task_id = 4) AS t4,
                MAX(t.waktutunggu_rs) FILTER (WHERE t.task_id = 5) AS t5,
                MAX(t.waktutunggu_rs) FILTER (WHERE t.task_id = 6) AS t6,
                MAX(t.waktutunggu_rs) FILTER (WHERE t.task_id = 7) AS t7

                FROM waktutunggupelayanan_t t
                JOIN pendaftaran_t pp ON pp.pendaftaran_id = t.pendaftaran_id 
                JOIN ruangan_m rm ON rm.ruangan_id = pp.ruangan_id

                WHERE 
                pp.tgl_pendaftaran::date BETWEEN :datefrom and :dateto 
                AND rm.is_eksekutif = true
                AND pp.pasienbatalperiksa_id IS NULL

                GROUP BY t.pendaftaran_id, pp.ruangan_id, rm.ruangan_nama
                
            ),

            avg_detik AS (
                SELECT 
                    ruangan_id,
                    ruangan_nama,
                    AVG(EXTRACT(EPOCH FROM (t4 - t3))) AS d_3_4,
                    AVG(EXTRACT(EPOCH FROM (t5 - t4))) AS d_4_5,
                    AVG(EXTRACT(EPOCH FROM (t6 - t5))) AS d_5_6,
                    AVG(EXTRACT(EPOCH FROM (t7 - t6))) AS d_6_7,
                    AVG(EXTRACT(EPOCH FROM (t7 - t3))) AS d_3_7
                FROM data_pasien
                WHERE 
                    t3 IS NOT NULL AND 
                    t4 IS NOT NULL AND 
                    t5 IS NOT NULL AND 
                    t6 IS NOT NULL AND 
                    t7 IS NOT NULL

                GROUP BY ruangan_id, ruangan_nama
            )

            SELECT
                ruangan_nama,
                CONCAT(
                    FLOOR(d_3_4 / 60), ' menit ',
                    FLOOR(MOD(d_3_4::numeric, 60)), ' detik'
                ) AS avg_3_4,

                CONCAT(
                    FLOOR(d_4_5 / 60), ' menit ',
                    FLOOR(MOD(d_4_5::numeric, 60)), ' detik'
                ) AS avg_4_5,

                CONCAT(
                    FLOOR(d_5_6 / 60), ' menit ',
                    FLOOR(MOD(d_5_6::numeric, 60)), ' detik'
                ) AS avg_5_6,

                CONCAT(
                    FLOOR(d_6_7 / 60), ' menit ',
                    FLOOR(MOD(d_6_7::numeric, 60)), ' detik'
                ) AS avg_6_7,

                CONCAT(
                    FLOOR(d_3_7 / 60), ' menit ',
                    FLOOR(MOD(d_3_7::numeric, 60)), ' detik'
                ) AS avg_total_3_7




            FROM avg_detik;
        ";

        $query = $this->queryFilter($query);

        return $query;

    }

    public function queryFilter($query)
    {
        $this->params = array_merge($this->params, [':datefrom' => $this->dateFrom, ':dateto' => $this->dateTo]);

        return $query;
    }

    public function countQuery()
    {
        $query = "
        WITH data_pasien AS (
                SELECT 
                t.pendaftaran_id, 
                pp.ruangan_id,
                rm.ruangan_nama,

                MAX(t.waktutunggu_rs) FILTER (WHERE t.task_id = 3) AS t3,
                MAX(t.waktutunggu_rs) FILTER (WHERE t.task_id = 4) AS t4,
                MAX(t.waktutunggu_rs) FILTER (WHERE t.task_id = 5) AS t5,
                MAX(t.waktutunggu_rs) FILTER (WHERE t.task_id = 6) AS t6,
                MAX(t.waktutunggu_rs) FILTER (WHERE t.task_id = 7) AS t7

                FROM waktutunggupelayanan_t t
                JOIN pendaftaran_t pp ON pp.pendaftaran_id = t.pendaftaran_id 
                JOIN ruangan_m rm ON rm.ruangan_id = pp.ruangan_id

                WHERE 
                pp.tgl_pendaftaran::date BETWEEN :datefrom and :dateto 
                AND rm.is_eksekutif = true
                AND pp.pasienbatalperiksa_id IS NULL

                GROUP BY t.pendaftaran_id, pp.ruangan_id, rm.ruangan_nama
                
            ),

            avg_detik AS (
                SELECT 
                    ruangan_id,
                    ruangan_nama,
                    AVG(EXTRACT(EPOCH FROM (t4 - t3))) AS d_3_4,
                    AVG(EXTRACT(EPOCH FROM (t5 - t4))) AS d_4_5,
                    AVG(EXTRACT(EPOCH FROM (t6 - t5))) AS d_5_6,
                    AVG(EXTRACT(EPOCH FROM (t7 - t6))) AS d_6_7,
                    AVG(EXTRACT(EPOCH FROM (t7 - t3))) AS d_3_7
                FROM data_pasien
                WHERE 
                    t3 IS NOT NULL AND 
                    t4 IS NOT NULL AND 
                    t5 IS NOT NULL AND 
                    t6 IS NOT NULL AND 
                    t7 IS NOT NULL

                GROUP BY ruangan_id, ruangan_nama
            )

            
            select count(*) from avg_detik as sub_total
        ";

        $query = $this->queryFilter($query);


        $command = Yii::$app->db->createCommand($query);
        $command->bindValue(':datefrom', $this->dateFrom);
        $command->bindValue(':dateto', $this->dateTo);
        
        return $command->queryScalar();
    }

}
