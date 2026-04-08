<?php

namespace app\modules\farmasi\controllers;

use app\controllers\BaseController;
use yii\data\SqlDataProvider;
use app\models\JenistarifM;
use app\models\KelaspelayananM;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yii;
use DateTime;

class PenggunaanObatPerpasienController extends BaseController
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

        //$command = Yii::$app->db->createCommand($a->sql, $a->params);
        //echo "SQL Query: " . $command->getRawSql(); die;
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
        $sheet->setCellValue('A2', 'Laporan Penggunaan Obat Per Pasien');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(18);
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(14);

        $sheet->setCellValue('A4', 'No');
        $sheet->setCellValue('B4', 'Tanggal Pendaftaran');
        $sheet->setCellValue('C4', 'Tanggal Pelayanan');
        $sheet->setCellValue('D4', 'Tanggal Input');
        $sheet->setCellValue('E4', 'Asal Masuk');
        $sheet->setCellValue('F4', 'Dokter');
        $sheet->setCellValue('G4', 'No Pembayaran');
        $sheet->setCellValue('H4', 'Tanggal Pembayaran');
        $sheet->setCellValue('I4', 'Obat Alkes Kode');
        $sheet->setCellValue('J4', 'Obat Alkes Nama');
        $sheet->setCellValue('K4', 'Qty oa');
        $sheet->setCellValue('L4', 'No Rekam Medik');
        $sheet->setCellValue('M4', 'Nama Pasien');
        $sheet->setCellValue('N4', 'Kronis/ Non Kronis');
        $sheet->setCellValue('O4', 'Kategori');        
        $sheet->setCellValue('P4', 'Jenis Obat Alkes');        
        $sheet->setCellValue('Q4', 'BPJS Kesehatan');
        $sheet->setCellValue('R4', 'Umum');
        $sheet->setCellValue('S4', 'BPJS Ketenagakerjaan');
        $sheet->setCellValue('T4', 'Kemenkes RI');
        $sheet->setCellValue('U4', 'Asuransi');

        // Isi Data
        $row = 5; // Mulai dari baris kedua
        $i = 1;
        foreach ($models as $model) {
            $sheet->setCellValue('A' . $row, $i);
            $sheet->setCellValue('B' . $row, $model['tgl_pendaftaran']);
            $sheet->setCellValue('C' . $row, $model['tglpelayanan']);
            $sheet->setCellValue('D' . $row, $model['tglinput']);
            $sheet->setCellValue('E' . $row, $model['asalmasuk']);
            $sheet->setCellValue('F' . $row, $model['dokter']);
            $sheet->setCellValue('G' . $row, $model['nopembayaran']);
            $sheet->setCellValue('H' . $row, $model['tglpembayaran']);
            $sheet->setCellValue('I' . $row, $model['obatalkes_kode']);
            $sheet->setCellValue('J' . $row, $model['obatalkes_nama']);
            $sheet->setCellValue('K' . $row, $model['qty_oa']);
            $sheet->setCellValue('L' . $row, $model['no_rekam_medik']);
            $sheet->setCellValue('M' . $row, $model['nama_pasien']);
            $sheet->setCellValue('N' . $row, $model['obatalkes_kronis']);
            $sheet->setCellValue('O' . $row, $model['obatalkes_kategori']);
            $sheet->setCellValue('P' . $row, $model['jenisobatalkes_nama']);
            $sheet->setCellValue('Q' . $row, !empty($model['bpjskesehatan']) ? number_format($model['bpjskesehatan'], 2, ',', '.') : "");
            $sheet->setCellValue('R' . $row, !empty($model['umum']) ? number_format($model['umum'], 2, ',', '.') : '');
            $sheet->setCellValue('S' . $row, !empty($model['bpjsketenagakerjaan']) ? number_format($model['bpjsketenagakerjaan'], 2, ',', '.') : '');
            $sheet->setCellValue('T' . $row, !empty($model['kemenkesri']) ? number_format($model['kemenkesri'], 2, ',', '.') : '');
            $sheet->setCellValue('U' . $row, !empty($model['asuransi']) ? number_format($model['asuransi'], 2, ',', '.') : '');
           
            $row++;
            $i++;
        }

        $sheet->getStyle('A4:U'.($row -1))->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        // Simpan file ke response
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
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
            select pt.tgl_pendaftaran, tglpelayanan,opt.create_time as tglinput ,rm.ruangan_nama As asalmasuk,
            pgm.nama_pegawai As dokter, nopembayaran, tglpembayaran, obatalkes_kode, 
            om.obatalkes_nama,opt.qty_oa ,pm.no_rekam_medik, pm.nama_pasien, om.obatalkes_kronis, om.obatalkes_kategori, jom.jenisobatalkes_nama,
            round(cast(sum(opt.qty_oa*opt.hargasatuan_oa)
            filter (where  pt.penjamin_id in ('2','67'))AS Numeric),2)as bpjskesehatan,
            round(cast(sum(opt.qty_oa*opt.hargasatuan_oa)
            filter (where  pt.penjamin_id in ('398'))AS Numeric),2)as umum,
            round(cast(sum(opt.qty_oa*opt.hargasatuan_oa)
            filter (where  pt.penjamin_id in ('3'))AS Numeric),2)as bpjsketenagakerjaan,
            round(cast(sum(opt.qty_oa*opt.hargasatuan_oa)
            filter (where  pt.penjamin_id in ('54'))AS Numeric),2)as kemenkesri,
            round(cast(sum(opt.qty_oa*opt.hargasatuan_oa)
            filter (where  pt.penjamin_id not in ('398','2','67','3','54'))AS Numeric),2)as asuransi
            from obatalkespasien_t opt
            inner join obatalkes_m om on opt.obatalkes_id = om.obatalkes_id
            inner join pasien_m pm on pm.pasien_id = opt.pasien_id
            inner join pendaftaran_t pt on pt.pendaftaran_id=opt.pendaftaran_id
            inner join ruangan_m rm on rm.ruangan_id = pt.ruangan_id
            inner join ruangan_m rm2 on rm2.ruangan_id = opt.create_ruangan
            inner join pembayaranpelayanan_t pyt on pyt.pendaftaran_id = pt.pendaftaran_id
            left join jenisobatalkes_m jom on jom.jenisobatalkes_id=om.jenisobatalkes_id
            inner join pegawai_m pgm on pgm.pegawai_id=opt.pegawai_id
            where pt.pasienbatalperiksa_id is null
            
        ";

        $query = $this->queryFilter($query);

        $query .= "
         group by 
           tglpembayaran,
            pgm.nama_pegawai,
            obatalkes_kode,
            jom.jenisobatalkes_nama,
            tglpelayanan, 
            opt.create_time,
            nopembayaran,
            pm.no_rekam_medik, 
            pm.nama_pasien, 
            rm.ruangan_nama, 
            rm2.ruangan_nama, 
            pt.tgl_pendaftaran, 
            rm.ruangan_nama, 
            om.obatalkes_nama, 
            opt.qty_oa, 
            om.obatalkes_kategori, 
            om.obatalkes_kronis  
        order by pt.tgl_pendaftaran Asc
        ";

        return $query;

    }

    public function queryFilter($query)
    {

        $query .= " and date (opt.create_time) between :datefrom and :dateto ";
        $this->params = array_merge($this->params, [':datefrom' => $this->dateFrom, ':dateto' => $this->dateTo]);

        return $query;
    }

    public function countQuery()
    {
        $query = "
            select count(*) as total from (
                select count(*)
                from obatalkespasien_t opt
                inner join obatalkes_m om on opt.obatalkes_id = om.obatalkes_id 
                inner join pasien_m pm on pm.pasien_id = opt.pasien_id 
                inner join pendaftaran_t pt on pt.pendaftaran_id = opt.pendaftaran_id 
                inner join ruangan_m rm on rm.ruangan_id = pt.ruangan_id 
                inner join ruangan_m rm2 on rm2.ruangan_id = opt.create_ruangan
                inner join pembayaranpelayanan_t pyt on pyt.pendaftaran_id = pt.pendaftaran_id
                left join jenisobatalkes_m jom on jom.jenisobatalkes_id=om.jenisobatalkes_id
                inner join pegawai_m pgm on pgm.pegawai_id=opt.pegawai_id
                where pt.pasienbatalperiksa_id is null
        ";

        $query = $this->queryFilter($query);
    
        $query .= " 
                group by 
                tglpembayaran,
                pgm.nama_pegawai,
                obatalkes_kode,
                jom.jenisobatalkes_nama,
                tglpelayanan, 
                opt.create_time,
                nopembayaran,
                pm.no_rekam_medik, 
                pm.nama_pasien, 
                rm.ruangan_nama, 
                rm2.ruangan_nama, 
                pt.tgl_pendaftaran, 
                rm.ruangan_nama, 
                om.obatalkes_nama, 
                opt.qty_oa, 
                om.obatalkes_kategori, 
                om.obatalkes_kronis 
                order by pt.tgl_pendaftaran Asc
            ) as sub_total";


        $command = Yii::$app->db->createCommand($query);
        $command->bindValue(':datefrom', $this->dateFrom);
        $command->bindValue(':dateto', $this->dateTo);
        
        return $command->queryScalar();
    }

}
