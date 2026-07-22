<?php

namespace app\modules\RawatInap\controllers;

use app\controllers\BaseController;
use Yii;

class DashboardController extends BaseController
{
    public function actionIndex()
    {
        return $this->render('index');
    }
}
