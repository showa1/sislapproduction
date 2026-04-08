<?php

namespace app\modules\rawatjalan\controllers;

use app\controllers\BaseController;

class DefaultController extends BaseController
{
    /**
     * Menampilkan menu utama modul
     */
    public function actionIndex()
    {
        return $this->render('index');
    }
}
