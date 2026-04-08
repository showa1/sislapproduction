<?php

namespace app\modules\eksekutif\controllers;

use app\controllers\BaseController;

class DefaultController extends BaseController
{
    /**
     * Menampilkan menu utama modul / Dashboard Eksekutif
     */
    public function actionIndex()
    {
        $db = \Yii::$app->db;
        $tahunIni = date('Y');
        $bulanIni = date('m');
        $bulanLalu = date('m', strtotime('-1 month'));
        $tahunLalu = date('Y', strtotime('-1 month'));

        // 1. KPI 1: RAWAT JALAN (Asumsi instalasi = 2)
        $rf_rj_sql = "
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN statuspasien = 'PENGUNJUNG BARU' THEN 1 ELSE 0 END) as baru,
                SUM(CASE WHEN statuspasien = 'PENGUNJUNG LAMA' THEN 1 ELSE 0 END) as lama
            FROM pendaftaran_t pt
            JOIN ruangan_m rm ON rm.ruangan_id = pt.ruangan_id
            WHERE EXTRACT(MONTH FROM pt.tgl_pendaftaran) = :bulan 
              AND EXTRACT(YEAR FROM pt.tgl_pendaftaran) = :tahun
              AND rm.instalasi_id = 2
              AND pt.pasienbatalperiksa_id IS NULL
        ";
        $rj_curr = $db->createCommand($rf_rj_sql)->bindValues([':bulan' => $bulanIni, ':tahun' => $tahunIni])->queryOne();
        $rj_prev = $db->createCommand("SELECT COUNT(*) FROM pendaftaran_t pt JOIN ruangan_m rm ON rm.ruangan_id = pt.ruangan_id WHERE EXTRACT(MONTH FROM pt.tgl_pendaftaran) = :bulan AND EXTRACT(YEAR FROM pt.tgl_pendaftaran) = :tahun AND rm.instalasi_id = 2 AND pt.pasienbatalperiksa_id IS NULL")->bindValues([':bulan' => $bulanLalu, ':tahun' => $tahunLalu])->queryScalar();
        $rj_growth = $rj_prev > 0 ? round((($rj_curr['total'] - $rj_prev) / $rj_prev) * 100, 1) : 0;
        $rj_baru_pct = $rj_curr['total'] > 0 ? round(($rj_curr['baru'] / $rj_curr['total']) * 100, 1) : 0;
        
        // 2. KPI 2: RAWAT DARURAT (Asumsi instalasi = 3)
        $igd_curr = $db->createCommand("SELECT COUNT(*) FROM pendaftaran_t pt JOIN ruangan_m rm ON rm.ruangan_id = pt.ruangan_id WHERE EXTRACT(MONTH FROM pt.tgl_pendaftaran) = :bulan AND EXTRACT(YEAR FROM pt.tgl_pendaftaran) = :tahun AND rm.instalasi_id = 3 AND pt.pasienbatalperiksa_id IS NULL")->bindValues([':bulan' => $bulanIni, ':tahun' => $tahunIni])->queryScalar();
        $igd_prev = $db->createCommand("SELECT COUNT(*) FROM pendaftaran_t pt JOIN ruangan_m rm ON rm.ruangan_id = pt.ruangan_id WHERE EXTRACT(MONTH FROM pt.tgl_pendaftaran) = :bulan AND EXTRACT(YEAR FROM pt.tgl_pendaftaran) = :tahun AND rm.instalasi_id = 3 AND pt.pasienbatalperiksa_id IS NULL")->bindValues([':bulan' => $bulanLalu, ':tahun' => $tahunLalu])->queryScalar();
        $igd_growth = $igd_prev > 0 ? round((($igd_curr - $igd_prev) / $igd_prev) * 100, 1) : 0;

        // 3. KPI 3: RAWAT INAP (Kunjungan Admisi & BOR)
        $inap_curr = $db->createCommand("SELECT COUNT(*) FROM pasienadmisi_t WHERE EXTRACT(MONTH FROM tgladmisi) = :bulan AND EXTRACT(YEAR FROM tgladmisi) = :tahun")->bindValues([':bulan' => $bulanIni, ':tahun' => $tahunIni])->queryScalar();
        $inap_prev = $db->createCommand("SELECT COUNT(*) FROM pasienadmisi_t WHERE EXTRACT(MONTH FROM tgladmisi) = :bulan AND EXTRACT(YEAR FROM tgladmisi) = :tahun")->bindValues([':bulan' => $bulanLalu, ':tahun' => $tahunLalu])->queryScalar();
        $inap_growth = $inap_prev > 0 ? round((($inap_curr - $inap_prev) / $inap_prev) * 100, 1) : 0;
        
        $bed_data = $db->createCommand("SELECT SUM(kapasitas) as total_bed, SUM(tersedia) as bed_kosong FROM kamaraplicare_r")->queryOne();
        $total_bed = $bed_data['total_bed'] ?: 1;
        $bor_pct = round((($total_bed - $bed_data['bed_kosong']) / $total_bed) * 100, 1);

        // 4. KPI 4: LAB & RAD (Instalasi 20 & 24)
        $labrad_curr = $db->createCommand("SELECT COUNT(*) FROM pendaftaran_t pt JOIN ruangan_m rm ON rm.ruangan_id = pt.ruangan_id WHERE EXTRACT(MONTH FROM pt.tgl_pendaftaran) = :bulan AND EXTRACT(YEAR FROM pt.tgl_pendaftaran) = :tahun AND rm.instalasi_id IN (20, 24) AND pt.pasienbatalperiksa_id IS NULL")->bindValues([':bulan' => $bulanIni, ':tahun' => $tahunIni])->queryScalar();
        $labrad_prev = $db->createCommand("SELECT COUNT(*) FROM pendaftaran_t pt JOIN ruangan_m rm ON rm.ruangan_id = pt.ruangan_id WHERE EXTRACT(MONTH FROM pt.tgl_pendaftaran) = :bulan AND EXTRACT(YEAR FROM pt.tgl_pendaftaran) = :tahun AND rm.instalasi_id IN (20, 24) AND pt.pasienbatalperiksa_id IS NULL")->bindValues([':bulan' => $bulanLalu, ':tahun' => $tahunLalu])->queryScalar();
        $labrad_growth = $labrad_prev > 0 ? round((($labrad_curr - $labrad_prev) / $labrad_prev) * 100, 1) : 0;

        // Health Service Funnel (Stacked Chart Data)
        $trendData = $db->createCommand("
            SELECT 
                EXTRACT(MONTH FROM pt.tgl_pendaftaran) as bln,
                SUM(CASE WHEN rm.instalasi_id = 2 THEN 1 ELSE 0 END) as rj,
                SUM(CASE WHEN rm.instalasi_id = 3 THEN 1 ELSE 0 END) as igd,
                SUM(CASE WHEN rm.instalasi_id IN (20,24) THEN 1 ELSE 0 END) as penunjang,
                SUM(CASE WHEN rm.instalasi_id NOT IN (2,3,20,24) THEN 1 ELSE 0 END) as lain
            FROM pendaftaran_t pt
            JOIN ruangan_m rm ON rm.ruangan_id = pt.ruangan_id
            WHERE EXTRACT(YEAR FROM pt.tgl_pendaftaran) = :tahun AND pt.pasienbatalperiksa_id IS NULL
            GROUP BY EXTRACT(MONTH FROM pt.tgl_pendaftaran)
            ORDER BY bln
        ")->bindValues([':tahun' => $tahunIni])->queryAll();
        
        // Rawat Inap contribution to funnel (Adminisi)
        $inapTrend = $db->createCommand("
            SELECT 
                EXTRACT(MONTH FROM tgladmisi) as bln,
                COUNT(*) as ri
            FROM pasienadmisi_t
            WHERE EXTRACT(YEAR FROM tgladmisi) = :tahun
            GROUP BY EXTRACT(MONTH FROM tgladmisi)
            ORDER BY bln
        ")->bindValues([':tahun' => $tahunIni])->queryAll();

        $trendRJ = array_fill(1, 12, 0);
        $trendIGD = array_fill(1, 12, 0);
        $trendRI = array_fill(1, 12, 0);
        $trendLabRad = array_fill(1, 12, 0);
        $trendLain = array_fill(1, 12, 0);
        
        foreach($trendData as $t) {
            $trendRJ[$t['bln']] = (int)$t['rj'];
            $trendIGD[$t['bln']] = (int)$t['igd'];
            $trendLabRad[$t['bln']] = (int)$t['penunjang'];
            $trendLain[$t['bln']] = (int)$t['lain'];
        }
        foreach($inapTrend as $t) {
            $trendRI[$t['bln']] = (int)$t['ri'];
        }

        // Heatmap Waktu Kunjungan (DOW and Hour) for RJ and IGD
        $heatmapDataSql = "
            SELECT 
                EXTRACT(DOW FROM tgl_pendaftaran) as hari,
                EXTRACT(HOUR FROM tgl_pendaftaran) as jam,
                COUNT(*) as jumlah
            FROM pendaftaran_t pt
            JOIN ruangan_m rm ON rm.ruangan_id = pt.ruangan_id
            WHERE EXTRACT(MONTH FROM pt.tgl_pendaftaran) = :bulan 
              AND EXTRACT(YEAR FROM pt.tgl_pendaftaran) = :tahun
              AND rm.instalasi_id IN (2, 3)
              AND pt.pasienbatalperiksa_id IS NULL
            GROUP BY hari, jam
        ";
        $heatmapRaw = $db->createCommand($heatmapDataSql)->bindValues([':bulan' => $bulanIni, ':tahun' => $tahunIni])->queryAll();
        
        // Initialize Heatmap Data
        $heatmapMatrix = [];
        $hari_labels = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
        for ($h = 0; $h <= 6; $h++) {
            for ($j = 0; $j < 24; $j++) {
                $heatmapMatrix[$hari_labels[$h]][$j] = 0;
            }
        }
        foreach ($heatmapRaw as $row) {
            $hari_idx = (int)$row['hari'];
            $jam = (int)$row['jam'];
            if(isset($hari_labels[$hari_idx])) {
                $heatmapMatrix[$hari_labels[$hari_idx]][$jam] = (int)$row['jumlah'];
            }
        }

        // Top 5 Unit/Pemeriksaan Penunjang (Operational Efficiency)
        // Proxy: Group by Ruangan for Penunjang to get 'Jenis Pemeriksaan' proxy OR join tindakanpelayanan_t 
        // We will just do Ruangan for Penunjang for safety unless daftartindakan_m gives errors.
        try {
            $topPemeriksaan = $db->createCommand("
                SELECT 
                    dt.daftartindakan_nama as nama,
                    COUNT(tp.tindakanpelayanan_id) as total
                FROM tindakanpelayanan_t tp
                JOIN daftartindakan_m dt ON dt.daftartindakan_id = tp.daftartindakan_id
                JOIN ruangan_m rm ON rm.ruangan_id = tp.ruangan_id
                WHERE EXTRACT(MONTH FROM tp.tgl_tindakan) = :bulan 
                  AND EXTRACT(YEAR FROM tp.tgl_tindakan) = :tahun
                  AND rm.instalasi_id IN (20, 24)
                GROUP BY dt.daftartindakan_id, dt.daftartindakan_nama
                ORDER BY total DESC LIMIT 5
            ")->bindValues([':bulan' => $bulanIni, ':tahun' => $tahunIni])->queryAll();
        } catch (\Exception $e) {
            // Fallback to top Unit Penunjang if daftartindakan_m relation fails
            $topPemeriksaan = $db->createCommand("
                SELECT 
                    rm.ruangan_nama as nama,
                    COUNT(pt.pendaftaran_id) as total
                FROM pendaftaran_t pt
                JOIN ruangan_m rm ON rm.ruangan_id = pt.ruangan_id
                WHERE EXTRACT(MONTH FROM pt.tgl_pendaftaran) = :bulan 
                  AND EXTRACT(YEAR FROM pt.tgl_pendaftaran) = :tahun
                  AND rm.instalasi_id IN (20, 24)
                  AND pt.pasienbatalperiksa_id IS NULL
                GROUP BY rm.ruangan_id, rm.ruangan_nama
                ORDER BY total DESC LIMIT 5
            ")->bindValues([':bulan' => $bulanIni, ':tahun' => $tahunIni])->queryAll();
        }

        // Dominasi Cara Bayar untuk matriks lama (dipertahankan)
        $topCaraBayar = $db->createCommand("
            SELECT cm.carabayar_nama, COUNT(pt.pendaftaran_id) as total 
            FROM pendaftaran_t pt
            JOIN carabayar_m cm ON cm.carabayar_id = pt.carabayar_id
            WHERE EXTRACT(MONTH FROM pt.tgl_pendaftaran) = :bulan AND EXTRACT(YEAR FROM pt.tgl_pendaftaran) = :tahun AND pt.pasienbatalperiksa_id IS NULL
            GROUP BY cm.carabayar_id, cm.carabayar_nama
            ORDER BY total DESC LIMIT 1
        ")->bindValues([':bulan' => $bulanIni, ':tahun' => $tahunIni])->queryOne();

        $matriksBayar = $db->createCommand("
            SELECT cm.carabayar_nama, pt.statuspasien, COUNT(pt.pendaftaran_id) as total 
            FROM pendaftaran_t pt
            JOIN carabayar_m cm ON cm.carabayar_id = pt.carabayar_id
            WHERE EXTRACT(MONTH FROM pt.tgl_pendaftaran) = :bulan AND EXTRACT(YEAR FROM pt.tgl_pendaftaran) = :tahun AND pt.pasienbatalperiksa_id IS NULL
            GROUP BY cm.carabayar_id, cm.carabayar_nama, pt.statuspasien
            ORDER BY total DESC
        ")->bindValues([':bulan' => $bulanIni, ':tahun' => $tahunIni])->queryAll();

        return $this->render('index', [
            'rj_curr' => $rj_curr['total'],
            'rj_baru_pct' => $rj_baru_pct,
            'rj_growth' => $rj_growth,
            
            'igd_curr' => $igd_curr,
            'igd_growth' => $igd_growth,
            
            'inap_curr' => $inap_curr,
            'inap_growth' => $inap_growth,
            'bor_pct' => $bor_pct,
            
            'labrad_curr' => $labrad_curr,
            'labrad_growth' => $labrad_growth,
            
            'topCaraBayar' => $topCaraBayar,
            'matriksBayar' => $matriksBayar,
            
            'trendRJ' => array_values($trendRJ),
            'trendIGD' => array_values($trendIGD),
            'trendRI' => array_values($trendRI),
            'trendLabRad' => array_values($trendLabRad),
            'trendLain' => array_values($trendLain),
            
            'heatmapMatrix' => $heatmapMatrix,
            'hari_labels' => $hari_labels,
            
            'topPemeriksaan' => $topPemeriksaan,
            
            'tahunIni' => $tahunIni,
            'bulanIni' => $bulanIni,
        ]);
    }
}
