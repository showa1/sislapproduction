<?php

namespace app\modules\Pendaftaran\controllers;

use Yii;
use app\controllers\BaseController;
use yii\data\SqlDataProvider;
use yii\web\Response;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class PasienBaruLamaController extends BaseController
{
    public function actionIndex()
    {
        $request  = Yii::$app->request;
        $dateFrom = $request->get('date_from', date('Y-m-01'));
        $dateTo   = $request->get('date_to', date('Y-m-t'));
        $status   = $request->get('status', ''); // '' | 'baru' | 'lama'
        $search   = trim((string)$request->get('search', ''));
        $export   = $request->get('export', '0');

        $params = [
            ':df' => $dateFrom,
            ':dt' => $dateTo,
        ];

        $searchWhere = "";
        if ($search !== '') {
            $searchWhere = " AND (LOWER(pm.no_rekam_medik) LIKE LOWER(:search) OR LOWER(pm.nama_pasien) LIKE LOWER(:search)) ";
            $params[':search'] = '%' . $search . '%';
        }

        // =========================================================================
        // 1. Query Utama Pasien Baru vs Pasien Lama (sesuai rumus query yang diminta)
        // =========================================================================
        $sqlSummary = "
            SELECT
                COUNT(DISTINCT pt.pasien_id) FILTER (
                    WHERE pm.create_time::date BETWEEN :df::date AND :dt::date
                ) AS total_pasien_baru,

                COUNT(DISTINCT pt.pasien_id) FILTER (
                    WHERE pm.create_time::date < :df::date
                ) AS total_pasien_lama

            FROM pendaftaran_t pt
            JOIN pasien_m pm
                ON pm.pasien_id = pt.pasien_id

            WHERE pt.tgl_pendaftaran::date BETWEEN :df::date AND :dt::date
              AND pt.pasienbatalperiksa_id IS NULL
              {$searchWhere}
        ";

        try {
            $summaryRow = Yii::$app->db->createCommand($sqlSummary, $params)->queryOne();
        } catch (\Exception $e) {
            $summaryRow = ['total_pasien_baru' => 0, 'total_pasien_lama' => 0];
        }

        $totalBaru  = (int)($summaryRow['total_pasien_baru'] ?? 0);
        $totalLama  = (int)($summaryRow['total_pasien_lama'] ?? 0);
        $totalSemua = $totalBaru + $totalLama;

        $summary = [
            'total_baru'  => $totalBaru,
            'total_lama'  => $totalLama,
            'total_semua' => $totalSemua,
            'pct_baru'    => $totalSemua > 0 ? round(($totalBaru / $totalSemua) * 100, 1) : 0,
            'pct_lama'    => $totalSemua > 0 ? round(($totalLama / $totalSemua) * 100, 1) : 0,
        ];

        // =========================================================================
        // 2. Rekapitulasi Bulanan (Januari - Desember) pada Tahun dari $dateFrom
        // =========================================================================
        $tahun = (int)date('Y', strtotime($dateFrom));
        $sqlMonthly = "
            SELECT
                EXTRACT(MONTH FROM pt.tgl_pendaftaran)::int AS bulan,
                COUNT(DISTINCT pt.pasien_id) FILTER (
                    WHERE pm.create_time::date >= DATE_TRUNC('month', pt.tgl_pendaftaran)::date
                      AND pm.create_time::date <= (DATE_TRUNC('month', pt.tgl_pendaftaran) + INTERVAL '1 month - 1 day')::date
                ) AS pasien_baru,
                COUNT(DISTINCT pt.pasien_id) FILTER (
                    WHERE pm.create_time::date < DATE_TRUNC('month', pt.tgl_pendaftaran)::date
                ) AS pasien_lama
            FROM pendaftaran_t pt
            JOIN pasien_m pm ON pm.pasien_id = pt.pasien_id
            WHERE EXTRACT(YEAR FROM pt.tgl_pendaftaran) = :tahun
              AND pt.pasienbatalperiksa_id IS NULL
            GROUP BY EXTRACT(MONTH FROM pt.tgl_pendaftaran)
            ORDER BY bulan ASC
        ";

        try {
            $monthlyRows = Yii::$app->db->createCommand($sqlMonthly, [':tahun' => $tahun])->queryAll();
        } catch (\Exception $e) {
            $monthlyRows = [];
        }

        $monthly = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthly[$m] = ['baru' => 0, 'lama' => 0, 'total' => 0];
        }
        foreach ($monthlyRows as $mr) {
            $b = (int)$mr['bulan'];
            $baru = (int)$mr['pasien_baru'];
            $lama = (int)$mr['pasien_lama'];
            $monthly[$b] = [
                'baru'  => $baru,
                'lama'  => $lama,
                'total' => $baru + $lama,
            ];
        }

        // =========================================================================
        // 3. Query Daftar Detail Pasien Unik pada Periode :df s/d :dt
        // =========================================================================
        $statusFilterSql = "";
        if ($status === 'baru') {
            $statusFilterSql = " AND pm.create_time::date BETWEEN :df::date AND :dt::date ";
        } elseif ($status === 'lama') {
            $statusFilterSql = " AND pm.create_time::date < :df::date ";
        } else {
            $statusFilterSql = " AND pm.create_time::date <= :dt::date ";
        }

        $detailBaseFrom = "
            FROM pendaftaran_t pt
            JOIN pasien_m pm ON pm.pasien_id = pt.pasien_id
            LEFT JOIN ruangan_m rm ON rm.ruangan_id = pt.ruangan_id
            LEFT JOIN carabayar_m cm ON cm.carabayar_id = pt.carabayar_id
            WHERE pt.tgl_pendaftaran::date BETWEEN :df::date AND :dt::date
              AND pt.pasienbatalperiksa_id IS NULL
              {$searchWhere}
              {$statusFilterSql}
        ";

        $sqlDetail = "
            SELECT * FROM (
                SELECT DISTINCT ON (pt.pasien_id)
                    pt.pasien_id,
                    pt.tgl_pendaftaran,
                    pt.no_pendaftaran,
                    pm.no_rekam_medik,
                    pm.nama_pasien,
                    pm.create_time AS tgl_rekam_medik,
                    rm.ruangan_nama,
                    cm.carabayar_nama,
                    CASE
                        WHEN pm.create_time::date BETWEEN :df::date AND :dt::date THEN 'Pasien Baru'
                        ELSE 'Pasien Lama'
                    END AS kategori_pasien
                {$detailBaseFrom}
                ORDER BY pt.pasien_id, pt.tgl_pendaftaran DESC
            ) sub
            ORDER BY sub.tgl_pendaftaran DESC
        ";

        $countSql = "
            SELECT COUNT(DISTINCT pt.pasien_id)
            {$detailBaseFrom}
        ";

        if ($export === '1') {
            $detailData = Yii::$app->db->createCommand($sqlDetail, $params)->queryAll();
            return $this->exportExcel($dateFrom, $dateTo, $tahun, $summary, $monthly, $detailData);
        }

        try {
            $totalCount = (int)Yii::$app->db->createCommand($countSql, $params)->queryScalar();
        } catch (\Exception $e) {
            $totalCount = 0;
        }

        $dataProvider = new SqlDataProvider([
            'sql'        => $sqlDetail,
            'params'     => $params,
            'totalCount' => $totalCount,
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);

        return $this->render('index', [
            'dateFrom'     => $dateFrom,
            'dateTo'       => $dateTo,
            'tahun'        => $tahun,
            'status'       => $status,
            'search'       => $search,
            'summary'      => $summary,
            'monthly'      => $monthly,
            'dataProvider' => $dataProvider,
        ]);
    }

    private function exportExcel($dateFrom, $dateTo, $tahun, array $summary, array $monthly, array $detailData)
    {
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Pasien Baru & Lama');

        $sheet->setCellValue('A1', 'Rumah Sakit Priscilla Medical Center');
        $sheet->setCellValue('A2', 'Laporan Pasien Baru / Lama');
        $sheet->setCellValue('A3', 'Periode: ' . $dateFrom . ' s/d ' . $dateTo);
        $sheet->getStyle('A1:A3')->getFont()->setBold(true);

        // Ringkasan Periode
        $sheet->setCellValue('A5', 'Total Pasien Baru');
        $sheet->setCellValue('B5', $summary['total_baru'] . ' (' . $summary['pct_baru'] . '%)');
        $sheet->setCellValue('A6', 'Total Pasien Lama');
        $sheet->setCellValue('B6', $summary['total_lama'] . ' (' . $summary['pct_lama'] . '%)');
        $sheet->setCellValue('A7', 'Total Pasien Unik');
        $sheet->setCellValue('B7', $summary['total_semua']);
        $sheet->getStyle('A5:B7')->getFont()->setBold(true);

        // Tabel Detail Pasien
        $headers = ['No', 'Tgl Pendaftaran', 'No. Pendaftaran', 'No. RM', 'Nama Pasien', 'Tgl Buat RM (create_time)', 'Ruangan', 'Cara Bayar', 'Kategori Pasien'];
        $col     = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($col . '9', $h);
            $col++;
        }

        $sheet->getStyle('A9:I9')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '002D72']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $rowIdx = 10;
        $i      = 1;
        foreach ($detailData as $r) {
            $sheet->setCellValue('A' . $rowIdx, $i);
            $sheet->setCellValue('B' . $rowIdx, !empty($r['tgl_pendaftaran']) ? date('d/m/Y H:i', strtotime($r['tgl_pendaftaran'])) : '-');
            $sheet->setCellValueExplicit('C' . $rowIdx, $r['no_pendaftaran'] ?? '-', DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('D' . $rowIdx, $r['no_rekam_medik'] ?? '-', DataType::TYPE_STRING);
            $sheet->setCellValue('E' . $rowIdx, $r['nama_pasien'] ?? '-');
            $sheet->setCellValue('F' . $rowIdx, !empty($r['tgl_rekam_medik']) ? date('d/m/Y', strtotime($r['tgl_rekam_medik'])) : '-');
            $sheet->setCellValue('G' . $rowIdx, $r['ruangan_nama'] ?? '-');
            $sheet->setCellValue('H' . $rowIdx, $r['carabayar_nama'] ?? '-');
            $sheet->setCellValue('I' . $rowIdx, $r['kategori_pasien'] ?? '-');
            $rowIdx++;
            $i++;
        }

        $lastRow = max(9, $rowIdx - 1);
        $sheet->getStyle('A9:I' . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        foreach (range('A', 'I') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $writer   = new Xlsx($spreadsheet);
        $fileName = 'Laporan_Pasien_Baru_Lama_' . $dateFrom . '_sd_' . $dateTo . '.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), $fileName);
        $writer->save($tempFile);

        return Yii::$app->response->sendFile($tempFile, $fileName)->on(
            Response::EVENT_AFTER_SEND,
            function ($event) {
                @unlink($event->data);
            },
            $tempFile
        );
    }
}

