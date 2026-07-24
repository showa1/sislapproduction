<?php

namespace app\modules\Pendaftaran\controllers;

use Yii;
use app\controllers\BaseController;

class DashboardController extends BaseController
{
    public function actionIndex()
    {
        $request = Yii::$app->request;
        $dateFrom = $request->get('date_from', date('Y-m-01'));
        $dateTo = $request->get('date_to', date('Y-m-t'));

        $db = Yii::$app->db;
        $params = [':df' => $dateFrom, ':dt' => $dateTo];

        // 1. Top Summary Cards (KPI Metrics)
        $sqlKpi = "
            SELECT 
                COUNT(pt.pendaftaran_id) as total_pendaftaran,
                SUM(CASE WHEN bt.is_jkn = true THEN 1 ELSE 0 END) as total_mjkn,
                SUM(CASE WHEN bt.is_jkn IS NULL OR bt.is_jkn = false THEN 1 ELSE 0 END) as total_onsite,
                SUM(CASE WHEN bt.is_jkn = true AND bt.is_checkin = true THEN 1 ELSE 0 END) as total_checkin_mjkn
            FROM pendaftaran_t pt
            LEFT JOIN buatjanjipoli_t bt ON bt.pendaftaran_id = pt.pendaftaran_id
            WHERE pt.ruangan_id IN (
                SELECT ruangan_id FROM ruangan_m WHERE instalasi_id = '2'
            )
            AND DATE(pt.tgl_pendaftaran) BETWEEN :df AND :dt
            AND pt.pasienbatalperiksa_id IS NULL
        ";
        
        $kpiData = $db->createCommand($sqlKpi, $params)->queryOne();

        $totalPendaftaran = (int)($kpiData['total_pendaftaran'] ?? 0);
        $totalMjkn = (int)($kpiData['total_mjkn'] ?? 0);
        $totalOnsite = (int)($kpiData['total_onsite'] ?? 0);
        $totalCheckinMjkn = (int)($kpiData['total_checkin_mjkn'] ?? 0);

        $pctMjkn = $totalPendaftaran > 0 ? ($totalMjkn / $totalPendaftaran) * 100 : 0;
        $pctOnsite = $totalPendaftaran > 0 ? ($totalOnsite / $totalPendaftaran) * 100 : 0;
        $pctCheckin = $totalMjkn > 0 ? ($totalCheckinMjkn / $totalMjkn) * 100 : 0;

        // 2. Daily Trend Line Chart: MJKN vs Onsite Harian
        $sqlTrend = "
            SELECT 
                DATE(pt.tgl_pendaftaran) as tanggal,
                SUM(CASE WHEN bt.is_jkn = true THEN 1 ELSE 0 END) as mjkn,
                SUM(CASE WHEN bt.is_jkn IS NULL OR bt.is_jkn = false THEN 1 ELSE 0 END) as onsite
            FROM pendaftaran_t pt
            LEFT JOIN buatjanjipoli_t bt ON bt.pendaftaran_id = pt.pendaftaran_id
            WHERE pt.ruangan_id IN (
                SELECT ruangan_id FROM ruangan_m WHERE instalasi_id = '2'
            )
            AND DATE(pt.tgl_pendaftaran) BETWEEN :df AND :dt
            AND pt.pasienbatalperiksa_id IS NULL
            GROUP BY DATE(pt.tgl_pendaftaran)
            ORDER BY tanggal ASC
        ";
        
        $trendData = $db->createCommand($sqlTrend, $params)->queryAll();
        
        $chartLabels = [];
        $chartDataMjkn = [];
        $chartDataOnsite = [];

        foreach ($trendData as $row) {
            $chartLabels[] = date('d M', strtotime($row['tanggal']));
            $chartDataMjkn[] = (int)$row['mjkn'];
            $chartDataOnsite[] = (int)$row['onsite'];
        }

        // 3. Bar Chart: Distribusi Cara Daftar per Poliklinik / Ruangan
        $sqlPoli = "
            SELECT 
                rm.ruangan_nama,
                SUM(CASE WHEN bt.is_jkn = true THEN 1 ELSE 0 END) as mjkn,
                SUM(CASE WHEN bt.is_jkn IS NULL OR bt.is_jkn = false THEN 1 ELSE 0 END) as onsite
            FROM pendaftaran_t pt
            LEFT JOIN buatjanjipoli_t bt ON bt.pendaftaran_id = pt.pendaftaran_id
            JOIN ruangan_m rm ON rm.ruangan_id = pt.ruangan_id
            WHERE pt.ruangan_id IN (
                SELECT ruangan_id FROM ruangan_m WHERE instalasi_id = '2'
            )
            AND DATE(pt.tgl_pendaftaran) BETWEEN :df AND :dt
            AND pt.pasienbatalperiksa_id IS NULL
            GROUP BY rm.ruangan_nama
            ORDER BY (SUM(CASE WHEN bt.is_jkn = true THEN 1 ELSE 0 END) + SUM(CASE WHEN bt.is_jkn IS NULL OR bt.is_jkn = false THEN 1 ELSE 0 END)) DESC
        ";
        
        $poliData = $db->createCommand($sqlPoli, $params)->queryAll();

        $poliLabels = [];
        $poliDataMjkn = [];
        $poliDataOnsite = [];

        foreach ($poliData as $row) {
            $poliLabels[] = $row['ruangan_nama'];
            $poliDataMjkn[] = (int)$row['mjkn'];
            $poliDataOnsite[] = (int)$row['onsite'];
        }

        return $this->render('index', [
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'totalPendaftaran' => $totalPendaftaran,
            'totalMjkn' => $totalMjkn,
            'totalOnsite' => $totalOnsite,
            'totalCheckinMjkn' => $totalCheckinMjkn,
            'pctMjkn' => $pctMjkn,
            'pctOnsite' => $pctOnsite,
            'pctCheckin' => $pctCheckin,
            'chartLabels' => json_encode($chartLabels),
            'chartDataMjkn' => json_encode($chartDataMjkn),
            'chartDataOnsite' => json_encode($chartDataOnsite),
            'poliLabels' => json_encode($poliLabels),
            'poliDataMjkn' => json_encode($poliDataMjkn),
            'poliDataOnsite' => json_encode($poliDataOnsite),
        ]);
    }
}
