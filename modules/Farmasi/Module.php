<?php

namespace app\modules\farmasi;

use yii\base\Module as BaseModule;

class Module extends BaseModule
{
    public $controllerNamespace = 'app\modules\farmasi\controllers';

    public $menu = [
        ['label' => 'Penggunaan Obat Perpasien', 'url' => '/farmasi/penggunaan-obat-perpasien/index', 'icon' => 'bi bi-prescription2'],
        ['label' => 'Laporan Persediaan', 'url' => '/farmasi/persediaan/index', 'icon' => 'bi bi-boxes'],
        ['label' => 'Laporan Stok Opname', 'url' => '/farmasi/stokopname/index', 'icon' => 'bi bi-clipboard2-pulse'],
        ['label' => 'Laporan Minimal Stok', 'url' => '/farmasi/minimal-stok/index', 'icon' => 'bi bi-exclamation-triangle'],
        ['label' => 'Laporan Jatuh Tempo', 'url' => '/farmasi/jatuh-tempo/index', 'icon' => 'bi bi-calendar-x'],
    ];
    
    public function init()
    {
        parent::init();
    }
}
