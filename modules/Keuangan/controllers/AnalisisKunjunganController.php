<?php

namespace app\modules\keuangan\controllers;

use app\controllers\BaseController;
use Yii;
use DateTime;
use DateInterval;

class AnalisisKunjunganController extends BaseController
{
    /**
     * Menampilkan dashboard Analisis Perbandingan Pasien (Closing Kasir) 
     * Dirancang khusus untuk level Direksi.
     */
    public function actionIndex()
    {
        // 1. Ambil Parameter Tanggal
        $startDate = Yii::$app->request->get('start_date', date('Y-m-01'));
        $endDate = Yii::$app->request->get('end_date', date('Y-m-d'));
        $filterType = Yii::$app->request->get('filter_type', 'custom'); // 'mtd', 'last_month', 'first_10'

        // Hitung Periode Bulan Lalu (Untuk perbandingan)
        $date1 = new DateTime($startDate);
        $date2 = new DateTime($endDate);
        
        $diff = $date1->diff($date2)->days + 1;
        
        $pastStartDate = (clone $date1)->sub(new DateInterval('P1M'))->format('Y-m-d');
        $pastEndDate = (clone $date2)->sub(new DateInterval('P1M'))->format('Y-m-d');

        // ==========================================
        // 2. Fetch Real Data Sync With Pendapatan Pasien
        // ==========================================
        $db = \Yii::$app->db;
        
        $baseQueryStr = "
            FROM pembayaranpelayanan_t ppt 
            JOIN pasien_m pm ON pm.pasien_id = ppt.pasien_id 
            JOIN pendaftaran_t pt ON pt.pendaftaran_id = ppt.pendaftaran_id 
            JOIN carabayar_m cm ON cm.carabayar_id = ppt.carabayar_id 
            JOIN tandabuktibayar_t tbt ON tbt.tandabuktibayar_id = ppt.tandabuktibayar_id 
            JOIN closingkasir_t ct ON ct.closingkasir_id = tbt.closingkasir_id 
            JOIN ruangan_m rm ON rm.ruangan_id = pt.ruangan_id
            JOIN pegawai_m pg ON pg.pegawai_id = pt.pegawai_id
            JOIN instalasi_m im ON im.instalasi_id = rm.instalasi_id
            LEFT JOIN pasienadmisi_t pat ON pat.pasienadmisi_id = ppt.pasienadmisi_id
            LEFT JOIN ruangan_m rm2 ON rm2.ruangan_id=pat.ruangan_id
            LEFT JOIN pegawai_m pg2 ON pg2.pegawai_id=pat.pegawai_id
            LEFT JOIN instalasi_m im2 ON im2.instalasi_id = rm2.instalasi_id
            WHERE DATE(ct.tglclosingkasir) BETWEEN :start AND :end
        ";

        $groupByStr = "
            GROUP BY 
                pt.tgl_pendaftaran, pt.no_pendaftaran, ppt.tglpembayaran, ppt.nopembayaran, ct.tglclosingkasir, ct.closingkasir_no, 
                pm.no_rekam_medik, pm.nama_pasien, cm.carabayar_nama, ppt.totalbiayatindakan, ppt.totalbiayaoa, 
                ppt.totalbiayapelayanan, ppt.totalppnfarmasi, im.instalasi_nama, rm.ruangan_nama, im2.instalasi_nama, 
                rm2.ruangan_nama, pg.gelardepan, pg.nama_pegawai, pg2.gelardepan, pg2.nama_pegawai
        ";

        // Total Kunjungan Bulan/Periode Ini (Berdasar Closing Kasir)
        $totalBulanIni = (int)$db->createCommand("
            SELECT COUNT(*) FROM (
                SELECT 1 $baseQueryStr $groupByStr
            ) t
        ")->bindValues([':start' => $startDate, ':end' => $endDate])->queryScalar();

        // Total Kunjungan Raw/Kasar Bulan Ini (Hanya Pendaftaran Tanpa Filter Closing)
        $totalPendaftaranRaw = (int)$db->createCommand("
            SELECT COUNT(1) FROM pendaftaran_t 
            WHERE DATE(tgl_pendaftaran) BETWEEN :start AND :end
        ")->bindValues([':start' => $startDate, ':end' => $endDate])->queryScalar();

        // Total Kunjungan Bulan/Periode Lalu (Sama panjang hari)
        $totalBulanLalu = (int)$db->createCommand("
            SELECT COUNT(*) FROM (
                SELECT 1 $baseQueryStr $groupByStr
            ) t
        ")->bindValues([':start' => $pastStartDate, ':end' => $pastEndDate])->queryScalar();

        $selisih = $totalBulanIni - $totalBulanLalu;
        $belumClosing = $totalPendaftaranRaw - $totalBulanIni;
        
        $persentase = 0;
        if($totalBulanLalu > 0) {
            $persentase = round(($selisih / $totalBulanLalu) * 100, 1);
        }

        // Teks Naratif Cerdas
        if ($persentase > 0) {
            $narasi = "Luar biasa! Kunjungan meningkat <span class='font-bold text-emerald-600'>{$persentase}%</span> dibanding periode yang sama sebelumnya, menandakan tren layanan positif.";
            $statusWarna = 'emerald';
        } else if ($persentase < 0) {
            $narasi = "Perhatian: Kunjungan menurun <span class='font-bold text-rose-600'>".abs($persentase)."%</span> dibanding periode sebelumnya. Perlu peninjauan operasional segera.";
            $statusWarna = 'rose';
        } else {
            $narasi = "Kunjungan relatif stabil dibanding periode sebelumnya (0%).";
            $statusWarna = 'slate';
        }

        $kpi = [
            'bulan_lalu' => $totalBulanLalu,
            'bulan_ini' => $totalBulanIni,
            'selisih' => $selisih,
            'persentase' => $persentase,
            'belum_closing' => $belumClosing > 0 ? $belumClosing : 0, 
            'narasi' => $narasi,
            'warna' => $statusWarna,
            'label_sekarang' => date('d M Y', strtotime($startDate)) . ' - ' . date('d M Y', strtotime($endDate)),
            'label_lalu' => date('d M Y', strtotime($pastStartDate)) . ' - ' . date('d M Y', strtotime($pastEndDate))
        ];

        // 3. Data Line Chart (Tren Harian) - Superimposed Set
        $labels = [];
        $dataSekarang = [];
        $dataLalu = [];

        // Fetch Daily Trend Current Period
        $querySekarang = "
            SELECT DATE(tglclosingkasir) as tgl, COUNT(*) as total FROM (
                SELECT ct.tglclosingkasir $baseQueryStr $groupByStr
            ) t
            GROUP BY DATE(tglclosingkasir)
            ORDER BY tgl ASC
        ";
        $trendSekarangRaw = $db->createCommand($querySekarang)
            ->bindValues([':start' => $startDate, ':end' => $endDate])->queryAll();
        
        // Dictionary for O(1) matching
        $dictSekarang = [];
        foreach($trendSekarangRaw as $row) { $dictSekarang[$row['tgl']] = (int)$row['total']; }

        // Fetch Daily Trend Past Period
        $queryLalu = "
            SELECT DATE(tglclosingkasir) as tgl, COUNT(*) as total FROM (
                SELECT ct.tglclosingkasir $baseQueryStr $groupByStr
            ) t
            GROUP BY DATE(tglclosingkasir)
            ORDER BY tgl ASC
        ";
        $trendLaluRaw = $db->createCommand($queryLalu)
            ->bindValues([':start' => $pastStartDate, ':end' => $pastEndDate])->queryAll();

        $dictLalu = [];
        foreach($trendLaluRaw as $row) { $dictLalu[$row['tgl']] = (int)$row['total']; }

        // Loop through days linearly to construct arrays for Chart.js
        $currentUnix = strtotime($startDate);
        $pastUnix = strtotime($pastStartDate);
        $maxDays = min($diff, 31);

        for ($i = 0; $i < $maxDays; $i++) {
            $tglIniStr = date('Y-m-d', $currentUnix);
            $tglLaluStr = date('Y-m-d', $pastUnix);

            $labels[] = date('d M', $currentUnix);
            $dataSekarang[] = isset($dictSekarang[$tglIniStr]) ? $dictSekarang[$tglIniStr] : 0;
            $dataLalu[] = isset($dictLalu[$tglLaluStr]) ? $dictLalu[$tglLaluStr] : 0;

            // Increment strictly by +1 day (86400 seconds)
            $currentUnix = strtotime('+1 day', $currentUnix);
            $pastUnix = strtotime('+1 day', $pastUnix);
        }

        $chartData = [
            'labels' => $labels,
            'data_sekarang' => $dataSekarang,
            'data_lalu' => $dataLalu,
            'label_sekarang' => 'Periode Saat Ini',
            'label_lalu' => 'Periode Bulan Lalu'
        ];

        return $this->render('index', [
            'kpi' => $kpi,
            'chartData' => $chartData,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'filterType' => $filterType
        ]);
    }
}
