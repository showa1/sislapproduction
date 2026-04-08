<?php

namespace app\modules\rawatjalan\controllers;

use app\controllers\BaseController;
use Yii;
use DateTime;

class DashboardController extends BaseController
{
    /**
     * Dashboard Rawat Jalan — Ringkasan bulan berjalan.
     */
    public function actionIndex()
    {
        // Default: bulan berjalan
        $dateFrom = Yii::$app->request->get('date_from');
        $dateTo = Yii::$app->request->get('date_to');

        if (empty($dateFrom)) {
            $dateFrom = date('Y-m-01');
        } else {
            // Convert if format is d-m-Y
            if (strpos($dateFrom, '-') === 2) {
                $dateFrom = DateTime::createFromFormat('d-m-Y', $dateFrom)->format('Y-m-d');
            }
        }

        if (empty($dateTo)) {
            $dateTo = date('Y-m-t');
        } else {
            // Convert if format is d-m-Y
            if (strpos($dateTo, '-') === 2) {
                $dateTo = DateTime::createFromFormat('d-m-Y', $dateTo)->format('Y-m-d');
            }
        }

        $db = Yii::$app->db;

        // ── 1. Stat Cards ──────────────────────────────────────────────────
        $statsRow = $db->createCommand("
            SELECT
                COUNT(DISTINCT p.pendaftaran_id) AS total_pasien,
                COALESCE(
                    ROUND((AVG(EXTRACT(EPOCH FROM (
                        (SELECT MAX(wt2.waktutunggu_rs) FROM waktutunggupelayanan_t wt2
                         WHERE wt2.pendaftaran_id = p.pendaftaran_id AND wt2.task_id = 7)
                        -
                        (SELECT MAX(wt3.waktutunggu_rs) FROM waktutunggupelayanan_t wt3
                         WHERE wt3.pendaftaran_id = p.pendaftaran_id AND wt3.task_id = 3)
                    ))) / 60)::numeric, 1), 0
                ) AS avg_responstime_menit
            FROM pendaftaran_t p
            JOIN ruangan_m rm ON rm.ruangan_id = p.ruangan_id
            WHERE p.tgl_pendaftaran::date BETWEEN :df AND :dt
              AND p.pasienbatalperiksa_id IS NULL
              AND rm.instalasi_id = 2
        ", [':df' => $dateFrom, ':dt' => $dateTo])->queryOne();

        // Poli teraktif
        $politeraktif = $db->createCommand("
            SELECT rm.ruangan_nama, COUNT(p.pendaftaran_id) AS jml
            FROM pendaftaran_t p
            JOIN ruangan_m rm ON rm.ruangan_id = p.ruangan_id
            WHERE p.tgl_pendaftaran::date BETWEEN :df AND :dt
              AND p.pasienbatalperiksa_id IS NULL
              AND rm.instalasi_id = 2
            GROUP BY rm.ruangan_nama
            ORDER BY jml DESC
            LIMIT 1
        ", [':df' => $dateFrom, ':dt' => $dateTo])->queryOne();

        // ── 2. Line Chart: Tren kunjungan harian ───────────────────────────
        $trendData = $db->createCommand("
            SELECT
                TO_CHAR(p.tgl_pendaftaran::date, 'DD Mon') AS tgl_label,
                p.tgl_pendaftaran::date AS tgl_sort,
                COUNT(p.pendaftaran_id) AS jml_pasien
            FROM pendaftaran_t p
            JOIN ruangan_m rm ON rm.ruangan_id = p.ruangan_id
            WHERE p.tgl_pendaftaran::date BETWEEN :df AND :dt
              AND p.pasienbatalperiksa_id IS NULL
              AND rm.instalasi_id = 2
            GROUP BY p.tgl_pendaftaran::date
            ORDER BY tgl_sort ASC
        ", [':df' => $dateFrom, ':dt' => $dateTo])->queryAll();

        // ── 3. Bar Chart: Avg waktu pelayanan (task 4→5) per poli ──────────
        $barData = $db->createCommand("
            WITH pelayanan AS (
                SELECT
                    p.pendaftaran_id,
                    rm.ruangan_nama,
                    MAX(wt.waktutunggu_rs) FILTER (WHERE wt.task_id = 4) AS t4,
                    MAX(wt.waktutunggu_rs) FILTER (WHERE wt.task_id = 5) AS t5
                FROM pendaftaran_t p
                JOIN ruangan_m rm ON rm.ruangan_id = p.ruangan_id
                JOIN waktutunggupelayanan_t wt ON wt.pendaftaran_id = p.pendaftaran_id
                WHERE p.tgl_pendaftaran::date BETWEEN :df AND :dt
                  AND p.pasienbatalperiksa_id IS NULL
                  AND rm.instalasi_id = 2
                GROUP BY p.pendaftaran_id, rm.ruangan_nama
            )
            SELECT
                ruangan_nama,
                ROUND((AVG(EXTRACT(EPOCH FROM (t5 - t4))) / 60)::numeric, 1) AS avg_menit
            FROM pelayanan
            WHERE t4 IS NOT NULL AND t5 IS NOT NULL
            GROUP BY ruangan_nama
            ORDER BY avg_menit DESC
            LIMIT 10
        ", [':df' => $dateFrom, ':dt' => $dateTo])->queryAll();

        // ── 4. Tabel Responstime Summary ───────────────────────────────────
        $rtSummary = $db->createCommand("
            WITH data AS (
                SELECT
                    p.pendaftaran_id,
                    MAX(wt.waktutunggu_rs) FILTER (WHERE wt.task_id = 3) AS t3,
                    MAX(wt.waktutunggu_rs) FILTER (WHERE wt.task_id = 4) AS t4,
                    MAX(wt.waktutunggu_rs) FILTER (WHERE wt.task_id = 5) AS t5,
                    MAX(wt.waktutunggu_rs) FILTER (WHERE wt.task_id = 6) AS t6,
                    MAX(wt.waktutunggu_rs) FILTER (WHERE wt.task_id = 7) AS t7
                FROM pendaftaran_t p
                JOIN ruangan_m rm ON rm.ruangan_id = p.ruangan_id
                JOIN waktutunggupelayanan_t wt ON wt.pendaftaran_id = p.pendaftaran_id
                WHERE p.tgl_pendaftaran::date BETWEEN :df AND :dt
                  AND p.pasienbatalperiksa_id IS NULL
                  AND rm.instalasi_id = 2
                GROUP BY p.pendaftaran_id
            )
            SELECT
                ROUND((AVG(EXTRACT(EPOCH FROM (t4-t3)))/60)::numeric, 1) AS avg_3_4,
                ROUND((AVG(EXTRACT(EPOCH FROM (t5-t4)))/60)::numeric, 1) AS avg_4_5,
                ROUND((AVG(EXTRACT(EPOCH FROM (t6-t5)))/60)::numeric, 1) AS avg_5_6,
                ROUND((AVG(EXTRACT(EPOCH FROM (t7-t6)))/60)::numeric, 1) AS avg_6_7,
                ROUND((AVG(EXTRACT(EPOCH FROM (t7-t3)))/60)::numeric, 1) AS avg_total
            FROM data
            WHERE t3 IS NOT NULL AND t4 IS NOT NULL AND t5 IS NOT NULL
              AND t6 IS NOT NULL AND t7 IS NOT NULL
        ", [':df' => $dateFrom, ':dt' => $dateTo])->queryOne();

        // ── 5. Top 5 Dokter responstime terbaik ────────────────────────────
        $top5 = $db->createCommand("
            WITH data AS (
                SELECT
                    pg.nama_pegawai,
                    COALESCE(pg.gelardepan, '') AS gelardepan,
                    MAX(wt.waktutunggu_rs) FILTER (WHERE wt.task_id = 3) AS t3,
                    MAX(wt.waktutunggu_rs) FILTER (WHERE wt.task_id = 7) AS t7
                FROM pendaftaran_t p
                JOIN ruangan_m rm ON rm.ruangan_id = p.ruangan_id
                JOIN pegawai_m pg ON pg.pegawai_id = p.pegawai_id
                JOIN waktutunggupelayanan_t wt ON wt.pendaftaran_id = p.pendaftaran_id
                WHERE p.tgl_pendaftaran::date BETWEEN :df AND :dt
                  AND p.pasienbatalperiksa_id IS NULL
                  AND rm.instalasi_id = 2
                GROUP BY p.pendaftaran_id, pg.nama_pegawai, pg.gelardepan
            )
            SELECT
                gelardepan || ' ' || nama_pegawai AS nama_dokter,
                ROUND((AVG(EXTRACT(EPOCH FROM (t7-t3)))/60)::numeric, 1) AS avg_total_menit,
                COUNT(*) AS jml_pasien
            FROM data
            WHERE t3 IS NOT NULL AND t7 IS NOT NULL
            GROUP BY nama_pegawai, gelardepan
            ORDER BY avg_total_menit ASC
            LIMIT 5
        ", [':df' => $dateFrom, ':dt' => $dateTo])->queryAll();

        // ── 6. Repeat Patient Metrics (Loyalty) ──────────────────────────
        $repeatStats = $db->createCommand("
            SELECT 
                COUNT(*) as count_loyal,
                (SELECT COUNT(DISTINCT p2.pasien_id) 
                 FROM pendaftaran_t p2 
                 JOIN ruangan_m rm2 ON rm2.ruangan_id = p2.ruangan_id
                 WHERE p2.tgl_pendaftaran::date BETWEEN :df AND :dt
                   AND p2.pasienbatalperiksa_id IS NULL
                   AND rm2.instalasi_id = 2) as total_pasien_unik
            FROM (
                SELECT p.pasien_id
                FROM pendaftaran_t p
                JOIN ruangan_m rm ON rm.ruangan_id = p.ruangan_id
                WHERE p.tgl_pendaftaran::date BETWEEN :df AND :dt
                  AND p.pasienbatalperiksa_id IS NULL
                  AND rm.instalasi_id = 2
                GROUP BY p.pasien_id
                HAVING COUNT(p.pendaftaran_id) > 1
            ) AS sub
        ", [':df' => $dateFrom, ':dt' => $dateTo])->queryOne();

        // ── 7. Top 10 High-Frequency Patients ─────────────────────────────
        $topRepeatPatients = $db->createCommand("
            SELECT 
                pm.no_rekam_medik, 
                pm.nama_pasien, 
                COUNT(p.pendaftaran_id) AS total_kunjungan,
                MAX(rm.ruangan_nama) as poliklinik_terakhir
            FROM pendaftaran_t p
            JOIN pasien_m pm ON pm.pasien_id = p.pasien_id
            JOIN ruangan_m rm ON rm.ruangan_id = p.ruangan_id
            WHERE p.tgl_pendaftaran::date BETWEEN :df AND :dt
              AND p.pasienbatalperiksa_id IS NULL
              AND rm.instalasi_id = 2
            GROUP BY pm.no_rekam_medik, pm.nama_pasien
            ORDER BY total_kunjungan DESC
            LIMIT 10
        ", [':df' => $dateFrom, ':dt' => $dateTo])->queryAll();

        return $this->render('index', [
            'dateFrom'           => $dateFrom,
            'dateTo'             => $dateTo,
            'stats'              => $statsRow,
            'politeraktif'       => $politeraktif,
            'trendData'          => $trendData,
            'barData'            => $barData,
            'rtSummary'          => $rtSummary,
            'top5'               => $top5,
            'repeatStats'        => $repeatStats,
            'topRepeatPatients'  => $topRepeatPatients,
        ]);
    }
}
