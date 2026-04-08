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

class JatuhTempoController extends BaseController
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
            $this->dateFrom = DateTime::createFromFormat('d-m-Y', $this->dateFrom)->format('Y-m-d') . ' 00:00:00';      
        }
        
        if (!empty($this->dateTo)) {
            $this->dateTo = DateTime::createFromFormat('d-m-Y', $this->dateTo)->format('Y-m-d') . ' 23:59:59';      
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
        $sheet->setCellValue('A2', 'Laporan Jatuh Tempo');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(18);
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(14);
        
        $sheet->setCellValue('A4', 'No');
        $sheet->setCellValue('B4', 'Tanggal Terima');
        $sheet->setCellValue('C4', 'Nomor Terima');
        $sheet->setCellValue('D4', 'Tanggal Faktur');
        $sheet->setCellValue('E4', 'Nomor Faktur');
        $sheet->setCellValue('F4', 'Tgl Bayar ke Supplier');
        $sheet->setCellValue('G4', 'Tgl Permintaan Pembelian');
        $sheet->setCellValue('H4', 'Jatuh Tempo');
        $sheet->setCellValue('I4', 'Nomor Perencanaan');
        $sheet->setCellValue('J4', 'Sumber Dana');
        $sheet->setCellValue('K4', 'Nama Supplier');
        $sheet->setCellValue('L4', 'Nomor Permintaan');
        $sheet->setCellValue('M4', 'Nomor Referensi');
        $sheet->setCellValue('N4', 'Keterangan Permintaan');
        $sheet->setCellValue('O4', 'Total');
        $sheet->setCellValue('P4', 'Termin Pembayaran');
       
        // Isi Data
        $row = 5; // Mulai dari baris kedua
        $i = 1;
        foreach ($models as $model) {
            $sheet->setCellValue('A' . $row, $i);
            $sheet->setCellValue('B' . $row, isset($model['tglterima']) ? $model['tglterima'] : '');
            $sheet->setCellValue('C' . $row, isset($model['noterima']) ? $model['noterima'] : '');
            $sheet->setCellValue('D' . $row, isset($model['tglfaktur']) ? $model['tglfaktur'] : '');
            $sheet->setCellValue('E' . $row, isset($model['nofaktur']) ? $model['nofaktur'] : '');
            $sheet->setCellValue('F' . $row, isset($model['tglbayarkesupplier']) ? $model['tglbayarkesupplier'] : '');
            $sheet->setCellValue('G' . $row, isset($model['tglpermintaanpembelian']) ? $model['tglpermintaanpembelian'] : '');
            $sheet->setCellValue('H' . $row, isset($model['jatuh_tempo']) ? $model['jatuh_tempo'] : '');
            $sheet->setCellValue('I' . $row, isset($model['noperencnaan']) ? $model['noperencnaan'] : '');
            $sheet->setCellValue('J' . $row, isset($model['sumberdana_nama']) ? $model['sumberdana_nama'] : '');
            $sheet->setCellValue('K' . $row, isset($model['supplier_nama']) ? $model['supplier_nama'] : '');
            $sheet->setCellValue('L' . $row, isset($model['nopermintaan']) ? $model['nopermintaan'] : '');
            $sheet->setCellValue('M' . $row, isset($model['noreferensi']) ? $model['noreferensi'] : '');
            $sheet->setCellValue('N' . $row, isset($model['keteranganpermintaan']) ? $model['keteranganpermintaan'] : '');
            $sheet->setCellValue('O' . $row, isset($model['total']) ? floatval($model['total']) : '');
            $sheet->setCellValue('P' . $row, isset($model['terminpembayaran']) ? $model['terminpembayaran'] : '');
    
            $row++;
            $i++;
        }

        $sheet->getStyle('A4:P'.($row -1))->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->getStyle('O5:O' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
        
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $fileName = 'laporan-jatuh-tempo.xlsx';
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
            SELECT 
                to_char(penerimaanbarang_t.tglterima,'dd/mm/yy') as tglterima,
                penerimaanbarang_t.noterima,
                to_char(fakturpembelian_t.tglfaktur,'dd/mm/yy') as tglfaktur,
                fakturpembelian_t.nofaktur,
                bayarkesupplier_t.tglbayarkesupplier,
                to_char(permintaanpembelian_t.tglpermintaanpembelian,'dd/mm/yy')as tglpermintaanpembelian,
                to_char(permintaanpembelian_t.tglpermintaanpembelian + INTERVAL '1' day * supplier_m.terminpembayaran ,'dd/mm/yy')
                as jatuh_tempo,
                informasipermintaanpembelian_v.noperencnaan,
                informasipermintaanpembelian_v.sumberdana_nama,
                informasipermintaanpembelian_v.supplier_nama,
                permintaanpembelian_t.nopermintaan,
                permintaanpembelian_t.noreferensi, 
                permintaanpembelian_t.keteranganpermintaan,
                sum(hargasatuanper) total,
                supplier_m.terminpembayaran
            FROM
                permintaanpembelian_t
            LEFT JOIN penerimaanbarang_t ON penerimaanbarang_t.penerimaanbarang_id=permintaanpembelian_t.penerimaanbarang_id
            LEFT JOIN fakturpembelian_t ON fakturpembelian_t.fakturpembelian_id=penerimaanbarang_t.fakturpembelian_id
            LEFT JOIN bayarkesupplier_t ON bayarkesupplier_t.fakturpembelian_id=fakturpembelian_t.fakturpembelian_id,
            informasipermintaanpembelian_v,
            permintaandetail_t,
            supplier_m 
        ";

        $query = $this->queryFilter($query);

        $query .= "
            GROUP BY  permintaanpembelian_t.penerimaanbarang_id,
            bayarkesupplier_t.tglbayarkesupplier,
            fakturpembelian_t.tglfaktur,
            fakturpembelian_t.nofaktur,
            penerimaanbarang_t.fakturpembelian_id,
            penerimaanbarang_t.penerimaanbarang_id,
            permintaanpembelian_t.permintaanpembelian_id,
            to_char(permintaanpembelian_t.tglpermintaanpembelian,'dd/mm/yy'),
            informasipermintaanpembelian_v.sumberdana_nama,
            informasipermintaanpembelian_v.supplier_nama,
            permintaanpembelian_t.nopermintaan,
            permintaanpembelian_t.noreferensi,
            informasipermintaanpembelian_v.noperencnaan,
            permintaanpembelian_t.keteranganpermintaan,
            informasipermintaanpembelian_v.pajak_nama,
            supplier_m.terminpembayaran
            order by permintaanpembelian_t.tglpermintaanpembelian desc
        ";

        return $query;

    }

    public function queryFilter($query)
    {

        $query .= " WHERE 
            1=1  AND
            permintaanpembelian_t.permintaanpembelian_id=informasipermintaanpembelian_v.permintaanpembelian_id AND 
            permintaanpembelian_t.batalpermintaanpembelian_id is null and
            permintaanpembelian_t.supplier_id=supplier_m.supplier_id and
            permintaandetail_t.permintaanpembelian_id=informasipermintaanpembelian_v.permintaanpembelian_id and
            permintaanpembelian_t.tglpermintaanpembelian between :datefrom
            AND :dateto 
             ";
        $this->params = array_merge($this->params, [':datefrom' => $this->dateFrom, ':dateto' => $this->dateTo]);

        return $query;
    }

    public function countQuery()
    {
        $query = "
            select count(*) as total from (
                select count(*)
                FROM
                    permintaanpembelian_t
                LEFT JOIN penerimaanbarang_t ON penerimaanbarang_t.penerimaanbarang_id=permintaanpembelian_t.penerimaanbarang_id
                LEFT JOIN fakturpembelian_t ON fakturpembelian_t.fakturpembelian_id=penerimaanbarang_t.fakturpembelian_id
                LEFT JOIN bayarkesupplier_t ON bayarkesupplier_t.fakturpembelian_id=fakturpembelian_t.fakturpembelian_id,
                informasipermintaanpembelian_v,
                permintaandetail_t,
                supplier_m 
        ";

        $query = $this->queryFilter($query);

        $query .= " 
                GROUP BY  permintaanpembelian_t.penerimaanbarang_id,
                bayarkesupplier_t.tglbayarkesupplier,
                fakturpembelian_t.tglfaktur,
                fakturpembelian_t.nofaktur,
                penerimaanbarang_t.fakturpembelian_id,
                penerimaanbarang_t.penerimaanbarang_id,
                permintaanpembelian_t.permintaanpembelian_id,
                to_char(permintaanpembelian_t.tglpermintaanpembelian,'dd/mm/yy'),
                informasipermintaanpembelian_v.sumberdana_nama,
                informasipermintaanpembelian_v.supplier_nama,
                permintaanpembelian_t.nopermintaan,
                permintaanpembelian_t.noreferensi,
                informasipermintaanpembelian_v.noperencnaan,
                permintaanpembelian_t.keteranganpermintaan,
                informasipermintaanpembelian_v.pajak_nama,
                supplier_m.terminpembayaran
                order by permintaanpembelian_t.tglpermintaanpembelian desc
            ) as sub_total";


        $command = Yii::$app->db->createCommand($query);
        $command->bindValue(':datefrom', $this->dateFrom);
        $command->bindValue(':dateto', $this->dateTo);
        
        return $command->queryScalar();
    }

}
