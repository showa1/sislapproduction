<?php

namespace app\modules\eksekutif;

use yii\base\Module as BaseModule;

class Module extends BaseModule
{
    public $controllerNamespace = 'app\modules\eksekutif\controllers';
    public $defaultRoute = 'dashboard';

    public $menu = [
        ['label' => 'Kunjungan Rawat Jalan Detail', 'url' => '/eksekutif/pendapatan/index', 'icon' => 'bi bi-person-lines-fill'],
        ['label' => 'Kunjungan Rawat Darurat Detail', 'url' => '/eksekutif/kunjungan-darurat/index', 'icon' => 'bi bi-heart-pulse'],
        ['label' => 'Kunjungan Fisioterapi Detail', 'url' => '/eksekutif/kunjungan-fisioterapi/index', 'icon' => 'bi bi-person-wheelchair'],
        ['label' => 'Kunjungan MCU Detail', 'url' => '/eksekutif/medical-chekup/index', 'icon' => 'bi bi-clipboard2-pulse'],
        ['label' => 'Kunjungan Laboratorium Detail', 'url' => '/eksekutif/lab/index', 'icon' => 'bi bi-droplet-half'],
        ['label' => 'Kunjungan Radiologi Detail', 'url' => '/eksekutif/radiologi/index', 'icon' => 'bi bi-virus'],
        ['label' => 'Perawatan Intensif Detail', 'url' => '/eksekutif/perawatan-intensif/index', 'icon' => 'bi bi-lungs'],
        ['label' => 'Kunjungan Hemodialisa Detail', 'url' => '/eksekutif/hemodialisa/index', 'icon' => 'bi bi-bandaid'],
        ['label' => 'Kebidanan Kandungan /VK', 'url' => '/eksekutif/kebidanan-kandungan/index', 'icon' => 'bi bi-gender-female'],
        ['label' => 'Kunjungan Rawat Inap <br>Langsung', 'url' => '/eksekutif/rawat-inap/index', 'icon' => 'bi bi-hospital'],
        ['label' => 'Pembelian Obat Langsung', 'url' => '/eksekutif/pembelian-obat/index', 'icon' => 'bi bi-capsule'],
        ['label' => 'Pemakaian Ambulance <br>Langsung', 'url' => '/eksekutif/pemakaian-ambulance/index', 'icon' => 'bi bi-truck'],
        ['label' => 'Kunjungan Berdasarkan <br>Cara Bayar', 'url' => '/eksekutif/cara-bayar/index', 'icon' => 'bi bi-cash-stack'],
        ['label' => 'Cara Pembayaran<br>Berdasarkan Penjamin', 'url' => '/eksekutif/cara-bayar-penjamin/index', 'icon' => 'bi bi-bank'],
        ['label' => 'Laporan Berdasarkan Dokter', 'url' => '/eksekutif/cara-bayar-dokter/index', 'icon' => 'bi bi-file-earmark-medical'],
        ['label' => 'Laporan Per Dokter <br>Per Ruangan', 'url' => '/eksekutif/cara-bayar-dokter-ruangan/index', 'icon' => 'bi bi-hospital-fill'],
        ['label' => 'Laporan Kunjungan <br>Per Ruangan', 'url' => '/eksekutif/kunjungan-per-ruangan/index', 'icon' => 'bi bi-door-open'],
        ['label' => 'Laporan Per Ruangan <br>Per Cara Bayar', 'url' => '/eksekutif/per-ruangan-carabayar/index', 'icon' => 'bi bi-receipt'],
        ['label' => 'Laporan Per Ruangan <br>Per Penjamin', 'url' => '/eksekutif/per-ruangan-penjamin/index', 'icon' => 'bi bi-shield-check'],
        ['label' => 'Kunjungan Pasien<br>Per Kecamatan', 'url' => '/eksekutif/pasien-per-kecamatan/index', 'icon' => 'bi bi-map'],
        ['label' => 'Kunjungan Rawat Inap', 'url' => '/eksekutif/kunjungan-rawat-inap/index', 'icon' => 'bi bi-bed'],
        ['label' => 'Jumlah Kunjungan Berdasarkan<br>Asal Masuk Pasien', 'url' => '/eksekutif/kunjungan-asal-masuk/index', 'icon' => 'bi bi-box-arrow-in-right'],
        ['label' => 'Jumlah Kunjungan Asal Pasien<br>Berdasarkan Kelas', 'url' => '/eksekutif/kunjungan-kelas/index', 'icon' => 'bi bi-tags'],
        ['label' => 'Jumlah Kunjungan Rawat Inap<br>Berdasarkan Cara Bayar', 'url' => '/eksekutif/kunjungan-inap-bayar/index', 'icon' => 'bi bi-wallet2'],
        ['label' => 'Jumlah Kunjungan Rawat Inap<br>Berdasarkan Asal Pasien<br>Dan Cara Bayar', 'url' => '/eksekutif/inap-pasien-bayar/index', 'icon' => 'bi bi-signpost-split'],
        ['label' => 'Kunjungan Rawat Inap<br>Per Kecamatan', 'url' => '/eksekutif/inap-per-kecamatan/index', 'icon' => 'bi bi-pin-map'],
        ['label' => 'Laporan Pemeriksaan <br>Penunjang', 'url' => '/eksekutif/pemeriksaan-penunjang/index', 'icon' => 'bi bi-clipboard-data'],
        ['label' => 'Laporan Kinerja Dokter', 'url' => '/eksekutif/kinerja-dokter/index', 'icon' => 'bi bi-graph-up-arrow'],
    ];
    
    public function init()
    {
        parent::init();
        // Tambahkan kode inisialisasi jika diperlukan
    }
}
