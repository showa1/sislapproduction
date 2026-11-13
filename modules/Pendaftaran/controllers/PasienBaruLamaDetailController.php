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

class PasienBaruLamaDetailController extends BaseController
{
    public function actionIndex()
    {
        $request  = Yii::$app->request;
        $dateFrom = $request->get('date_from', date('Y-m-01'));
        $dateTo   = $request->get('date_to', date('Y-m-t'));
        $status   = $request->get('status', ''); // '' | 'Pasien Baru' | 'Pasien Lama'
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

        // Hitung ringkasan Pasien Baru & Pasien Lama pada periode terpilih
        $sqlSummary = "
            SELECT
                COUNT(DISTINCT pt.pasien_id) FILTER (
                    WHERE pm.create_time::date BETWEEN :df::date AND :dt::date
                ) AS total_pasien_baru,
                COUNT(DISTINCT pt.pasien_id) FILTER (
                    WHERE pm.create_time::date < :df::date
                ) AS total_pasien_lama
            FROM pendaftaran_t pt
            JOIN pasien_m pm ON pm.pasien_id = pt.pasien_id
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

        // Filter Kategori Pasien (jika dipilih di dropdown)
        $statusWherePasienPeriode = "";
        if ($status === 'Pasien Baru') {
            $statusWherePasienPeriode = " AND pm.create_time::date BETWEEN :df::date AND :dt::date ";
        } elseif ($status === 'Pasien Lama') {
            $statusWherePasienPeriode = " AND pm.create_time::date < :df::date ";
        }

        // Query CTE sesuai yang diminta
        $sql = "
            WITH pasien_periode AS (
                SELECT DISTINCT
                    pt.pasien_id,
                    pm.no_rekam_medik,
                    pm.nama_pasien,
                    pm.create_time
                FROM pendaftaran_t pt
                JOIN pasien_m pm
                    ON pm.pasien_id = pt.pasien_id
                WHERE pt.tgl_pendaftaran::date
                      BETWEEN :df::date AND :dt::date
                  AND pt.pasienbatalperiksa_id IS NULL
                  {$searchWhere}
                  {$statusWherePasienPeriode}
            ),

            kunjungan_periode AS (
                SELECT
                    pt.pasien_id,
                    STRING_AGG(
                        DISTINCT TO_CHAR(pt.tgl_pendaftaran::date, 'DD-MM-YYYY'),
                        ', '
                        ORDER BY TO_CHAR(pt.tgl_pendaftaran::date, 'DD-MM-YYYY')
                    ) AS tanggal_kunjungan_periode
                FROM pendaftaran_t pt
                WHERE pt.tgl_pendaftaran::date
                      BETWEEN :df::date AND :dt::date
                  AND pt.pasienbatalperiksa_id IS NULL
                GROUP BY pt.pasien_id
            ),

            histori_kunjungan AS (
                SELECT
                    pt.pasien_id,
                    STRING_AGG(
                        DISTINCT TO_CHAR(pt.tgl_pendaftaran::date, 'DD-MM-YYYY'),
                        ', '
                        ORDER BY TO_CHAR(pt.tgl_pendaftaran::date, 'DD-MM-YYYY')
                    ) AS seluruh_histori_kunjungan
                FROM pendaftaran_t pt
                WHERE pt.pasienbatalperiksa_id IS NULL
                  AND pt.pasien_id IN (SELECT pasien_id FROM pasien_periode)
                GROUP BY pt.pasien_id
            )

            SELECT
                pp.pasien_id,
                pp.no_rekam_medik,
                pp.nama_pasien,

                TO_CHAR(pp.create_time::date, 'DD-MM-YYYY') AS tanggal_create_rm,

                kp.tanggal_kunjungan_periode,

                hk.seluruh_histori_kunjungan,

                CASE
                    WHEN pp.create_time::date BETWEEN :df::date AND :dt::date
                    THEN 'Pasien Baru'
                    ELSE 'Pasien Lama'
                END AS kategori_pasien

            FROM pasien_periode pp

            LEFT JOIN kunjungan_periode kp
                ON kp.pasien_id = pp.pasien_id

            LEFT JOIN histori_kunjungan hk
                ON hk.pasien_id = pp.pasien_id

            ORDER BY
                kategori_pasien,
                pp.no_rekam_medik
        ";

        $countSql = "
            SELECT COUNT(DISTINCT pt.pasien_id)
            FROM pendaftaran_t pt
            JOIN pasien_m pm ON pm.pasien_id = pt.pasien_id
            WHERE pt.tgl_pendaftaran::date BETWEEN :df::date AND :dt::date
              AND pt.pasienbatalperiksa_id IS NULL
              {$searchWhere}
              {$statusWherePasienPeriode}
        ";

        if ($export === '1') {
            $data = Yii::$app->db->createCommand($sql, $params)->queryAll();
            return $this->exportExcel($dateFrom, $dateTo, $summary, $data);
        }

        try {
            $totalCount = (int)Yii::$app->db->createCommand($countSql, $params)->queryScalar();
        } catch (\Exception $e) {
            $totalCount = 0;
        }

        $dataProvider = new SqlDataProvider([
            'sql'        => $sql,
            'params'     => $params,
            'totalCount' => $totalCount,
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);

        return $this->render('index', [
            'dateFrom'     => $dateFrom,
            'dateTo'       => $dateTo,
            'status'       => $status,
            'search'       => $search,
            'summary'      => $summary,
            'dataProvider' => $dataProvider,
        ]);
    }

    private function exportExcel($dateFrom, $dateTo, array $summary, array $data)
    {
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Pasien Baru Lama Detail');

        $sheet->setCellValue('A1', 'Rumah Sakit Priscilla Medical Center');
        $sheet->setCellValue('A2', 'Laporan Pasien Baru / Lama Detail');
        $sheet->setCellValue('A3', 'Periode: ' . $dateFrom . ' s/d ' . $dateTo);
        $sheet->getStyle('A1:A3')->getFont()->setBold(true);

        $headers = [
            'No',
            'Pasien ID',
            'No. Rekam Medik',
            'Nama Pasien',
            'Tanggal Create RM',
            'Tanggal Kunjungan Periode',
            'Seluruh Histori Kunjungan',
            'Kategori Pasien',
        ];

        $col = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($col . '5', $h);
            $col++;
        }

        $sheet->getStyle('A5:H5')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '002D72']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $rowIdx = 6;
        $i      = 1;
        foreach ($data as $r) {
            $sheet->setCellValue('A' . $rowIdx, $i);
            $sheet->setCellValue('B' . $rowIdx, $r['pasien_id'] ?? '-');
            $sheet->setCellValueExplicit('C' . $rowIdx, $r['no_rekam_medik'] ?? '-', DataType::TYPE_STRING);
            $sheet->setCellValue('D' . $rowIdx, $r['nama_pasien'] ?? '-');
            $sheet->setCellValue('E' . $rowIdx, $r['tanggal_create_rm'] ?? '-');
            $sheet->setCellValue('F' . $rowIdx, $r['tanggal_kunjungan_periode'] ?? '-');
            $sheet->setCellValue('G' . $rowIdx, $r['seluruh_histori_kunjungan'] ?? '-');
            $sheet->setCellValue('H' . $rowIdx, $r['kategori_pasien'] ?? '-');
            $rowIdx++;
            $i++;
        }

        $lastRow = max(5, $rowIdx - 1);
        $sheet->getStyle('A5:H' . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        foreach (range('A', 'H') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $writer   = new Xlsx($spreadsheet);
        $fileName = 'Laporan_Pasien_Baru_Lama_Detail_' . $dateFrom . '_sd_' . $dateTo . '.xlsx';
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

