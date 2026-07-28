<?php

namespace app\modules\RawatInap\controllers;

use app\controllers\BaseController;
use Yii;

class DashboardController extends BaseController
{
    public function actionIndex()
    {
        $year = Yii::$app->request->get('year', date('Y'));
        $month = Yii::$app->request->get('month', ($year == date('Y')) ? date('n') : 12);
        $caraBayar = Yii::$app->request->get('cara_bayar', 'Semua');
        $ruangan = Yii::$app->request->get('ruangan', []);
        $ruangan_m = Yii::$app->request->get('ruangan_m', []);

        // Month to act as "Bulan Ini"
        $currentMonth = (int) $month;

        // Base query conditions
        $joinCaraBayar = "";
        $whereCaraBayar = "";
        $params = [':year' => $year];

        if ($caraBayar !== 'Semua') {
            $joinCaraBayar = "JOIN carabayar_m cm ON cm.carabayar_id = pat.carabayar_id";
            $whereCaraBayar = "AND cm.carabayar_nama = :cb";
            $params[':cb'] = $caraBayar;
        }

        $joinRuangan = "";
        $whereRuangan = "";
        
        $ruangan_selected = (array)$ruangan;
        $filter_instalasi = !empty($ruangan_selected) && !in_array('Semua', $ruangan_selected);
        
        $ruangan_m_selected = (array)$ruangan_m;
        $filter_ruangan = !empty($ruangan_m_selected) && !in_array('Semua', $ruangan_m_selected);

        if ($filter_instalasi || $filter_ruangan) {
            $joinRuangan = "JOIN ruangan_m rm ON rm.ruangan_id = pat.ruangan_id ";
            
            if ($filter_instalasi) {
                $joinRuangan .= "JOIN instalasi_m im ON im.instalasi_id = rm.instalasi_id ";
                $placeholders = [];
                foreach ($ruangan_selected as $index => $r_val) {
                    $p = ':inst' . $index;
                    $placeholders[] = $p;
                    $params[$p] = $r_val;
                }
                $whereRuangan .= "AND im.instalasi_nama IN (" . implode(',', $placeholders) . ") ";
            }

            if ($filter_ruangan) {
                $placeholders = [];
                foreach ($ruangan_m_selected as $index => $r_val) {
                    $p = ':rm' . $index;
                    $placeholders[] = $p;
                    $params[$p] = $r_val;
                }
                $whereRuangan .= "AND rm.ruangan_nama IN (" . implode(',', $placeholders) . ") ";
            }
        }

        // 1. Trend Bulanan (Jan - Des) - Tetap dalam 1 tahun
        $sqlTrend = "
            SELECT 
                CAST(EXTRACT(MONTH FROM pat.tglpulang) AS INTEGER) as bulan,
                COUNT(DISTINCT pat.pasienadmisi_id) as jumlah_pasien,
                SUM(EXTRACT(EPOCH FROM (pat.tglpulang - pat.tgladmisi)) / 3600) as total_jam
            FROM pasienadmisi_t pat
            JOIN pendaftaran_t pt ON pt.pendaftaran_id = pat.pendaftaran_id
            $joinCaraBayar
            $joinRuangan
            WHERE pat.tglpulang IS NOT NULL 
              AND pt.pasienbatalperiksa_id IS NULL
              AND EXTRACT(YEAR FROM pat.tglpulang) = :year
              $whereCaraBayar
              $whereRuangan
            GROUP BY EXTRACT(MONTH FROM pat.tglpulang)
            ORDER BY bulan ASC
        ";
        $dataTrend = Yii::$app->db->createCommand($sqlTrend, $params)->queryAll();

        // 2. Breakdown Cara Bayar (Berdasarkan Bulan yang dipilih)
        $sqlBreakdown = "
            SELECT 
                cm.carabayar_nama,
                COUNT(DISTINCT pat.pasienadmisi_id) as jumlah_pasien,
                SUM(EXTRACT(EPOCH FROM (pat.tglpulang - pat.tgladmisi)) / 3600) as total_jam
            FROM pasienadmisi_t pat
            JOIN pendaftaran_t pt ON pt.pendaftaran_id = pat.pendaftaran_id
            JOIN carabayar_m cm ON cm.carabayar_id = pat.carabayar_id
            $joinRuangan
            WHERE pat.tglpulang IS NOT NULL 
              AND pt.pasienbatalperiksa_id IS NULL
              AND EXTRACT(YEAR FROM pat.tglpulang) = :year
              AND EXTRACT(MONTH FROM pat.tglpulang) = :month
              $whereRuangan
            GROUP BY cm.carabayar_nama
            ORDER BY jumlah_pasien DESC
        ";
        // Do not use $params here directly because it might contain :cb which is not used in this query
        $paramBd = [':year' => $year, ':month' => $currentMonth];
        if ($filter_instalasi) {
            foreach ($ruangan_selected as $index => $r_val) {
                $paramBd[':inst' . $index] = $r_val;
            }
        }
        if ($filter_ruangan) {
            foreach ($ruangan_m_selected as $index => $r_val) {
                $paramBd[':rm' . $index] = $r_val;
            }
        }
        $dataBreakdown = Yii::$app->db->createCommand($sqlBreakdown, $paramBd)->queryAll();

        // Rumus LOS
        $trendByMonth = array_fill(1, 12, ['jumlah_pasien' => 0, 'total_jam' => 0, 'rata_rata_los' => 0]);
        foreach ($dataTrend as $row) {
            $b = $row['bulan'];
            $trendByMonth[$b]['jumlah_pasien'] = $row['jumlah_pasien'];
            $trendByMonth[$b]['total_jam'] = $row['total_jam'];
            $trendByMonth[$b]['rata_rata_los'] = $row['jumlah_pasien'] > 0 ? ($row['total_jam'] / $row['jumlah_pasien']) : 0;
        }

        // Current vs Previous Month
        $prevMonth = $currentMonth - 1;

        $currData = $trendByMonth[$currentMonth] ?? ['jumlah_pasien' => 0, 'total_jam' => 0, 'rata_rata_los' => 0];
        $prevData = $prevMonth > 0 ? ($trendByMonth[$prevMonth] ?? ['jumlah_pasien' => 0, 'total_jam' => 0, 'rata_rata_los' => 0]) : ['jumlah_pasien' => 0, 'total_jam' => 0, 'rata_rata_los' => 0];

        // Sparkline 6 months (ending in currentMonth)
        $sparklineData = [];
        $sparklineLabels = [];
        $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

        for ($i = $currentMonth - 5; $i <= $currentMonth; $i++) {
            $m = $i;
            if ($m < 1) {
                $prevM = $m + 12;
                $sparklineData[] = 0; // Simplified for previous year handling
                $sparklineLabels[] = $monthNames[$prevM - 1];
            } else {
                $sparklineData[] = $trendByMonth[$m]['jumlah_pasien'];
                $sparklineLabels[] = $monthNames[$m - 1];
            }
        }

        // Opt dropdowns
        $optCaraBayar = Yii::$app->db->createCommand("SELECT DISTINCT carabayar_nama FROM carabayar_m ORDER BY carabayar_nama")->queryColumn();
        $optRuangan = ['Rawat Inap', 'Perawatan Intensif'];
        
        $sqlOptRm = "SELECT DISTINCT rm.ruangan_nama FROM ruangan_m rm ";
        $paramOptRm = [];
        if ($filter_instalasi) {
            $sqlOptRm .= "JOIN instalasi_m im ON im.instalasi_id = rm.instalasi_id WHERE im.instalasi_nama IN (";
            $phs = [];
            foreach ($ruangan_selected as $index => $r_val) {
                $p = ':i' . $index;
                $phs[] = $p;
                $paramOptRm[$p] = $r_val;
            }
            $sqlOptRm .= implode(',', $phs) . ") ";
        }
        $sqlOptRm .= "ORDER BY rm.ruangan_nama";
        $optRuanganM = Yii::$app->db->createCommand($sqlOptRm, $paramOptRm)->queryColumn();

        $rawRooms = Yii::$app->db->createCommand("
            SELECT DISTINCT rm.ruangan_nama, im.instalasi_nama 
            FROM ruangan_m rm 
            JOIN instalasi_m im ON im.instalasi_id = rm.instalasi_id 
            ORDER BY rm.ruangan_nama
        ")->queryAll();

        return $this->render('index', [
            'year' => $year,
            'caraBayar' => $caraBayar,
            'ruangan' => $ruangan,
            'ruangan_m' => $ruangan_m,
            'optCaraBayar' => $optCaraBayar,
            'optRuangan' => $optRuangan,
            'optRuanganM' => $optRuanganM,
            'rawRooms' => $rawRooms,
            'currentMonth' => $currentMonth,
            'trendByMonth' => $trendByMonth,
            'currData' => $currData,
            'prevData' => $prevData,
            'sparklineData' => $sparklineData,
            'sparklineLabels' => $sparklineLabels,
            'dataBreakdown' => $dataBreakdown
        ]);
    }
}
