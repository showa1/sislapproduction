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

        // Ambil kapasitas Tempat Tidur (TT) SIOP dari config/params.php (default: 102)
        $jumlahTT = (int)(Yii::$app->params['kapasitas_tt_siop'] ?? 102);

        $joinCaraBayar  = "";
        $whereCaraBayar = "";
        $params         = [
            ':tahun'     => $tahun,
            ':jumlah_tt' => $jumlahTT,
        ];

        if (!empty($caraBayar) && $caraBayar !== 'Semua') {
            $joinCaraBayar  = "JOIN carabayar_m cm ON cm.carabayar_id = pat.carabayar_id";
            $whereCaraBayar = "AND cm.carabayar_nama = :cb";
            $params[':cb']  = $caraBayar;
        }

        $sql = "
            WITH parameter AS (
                SELECT
                    m AS bulan,
                    MAKE_DATE(:tahun, m, 1) AS tgl_awal,
                    (MAKE_DATE(:tahun, m, 1) + INTERVAL '1 month')::date AS tgl_akhir,
                    (:jumlah_tt)::numeric AS jumlah_tt
                FROM generate_series(1, 12) AS m
            ),

            perawatan AS (
                SELECT
                    p.bulan,
                    pat.pasienadmisi_id,

                    GREATEST(
                        pat.tgladmisi,
                        p.tgl_awal::timestamp
                    ) AS mulai_perawatan,

                    LEAST(
                        COALESCE(pat.tglpulang, NOW()),
                        p.tgl_akhir::timestamp
                    ) AS akhir_perawatan

                FROM pasienadmisi_t pat

                JOIN pendaftaran_t pt
                    ON pt.pendaftaran_id = pat.pendaftaran_id

                {$joinCaraBayar}

                CROSS JOIN parameter p

                WHERE pat.tgladmisi < p.tgl_akhir::timestamp
                  AND (
                      pat.tglpulang > p.tgl_awal::timestamp
                      OR (
                          pat.tglpulang IS NULL
                          AND pat.tgladmisi >= CURRENT_DATE - INTERVAL '30 days'
                      )
                  )
                  AND pt.pasienbatalperiksa_id IS NULL
                  AND p.tgl_awal <= CURRENT_DATE
                  {$whereCaraBayar}
            ),

            rekap AS (
                SELECT
                    bulan,
                    COUNT(DISTINCT pasienadmisi_id) AS jumlah_pasien,
                    SUM(
                        EXTRACT(EPOCH FROM (akhir_perawatan - mulai_perawatan)) / 86400
                    ) AS total_hari_perawatan
                FROM perawatan
                WHERE akhir_perawatan > mulai_perawatan
                GROUP BY bulan
            )

            SELECT
                p.bulan,
                p.tgl_awal,
                (p.tgl_akhir - 1) AS tgl_akhir,
                p.jumlah_tt,
                (p.tgl_akhir - p.tgl_awal) AS jumlah_hari_periode,
                COALESCE(r.jumlah_pasien, 0) AS jumlah_pasien,

                ROUND(COALESCE(r.total_hari_perawatan, 0)::numeric, 2)
                    AS total_hari_perawatan,

                ROUND(
                    (
                        COALESCE(r.total_hari_perawatan, 0)
                        / NULLIF(
                            p.jumlah_tt * (p.tgl_akhir - p.tgl_awal),
                            0
                        )
                    * 100)::numeric,
                    2
                ) AS bor_persen

            FROM parameter p
            LEFT JOIN rekap r ON r.bulan = p.bulan
            ORDER BY p.bulan ASC
        ";

        $rows = Yii::$app->db->createCommand($sql, $params)->queryAll();

        $monthly = [];
        $totalPasienTahun        = 0;
        $totalHariPerawatanTahun = 0.0;
        $totalHariKalenderTahun  = 0;
        $totalKapasitasHariTahun = 0;
        $bulanAktif              = 0;
        $hariKalenderAktif       = 0;

        foreach ($rows as $r) {
            $m                   = (int)$r['bulan'];
            $jumlahHariPeriode   = (int)$r['jumlah_hari_periode'];
            $totalHariPerawatan  = (float)$r['total_hari_perawatan'];
            $borPersen           = (float)$r['bor_persen'];
            $jumlahPasien        = (int)$r['jumlah_pasien'];
            $kapasitasHariTT     = $jumlahTT * $jumlahHariPeriode;

            if ($totalHariPerawatan > 0) {
                $bulanAktif++;
                $hariKalenderAktif += $jumlahHariPeriode;
            }

            $monthly[$m] = [
                'tgl_awal'             => $r['tgl_awal'],
                'tgl_akhir'            => $r['tgl_akhir'],
                'jumlah_pasien'        => $jumlahPasien,
                'jumlah_tt'            => $jumlahTT,
                'jumlah_hari_periode'  => $jumlahHariPeriode,
                'kapasitas_hari_tt'    => $kapasitasHariTT,
                'total_hari_perawatan' => $totalHariPerawatan,
                'bor_persen'           => $borPersen,
            ];

            $totalPasienTahun        += $jumlahPasien;
            $totalHariPerawatanTahun += $totalHariPerawatan;
            $totalHariKalenderTahun  += $jumlahHariPeriode;
            $totalKapasitasHariTahun += $kapasitasHariTT;
        }

        $borTahunan = $totalKapasitasHariTahun > 0
            ? round(($totalHariPerawatanTahun / ($jumlahTT * $totalHariKalenderTahun)) * 100, 2)
            : 0.0;

        $borBulanAktif = ($hariKalenderAktif > 0 && $jumlahTT > 0)
            ? round(($totalHariPerawatanTahun / ($jumlahTT * $hariKalenderAktif)) * 100, 2)
            : 0.0;

        $summary = [
            'jumlah_tt'               => $jumlahTT,
            'total_pasien'            => $totalPasienTahun,
            'total_hari_perawatan'    => round($totalHariPerawatanTahun, 2),
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
            ['no' => 1, 'label' => 'Jumlah Pasien Dirawat (jumlah_pasien)', 'key' => 'jumlah_pasien', 'total' => number_format($summary['total_pasien'], 0, ',', '.'), 'decimals' => 0],
            ['no' => 2, 'label' => 'Total Hari Perawatan (HP)', 'key' => 'total_hari_perawatan', 'total' => number_format($summary['total_hari_perawatan'], 2, ',', '.'), 'decimals' => 2],
            ['no' => 3, 'label' => 'Kapasitas Tempat Tidur SIOP (jumlah_tt)', 'key' => 'jumlah_tt', 'total' => $summary['jumlah_tt'] . ' TT', 'decimals' => 0],
            ['no' => 4, 'label' => 'Jumlah Hari Periode (jumlah_hari_periode)', 'key' => 'jumlah_hari_periode', 'total' => $summary['total_hari_kalender'] . ' Hari', 'decimals' => 0],
            ['no' => 5, 'label' => 'Kapasitas Hari Tempat Tidur (TT x Hari)', 'key' => 'kapasitas_hari_tt', 'total' => number_format($summary['total_kapasitas_hari_tt'], 0, ',', '.'), 'decimals' => 0],
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
        $sheet->setCellValue('B' . $rowIdx, 'BOR (BED OCCUPANCY RATE) (%)');
        $colIdx = 'C';
        for ($m = 1; $m <= 12; $m++) {
            $sheet->setCellValue($colIdx . $rowIdx, number_format($monthly[$m]['bor_persen'], 2, ',', '.') . '%');
            $colIdx++;
        }
        $sheet->setCellValue('O' . $rowIdx, number_format($summary['bor_tahunan'], 2, ',', '.') . '%');

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
