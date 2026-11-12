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

class LosController extends BaseController
{
    public function actionIndex()
    {
        $request   = Yii::$app->request;
        $tahun     = (int)$request->get('tahun', date('Y'));
        $caraBayar = $request->get('cara_bayar', 'Semua');
        $ruangan   = $request->get('ruangan_nama', 'Semua');
        $export    = $request->get('export', '0');

        $joins  = "";
        $wheres = "";
        $params = [':tahun' => $tahun];

        if (!empty($caraBayar) && $caraBayar !== 'Semua') {
            $joins .= " JOIN carabayar_m cm ON cm.carabayar_id = pat.carabayar_id ";
            $wheres .= " AND cm.carabayar_nama = :cb ";
            $params[':cb'] = $caraBayar;
        }

        if (!empty($ruangan) && $ruangan !== 'Semua') {
            $joins .= " JOIN ruangan_m rm ON rm.ruangan_id = pat.ruangan_id ";
            $wheres .= " AND rm.ruangan_nama = :rm ";
            $params[':rm'] = $ruangan;
        }

        // 1. Query Indikator LOS per Bulan (Jan - Des)
        $sql = "
            SELECT 
                CAST(EXTRACT(MONTH FROM pat.tglpulang) AS INTEGER) AS bulan,
                COUNT(DISTINCT pat.pasienadmisi_id) AS jumlah_pasien,
                SUM(EXTRACT(EPOCH FROM (pat.tglpulang - pat.tgladmisi)) / 3600) AS total_jam
            FROM pasienadmisi_t pat
            JOIN pendaftaran_t pt ON pt.pendaftaran_id = pat.pendaftaran_id
            {$joins}
            WHERE pat.tglpulang IS NOT NULL 
              AND pt.pasienbatalperiksa_id IS NULL
              AND EXTRACT(YEAR FROM pat.tglpulang) = :tahun
              {$wheres}
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
        $bulanAktif              = 0;

        for ($m = 1; $m <= 12; $m++) {
            $jumlahPasien       = $rawByMonth[$m]['jumlah_pasien'] ?? 0;
            $totalJam           = $rawByMonth[$m]['total_jam'] ?? 0.0;
            $totalHariPerawatan = $totalJam / 24;

            // Rumus LOS (sama dengan Dashboard Rawat Inap):
            // Rata-rata LOS (Jam)  = Total Jam / Jumlah Pasien Pulang
            // Rata-rata LOS (Hari) = (Total Jam / Jumlah Pasien Pulang) / 24 = Total Hari Perawatan / Jumlah Pasien Pulang
            $losJam  = $jumlahPasien > 0 ? ($totalJam / $jumlahPasien) : 0.0;
            $losHari = $jumlahPasien > 0 ? ($totalHariPerawatan / $jumlahPasien) : 0.0;

            if ($jumlahPasien > 0) {
                $bulanAktif++;
            }

            $monthly[$m] = [
                'jumlah_pasien'  => $jumlahPasien,
                'total_jam'      => $totalJam,
                'hari_perawatan' => $totalHariPerawatan,
                'los_jam'        => $losJam,
                'los_hari'       => $losHari,
            ];

            $totalPasienTahun        += $jumlahPasien;
            $totalJamTahun           += $totalJam;
            $totalHariPerawatanTahun += $totalHariPerawatan;
        }

        $rataLosJamTahun  = $totalPasienTahun > 0 ? ($totalJamTahun / $totalPasienTahun) : 0.0;
        $rataLosHariTahun = $totalPasienTahun > 0 ? ($totalHariPerawatanTahun / $totalPasienTahun) : 0.0;

        $summary = [
            'total_pasien'         => $totalPasienTahun,
            'total_jam'            => $totalJamTahun,
            'total_hari_perawatan' => $totalHariPerawatanTahun,
            'bulan_aktif'          => $bulanAktif,
            'rata_los_jam'         => $rataLosJamTahun,
            'rata_los_hari'        => $rataLosHariTahun,
        ];

        // 2. Breakdown Rata-rata LOS (Hari) per Cara Bayar (Jan - Des)
        $joinRmBreakdown  = (!empty($ruangan) && $ruangan !== 'Semua') ? " JOIN ruangan_m rm ON rm.ruangan_id = pat.ruangan_id " : "";
        $whereRmBreakdown = (!empty($ruangan) && $ruangan !== 'Semua') ? " AND rm.ruangan_nama = :rm " : "";
        $whereCbBreakdown = (!empty($caraBayar) && $caraBayar !== 'Semua') ? " AND cm.carabayar_nama = :cb " : "";

        $sqlCb = "
            SELECT 
                cm.carabayar_nama,
                CAST(EXTRACT(MONTH FROM pat.tglpulang) AS INTEGER) AS bulan,
                COUNT(DISTINCT pat.pasienadmisi_id) AS jumlah_pasien,
                SUM(EXTRACT(EPOCH FROM (pat.tglpulang - pat.tgladmisi)) / 3600) AS total_jam
            FROM pasienadmisi_t pat
            JOIN pendaftaran_t pt ON pt.pendaftaran_id = pat.pendaftaran_id
            JOIN carabayar_m cm ON cm.carabayar_id = pat.carabayar_id
            {$joinRmBreakdown}
            WHERE pat.tglpulang IS NOT NULL 
              AND pt.pasienbatalperiksa_id IS NULL
              AND EXTRACT(YEAR FROM pat.tglpulang) = :tahun
              {$whereCbBreakdown}
              {$whereRmBreakdown}
            GROUP BY cm.carabayar_nama, EXTRACT(MONTH FROM pat.tglpulang)
            ORDER BY cm.carabayar_nama ASC, bulan ASC
        ";

        $cbRows = Yii::$app->db->createCommand($sqlCb, $params)->queryAll();
        $breakdownCaraBayar = [];

        foreach ($cbRows as $r) {
            $cbName = $r['carabayar_nama'];
            $b      = (int)$r['bulan'];
            $pasien = (int)$r['jumlah_pasien'];
            $jam    = (float)$r['total_jam'];
            $hp     = $jam / 24;

            if (!isset($breakdownCaraBayar[$cbName])) {
                $breakdownCaraBayar[$cbName] = [
                    'nama'         => $cbName,
                    'bulan'        => array_fill(1, 12, ['pasien' => 0, 'hp' => 0.0, 'los_hari' => 0.0]),
                    'total_pasien' => 0,
                    'total_hp'     => 0.0,
                    'rata_los'     => 0.0,
                ];
            }

            $breakdownCaraBayar[$cbName]['bulan'][$b] = [
                'pasien'   => $pasien,
                'hp'       => $hp,
                'los_hari' => $pasien > 0 ? ($hp / $pasien) : 0.0,
            ];
            $breakdownCaraBayar[$cbName]['total_pasien'] += $pasien;
            $breakdownCaraBayar[$cbName]['total_hp']     += $hp;
        }

        foreach ($breakdownCaraBayar as &$cbItem) {
            $cbItem['rata_los'] = $cbItem['total_pasien'] > 0
                ? ($cbItem['total_hp'] / $cbItem['total_pasien'])
                : 0.0;
        }
        unset($cbItem);

        if ($export === '1') {
            return $this->exportExcel($tahun, $caraBayar, $ruangan, $monthly, $summary, array_values($breakdownCaraBayar));
        }

        $optCaraBayar = Yii::$app->db->createCommand("
            SELECT DISTINCT carabayar_nama 
            FROM carabayar_m 
            ORDER BY carabayar_nama
        ")->queryColumn();

        $optRuangan = Yii::$app->db->createCommand("
            SELECT DISTINCT rm.ruangan_nama 
            FROM ruangan_m rm
            JOIN instalasi_m im ON im.instalasi_id = rm.instalasi_id
            WHERE im.instalasi_nama IN ('Rawat Inap', 'Perawatan Intensif')
              AND rm.ruangan_aktif = true
            ORDER BY rm.ruangan_nama
        ")->queryColumn();

        return $this->render('index', [
            'tahun'              => $tahun,
            'caraBayar'          => $caraBayar,
            'ruangan'            => $ruangan,
            'optCaraBayar'       => $optCaraBayar,
            'optRuangan'         => $optRuangan,
            'monthly'            => $monthly,
            'summary'            => $summary,
            'breakdownCaraBayar' => array_values($breakdownCaraBayar),
        ]);
    }

    private function exportExcel($tahun, $caraBayar, $ruangan, array $monthly, array $summary, array $breakdownCaraBayar)
    {
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan LOS ' . $tahun);

        $sheet->setCellValue('A1', 'Rumah Sakit Priscilla Medical Center');
        $sheet->setCellValue('A2', 'Laporan LOS (Length of Stay) Rawat Inap - Tahun ' . $tahun);
        $sheet->setCellValue('A3', 'Cara Bayar: ' . $caraBayar . ' | Ruangan: ' . $ruangan);
        $sheet->getStyle('A1:A3')->getFont()->setBold(true);

        $bulanNames = [1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'];
        $headers    = array_merge(['No', 'Indikator / Komponen LOS'], array_values($bulanNames), ['Total / Rata-rata']);

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
            ['no' => 1, 'label' => 'Jumlah Pasien Pulang / KRS (Pasien)', 'key' => 'jumlah_pasien', 'total' => number_format($summary['total_pasien'], 0, ',', '.'), 'decimals' => 0],
            ['no' => 2, 'label' => 'Total Jam Perawatan (Jam)', 'key' => 'total_jam', 'total' => number_format($summary['total_jam'], 1, ',', '.'), 'decimals' => 1],
            ['no' => 3, 'label' => 'Total Hari Perawatan / Lama Dirawat (Hari)', 'key' => 'hari_perawatan', 'total' => number_format($summary['total_hari_perawatan'], 1, ',', '.'), 'decimals' => 1],
            ['no' => 4, 'label' => 'Rata-Rata LOS dalam Jam (Jam / Pasien)', 'key' => 'los_jam', 'total' => number_format($summary['rata_los_jam'], 1, ',', '.') . ' Jam', 'decimals' => 1],
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

        // Baris Footer RATA-RATA LOS (HARI)
        $sheet->setCellValue('A' . $rowIdx, '');
        $sheet->setCellValue('B' . $rowIdx, 'RATA-RATA LOS / ALOS (HARI)');
        $colIdx = 'C';
        for ($m = 1; $m <= 12; $m++) {
            $sheet->setCellValue($colIdx . $rowIdx, number_format($monthly[$m]['los_hari'], 1, ',', '.') . ' Hari');
            $colIdx++;
        }
        $sheet->setCellValue('O' . $rowIdx, number_format($summary['rata_los_hari'], 1, ',', '.') . ' Hari');

        $sheet->getStyle('A' . $rowIdx . ':O' . $rowIdx)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '002D72']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getStyle('B' . $rowIdx)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('A5:O' . $rowIdx)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // Tabel 2 di Excel: Breakdown per Cara Bayar
        if (!empty($breakdownCaraBayar)) {
            $rowIdx += 3;
            $sheet->setCellValue('A' . $rowIdx, 'Rincian Rata-Rata LOS (Hari) Berdasarkan Cara Bayar');
            $sheet->getStyle('A' . $rowIdx)->getFont()->setBold(true);
            $rowIdx++;

            $headersCb = array_merge(['No', 'Cara Bayar'], array_values($bulanNames), ['Rata-Rata LOS']);
            $col = 'A';
            foreach ($headersCb as $h) {
                $sheet->setCellValue($col . $rowIdx, $h);
                $col++;
            }
            $headerCbRow = $rowIdx;
            $sheet->getStyle('A' . $headerCbRow . ':O' . $headerCbRow)->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '002D72']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
            $rowIdx++;

            foreach ($breakdownCaraBayar as $idx => $cb) {
                $sheet->setCellValue('A' . $rowIdx, $idx + 1);
                $sheet->setCellValue('B' . $rowIdx, $cb['nama']);
                $colIdx = 'C';
                for ($m = 1; $m <= 12; $m++) {
                    $losVal = $cb['bulan'][$m]['los_hari'] ?? 0;
                    $sheet->setCellValue($colIdx . $rowIdx, $losVal > 0 ? round($losVal, 1) : 0);
                    $colIdx++;
                }
                $sheet->setCellValue('O' . $rowIdx, number_format($cb['rata_los'], 1, ',', '.') . ' Hari');
                $rowIdx++;
            }
            $sheet->getStyle('A' . $headerCbRow . ':O' . ($rowIdx - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        }

        foreach (range('A', 'O') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $writer   = new Xlsx($spreadsheet);
        $fileName = 'Laporan_LOS_Rawat_Inap_' . $tahun . '.xlsx';
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

