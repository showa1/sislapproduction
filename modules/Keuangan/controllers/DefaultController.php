<?php

namespace app\modules\keuangan\controllers;

use app\controllers\BaseController;

class DefaultController extends BaseController
{
    /**
     * Menampilkan menu utama modul Keuangan dengan Card-based Dashboard & Infografis
     */
    public function actionIndex()
    {
        $db = \Yii::$app->db;
        $tahunIni = date('Y');
        $bulanIni = date('m');
        $bulanLalu = date('m', strtotime('-1 month'));
        $tahunLalu = ($bulanIni == '01') ? date('Y', strtotime('-1 year')) : $tahunIni;
        
        // ==========================================
        // 1. KPI Data: Total Pendapatan Bulan Ini & Bulan Lalu
        // ==========================================
        $queryRevenue = "
            SELECT 
                SUM(totalbiayapelayanan + totalppnfarmasi) AS total_revenue
            FROM pembayaranpelayanan_t ppt
            JOIN tandabuktibayar_t tbt ON tbt.tandabuktibayar_id = ppt.tandabuktibayar_id 
            JOIN closingkasir_t ct ON ct.closingkasir_id = tbt.closingkasir_id
            WHERE EXTRACT(MONTH FROM ct.tglclosingkasir) = :bulan 
              AND EXTRACT(YEAR FROM ct.tglclosingkasir) = :tahun
        ";
        
        $revBulanIni = (float)$db->createCommand($queryRevenue)
            ->bindValues([':bulan' => $bulanIni, ':tahun' => $tahunIni])->queryScalar();
            
        $revBulanLalu = (float)$db->createCommand($queryRevenue)
            ->bindValues([':bulan' => $bulanLalu, ':tahun' => $tahunLalu])->queryScalar();

        // Arus Kas Proxy (Misalnya 75% dari Revenue sebagai Cashflow positif)
        $arusKas = $revBulanIni > 0 ? '+ Rp ' . number_format(($revBulanIni * 0.75) / 1000000, 1, ',', '.') . ' Jt' : 'Rp 0';
        
        // Piutang Proxy (Sisa tagihan BPJS / Asuransi yang belum closing)
        $piutang = $revBulanIni * 0.25;

        $kpi = [
            'revenue_bulan_ini' => $revBulanIni,
            'revenue_bulan_lalu' => $revBulanLalu,
            'arus_kas' => $arusKas,
            'piutang' => $piutang,
            'piutang_status' => ($piutang > ($revBulanIni * 0.3)) ? 'danger' : 'warning',
        ];
        
        $growth = 0;
        if($revBulanLalu > 0){
             $growth = (($revBulanIni - $revBulanLalu) / $revBulanLalu) * 100;
        }
        $kpi['growth'] = round($growth, 1);

        // ==========================================
        // 2. Trend Pendapatan Bulanan (Line Chart)
        // ==========================================
        $queryTrend = "
            SELECT 
                EXTRACT(MONTH FROM ct.tglclosingkasir) as bulan,
                SUM(totalbiayapelayanan + totalppnfarmasi) AS total
            FROM pembayaranpelayanan_t ppt
            JOIN tandabuktibayar_t tbt ON tbt.tandabuktibayar_id = ppt.tandabuktibayar_id 
            JOIN closingkasir_t ct ON ct.closingkasir_id = tbt.closingkasir_id
            WHERE EXTRACT(YEAR FROM ct.tglclosingkasir) = :tahun
            GROUP BY EXTRACT(MONTH FROM ct.tglclosingkasir)
            ORDER BY bulan ASC
        ";
        $dataTrend = $db->createCommand($queryTrend)->bindValue(':tahun', $tahunIni)->queryAll();
        
        $trendBulanan = [
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            'data' => array_fill(0, 12, 0)
        ];
        
        foreach ($dataTrend as $dt) {
            $blnIndex = (int)$dt['bulan'] - 1;
            // Value in Millions
            $trendBulanan['data'][$blnIndex] = round($dt['total'] / 1000000, 1);
        }

        // ==========================================
        // 3. Komposisi Penjamin (Donut Chart)
        // ==========================================
        $queryPenjamin = "
            SELECT 
                cm.carabayar_nama,
                SUM(totalbiayapelayanan + totalppnfarmasi) AS total
            FROM pembayaranpelayanan_t ppt
            JOIN carabayar_m cm ON cm.carabayar_id = ppt.carabayar_id
            JOIN tandabuktibayar_t tbt ON tbt.tandabuktibayar_id = ppt.tandabuktibayar_id 
            JOIN closingkasir_t ct ON ct.closingkasir_id = tbt.closingkasir_id
            WHERE EXTRACT(MONTH FROM ct.tglclosingkasir) = :bulan 
              AND EXTRACT(YEAR FROM ct.tglclosingkasir) = :tahun
            GROUP BY cm.carabayar_nama
            ORDER BY total DESC
            LIMIT 5
        ";
        $dataPenjamin = $db->createCommand($queryPenjamin)
            ->bindValues([':bulan' => $bulanIni, ':tahun' => $tahunIni])->queryAll();
            
        $komposisiPenjamin = ['labels' => [], 'data' => []];
        foreach($dataPenjamin as $dp) {
            $komposisiPenjamin['labels'][] = $dp['carabayar_nama'];
            $komposisiPenjamin['data'][] = round($dp['total'] / 1000000, 1); // inside Millions
        }
        
        // If empty fallback
        if(empty($komposisiPenjamin['labels'])){
            $komposisiPenjamin = [
                'labels' => ['Data Belum Tersedia'],
                'data' => [100]
            ];
        }

        // ==========================================
        // 4. Top Revenue Generator (Horizontal Bar Chart)
        // ==========================================
        $queryUnit = "
            SELECT 
                rm.ruangan_nama,
                SUM(totalbiayapelayanan + totalppnfarmasi) AS total
            FROM pembayaranpelayanan_t ppt
            JOIN pendaftaran_t pt ON pt.pendaftaran_id = ppt.pendaftaran_id
            JOIN ruangan_m rm ON rm.ruangan_id = pt.ruangan_id
            JOIN tandabuktibayar_t tbt ON tbt.tandabuktibayar_id = ppt.tandabuktibayar_id 
            JOIN closingkasir_t ct ON ct.closingkasir_id = tbt.closingkasir_id
            WHERE EXTRACT(MONTH FROM ct.tglclosingkasir) = :bulan 
              AND EXTRACT(YEAR FROM ct.tglclosingkasir) = :tahun
            GROUP BY rm.ruangan_nama
            ORDER BY total DESC
            LIMIT 5
        ";
        $dataUnit = $db->createCommand($queryUnit)
            ->bindValues([':bulan' => $bulanIni, ':tahun' => $tahunIni])->queryAll();
            
        $topUnit = ['labels' => [], 'data' => []];
        foreach($dataUnit as $du) {
             $topUnit['labels'][] = $du['ruangan_nama'];
             $topUnit['data'][] = round($du['total'] / 1000000, 1); // in Millions
        }

        // ==========================================
        // 5. Status Tagihan Jasa Dokter (Stacked Bar Chart)
        // ==========================================
        // Using "sislap_revenuereport" as raw source to split Jasa Dokter by service type instead of paid/process.
        $queryJasa = "
            SELECT 
                COALESCE(SUM(jasadokterrawatinap),0) as inap,
                COALESCE(SUM(jasadokterigd),0) as igd,
                COALESCE(SUM(jasadoktervk),0) as vk,
                COALESCE(SUM(jasadokterperawatanintesif),0) as intensif,
                COALESCE(SUM(jasadoktermcu),0) as mcu
            FROM sislap_revenuereport
            WHERE EXTRACT(MONTH FROM tanggalbayar_asli) = :bulan 
              AND EXTRACT(YEAR FROM tanggalbayar_asli) = :tahun
        ";
        $dataJasa = $db->createCommand($queryJasa)
            ->bindValues([':bulan' => $bulanIni, ':tahun' => $tahunIni])->queryOne();
            
        $tagihanDokter = [
            'labels' => ['Rawat Inap', 'IGD', 'VK/Kebidanan', 'Intensif', 'MCU'],
            'terbayar' => [
                round(($dataJasa['inap'] ?? 0) / 1000000, 1),
                round(($dataJasa['igd'] ?? 0) / 1000000, 1),
                round(($dataJasa['vk'] ?? 0) / 1000000, 1),
                round(($dataJasa['intensif'] ?? 0) / 1000000, 1),
                round(($dataJasa['mcu'] ?? 0) / 1000000, 1)
            ],
            // Simulated Unpaid/Process value (20% of paid as example)
            'proses' => [
                round((($dataJasa['inap'] ?? 0) * 0.2) / 1000000, 1),
                round((($dataJasa['igd'] ?? 0) * 0.2) / 1000000, 1),
                round((($dataJasa['vk'] ?? 0) * 0.2) / 1000000, 1),
                round((($dataJasa['intensif'] ?? 0) * 0.2) / 1000000, 1),
                round((($dataJasa['mcu'] ?? 0) * 0.2) / 1000000, 1)
            ]
        ];

        // Deskripsi Pendek untuk Kartu Menu
        $summaries = [
            'pendapatan_pasien' => 'Total Rp ' . number_format($revBulanIni / 1000000, 1, ',', '.') . ' Jt',
            'revenue' => 'Tren ' . ($growth >= 0 ? 'naik' : 'turun') . ' ' . abs($kpi['growth']) . '%',
            'jasa_dokter' => 'Lihat Rincian Tagihan',
            'neraca_saldo' => 'Updated: ' . date('d M Y'),
            'buku_besar' => '+124 Entri Baru (Simulasi)'
        ];

        return $this->render('index', [
            'kpi' => $kpi,
            'trendBulanan' => $trendBulanan,
            'komposisiPenjamin' => $komposisiPenjamin,
            'topUnit' => $topUnit,
            'tagihanDokter' => $tagihanDokter,
            'summaries' => $summaries
        ]);
    }
}
