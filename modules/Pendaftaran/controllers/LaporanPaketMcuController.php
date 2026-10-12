<?php

namespace app\modules\Pendaftaran\controllers;

use Yii;
use app\controllers\BaseController;
use yii\web\Response;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class LaporanPaketMcuController extends BaseController
{
    /** Default paket IDs MCU ketika user belum memilih apapun */
    private $defaultPaketIdsMcu = [58, 65, 75];

    /** Default tindakan IDs ketika user belum memilih apapun */
    private $defaultTindakanIds = [515, 773, 650, 441, 463];

    /**
     * Parse dan validasi parameter dari request.
     * Mengembalikan array ['tahun', 'selectedIds', 'statusCari'].
     */
    private function parseParams($request, $activeTab = 'mcu')
    {
        $tahun = (int)$request->get('tahun', date('Y'));

        if ($activeTab === 'program') {
            $rawIds = $request->get('tindakan_ids', []);
            if (empty($rawIds)) {
                $rawIds = $request->get('paket_ids', []);
            }
        } else {
            $rawIds = $request->get('paket_ids', []);
        }

        if (!is_array($rawIds)) {
            $rawIds = array_filter(explode(',', (string)$rawIds));
        }

        // Sanitize: hanya izinkan integer positif
        $selectedIds = array_values(array_filter(array_map('intval', $rawIds), function($v) {
            return $v > 0;
        }));

        // Fallback ke default sesuai tab jika tidak ada yang dipilih
        if (empty($selectedIds)) {
            $selectedIds = ($activeTab === 'program') ? $this->defaultTindakanIds : $this->defaultPaketIdsMcu;
        }

        // statusCari: hanya true jika user sudah klik Tampilkan Data
        $statusCari = ($request->get('cari') !== null);

        return [
            'tahun'       => $tahun,
            'selectedIds' => $selectedIds,
            'statusCari'  => $statusCari,
        ];
    }

    /**
     * Build SQL query pivot tahunan untuk Paket MCU (dari permintaanmcu_t).
     */
    private function buildSqlMcu($tahun, array $paketIds)
    {
        $tahun  = (int)$tahun;
        $inList = implode(', ', $paketIds);

        return "
            SELECT
                tp.tipepaket_id AS id,
                tp.tipepaket_nama AS nama,
                SUM(CASE WHEN sub.bln = 1  THEN 1 ELSE 0 END) AS bln_01,
                SUM(CASE WHEN sub.bln = 2  THEN 1 ELSE 0 END) AS bln_02,
                SUM(CASE WHEN sub.bln = 3  THEN 1 ELSE 0 END) AS bln_03,
                SUM(CASE WHEN sub.bln = 4  THEN 1 ELSE 0 END) AS bln_04,
                SUM(CASE WHEN sub.bln = 5  THEN 1 ELSE 0 END) AS bln_05,
                SUM(CASE WHEN sub.bln = 6  THEN 1 ELSE 0 END) AS bln_06,
                SUM(CASE WHEN sub.bln = 7  THEN 1 ELSE 0 END) AS bln_07,
                SUM(CASE WHEN sub.bln = 8  THEN 1 ELSE 0 END) AS bln_08,
                SUM(CASE WHEN sub.bln = 9  THEN 1 ELSE 0 END) AS bln_09,
                SUM(CASE WHEN sub.bln = 10 THEN 1 ELSE 0 END) AS bln_10,
                SUM(CASE WHEN sub.bln = 11 THEN 1 ELSE 0 END) AS bln_11,
                SUM(CASE WHEN sub.bln = 12 THEN 1 ELSE 0 END) AS bln_12,
                COUNT(sub.pendaftaran_id) AS total
            FROM tipepaket_m tp
            LEFT JOIN (
                SELECT DISTINCT
                    pm.tipepaket_id,
                    pt.pendaftaran_id,
                    EXTRACT(MONTH FROM pt.tgl_pendaftaran)::int AS bln
                FROM permintaanmcu_t pm
                JOIN pendaftaran_t pt ON pt.pendaftaran_id = pm.pendaftaran_id
                WHERE EXTRACT(YEAR FROM pt.tgl_pendaftaran) = {$tahun}
                  AND pm.tipepaket_id IN ({$inList})
                  AND pt.pasienbatalperiksa_id IS NULL
            ) sub ON sub.tipepaket_id = tp.tipepaket_id
            WHERE tp.tipepaket_id IN ({$inList})
            GROUP BY tp.tipepaket_id, tp.tipepaket_nama
            ORDER BY tp.tipepaket_nama
        ";
    }

    /**
     * Build SQL query pivot tahunan untuk Tindakan (dari daftartindakan_m & tindakanpelayanan_t).
     */
    private function buildSqlTindakan($tahun, array $tindakanIds)
    {
        $tahun  = (int)$tahun;
        $inList = implode(', ', $tindakanIds);

        return "
            SELECT
                dt.daftartindakan_id AS id,
                dt.daftartindakan_nama AS nama,
                SUM(CASE WHEN sub.bln = 1  THEN 1 ELSE 0 END) AS bln_01,
                SUM(CASE WHEN sub.bln = 2  THEN 1 ELSE 0 END) AS bln_02,
                SUM(CASE WHEN sub.bln = 3  THEN 1 ELSE 0 END) AS bln_03,
                SUM(CASE WHEN sub.bln = 4  THEN 1 ELSE 0 END) AS bln_04,
                SUM(CASE WHEN sub.bln = 5  THEN 1 ELSE 0 END) AS bln_05,
                SUM(CASE WHEN sub.bln = 6  THEN 1 ELSE 0 END) AS bln_06,
                SUM(CASE WHEN sub.bln = 7  THEN 1 ELSE 0 END) AS bln_07,
                SUM(CASE WHEN sub.bln = 8  THEN 1 ELSE 0 END) AS bln_08,
                SUM(CASE WHEN sub.bln = 9  THEN 1 ELSE 0 END) AS bln_09,
                SUM(CASE WHEN sub.bln = 10 THEN 1 ELSE 0 END) AS bln_10,
                SUM(CASE WHEN sub.bln = 11 THEN 1 ELSE 0 END) AS bln_11,
                SUM(CASE WHEN sub.bln = 12 THEN 1 ELSE 0 END) AS bln_12,
                COUNT(sub.pendaftaran_id) AS total
            FROM daftartindakan_m dt
            LEFT JOIN (
                SELECT DISTINCT
                    tp.daftartindakan_id,
                    pt.pendaftaran_id,
                    EXTRACT(MONTH FROM tp.tgl_tindakan)::int AS bln
                FROM tindakanpelayanan_t tp
                JOIN pendaftaran_t pt ON pt.pendaftaran_id = tp.pendaftaran_id
                WHERE EXTRACT(YEAR FROM tp.tgl_tindakan) = {$tahun}
                  AND tp.daftartindakan_id IN ({$inList})
                  AND pt.pasienbatalperiksa_id IS NULL
            ) sub ON sub.daftartindakan_id = dt.daftartindakan_id
            WHERE dt.daftartindakan_id IN ({$inList})
            GROUP BY dt.daftartindakan_id, dt.daftartindakan_nama
            ORDER BY dt.daftartindakan_nama
        ";
    }

    /**
     * Ambil daftar semua tipe paket untuk dropdown UI (Tab MCU).
     */
    private function getPaketList()
    {
        try {
            return Yii::$app->db->createCommand("
                SELECT tipepaket_id AS id, tipepaket_nama AS nama
                FROM tipepaket_m
                ORDER BY tipepaket_nama ASC
            ")->queryAll();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Ambil daftar tindakan untuk dropdown UI (Tab Program Paket / Tindakan).
     */
    private function getTindakanList()
    {
        try {
            return Yii::$app->db->createCommand("
                SELECT daftartindakan_id AS id, daftartindakan_nama AS nama
                FROM daftartindakan_m
                WHERE daftartindakan_aktif = true
                ORDER BY daftartindakan_nama ASC
            ")->queryAll();
        } catch (\Exception $e) {
            return [];
        }
    }

    /** Hitung grand total dari data result */
    private function calcGrandTotal(array $data)
    {
        $keys = ['bln_01','bln_02','bln_03','bln_04','bln_05','bln_06',
                 'bln_07','bln_08','bln_09','bln_10','bln_11','bln_12','total'];
        $gt = array_fill_keys($keys, 0);
        foreach ($data as $row) {
            foreach ($keys as $k) {
                $gt[$k] += (int)($row[$k] ?? 0);
            }
        }
        return $gt;
    }

    // -------------------------------------------------------------------------

    public function actionIndex()
    {
        $request   = Yii::$app->request;
        $activeTab = $request->get('tab', 'mcu');
        if (!in_array($activeTab, ['mcu', 'program'])) {
            $activeTab = 'mcu';
        }

        $p        = $this->parseParams($request, $activeTab);
        $itemList = ($activeTab === 'program') ? $this->getTindakanList() : $this->getPaketList();

        $data       = [];
        $grandTotal = $this->calcGrandTotal([]);

        if ($p['statusCari']) {
            try {
                $sql = ($activeTab === 'program')
                    ? $this->buildSqlTindakan($p['tahun'], $p['selectedIds'])
                    : $this->buildSqlMcu($p['tahun'], $p['selectedIds']);

                $data = Yii::$app->db->createCommand($sql)->queryAll();
                $grandTotal = $this->calcGrandTotal($data);
            } catch (\Exception $e) {
                Yii::$app->session->setFlash('error', 'Terjadi kesalahan: ' . $e->getMessage());
            }
        }

        return $this->render('index', [
            'activeTab'   => $activeTab,
            'data'        => $data,
            'statusCari'  => $p['statusCari'],
            'tahun'       => $p['tahun'],
            'selectedIds' => $p['selectedIds'],
            'itemList'    => $itemList,
            'grandTotal'  => $grandTotal,
        ]);
    }

    public function actionExport()
    {
        $request   = Yii::$app->request;
        $activeTab = $request->get('tab', 'mcu');
        if (!in_array($activeTab, ['mcu', 'program'])) {
            $activeTab = 'mcu';
        }

        $p = $this->parseParams($request, $activeTab);

        if ($activeTab === 'program') {
            $sql        = $this->buildSqlTindakan($p['tahun'], $p['selectedIds']);
            $tabLabel   = 'Laporan Program Paket & Tindakan Pasien';
            $headerName = 'Nama Tindakan';
            $filePrefix = 'Laporan_Tindakan_Pasien_';
            $itemList   = $this->getTindakanList();
        } else {
            $sql        = $this->buildSqlMcu($p['tahun'], $p['selectedIds']);
            $tabLabel   = 'Laporan Paket MCU';
            $headerName = 'Nama Paket';
            $filePrefix = 'Laporan_Paket_MCU_';
            $itemList   = $this->getPaketList();
        }

        $data = Yii::$app->db->createCommand($sql)->queryAll();

        $bulanLabels = [
            'bln_01' => 'Januari',   'bln_02' => 'Februari', 'bln_03' => 'Maret',
            'bln_04' => 'April',     'bln_05' => 'Mei',       'bln_06' => 'Juni',
            'bln_07' => 'Juli',      'bln_08' => 'Agustus',   'bln_09' => 'September',
            'bln_10' => 'Oktober',   'bln_11' => 'November',  'bln_12' => 'Desember',
        ];

        // Ambil nama item terpilih untuk keterangan header
        $selectedNames = [];
        foreach ($itemList as $it) {
            if (in_array((int)$it['id'], $p['selectedIds'])) {
                $selectedNames[] = $it['nama'];
            }
        }
        $selectedNamesStr = implode(', ', $selectedNames) ?: 'Semua';

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header info
        $sheet->setCellValue('A1', 'Rumah Sakit Priscilla Medical Center');
        $sheet->setCellValue('A2', $tabLabel);
        $sheet->setCellValue('A3', 'Tahun: ' . $p['tahun']);
        $sheet->setCellValue('A4', ($activeTab === 'program' ? 'Tindakan: ' : 'Paket: ') . $selectedNamesStr);
        $sheet->getStyle('A1:A4')->getFont()->setBold(true);
        $sheet->getStyle('A1')->getFont()->setSize(16);
        $sheet->getStyle('A2')->getFont()->setSize(14);

        // Header tabel (baris 6)
        $headers = array_merge(['No', $headerName], array_values($bulanLabels), ['Total']);
        $colIdx  = 'A';
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

        // Data rows
        $rowIdx = $headerRow + 1;
        $i      = 1;
        $gt = array_fill_keys(array_keys($bulanLabels), 0);
        $gt['total'] = 0;

        foreach ($data as $r) {
            $sheet->setCellValue('A' . $rowIdx, $i);
            $sheet->setCellValue('B' . $rowIdx, $r['nama']);

            $colIdx = 'C';
            foreach (array_keys($bulanLabels) as $bKey) {
                $val = (int)($r[$bKey] ?? 0);
                $sheet->setCellValue($colIdx . $rowIdx, $val);
                $sheet->getStyle($colIdx . $rowIdx)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $gt[$bKey] += $val;
                $colIdx++;
            }

            $total = (int)($r['total'] ?? 0);
            $sheet->setCellValue($colIdx . $rowIdx, $total);
            $sheet->getStyle($colIdx . $rowIdx)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($colIdx . $rowIdx)->getFont()->setBold(true);
            $gt['total'] += $total;

            $rowIdx++;
            $i++;
        }

        // Grand total row
        $sheet->setCellValue('A' . $rowIdx, 'TOTAL');
        $sheet->mergeCells('A' . $rowIdx . ':B' . $rowIdx);
        $colIdx = 'C';
        foreach (array_keys($bulanLabels) as $bKey) {
            $sheet->setCellValue($colIdx . $rowIdx, $gt[$bKey]);
            $sheet->getStyle($colIdx . $rowIdx)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $colIdx++;
        }
        $sheet->setCellValue($colIdx . $rowIdx, $gt['total']);
        $sheet->getStyle($colIdx . $rowIdx)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $totalRowRange = 'A' . $rowIdx . ':' . $lastHeaderCol . $rowIdx;
        $sheet->getStyle($totalRowRange)->getFont()->setBold(true);
        $sheet->getStyle($totalRowRange)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE9ECEF');

        // Border & auto size
        $dataRange = 'A' . $headerRow . ':' . $lastHeaderCol . $rowIdx;
        $sheet->getStyle($dataRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        foreach (range('A', $lastHeaderCol) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $fileName = $filePrefix . $p['tahun'] . '_' . date('Ymd_His') . '.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), 'export_pivot');
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
