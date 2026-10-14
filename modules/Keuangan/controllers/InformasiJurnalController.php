<?php

namespace app\modules\keuangan\controllers;

use Yii;
use app\controllers\BaseController;
use yii\data\SqlDataProvider;
use yii\web\Response;
use yii\helpers\ArrayHelper;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use DateTime;

class InformasiJurnalController extends BaseController
{
    private function parseDateToSql($dateStr, $isEndTime = false)
    {
        if (empty($dateStr)) {
            return $isEndTime ? date('Y-m-t 23:59:59') : date('Y-m-01 00:00:00');
        }

        $dt = DateTime::createFromFormat('d-m-Y', $dateStr);
        if (!$dt) {
            $dt = DateTime::createFromFormat('Y-m-d', $dateStr);
        }
        if (!$dt) {
            $ts = strtotime($dateStr);
            if ($ts !== false) {
                $dt = new DateTime();
                $dt->setTimestamp($ts);
            }
        }

        if ($dt) {
            return $isEndTime ? $dt->format('Y-m-d 23:59:59') : $dt->format('Y-m-d 00:00:00');
        }

        return $isEndTime ? date('Y-m-t 23:59:59') : date('Y-m-01 00:00:00');
    }

    private function buildQueryAndParams($request)
    {
        $cari = $request->get('cari');
        $statusCari = !empty($cari);

        $dateFromRaw  = $request->get('date_from');
        $dateToRaw    = $request->get('date_to');
        $kodeJurnal   = trim($request->get('kodejurnal', ''));
        $urianJurnal  = trim($request->get('urianjurnal', ''));
        $kdRekening5  = trim($request->get('kdrekening5', ''));
        $jenisJurnalId = $request->get('jenisjurnal_id', '');

        // Jika belum submit pencarian, return query kosong dengan default tanggal bulan berjalan
        if (!$statusCari) {
            if (empty($dateFromRaw)) {
                $dateFromRaw = date('d-m-Y', strtotime(date('Y-m-01')));
            }
            if (empty($dateToRaw)) {
                $dateToRaw = date('d-m-Y', strtotime(date('Y-m-t')));
            }

            return [
                'sql'           => "SELECT 1 WHERE 1=0",
                'countSql'      => "SELECT 0",
                'summarySql'    => "SELECT 0 AS total_baris, 0 AS total_jurnal, 0 AS total_debit, 0 AS total_kredit, 0 AS selisih",
                'params'        => [],
                'statusCari'    => false,
                'dateFromRaw'   => $dateFromRaw,
                'dateToRaw'     => $dateToRaw,
                'kodeJurnal'    => $kodeJurnal,
                'urianJurnal'   => $urianJurnal,
                'kdRekening5'   => $kdRekening5,
                'jenisJurnalId' => $jenisJurnalId,
            ];
        }

        $whereConditions = [];
        $params          = [];

        // Filter Rentang Tanggal Bukti Jurnal jika diisi
        if (!empty($dateFromRaw) && !empty($dateToRaw)) {
            $formattedDateFrom = $this->parseDateToSql($dateFromRaw, false);
            $formattedDateTo   = $this->parseDateToSql($dateToRaw, true);
            $whereConditions[] = "jr.tglbuktijurnal >= :dateFrom AND jr.tglbuktijurnal <= :dateTo";
            $params[':dateFrom'] = $formattedDateFrom;
            $params[':dateTo']   = $formattedDateTo;
        } elseif (!empty($dateFromRaw)) {
            $formattedDateFrom = $this->parseDateToSql($dateFromRaw, false);
            $whereConditions[] = "jr.tglbuktijurnal >= :dateFrom";
            $params[':dateFrom'] = $formattedDateFrom;
        } elseif (!empty($dateToRaw)) {
            $formattedDateTo   = $this->parseDateToSql($dateToRaw, true);
            $whereConditions[] = "jr.tglbuktijurnal <= :dateTo";
            $params[':dateTo']   = $formattedDateTo;
        }

        // Filter Kode Jurnal (spesifik atau parsial)
        if (!empty($kodeJurnal)) {
            $whereConditions[] = "jr.kodejurnal ILIKE :kodejurnal";
            $params[':kodejurnal'] = '%' . $kodeJurnal . '%';
        }

        // Filter Uraian Jurnal / No Referensi / No Bukti Jurnal
        if (!empty($urianJurnal)) {
            $whereConditions[] = "(
                jr.urianjurnal ILIKE :urian
                OR jr.noreferensi ILIKE :urian
                OR jr.nobuktijurnal ILIKE :urian
                OR jd.uraiantransaksi ILIKE :urian
            )";
            $params[':urian'] = '%' . $urianJurnal . '%';
        }

        // Filter Kode Rekening 5
        if (!empty($kdRekening5)) {
            $whereConditions[] = "r5.kdrekening5 = :kdrekening5";
            $params[':kdrekening5'] = $kdRekening5;
        }

        // Filter Jenis Jurnal
        if (!empty($jenisJurnalId)) {
            $whereConditions[] = "jr.jenisjurnal_id = :jenisjurnal_id";
            $params[':jenisjurnal_id'] = (int)$jenisJurnalId;
        }

        $whereSql = !empty($whereConditions) ? implode(' AND ', $whereConditions) : '1=1';

        $baseFrom = "
            FROM jurnaldetail_t jd
            JOIN jurnalrekening_t jr ON jr.jurnalrekening_id = jd.jurnalrekening_id
            JOIN jenisjurnal_m ON jr.jenisjurnal_id = jenisjurnal_m.jenisjurnal_id
            LEFT JOIN jurnalposting_t jp ON jd.jurnalposting_id = jp.jurnalposting_id
            JOIN rekening5_m r5 ON jd.rekening5_id = r5.rekening5_id
            JOIN rekening4_m r4 ON r4.rekening4_id = r5.rekening4_id
            JOIN rekening3_m r3 ON r3.rekening3_id = r4.rekening3_id
            JOIN rekening2_m r2 ON r2.rekening2_id = r3.rekening2_id
            JOIN rekening1_m r1 ON r1.rekening1_id = r2.rekening1_id
            JOIN kelrekening_m kr ON kr.kelrekening_id = r1.kelrekening_id
            WHERE {$whereSql}
        ";

        $sql = "
            SELECT
                jenisjurnal_m.jenisjurnal_nama,
                jr.tglbuktijurnal,
                jr.nobuktijurnal,
                jr.kodejurnal,
                jr.noreferensi,
                jr.tglreferensi,
                jr.nobku,
                jr.urianjurnal,
                jr.create_time,
                jr.update_time,
                jp.tgljurnalpost,
                jp.keterangan,
                r1.kdrekening1,
                r1.nmrekening1,
                r2.kdrekening2,
                r2.nmrekening2,
                r3.kdrekening3,
                r3.nmrekening3,
                r4.kdrekening4,
                r4.nmrekening4,
                r5.kdrekening5,
                r5.nmrekening5,
                jd.nourut,
                jd.uraiantransaksi,
                jd.saldodebit,
                jd.saldokredit,
                jd.koreksi,
                jd.catatan
            {$baseFrom}
            ORDER BY jr.tglbuktijurnal DESC, jr.jurnalrekening_id DESC, jd.nourut ASC
        ";

        $countSql = "SELECT COUNT(1) {$baseFrom}";

        $summarySql = "
            SELECT
                COUNT(1) AS total_baris,
                COUNT(DISTINCT jr.jurnalrekening_id) AS total_jurnal,
                COALESCE(SUM(jd.saldodebit), 0) AS total_debit,
                COALESCE(SUM(jd.saldokredit), 0) AS total_kredit,
                COALESCE(SUM(jd.saldodebit) - SUM(jd.saldokredit), 0) AS selisih
            {$baseFrom}
        ";

        return [
            'sql'           => $sql,
            'countSql'      => $countSql,
            'summarySql'    => $summarySql,
            'params'        => $params,
            'statusCari'    => true,
            'dateFromRaw'   => $dateFromRaw,
            'dateToRaw'     => $dateToRaw,
            'kodeJurnal'    => $kodeJurnal,
            'urianJurnal'   => $urianJurnal,
            'kdRekening5'   => $kdRekening5,
            'jenisJurnalId' => $jenisJurnalId,
        ];
    }

    public function actionIndex()
    {
        $request    = Yii::$app->request;
        $q          = $this->buildQueryAndParams($request);
        $statusCari = $q['statusCari'];

        if ($statusCari) {
            try {
                $totalCount  = (int)Yii::$app->db->createCommand($q['countSql'], $q['params'])->queryScalar();
                $summaryData = Yii::$app->db->createCommand($q['summarySql'], $q['params'])->queryOne();
            } catch (\Exception $e) {
                Yii::$app->session->setFlash('error', 'Terjadi kesalahan query: ' . $e->getMessage());
                $totalCount  = 0;
                $summaryData = ['total_baris' => 0, 'total_jurnal' => 0, 'total_debit' => 0, 'total_kredit' => 0, 'selisih' => 0];
            }
        } else {
            $totalCount  = 0;
            $summaryData = ['total_baris' => 0, 'total_jurnal' => 0, 'total_debit' => 0, 'total_kredit' => 0, 'selisih' => 0];
        }

        $dataProvider = new SqlDataProvider([
            'sql'        => $q['sql'],
            'params'     => $q['params'],
            'totalCount' => $totalCount,
            'pagination' => [
                'pageSize' => 25,
            ],
        ]);

        $jenisJurnalList = ArrayHelper::map(
            Yii::$app->db->createCommand("SELECT jenisjurnal_id, jenisjurnal_nama FROM jenisjurnal_m ORDER BY jenisjurnal_id ASC")->queryAll(),
            'jenisjurnal_id',
            'jenisjurnal_nama'
        );

        $rekening5List = ArrayHelper::map(
            Yii::$app->db->createCommand("
                SELECT kdrekening5, kdrekening5 || ' - ' || nmrekening5 AS nama
                FROM rekening5_m
                WHERE rekening5_aktif = true
                ORDER BY kdrekening5 ASC
            ")->queryAll(),
            'kdrekening5',
            'nama'
        );

        return $this->render('index', [
            'dataProvider'    => $dataProvider,
            'statusCari'      => $statusCari,
            'dateFrom'        => $q['dateFromRaw'],
            'dateTo'          => $q['dateToRaw'],
            'kodeJurnal'      => $q['kodeJurnal'],
            'urianJurnal'     => $q['urianJurnal'],
            'kdRekening5'     => $q['kdRekening5'],
            'jenisJurnalId'   => $q['jenisJurnalId'],
            'jenisJurnalList' => $jenisJurnalList,
            'rekening5List'   => $rekening5List,
            'summary'         => $summaryData,
        ]);
    }

    public function actionExport()
    {
        $request = Yii::$app->request;
        $q       = $this->buildQueryAndParams($request);

        if (!$q['statusCari']) {
            Yii::$app->session->setFlash('warning', 'Silakan lakukan pencarian data terlebih dahulu sebelum melakukan export.');
            return $this->redirect(['index']);
        }

        $models = Yii::$app->db->createCommand($q['sql'], $q['params'])->queryAll();

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();

        // Header Dokumen
        $sheet->setCellValue('A1', 'Rumah Sakit Priscilla Medical Center');
        $sheet->setCellValue('A2', 'Laporan Informasi Jurnal Lengkap');
        $sheet->setCellValue('A3', 'Periode Bukti: ' . $q['dateFromRaw'] . ' s/d ' . $q['dateToRaw']);
        if (!empty($q['kodeJurnal'])) {
            $sheet->setCellValue('A4', 'Filter Kode Jurnal: ' . $q['kodeJurnal']);
        }
        $sheet->getStyle('A1:A4')->getFont()->setBold(true);
        $sheet->getStyle('A1')->getFont()->setSize(16);
        $sheet->getStyle('A2')->getFont()->setSize(13);

        // Header Tabel (Baris 6)
        $headers = [
            'No',
            'Jenis Jurnal',
            'Tgl Bukti Jurnal',
            'No Bukti Jurnal',
            'Kode Jurnal',
            'No Referensi',
            'Tgl Referensi',
            'No BKU',
            'Uraian Jurnal',
            'Tgl Posting',
            'Kd Rek 1',
            'Nm Rekening 1',
            'Kd Rek 2',
            'Nm Rekening 2',
            'Kd Rek 3',
            'Nm Rekening 3',
            'Kd Rek 4',
            'Nm Rekening 4',
            'Kd Rek 5',
            'Nm Rekening 5',
            'No Urut',
            'Uraian Transaksi',
            'Saldo Debit',
            'Saldo Kredit',
            'Koreksi',
            'Catatan'
        ];

        $colIdx    = 'A';
        $headerRow = 6;
        foreach ($headers as $h) {
            $sheet->setCellValue($colIdx . $headerRow, $h);
            $colIdx++;
        }
        $lastHeaderCol = chr(ord('A') + count($headers) - 1);
        $headerRange   = 'A' . $headerRow . ':' . $lastHeaderCol . $headerRow;

        $sheet->getStyle($headerRange)->getFont()->setBold(true);
        $sheet->getStyle($headerRange)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF002D72');
        $sheet->getStyle($headerRange)->getFont()->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Data Rows
        $rowIdx      = $headerRow + 1;
        $i           = 1;
        $totDebit    = 0;
        $totKredit   = 0;

        foreach ($models as $m) {
            $sDebit  = (float)($m['saldodebit'] ?? 0);
            $sKredit = (float)($m['saldokredit'] ?? 0);
            $totDebit  += $sDebit;
            $totKredit += $sKredit;

            $sheet->setCellValue('A' . $rowIdx, $i);
            $sheet->setCellValue('B' . $rowIdx, $m['jenisjurnal_nama']);
            $sheet->setCellValue('C' . $rowIdx, !empty($m['tglbuktijurnal']) ? date('d-m-Y H:i', strtotime($m['tglbuktijurnal'])) : '-');
            $sheet->setCellValue('D' . $rowIdx, $m['nobuktijurnal']);
            $sheet->setCellValue('E' . $rowIdx, $m['kodejurnal']);
            $sheet->setCellValue('F' . $rowIdx, $m['noreferensi']);
            $sheet->setCellValue('G' . $rowIdx, !empty($m['tglreferensi']) ? date('d-m-Y', strtotime($m['tglreferensi'])) : '-');
            $sheet->setCellValue('H' . $rowIdx, $m['nobku']);
            $sheet->setCellValue('I' . $rowIdx, $m['urianjurnal']);
            $sheet->setCellValue('J' . $rowIdx, !empty($m['tgljurnalpost']) ? date('d-m-Y H:i', strtotime($m['tgljurnalpost'])) : '-');
            $sheet->setCellValue('K' . $rowIdx, $m['kdrekening1']);
            $sheet->setCellValue('L' . $rowIdx, $m['nmrekening1']);
            $sheet->setCellValue('M' . $rowIdx, $m['kdrekening2']);
            $sheet->setCellValue('N' . $rowIdx, $m['nmrekening2']);
            $sheet->setCellValue('O' . $rowIdx, $m['kdrekening3']);
            $sheet->setCellValue('P' . $rowIdx, $m['nmrekening3']);
            $sheet->setCellValue('Q' . $rowIdx, $m['kdrekening4']);
            $sheet->setCellValue('R' . $rowIdx, $m['nmrekening4']);
            $sheet->setCellValue('S' . $rowIdx, $m['kdrekening5']);
            $sheet->setCellValue('T' . $rowIdx, $m['nmrekening5']);
            $sheet->setCellValue('U' . $rowIdx, $m['nourut']);
            $sheet->setCellValue('V' . $rowIdx, $m['uraiantransaksi']);
            $sheet->setCellValue('W' . $rowIdx, $sDebit);
            $sheet->setCellValue('X' . $rowIdx, $sKredit);
            $sheet->setCellValue('Y' . $rowIdx, $m['koreksi']);
            $sheet->setCellValue('Z' . $rowIdx, $m['catatan']);

            $sheet->getStyle('W' . $rowIdx . ':X' . $rowIdx)->getNumberFormat()->setFormatCode('#,##0.00');

            $rowIdx++;
            $i++;
        }

        // Summary Row
        $sheet->setCellValue('A' . $rowIdx, 'TOTAL');
        $sheet->mergeCells('A' . $rowIdx . ':V' . $rowIdx);
        $sheet->setCellValue('W' . $rowIdx, $totDebit);
        $sheet->setCellValue('X' . $rowIdx, $totKredit);
        $sheet->getStyle('W' . $rowIdx . ':X' . $rowIdx)->getNumberFormat()->setFormatCode('#,##0.00');

        $totalRowRange = 'A' . $rowIdx . ':' . $lastHeaderCol . $rowIdx;
        $sheet->getStyle($totalRowRange)->getFont()->setBold(true);
        $sheet->getStyle($totalRowRange)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE9ECEF');

        // Border & Auto Size
        $dataRange = 'A' . $headerRow . ':' . $lastHeaderCol . $rowIdx;
        $sheet->getStyle($dataRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        foreach (range('A', $lastHeaderCol) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $fileName = 'Informasi_Jurnal_' . date('Ymd_His') . '.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), 'export_info_jurnal');
        $writer   = new Xlsx($spreadsheet);
        $writer->save($tempFile);

        return Yii::$app->response->sendFile($tempFile, $fileName)->on(
            Response::EVENT_AFTER_SEND,
            function ($event) {
                if (file_exists($event->data)) {
                    @unlink($event->data);
                }
            },
            $tempFile
        );
    }
}
