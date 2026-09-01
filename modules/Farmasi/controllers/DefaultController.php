<?php

namespace app\modules\farmasi\controllers;

use app\controllers\BaseController;
use Yii;

class DefaultController extends BaseController
{
    /**
     * Menampilkan dashboard utama farmasi
     */
    public function actionIndex()
    {
        $db = \Yii::$app->db;
        $tahunIni = date('Y');
        $bulanIni = date('m');
        $bulanLalu = date('m', strtotime('-1 month'));
        $tahunLalu = date('Y', strtotime('-1 month'));

        // 1. Total Resep (Distinct Pendaftaran)
        $resepSql = "SELECT COUNT(DISTINCT pendaftaran_id) FROM obatalkespasien_t WHERE EXTRACT(MONTH FROM create_time) = :bulan AND EXTRACT(YEAR FROM create_time) = :tahun";
        $resep_curr = $db->createCommand($resepSql)->bindValues([':bulan' => $bulanIni, ':tahun' => $tahunIni])->queryScalar();
        $resep_prev = $db->createCommand($resepSql)->bindValues([':bulan' => $bulanLalu, ':tahun' => $tahunLalu])->queryScalar();
        $resep_growth = $resep_prev > 0 ? round((($resep_curr - $resep_prev) / $resep_prev) * 100, 1) : 0;

        // 2. Estimasi Pendapatan Obat
        $pendapatanSql = "SELECT SUM(qty_oa * hargasatuan_oa) FROM obatalkespasien_t WHERE EXTRACT(MONTH FROM create_time) = :bulan AND EXTRACT(YEAR FROM create_time) = :tahun";
        $pendapatan_curr = $db->createCommand($pendapatanSql)->bindValues([':bulan' => $bulanIni, ':tahun' => $tahunIni])->queryScalar();
        $pendapatan_prev = $db->createCommand($pendapatanSql)->bindValues([':bulan' => $bulanLalu, ':tahun' => $tahunLalu])->queryScalar();
        $pendapatan_growth = $pendapatan_prev > 0 ? round((($pendapatan_curr - $pendapatan_prev) / $pendapatan_prev) * 100, 1) : 0;

        // 3. Stok Minimal Alert
        $stok_min_alert = $db->createCommand("
            SELECT COUNT(*) FROM (
                SELECT om.obatalkes_id, sum(COALESCE(st.qtystok_in,0) - COALESCE(st.qtystok_out,0)) AS stok, stm.jmlminimalstok
                FROM obatalkes_m om
                JOIN stokminimal_t stm ON stm.obatalkes_id = om.obatalkes_id
                LEFT JOIN stokobatalkes_t st ON om.obatalkes_id = st.obatalkes_id AND st.ruangan_id = stm.ruangan_id
                WHERE om.obatalkes_aktif = true
                GROUP BY om.obatalkes_id, stm.jmlminimalstok
            ) sub WHERE stok <= jmlminimalstok")->queryScalar();

        // 4. Obat Expired < 90 Hari
        $expired_alert = $db->createCommand("SELECT COUNT(*) FROM obatalkes_m WHERE obatalkes_aktif = true AND tglkadaluarsa IS NOT NULL AND tglkadaluarsa <= NOW() + INTERVAL '90 days'")->queryScalar();

        // 5. Stock Out (Stok = 0)
        $stockout_alert = $db->createCommand("
            SELECT COUNT(*) FROM (
                SELECT om.obatalkes_id, COALESCE(SUM(st.qtystok_in - st.qtystok_out), 0) AS stok
                FROM obatalkes_m om
                LEFT JOIN stokobatalkes_t st ON st.obatalkes_id = om.obatalkes_id
                WHERE om.obatalkes_aktif = true
                GROUP BY om.obatalkes_id
                HAVING COALESCE(SUM(st.qtystok_in - st.qtystok_out), 0) = 0
            ) sub")->queryScalar();

        // 6. Dead Stock (tidak bergerak 180 hari)
        $deadstock = $db->createCommand("
            SELECT COUNT(DISTINCT st.obatalkes_id)
            FROM stokobatalkes_t st
            WHERE st.obatalkes_id NOT IN (
                SELECT DISTINCT obatalkes_id FROM stokobatalkes_t
                WHERE create_time >= NOW() - INTERVAL '180 days'
            )")->queryScalar();

        // 7. Nilai Aset Gudang (stok saat ini x harga netto)
        $nilaiAset = $db->createCommand("
            SELECT SUM(sub.stok * om.harganetto)
            FROM (
                SELECT obatalkes_id, SUM(qtystok_in - qtystok_out) AS stok
                FROM stokobatalkes_t GROUP BY obatalkes_id
            ) sub
            JOIN obatalkes_m om ON om.obatalkes_id = sub.obatalkes_id
            WHERE om.obatalkes_aktif = true AND sub.stok > 0")->queryScalar();

        // 8. TOR (Turnover Ratio) = qty keluar 1 tahun / rata-rata stok
        $totalKeluarTahunan = $db->createCommand("
            SELECT SUM(qtystok_out) FROM stokobatalkes_t
            WHERE create_time >= NOW() - INTERVAL '12 months'")->queryScalar();
        $rataRataStok = $db->createCommand("
            SELECT AVG(stok) FROM (
                SELECT obatalkes_id, SUM(qtystok_in - qtystok_out) AS stok
                FROM stokobatalkes_t GROUP BY obatalkes_id
                HAVING SUM(qtystok_in - qtystok_out) > 0
            ) sub")->queryScalar();
        $tor = $rataRataStok > 0 ? round($totalKeluarTahunan / $rataRataStok, 1) : 0;

        // 9. Distribusi Kategori (untuk Donut Chart)
        $distribusiKategori = $db->createCommand("
            SELECT om.obatalkes_kategori AS kategori, COUNT(*) as jumlah
            FROM obatalkes_m om WHERE om.obatalkes_aktif = true
            GROUP BY om.obatalkes_kategori ORDER BY jumlah DESC")->queryAll();

        // 10. Pending PO
        $pendingPO = $db->createCommand("
            SELECT COUNT(*) FROM permintaanpembelian_t
            WHERE batalpermintaanpembelian_id IS NULL AND penerimaanbarang_id IS NULL")->queryScalar();

        // 11. Avg Lead Time (hari)
        $leadTime = $db->createCommand("
            SELECT ROUND(AVG(EXTRACT(DAY FROM (pb.tglterima - pp.tglpermintaanpembelian)))::numeric, 1)
            FROM permintaanpembelian_t pp
            JOIN penerimaanbarang_t pb ON pb.penerimaanbarang_id = pp.penerimaanbarang_id
            WHERE pp.tglpermintaanpembelian >= NOW() - INTERVAL '1 year'")->queryScalar();

        // 12. Activity Log (10 transaksi terakhir)
        $activityLog = $db->createCommand("
            SELECT st.create_time, om.obatalkes_nama, st.qtystok_in, st.qtystok_out
            FROM stokobatalkes_t st
            JOIN obatalkes_m om ON om.obatalkes_id = st.obatalkes_id
            ORDER BY st.create_time DESC LIMIT 10")->queryAll();

        // 13. Tren Harian Resep (Bulan Ini)
        $hariDalamBulan = date('t');
        $trendRaw = $db->createCommand("
            SELECT EXTRACT(DAY FROM create_time) as hari, COUNT(DISTINCT pendaftaran_id) as total_resep
            FROM obatalkespasien_t
            WHERE EXTRACT(MONTH FROM create_time) = :bulan AND EXTRACT(YEAR FROM create_time) = :tahun
            GROUP BY EXTRACT(DAY FROM create_time) ORDER BY hari
        ")->bindValues([':bulan' => $bulanIni, ':tahun' => $tahunIni])->queryAll();

        $trendDates = [];
        $trendData = [];
        for ($i = 1; $i <= $hariDalamBulan; $i++) {
            $trendDates[] = $i;
            $trendData[$i] = 0;
        }
        foreach ($trendRaw as $t) {
            $trendData[(int)$t['hari']] = (int)$t['total_resep'];
        }

        // 14. Top 10 Fast Moving
        $topObat = $db->createCommand("
            SELECT om.obatalkes_nama as nama, SUM(opt.qty_oa) as total
            FROM obatalkespasien_t opt
            JOIN obatalkes_m om ON om.obatalkes_id = opt.obatalkes_id
            WHERE EXTRACT(MONTH FROM opt.create_time) = :bulan AND EXTRACT(YEAR FROM opt.create_time) = :tahun
            GROUP BY om.obatalkes_id, om.obatalkes_nama
            ORDER BY total DESC LIMIT 10
        ")->bindValues([':bulan' => $bulanIni, ':tahun' => $tahunIni])->queryAll();

        // 15. Detail Stok Menipis (untuk modal)
        $stokMenipis = $db->createCommand("
            SELECT om.obatalkes_nama, om.obatalkes_kategori,
                   sum(COALESCE(st.qtystok_in,0) - COALESCE(st.qtystok_out,0)) AS stok,
                   stm.jmlminimalstok
            FROM obatalkes_m om
            JOIN stokminimal_t stm ON stm.obatalkes_id = om.obatalkes_id
            LEFT JOIN stokobatalkes_t st ON om.obatalkes_id = st.obatalkes_id AND st.ruangan_id = stm.ruangan_id
            WHERE om.obatalkes_aktif = true
            GROUP BY om.obatalkes_nama, om.obatalkes_kategori, stm.jmlminimalstok
            HAVING sum(COALESCE(st.qtystok_in,0) - COALESCE(st.qtystok_out,0)) <= stm.jmlminimalstok
            ORDER BY stok ASC LIMIT 20")->queryAll();

        // 16. Detail Obat Mendekati Expired (untuk modal)
        $expiredDetail = $db->createCommand("
            SELECT obatalkes_nama, obatalkes_kategori, tglkadaluarsa,
                   EXTRACT(DAY FROM (tglkadaluarsa - NOW())) as sisa_hari
            FROM obatalkes_m
            WHERE obatalkes_aktif = true AND tglkadaluarsa IS NOT NULL
              AND tglkadaluarsa <= NOW() + INTERVAL '90 days'
            ORDER BY tglkadaluarsa ASC LIMIT 20")->queryAll();

        // 17. Detail Dead Stock (untuk modal)
        $deadstockDetail = $db->createCommand("
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
            ORDER BY last_trx ASC LIMIT 50")->queryAll();

        // 18. Detail Stock Out (untuk modal)
        $stockoutDetail = $db->createCommand("
            SELECT om.obatalkes_nama, om.obatalkes_kategori,
                   COALESCE(SUM(st.qtystok_in - st.qtystok_out), 0) AS stok,
                   MAX(st.create_time) AS last_trx
            FROM obatalkes_m om
            LEFT JOIN stokobatalkes_t st ON st.obatalkes_id = om.obatalkes_id
            WHERE om.obatalkes_aktif = true
            GROUP BY om.obatalkes_id, om.obatalkes_nama, om.obatalkes_kategori
            HAVING COALESCE(SUM(st.qtystok_in - st.qtystok_out), 0) = 0
            ORDER BY last_trx DESC LIMIT 50")->queryAll();

        return $this->render('index', [
            'resep_curr' => $resep_curr,
            'resep_growth' => $resep_growth,
            'pendapatan_curr' => $pendapatan_curr,
            'pendapatan_growth' => $pendapatan_growth,
            'stok_min_alert' => $stok_min_alert,
            'expired_alert' => $expired_alert,
            'stockout_alert' => $stockout_alert,
            'deadstock' => $deadstock,
            'nilaiAset' => $nilaiAset,
            'tor' => $tor,
            'distribusiKategori' => $distribusiKategori,
            'pendingPO' => $pendingPO,
            'leadTime' => $leadTime,
            'activityLog' => $activityLog,
            'trendDates' => $trendDates,
            'trendData' => array_values($trendData),
            'topObat' => $topObat,
            'stokMenipis' => $stokMenipis,
            'expiredDetail' => $expiredDetail,
            'deadstockDetail' => $deadstockDetail,
            'stockoutDetail' => $stockoutDetail,
            'tahunIni' => $tahunIni,
            'bulanIni' => $bulanIni,
        ]);
    }
}
