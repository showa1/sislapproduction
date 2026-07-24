<?php

namespace app\modules\Gizi\controllers;

use app\controllers\BaseController;

class DashboardController extends BaseController
{
    public function actionIndex()
    {
        return $this->render('index');
    }
}
