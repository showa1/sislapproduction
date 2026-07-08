<?php

namespace app\modules\laboratorium\controllers;

use app\controllers\BaseController;
use yii\data\ArrayDataProvider;
use yii\web\Response;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yii;

class DashboardController extends BaseController
{
    /**
     * SQL base: Direct join dari tabel transaksi dasar (bypass view unpaid-only)
     * Ini menjamin data historis (paid/lunas) dan data baru (unpaid) semuanya muncul.
     */
    private function getBaseQuery(): string
    {
        return "
            FROM pasienmasukpenunjang_t pp
            JOIN pendaftaran_t pd ON pd.pendaftaran_id = pp.pendaftaran_id
            JOIN pasien_m v ON v.pasien_id = pp.pasien_id
            JOIN carabayar_m cb ON cb.carabayar_id = pd.carabayar_id
            JOIN penjaminpasien_m penj ON penj.penjamin_id = pd.penjamin_id
            JOIN ruangan_m r_lab ON r_lab.ruangan_id = pp.ruangan_id
            LEFT JOIN rujukan_t r ON r.rujukan_id = pd.rujukan_id
            LEFT JOIN asalrujukan_m ar ON ar.asalrujukan_id = r.asalrujukan_id
            LEFT JOIN tindakanpelayanan_t tp ON tp.pasienmasukpenunjang_id = pp.pasienmasukpenunjang_id
            LEFT JOIN daftartindakan_m dt ON dt.daftartindakan_id = tp.daftartindakan_id
            LEFT JOIN (
                SELECT pl.daftartindakan_id, MIN(jp.jenispemeriksaanlab_nama) as jenispemeriksaanlab_nama
                FROM pemeriksaanlab_m pl
                JOIN jenispemeriksaanlab_m jp ON jp.jenispemeriksaanlab_id = pl.jenispemeriksaanlab_id
                GROUP BY pl.daftartindakan_id
            ) jp ON jp.daftartindakan_id = tp.daftartindakan_id
            LEFT JOIN pegawai_m peg ON peg.pegawai_id = pp.pegawai_id
            LEFT JOIN gelarbelakang_m gb ON gb.gelarbelakang_id = peg.gelarbelakang_id
            LEFT JOIN kelaspelayanan_m kp ON kp.kelaspelayanan_id = pp.kelaspelayanan_id
            LEFT JOIN asuransipasien_m asur ON asur.asuransipasien_id = pd.asuransipasien_id
            WHERE r_lab.ruangan_nama ILIKE '%laborat%'
        ";
    }

    // ==============================================================
    // ACTION: Dashboard Utama
    // ==============================================================
    public function actionIndex()
    {
        $db = Yii::$app->db;

        // ---- Filter tanggal ----
        $dateFrom = Yii::$app->request->get('date_from', date('Y-m-01'));
        $dateTo   = Yii::$app->request->get('date_to',   date('Y-m-d'));

        // Bulan sebelumnya (MoM)
        $prevFrom = date('Y-m-01', strtotime('-1 month', strtotime($dateFrom)));
        $prevTo   = date('Y-m-t',  strtotime('-1 month', strtotime($dateFrom)));

        $base = $this->getBaseQuery();

        // ============================================================
        // QUERY 1 (digabung): Semua KPI curr+prev dalam 1 query CASE WHEN
        // ============================================================
        $kpiRow = $db->createCommand("
            SELECT
                COUNT(DISTINCT CASE WHEN pp.tglmasukpenunjang >= :df  AND pp.tglmasukpenunjang <= :dt  THEN pp.pasienmasukpenunjang_id END) AS kunj_curr,
                COUNT(DISTINCT CASE WHEN pp.tglmasukpenunjang >= :pdf AND pp.tglmasukpenunjang <= :pdt THEN pp.pasienmasukpenunjang_id END) AS kunj_prev,
                COUNT(DISTINCT CASE WHEN pp.tglmasukpenunjang >= :df  AND pp.tglmasukpenunjang <= :dt
                    AND pp.kunjungan ILIKE '%baru%' THEN pp.pasienmasukpenunjang_id END)              AS kunj_baru,
                COUNT(DISTINCT CASE WHEN tp.tgl_tindakan >= :df  AND tp.tgl_tindakan <= :dt2  THEN tp.tindakanpelayanan_id END) AS item_curr,
                COUNT(DISTINCT CASE WHEN tp.tgl_tindakan >= :pdf AND tp.tgl_tindakan <= :pdt2 THEN tp.tindakanpelayanan_id END) AS item_prev,
                COALESCE(SUM(CASE WHEN tp.tgl_tindakan >= :df  AND tp.tgl_tindakan <= :dt2  THEN tp.tarif_tindakan ELSE 0 END), 0) AS pend_curr,
                COALESCE(SUM(CASE WHEN tp.tgl_tindakan >= :pdf AND tp.tgl_tindakan <= :pdt2 THEN tp.tarif_tindakan ELSE 0 END), 0) AS pend_prev
            $base
            AND (
                (pp.tglmasukpenunjang >= :pdf AND pp.tglmasukpenunjang <= :dt)
                OR (tp.tgl_tindakan   >= :pdf AND tp.tgl_tindakan    <= :dt2)
            )
        ")->bindValues([
            ':df'   => $dateFrom,
            ':dt'   => $dateTo,
            ':dt2'  => $dateTo . ' 23:59:59',
            ':pdf'  => $prevFrom,
            ':pdt'  => $prevTo,
            ':pdt2' => $prevTo . ' 23:59:59',
        ])->queryOne();

        $kunjunganCurr  = (int)($kpiRow['kunj_curr'] ?? 0);
        $kunjunganPrev  = (int)($kpiRow['kunj_prev'] ?? 0);
        $pasienBaru     = (int)($kpiRow['kunj_baru'] ?? 0);
        $itemCurr       = (int)($kpiRow['item_curr'] ?? 0);
        $itemPrev       = (int)($kpiRow['item_prev'] ?? 0);
        $pendapatanCurr = (float)($kpiRow['pend_curr'] ?? 0);
        $pendapatanPrev = (float)($kpiRow['pend_prev'] ?? 0);

        $kunjunganGrowth  = $kunjunganPrev > 0 ? round((($kunjunganCurr - $kunjunganPrev) / $kunjunganPrev) * 100, 1) : 0;
        $itemGrowth       = $itemPrev > 0      ? round((($itemCurr - $itemPrev) / $itemPrev) * 100, 1) : 0;
        $pendapatanGrowth = $pendapatanPrev > 0 ? round((($pendapatanCurr - $pendapatanPrev) / $pendapatanPrev) * 100, 1) : 0;
        $pasienBaruPct    = $kunjunganCurr > 0  ? round(($pasienBaru / $kunjunganCurr) * 100, 1) : 0;

        // ============================================================
        // QUERY 2: Top pemeriksaan terbanyak
        // ============================================================
        $topPemeriksaan = $db->createCommand("
            SELECT dt.daftartindakan_nama AS nama, COUNT(tp.tindakanpelayanan_id) AS total
            $base
            AND tp.tgl_tindakan >= :df AND tp.tgl_tindakan <= :dt
            GROUP BY dt.daftartindakan_nama
            ORDER BY total DESC LIMIT 1
        ")->bindValues([':df' => $dateFrom, ':dt' => $dateTo . ' 23:59:59'])->queryOne();

        // ============================================================
        // CHART: Distribusi Asal Rujukan
        // ============================================================
        $rujukanDist = $db->createCommand("
            SELECT
                COALESCE(ar.asalrujukan_nama, 'Tanpa Rujukan') AS label,
                COUNT(DISTINCT pp.pasienmasukpenunjang_id) AS total
            $base
            AND pp.tglmasukpenunjang >= :df AND pp.tglmasukpenunjang <= :dt
            GROUP BY ar.asalrujukan_nama
            ORDER BY total DESC
        ")->bindValues([':df' => $dateFrom, ':dt' => $dateTo])->queryAll();

        // ============================================================
        // CHART: Distribusi Cara Bayar / Penjamin
        // ============================================================
        $penjaminDist = $db->createCommand("
            SELECT
                cb.carabayar_nama AS label,
                COUNT(DISTINCT pp.pasienmasukpenunjang_id) AS total,
                SUM(tp.tarif_tindakan) AS pendapatan
            $base
            AND pp.tglmasukpenunjang >= :df AND pp.tglmasukpenunjang <= :dt
            GROUP BY cb.carabayar_nama
            ORDER BY total DESC
        ")->bindValues([':df' => $dateFrom, ':dt' => $dateTo])->queryAll();

        // ============================================================
        // CHART: Tren Harian Kunjungan
        // ============================================================
        $trendHarian = $db->createCommand("
            SELECT
                DATE(pp.tglmasukpenunjang) AS tgl,
                COUNT(DISTINCT pp.pasienmasukpenunjang_id) AS total,
                SUM(tp.tarif_tindakan) AS pendapatan
            $base
            AND pp.tglmasukpenunjang >= :df AND pp.tglmasukpenunjang <= :dt
            GROUP BY DATE(pp.tglmasukpenunjang)
            ORDER BY tgl ASC
        ")->bindValues([':df' => $dateFrom, ':dt' => $dateTo])->queryAll();

        // ============================================================
        // TABLE: Top 10 Jenis Pemeriksaan
        // ============================================================
        $topJenisPemeriksaan = $db->createCommand("
            SELECT
                jp.jenispemeriksaanlab_nama AS jenis,
                dt.daftartindakan_nama AS nama_pemeriksaan,
                COUNT(tp.tindakanpelayanan_id) AS total_item,
                COUNT(DISTINCT pp.pasienmasukpenunjang_id) AS total_kunjungan,
                SUM(tp.tarif_tindakan) AS total_tarif
            $base
            AND tp.tgl_tindakan >= :df AND tp.tgl_tindakan <= :dt
            GROUP BY jp.jenispemeriksaanlab_nama, dt.daftartindakan_nama
            ORDER BY total_item DESC
            LIMIT 10
        ")->bindValues([':df' => $dateFrom, ':dt' => $dateTo . ' 23:59:59'])->queryAll();

        // ============================================================
        // TABLE: Statistik per Asal Rujukan × Cara Bayar
        // ============================================================
        $statRujukan = $db->createCommand("
            SELECT
                COALESCE(ar.asalrujukan_nama, 'Tanpa Rujukan') AS asal_rujukan,
                cb.carabayar_nama AS cara_bayar,
                COUNT(DISTINCT pp.pasienmasukpenunjang_id) AS jumlah_kunjungan,
                COUNT(DISTINCT tp.tindakanpelayanan_id) AS jumlah_item,
                SUM(tp.tarif_tindakan) AS total_tarif
            $base
            AND pp.tglmasukpenunjang >= :df AND pp.tglmasukpenunjang <= :dt
            GROUP BY ar.asalrujukan_nama, cb.carabayar_nama
            ORDER BY jumlah_kunjungan DESC
        ")->bindValues([':df' => $dateFrom, ':dt' => $dateTo])->queryAll();

        return $this->render('index', [
            'dateFrom'            => $dateFrom,
            'dateTo'              => $dateTo,
            'kunjunganCurr'       => $kunjunganCurr,
            'kunjunganPrev'       => $kunjunganPrev,
            'kunjunganGrowth'     => $kunjunganGrowth,
            'itemCurr'            => $itemCurr,
            'itemPrev'            => $itemPrev,
            'itemGrowth'          => $itemGrowth,
            'pendapatanCurr'      => $pendapatanCurr,
            'pendapatanPrev'      => $pendapatanPrev,
            'pendapatanGrowth'    => $pendapatanGrowth,
            'topPemeriksaan'      => $topPemeriksaan,
            'pasienBaru'          => $pasienBaru,
            'pasienBaruPct'       => $pasienBaruPct,
            'rujukanDist'         => $rujukanDist,
            'penjaminDist'        => $penjaminDist,
            'trendHarian'         => $trendHarian,
            'topJenisPemeriksaan' => $topJenisPemeriksaan,
            'statRujukan'         => $statRujukan,
        ]);
    }

    // ==============================================================
    // ACTION: Laporan Detail Pasien + Lab + Penjamin + Rujukan
    // ==============================================================
    public function actionLaporan()
    {
        $db = Yii::$app->db;

        $dateFrom         = Yii::$app->request->get('date_from', date('Y-m-01'));
        $dateTo           = Yii::$app->request->get('date_to',   date('Y-m-d'));
        $caraBayar        = Yii::$app->request->get('cara_bayar', '');
        $asalRujukan      = Yii::$app->request->get('asal_rujukan', '');
        $jenisPemeriksaan = Yii::$app->request->get('jenis_pemeriksaan', '');
        $export           = Yii::$app->request->get('export', '0');

        $base = $this->getBaseQuery();

        $rows     = [];
        $errorMsg = null;

        try {
            // ---- Query utama dengan join tabel transaksi dasar ----
            $sql = "
                SELECT
                    v.no_rekam_medik                                     AS no_rekam_medis,
                    v.nama_pasien,
                    v.jeniskelamin                                       AS jenis_kelamin,
                    pd.umur,
                    pd.no_pendaftaran,
                    pd.tgl_pendaftaran,
                    pp.no_masukpenunjang                                 AS no_order_lab,
                    pp.tglmasukpenunjang                                 AS tgl_masuk_lab,
                    pp.kunjungan,
                    COALESCE(pp.statusperiksa, '-')                      AS status_periksa,
                    COALESCE(cb.carabayar_nama, '-')                     AS cara_bayar,
                    COALESCE(penj.penjamin_nama, '-')                    AS penjamin_nama,
                    COALESCE(kp.kelaspelayanan_nama, '-')                 AS kelas_pelayanan,
                    COALESCE(asur.nokartuasuransi, '-')                  AS no_asuransi,
                    COALESCE(r.no_rujukan, '-')                           AS no_surat_rujukan,
                    r.tanggal_rujukan                                     AS tgl_surat_rujukan,
                    COALESCE(r.nama_perujuk, '-')                         AS nama_perujuk,
                    COALESCE(ar.asalrujukan_nama, 'Tanpa Rujukan')        AS asal_rujukan,
                    TRIM(CONCAT(
                        COALESCE(peg.gelardepan,''), ' ',
                        COALESCE(peg.nama_pegawai,''), ' ',
                        COALESCE(gb.gelarbelakang_nama,'')
                    ))                                                    AS dokter_pemeriksa,
                    COUNT(DISTINCT tp.tindakanpelayanan_id)                AS jumlah_item,
                    STRING_AGG(DISTINCT dt.daftartindakan_nama, ', '
                        ORDER BY dt.daftartindakan_nama)                  AS daftar_pemeriksaan,
                    STRING_AGG(DISTINCT jp.jenispemeriksaanlab_nama, ', ') AS jenis_pemeriksaan,
                    COALESCE(SUM(tp.tarif_tindakan), 0)                    AS total_tarif
                $base
                AND pp.tglmasukpenunjang >= :df AND pp.tglmasukpenunjang <= :dt
            ";

            $params = [':df' => $dateFrom, ':dt' => $dateTo . ' 23:59:59'];

            if (!empty($caraBayar)) {
                $sql .= " AND cb.carabayar_nama = :cb";
                $params[':cb'] = $caraBayar;
            }
            if (!empty($asalRujukan)) {
                if ($asalRujukan === 'Tanpa Rujukan') {
                    $sql .= " AND ar.asalrujukan_id IS NULL";
                } else {
                    $sql .= " AND ar.asalrujukan_nama = :ar";
                    $params[':ar'] = $asalRujukan;
                }
            }
            if (!empty($jenisPemeriksaan)) {
                $sql .= " AND jp.jenispemeriksaanlab_nama = :jp";
                $params[':jp'] = $jenisPemeriksaan;
            }

            $sql .= "
                GROUP BY
                    v.pasien_id, v.no_rekam_medik, v.nama_pasien, v.jeniskelamin, pd.umur,
                    pd.pendaftaran_id, pd.no_pendaftaran, pd.tgl_pendaftaran,
                    pp.no_masukpenunjang, pp.tglmasukpenunjang, pp.kunjungan, pp.statusperiksa,
                    cb.carabayar_nama, penj.penjamin_nama, kp.kelaspelayanan_nama, asur.nokartuasuransi,
                    r.no_rujukan, r.tanggal_rujukan, r.nama_perujuk,
                    ar.asalrujukan_nama,
                    peg.gelardepan, peg.nama_pegawai, gb.gelarbelakang_nama
                ORDER BY pp.tglmasukpenunjang DESC
            ";

            $rows = $db->createCommand($sql, $params)->queryAll();

        } catch (\Exception $e) {
            Yii::error('Lab Laporan Error: ' . $e->getMessage(), 'laboratorium');
            $errorMsg = $e->getMessage();
        }

        if ($export === '1' && empty($errorMsg)) {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            $sheet->setCellValue('A1', 'Rumah Sakit Priscilla Medical Center');
            $sheet->setCellValue('A2', 'Laporan Pemeriksaan Laboratorium');
            $sheet->setCellValue('A3', 'Periode: ' . $dateFrom . ' s/d ' . $dateTo);
            $sheet->getStyle('A1:A3')->getFont()->setBold(true);

            // Headers
            $headers = ['No', 'No. RM', 'Nama Pasien', 'L/P', 'Umur', 'No. Order Lab', 'Tgl Masuk Lab', 'No. Pendaftaran', 'Kunjungan', 'Status', 'Cara Bayar', 'Penjamin', 'Asal Rujukan', 'No. Rujukan', 'Perujuk', 'Dokter Periksa', 'Jml Item', 'Daftar Pemeriksaan', 'Total Tarif'];
            $col = 'A';
            foreach ($headers as $h) {
                $sheet->setCellValue($col . '5', $h);
                $col++;
            }
            $sheet->getStyle('A5:S5')->getFont()->setBold(true);

            $rowIdx = 6;
            $i = 1;
            foreach ($rows as $r) {
                $sheet->setCellValue('A' . $rowIdx, $i);
                $sheet->setCellValue('B' . $rowIdx, $r['no_rekam_medis']);
                $sheet->setCellValue('C' . $rowIdx, $r['nama_pasien']);
                $sheet->setCellValue('D' . $rowIdx, $r['jenis_kelamin'] === 'PEREMPUAN' ? 'P' : 'L');
                $sheet->setCellValue('E' . $rowIdx, $r['umur']);
                $sheet->setCellValue('F' . $rowIdx, $r['no_order_lab']);
                $sheet->setCellValue('G' . $rowIdx, $r['tgl_masuk_lab'] ? date('d/m/Y H:i', strtotime($r['tgl_masuk_lab'])) : '-');
                $sheet->setCellValue('H' . $rowIdx, $r['no_pendaftaran']);
                $sheet->setCellValue('I' . $rowIdx, stripos($r['kunjungan'] ?? '', 'baru') !== false ? 'Baru' : 'Lama');
                $sheet->setCellValue('J' . $rowIdx, $r['status_periksa']);
                $sheet->setCellValue('K' . $rowIdx, $r['cara_bayar']);
                $sheet->setCellValue('L' . $rowIdx, $r['penjamin_nama']);
                $sheet->setCellValue('M' . $rowIdx, $r['asal_rujukan'] ?? 'Tanpa Rujukan');
                $sheet->setCellValue('N' . $rowIdx, $r['no_surat_rujukan']);
                $sheet->setCellValue('O' . $rowIdx, $r['nama_perujuk']);
                $sheet->setCellValue('P' . $rowIdx, $r['dokter_pemeriksa']);
                $sheet->setCellValue('Q' . $rowIdx, $r['jumlah_item']);
                $sheet->setCellValue('R' . $rowIdx, $r['daftar_pemeriksaan']);
                $sheet->setCellValue('S' . $rowIdx, $r['total_tarif']);
                $rowIdx++;
                $i++;
            }

            $sheet->getStyle('A5:S'.($rowIdx - 1))->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            
            foreach (range('A', 'S') as $columnID) {
                $sheet->getColumnDimension($columnID)->setAutoSize(true);
            }

            $writer = new Xlsx($spreadsheet);
            $fileName = 'Laporan_Pemeriksaan_Lab.xlsx';
            $tempFile = tempnam(sys_get_temp_dir(), $fileName);
            $writer->save($tempFile);

            return Yii::$app->response->sendFile($tempFile, $fileName)->on(
                \yii\web\Response::EVENT_AFTER_SEND,
                function ($event) {
                    unlink($event->data);
                },
                $tempFile
            );
        }

        // ---- Dropdown filter (try-catch terpisah) ----
        $optCaraBayar = $optAsalRujukan = $optJenisPemeriksaan = [];
        try {
            $optCaraBayar = $db->createCommand("
                SELECT DISTINCT cb.carabayar_nama AS val $base
                AND pp.tglmasukpenunjang >= :df AND pp.tglmasukpenunjang <= :dt
                AND cb.carabayar_nama IS NOT NULL ORDER BY cb.carabayar_nama
            ")->bindValues([':df' => $dateFrom, ':dt' => $dateTo . ' 23:59:59'])->queryColumn();

            $optAsalRujukan = $db->createCommand("
                SELECT DISTINCT COALESCE(ar.asalrujukan_nama, 'Tanpa Rujukan') AS val $base
                AND pp.tglmasukpenunjang >= :df AND pp.tglmasukpenunjang <= :dt ORDER BY val
            ")->bindValues([':df' => $dateFrom, ':dt' => $dateTo . ' 23:59:59'])->queryColumn();

            $optJenisPemeriksaan = $db->createCommand("
                SELECT DISTINCT jp.jenispemeriksaanlab_nama AS val $base
                AND pp.tglmasukpenunjang >= :df AND pp.tglmasukpenunjang <= :dt
                AND jp.jenispemeriksaanlab_nama IS NOT NULL ORDER BY val
            ")->bindValues([':df' => $dateFrom, ':dt' => $dateTo . ' 23:59:59'])->queryColumn();
        } catch (\Exception $e) {
            Yii::warning('Lab filter options error: ' . $e->getMessage(), 'laboratorium');
        }

        return $this->render('laporan', [
            'dateFrom'            => $dateFrom,
            'dateTo'              => $dateTo,
            'caraBayar'           => $caraBayar,
            'asalRujukan'         => $asalRujukan,
            'jenisPemeriksaan'    => $jenisPemeriksaan,
            'rows'                => $rows,
            'optCaraBayar'        => $optCaraBayar,
            'optAsalRujukan'      => $optAsalRujukan,
            'optJenisPemeriksaan' => $optJenisPemeriksaan,
            'totalKunjungan'      => count($rows),
            'totalTarif'          => array_sum(array_column($rows, 'total_tarif')),
            'errorMsg'            => $errorMsg,
        ]);
    }
}
