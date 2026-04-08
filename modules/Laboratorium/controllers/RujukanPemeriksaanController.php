<?php

namespace app\modules\laboratorium\controllers;

use app\controllers\BaseController;

class RujukanPemeriksaanController extends BaseController
{
    public function actionIndex()
    {
        return $this->render('index');
    }
}
