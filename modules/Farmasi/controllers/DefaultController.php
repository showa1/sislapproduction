<?php

namespace app\modules\farmasi\controllers;

use app\controllers\BaseController;
use Yii;
use yii\web\Response;

class DefaultController extends BaseController
{
    /**
     * Dashboard utama - hanya load KPI summary (4 query ringan)
     * Data berat (chart, modal detail) di-load via AJAX
     */
    public function actionIndex()
    {
        $db      = Yii::$app->db;
        $bulanIni = date('m');
        $tahunIni = date('Y');
        $bulanLalu = date('m', strtotime('-1 month'));
        $tahunLalu = date('Y', strtotime('-1 month'));

        // --- KPI Utama: 4 query ringan dengan date() filter ---
        $kpiSql = "
            SELECT
                COUNT(DISTINCT pendaftaran_id)          AS resep_curr,
                SUM(qty_oa * hargasatuan_oa)            AS pendapatan_curr
            FROM obatalkespasien_t
            WHERE EXTRACT(MONTH FROM create_time) = :bulan
              AND EXTRACT(YEAR  FROM create_time) = :tahun
        ";
        $kpiCurr = $db->createCommand($kpiSql)
            ->bindValues([':bulan' => $bulanIni, ':tahun' => $tahunIni])
            ->queryOne();

        $kpiPrev = $db->createCommand($kpiSql)
            ->bindValues([':bulan' => $bulanLalu, ':tahun' => $tahunLalu])
            ->queryOne();

        $resep_curr      = (int)($kpiCurr['resep_curr'] ?? 0);
        $resep_prev      = (int)($kpiPrev['resep_curr'] ?? 0);
        $resep_growth    = $resep_prev > 0 ? round((($resep_curr - $resep_prev) / $resep_prev) * 100, 1) : 0;

        $pendapatan_curr   = (float)($kpiCurr['pendapatan_curr'] ?? 0);
        $pendapatan_prev   = (float)($kpiPrev['pendapatan_curr'] ?? 0);
        $pendapatan_growth = $pendapatan_prev > 0 ? round((($pendapatan_curr - $pendapatan_prev) / $pendapatan_prev) * 100, 1) : 0;

        // --- Alert counts: di-cache 5 menit agar tidak berat setiap reload ---
        $cacheKey = 'farmasi_alert_counts';
        $alertData = Yii::$app->cache->get($cacheKey);

        if ($alertData === false) {
            // Stok minimal & stock-out dalam satu query CTE
            $alertSql = "
                WITH stok_saat_ini AS (
                    SELECT om.obatalkes_id,
                           COALESCE(SUM(st.qtystok_in  - st.qtystok_out), 0) AS stok
                    FROM obatalkes_m om
                    LEFT JOIN stokobatalkes_t st ON st.obatalkes_id = om.obatalkes_id
                    WHERE om.obatalkes_aktif = true
                    GROUP BY om.obatalkes_id
                )
                SELECT
                    (SELECT COUNT(*)
                     FROM stok_saat_ini s
                     JOIN stokminimal_t stm ON stm.obatalkes_id = s.obatalkes_id
                     WHERE s.stok <= stm.jmlminimalstok
                    ) AS stok_min_alert,

                    (SELECT COUNT(*) FROM stok_saat_ini WHERE stok = 0)
                        AS stockout_alert,

                    (SELECT COUNT(*) FROM obatalkes_m
                     WHERE obatalkes_aktif = true
                       AND tglkadaluarsa IS NOT NULL
                       AND tglkadaluarsa > '2000-01-01'
                       AND tglkadaluarsa <= NOW() + INTERVAL '90 days'
                    ) AS expired_alert,

                    (SELECT COUNT(DISTINCT st2.obatalkes_id)
                     FROM stokobatalkes_t st2
                     WHERE st2.obatalkes_id NOT IN (
                         SELECT DISTINCT obatalkes_id FROM stokobatalkes_t
                         WHERE create_time >= NOW() - INTERVAL '180 days'
                     )
                    ) AS deadstock
            ";

            $alertData = $db->createCommand($alertSql)->queryOne();
            Yii::$app->cache->set($cacheKey, $alertData, 300); // cache 5 menit
        }

        return $this->render('index', [
            'resep_curr'        => $resep_curr,
            'resep_growth'      => $resep_growth,
            'pendapatan_curr'   => $pendapatan_curr,
            'pendapatan_growth' => $pendapatan_growth,
            'stok_min_alert'    => (int)($alertData['stok_min_alert'] ?? 0),
            'expired_alert'     => (int)($alertData['expired_alert']  ?? 0),
            'stockout_alert'    => (int)($alertData['stockout_alert'] ?? 0),
            'deadstock'         => (int)($alertData['deadstock']       ?? 0),
            'tahunIni'          => $tahunIni,
            'bulanIni'          => $bulanIni,
        ]);
    }

    /**
     * AJAX: Statistik berat (nilai aset, TOR, pending PO, lead time, distribusi kategori)
     */
    public function actionAjaxStats()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $db = Yii::$app->db;

        $cacheKey = 'farmasi_ajax_stats';
        $data = Yii::$app->cache->get($cacheKey);

        if ($data === false) {
            // Nilai Aset
            $nilaiAset = $db->createCommand("
                SELECT COALESCE(SUM(sub.stok * om.harganetto), 0)
                FROM (
                    SELECT obatalkes_id, SUM(qtystok_in - qtystok_out) AS stok
                    FROM stokobatalkes_t GROUP BY obatalkes_id
                ) sub
                JOIN obatalkes_m om ON om.obatalkes_id = sub.obatalkes_id
                WHERE om.obatalkes_aktif = true AND sub.stok > 0
            ")->queryScalar();

            // TOR
            $totalKeluar = $db->createCommand("
                SELECT COALESCE(SUM(qtystok_out), 0)
                FROM stokobatalkes_t
                WHERE create_time >= NOW() - INTERVAL '12 months'
            ")->queryScalar();

            $rataStok = $db->createCommand("
                SELECT COALESCE(AVG(stok), 0) FROM (
                    SELECT obatalkes_id, SUM(qtystok_in - qtystok_out) AS stok
                    FROM stokobatalkes_t GROUP BY obatalkes_id
                    HAVING SUM(qtystok_in - qtystok_out) > 0
                ) sub
            ")->queryScalar();

            $tor = $rataStok > 0 ? round($totalKeluar / $rataStok, 1) : 0;

            // Pending PO & Lead Time digabung
            $poData = $db->createCommand("
                SELECT
                    (SELECT COUNT(*) FROM permintaanpembelian_t
                     WHERE batalpermintaanpembelian_id IS NULL
                       AND penerimaanbarang_id IS NULL) AS pending_po,
                    (SELECT ROUND(AVG(EXTRACT(DAY FROM (pb.tglterima - pp.tglpermintaanpembelian)))::numeric, 1)
                     FROM permintaanpembelian_t pp
                     JOIN penerimaanbarang_t pb ON pb.penerimaanbarang_id = pp.penerimaanbarang_id
                     WHERE pp.tglpermintaanpembelian >= NOW() - INTERVAL '1 year') AS lead_time
            ")->queryOne();

            // Distribusi kategori
            $distribusi = $db->createCommand("
                SELECT COALESCE(obatalkes_kategori, 'Lainnya') AS kategori, COUNT(*) as jumlah
                FROM obatalkes_m WHERE obatalkes_aktif = true
                GROUP BY obatalkes_kategori ORDER BY jumlah DESC LIMIT 8
            ")->queryAll();

            $data = [
                'nilaiAset'  => (float)$nilaiAset,
                'tor'        => $tor,
                'pendingPO'  => (int)($poData['pending_po'] ?? 0),
                'leadTime'   => (float)($poData['lead_time'] ?? 0),
                'distribusi' => $distribusi,
            ];

            Yii::$app->cache->set($cacheKey, $data, 300);
        }

        return $data;
    }

    /**
     * AJAX: Trend harian + Top 10 obat (untuk chart)
     */
    public function actionAjaxCharts()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $db      = Yii::$app->db;
        $bulanIni = date('m');
        $tahunIni = date('Y');

        $cacheKey = 'farmasi_ajax_charts_' . $tahunIni . '_' . $bulanIni;
        $data = Yii::$app->cache->get($cacheKey);

        if ($data === false) {
            // Trend harian
            $trendRaw = $db->createCommand("
                SELECT EXTRACT(DAY FROM create_time) AS hari,
                       COUNT(DISTINCT pendaftaran_id) AS total_resep
                FROM obatalkespasien_t
                WHERE EXTRACT(MONTH FROM create_time) = :bulan
                  AND EXTRACT(YEAR  FROM create_time) = :tahun
                GROUP BY EXTRACT(DAY FROM create_time)
                ORDER BY hari
            ")->bindValues([':bulan' => $bulanIni, ':tahun' => $tahunIni])->queryAll();

            $hariDalamBulan = (int)date('t');
            $trendDates = range(1, $hariDalamBulan);
            $trendMap   = array_fill(1, $hariDalamBulan, 0);
            foreach ($trendRaw as $t) {
                $trendMap[(int)$t['hari']] = (int)$t['total_resep'];
            }

            // Top 10 fast-moving
            $topObat = $db->createCommand("
                SELECT om.obatalkes_nama AS nama, SUM(opt.qty_oa) AS total
                FROM obatalkespasien_t opt
                JOIN obatalkes_m om ON om.obatalkes_id = opt.obatalkes_id
                WHERE EXTRACT(MONTH FROM opt.create_time) = :bulan
                  AND EXTRACT(YEAR  FROM opt.create_time) = :tahun
                GROUP BY om.obatalkes_id, om.obatalkes_nama
                ORDER BY total DESC LIMIT 10
            ")->bindValues([':bulan' => $bulanIni, ':tahun' => $tahunIni])->queryAll();

            $data = [
                'trendDates' => $trendDates,
                'trendData'  => array_values($trendMap),
                'topObat'    => $topObat,
            ];

            Yii::$app->cache->set($cacheKey, $data, 180); // cache 3 menit
        }

        return $data;
    }

    /**
     * AJAX: Activity log 10 transaksi terakhir
     */
    public function actionAjaxActivity()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $db = Yii::$app->db;

        $log = $db->createCommand("
            SELECT st.create_time, om.obatalkes_nama, st.qtystok_in, st.qtystok_out
            FROM stokobatalkes_t st
            JOIN obatalkes_m om ON om.obatalkes_id = st.obatalkes_id
            ORDER BY st.create_time DESC LIMIT 10
        ")->queryAll();

        return $log;
    }

    /**
     * AJAX: Detail modal (stok menipis / expired / deadstock / stockout)
     */
    public function actionAjaxModal()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $db   = Yii::$app->db;
        $type = Yii::$app->request->get('type', '');

        switch ($type) {
            case 'stok_menipis':
                return $db->createCommand("
                    SELECT om.obatalkes_nama, om.obatalkes_kategori,
                           SUM(COALESCE(st.qtystok_in,0) - COALESCE(st.qtystok_out,0)) AS stok,
                           stm.jmlminimalstok
                    FROM obatalkes_m om
                    JOIN stokminimal_t stm ON stm.obatalkes_id = om.obatalkes_id
                    LEFT JOIN stokobatalkes_t st ON om.obatalkes_id = st.obatalkes_id
                        AND st.ruangan_id = stm.ruangan_id
                    WHERE om.obatalkes_aktif = true
                    GROUP BY om.obatalkes_nama, om.obatalkes_kategori, stm.jmlminimalstok
                    HAVING SUM(COALESCE(st.qtystok_in,0) - COALESCE(st.qtystok_out,0)) <= stm.jmlminimalstok
                    ORDER BY stok ASC LIMIT 20
                ")->queryAll();

            case 'expired':
                return $db->createCommand("
                    SELECT obatalkes_nama, obatalkes_kategori, tglkadaluarsa,
                           EXTRACT(DAY FROM (tglkadaluarsa - NOW())) AS sisa_hari
                    FROM obatalkes_m
                    WHERE obatalkes_aktif = true
                      AND tglkadaluarsa IS NOT NULL
                      AND tglkadaluarsa > '2000-01-01'
                      AND tglkadaluarsa <= NOW() + INTERVAL '90 days'
                    ORDER BY tglkadaluarsa ASC LIMIT 20
                ")->queryAll();

            case 'deadstock':
                return $db->createCommand("
                    SELECT om.obatalkes_nama, om.obatalkes_kategori,
                           SUM(st.qtystok_in - st.qtystok_out) AS stok,
                           MAX(st.create_time) AS last_trx,
                           ROUND(EXTRACT(DAY FROM (NOW() - MAX(st.create_time)))) AS hari_tidak_bergerak
                    FROM stokobatalkes_t st
                    JOIN obatalkes_m om ON om.obatalkes_id = st.obatalkes_id
                    WHERE st.obatalkes_id NOT IN (
                        SELECT DISTINCT obatalkes_id FROM stokobatalkes_t
                        WHERE create_time >= NOW() - INTERVAL '180 days'
                    )
                    GROUP BY om.obatalkes_id, om.obatalkes_nama, om.obatalkes_kategori
                    ORDER BY last_trx ASC LIMIT 50
                ")->queryAll();

            case 'stockout':
                return $db->createCommand("
                    SELECT om.obatalkes_nama, om.obatalkes_kategori,
                           COALESCE(SUM(st.qtystok_in - st.qtystok_out), 0) AS stok,
                           MAX(st.create_time) AS last_trx
                    FROM obatalkes_m om
                    LEFT JOIN stokobatalkes_t st ON st.obatalkes_id = om.obatalkes_id
                    WHERE om.obatalkes_aktif = true
                    GROUP BY om.obatalkes_id, om.obatalkes_nama, om.obatalkes_kategori
                    HAVING COALESCE(SUM(st.qtystok_in - st.qtystok_out), 0) = 0
                    ORDER BY last_trx DESC LIMIT 50
                ")->queryAll();

            default:
                return ['error' => 'Unknown type'];
        }
    }
}