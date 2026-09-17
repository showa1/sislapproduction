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

class InformasiPaketPasienController extends BaseController
{
    private function buildQueryAndParams($request)
    {
        $search = trim($request->get('search', ''));
        $dateFrom = $request->get('date_from', date('Y-m-01'));
        $dateTo = $request->get('date_to', date('Y-m-t'));
        $tipepaketId = $request->get('tipepaket_id', '');
        $penjaminId = $request->get('penjamin_id', '');
        $sortOrder = $request->get('sort_order', 'tgl_desc');

        $formattedDateFrom = date('Y-m-d', strtotime($dateFrom));
        $formattedDateTo = date('Y-m-d', strtotime($dateTo));

        switch ($sortOrder) {
            case 'tgl_asc':
                $orderBy = "DATE(lpm.tgl_tindakan) ASC, p.nama_pasien ASC";
                break;
            case 'nama_asc':
                $orderBy = "p.nama_pasien ASC, DATE(lpm.tgl_tindakan) DESC";
                break;
            case 'nama_desc':
                $orderBy = "p.nama_pasien DESC, DATE(lpm.tgl_tindakan) DESC";
                break;
            case 'rm_asc':
                $orderBy = "p.no_rekam_medik ASC";
                break;
            case 'paket_asc':
                $orderBy = "tp.tipepaket_nama ASC, p.nama_pasien ASC";
                break;
            case 'tgl_desc':
            default:
                $orderBy = "DATE(lpm.tgl_tindakan) DESC, p.nama_pasien ASC";
                break;
        }

        $sql = "
            SELECT DISTINCT
                tp.tipepaket_id AS id_paket,
                tp.tipepaket_nama AS nama_paket_mcu,
                pt.no_pendaftaran,
                DATE(lpm.tgl_tindakan) AS tgl_tindakan,
                p.no_rekam_medik AS no_rm,
                p.nama_pasien,
                COALESCE(pj.penjamin_nama, 'Umum') AS penjamin,
                tp.tarifpaket AS tarif_per_paket
            FROM tipepaket_m tp
            JOIN laporanpelayananmcu_v lpm ON tp.tipepaket_id = lpm.tipepaket_id
            JOIN pendaftaran_t pt ON lpm.pendaftaran_id = pt.pendaftaran_id
            JOIN pasien_m p ON pt.pasien_id = p.pasien_id
            LEFT JOIN penjaminpasien_m pj ON pt.penjamin_id = pj.penjamin_id
            WHERE DATE(lpm.tgl_tindakan) BETWEEN :date_from AND :date_to
              AND (tp.tipepaket_nama ILIKE '%MCU%' OR tp.tipepaket_nama ILIKE '%PAKET%')
        ";

        $countSql = "
            SELECT COUNT(DISTINCT lpm.pendaftaran_id)
            FROM tipepaket_m tp
            JOIN laporanpelayananmcu_v lpm ON tp.tipepaket_id = lpm.tipepaket_id
            JOIN pendaftaran_t pt ON lpm.pendaftaran_id = pt.pendaftaran_id
            JOIN pasien_m p ON pt.pasien_id = p.pasien_id
            LEFT JOIN penjaminpasien_m pj ON pt.penjamin_id = pj.penjamin_id
            WHERE DATE(lpm.tgl_tindakan) BETWEEN :date_from AND :date_to
              AND (tp.tipepaket_nama ILIKE '%MCU%' OR tp.tipepaket_nama ILIKE '%PAKET%')
        ";

        $params = [
            ':date_from' => $formattedDateFrom,
            ':date_to' => $formattedDateTo,
        ];
        $countParams = [
            ':date_from' => $formattedDateFrom,
            ':date_to' => $formattedDateTo,
        ];

        if (!empty($tipepaketId)) {
            $sql .= " AND tp.tipepaket_id = :tipepaket_id";
            $countSql .= " AND tp.tipepaket_id = :tipepaket_id";
            $params[':tipepaket_id'] = $tipepaketId;
            $countParams[':tipepaket_id'] = $tipepaketId;
        }

        if (!empty($penjaminId)) {
            $sql .= " AND pt.penjamin_id = :penjamin_id";
            $countSql .= " AND pt.penjamin_id = :penjamin_id";
            $params[':penjamin_id'] = $penjaminId;
            $countParams[':penjamin_id'] = $penjaminId;
        }

        if (!empty($search)) {
            $sql .= " AND (p.nama_pasien ILIKE :search OR p.no_rekam_medik ILIKE :search OR pt.no_pendaftaran ILIKE :search)";
            $countSql .= " AND (p.nama_pasien ILIKE :search OR p.no_rekam_medik ILIKE :search OR pt.no_pendaftaran ILIKE :search)";
            $params[':search'] = '%' . $search . '%';
            $countParams[':search'] = '%' . $search . '%';
        }

        $sql .= " ORDER BY {$orderBy}";

        return [
            'sql' => $sql,
            'countSql' => $countSql,
            'params' => $params,
            'countParams' => $countParams,
            'dateFrom' => $formattedDateFrom,
            'dateTo' => $formattedDateTo,
            'tipepaketId' => $tipepaketId,
            'penjaminId' => $penjaminId,
            'search' => $search,
            'sortOrder' => $sortOrder,
        ];
    }

    public function actionIndex()
    {
        $request = Yii::$app->request;
        $q = $this->buildQueryAndParams($request);

        try {
            $totalCount = (int)Yii::$app->db->createCommand($q['countSql'], $q['countParams'])->queryScalar();
        } catch (\Exception $e) {
            $totalCount = 0;
        }

        $dataProvider = new SqlDataProvider([
            'sql' => $q['sql'],
            'params' => $q['params'],
            'totalCount' => $totalCount,
            'pagination' => [
                'pageSize' => 5,
            ],
        ]);

        // Dropdown List untuk Paket MCU
        $listPaketSql = "
            SELECT tipepaket_id, tipepaket_nama
            FROM tipepaket_m
            WHERE (tipepaket_nama ILIKE '%MCU%' OR tipepaket_nama ILIKE '%PAKET%')
            ORDER BY tipepaket_nama ASC
        ";
        $listPaketData = Yii::$app->db->createCommand($listPaketSql)->queryAll();
        $paketList = [];
        foreach ($listPaketData as $p) {
            $paketList[$p['tipepaket_id']] = $p['tipepaket_nama'];
        }

        // Dropdown List untuk Penjamin
        $listPenjaminSql = "
            SELECT penjamin_id, penjamin_nama
            FROM penjaminpasien_m
            WHERE penjamin_aktif = true
            ORDER BY penjamin_nama ASC
        ";
        $listPenjaminData = Yii::$app->db->createCommand($listPenjaminSql)->queryAll();
        $penjaminList = [];
        foreach ($listPenjaminData as $pj) {
            $penjaminList[$pj['penjamin_id']] = $pj['penjamin_nama'];
        }

        // Summary KPI
        $summarySql = "
            SELECT 
                COUNT(DISTINCT lpm.pendaftaran_id) AS total_pasien,
                COUNT(DISTINCT tp.tipepaket_id) AS total_paket_terpakai
            FROM tipepaket_m tp
            JOIN laporanpelayananmcu_v lpm ON tp.tipepaket_id = lpm.tipepaket_id
            JOIN pendaftaran_t pt ON lpm.pendaftaran_id = pt.pendaftaran_id
            WHERE DATE(lpm.tgl_tindakan) BETWEEN :date_from AND :date_to
              AND (tp.tipepaket_nama ILIKE '%MCU%' OR tp.tipepaket_nama ILIKE '%PAKET%')
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
            'tipepaketId' => $q['tipepaketId'],
            'penjaminId' => $q['penjaminId'],
            'sortOrder' => $q['sortOrder'],
            'paketList' => $paketList,
            'penjaminList' => $penjaminList,
            'totalPasien' => $summaryData['total_pasien'] ?? 0,
            'totalPaketTerpakai' => $summaryData['total_paket_terpakai'] ?? 0,
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
        $sheet->setCellValue('A2', 'Laporan Informasi Detail Pasien per Paket MCU');
        $sheet->setCellValue('A3', 'Periode: ' . date('d/m/Y', strtotime($q['dateFrom'])) . ' s/d ' . date('d/m/Y', strtotime($q['dateTo'])));
        $sheet->getStyle('A1:A3')->getFont()->setBold(true);
        $sheet->getStyle('A1')->getFont()->setSize(16);
        $sheet->getStyle('A2')->getFont()->setSize(14);

        $headers = ['No', 'Tgl Tindakan', 'No Pendaftaran', 'No RM', 'Nama Pasien', 'Penjamin', 'ID Paket', 'Nama Paket MCU', 'Tarif Paket (Rp)'];
        $col = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($col . '5', $h);
            $col++;
        }
        $sheet->getStyle('A5:I5')->getFont()->setBold(true);
        $sheet->getStyle('A5:I5')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF002D72');
        $sheet->getStyle('A5:I5')->getFont()->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle('A5:I5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $rowIdx = 6;
        $i = 1;

        foreach ($data as $r) {
            $sheet->setCellValue('A' . $rowIdx, $i);
            $sheet->setCellValue('B' . $rowIdx, !empty($r['tgl_tindakan']) ? date('d/m/Y', strtotime($r['tgl_tindakan'])) : '-');
            $sheet->setCellValueExplicit('C' . $rowIdx, $r['no_pendaftaran'] ?? '-', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('D' . $rowIdx, $r['no_rm'] ?? '-', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('E' . $rowIdx, $r['nama_pasien'] ?? '-');
            $sheet->setCellValue('F' . $rowIdx, $r['penjamin'] ?? '-');
            $sheet->setCellValue('G' . $rowIdx, $r['id_paket'] ?? '-');
            $sheet->setCellValue('H' . $rowIdx, $r['nama_paket_mcu'] ?? '-');
            $sheet->setCellValue('I' . $rowIdx, (float)($r['tarif_per_paket'] ?? 0));
            $sheet->getStyle('I' . $rowIdx)->getNumberFormat()->setFormatCode('#,##0');

            $rowIdx++;
            $i++;
        }

        // Summary Row
        $sheet->setCellValue('A' . $rowIdx, 'TOTAL PASIEN');
        $sheet->mergeCells('A' . $rowIdx . ':H' . $rowIdx);
        $sheet->setCellValue('I' . $rowIdx, ($i - 1) . ' Pasien');
        $sheet->getStyle('A' . $rowIdx . ':I' . $rowIdx)->getFont()->setBold(true);
        $sheet->getStyle('A' . $rowIdx . ':I' . $rowIdx)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE9ECEF');

        $lastRow = max(5, $rowIdx);
        $sheet->getStyle('A5:I' . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        
        foreach (range('A', 'I') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $fileName = 'Informasi_Paket_Pasien_MCU_' . date('Ymd_His') . '.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), 'export_info_mcu');
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
