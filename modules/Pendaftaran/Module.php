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
