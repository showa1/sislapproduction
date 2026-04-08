<?php

namespace app\controllers;

use yii\web\Controller;

class BaseController extends Controller
{
    public $layout = '@app/views/layouts/menu'; // Layout default

    /**
     * Fungsi untuk mengambil menu dari modul.
     */
    protected function getModuleMenu()
    {
        return \Yii::$app->controller->module->menu ?? [];
    }

    /**
     * Override sebelum action dijalankan.
     */
    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }
        
        \Yii::$app->view->params['menuItems'] = $this->getModuleMenu();

        return true;
    }
}
