<?php

namespace app\modules\Pendaftaran\controllers;

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

class ProgramPaketController extends BaseController
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

        $dateFromRaw = $request->get('date_from');
        $dateToRaw = $request->get('date_to');
        $penjaminKategori = $request->get('penjamin_kategori', ''); // 'umum', 'asuransi', 'bpjs', or ''
        $penjaminId = $request->get('penjamin_id', '');
        $tindakanIds = $request->get('tindakan_ids', []); // Array ID tindakan
        $search = trim($request->get('search', ''));
        $sortOrder = $request->get('sort_order', 'tgl_desc');

        if (empty($dateFromRaw)) {
            $dateFromRaw = date('d-m-Y', strtotime(date('Y-m-01')));
        }
        if (empty($dateToRaw)) {
            $dateToRaw = date('d-m-Y', strtotime(date('Y-m-t')));
        }

        $formattedDateFrom = $this->parseDateToSql($dateFromRaw, false);
        $formattedDateTo = $this->parseDateToSql($dateToRaw, true);

        // Jika belum submit 'cari', kembalikan query kosong (WHERE 1=0)
        if (!$statusCari) {
            return [
                'sql' => "SELECT * FROM tindakanpelayanan_t WHERE 1=0",
                'countSql' => "SELECT 0",
                'summarySql' => "SELECT 0 AS total_pasien, 0 AS total_tindakan, 0 AS total_nominal",
                'params' => [],
                'statusCari' => false,
                'dateFromRaw' => $dateFromRaw,
                'dateToRaw' => $dateToRaw,
                'penjaminKategori' => $penjaminKategori,
                'penjaminId' => $penjaminId,
                'tindakanIds' => is_array($tindakanIds) ? $tindakanIds : [],
                'search' => $search,
                'sortOrder' => $sortOrder,
            ];
        }

        switch ($sortOrder) {
            case 'tgl_asc':
                $orderBy = "tp.tgl_tindakan ASC, pm.nama_pasien ASC";
                break;
            case 'nama_asc':
                $orderBy = "pm.nama_pasien ASC, tp.tgl_tindakan DESC";
                break;
            case 'nama_desc':
                $orderBy = "pm.nama_pasien DESC, tp.tgl_tindakan DESC";
                break;
            case 'tindakan_asc':
                $orderBy = "dt.daftartindakan_nama ASC, pm.nama_pasien ASC";
                break;
            case 'tarif_desc':
                $orderBy = "tp.total_tarifakhir DESC";
                break;
            case 'tgl_desc':
            default:
                $orderBy = "tp.tgl_tindakan DESC, pm.nama_pasien ASC";
                break;
        }

        $whereConditions = [
            "pt.pasienbatalperiksa_id IS NULL",
            "tp.tgl_tindakan >= :dateFrom",
            "tp.tgl_tindakan <= :dateTo"
        ];

        $params = [
            ':dateFrom' => $formattedDateFrom,
            ':dateTo' => $formattedDateTo,
        ];

        // Filter Penjamin Kategori (Umum / Asuransi / BPJS)
        if ($penjaminKategori === 'umum') {
            $whereConditions[] = "(cb.carabayar_nama ILIKE '%UMUM%' OR pj.penjamin_nama ILIKE '%UMUM%')";
        } elseif ($penjaminKategori === 'asuransi') {
            $whereConditions[] = "(
                (cb.carabayar_nama ILIKE '%ASURANSI%' OR pj.penjamin_nama ILIKE '%ASURANSI%' OR cb.carabayar_nama ILIKE '%PERUSAHAAN%')
                AND cb.carabayar_nama NOT ILIKE '%BPJS%'
                AND pj.penjamin_nama NOT ILIKE '%BPJS%'
            )";
        } elseif ($penjaminKategori === 'bpjs') {
            $whereConditions[] = "(cb.carabayar_nama ILIKE '%BPJS%' OR pj.penjamin_nama ILIKE '%BPJS%')";
        }

        // Filter Spesifik Penjamin
        if (!empty($penjaminId)) {
            $whereConditions[] = "COALESCE(pa.penjamin_id, pt.penjamin_id) = :penjaminId";
            $params[':penjaminId'] = $penjaminId;
        }

        // Filter Multi-Pilih Tindakan
        if (!empty($tindakanIds)) {
            if (is_string($tindakanIds)) {
                $tindakanIds = array_filter(explode(',', $tindakanIds));
            }
            if (is_array($tindakanIds) && count($tindakanIds) > 0) {
                $inParams = [];
                foreach (array_values($tindakanIds) as $idx => $idVal) {
                    $pName = ':tindakanId_' . $idx;
                    $inParams[] = $pName;
                    $params[$pName] = (int)$idVal;
                }
                $whereConditions[] = "dt.daftartindakan_id IN (" . implode(', ', $inParams) . ")";
            }
        }

        // Filter Search Keyword
        if (!empty($search)) {
            $whereConditions[] = "(
                pm.nama_pasien ILIKE :search 
                OR pm.no_rekam_medik ILIKE :search 
                OR pt.no_pendaftaran ILIKE :search
                OR dt.daftartindakan_nama ILIKE :search
            )";
            $params[':search'] = '%' . $search . '%';
        }

        $whereSql = implode(' AND ', $whereConditions);

        $sql = "
            SELECT 
                tp.tindakanpelayanan_id,
                tp.tgl_tindakan,
                pt.tgl_pendaftaran,
                pt.no_pendaftaran,
                pm.no_rekam_medik,
                pm.nama_pasien,
                pm.jeniskelamin,
                cb.carabayar_nama AS cara_bayar,
                COALESCE(pj.penjamin_nama, 'UMUM') AS penjamin_nama,
                dt.daftartindakan_id,
                dt.daftartindakan_kode,
                dt.daftartindakan_nama,
                tp.qty_tindakan,
                tp.tarif_satuan,
                tp.total_tarifakhir AS total_tarif,
                rm.ruangan_nama,
                pg.nama_pegawai AS nama_dokter
            FROM tindakanpelayanan_t tp
            JOIN pendaftaran_t pt ON pt.pendaftaran_id = tp.pendaftaran_id
            JOIN pasien_m pm ON pm.pasien_id = tp.pasien_id
            JOIN daftartindakan_m dt ON dt.daftartindakan_id = tp.daftartindakan_id
            LEFT JOIN pasienadmisi_t pa ON pa.pendaftaran_id = pt.pendaftaran_id
            LEFT JOIN carabayar_m cb ON cb.carabayar_id = COALESCE(pa.carabayar_id, pt.carabayar_id)
            LEFT JOIN penjaminpasien_m pj ON pj.penjamin_id = COALESCE(pa.penjamin_id, pt.penjamin_id)
            LEFT JOIN ruangan_m rm ON rm.ruangan_id = tp.ruangan_id
            LEFT JOIN pegawai_m pg ON pg.pegawai_id = tp.dokterpemeriksa1_id
            WHERE {$whereSql}
            ORDER BY {$orderBy}
        ";

        $countSql = "
            SELECT COUNT(1)
            FROM tindakanpelayanan_t tp
            JOIN pendaftaran_t pt ON pt.pendaftaran_id = tp.pendaftaran_id
            JOIN pasien_m pm ON pm.pasien_id = tp.pasien_id
            JOIN daftartindakan_m dt ON dt.daftartindakan_id = tp.daftartindakan_id
            LEFT JOIN pasienadmisi_t pa ON pa.pendaftaran_id = pt.pendaftaran_id
            LEFT JOIN carabayar_m cb ON cb.carabayar_id = COALESCE(pa.carabayar_id, pt.carabayar_id)
            LEFT JOIN penjaminpasien_m pj ON pj.penjamin_id = COALESCE(pa.penjamin_id, pt.penjamin_id)
            WHERE {$whereSql}
        ";

        $summarySql = "
            SELECT 
                COUNT(DISTINCT pt.pasien_id) AS total_pasien,
                COUNT(tp.tindakanpelayanan_id) AS total_tindakan,
                COALESCE(SUM(tp.total_tarifakhir), 0) AS total_nominal
            FROM tindakanpelayanan_t tp
            JOIN pendaftaran_t pt ON pt.pendaftaran_id = tp.pendaftaran_id
            JOIN pasien_m pm ON pm.pasien_id = tp.pasien_id
            JOIN daftartindakan_m dt ON dt.daftartindakan_id = tp.daftartindakan_id
            LEFT JOIN pasienadmisi_t pa ON pa.pendaftaran_id = pt.pendaftaran_id
            LEFT JOIN carabayar_m cb ON cb.carabayar_id = COALESCE(pa.carabayar_id, pt.carabayar_id)
            LEFT JOIN penjaminpasien_m pj ON pj.penjamin_id = COALESCE(pa.penjamin_id, pt.penjamin_id)
            WHERE {$whereSql}
        ";

        return [
            'sql' => $sql,
            'countSql' => $countSql,
            'summarySql' => $summarySql,
            'params' => $params,
            'statusCari' => true,
            'dateFromRaw' => $dateFromRaw,
            'dateToRaw' => $dateToRaw,
            'penjaminKategori' => $penjaminKategori,
            'penjaminId' => $penjaminId,
            'tindakanIds' => is_array($tindakanIds) ? $tindakanIds : [],
            'search' => $search,
            'sortOrder' => $sortOrder,
        ];
    }

    public function actionIndex()
    {
        $request = Yii::$app->request;
        $q = $this->buildQueryAndParams($request);

        $statusCari = $q['statusCari'];

        if ($statusCari) {
            try {
                $totalCount = (int)Yii::$app->db->createCommand($q['countSql'], $q['params'])->queryScalar();
                $summaryData = Yii::$app->db->createCommand($q['summarySql'], $q['params'])->queryOne();
            } catch (\Exception $e) {
                $totalCount = 0;
                $summaryData = ['total_pasien' => 0, 'total_tindakan' => 0, 'total_nominal' => 0];
            }
        } else {
            $totalCount = 0;
            $summaryData = ['total_pasien' => 0, 'total_tindakan' => 0, 'total_nominal' => 0];
        }

        $dataProvider = new SqlDataProvider([
            'sql' => $q['sql'],
            'params' => $q['params'],
            'totalCount' => $totalCount,
            'pagination' => [
                'pageSize' => 15,
            ],
        ]);

        $penjaminList = ArrayHelper::map(
            Yii::$app->db->createCommand("SELECT penjamin_id, penjamin_nama FROM penjaminpasien_m WHERE penjamin_aktif = true ORDER BY penjamin_nama ASC")->queryAll(),
            'penjamin_id',
            'penjamin_nama'
        );

        $tindakanList = ArrayHelper::map(
            Yii::$app->db->createCommand("SELECT daftartindakan_id, daftartindakan_nama FROM daftartindakan_m WHERE daftartindakan_aktif = true ORDER BY daftartindakan_nama ASC")->queryAll(),
            'daftartindakan_id',
            'daftartindakan_nama'
        );

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'statusCari' => $statusCari,
            'dateFrom' => $q['dateFromRaw'],
            'dateTo' => $q['dateToRaw'],
            'penjaminKategori' => $q['penjaminKategori'],
            'penjaminId' => $q['penjaminId'],
            'tindakanIds' => $q['tindakanIds'],
            'search' => $q['search'],
            'sortOrder' => $q['sortOrder'],
            'penjaminList' => $penjaminList,
            'tindakanList' => $tindakanList,
            'totalPasien' => $summaryData['total_pasien'] ?? 0,
            'totalTindakan' => $summaryData['total_tindakan'] ?? 0,
            'totalNominal' => $summaryData['total_nominal'] ?? 0,
        ]);
    }

    public function actionExport()
    {
        $request = Yii::$app->request;
        $q = $this->buildQueryAndParams($request);

        // Jika export dipanggil tanpa pencarian aktif, jadikan statusCari true untuk export sesuai filter yang dipilih
        if (!$q['statusCari']) {
            $request->setQueryParams(array_merge($request->getQueryParams(), ['cari' => '1']));
            $q = $this->buildQueryAndParams($request);
        }

        $data = Yii::$app->db->createCommand($q['sql'], $q['params'])->queryAll();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'Rumah Sakit Priscilla Medical Center');
        $sheet->setCellValue('A2', 'Laporan Program Paket & Tindakan Pasien');
        $sheet->setCellValue('A3', 'Periode: ' . $q['dateFromRaw'] . ' s/d ' . $q['dateToRaw']);

        $sheet->getStyle('A1:A3')->getFont()->setBold(true);
        $sheet->getStyle('A1')->getFont()->setSize(16);
        $sheet->getStyle('A2')->getFont()->setSize(14);

        $headers = [
            'No',
            'Tgl Tindakan',
            'No Pendaftaran',
            'No RM',
            'Nama Pasien',
            'JK',
            'Cara Bayar',
            'Penjamin',
            'Kode Tindakan',
            'Nama Tindakan',
            'Qty',
            'Tarif Satuan (Rp)',
            'Total Tarif (Rp)',
            'Ruangan/Poli',
            'Dokter Pemeriksa'
        ];

        $col = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($col . '5', $h);
            $col++;
        }

        $sheet->getStyle('A5:O5')->getFont()->setBold(true);
        $sheet->getStyle('A5:O5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF002D72');
        $sheet->getStyle('A5:O5')->getFont()->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle('A5:O5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $rowIdx = 6;
        $i = 1;
        $grandTotalTarif = 0;

        foreach ($data as $r) {
            $sheet->setCellValue('A' . $rowIdx, $i);
            $sheet->setCellValue('B' . $rowIdx, !empty($r['tgl_tindakan']) ? date('d/m/Y H:i', strtotime($r['tgl_tindakan'])) : '-');
            $sheet->setCellValueExplicit('C' . $rowIdx, $r['no_pendaftaran'] ?? '-', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('D' . $rowIdx, $r['no_rekam_medik'] ?? '-', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('E' . $rowIdx, $r['nama_pasien'] ?? '-');
            $sheet->setCellValue('F' . $rowIdx, $r['jeniskelamin'] ?? '-');
            $sheet->setCellValue('G' . $rowIdx, $r['cara_bayar'] ?? '-');
            $sheet->setCellValue('H' . $rowIdx, $r['penjamin_nama'] ?? '-');
            $sheet->setCellValueExplicit('I' . $rowIdx, $r['daftartindakan_kode'] ?? '-', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('J' . $rowIdx, $r['daftartindakan_nama'] ?? '-');
            $sheet->setCellValue('K' . $rowIdx, (float)($r['qty_tindakan'] ?? 1));
            $sheet->setCellValue('L' . $rowIdx, (float)($r['tarif_satuan'] ?? 0));
            $sheet->setCellValue('M' . $rowIdx, (float)($r['total_tarif'] ?? 0));
            $sheet->setCellValue('N' . $rowIdx, $r['ruangan_nama'] ?? '-');
            $sheet->setCellValue('O' . $rowIdx, $r['nama_dokter'] ?? '-');

            $sheet->getStyle('L' . $rowIdx . ':M' . $rowIdx)->getNumberFormat()->setFormatCode('#,##0');

            $grandTotalTarif += (float)($r['total_tarif'] ?? 0);
            $rowIdx++;
            $i++;
        }

        // Summary Row
        $sheet->setCellValue('A' . $rowIdx, 'TOTAL NOMINAL TARIF');
        $sheet->mergeCells('A' . $rowIdx . ':L' . $rowIdx);
        $sheet->setCellValue('M' . $rowIdx, $grandTotalTarif);
        $sheet->getStyle('M' . $rowIdx)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('A' . $rowIdx . ':O' . $rowIdx)->getFont()->setBold(true);
        $sheet->getStyle('A' . $rowIdx . ':O' . $rowIdx)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE9ECEF');

        $lastRow = max(5, $rowIdx);
        $sheet->getStyle('A5:O' . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        foreach (range('A', 'O') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $fileName = 'Laporan_Program_Paket_' . date('Ymd_His') . '.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), 'export_program_paket');
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
