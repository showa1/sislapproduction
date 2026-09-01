<?php

namespace app\modules\keuangan\controllers;

use app\controllers\BaseController;
use yii\data\SqlDataProvider;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yii;
use DateTime;
use DateInterval;

class BukuBesarController extends BaseController
{    

    public $dateFrom, $dateTo, $totalCount;

    public $params, $dataRekening, $dataBukuBesar, $res= [];

    public $statuscari;
    public $noReferensi;
    public $kdRekening5;

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {   
        $this->setupSearch();

        if ($this->statuscari) {
            $this->setDataRekening();
            $this->setDataBukuBesar();
            $this->setDataResult();
            
        }

        $dropdownselect = [
            'start' =>  Yii::$app->request->get('date_from'),
            'to' =>  Yii::$app->request->get('date_to'),
            'no_referensi' => Yii::$app->request->get('no_referensi'),
            'kdrekening5' => Yii::$app->request->get('kdrekening5'),
        ];

        return $this->render('index', [
            'data' => $this->res,
            'dropdownselect' => $dropdownselect,
            'listRekening' => $this->getOptRekening5(),
        ]);

    }

    public function getOptRekening5()
    {
        $sql = "SELECT kdrekening5, nmrekening5 
                FROM rekening5_m 
                WHERE rekening5_aktif = TRUE 
                ORDER BY kdrekening5 ASC";
        $rows = Yii::$app->db->createCommand($sql)->queryAll();
        $opt = [];
        foreach ($rows as $r) {
            $opt[$r['kdrekening5']] = $r['kdrekening5'] . ' - ' . trim($r['nmrekening5']);
        }
        return $opt;
    }

    public function setDataResult()
    {

        $res = [];
        if (!empty($this->dataRekening)) {
            $res = $this->dataRekening;
        }

        foreach ($this->dataBukuBesar as $item) {

            $kd = $item['kdrekening5'];

            // =============================
            // INIT REKENING
            // =============================
            if (!isset($res[$kd])) {
                $res[$kd] = [
                    'nama' => $item['nmrekening5'],
                    'id'   => $item['rekening5_id'],
                    'nb'   => $item['saldonormal'],
                    'saldo_awal_debit'  => 0,
                    'saldo_awal_kredit'=> 0,
                    'data' => [],
                    'tiperekening_id' => $item['tiperekening_id'],
                ];
            }

            // =============================
            // SALDO AWAL
            // =============================
            if (!empty($item['saldoawal_id'])) {

                if (!empty($dat_saldo_awal[$item['rekening5_id']])) {
                    continue;
                }

                if ($item['saldonormal'] === 'D') {
                    $res[$kd]['saldo_awal_debit']  += $item['saldodebit'];
                    $res[$kd]['saldo_awal_kredit'] -= $item['saldokredit'];
                } else {
                    $res[$kd]['saldo_awal_debit']  -= $item['saldodebit'];
                    $res[$kd]['saldo_awal_kredit'] += $item['saldokredit'];
                }

                continue; // saldo awal tidak masuk mutasi
            }

            // =============================
            // KEY MUTASI (BUAT SEKALI)
            // =============================
            $key = implode('_', [
                $kd,
                date('dmY', strtotime($item['tglbukubesar'])),
                $item['noreferensi'],
                md5($item['uraiantransaksi'])
            ]);

            // =============================
            // INIT MUTASI
            // =============================
            if (!isset($res[$kd]['data'][$key])) {
                $res[$kd]['data'][$key] = [
                    'kdrekening5' => $kd,
                    'jeniskode'   => $item['jeniskode'],
                    'saldonormal'=> $item['saldonormal'],
                    'saldoawal_id'=> $item['saldoawal_id'],
                    'periodeposting_id'=> isset($item['periodeposting_id']) ? $item['periodeposting_id'] : null,
                    'tglperiodeposting_akhir'=> isset($item['tglperiodeposting_akhir']) ? $item['tglperiodeposting_akhir'] : null,
                    'noreferensi'=> isset($item['noreferensi']) ? $item['noreferensi'] : null,
                    'uraiantransaksi'=> isset($item['uraiantransaksi']) ? $item['uraiantransaksi'] : null,
                    'tglbuktijurnal'=> isset($item['tglbuktijurnal']) ? $item['tglbuktijurnal'] : null,
                    'tglbukubesar'=> isset($item['tglbukubesar']) ? $item['tglbukubesar'] : null,
                    'saldodebit' => 0,
                    'saldokredit'=> 0,
                ];
            }

            // =============================
            // AKUMULASI MUTASI
            // =============================
            $res[$kd]['data'][$key]['saldodebit']  += $item['saldodebit'];
            $res[$kd]['data'][$key]['saldokredit'] += $item['saldokredit'];
        }

        // Filter out accounts without data if no_referensi filter is specified
        if (!empty($this->noReferensi)) {
            foreach ($res as $kd => $val) {
                if (empty($val['data'])) {
                    unset($res[$kd]);
                }
            }
        }

        ksort($res);

        $this->res = $res;
    }

    public function setDataBukuBesar()
    {
        $whereRef1 = "";
        $whereRef2 = "";
        $whereRef3 = "";
        $queryParams = [
            ':startDate' => $this->dateFrom,
            ':endDate'   => $this->dateTo,
        ];

        if (!empty($this->noReferensi)) {
            $whereRef1 = " AND jr.noreferensi ILIKE :noReferensi ";
            $whereRef2 = " AND 1 = 0 ";
            $whereRef3 = " AND jr.noreferensi ILIKE :noReferensi ";
            $queryParams[':noReferensi'] = '%' . trim($this->noReferensi) . '%';
        }

        if (!empty($this->kdRekening5)) {
            $whereRef1 .= " AND r5.kdrekening5 = :kdRekening5 ";
            $whereRef2 .= " AND r5.kdrekening5 = :kdRekening5 ";
            $whereRef3 .= " AND r5.kdrekening5 = :kdRekening5 ";
            $queryParams[':kdRekening5'] = trim($this->kdRekening5);
        }

        $sql = <<<SQL
        SELECT 
            r5.rekening5_id,
            r5.kdrekening5,
            r5.nmrekening5,
            bb.saldodebit,
            bb.saldokredit,
            bb.saldoawal_id,
            bb.uraiantransaksi,
            bb.tglbukubesar,
            jr.noreferensi,
            r5.tiperekening_id,
            r5.rekening5_nb AS saldonormal,
            jj.jeniskode,
            bb.bukubesar_id
        FROM bukubesar_t bb
        JOIN jurnalposting_t jp ON bb.jurnalposting_id = jp.jurnalposting_id
        JOIN jurnaldetail_t jd ON jp.jurnaldetail_id = jd.jurnaldetail_id
        JOIN jurnalrekening_t jr ON jd.jurnalrekening_id = jr.jurnalrekening_id
        JOIN rekening5_m r5 ON bb.rekening5_id = r5.rekening5_id
        JOIN rekening4_m r4 ON r5.rekening4_id = r4.rekening4_id
        JOIN rekening3_m r3 ON r4.rekening3_id = r3.rekening3_id
        JOIN rekening2_m r2 ON r3.rekening2_id = r2.rekening2_id
        JOIN rekening1_m r1 ON r2.rekening1_id = r1.rekening1_id
        JOIN kelrekening_m kr ON kr.kelrekening_id = r1.kelrekening_id
        JOIN periodeposting_m pp ON pp.periodeposting_id = bb.periodeposting_id
        JOIN jenisjurnal_m jj ON jj.jenisjurnal_id = jr.jenisjurnal_id
        WHERE bb.saldoawal_id IS NULL
        AND bb.tglbukubesar BETWEEN :startDate AND :endDate
        {$whereRef1}

        UNION ALL

        SELECT
            r5.rekening5_id,
            r5.kdrekening5,
            r5.nmrekening5,
            bb.saldodebit,
            bb.saldokredit,
            bb.saldoawal_id,
            bb.uraiantransaksi,
            bb.tglbukubesar,
            NULL::character varying AS noreferensi,
            r5.tiperekening_id,
            r5.rekening5_nb AS saldonormal,
            NULL::character varying AS jeniskode,
            bb.bukubesar_id
        FROM bukubesar_t bb
        JOIN rekening5_m r5 ON bb.rekening5_id = r5.rekening5_id
        JOIN rekening4_m r4 ON r5.rekening4_id = r4.rekening4_id
        JOIN rekening3_m r3 ON r4.rekening3_id = r3.rekening3_id
        JOIN rekening2_m r2 ON r3.rekening2_id = r2.rekening2_id
        JOIN rekening1_m r1 ON r2.rekening1_id = r1.rekening1_id
        JOIN kelrekening_m kr ON kr.kelrekening_id = r1.kelrekening_id
        JOIN periodeposting_m pp ON pp.periodeposting_id = bb.periodeposting_id
        WHERE r5.rekening5_id IN (336, 337)
        AND bb.tglbukubesar BETWEEN :startDate AND :endDate
        {$whereRef2}

        UNION ALL

        SELECT
            r5.rekening5_id,
            r5.kdrekening5,
            r5.nmrekening5,
            bb.saldodebit,
            bb.saldokredit,
            bb.saldoawal_id,
            bb.uraiantransaksi,
            bb.tglbukubesar,
            jr.noreferensi,
            r5.tiperekening_id,
            r5.rekening5_nb AS saldonormal,
            jenisjurnal_m.jeniskode,
            bb.bukubesar_id
        FROM bukubesar_t bb
        JOIN jurnalposting_t jp ON bb.jurnalposting_id = jp.jurnalposting_id
        JOIN jurnaldetail_t jd ON jp.jurnaldetail_id = jd.jurnaldetail_id
        JOIN jurnalrekening_t jr ON jd.jurnalrekening_id = jr.jurnalrekening_id
        JOIN rekening5_m r5 ON bb.rekening5_id = r5.rekening5_id
        JOIN rekening4_m r4 ON r5.rekening4_id = r4.rekening4_id
        JOIN rekening3_m r3 ON r4.rekening3_id = r3.rekening3_id
        JOIN rekening2_m r2 ON r3.rekening2_id = r2.rekening2_id
        JOIN rekening1_m r1 ON r2.rekening1_id = r1.rekening1_id
        JOIN kelrekening_m kr ON kr.kelrekening_id = r1.kelrekening_id
        JOIN periodeposting_m pp ON pp.periodeposting_id = bb.periodeposting_id
        LEFT JOIN jenisjurnal_m ON jenisjurnal_m.jenisjurnal_id = jr.jenisjurnal_id 
        WHERE bb.saldoawal_id IS NOT NULL
        AND bb.tglbukubesar BETWEEN :startDate AND :endDate
        {$whereRef3}
        SQL;

        $cmd = Yii::$app->db->createCommand($sql);
        foreach ($queryParams as $key => $val) {
            $cmd->bindValue($key, $val);
        }

        $this->dataBukuBesar = array_column(
            $cmd->queryAll(),
            null,
            'bukubesar_id'
        );


    }

    public function setDataRekening()
    {
        $whereKd = "";
        $queryParams = [
            ':startDate' => $this->dateFrom,
            ':endDate'   => $this->dateTo,
        ];

        if (!empty($this->kdRekening5)) {
            $whereKd = " AND r5.kdrekening5 = :kdRekening5 ";
            $queryParams[':kdRekening5'] = trim($this->kdRekening5);
        }

        $sql = <<<SQL
        WITH saldo AS (
            SELECT
                t.rekening5_id,
                r.rekening5_nb,

                CASE 
                    WHEN r.rekening5_nb = 'D'
                        THEN SUM(t.jmlsaldoawald)
                    ELSE
                        0 - SUM(t.jmlsaldoawald)
                END AS saldo_awal_debit,

                CASE 
                    WHEN r.rekening5_nb = 'D'
                        THEN 0 - SUM(t.jmlsaldoawalk)
                    ELSE
                        SUM(t.jmlsaldoawalk)
                END AS saldo_awal_kredit,

                CASE 
                    WHEN r.rekening5_nb = 'D'
                        THEN SUM(t.jmlsaldoakhird)
                    ELSE
                        0 - SUM(t.jmlsaldoakhird)
                END AS saldo_akhir_debit,

                CASE 
                    WHEN r.rekening5_nb = 'D'
                        THEN 0 - SUM(t.jmlsaldoakhirk)
                    ELSE
                        SUM(t.jmlsaldoakhirk)
                END AS saldo_akhir_kredit

            FROM saldoawal_t t
            JOIN rekening5_m r 
                ON r.rekening5_id = t.rekening5_id
            JOIN rekperiod_m rkp 
                ON rkp.rekperiod_id = t.rekperiod_id
            WHERE 
                rkp.perideawal::date BETWEEN :startDate AND :endDate
                OR
                rkp.sampaidgn::date BETWEEN :startDate AND :endDate
            GROUP BY
                t.rekening5_id,
                r.rekening5_nb
        )

        SELECT
            r5.rekening5_id,
            r5.kdrekening5 AS kdrekening5,
            r5.nmrekening5 AS nama,
            r5.rekening5_nb AS nb,

            COALESCE(s.saldo_awal_debit, 0)  AS saldo_awal_debit,
            COALESCE(s.saldo_awal_kredit, 0) AS saldo_awal_kredit,
            COALESCE(s.saldo_akhir_debit, 0) AS saldo_akhir_debit,
            COALESCE(s.saldo_akhir_kredit, 0) AS saldo_akhir_kredit

        FROM rekening1_m r1
        JOIN kelrekening_m k ON k.kelrekening_id = r1.kelrekening_id
        JOIN rekening2_m r2 ON r2.rekening1_id = r1.rekening1_id
        JOIN rekening3_m r3 ON r3.rekening2_id = r2.rekening2_id
        JOIN rekening4_m r4 ON r4.rekening3_id = r3.rekening3_id
        JOIN rekening5_m r5 ON r5.rekening4_id = r4.rekening4_id

        LEFT JOIN saldo s 
            ON s.rekening5_id = r5.rekening5_id

        WHERE
            r1.rekening1_aktif = TRUE
            AND r2.rekening2_aktif = TRUE
            AND r3.rekening3_aktif = TRUE
            AND r4.rekening4_aktif = TRUE
            AND r5.rekening5_aktif = TRUE
            {$whereKd}
            AND (
                COALESCE(s.saldo_awal_debit, 0) <> 0
                OR COALESCE(s.saldo_awal_kredit, 0) <> 0
                OR COALESCE(s.saldo_akhir_debit, 0) <> 0
                OR COALESCE(s.saldo_akhir_kredit, 0) <> 0
            )
        SQL;

        $cmd = Yii::$app->db->createCommand($sql);
        foreach ($queryParams as $key => $val) {
            $cmd->bindValue($key, $val);
        }

        $this->dataRekening = array_column($cmd->queryAll(), null, 'kdrekening5');

    }

    public function setupSearch()
    {
        $this->dateFrom = Yii::$app->request->get('date_from');
        $this->dateTo = Yii::$app->request->get('date_to');
        $this->noReferensi = Yii::$app->request->get('no_referensi');
        $this->kdRekening5 = Yii::$app->request->get('kdrekening5');
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

    private function n($value)
    {
        return (float) ($value ?? 0);
    }

    public function actionExport()
    {
        $this->setupSearch();
        if ($this->statuscari) {
            $this->setDataRekening();
            $this->setDataBukuBesar();
            $this->setDataResult();
            
        }
                
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'Rumah Sakit Priscilla Medical Center');
        $sheet->setCellValue('A2', 'Laporan Neraca Saldo');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(18);
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(14);

        $rowExcel = 5;

        foreach ($this->res as $key => $row) {

            /** =========================
             *  HEADER GROUP
             *  ========================= */
            $sheet->setCellValue("A{$rowExcel}", $key);
            $sheet->setCellValue("B{$rowExcel}", $row['nama']);
            $rowExcel++;

            // header kolom
            $sheet->fromArray([
                'Tanggal',
                'Tp',
                'No. Ref.',
                'Keterangan',
                'Debit',
                'Kredit',
                'Saldo'
            ], null, "A{$rowExcel}");
            $rowExcel++;

            /** =========================
             *  SALDO AWAL
             *  ========================= */
            $saldodebit  = $this->n($row['saldo_awal_debit']);
            $saldokredit = $this->n($row['saldo_awal_kredit']);
            $totalsaldo  = $saldodebit + $saldokredit;

            $sheet->setCellValue("A{$rowExcel}", 'Saldo Awal');
            $sheet->setCellValue("B{$rowExcel}", '');
            $sheet->setCellValue("C{$rowExcel}", '');
            $sheet->setCellValue("D{$rowExcel}", '');

            if ($saldodebit == 0) {
                $sheet->setCellValue("E{$rowExcel}", '');
            } else {
                $sheet->setCellValueExplicit(
                    "E{$rowExcel}",
                    $saldodebit,
                    \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC
                );
            }

            if ($saldokredit == 0) {
                $sheet->setCellValue("F{$rowExcel}", '');
            } else {
                $sheet->setCellValueExplicit(
                    "F{$rowExcel}",
                    $saldokredit,
                    \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC
                );
            }

            $sheet->setCellValueExplicit(
                "G{$rowExcel}",
                $totalsaldo,
                \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC
            );

            // format angka (hanya apply ke yg numeric)
            $sheet->getStyle("E{$rowExcel}:G{$rowExcel}")
                ->getNumberFormat()
                ->setFormatCode('#,##0.00');

            $rowExcel++;

            /** =========================
             *  DETAIL MUTASI
             *  ========================= */
            $subdebit  = 0;
            $subkredit = 0;
            $saldo     = 0;
            $subsaldo  = 0;

            foreach ($row['data'] as $detail) {

                $debit  = (float) $detail['saldodebit'] ;
                $kredit = (float) $detail['saldokredit'];

                $subdebit  += $debit;
                $subkredit += $kredit;

                $saldonormal = $detail['saldonormal'] ?? 'D';

                // mutasi
                if ($saldonormal === 'K') {
                    $subsaldo += $kredit - $debit;
                    $saldo    += $kredit - $debit;
                } else {
                    $subsaldo += $debit - $kredit;
                    $saldo    += $debit - $kredit;
                }

                $sheet->setCellValue("A{$rowExcel}", date('d-m-Y', strtotime($detail['tglbukubesar'])));
                $sheet->setCellValue("B{$rowExcel}", $detail['jeniskode']);
                $sheet->setCellValue("C{$rowExcel}", $detail['noreferensi']);
                $sheet->setCellValue("D{$rowExcel}", $detail['uraiantransaksi']);

                $sheet->setCellValueExplicit("E{$rowExcel}", $debit,  \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                $sheet->setCellValueExplicit("F{$rowExcel}", $kredit, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                $sheet->setCellValueExplicit("G{$rowExcel}", $saldo,  \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);

                $sheet->getStyle("E{$rowExcel}:G{$rowExcel}")
                    ->getNumberFormat()
                    ->setFormatCode('#,##0.00');

                $rowExcel++;
            }

            /** =========================
             *  FOOTER / TOTAL
             *  ========================= */
            $saldoakhir = $totalsaldo + $subsaldo;

            // Total mutasi
            $sheet->fromArray([
                'Saldo Awal',
                $totalsaldo,
                '',
                'Total',
                $subdebit,
                $subkredit,
                ''
            ], null, "A{$rowExcel}");

            $sheet->setCellValueExplicit(
                "B{$rowExcel}",
                (float) $totalsaldo,
                \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC
            );

            $sheet->getStyle("B{$rowExcel}")
            ->getNumberFormat()
            ->setFormatCode('#,##0.00');

            $sheet->getStyle("E{$rowExcel}:F{$rowExcel}")
                ->getNumberFormat()
                ->setFormatCode('#,##0.00');
            
            $rowExcel++;

            // Saldo akhir
            $sheet->fromArray([
                'Saldo Akhir',
                $saldoakhir,
                '',
                'Mutasi',
                $subsaldo,
                '',
                ''
            ], null, "A{$rowExcel}");
            $sheet->getStyle("B{$rowExcel}")
            ->getNumberFormat()
            ->setFormatCode('#,##0.00');

            $sheet->getStyle("E{$rowExcel}")
            ->getNumberFormat()
            ->setFormatCode('#,##0.00');
            $rowExcel++;

            // spasi antar rekening
            $rowExcel += 2;
        }
        
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
