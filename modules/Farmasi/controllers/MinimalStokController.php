<?php

namespace app\modules\farmasi\controllers;

use app\controllers\BaseController;
use yii\data\SqlDataProvider;
use app\models\RuanganM;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Yii;
use DateTime;
use DateInterval;

class MinimalStokController extends BaseController
{    

    public $dateFrom, $dateTo, $totalCount;

    public $params = [];

    public $ruangan, $statuscari, $ruanganSelect;

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {   
        $this->setupSearch();

        $dropdownselect = [
            'ruangan' =>  $this->ruanganSelect,
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
            'Gudang Farmasi',
            'Apotek'
        ];
    }

    public function setupRuangan()
    {
        $ruanganid = Yii::$app->request->get('ruangan');
        if(isset($ruanganid)) {
            $gudangFarmasi = 0;
            $apotek = 1;
            $unitlain = 2;

            if ($ruanganid == $gudangFarmasi) {
                $this->ruangan = '58';
                $this->ruanganSelect = 0;
            }

            if ($ruanganid == $apotek) {
                $this->ruangan = '59';
                $this->ruanganSelect = 1;
            }
        }    
    }

    public function setupSearch()
    {
        $this->dateFrom = Yii::$app->request->get('date_from');
        $this->dateTo = Yii::$app->request->get('date_to');
        $this->setupRuangan();
        
        if (!empty($this->dateFrom)) {
            $this->dateFrom = DateTime::createFromFormat('d-m-Y', $this->dateFrom)->format('Y-m-d') . " 00:00:00";
        }
        
        if (!empty($this->dateTo)) {
            $this->dateTo = DateTime::createFromFormat('d-m-Y', $this->dateTo)->format('Y-m-d') . " 23:59:59";
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
        $sheet->setCellValue('A2', 'Laporan Minimal Stok');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(18);
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(14);
       
        $sheet->setCellValue('A4', 'No');
        $sheet->setCellValue('B4', 'Ruangan ID');
        $sheet->setCellValue('C4', 'Ruangan');
        $sheet->setCellValue('D4', 'Kategori Obat Alkes');
        $sheet->setCellValue('E4', 'Kronis/ Non Kronis');
        $sheet->setCellValue('F4', 'Kode Obat Alkes');
        $sheet->setCellValue('G4', 'Nama Obat Alkes');
        $sheet->setCellValue('H4', 'Tanggal Kadaluarsa');
        $sheet->setCellValue('I4', 'Stok');
        $sheet->setCellValue('J4', 'Jumlah Minimal Stok');

        // Isi Data
        $row = 5; // Mulai dari baris kedua
        $i = 1;
      
        foreach ($models as $model) {
            $sheet->setCellValue('A' . $row, $i);
            $sheet->setCellValue('B' . $row, $model['ruangan_id']);
            $sheet->setCellValue('C' . $row, $model['ruangan_nama']);
            $sheet->setCellValue('D' . $row, $model['obatalkes_kategori']);
            $sheet->setCellValue('E' . $row, $model['obatalkes_kronis']);
            $sheet->setCellValue('F' . $row, $model['obatalkes_kode']);
            $sheet->setCellValue('G' . $row, $model['obatalkes_nama']);
            $sheet->setCellValue('H' . $row, $model['tglkadaluarsa']);
            $sheet->setCellValue('I' . $row, isset($model['stok']) ? (float) $model['stok'] : '');
            $sheet->setCellValue('J' . $row, isset($model['jmlminimalstok']) ? (float) $model['jmlminimalstok'] : '');

            if ($model['minimal']) {
                $sheet->getStyle('A'. $row.':I'.$row)->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FF9999'],
                    ],
                ]);
            }
            $row++;
            $i++;
        }

        $sheet->getStyle('H5:J' . $row)->getNumberFormat()->setFormatCode('#,##0.00');

        $sheet->getStyle('A4:J'.($row -1))->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $fileName = 'laporan-minimal-stok.xlsx';
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
                rm.ruangan_id,
                rm.ruangan_nama,
                om.obatalkes_kategori,
                om.obatalkes_kronis,
                om.obatalkes_kode,
                om.obatalkes_nama,
                om.tglkadaluarsa,
                sum(st.qtystok_in - st.qtystok_out) AS stok,
                stm.jmlminimalstok,
                sum(st.qtystok_in - st.qtystok_out) <= stm.jmlminimalstok AS minimal
            FROM obatalkes_m om
                LEFT JOIN stokminimal_t stm ON stm.obatalkes_id = om.obatalkes_id
                LEFT JOIN ruangan_m rm ON rm.ruangan_id = stm.ruangan_id
                LEFT JOIN stokobatalkes_t st ON om.obatalkes_id = st.obatalkes_id AND st.ruangan_id = rm.ruangan_id
        ";

        return $this->queryFilter($query);

    }

    public function queryFilter($query)
    {
        $query .= "
            WHERE stm.ruangan_id = :ruangan AND om.obatalkes_aktif = true
            GROUP BY om.obatalkes_kategori, om.obatalkes_kode, om.obatalkes_nama, stm.jmlminimalstok, om.tglkadaluarsa, rm.ruangan_nama, rm.ruangan_id, om.obatalkes_kronis
            ORDER BY om.obatalkes_nama 
        ";

        $this->params = array_merge($this->params, [
            ':ruangan' => $this->ruangan,
        ]);

        return $query;
    }

    public function countQuery()
    {
        $query = "
            select count(*) as total from (
                select count(1)
                FROM obatalkes_m om
                LEFT JOIN stokminimal_t stm ON stm.obatalkes_id = om.obatalkes_id
                LEFT JOIN ruangan_m rm ON rm.ruangan_id = stm.ruangan_id
                LEFT JOIN stokobatalkes_t st ON om.obatalkes_id = st.obatalkes_id AND st.ruangan_id = rm.ruangan_id
                
        ";

        $query = $this->queryFilter($query);

        $query .= ") as sub_total";

        $command = Yii::$app->db->createCommand($query);
        $command->bindValue(':ruangan', $this->ruangan);
        
        return $command->queryScalar();
    }

}
