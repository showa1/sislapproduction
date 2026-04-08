<?php

namespace app\modules\keuangan\controllers;

use app\controllers\BaseController;
use yii\data\SqlDataProvider;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yii;
use DateTime;
use DateInterval;

class RevenueController extends BaseController
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
        $sheet->setCellValue('A2', 'Laporan Revenue');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(18);
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(14);

        $sheet->setCellValue('A4', 'No');
        $sheet->setCellValue('B4', 'Tanggal Bayar');
        $sheet->setCellValue('C4', 'No Pembayaran');
        $sheet->setCellValue('D4', 'Administrasi RJ');
        $sheet->setCellValue('E4', 'Dokter Spesialis');
        $sheet->setCellValue('F4', 'BHP RJ');
        $sheet->setCellValue('G4', 'Pendapatan Para Medis Dannon');
        $sheet->setCellValue('H4', 'Tindakan Rawat Jalan');
        $sheet->setCellValue('I4', 'Administrasi Rawat Inap');
        $sheet->setCellValue('J4', 'Akomodasi Rawat Inap');
        $sheet->setCellValue('K4', 'Jasa Dokter Rawat Inap');
        $sheet->setCellValue('L4', 'Tindakan Rawat Inap');
        $sheet->setCellValue('M4', 'BHP RI');
        $sheet->setCellValue('N4', 'Administrasi IGD');
        $sheet->setCellValue('O4', 'Jasa Dokter IGD');
        $sheet->setCellValue('P4', 'Pendapatan paramedis dannon IGD');
        $sheet->setCellValue('Q4', 'Tindakan IGD');
        $sheet->setCellValue('R4', 'BHP RD');
        $sheet->setCellValue('S4', 'Jasa Dokter VK');
        $sheet->setCellValue('T4', 'Tindakan VK');
        $sheet->setCellValue('U4', 'BHP VK');
        $sheet->setCellValue('V4', 'Akomodasi Perawatan Intensif');
        $sheet->setCellValue('W4', 'Jasa Dokter Perawatan Intensif');
        $sheet->setCellValue('X4', 'Tindakan Perawatan Intensif');
        $sheet->setCellValue('Y4', 'BHP Perawatan Intensif');
        $sheet->setCellValue('Z4', 'Tindakan Fisiotherapy');
        $sheet->setCellValue('AA4', 'BHP Fisiotherapy');
        $sheet->setCellValue('AB4', 'Tindakan MCU');
        $sheet->setCellValue('AC4', 'Jasa Dokter MCU');
        $sheet->setCellValue('AD4', 'BHP MCU');
        $sheet->setCellValue('AE4', 'Tindakan Lab');
        $sheet->setCellValue('AF4', 'BHP Lab');
        $sheet->setCellValue('AG4', 'Tindakan Rad');
        $sheet->setCellValue('AH4', 'BHP Rad');
        $sheet->setCellValue('AI4', 'Tindakan Ibs');
        $sheet->setCellValue('AJ4', 'BHP Ibs');
        $sheet->setCellValue('AK4', 'Tindakan Jenazah');
        $sheet->setCellValue('AL4', 'BHP Jenazah');
        $sheet->setCellValue('AM4', 'Tindakan Bank Darah');
        $sheet->setCellValue('AN4', 'BHP Bank Darah');
        $sheet->setCellValue('AO4', 'Tindakan Ambulance');
        $sheet->setCellValue('AP4', 'BHP Ambulance');

        // $row = 5;
        $i = 1;
        // foreach ($models as $model) {
        //     $sheet->setCellValue('A' . $row, $i);
        //     $sheet->setCellValue('B' . $row, $model['tanggalbayar']);
        //     $sheet->setCellValue('C' . $row, $model['nopembayaran']);
        //     $sheet->setCellValue('D' . $row, isset($model['administrasirj']) ? (float) $model['administrasirj'] : '');
        //     $sheet->setCellValue('E' . $row, isset($model['dokterspesialis']) ? (float) $model['dokterspesialis'] : '');
        //     $sheet->setCellValue('F' . $row, isset($model['bhprj']) ? (float) $model['bhprj'] : '');
        //     $sheet->setCellValue('G' . $row, isset($model['pendapatanparamedisdannon']) ? (float) $model['pendapatanparamedisdannon'] : '');
        //     $sheet->setCellValue('H' . $row, isset($model['tindakanrawatjalan']) ? (float) $model['tindakanrawatjalan'] : '');
        //     $sheet->setCellValue('I' . $row, isset($model['administrasirawatinap']) ? (float) $model['administrasirawatinap'] : '');
        //     $sheet->setCellValue('J' . $row, isset($model['akomodasirawatinap']) ? (float) $model['akomodasirawatinap'] : '');
        //     $sheet->setCellValue('K' . $row, isset($model['jasadokterrawatinap']) ? (float) $model['jasadokterrawatinap'] : '');
        //     $sheet->setCellValue('L' . $row, isset($model['tindakanrawatinap']) ? (float) $model['tindakanrawatinap'] : '');
        //     $sheet->setCellValue('M' . $row, isset($model['bhpri']) ? (float) $model['bhpri'] : '');
        //     $sheet->setCellValue('N' . $row, isset($model['administrasiigd']) ? (float) $model['administrasiigd'] : '');
        //     $sheet->setCellValue('O' . $row, isset($model['jasadokterigd']) ? (float) $model['jasadokterigd'] : '');
        //     $sheet->setCellValue('P' . $row, isset($model['pendapatanparamedisdannonigd']) ? (float) $model['pendapatanparamedisdannonigd'] : '');
        //     $sheet->setCellValue('Q' . $row, isset($model['tindakanigd']) ? (float) $model['tindakanigd'] : '');
        //     $sheet->setCellValue('R' . $row, isset($model['bhprd']) ? (float) $model['bhprd'] : '');
        //     $sheet->setCellValue('S' . $row, isset($model['jasadoktervk']) ? (float) $model['jasadoktervk'] : '');
        //     $sheet->setCellValue('T' . $row, isset($model['tindakanvk']) ? (float) $model['tindakanvk'] : '');
        //     $sheet->setCellValue('U' . $row, isset($model['bhpvk']) ? (float) $model['bhpvk'] : '');
        //     $sheet->setCellValue('V' . $row, isset($model['akomodasiperawatanintensif']) ? (float) $model['akomodasiperawatanintensif'] : '');
        //     $sheet->setCellValue('W' . $row, isset($model['jasadokterperawatanintesif']) ? (float) $model['jasadokterperawatanintesif'] : '');
        //     $sheet->setCellValue('X' . $row, isset($model['tindakanperawatanintesif']) ? (float) $model['tindakanperawatanintesif'] : '');
        //     $sheet->setCellValue('Y' . $row, isset($model['bhpperawatanintensif']) ? (float) $model['bhpperawatanintensif'] : '');
        //     $sheet->setCellValue('Z' . $row, isset($model['tindakanfisiotherapy']) ? (float) $model['tindakanfisiotherapy'] : '');
        //     $sheet->setCellValue('AA' . $row, isset($model['bhpfisiotherapy']) ? (float) $model['bhpfisiotherapy'] : '');
        //     $sheet->setCellValue('AB' . $row, isset($model['tindakanmcu']) ? (float) $model['tindakanmcu'] : '');
        //     $sheet->setCellValue('AC' . $row, isset($model['jasadoktermcu']) ? (float) $model['jasadoktermcu'] : '');
        //     $sheet->setCellValue('AD' . $row, isset($model['bhpmcu']) ? (float) $model['bhpmcu'] : '');
        //     $sheet->setCellValue('AE' . $row, isset($model['tindakanlab']) ? (float) $model['tindakanlab'] : '');
        //     $sheet->setCellValue('AF' . $row, isset($model['bhplab']) ? (float) $model['bhplab'] : '');
        //     $sheet->setCellValue('AG' . $row, isset($model['tindakanrad']) ? (float) $model['tindakanrad'] : '');
        //     $sheet->setCellValue('AH' . $row, isset($model['bhprad']) ? (float) $model['bhprad'] : '');
        //     $sheet->setCellValue('AI' . $row, isset($model['tindakanibs']) ? (float) $model['tindakanibs'] : '');
        //     $sheet->setCellValue('AJ' . $row, isset($model['bhpibs']) ? (float) $model['bhpibs'] : '');
        //     $sheet->setCellValue('AK' . $row, isset($model['tindakanjenazah']) ? (float) $model['tindakanjenazah'] : '');
        //     $sheet->setCellValue('AL' . $row, isset($model['bhpjenah']) ? (float) $model['bhpjenah'] : '');
        //     $sheet->setCellValue('AM' . $row, isset($model['tindakanbankdarah']) ? (float) $model['tindakanbankdarah'] : '');
        //     $sheet->setCellValue('AN' . $row, isset($model['bhpbankdarah']) ? (float) $model['bhpbankdarah'] : '');
        //     $sheet->setCellValue('AO' . $row, isset($model['tindakanambulance']) ? (float) $model['tindakanambulance'] : '');
        //     $sheet->setCellValue('AP' . $row, isset($model['bhpambulance']) ? (float) $model['bhpambulance'] : '');
            
        //     $row++;
        //     $i++;
        // }

        // $sheet->getStyle('D5:AP' . $row)->getNumberFormat()->setFormatCode('#,##0.00');

        // $sheet->getStyle('A4:AP'.($row -1))->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        $data = [];
        foreach ($models as $model) {
            $data[] = [
                $i,
                $model['tanggalbayar'] ?? '',
                $model['nopembayaran'] ?? '',
                isset($model['administrasirj']) ? (float) $model['administrasirj'] : '',
                isset($model['dokterspesialis']) ? (float) $model['dokterspesialis'] : '',
                isset($model['bhprj']) ? (float) $model['bhprj'] : '',
                isset($model['pendapatanparamedisdannon']) ? (float) $model['pendapatanparamedisdannon'] : '',
                isset($model['tindakanrawatjalan']) ? (float) $model['tindakanrawatjalan'] : '',
                isset($model['administrasirawatinap']) ? (float) $model['administrasirawatinap'] : '',
                isset($model['akomodasirawatinap']) ? (float) $model['akomodasirawatinap'] : '',
                isset($model['jasadokterrawatinap']) ? (float) $model['jasadokterrawatinap'] : '',
                isset($model['tindakanrawatinap']) ? (float) $model['tindakanrawatinap'] : '',
                isset($model['bhpri']) ? (float) $model['bhpri'] : '',
                isset($model['administrasiigd']) ? (float) $model['administrasiigd'] : '',
                isset($model['jasadokterigd']) ? (float) $model['jasadokterigd'] : '',
                isset($model['pendapatanparamedisdannonigd']) ? (float) $model['pendapatanparamedisdannonigd'] : '',
                isset($model['tindakanigd']) ? (float) $model['tindakanigd'] : '',
                isset($model['bhprd']) ? (float) $model['bhprd'] : '',
                isset($model['jasadoktervk']) ? (float) $model['jasadoktervk'] : '',
                isset($model['tindakanvk']) ? (float) $model['tindakanvk'] : '',
                isset($model['bhpvk']) ? (float) $model['bhpvk'] : '',
                isset($model['akomodasiperawatanintensif']) ? (float) $model['akomodasiperawatanintensif'] : '',
                isset($model['jasadokterperawatanintesif']) ? (float) $model['jasadokterperawatanintesif'] : '',
                isset($model['tindakanperawatanintesif']) ? (float) $model['tindakanperawatanintesif'] : '',
                isset($model['bhpperawatanintensif']) ? (float) $model['bhpperawatanintensif'] : '',
                isset($model['tindakanfisiotherapy']) ? (float) $model['tindakanfisiotherapy'] : '',
                isset($model['bhpfisiotherapy']) ? (float) $model['bhpfisiotherapy'] : '',
                isset($model['tindakanmcu']) ? (float) $model['tindakanmcu'] : '',
                isset($model['jasadoktermcu']) ? (float) $model['jasadoktermcu'] : '',
                isset($model['bhpmcu']) ? (float) $model['bhpmcu'] : '',
                isset($model['tindakanlab']) ? (float) $model['tindakanlab'] : '',
                isset($model['bhplab']) ? (float) $model['bhplab'] : '',
                isset($model['tindakanrad']) ? (float) $model['tindakanrad'] : '',
                isset($model['bhprad']) ? (float) $model['bhprad'] : '',
                isset($model['tindakanibs']) ? (float) $model['tindakanibs'] : '',
                isset($model['bhpibs']) ? (float) $model['bhpibs'] : '',
                isset($model['tindakanjenazah']) ? (float) $model['tindakanjenazah'] : '',
                isset($model['bhpjenah']) ? (float) $model['bhpjenah'] : '',
                isset($model['tindakanbankdarah']) ? (float) $model['tindakanbankdarah'] : '',
                isset($model['bhpbankdarah']) ? (float) $model['bhpbankdarah'] : '',
                isset($model['tindakanambulance']) ? (float) $model['tindakanambulance'] : '',
                isset($model['bhpambulance']) ? (float) $model['bhpambulance'] : '',
            ];
            $i++;
        }

        $startRow = 5;
        $sheet->fromArray($data, null, 'A' . $startRow);

        $lastRow = $startRow + count($data) - 1;
        $sheet->getStyle('D' . $startRow . ':AP' . $lastRow)
            ->getNumberFormat()
            ->setFormatCode('#,##0.00');

        $sheet->getStyle('A4:AP' . $lastRow)
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $fileName = 'laporan-revenue.xlsx';
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
            SELECT * from sislap_revenuereport 
        ";

        $query =  $this->queryFilter($query);

        return $query;
    }

    public function queryFilter($query)
    {
        $query .= " WHERE tanggalbayar_asli BETWEEN :datefrom AND :dateto ";
        $this->params = array_merge($this->params, [':datefrom' => $this->dateFrom, ':dateto' => $this->dateTo]);

        return $query;
    }

    public function countQuery()
    {
        $query = " select count(1) FROM sislap_revenuereport";

        $query = $this->queryFilter($query);

        $command = Yii::$app->db->createCommand($query);
        $command->bindValue(':datefrom', $this->dateFrom);
        $command->bindValue(':dateto', $this->dateTo);
        
        return $command->queryScalar();
    }

}
