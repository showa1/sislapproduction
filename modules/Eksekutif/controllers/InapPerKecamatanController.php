<?php

namespace app\modules\eksekutif\controllers;

use app\controllers\BaseController;
use yii\data\SqlDataProvider;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yii;
use DateTime;
use DateInterval;

class InapPerKecamatanController extends BaseController
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
        $sheet->setCellValue('A2', 'Laporan Kunjungan Rawat Inap Per Kecamatan');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(18);
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(14);

        $sheet->setCellValue('A4', 'No');
        $sheet->setCellValue('B4', 'Kecamatan');
        $sheet->setCellValue('C4', 'Jumlah');

        // Isi Data
        $row = 5; // Mulai dari baris kedua
        $i = 1;
        foreach ($models as $model) {
            $sheet->setCellValue('A' . $row, $i);
            $sheet->setCellValue('B' . $row, $model['kecamatan_nama']);
            $sheet->setCellValue('C' . $row, $model['jumlah']);
            
            $row++;
            $i++;
        }

        $sheet->getStyle('A4:C'.($row -1))->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $fileName = 'inap-per-kecamatan.xlsx';
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
                kcm.kecamatan_nama,
                count (pt.pendaftaran_id) as Jumlah
            FROM
                pembayaranpelayanan_t ppt
            JOIN pasien_m pm ON pm.pasien_id=ppt.pasien_id
            JOIN pendaftaran_t pt ON pt.pendaftaran_id=ppt.pendaftaran_id
            JOIN carabayar_m cm ON cm.carabayar_id=ppt.carabayar_id
            JOIN tandabuktibayar_t tbt ON tbt.tandabuktibayar_id=ppt.tandabuktibayar_id
            JOIN closingkasir_t ct ON ct.closingkasir_id=tbt.closingkasir_id
            JOIN ruangan_m rm ON rm.ruangan_id=pt.ruangan_id
            join kecamatan_m kcm on kcm.kecamatan_id=pm.kecamatan_id
            JOIN instalasi_m im ON im.instalasi_id=rm.instalasi_id
            join pegawai_m pgm on pgm.pegawai_id= pt.pegawai_id
            JOIN pasienadmisi_t pat ON pat.pasienadmisi_id=pt.pasienadmisi_id
            join pegawai_m pgm2 on pgm2.pegawai_id=pat.pegawai_id
            JOIN ruangan_m rm2 ON rm2.ruangan_id=pat.ruangan_id
            JOIN instalasi_m im2 ON im2.instalasi_id=rm2.instalasi_id 
        ";

        $query =  $this->queryFilter($query);

        $query .= "
            GROUP BY 
                kcm.kecamatan_nama 
        ";

        return $query;
    }

    public function queryFilter($query)
    {
        $query .= " WHERE DATE(tglclosingkasir) between :datefrom and :dateto ";
        $this->params = array_merge($this->params, [':datefrom' => $this->dateFrom, ':dateto' => $this->dateTo]);

        return $query;
    }

    public function countQuery()
    {
        $query = "
                select count(*) as total from (
                    select count(1)
                    FROM
                        pembayaranpelayanan_t ppt
                    JOIN pasien_m pm ON pm.pasien_id=ppt.pasien_id
                    JOIN pendaftaran_t pt ON pt.pendaftaran_id=ppt.pendaftaran_id
                    JOIN carabayar_m cm ON cm.carabayar_id=ppt.carabayar_id
                    JOIN tandabuktibayar_t tbt ON tbt.tandabuktibayar_id=ppt.tandabuktibayar_id
                    JOIN closingkasir_t ct ON ct.closingkasir_id=tbt.closingkasir_id
                    JOIN ruangan_m rm ON rm.ruangan_id=pt.ruangan_id
                    join kecamatan_m kcm on kcm.kecamatan_id=pm.kecamatan_id
                    JOIN instalasi_m im ON im.instalasi_id=rm.instalasi_id
                    join pegawai_m pgm on pgm.pegawai_id= pt.pegawai_id
                    JOIN pasienadmisi_t pat ON pat.pasienadmisi_id=pt.pasienadmisi_id
                    join pegawai_m pgm2 on pgm2.pegawai_id=pat.pegawai_id
                    JOIN ruangan_m rm2 ON rm2.ruangan_id=pat.ruangan_id
                    JOIN instalasi_m im2 ON im2.instalasi_id=rm2.instalasi_id 
        ";

        $query = $this->queryFilter($query);

        $query .= "
            GROUP BY 
                kcm.kecamatan_nama 
            ) as sub_total
        ";

        $command = Yii::$app->db->createCommand($query);
        $command->bindValue(':datefrom', $this->dateFrom);
        $command->bindValue(':dateto', $this->dateTo);
        
        return $command->queryScalar();
    }

}
