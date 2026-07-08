<?php

namespace app\modules\eksekutif\controllers;

use app\controllers\BaseController;
use app\modules\eksekutif\models\DashboardSearch;
use yii\data\ArrayDataProvider;
use yii\filters\VerbFilter;
use yii\web\Response;
use Yii;
use DateTime;

class DashboardController extends BaseController
{
    // ----------------------------------------------------------------
    // instalasi_id  →  unit_bisnis label
    // ----------------------------------------------------------------
    private static $INSTALASI_MAP = [
        2  => 'Rajal',
        3  => 'IGD',
        4  => 'Ranap',
        5  => 'Laboratorium',
        6  => 'Radiologi',
        7  => 'IBS',
        9  => 'Farmasi',
        20 => 'ICU/PICU',
        38 => 'Ranap',       // Kebidanan → Ranap
        72 => 'Fisioterapi',
        73 => 'Hemodialisa',
        74 => 'Fisioterapi',
        76 => 'ICU/PICU',
        81 => 'IBS',
        82 => 'MCU',
    ];

    // unit label  →  instalasi_ids  (untuk AJAX trend)
    private static $UNIT_TO_INSTALASI = [
        'Rajal'        => [2],
        'IGD'          => [3],
        'Ranap'        => [4, 38],
        'IBS'          => [7, 81],
        'ICU/PICU'     => [20, 76],
        'Hemodialisa'  => [73],
        'Radiologi'    => [6],
        'Laboratorium' => [5],
        'Farmasi'      => [9],
        'MCU'          => [82],
        'VK'           => [38],
        'Lainnya'      => [72, 74, 10],
    ];

    /**
     * SQL CASE expression: normalise carabayar_m.carabayar_nama → 4 canonical labels.
     * Assumes alias 'cb' for carabayar_m in the query.
     */
    private function cbCase(): string
    {
        return "CASE
            WHEN UPPER(cb.carabayar_nama) LIKE '%BPJS KES%'  THEN 'BPJS Kesehatan'
            WHEN UPPER(cb.carabayar_nama) LIKE '%JASA RAH%'  THEN 'Jasa Raharja'
            WHEN UPPER(cb.carabayar_nama) LIKE '%ASURANSI%'  THEN 'Asuransi Komersial'
            ELSE 'Umum'
        END";
    }

    /** @inheritdoc */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class'   => VerbFilter::class,
                'actions' => [
                    'index'          => ['GET', 'POST'],
                    'get-trend-data' => ['GET'],
                ],
            ],
        ]);
    }

    // ================================================================
    // ACTION: AJAX — Trend Pendapatan per cara_bayar per unit
    // ================================================================
    /**
     * Returns JSON trend data (pendapatan in juta Rp) for the selected
     * unit and time scale, queried directly from PostgreSQL.
     */
    public function actionGetTrendData($unit, $scale)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $db = Yii::$app->db;

        $validUnits = array_keys(self::$UNIT_TO_INSTALASI);
        if (!in_array($unit, $validUnits, true)) $unit = 'Rajal';
        if (!in_array($scale, ['Harian','Bulanan','Tahunan'], true)) $scale = 'Bulanan';

        $insIds    = self::$UNIT_TO_INSTALASI[$unit];
        $insSql    = implode(',', $insIds);
        $cbCase    = $this->cbCase();

        // Latest date in the live DB
        $maxDate = $db->createCommand(
            "SELECT MAX(tgl_pendaftaran::date) FROM pendaftaran_t WHERE pasienbatalperiksa_id IS NULL"
        )->queryScalar() ?: date('Y-m-d');

        $dtTo = new DateTime($maxDate);
        if ($scale === 'Harian')      $dtFrom = (clone $dtTo)->modify('-29 days');
        elseif ($scale === 'Tahunan') $dtFrom = (clone $dtTo)->modify('-4 years');
        else                          $dtFrom = (clone $dtTo)->modify('-11 months');

        $dateFrom = $dtFrom->format('Y-m-d');
        $dateTo   = $dtTo->format('Y-m-d');

        // Build category spine and period SQL expression
        $categories = [];
        $labelKeys  = [];

        if ($scale === 'Harian') {
            $cursor = clone $dtFrom;
            while ($cursor <= $dtTo) {
                $categories[] = $cursor->format('d M');
                $labelKeys[]  = $cursor->format('Y-m-d');
                $cursor->modify('+1 day');
            }
            $periodExpr = "p.tgl_pendaftaran::date::text";

        } elseif ($scale === 'Tahunan') {
            $sy = (int)$dtFrom->format('Y');
            $ey = (int)$dtTo->format('Y');
            for ($y = $sy; $y <= $ey; $y++) {
                $categories[] = $labelKeys[] = (string)$y;
            }
            $periodExpr = "EXTRACT(YEAR FROM p.tgl_pendaftaran)::text";

        } else { // Bulanan
            $cursor = new DateTime($dtFrom->format('Y-m-01'));
            $end    = new DateTime($dtTo->format('Y-m-01'));
            while ($cursor <= $end) {
                $categories[] = $cursor->format('M Y');
                $labelKeys[]  = $cursor->format('Y-m');
                $cursor->modify('+1 month');
            }
            $periodExpr = "TO_CHAR(p.tgl_pendaftaran, 'YYYY-MM')";
        }

        $rawData = $db->createCommand("
            SELECT
                {$periodExpr} AS period_key,
                SUM(CASE WHEN {$cbCase} = 'BPJS Kesehatan'     THEN COALESCE(tp.total_tarifakhir,0) ELSE 0 END) / 1000000.0 AS bpjs,
                SUM(CASE WHEN {$cbCase} = 'Jasa Raharja'        THEN COALESCE(tp.total_tarifakhir,0) ELSE 0 END) / 1000000.0 AS jasa_raharja,
                SUM(CASE WHEN {$cbCase} = 'Asuransi Komersial'  THEN COALESCE(tp.total_tarifakhir,0) ELSE 0 END) / 1000000.0 AS komersial,
                SUM(CASE WHEN {$cbCase} = 'Umum'                THEN COALESCE(tp.total_tarifakhir,0) ELSE 0 END) / 1000000.0 AS umum
            FROM pendaftaran_t p
            JOIN ruangan_m rm ON rm.ruangan_id = p.ruangan_id
            LEFT JOIN carabayar_m cb ON cb.carabayar_id = p.carabayar_id
            LEFT JOIN tindakanpelayanan_t tp ON tp.pendaftaran_id = p.pendaftaran_id
            WHERE p.tgl_pendaftaran::date BETWEEN :df AND :dt
              AND rm.instalasi_id IN ({$insSql})
              AND p.pasienbatalperiksa_id IS NULL
            GROUP BY period_key
            ORDER BY period_key
        ", [':df' => $dateFrom, ':dt' => $dateTo])->queryAll();

        $matrix = [];
        foreach ($rawData as $row) {
            $matrix[$row['period_key']] = [
                'bpjs'         => (float)$row['bpjs'],
                'jasa_raharja' => (float)$row['jasa_raharja'],
                'komersial'    => (float)$row['komersial'],
                'umum'         => (float)$row['umum'],
            ];
        }

        $series = ['bpjs'=>[], 'jasa_raharja'=>[], 'komersial'=>[], 'umum'=>[]];
        foreach ($labelKeys as $lk) {
            $m = $matrix[$lk] ?? ['bpjs'=>0.0,'jasa_raharja'=>0.0,'komersial'=>0.0,'umum'=>0.0];
            $series['bpjs'][]         = round($m['bpjs'],         2);
            $series['jasa_raharja'][] = round($m['jasa_raharja'], 2);
            $series['komersial'][]    = round($m['komersial'],    2);
            $series['umum'][]         = round($m['umum'],         2);
        }

        return [
            'categories' => $categories,
            'series' => [
                ['name' => 'BPJS Kesehatan',    'data' => $series['bpjs']],
                ['name' => 'Jasa Raharja',       'data' => $series['jasa_raharja']],
                ['name' => 'Asuransi Komersial', 'data' => $series['komersial']],
                ['name' => 'Umum',               'data' => $series['umum']],
            ],
        ];
    }

    // ================================================================
    // ACTION: Main Dashboard Page
    // ================================================================
    /**
     * Renders the executive dashboard.
     * Queries directly from PostgreSQL (Yii::$app->db).
     */
    public function actionIndex()
    {
        $searchModel = new DashboardSearch();
        $params      = Yii::$app->request->queryParams;
        $activeTab   = Yii::$app->request->get('tab', 'ringkasan');
        $db          = Yii::$app->db;
        $cbCase      = $this->cbCase();
        $instalasiMap = self::$INSTALASI_MAP;

        // ---- Date range ----
        if (!isset($params['DashboardSearch']['date_from']) && !isset($params['DashboardSearch']['date_to'])) {
            // Default: bulan terakhir yang ada datanya di DB
            $latestDate = $db->createCommand(
                "SELECT MAX(tgl_pendaftaran::date) FROM pendaftaran_t WHERE pasienbatalperiksa_id IS NULL"
            )->queryScalar();
            if ($latestDate) {
                $dtLatest = new DateTime($latestDate);
                $searchModel->date_from = $dtLatest->format('Y-m-01');
                $searchModel->date_to   = $dtLatest->format('Y-m-t');
            } else {
                $searchModel->date_from = date('Y-m-01');
                $searchModel->date_to   = date('Y-m-d');
            }
        } else {
            $searchModel->load($params);
        }

        $date_from = $searchModel->date_from;
        $date_to   = $searchModel->date_to;
        $dt_from   = new DateTime($date_from);
        $dt_to     = new DateTime($date_to);
        $year      = $dt_from->format('Y');

        // Previous-month range (for MoM growth)
        $prev_date_from = (clone $dt_from)->modify('-1 month')->format('Y-m-d');
        $prev_date_to   = (clone $dt_to)->modify('-1 month')->format('Y-m-d');

        // ---- Helper closure: count pendaftaran for given instalasi_ids ----
        $countFor = function(array $ids, string $df, string $dt) use ($db): int {
            $insSql = implode(',', $ids);
            return (int)$db->createCommand("
                SELECT COUNT(p.pendaftaran_id)
                FROM pendaftaran_t p
                JOIN ruangan_m rm ON rm.ruangan_id = p.ruangan_id
                WHERE rm.instalasi_id IN ({$insSql})
                  AND p.tgl_pendaftaran::date BETWEEN :df AND :dt
                  AND p.pasienbatalperiksa_id IS NULL
            ", [':df' => $df, ':dt' => $dt])->queryScalar();
        };

        // ==========================================================
        // KPI 1 — Rawat Jalan
        // ==========================================================
        $rj_curr   = $countFor([2], $date_from, $date_to);
        $rj_prev   = $countFor([2], $prev_date_from, $prev_date_to);
        $rj_growth = $rj_prev > 0 ? round(($rj_curr - $rj_prev) / $rj_prev * 100, 1) : 0;

        $rj_baru = (int)$db->createCommand("
            SELECT COUNT(p.pendaftaran_id)
            FROM pendaftaran_t p
            JOIN ruangan_m rm ON rm.ruangan_id = p.ruangan_id
            WHERE rm.instalasi_id = 2
              AND p.tgl_pendaftaran::date BETWEEN :df AND :dt
              AND p.pasienbatalperiksa_id IS NULL
              AND p.kunjungan = 'Baru'
        ", [':df' => $date_from, ':dt' => $date_to])->queryScalar();
        $rj_baru_pct = $rj_curr > 0 ? round($rj_baru / $rj_curr * 100, 1) : 0;

        // ==========================================================
        // KPI 2 — IGD
        // ==========================================================
        $igd_curr   = $countFor([3], $date_from, $date_to);
        $igd_prev   = $countFor([3], $prev_date_from, $prev_date_to);
        $igd_growth = $igd_prev > 0 ? round(($igd_curr - $igd_prev) / $igd_prev * 100, 1) : 0;

        // ==========================================================
        // KPI 3 — Rawat Inap (Menggunakan pasienadmisi_t)
        // ==========================================================
        $countRanapFor = function(string $df, string $dt) use ($db): int {
            return (int)$db->createCommand("
                SELECT COUNT(pa.pasienadmisi_id)
                FROM pasienadmisi_t pa
                JOIN ruangan_m rm ON rm.ruangan_id = pa.ruangan_id
                JOIN pendaftaran_t p ON p.pendaftaran_id = pa.pendaftaran_id
                WHERE pa.tgladmisi::date BETWEEN :df AND :dt
                  AND rm.instalasi_id IN (4, 38)
                  AND p.pasienbatalperiksa_id IS NULL
            ", [':df' => $df, ':dt' => $dt])->queryScalar();
        };
        $ranap_curr   = $countRanapFor($date_from, $date_to);
        $ranap_prev   = $countRanapFor($prev_date_from, $prev_date_to);
        $ranap_growth = $ranap_prev > 0 ? round(($ranap_curr - $ranap_prev) / $ranap_prev * 100, 1) : 0;
        $bor_pct      = round($ranap_curr / 1320 * 100, 1);

        // ==========================================================
        // KPI 4 — Lab & Rad (Menggunakan pasienmasukpenunjang_t)
        // ==========================================================
        $countLabradFor = function(string $df, string $dt) use ($db): int {
            return (int)$db->createCommand("
                SELECT COUNT(DISTINCT pmp.pendaftaran_id)
                FROM pasienmasukpenunjang_t pmp
                JOIN ruangan_m rm ON rm.ruangan_id = pmp.ruangan_id
                JOIN pendaftaran_t p ON p.pendaftaran_id = pmp.pendaftaran_id
                WHERE pmp.tglmasukpenunjang::date BETWEEN :df AND :dt
                  AND rm.instalasi_id IN (5, 6)
                  AND p.pasienbatalperiksa_id IS NULL
            ", [':df' => $df, ':dt' => $dt])->queryScalar();
        };
        $labrad_curr   = $countLabradFor($date_from, $date_to);
        $labrad_prev   = $countLabradFor($prev_date_from, $prev_date_to);
        $labrad_growth = $labrad_prev > 0 ? round(($labrad_curr - $labrad_prev) / $labrad_prev * 100, 1) : 0;


        // ==========================================================
        // Closing Status — sudah/belum closing kasir per unit
        // Menggunakan pembayaranpelayanan_id IS NULL sebagai indikator belum bayar
        // ==========================================================
        $kpiClosing = [];
        foreach (['rj'=>[2], 'igd'=>[3], 'ranap'=>[4,38], 'labrad'=>[5,6]] as $key => $ids) {
            $insSql = implode(',', $ids);
            $total  = match($key) {
                'rj'    => $rj_curr,
                'igd'   => $igd_curr,
                'ranap' => $ranap_curr,
                default => $labrad_curr,
            };
            if ($key === 'ranap') {
                $pending = (int)$db->createCommand("
                    SELECT COUNT(pa.pasienadmisi_id)
                    FROM pasienadmisi_t pa
                    JOIN ruangan_m rm ON rm.ruangan_id = pa.ruangan_id
                    JOIN pendaftaran_t p ON p.pendaftaran_id = pa.pendaftaran_id
                    WHERE pa.tgladmisi::date BETWEEN :df AND :dt
                      AND rm.instalasi_id IN (4, 38)
                      AND p.pasienbatalperiksa_id IS NULL
                      AND pa.pembayaranpelayanan_id IS NULL
                ", [':df' => $date_from, ':dt' => $date_to])->queryScalar();
            } elseif ($key === 'labrad') {
                $pending = (int)$db->createCommand("
                    SELECT COUNT(DISTINCT pmp.pendaftaran_id)
                    FROM pasienmasukpenunjang_t pmp
                    JOIN ruangan_m rm ON rm.ruangan_id = pmp.ruangan_id
                    JOIN pendaftaran_t p ON p.pendaftaran_id = pmp.pendaftaran_id
                    WHERE pmp.tglmasukpenunjang::date BETWEEN :df AND :dt
                      AND rm.instalasi_id IN (5, 6)
                      AND p.pasienbatalperiksa_id IS NULL
                      AND p.pembayaranpelayanan_id IS NULL
                ", [':df' => $date_from, ':dt' => $date_to])->queryScalar();
            } else {
                $pending = (int)$db->createCommand("
                    SELECT COUNT(p.pendaftaran_id) FROM pendaftaran_t p
                    JOIN ruangan_m rm ON rm.ruangan_id = p.ruangan_id
                    WHERE rm.instalasi_id IN ({$insSql})
                      AND p.tgl_pendaftaran::date BETWEEN :df AND :dt
                      AND p.pasienbatalperiksa_id IS NULL
                      AND p.pembayaranpelayanan_id IS NULL
                ", [':df' => $date_from, ':dt' => $date_to])->queryScalar();
            }
            $closing = $total - $pending;
            $kpiClosing[$key] = [
                'closing' => $closing,
                'pending' => $pending,
                'pct'     => $total > 0 ? round($closing / $total * 100) : 0,
            ];
        }

        // ==========================================================
        // KPI 5 — Proporsi Penjamin Rawat Jalan (Donut Card)
        // ==========================================================
        $rjPenjaminRaw = $db->createCommand("
            SELECT {$cbCase} AS cara_bayar, COUNT(*) AS total
            FROM pendaftaran_t p
            JOIN ruangan_m rm ON rm.ruangan_id = p.ruangan_id
            LEFT JOIN carabayar_m cb ON cb.carabayar_id = p.carabayar_id
            WHERE rm.instalasi_id = 2
              AND p.tgl_pendaftaran::date BETWEEN :df AND :dt
              AND p.pasienbatalperiksa_id IS NULL
            GROUP BY cara_bayar
        ", [':df' => $date_from, ':dt' => $date_to])->queryAll();

        $rjPenjaminMap   = array_column($rjPenjaminRaw, 'total', 'cara_bayar');
        $rjPenjaminOrder = ['BPJS Kesehatan', 'Jasa Raharja', 'Asuransi Komersial', 'Umum'];
        $rjPenjaminData  = [];
        $rjTotalPenjamin = 0;
        foreach ($rjPenjaminOrder as $cb) {
            $val = (int)($rjPenjaminMap[$cb] ?? 0);
            $rjPenjaminData[$cb] = $val;
            $rjTotalPenjamin += $val;
        }
        $rjPenjaminLabels = array_keys($rjPenjaminData);
        $rjPenjaminValues = array_values($rjPenjaminData);

        // ==========================================================
        // KPI 6 — Mini Progress Bars (penjamin per card)
        // ==========================================================
        $allInsIds  = [2, 3, 4, 5, 6, 7, 9, 20, 38, 72, 73, 74, 76, 81, 82];
        $allInsSql  = implode(',', $allInsIds);

        $kpiPenjaminRaw = $db->createCommand("
            SELECT rm.instalasi_id, {$cbCase} AS cara_bayar, COUNT(*) AS total
            FROM pendaftaran_t p
            JOIN ruangan_m rm ON rm.ruangan_id = p.ruangan_id
            LEFT JOIN carabayar_m cb ON cb.carabayar_id = p.carabayar_id
            WHERE rm.instalasi_id IN ({$allInsSql})
              AND p.tgl_pendaftaran::date BETWEEN :df AND :dt
              AND p.pasienbatalperiksa_id IS NULL
            GROUP BY rm.instalasi_id, cara_bayar
        ", [':df' => $date_from, ':dt' => $date_to])->queryAll();

        // Build matrix [unit_label][cara_bayar] = total
        $kpiMatrix = [];
        foreach ($kpiPenjaminRaw as $row) {
            $unit = $instalasiMap[(int)$row['instalasi_id']] ?? 'Lainnya';
            $cb   = $row['cara_bayar'];
            $kpiMatrix[$unit][$cb] = ($kpiMatrix[$unit][$cb] ?? 0) + (int)$row['total'];
        }

        // --- Perbaikan khusus Rawat Inap (Menggunakan pasienadmisi_t) ---
        $ranapPenjaminRaw = $db->createCommand("
            SELECT {$cbCase} AS cara_bayar, COUNT(pa.pasienadmisi_id) AS total
            FROM pasienadmisi_t pa
            JOIN ruangan_m rm ON rm.ruangan_id = pa.ruangan_id
            JOIN pendaftaran_t p ON p.pendaftaran_id = pa.pendaftaran_id
            LEFT JOIN carabayar_m cb ON cb.carabayar_id = pa.carabayar_id
            WHERE pa.tgladmisi::date BETWEEN :df AND :dt
              AND rm.instalasi_id IN (4, 38)
              AND p.pasienbatalperiksa_id IS NULL
            GROUP BY cara_bayar
        ", [':df' => $date_from, ':dt' => $date_to])->queryAll();
        
        $kpiMatrix['Ranap'] = [];
        foreach ($ranapPenjaminRaw as $row) {
            $kpiMatrix['Ranap'][$row['cara_bayar']] = (int)$row['total'];
        }
        // ----------------------------------------------------------------

        // Merge Lab + Rad into 'labrad'
        // --- Perbaikan khusus Lab & Rad (Menggunakan pasienmasukpenunjang_t) ---
        $labradPenjaminRaw = $db->createCommand("
            SELECT {$cbCase} AS cara_bayar, COUNT(DISTINCT pmp.pendaftaran_id) AS total
            FROM pasienmasukpenunjang_t pmp
            JOIN ruangan_m rm ON rm.ruangan_id = pmp.ruangan_id
            JOIN pendaftaran_t p ON p.pendaftaran_id = pmp.pendaftaran_id
            LEFT JOIN carabayar_m cb ON cb.carabayar_id = p.carabayar_id
            WHERE pmp.tglmasukpenunjang::date BETWEEN :df AND :dt
              AND rm.instalasi_id IN (5, 6)
              AND p.pasienbatalperiksa_id IS NULL
            GROUP BY cara_bayar
        ", [':df' => $date_from, ':dt' => $date_to])->queryAll();
        
        $kpiMatrix['labrad'] = [];
        foreach ($labradPenjaminRaw as $row) {
            $kpiMatrix['labrad'][$row['cara_bayar']] = (int)$row['total'];
        }
        // ----------------------------------------------------------------


        $cbColors = ['BPJS Kesehatan'=>'#3B82F6','Jasa Raharja'=>'#10B981','Asuransi Komersial'=>'#F59E0B','Umum'=>'#8B5CF6'];
        $cbShort  = ['BPJS Kesehatan'=>'BPJS','Jasa Raharja'=>'JR','Asuransi Komersial'=>'Asuransi','Umum'=>'Umum'];

        $kpiPenjamin = [];
        foreach (['rj'=>'Rajal','igd'=>'IGD','ranap'=>'Ranap','labrad'=>'labrad'] as $key => $matrixKey) {
            $data  = $kpiMatrix[$matrixKey] ?? [];
            arsort($data);
            $total = array_sum($data);
            $rows  = [];
            $count = 0;
            foreach ($data as $cb => $v) {
                if ($v <= 0 || $count >= 3) continue;
                $rows[] = [
                    'label' => $cbShort[$cb] ?? $cb,
                    'color' => $cbColors[$cb] ?? '#94A3B8',
                    'val'   => $v,
                    'pct'   => $total > 0 ? round($v / $total * 100, 1) : 0,
                ];
                $count++;
            }
            $kpiPenjamin[$key] = $rows;
        }

        // ==========================================================
        // 12-Month Trend Bar Chart
        // ==========================================================
        $trendRaw = $db->createCommand("
            SELECT
                EXTRACT(MONTH FROM p.tgl_pendaftaran)::int AS bln,
                rm.instalasi_id,
                COUNT(p.pendaftaran_id) AS total
            FROM pendaftaran_t p
            JOIN ruangan_m rm ON rm.ruangan_id = p.ruangan_id
            WHERE EXTRACT(YEAR FROM p.tgl_pendaftaran) = :yr
              AND p.pasienbatalperiksa_id IS NULL
            GROUP BY bln, rm.instalasi_id
        ", [':yr' => $year])->queryAll();

        $trendRJ = $trendIGD = $trendRI = $trendPenunjang = array_fill(0, 12, 0);
        foreach ($trendRaw as $row) {
            $m    = (int)$row['bln'] - 1;
            $unit = $instalasiMap[(int)$row['instalasi_id']] ?? 'Lainnya';
            $tot  = (int)$row['total'];
            switch ($unit) {
                case 'Rajal': $trendRJ[$m]       += $tot; break;
                case 'IGD':   $trendIGD[$m]      += $tot; break;
                case 'Ranap': $trendRI[$m]        += $tot; break;
                default:      $trendPenunjang[$m] += $tot; break;
            }
        }

        // ==========================================================
        // Heatmap (Rawat Jalan + IGD, by hari & jam)
        // ==========================================================
        $hariMapPg = [
            'Monday'=>'Senin','Tuesday'=>'Selasa','Wednesday'=>'Rabu',
            'Thursday'=>'Kamis','Friday'=>'Jumat','Saturday'=>'Sabtu','Sunday'=>'Minggu',
        ];
        $dayMapRev = ['Senin'=>'Sen','Selasa'=>'Sel','Rabu'=>'Rab','Kamis'=>'Kam','Jumat'=>'Jum','Sabtu'=>'Sab','Minggu'=>'Min'];

        $heatmapRaw = $db->createCommand("
            SELECT
                TRIM(TO_CHAR(p.tgl_pendaftaran, 'Day'))                          AS hari_en,
                LPAD(FLOOR(EXTRACT(HOUR FROM p.tgl_pendaftaran))::int::text, 2, '0') || ':00' AS jam,
                COUNT(*) AS total
            FROM pendaftaran_t p
            JOIN ruangan_m rm ON rm.ruangan_id = p.ruangan_id
            WHERE rm.instalasi_id IN (2, 3)
              AND p.tgl_pendaftaran::date BETWEEN :df AND :dt
              AND p.pasienbatalperiksa_id IS NULL
            GROUP BY hari_en, jam
        ", [':df' => $date_from, ':dt' => $date_to])->queryAll();

        $hours = ['07:00','08:00','09:00','10:00','11:00','12:00','13:00','14:00','15:00','16:00','17:00','18:00','19:00','20:00','21:00'];
        $days  = ['Sen','Sel','Rab','Kam','Jum','Sab','Min'];

        $heatmapData = [];
        foreach ($hours as $h) {
            foreach ($days as $d) $heatmapData[$h][$d] = 0;
        }
        foreach ($heatmapRaw as $row) {
            $hariId   = $hariMapPg[$row['hari_en']] ?? '';
            $dayShort = $dayMapRev[$hariId] ?? '';
            $jam      = $row['jam'];
            if (isset($heatmapData[$jam]) && $dayShort) {
                $heatmapData[$jam][$dayShort] = (int)$row['total'];
            }
        }
        $heatmapSeries = [];
        foreach ($hours as $h) {
            $data = [];
            foreach ($days as $d) $data[] = ['x' => $d, 'y' => $heatmapData[$h][$d]];
            $heatmapSeries[] = ['name' => $h, 'data' => $data];
        }

        // ==========================================================
        // Donut Chart — Cara Bayar (semua unit)
        // ==========================================================
        $donutRaw = $db->createCommand("
            SELECT {$cbCase} AS cara_bayar, COUNT(*) AS total
            FROM pendaftaran_t p
            JOIN ruangan_m rm ON rm.ruangan_id = p.ruangan_id
            LEFT JOIN carabayar_m cb ON cb.carabayar_id = p.carabayar_id
            WHERE p.tgl_pendaftaran::date BETWEEN :df AND :dt
              AND p.pasienbatalperiksa_id IS NULL
            GROUP BY cara_bayar
        ", [':df' => $date_from, ':dt' => $date_to])->queryAll();

        $orderedCb   = ['BPJS Kesehatan', 'Jasa Raharja', 'Asuransi Komersial', 'Umum'];
        $donutMap    = array_column($donutRaw, 'total', 'cara_bayar');
        $donutLabels = $donutValues = [];
        foreach ($orderedCb as $cb) {
            if (!empty($donutMap[$cb])) {
                $donutLabels[] = $cb;
                $donutValues[] = (int)$donutMap[$cb];
            }
        }

        // ==========================================================
        // Top 5 Penunjang GridView
        // ==========================================================
        $penunjangRaw = $db->createCommand("
            SELECT im.instalasi_nama AS unit_bisnis, COUNT(p.pendaftaran_id) AS total
            FROM pendaftaran_t p
            JOIN ruangan_m rm ON rm.ruangan_id = p.ruangan_id
            JOIN instalasi_m im ON im.instalasi_id = rm.instalasi_id
            WHERE rm.instalasi_id IN (5, 6, 9, 72, 74, 10)
              AND p.tgl_pendaftaran::date BETWEEN :df AND :dt
              AND p.pasienbatalperiksa_id IS NULL
            GROUP BY im.instalasi_nama
            ORDER BY total DESC
            LIMIT 5
        ", [':df' => $date_from, ':dt' => $date_to])->queryAll();

        $dataProviderPenunjang = new ArrayDataProvider([
            'allModels'  => $penunjangRaw,
            'pagination' => false,
        ]);

        // ==========================================================
        // PENDAPATAN TAB (hanya jika aktif)
        // ==========================================================
        $pendapatanData = [];

        if ($activeTab === 'pendapatan') {

            // Revenue by cara_bayar
            $revByCBRaw = $db->createCommand("
                SELECT {$cbCase} AS cara_bayar,
                       SUM(COALESCE(tp.total_tarifakhir, 0)) AS total_rev
                FROM pendaftaran_t p
                JOIN ruangan_m rm ON rm.ruangan_id = p.ruangan_id
                LEFT JOIN carabayar_m cb ON cb.carabayar_id = p.carabayar_id
                LEFT JOIN tindakanpelayanan_t tp ON tp.pendaftaran_id = p.pendaftaran_id
                WHERE p.tgl_pendaftaran::date BETWEEN :df AND :dt
                  AND p.pasienbatalperiksa_id IS NULL
                GROUP BY cara_bayar
            ", [':df' => $date_from, ':dt' => $date_to])->queryAll();

            $revMap   = array_column($revByCBRaw, 'total_rev', 'cara_bayar');
            $totalRev = (float)array_sum($revMap);

            $kpiCB = [];
            foreach ($orderedCb as $cb) {
                $val      = (float)($revMap[$cb] ?? 0);
                $pct      = $totalRev > 0 ? round($val / $totalRev * 100, 1) : 0;
                $kpiCB[$cb] = ['amount' => $val, 'pct' => $pct];
            }

            // Revenue by unit x cara_bayar
            $unitOrder    = ['Rajal','IGD','Ranap','IBS','ICU/PICU','Hemodialisa','Radiologi','Laboratorium','Farmasi','MCU','VK','Lainnya'];
            $unitCaseSql  = "CASE rm.instalasi_id
                WHEN 2  THEN 'Rajal'        WHEN 3  THEN 'IGD'
                WHEN 4  THEN 'Ranap'        WHEN 38 THEN 'Ranap'
                WHEN 5  THEN 'Laboratorium' WHEN 6  THEN 'Radiologi'
                WHEN 7  THEN 'IBS'          WHEN 81 THEN 'IBS'
                WHEN 9  THEN 'Farmasi'      WHEN 20 THEN 'ICU/PICU'
                WHEN 76 THEN 'ICU/PICU'     WHEN 73 THEN 'Hemodialisa'
                WHEN 72 THEN 'Fisioterapi'  WHEN 74 THEN 'Fisioterapi'
                WHEN 82 THEN 'MCU'          ELSE 'Lainnya'
            END";

            $revByUnitRaw = $db->createCommand("
                SELECT
                    {$unitCaseSql}   AS unit_bisnis,
                    {$cbCase}        AS cara_bayar,
                    SUM(COALESCE(tp.total_tarifakhir, 0)) AS total_rev
                FROM pendaftaran_t p
                JOIN ruangan_m rm ON rm.ruangan_id = p.ruangan_id
                LEFT JOIN carabayar_m cb ON cb.carabayar_id = p.carabayar_id
                LEFT JOIN tindakanpelayanan_t tp ON tp.pendaftaran_id = p.pendaftaran_id
                WHERE p.tgl_pendaftaran::date BETWEEN :df AND :dt
                  AND p.pasienbatalperiksa_id IS NULL
                GROUP BY unit_bisnis, cara_bayar
            ", [':df' => $date_from, ':dt' => $date_to])->queryAll();

            $revMatrix = [];
            foreach ($revByUnitRaw as $row) {
                $revMatrix[$row['unit_bisnis']][$row['cara_bayar']] = (float)$row['total_rev'];
            }

            $cbColors2      = ['BPJS Kesehatan'=>'#007BFF','Jasa Raharja'=>'#28A745','Asuransi Komersial'=>'#FFA500','Umum'=>'#6F42C1'];
            $stackedSeries  = [];
            foreach ($orderedCb as $cb) {
                $seriesData = [];
                foreach ($unitOrder as $unit) {
                    $seriesData[] = round(($revMatrix[$unit][$cb] ?? 0) / 1_000_000, 1);
                }
                $stackedSeries[] = ['name' => $cb, 'data' => $seriesData, 'color' => $cbColors2[$cb]];
            }

            $grandTotal = $totalRev ?: 1;
            $tableRows  = [];
            foreach ($unitOrder as $unit) {
                $bpjs  = (float)($revMatrix[$unit]['BPJS Kesehatan']     ?? 0);
                $jr    = (float)($revMatrix[$unit]['Jasa Raharja']        ?? 0);
                $asur  = (float)($revMatrix[$unit]['Asuransi Komersial']  ?? 0);
                $umum  = (float)($revMatrix[$unit]['Umum']                ?? 0);
                $unitTotal = $bpjs + $jr + $asur + $umum;
                if ($unitTotal <= 0) continue;
                $tableRows[] = [
                    'unit'      => $unit,
                    'bpjs'      => $bpjs,
                    'jr'        => $jr,
                    'asuransi'  => $asur,
                    'umum'      => $umum,
                    'total'     => $unitTotal,
                    'pct_total' => round($unitTotal / $grandTotal * 100, 1),
                ];
            }

            $pendapatanData = [
                'kpiCB'              => $kpiCB,
                'totalRev'           => $totalRev,
                'unitOrder'          => $unitOrder,
                'stackedSeries'      => $stackedSeries,
                'tableRows'          => $tableRows,
                'dataProviderRincian'=> new ArrayDataProvider(['allModels'=>$tableRows,'pagination'=>false]),
            ];
        }

        return $this->render('index', [
            'searchModel'           => $searchModel,
            'activeTab'             => $activeTab,
            // KPIs
            'rj_curr'               => $rj_curr,
            'rj_growth'             => $rj_growth,
            'rj_baru_pct'           => $rj_baru_pct,
            'igd_curr'              => $igd_curr,
            'igd_growth'            => $igd_growth,
            'ranap_curr'            => $ranap_curr,
            'ranap_growth'          => $ranap_growth,
            'bor_pct'               => $bor_pct,
            'labrad_curr'           => $labrad_curr,
            'labrad_growth'         => $labrad_growth,
            // Penjamin
            'rjPenjaminData'        => $rjPenjaminData,
            'rjPenjaminLabels'      => $rjPenjaminLabels,
            'rjPenjaminValues'      => $rjPenjaminValues,
            'rjTotalPenjamin'       => $rjTotalPenjamin,
            'kpiPenjamin'           => $kpiPenjamin,
            'kpiClosing'            => $kpiClosing,
            // Charts
            'trendRJ'               => $trendRJ,
            'trendIGD'              => $trendIGD,
            'trendRI'               => $trendRI,
            'trendPenunjang'        => $trendPenunjang,
            'heatmapSeries'         => $heatmapSeries,
            'donutLabels'           => $donutLabels,
            'donutValues'           => $donutValues,
            'dataProviderPenunjang' => $dataProviderPenunjang,
            'year'                  => $year,
            // Pendapatan tab
            'pendapatanData'        => $pendapatanData,
        ]);
    }
}
