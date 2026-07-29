<?php

namespace app\modules\keuangan\controllers;

use app\controllers\BaseController;
use app\modules\eksekutif\models\DashboardSearch;
use yii\data\ArrayDataProvider;
use yii\filters\VerbFilter;
use yii\web\Response;
use Yii;
use DateTime;

class DashboardPendapatanController extends BaseController
{
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

    private function cbCase(): string
    {
        return "CASE
            WHEN UPPER(cb.carabayar_nama) LIKE '%BPJS KES%'  THEN 'BPJS Kesehatan'
            WHEN UPPER(cb.carabayar_nama) LIKE '%JASA RAH%'  THEN 'Jasa Raharja'
            WHEN UPPER(cb.carabayar_nama) LIKE '%ASURANSI%'  THEN 'Asuransi Komersial'
            ELSE 'Umum'
        END";
    }

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

    public function actionIndex()
    {
        $searchModel = new DashboardSearch();
        $params      = Yii::$app->request->queryParams;
        $db          = Yii::$app->db;
        $cbCase      = $this->cbCase();

        if (!isset($params['DashboardSearch']['date_from']) && !isset($params['DashboardSearch']['date_to'])) {
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
        
        $orderedCb = ['BPJS Kesehatan', 'Jasa Raharja', 'Asuransi Komersial', 'Umum'];
        
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

        return $this->render('index', [
            'searchModel'    => $searchModel,
            'pendapatanData' => $pendapatanData,
        ]);
    }
}
