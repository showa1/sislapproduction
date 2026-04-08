<?php

namespace app\modules\laboratorium;

use yii\base\Module as BaseModule;

class Module extends BaseModule
{
    public $controllerNamespace = 'app\modules\laboratorium\controllers';

    public $menu = [
        ['label' => 'Rujukan Pemeriksaan Laboratorium', 'url' => '/laboratorium/rujukan-pemeriksaan/index', 'icon' => 'bi bi-file-earmark-medical'],
    ];
    
    public function init()
    {
        parent::init();
    }
}
