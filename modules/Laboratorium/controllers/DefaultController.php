<?php

namespace app\modules\laboratorium\controllers;

use app\controllers\BaseController;

class DefaultController extends BaseController
{
    public function actionIndex()
    {
        $bulanIni = date('m');
        $tahunIni = date('Y');

        // Dummy Data KPI
        $kpi = [
            'total_pemeriksaan' => 4520,
            'pertumbuhan_pemeriksaan' => 12.5,
            
            'rata_tat' => 45, // menit
            'pertumbuhan_tat' => -5.2, // turun berarti bagus
            'sla_terpenuhi' => true, // < 60 menit itu true
            
            'pendapatan_lab' => 845000000,
            'pertumbuhan_pendapatan' => 8.4,
            
            'tes_terbanyak_nama' => 'Darah Lengkap (DL)',
            'tes_terbanyak_qty' => 1250,
            'pertumbuhan_tes' => 5.1
        ];

        // Dummy Data Tren TAT Harian (Bulan Ini)
        $hariDalamBulan = date('t');
        $trendDates = [];
        $trendTAT = [];
        for ($i = 1; $i <= $hariDalamBulan; $i++) {
            $trendDates[] = $i;
            $trendTAT[] = rand(35, 65); // fluktuasi TAT harian
        }

        // Dummy Data Volume vs Kapasitas
        $volumeData = [];
        $kapasitasData = [];
        for ($i = 1; $i <= 7; $i++) { // 7 hari terakhir
            $volumeData[] = rand(150, 250);
            $kapasitasData[] = 300; // kapasitas maksimal per hari
        }

        // Dummy Data Top Dokter
        $topDokter = [
            ['nama' => 'dr. Andi Susanto, Sp.PD', 'spesialis' => 'Penyakit Dalam', 'jumlah' => 450],
            ['nama' => 'dr. Budi Setiawan, Sp.A', 'spesialis' => 'Anak', 'jumlah' => 380],
            ['nama' => 'dr. Citra Lestari, Sp.OG', 'spesialis' => 'Kandungan', 'jumlah' => 310],
            ['nama' => 'dr. Dini Pertiwi, Sp.JP', 'spesialis' => 'Jantung', 'jumlah' => 290],
            ['nama' => 'dr. Eko Prasetyo, Umum', 'spesialis' => 'Umum', 'jumlah' => 200],
        ];

        // Dummy Data Stok Kritis
        $stokKritis = [
            ['reagen' => 'Reagen Hematologi Sysmex', 'kategori' => 'Hematologi', 'stok' => 5, 'minimal' => 10, 'status' => 'Kritis'],
            ['reagen' => 'Reagen Gula Darah GOD-PAP', 'kategori' => 'Kimia Klinik', 'stok' => 2, 'minimal' => 15, 'status' => 'Sangat Kritis'],
            ['reagen' => 'Tabung EDTA', 'kategori' => 'BMHP', 'stok' => 50, 'minimal' => 100, 'status' => 'Perhatian'],
            ['reagen' => 'Reagen Asam Urat', 'kategori' => 'Kimia Klinik', 'stok' => 10, 'minimal' => 20, 'status' => 'Kritis'],
        ];

        return $this->render('index', [
            'bulanIni' => $bulanIni,
            'tahunIni' => $tahunIni,
            'kpi' => $kpi,
            'trendDates' => $trendDates,
            'trendTAT' => $trendTAT,
            'volumeData' => $volumeData,
            'kapasitasData' => $kapasitasData,
            'topDokter' => $topDokter,
            'stokKritis' => $stokKritis,
        ]);
    }
}
