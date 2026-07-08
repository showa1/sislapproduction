<?php

namespace app\modules\keuangan;

use yii\base\Module as BaseModule;

class Module extends BaseModule
{
    public $controllerNamespace = 'app\modules\keuangan\controllers';

    public $menu = [
        ['label' => 'Analisis Kunjungan Closing', 'url' => '/keuangan/analisis-kunjungan/index', 'icon' => 'bi bi-bar-chart-steps'],
        ['label' => 'Pendapatan Pasien', 'url' => '/keuangan/pendapatan-pasien/index', 'icon' => 'bi bi-cash-stack'],
        ['label' => 'Dashboard Pendapatan RI', 'url' => '/keuangan/pendapatan-rawat-inap/index', 'icon' => 'bi bi-hospital'],
        ['label' => 'Revenue', 'url' => '/keuangan/revenue/index', 'icon' => 'bi bi-graph-up-arrow'],
        ['label' => 'Jasa Dokter', 'url' => '/keuangan/jasa-dokter/index', 'icon' => 'bi bi-person-badge'],
        ['label' => 'Jasa Dokter (Penunjang)', 'url' => '/keuangan/jasa-dokter-penunjang/index', 'icon' => 'bi bi-clipboard2-pulse'],
        ['label' => 'Neraca Saldo', 'url' => '/keuangan/neraca-saldo/index', 'icon' => 'bi bi-scales'],
        ['label' => 'Buku Besar', 'url' => '/keuangan/buku-besar/index', 'icon' => 'bi bi-journal-text'],
        ['label' => 'Jurnal', 'url' => '/keuangan/jurnal/index', 'icon' => 'bi bi-journal-check'],
        ['label' => 'Audit Log Akses', 'url' => '/keuangan/log-akses/index', 'icon' => 'bi bi-shield-lock'],
    ];
    
    public function init()
    {
        parent::init();
        // Tambahkan kode inisialisasi jika diperlukan
    }
}
