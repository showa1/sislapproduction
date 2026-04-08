<?php

namespace app\modules\keuangan\controllers;

use app\controllers\BaseController;
use yii\data\SqlDataProvider;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yii;
use DateTime;
use DateInterval;

class NeracaSaldoController extends BaseController
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
            'totalCount' => 0,
            'pagination' => false
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
        $sheet->setCellValue('A2', 'Laporan Neraca Saldo');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(18);
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(14);

        $sheet->setCellValue('A4', 'No');
        $sheet->setCellValue('B4', 'No Akun');
        $sheet->setCellValue('C4', 'Nama Akun');
        $sheet->setCellValue('D4', 'Debit');
        $sheet->setCellValue('E4', 'Kredit');

        $i = 1;
        $row = 5;
        $data = [];
        foreach ($models as $model) {
            $sheet->setCellValue('A' . $row, $i);
            $sheet->setCellValue('B' . $row, $model['kdrekening5']);
            $sheet->setCellValue('C' . $row, $model['nmrekening5']);
            $sheet->setCellValue('D' . $row, (float) $model['saldo_debit']);
            $sheet->setCellValue('E' . $row, (float) $model['saldo_kredit']);

            $row++;
            $i++;
        }

        $sheet->getStyle('D5:E' . $row)->getNumberFormat()->setFormatCode('#,##0.00');

        $sheet->getStyle('A4:E'.($row -1))->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $fileName = 'laporan-neraca-saldo.xlsx';
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
            WITH rekening_neraca AS (
                SELECT 
                    r5.rekening5_id,
                    r5.kdrekening5,
                    r5.nmrekening5,
                    r5.rekening5_nb
                FROM rekening1_m r1
                JOIN kelrekening_m kr ON kr.kelrekening_id = r1.kelrekening_id
                JOIN rekening2_m r2 ON r2.rekening1_id = r1.rekening1_id
                JOIN rekening3_m r3 ON r3.rekening2_id = r2.rekening2_id
                JOIN rekening4_m r4 ON r4.rekening3_id = r3.rekening3_id
                JOIN rekening5_m r5 ON r5.rekening4_id = r4.rekening4_id
                WHERE r1.rekening1_aktif = true
                AND r2.rekening2_aktif = true
                AND r3.rekening3_aktif = true
                AND r4.rekening4_aktif = true
                AND r5.rekening5_aktif = true
                AND r5.rekening5_id <> 337
            ),

            saldo_awal AS (
                SELECT 
                    r.rekening5_id,
                    r.rekening5_nb,
                    SUM(t.jmlsaldoawald) AS saldo_awal_debit,
                    SUM(t.jmlsaldoawalk) AS saldo_awal_kredit
                FROM saldoawal_t t
                JOIN rekening5_m r ON r.rekening5_id = t.rekening5_id
                JOIN rekperiod_m rp ON rp.rekperiod_id = t.rekperiod_id
                WHERE (
                    rp.perideawal::date BETWEEN :datefrom AND :dateto
                    OR rp.sampaidgn::date BETWEEN :datefrom AND :dateto
                )
                AND r.rekening5_id <> 337
                GROUP BY r.rekening5_id, r.rekening5_nb
            ),

            saldo_awal_fix AS (
                SELECT
                    rn.rekening5_id,
                    rn.kdrekening5,
                    rn.nmrekening5,
                    rn.rekening5_nb AS saldonormal,

                    COALESCE(
                        CASE 
                            WHEN rn.rekening5_nb <> 'D' THEN 0 - sa.saldo_awal_debit
                            ELSE sa.saldo_awal_debit
                        END, 0
                    )
                    +
                    COALESCE(
                        CASE 
                            WHEN rn.rekening5_nb = 'D' THEN 0 - sa.saldo_awal_kredit
                            ELSE sa.saldo_awal_kredit
                        END, 0
                    ) AS saldo_awal
                FROM rekening_neraca rn
                LEFT JOIN saldo_awal sa ON sa.rekening5_id = rn.rekening5_id
            ),

            data_bb AS (
                SELECT 
                    rekening5_m.rekening5_id,
                    rekening5_m.kdrekening5,
                    rekening5_m.nmrekening5,
                    bukubesar_t.saldodebit,
                    bukubesar_t.saldokredit,
                    rekening5_m.rekening5_nb AS saldonormal
                FROM bukubesar_t
                JOIN jurnalposting_t ON bukubesar_t.jurnalposting_id = jurnalposting_t.jurnalposting_id
                JOIN jurnaldetail_t ON jurnalposting_t.jurnaldetail_id = jurnaldetail_t.jurnaldetail_id
                JOIN jurnalrekening_t ON jurnaldetail_t.jurnalrekening_id = jurnalrekening_t.jurnalrekening_id
                JOIN rekening5_m ON bukubesar_t.rekening5_id = rekening5_m.rekening5_id
                JOIN rekening4_m ON rekening5_m.rekening4_id = rekening4_m.rekening4_id
                JOIN rekening3_m ON rekening4_m.rekening3_id = rekening3_m.rekening3_id
                JOIN rekening2_m ON rekening3_m.rekening2_id = rekening2_m.rekening2_id
                JOIN rekening1_m ON rekening2_m.rekening1_id = rekening1_m.rekening1_id
                JOIN kelrekening_m ON kelrekening_m.kelrekening_id = rekening1_m.kelrekening_id
                JOIN periodeposting_m ON periodeposting_m.periodeposting_id = bukubesar_t.periodeposting_id
                JOIN jenisjurnal_m ON jenisjurnal_m.jenisjurnal_id = jurnalrekening_t.jenisjurnal_id
                WHERE bukubesar_t.saldoawal_id IS NULL
                AND bukubesar_t.tglbukubesar::date BETWEEN :datefrom AND :dateto
                AND rekening5_m.rekening5_id <> 337

                UNION ALL

                SELECT
                    r5.rekening5_id,
                    r5.kdrekening5,
                    r5.nmrekening5,
                    bb.saldodebit,
                    bb.saldokredit,
                    r5.rekening5_nb AS saldonormal
                FROM bukubesar_t bb
                JOIN rekening5_m r5 ON bb.rekening5_id = r5.rekening5_id
                JOIN rekening4_m r4 ON r5.rekening4_id = r4.rekening4_id
                JOIN rekening3_m r3 ON r4.rekening3_id = r3.rekening3_id
                JOIN rekening2_m r2 ON r3.rekening2_id = r2.rekening2_id
                JOIN rekening1_m r1 ON r2.rekening1_id = r1.rekening1_id
                JOIN kelrekening_m kr ON kr.kelrekening_id = r1.kelrekening_id
                JOIN periodeposting_m pp ON pp.periodeposting_id = bb.periodeposting_id
                WHERE r5.rekening5_id = 336
                AND bb.tglbukubesar BETWEEN :datefrom AND :dateto

                UNION ALL

                SELECT
                    r5.rekening5_id,
                    r5.kdrekening5,
                    r5.nmrekening5,
                    bb.saldodebit,
                    bb.saldokredit,
                    r5.rekening5_nb AS saldonormal
                FROM bukubesar_t bb
                JOIN rekening5_m r5 ON bb.rekening5_id = r5.rekening5_id
                JOIN rekening4_m r4 ON r5.rekening4_id = r4.rekening4_id
                JOIN rekening3_m r3 ON r4.rekening3_id = r3.rekening3_id
                JOIN rekening2_m r2 ON r3.rekening2_id = r2.rekening2_id
                JOIN rekening1_m r1 ON r2.rekening1_id = r1.rekening1_id
                JOIN kelrekening_m kr ON kr.kelrekening_id = r1.kelrekening_id
                JOIN periodeposting_m pp ON pp.periodeposting_id = bb.periodeposting_id
                WHERE bb.saldoawal_id IS NOT NULL
                AND bb.tglbukubesar BETWEEN :datefrom AND :dateto
            ),


            bb_fix AS (
                SELECT
                    rekening5_id,
                    kdrekening5,

                    SUM(
                        CASE 
                            WHEN saldonormal = 'K'
                                THEN 0 - saldodebit
                            ELSE saldodebit
                        END
                    ) AS saldo_debit,

                    SUM(
                        CASE 
                            WHEN saldonormal = 'D'
                                THEN 0 - saldokredit
                            ELSE saldokredit
                        END
                    ) AS saldo_kredit
                FROM data_bb
                GROUP BY rekening5_id, kdrekening5
            )


            SELECT
                sa.kdrekening5,
                sa.rekening5_id,
                sa.nmrekening5,
                sa.saldonormal,
                sa.saldo_awal,
                COALESCE(bb.saldo_debit, 0)  AS saldo_debit,
                COALESCE(bb.saldo_kredit, 0) AS saldo_kredit,
                sa.saldo_awal
                    + COALESCE(bb.saldo_debit, 0)
                    + COALESCE(bb.saldo_kredit, 0) AS saldo_akhir
            FROM saldo_awal_fix sa
            LEFT JOIN bb_fix bb ON bb.rekening5_id = sa.rekening5_id
            ORDER BY sa.kdrekening5;

        ";

        $query =  $this->queryFilter($query);

        return $query;
    }

    public function queryFilter($query)
    {
        $this->params = array_merge($this->params, [':datefrom' => $this->dateFrom, ':dateto' => $this->dateTo]);

        return $query;
    }

}
