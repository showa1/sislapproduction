<?php

namespace app\modules\farmasi\controllers;

use app\controllers\BaseController;
use yii\data\SqlDataProvider;
use app\models\RuanganM;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yii;
use DateTime;
use DateInterval;

class StokopnameController extends BaseController
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
            'start' =>  Yii::$app->request->get('date_from'),
            'to' =>  Yii::$app->request->get('date_to'),
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
            'Apotek',
            'Unit Lain'
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

            if ($ruanganid == $unitlain) {
                $this->ruangan = 'unit lain';
                $this->ruanganSelect = 2;
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
        $sheet->setCellValue('A2', 'Laporan Stok Opname');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(18);
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(14);
        
        $sheet->setCellValue('A4', 'No');
        $sheet->setCellValue('B4', 'Ruangan ID');
        $sheet->setCellValue('C4', 'Ruangan');
        $sheet->setCellValue('D4', 'No Stok Opname');
        $sheet->setCellValue('E4', 'Tanggal Stok Opname');
        $sheet->setCellValue('F4', 'Kode Obat Alkes');
        $sheet->setCellValue('G4', 'Jenis Obat Alkes');
        $sheet->setCellValue('H4', 'Nama Obat Alkes');
        $sheet->setCellValue('I4', 'Harga Netto');
        $sheet->setCellValue('J4', 'Discount');
        $sheet->setCellValue('K4', 'PPN Persen');
        $sheet->setCellValue('L4', 'Harga Jual');
        $sheet->setCellValue('M4', 'Volume Sistem');
        $sheet->setCellValue('N4', 'Volume Fisik');
        $sheet->setCellValue('O4', 'Jumlah Selisih Stok');
        $sheet->setCellValue('P4', 'Petugas 1');
        $sheet->setCellValue('Q4', 'Petugas 2');
        $sheet->setCellValue('R4', 'Pegawai Mengetahui');
        $sheet->setCellValue('S4', 'Keterangan Opname');

        // Isi Data
        $row = 5; // Mulai dari baris kedua
        $i = 1;
      
        foreach ($models as $model) {
            $sheet->setCellValue('A' . $row, $i);
            $sheet->setCellValue('B' . $row, $model['ruangan_id']);
            $sheet->setCellValue('C' . $row, $model['ruangan_nama']);
            $sheet->setCellValue('D' . $row, $model['nostokopname']);
            $sheet->setCellValue('E' . $row, $model['tglstokopname']);
            $sheet->setCellValue('F' . $row, $model['obatalkes_kode']);
            $sheet->setCellValue('G' . $row, $model['jenisobatalkes_nama']);
            $sheet->setCellValue('H' . $row, $model['obatalkes_nama']);
            $sheet->setCellValue('I' . $row, isset($model['harganetto']) ? (float) $model['harganetto'] : '');
            $sheet->setCellValue('J' . $row, isset($model['discount']) ? (float) $model['discount'] : '');
            $sheet->setCellValue('K' . $row, isset($model['ppn_persen']) ? (float) $model['ppn_persen'] : '');
            $sheet->setCellValue('L' . $row, isset($model['hargajual']) ? (float) $model['hargajual'] : '');
            $sheet->setCellValue('M' . $row, isset($model['volume_sistem']) ? (float) $model['volume_sistem'] : '');
            $sheet->setCellValue('N' . $row, isset($model['volume_fisik']) ? (float) $model['volume_fisik'] : '');
            $sheet->setCellValue('O' . $row, isset($model['jmlselisihstok']) ? (float) $model['jmlselisihstok'] : '');
            $sheet->setCellValue('P' . $row, !empty($model['petugas_1']) ? $model['petugas_1'] : '');
            $sheet->setCellValue('Q' . $row, !empty($model['petugas_2']) ? $model['petugas_2'] : '');
            $sheet->setCellValue('R' . $row, !empty($model['pegawai_mengetahui']) ? $model['pegawai_mengetahui'] : '');
            $sheet->setCellValue('S' . $row, $model['keterangan_opname']);
            
            $row++;
            $i++;
        }

        $sheet->getStyle('I5:O' . $row)->getNumberFormat()->setFormatCode('#,##0.00');

        $sheet->getStyle('A4:S'.($row -1))->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $fileName = 'laporan-stokopname.xlsx';
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
            select 
                rm.ruangan_id, 
                rm.ruangan_nama, 
                stkt.nostokopname, 
                stkt.tglstokopname, 
                om.obatalkes_kode, 
                jm.jenisobatalkes_nama, 
                om.obatalkes_nama, 
                om.harganetto, 
                om.discount, 
                om.ppn_persen, 
                om.hargajual, 
                stot.volume_sistem, 
                stot.volume_fisik, 
                stot.jmlselisihstok, 
                pg1.nama_pegawai as Petugas_1, 
                pg2.nama_pegawai as Petugas_2, 
                pg.nama_pegawai as Pegawai_Mengetahui, 
                stkt.keterangan_opname 
            from 
                stokopnamedet_t stot 
            inner join obatalkes_m om on om.obatalkes_id = stot.obatalkes_id 
            inner join stokopname_t stkt on stkt.stokopname_id = stot.stokopname_id 
            inner join jenisobatalkes_m jm on jm.jenisobatalkes_id = om.jenisobatalkes_id 
            inner join ruangan_m rm on rm.ruangan_id = stkt.ruangan_id 
            left join pegawai_m pg on stkt.mengetahui_id = pg.pegawai_id 
            left join pegawai_m pg1 on stkt.petugas1_id = pg1.pegawai_id 
            left join pegawai_m pg2 on stkt.petugas2_id = pg2.pegawai_id 
            

        ";

        return $this->queryFilter($query);

    }

    public function queryFilter($query)
    {
        $combine = '= :ruangan';
        if ($this->ruangan == 'unit lain') {
            $combine = 'not in (58, 59) ';
        }

        $query .= "
            WHERE 
                date (stkt.tglstokopname) between :datefrom and :dateto
                and stkt.ruangan_id " .$combine. "
            GROUP BY 
                stkt.tglstokopname, rm.ruangan_id, stkt.nostokopname, om.obatalkes_kode, jm.jenisobatalkes_nama,
                om.obatalkes_nama, om.harganetto, om.discount, om.ppn_persen, om.hargajual, stot.volume_sistem,
                stot.volume_fisik, stot.jmlselisihstok, pg1.nama_pegawai, pg2.nama_pegawai, pg.nama_pegawai,
                stkt.keterangan_opname
            order by 
                stkt.tglstokopname asc
        ";

        $this->params = array_merge($this->params, [
            ':datefrom' => $this->dateFrom,
            ':dateto' => $this->dateTo,
        ]);

        if ($this->ruangan !== 'unit lain') {
            $this->params = array_merge($this->params, [
                ':ruangan' => $this->ruangan,
            ]);
        }

        return $query;
    }

    public function countQuery()
    {
        $query = "
            select count(*) as total from (
                select count(1)
                from stokopnamedet_t stot 
                inner join obatalkes_m om on om.obatalkes_id = stot.obatalkes_id 
                inner join stokopname_t stkt on stkt.stokopname_id = stot.stokopname_id 
                inner join jenisobatalkes_m jm on jm.jenisobatalkes_id = om.jenisobatalkes_id 
                inner join ruangan_m rm on rm.ruangan_id = stkt.ruangan_id 
                left join pegawai_m pg on stkt.mengetahui_id = pg.pegawai_id 
                left join pegawai_m pg1 on stkt.petugas1_id = pg1.pegawai_id 
                left join pegawai_m pg2 on stkt.petugas2_id = pg2.pegawai_id 
                
        ";

        $query = $this->queryFilter($query);

        $query .= ") as sub_total";

        $command = Yii::$app->db->createCommand($query);
        $command->bindValue(':datefrom', $this->dateFrom);
        $command->bindValue(':dateto', $this->dateTo);
        
        if ($this->ruangan !== 'unit lain') {
            $command->bindValue(':ruangan', $this->ruangan);
        }
        
        return $command->queryScalar();
    }

}
