<?php

namespace app\modules\laboratorium;

use yii\base\Module as BaseModule;

class Module extends BaseModule
{
    public $controllerNamespace = 'app\modules\laboratorium\controllers';
    public $defaultRoute = 'dashboard';

    public $menu = [
        ['label' => 'Dashboard Laboratorium',           'url' => '/laboratorium/dashboard/index',           'icon' => 'bi bi-speedometer2'],
        ['label' => 'Laporan Pasien &amp; Penjamin',   'url' => '/laboratorium/dashboard/laporan',         'icon' => 'bi bi-table'],
        ['label' => 'Rujukan Pemeriksaan Laboratorium', 'url' => '/laboratorium/rujukan-pemeriksaan/index', 'icon' => 'bi bi-file-earmark-medical'],
        ['label' => 'Laporan Jumlah Pemeriksaan',       'url' => '/laboratorium/laporan-pemeriksaan/index', 'icon' => 'bi bi-bar-chart-fill'],
    ];
    
    public function init()
    {
        parent::init();
    }
}
