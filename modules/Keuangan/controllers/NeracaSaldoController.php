<?php

namespace app\modules\keuangan\controllers;

use app\controllers\BaseController;
use yii\data\SqlDataProvider;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Yii;
use DateTime;

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
            'start' => Yii::$app->request->get('date_from'),
            'to' => Yii::$app->request->get('date_to'),
        ];

        return $this->render('index', [
            'dataProvider' => $this->dataprovider(),
            'dropdownselect' => $dropdownselect,
            'statuscari' => $this->statuscari,
        ]);
    }

    public function setupSearch()
    {
        $cari = Yii::$app->request->get('cari');
        $this->statuscari = !empty($cari);

        $dateFromRaw = Yii::$app->request->get('date_from');
        $dateToRaw = Yii::$app->request->get('date_to');

        if (!empty($dateFromRaw)) {
            $d = DateTime::createFromFormat('d-m-Y', $dateFromRaw);
            $this->dateFrom = $d ? $d->format('Y-m-d') . ' 00:00:00' : date('Y-m-01 00:00:00');
        } else {
            $this->dateFrom = date('Y-m-01 00:00:00');
        }

        if (!empty($dateToRaw)) {
            $d = DateTime::createFromFormat('d-m-Y', $dateToRaw);
            $this->dateTo = $d ? $d->format('Y-m-d') . ' 23:59:59' : date('Y-m-t 23:59:59');
        } else {
            $this->dateTo = date('Y-m-t 23:59:59');
        }
    }

    public function dataprovider()
    {
        return new SqlDataProvider([
            'sql' => $this->statuscari ? $this->baseQuery() : $this->queryKosong(),
            'params' => $this->params,
            'totalCount' => 0,
            'pagination' => false,
        ]);
    }

    public function actionExport()
    {
        $this->setupSearch();
        $dataProvider = new SqlDataProvider([
            'sql' => $this->statuscari ? $this->baseQuery() : $this->queryKosong(),
            'params' => $this->params,
            'pagination' => false,
        ]);

        $models = $dataProvider->getModels();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'Rumah Sakit Priscilla Medical Center');
        $sheet->setCellValue('A2', 'Laporan Neraca Saldo');

        $dateFromRaw = Yii::$app->request->get('date_from');
        $dateToRaw = Yii::$app->request->get('date_to');
        $periodeText = 'Periode: ';
        if (!empty($dateFromRaw) && !empty($dateToRaw)) {
            $periodeText .= $dateFromRaw . ' s/d ' . $dateToRaw;
        } else {
            $periodeText .= 'Semua Periode';
        }
        $sheet->setCellValue('A3', $periodeText);

        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(13);
        $sheet->getStyle('A3')->getFont()->setItalic(true)->setSize(11);

        // Header Tabel
        $headers = [
            'A5' => 'No',
            'B5' => 'No Akun',
            'C5' => 'Nama Akun',
            'D5' => 'Saldo Awal',
            'E5' => 'Debit',
            'F5' => 'Kredit',
            'G5' => 'Saldo Akhir',
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        $sheet->getStyle('A5:G5')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '002D72'],
            ],
        ]);

        $i = 1;
        $row = 6;
        foreach ($models as $model) {
            $isTotal = (($model['urutan'] ?? 1) == 2) || (($model['nama_akun'] ?? '') === 'TOTAL SALDO');

            if ($isTotal) {
                $sheet->setCellValue('A' . $row, '');
                $sheet->setCellValue('B' . $row, '');
                $sheet->setCellValue('C' . $row, $model['nama_akun'] ?? 'TOTAL SALDO');
            } else {
                $sheet->setCellValue('A' . $row, $i);
                $sheet->setCellValueExplicit('B' . $row, (string) ($model['no_akun'] ?? ''), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValue('C' . $row, $model['nama_akun'] ?? '');
                $i++;
            }

            $sheet->setCellValue('D' . $row, (float) ($model['saldo_awal'] ?? 0));
            $sheet->setCellValue('E' . $row, (float) ($model['debit'] ?? 0));
            $sheet->setCellValue('F' . $row, (float) ($model['kredit'] ?? 0));
            $sheet->setCellValue('G' . $row, (float) ($model['saldo_akhir'] ?? 0));

            if ($isTotal) {
                $sheet->getStyle("A{$row}:G{$row}")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'E3F2FD'],
                    ],
                ]);
            }

            $row++;
        }

        $lastRow = max(5, $row - 1);
        if ($lastRow >= 6) {
            $sheet->getStyle('A6:A' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B6:B' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('D6:G' . $lastRow)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('A5:G' . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        }

        // Auto width
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'laporan-neraca-saldo.xlsx';
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
        return "SELECT 1 WHERE 1=0";
    }

    public function baseQuery()
    {
        $query = "
            WITH
            -- 1. Ambil saldo awal terakhir sebelum tanggal awal periode
            saldo_awal AS (
                SELECT DISTINCT ON (v.rekening5_id)
                    v.rekening5_id,
                    v.kdrekening5,
                    v.nmrekening5,
                    v.saldonormal,
                    COALESCE(v.saldodebit, 0)
                      - COALESCE(v.saldokredit, 0) AS saldo_awal
                FROM public.laporanbukubesar_v v
                WHERE
                    v.saldoawal_id IS NOT NULL
                    AND v.tglperiodeposting_awal < CAST(:datefrom AS TIMESTAMP)
                ORDER BY
                    v.rekening5_id,
                    v.tglperiodeposting_awal DESC,
                    v.bukubesar_id DESC
            ),

            -- 2. Rekap transaksi debit dan kredit periode berjalan
            mutasi AS (
                SELECT
                    v.rekening5_id,
                    v.kdrekening5,
                    v.nmrekening5,
                    v.saldonormal,
                    SUM(COALESCE(v.saldodebit, 0)) AS debit,
                    SUM(COALESCE(v.saldokredit, 0)) AS kredit
                FROM public.laporanbukubesar_v v
                WHERE
                    v.saldoawal_id IS NULL
                    AND v.tglbukubesar >= CAST(:datefrom AS TIMESTAMP)
                    AND v.tglbukubesar <= CAST(:dateto AS TIMESTAMP)
                GROUP BY
                    v.rekening5_id,
                    v.kdrekening5,
                    v.nmrekening5,
                    v.saldonormal
            ),

            -- 3. Gabungkan saldo awal dan mutasi
            rekap AS (
                SELECT
                    COALESCE(s.rekening5_id, m.rekening5_id) AS rekening5_id,
                    COALESCE(s.kdrekening5, m.kdrekening5) AS kdrekening5,
                    COALESCE(s.nmrekening5, m.nmrekening5) AS nmrekening5,
                    COALESCE(s.saldonormal, m.saldonormal) AS saldonormal,
                    COALESCE(s.saldo_awal, 0) AS saldo_awal,
                    COALESCE(m.debit, 0) AS debit,
                    COALESCE(m.kredit, 0) AS kredit
                FROM saldo_awal s
                FULL JOIN mutasi m
                    ON m.rekening5_id = s.rekening5_id
            ),

            -- 4. Hitung saldo akhir setiap rekening
            hasil AS (
                SELECT
                    kdrekening5,
                    nmrekening5,
                    ROUND(saldo_awal::numeric, 2) AS saldo_awal,
                    ROUND(debit::numeric, 2) AS debit,
                    ROUND(kredit::numeric, 2) AS kredit,
                    ROUND(
                        (saldo_awal + debit - kredit)::numeric,
                        2
                    ) AS saldo_akhir
                FROM rekap
                WHERE
                    saldo_awal <> 0
                    OR debit <> 0
                    OR kredit <> 0
            ),

            -- 5. Tambahkan baris total
            laporan AS (
                SELECT
                    1 AS urutan,
                    kdrekening5 AS no_akun,
                    nmrekening5 AS nama_akun,
                    saldo_awal,
                    debit,
                    kredit,
                    saldo_akhir
                FROM hasil

                UNION ALL

                SELECT
                    2 AS urutan,
                    NULL AS no_akun,
                    'TOTAL SALDO' AS nama_akun,
                    ROUND(SUM(saldo_awal)::numeric, 2) AS saldo_awal,
                    ROUND(SUM(debit)::numeric, 2) AS debit,
                    ROUND(SUM(kredit)::numeric, 2) AS kredit,
                    ROUND(SUM(saldo_akhir)::numeric, 2) AS saldo_akhir
                FROM hasil
            )

            SELECT
                urutan,
                no_akun,
                nama_akun,
                saldo_awal,
                debit,
                kredit,
                saldo_akhir
            FROM laporan
            ORDER BY
                urutan,
                no_akun;
        ";

        $query = $this->queryFilter($query);

        return $query;
    }

    public function queryFilter($query)
    {
        $this->params = array_merge($this->params, [
            ':datefrom' => $this->dateFrom,
            ':dateto' => $this->dateTo,
        ]);

        return $query;
    }

}

