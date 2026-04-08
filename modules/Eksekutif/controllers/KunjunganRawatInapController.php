<?php

namespace app\modules\eksekutif\controllers;

use app\controllers\BaseController;
use yii\data\SqlDataProvider;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yii;
use DateTime;
use DateInterval;

class KunjunganRawatInapController extends BaseController
{    

    public $dateFrom, $dateTo, $totalCount;

    public $params = [];

    public $statuscari, $instalasi;

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
            'instalasi' =>  Yii::$app->request->get('instalasi'),
        ];

        return $this->render('index', [
            'dataProvider' => $this->dataprovider(),
            'listinstalasi' => $this->listinstalasi(),
            'dropdownselect' => $dropdownselect
        ]);

    }

    public function listinstalasi()
    {
        return [
            2 => 'Rawat Jalan',
            3 => 'Rawat Darurat',
            4 => 'Rawat Inap Langsung',
        ];
    }

    public function setupSearch()
    {
        $this->dateFrom = Yii::$app->request->get('date_from');
        $this->dateTo = Yii::$app->request->get('date_to');
        $this->instalasi = Yii::$app->request->get('instalasi');
        
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
        $sheet->setCellValue('A2', 'Laporan Kunjungan Rawat Inap');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(18);
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(14);

        $sheet->setCellValue('A4', 'No');
        $sheet->setCellValue('B4', 'Tanggal Pendaftaran');
        $sheet->setCellValue('C4', 'No Pendaftaran');
        $sheet->setCellValue('D4', 'Tanggal Pembayaran');
        $sheet->setCellValue('E4', 'No Pembayaran');
        $sheet->setCellValue('F4', 'Tanggal Closing Kasir');
        $sheet->setCellValue('G4', 'No Closing Kasir');
        $sheet->setCellValue('H4', 'Instalasi Asal');
        $sheet->setCellValue('I4', 'Ruangan Asal');
        $sheet->setCellValue('J4', 'Instalasi Akhir');
        $sheet->setCellValue('K4', 'Ruangan Akhir');
        $sheet->setCellValue('L4', 'No Rekam Medik');
        $sheet->setCellValue('M4', 'Nama Pasien');
        $sheet->setCellValue('N4', 'Cara Bayar');
        $sheet->setCellValue('O4', 'Total Biaya Tindakan');
        $sheet->setCellValue('P4', 'Total Biaya OA');
        $sheet->setCellValue('Q4', 'Total Biaya');
        $sheet->setCellValue('R4', 'Total PPN Farmasi');
        $sheet->setCellValue('S4', 'Total Pelayanan');

        // Isi Data
        $row = 5; // Mulai dari baris kedua
        $i = 1;
        foreach ($models as $model) {
            $sheet->setCellValue('A' . $row, $i);
            $sheet->setCellValue('B' . $row, $model['tgl_pendaftaran']);
            $sheet->setCellValue('C' . $row, $model['no_pendaftaran']);
            $sheet->setCellValue('D' . $row, $model['tglpembayaran']);
            $sheet->setCellValue('E' . $row, $model['nopembayaran']);
            $sheet->setCellValue('F' . $row, $model['tglclosingkasir']);
            $sheet->setCellValue('G' . $row, $model['closingkasir_no']);
            $sheet->setCellValue('H' . $row, $model['intalasi_asal']);
            $sheet->setCellValue('I' . $row, $model['ruangan_asal']);
            $sheet->setCellValue('J' . $row, isset($model['instalasi_bayar']) ? $model['instalasi_bayar'] : $model['intalasi_asal'] );
            $sheet->setCellValue('K' . $row, isset($model['ruangan_bayar']) ? $model['ruangan_bayar'] : $model['ruangan_asal']);
            $sheet->setCellValue('L' . $row, $model['no_rekam_medik']);
            $sheet->setCellValue('M' . $row, $model['nama_pasien']);
            $sheet->setCellValue('N' . $row, $model['carabayar_nama']);
            $sheet->setCellValue('O' . $row, isset($model['totalbiayatindakan']) ? (float) $model['totalbiayatindakan'] : '');
            $sheet->setCellValue('P' . $row, isset($model['totalbiayaoa']) ? (float) $model['totalbiayaoa'] : '');
            $sheet->setCellValue('Q' . $row, isset($model['totalbiaya']) ? (float) $model['totalbiaya'] : '');
            $sheet->setCellValue('R' . $row, isset($model['totalppnfarmasi']) ? (float) $model['totalppnfarmasi'] : '');
            $sheet->setCellValue('S' . $row, isset($model['totalpelayanan']) ? (float) $model['totalpelayanan'] : '');
            
            $row++;
            $i++;
        }

        $sheet->getStyle('O5:S' . $row)->getNumberFormat()->setFormatCode('#,##0.00');

        $sheet->getStyle('A4:S'.($row -1))->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $fileName = 'kunj-rawat-inap.xlsx';
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
                tgl_pendaftaran,
                no_pendaftaran,
                tglpembayaran,
                nopembayaran,
                tglclosingkasir,
                closingkasir_no,
                im.instalasi_id,
                im.instalasi_nama AS intalasi_asal,
                rm.ruangan_nama AS ruangan_asal,
                im2.instalasi_nama AS instalasi_bayar,
                rm2.ruangan_nama AS ruangan_bayar,
                no_rekam_medik,
                nama_pasien,
                carabayar_nama,
                totalbiayatindakan,
                totalbiayaoa,
                totalbiayapelayanan AS totalbiaya,
                totalppnfarmasi,
                SUM(totalbiayapelayanan+totalppnfarmasi) AS totalpelayanan
            FROM
                pembayaranpelayanan_t ppt
            JOIN pasien_m pm ON pm.pasien_id=ppt.pasien_id
            JOIN pendaftaran_t pt ON pt.pendaftaran_id=ppt.pendaftaran_id
            JOIN carabayar_m cm ON cm.carabayar_id=ppt.carabayar_id
            JOIN tandabuktibayar_t tbt ON tbt.tandabuktibayar_id=ppt.tandabuktibayar_id
            JOIN closingkasir_t ct ON ct.closingkasir_id=tbt.closingkasir_id
            JOIN ruangan_m rm ON rm.ruangan_id=pt.ruangan_id
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
                tgl_pendaftaran,
                no_pendaftaran,
                tglpembayaran,
                nopembayaran,
                tglclosingkasir,
                closingkasir_no,
                no_rekam_medik,
                nama_pasien,
                carabayar_nama,
                totalbiayatindakan,
                totalbiayaoa,
                im.instalasi_id,
                totalbiayapelayanan,
                totalppnfarmasi,
                im.instalasi_nama,
                rm.ruangan_nama,
                im2.instalasi_nama,
                rm2.ruangan_nama 
        ";

        return $query;
    }

    public function queryFilter($query)
    {
        $query .= " WHERE DATE(tglclosingkasir) between :datefrom and :dateto AND im.instalasi_id = :instalasi ";
        $this->params = array_merge($this->params, [
                ':datefrom' => $this->dateFrom,
                ':dateto' => $this->dateTo,
                ':instalasi' => $this->instalasi]);

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
                tgl_pendaftaran,
                no_pendaftaran,
                tglpembayaran,
                nopembayaran,
                tglclosingkasir,
                closingkasir_no,
                no_rekam_medik,
                nama_pasien,
                carabayar_nama,
                totalbiayatindakan,
                totalbiayaoa,
                im.instalasi_id,
                totalbiayapelayanan,
                totalppnfarmasi,
                im.instalasi_nama,
                rm.ruangan_nama,
                im2.instalasi_nama,
                rm2.ruangan_nama 
            ) as sub_total
        ";

        $command = Yii::$app->db->createCommand($query);
        $command->bindValue(':datefrom', $this->dateFrom);
        $command->bindValue(':dateto', $this->dateTo);
        $command->bindValue(':instalasi', $this->instalasi);
        
        return $command->queryScalar();
    }

}
