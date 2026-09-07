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

class RekapitulasiPasienMcuController extends BaseController
{
    private function buildQueryAndParams($request)
    {
        $search = trim($request->get('search', ''));
        $dateFrom = $request->get('date_from', date('Y-m-01'));
        $dateTo = $request->get('date_to', date('Y-m-t'));
        $sortOrder = $request->get('sort_order', 'pasien_desc');

        // Robust date parsing for database query
        $formattedDateFrom = date('Y-m-d', strtotime($dateFrom));
        $formattedDateTo = date('Y-m-d', strtotime($dateTo));

        // Determine SQL ORDER BY clause based on sort_order parameter
        switch ($sortOrder) {
            case 'pasien_asc':
                $orderBy = "jumlah_pasien ASC, tp.tipepaket_nama ASC";
                break;
            case 'nama_asc':
                $orderBy = "tp.tipepaket_nama ASC";
                break;
            case 'nama_desc':
                $orderBy = "tp.tipepaket_nama DESC";
                break;
            case 'tarif_desc':
                $orderBy = "tp.tarifpaket DESC, tp.tipepaket_nama ASC";
                break;
            case 'tarif_asc':
                $orderBy = "tp.tarifpaket ASC, tp.tipepaket_nama ASC";
                break;
            case 'pasien_desc':
            default:
                $orderBy = "jumlah_pasien DESC, tp.tipepaket_nama ASC";
                break;
        }

        $sql = "
            SELECT 
                tp.tipepaket_id,
                tp.tipepaket_nama AS nama_paket_mcu,
                tp.tarifpaket AS tarif_per_paket,
                COUNT(DISTINCT lpm.pendaftaran_id) AS jumlah_pasien
            FROM tipepaket_m tp
            LEFT JOIN laporanpelayananmcu_v lpm ON tp.tipepaket_id = lpm.tipepaket_id
                AND DATE(lpm.tgl_tindakan) BETWEEN :date_from AND :date_to
            WHERE (tp.tipepaket_nama ILIKE '%MCU%' OR tp.tipepaket_nama ILIKE '%PAKET%')
        ";

        $countSql = "
            SELECT COUNT(DISTINCT tp.tipepaket_id)
            FROM tipepaket_m tp
            WHERE (tp.tipepaket_nama ILIKE '%MCU%' OR tp.tipepaket_nama ILIKE '%PAKET%')
        ";

        $params = [
            ':date_from' => $formattedDateFrom,
            ':date_to' => $formattedDateTo,
        ];

        $countParams = [];

        if (!empty($search)) {
            $sql .= " AND LOWER(tp.tipepaket_nama) LIKE LOWER(:search)";
            $countSql .= " AND LOWER(tp.tipepaket_nama) LIKE LOWER(:search)";
            $params[':search'] = '%' . $search . '%';
            $countParams[':search'] = '%' . $search . '%';
        }

        $sql .= "
            GROUP BY tp.tipepaket_id, tp.tipepaket_nama, tp.tarifpaket
            ORDER BY {$orderBy}
        ";

        return [
            'sql' => $sql,
            'countSql' => $countSql,
            'params' => $params,
            'countParams' => $countParams,
            'dateFrom' => $formattedDateFrom,
            'dateTo' => $formattedDateTo,
            'search' => $search,
            'sortOrder' => $sortOrder,
        ];
    }

    public function actionIndex()
    {
        $request = Yii::$app->request;
        $q = $this->buildQueryAndParams($request);

        try {
            $totalCount = Yii::$app->db->createCommand($q['countSql'], $q['countParams'])->queryScalar();
        } catch (\Exception $e) {
            $totalCount = 0;
        }

        $dataProvider = new SqlDataProvider([
            'sql' => $q['sql'],
            'params' => $q['params'],
            'totalCount' => $totalCount,
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);

        // Calculate summary cards info
        $summarySql = "
            SELECT 
                COUNT(DISTINCT tp.tipepaket_id) AS total_paket,
                SUM(t.jumlah_pasien) AS total_pasien
            FROM (
                SELECT tp.tipepaket_id, COUNT(DISTINCT lpm.pendaftaran_id) AS jumlah_pasien
                FROM tipepaket_m tp
                LEFT JOIN laporanpelayananmcu_v lpm ON tp.tipepaket_id = lpm.tipepaket_id
                    AND DATE(lpm.tgl_tindakan) BETWEEN :date_from AND :date_to
                WHERE (tp.tipepaket_nama ILIKE '%MCU%' OR tp.tipepaket_nama ILIKE '%PAKET%')
                GROUP BY tp.tipepaket_id
            ) t
            JOIN tipepaket_m tp ON t.tipepaket_id = tp.tipepaket_id
        ";
        $summaryData = Yii::$app->db->createCommand($summarySql, [
            ':date_from' => $q['dateFrom'],
            ':date_to' => $q['dateTo'],
        ])->queryOne();

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'search' => $q['search'],
            'dateFrom' => $q['dateFrom'],
            'dateTo' => $q['dateTo'],
            'sortOrder' => $q['sortOrder'],
            'totalPaket' => $summaryData['total_paket'] ?? 0,
            'totalPasien' => $summaryData['total_pasien'] ?? 0,
        ]);
    }

    public function actionExport()
    {
        $request = Yii::$app->request;
        $q = $this->buildQueryAndParams($request);

        $data = Yii::$app->db->createCommand($q['sql'], $q['params'])->queryAll();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $sheet->setCellValue('A1', 'Rumah Sakit Priscilla Medical Center');
        $sheet->setCellValue('A2', 'Laporan Rekapitulasi Jumlah Pasien per Paket MCU');
        $sheet->setCellValue('A3', 'Periode: ' . date('d/m/Y', strtotime($q['dateFrom'])) . ' s/d ' . date('d/m/Y', strtotime($q['dateTo'])));
        $sheet->getStyle('A1:A3')->getFont()->setBold(true);
        $sheet->getStyle('A1')->getFont()->setSize(16);
        $sheet->getStyle('A2')->getFont()->setSize(14);

        $headers = ['No', 'ID Paket', 'Nama Paket MCU', 'Tarif per Paket (Rp)', 'Jumlah Pasien'];
        $col = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($col . '5', $h);
            $col++;
        }
        $sheet->getStyle('A5:E5')->getFont()->setBold(true);
        $sheet->getStyle('A5:E5')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF002D72');
        $sheet->getStyle('A5:E5')->getFont()->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle('A5:E5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $rowIdx = 6;
        $i = 1;
        $totalPasien = 0;

        foreach ($data as $r) {
            $sheet->setCellValue('A' . $rowIdx, $i);
            $sheet->setCellValue('B' . $rowIdx, $r['tipepaket_id']);
            $sheet->setCellValue('C' . $rowIdx, $r['nama_paket_mcu']);
            $sheet->setCellValue('D' . $rowIdx, (float)$r['tarif_per_paket']);
            $sheet->getStyle('D' . $rowIdx)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->setCellValue('E' . $rowIdx, (int)$r['jumlah_pasien']);

            $totalPasien += (int)$r['jumlah_pasien'];
            $rowIdx++;
            $i++;
        }

        // Total Summary Row
        $sheet->setCellValue('A' . $rowIdx, 'TOTAL');
        $sheet->mergeCells('A' . $rowIdx . ':D' . $rowIdx);
        $sheet->setCellValue('E' . $rowIdx, $totalPasien);
        $sheet->getStyle('A' . $rowIdx . ':E' . $rowIdx)->getFont()->setBold(true);
        $sheet->getStyle('A' . $rowIdx . ':E' . $rowIdx)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE9ECEF');

        $lastRow = max(5, $rowIdx);
        $sheet->getStyle('A5:E' . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        
        foreach (range('A', 'E') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $fileName = 'Rekapitulasi_Pasien_MCU_' . date('Ymd_His') . '.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), 'export_mcu');
        $writer = new Xlsx($spreadsheet);
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
