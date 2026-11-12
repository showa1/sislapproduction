<?php

namespace app\modules\RawatInap\controllers;

use Yii;
use app\controllers\BaseController;
use yii\web\Response;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class BorController extends BaseController
{
    public function actionIndex()
    {
        $request   = Yii::$app->request;
        $tahun     = (int)$request->get('tahun', date('Y'));
        $caraBayar = $request->get('cara_bayar', 'Semua');
        $export    = $request->get('export', '0');

        // Ambil kapasitas Tempat Tidur (TT) SIOP dari config/params.php
        $jumlahTT = (int)(Yii::$app->params['kapasitas_tt_siop'] ?? 102);

        $joinCaraBayar  = "";
        $whereCaraBayar = "";
        $params         = [':tahun' => $tahun];

        if (!empty($caraBayar) && $caraBayar !== 'Semua') {
            $joinCaraBayar  = "JOIN carabayar_m cm ON cm.carabayar_id = pat.carabayar_id";
            $whereCaraBayar = "AND cm.carabayar_nama = :cb";
            $params[':cb']  = $caraBayar;
        }

        // Query sama seperti rumus LOS di Dashboard Rawat Inap:
        // Total Jam = SUM(EXTRACT(EPOCH FROM (pat.tglpulang - pat.tgladmisi)) / 3600)
        // Total Hari Perawatan = Total Jam / 24
        $sql = "
            SELECT 
                CAST(EXTRACT(MONTH FROM pat.tglpulang) AS INTEGER) AS bulan,
                COUNT(DISTINCT pat.pasienadmisi_id) AS jumlah_pasien,
                SUM(EXTRACT(EPOCH FROM (pat.tglpulang - pat.tgladmisi)) / 3600) AS total_jam
            FROM pasienadmisi_t pat
            JOIN pendaftaran_t pt ON pt.pendaftaran_id = pat.pendaftaran_id
            {$joinCaraBayar}
            WHERE pat.tglpulang IS NOT NULL 
              AND pt.pasienbatalperiksa_id IS NULL
              AND EXTRACT(YEAR FROM pat.tglpulang) = :tahun
              {$whereCaraBayar}
            GROUP BY EXTRACT(MONTH FROM pat.tglpulang)
            ORDER BY bulan ASC
        ";

        $rows = Yii::$app->db->createCommand($sql, $params)->queryAll();

        $rawByMonth = [];
        foreach ($rows as $r) {
            $b = (int)$r['bulan'];
            $rawByMonth[$b] = [
                'jumlah_pasien' => (int)$r['jumlah_pasien'],
                'total_jam'     => (float)$r['total_jam'],
            ];
        }

        $monthly = [];
        $totalPasienTahun        = 0;
        $totalJamTahun           = 0.0;
        $totalHariPerawatanTahun = 0.0;
        $totalHariKalenderTahun  = 0;
        $totalKapasitasHariTahun = 0;
        $bulanAktif              = 0;

        for ($m = 1; $m <= 12; $m++) {
            $jumlahHariDalamBulan = (int)date('t', mktime(0, 0, 0, $m, 1, $tahun));
            $jumlahPasien         = $rawByMonth[$m]['jumlah_pasien'] ?? 0;
            $totalJam             = $rawByMonth[$m]['total_jam'] ?? 0.0;
            $totalHariPerawatan   = $totalJam / 24;
            $kapasitasHariTT      = $jumlahTT * $jumlahHariDalamBulan;

            // Rumus BOR sesuai ketentuan:
            // $bor = ($totalHariPerawatan / ($jumlahTT * $jumlahHariDalamBulan)) * 100;
            $bor = $kapasitasHariTT > 0
                ? (($totalHariPerawatan / ($jumlahTT * $jumlahHariDalamBulan)) * 100)
                : 0.0;

            if ($jumlahPasien > 0) {
                $bulanAktif++;
            }

            $monthly[$m] = [
                'jumlah_pasien'        => $jumlahPasien,
                'total_jam'            => $totalJam,
                'hari_perawatan'       => $totalHariPerawatan,
                'jumlah_tt'            => $jumlahTT,
                'jumlah_hari_bulan'    => $jumlahHariDalamBulan,
                'kapasitas_hari_tt'    => $kapasitasHariTT,
                'bor'                  => $bor,
            ];

            $totalPasienTahun        += $jumlahPasien;
            $totalJamTahun           += $totalJam;
            $totalHariPerawatanTahun += $totalHariPerawatan;
            $totalHariKalenderTahun  += $jumlahHariDalamBulan;
            $totalKapasitasHariTahun += $kapasitasHariTT;
        }

        // BOR Tahunan (akumulasi setahun penuh) & Rata-rata BOR pada Bulan Aktif
        $borTahunan = $totalKapasitasHariTahun > 0
            ? (($totalHariPerawatanTahun / ($jumlahTT * $totalHariKalenderTahun)) * 100)
            : 0.0;

        // Hitung juga BOR kumulatif hanya pada bulan yang aktif (sudah berjalan)
        $hariKalenderAktif = 0;
        for ($m = 1; $m <= 12; $m++) {
            if ($monthly[$m]['jumlah_pasien'] > 0) {
                $hariKalenderAktif += $monthly[$m]['jumlah_hari_bulan'];
            }
        }
        $borBulanAktif = ($hariKalenderAktif > 0 && $jumlahTT > 0)
            ? (($totalHariPerawatanTahun / ($jumlahTT * $hariKalenderAktif)) * 100)
            : 0.0;

        $summary = [
            'jumlah_tt'               => $jumlahTT,
            'total_pasien'            => $totalPasienTahun,
            'total_jam'               => $totalJamTahun,
            'total_hari_perawatan'    => $totalHariPerawatanTahun,
            'total_hari_kalender'     => $totalHariKalenderTahun,
            'total_kapasitas_hari_tt' => $totalKapasitasHariTahun,
            'bulan_aktif'             => $bulanAktif,
            'bor_tahunan'             => $borTahunan,
            'bor_bulan_aktif'         => $borBulanAktif,
        ];

        if ($export === '1') {
            return $this->exportExcel($tahun, $caraBayar, $monthly, $summary);
        }

        $optCaraBayar = Yii::$app->db->createCommand("
            SELECT DISTINCT carabayar_nama 
            FROM carabayar_m 
            ORDER BY carabayar_nama
        ")->queryColumn();

        return $this->render('index', [
            'tahun'        => $tahun,
            'caraBayar'    => $caraBayar,
            'optCaraBayar' => $optCaraBayar,
            'jumlahTT'     => $jumlahTT,
            'monthly'      => $monthly,
            'summary'      => $summary,
        ]);
    }

    private function exportExcel($tahun, $caraBayar, array $monthly, array $summary)
    {
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan BOR ' . $tahun);

        $sheet->setCellValue('A1', 'Rumah Sakit Priscilla Medical Center');
        $sheet->setCellValue('A2', 'Laporan BOR (Bed Occupancy Rate) Rawat Inap - Tahun ' . $tahun);
        $sheet->setCellValue('A3', 'Kapasitas TT (SIOP): ' . $summary['jumlah_tt'] . ' TT | Cara Bayar: ' . $caraBayar);
        $sheet->getStyle('A1:A3')->getFont()->setBold(true);

        $bulanNames = [1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'];
        $headers    = array_merge(['No', 'Indikator / Komponen BOR'], array_values($bulanNames), ['Total / Rata-rata']);

        $col = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($col . '5', $h);
            $col++;
        }

        $sheet->getStyle('A5:O5')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '002D72']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        $rowsConfig = [
            ['no' => 1, 'label' => 'Jumlah Pasien Pulang (Pasien)', 'key' => 'jumlah_pasien', 'total' => number_format($summary['total_pasien'], 0, ',', '.'), 'decimals' => 0],
            ['no' => 2, 'label' => 'Total Jam Perawatan (Jam)', 'key' => 'total_jam', 'total' => number_format($summary['total_jam'], 1, ',', '.'), 'decimals' => 1],
            ['no' => 3, 'label' => 'Total Hari Perawatan / HP (Hari)', 'key' => 'hari_perawatan', 'total' => number_format($summary['total_hari_perawatan'], 1, ',', '.'), 'decimals' => 1],
            ['no' => 4, 'label' => 'Kapasitas Tempat Tidur SIOP (TT)', 'key' => 'jumlah_tt', 'total' => $summary['jumlah_tt'] . ' TT', 'decimals' => 0],
            ['no' => 5, 'label' => 'Jumlah Hari Kalender (Hari)', 'key' => 'jumlah_hari_bulan', 'total' => $summary['total_hari_kalender'], 'decimals' => 0],
            ['no' => 6, 'label' => 'Kapasitas Hari Tempat Tidur (TT x Hari)', 'key' => 'kapasitas_hari_tt', 'total' => number_format($summary['total_kapasitas_hari_tt'], 0, ',', '.'), 'decimals' => 0],
        ];

        $rowIdx = 6;
        foreach ($rowsConfig as $rc) {
            $sheet->setCellValue('A' . $rowIdx, $rc['no']);
            $sheet->setCellValue('B' . $rowIdx, $rc['label']);
            $colIdx = 'C';
            for ($m = 1; $m <= 12; $m++) {
                $val = $monthly[$m][$rc['key']] ?? 0;
                $formatted = $rc['decimals'] > 0 ? round($val, $rc['decimals']) : (int)$val;
                $sheet->setCellValue($colIdx . $rowIdx, $formatted);
                $colIdx++;
            }
            $sheet->setCellValue('O' . $rowIdx, $rc['total']);
            $rowIdx++;
        }

        // Baris Footer BOR (%)
        $sheet->setCellValue('A' . $rowIdx, '');
        $sheet->setCellValue('B' . $rowIdx, 'BOR (Bed Occupancy Rate) (%)');
        $colIdx = 'C';
        for ($m = 1; $m <= 12; $m++) {
            $sheet->setCellValue($colIdx . $rowIdx, number_format($monthly[$m]['bor'], 2, ',', '.') . '%');
            $colIdx++;
        }
        $sheet->setCellValue('O' . $rowIdx, number_format($summary['bor_bulan_aktif'], 2, ',', '.') . '%');

        $sheet->getStyle('A' . $rowIdx . ':O' . $rowIdx)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '002D72']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getStyle('B' . $rowIdx)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        $sheet->getStyle('A5:O' . $rowIdx)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        foreach (range('A', 'O') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $writer   = new Xlsx($spreadsheet);
        $fileName = 'Laporan_BOR_Rawat_Inap_' . $tahun . '.xlsx';
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

