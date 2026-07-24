<?php

namespace app\modules\Gizi;

use yii\base\Module as BaseModule;

class Module extends BaseModule
{
    public $controllerNamespace = 'app\modules\Gizi\controllers';
    public $defaultRoute = 'dashboard';

    public $menu = [
        ['label' => 'Dashboard Gizi', 'url' => '/gizi/dashboard/index', 'icon' => 'bi bi-speedometer2'],
        ['label' => 'Informasi Penerimaan Gizi', 'url' => '/gizi/informasi-penerimaan-gizi/index', 'icon' => 'bi bi-info-square'],
    ];
    
    public function init()
    {
        parent::init();
    }
}
