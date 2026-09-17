<?php

namespace app\modules\Pendaftaran;

/**
 * Pendaftaran module definition class
 */
class Module extends \yii\base\Module
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'app\modules\Pendaftaran\controllers';
    public $defaultRoute = 'dashboard';

    public $menu = [
        ['label' => 'Cara Daftar', 'url' => '/pendaftaran/cara-daftar/index', 'icon' => 'bi bi-phone'],
        ['label' => 'Monitoring Pasien', 'url' => '/pendaftaran/monitoring-pasien/index', 'icon' => 'bi bi-display'],
        ['label' => 'Data Pasien Meninggal', 'url' => '/pendaftaran/pasien-meninggal/index', 'icon' => 'bi bi-heartbreak'],
        ['label' => 'Penjamin Pasien', 'url' => '/pendaftaran/penjamin-pasien/index', 'icon' => 'bi bi-person-badge'],
        ['label' => 'Rekapitulasi Pasien per Paket MCU', 'url' => '/pendaftaran/rekapitulasi-pasien-mcu/index', 'icon' => 'bi bi-journal-check'],
        ['label' => 'Informasi Paket/Pasien', 'url' => '/pendaftaran/informasi-paket-pasien/index', 'icon' => 'bi bi-person-vcard'],
    ];

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        // custom initialization code goes here
    }
}
