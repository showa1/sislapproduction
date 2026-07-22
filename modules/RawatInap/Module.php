<?php

namespace app\modules\RawatInap;

use yii\base\Module as BaseModule;

class Module extends BaseModule
{
    public $controllerNamespace = 'app\modules\RawatInap\controllers';
    public $defaultRoute = 'dashboard';

    public $menu = [
        ['label' => 'Dashboard Rawat Inap', 'url' => '/rawatinap/dashboard/index', 'icon' => 'bi bi-speedometer2'],
        ['label' => 'Informasi Hari Rawat', 'url' => '/rawatinap/informasi-hari-rawat/index', 'icon' => 'bi bi-info-square'],
    ];
    
    public function init()
    {
        parent::init();
    }
}
