<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use app\models\LoginForm;

class SiteController extends Controller
{
    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionHome()
    {
        $this->layout = 'dashboard'; // Use dedicated dashboard layout
        $modul = $this->listModule();
        return $this->render('home', [
            'modul' => $modul
        ]);
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->redirect(['site/home']);
        }

        $this->layout = 'login'; // Use clean login layout (no container wrapper)

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->redirect(['site/home']);
        }

        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->redirect(['site/home']);
    }

    public function actionError()
    {
        $exception = Yii::$app->errorHandler->exception;
        if ($exception !== null) {
            return $this->render('error', ['exception' => $exception]);
        }
    }

    /**
     * List menampilkan modul dan setting redirect
     */
    public function listModule()
    {
        return [
            'Rawat Jalan' => [
                'label' => 'RAWAT JALAN',
                'url' => 'rawatjalan/default/index',
                'icon' => 'bi bi-hospital',
                'colorClass' => 'pmc-green'
            ],
            'Rawat Inap' => [
                'label' => 'RAWAT INAP',
                'url' => 'rawat-jalan/BaseMenu',
                'icon' => 'bi bi-heart-pulse',
                'colorClass' => 'pmc-blue'
            ],
            'Rawat Darurat' => [
                'label' => 'RAWAT DARURAT',
                'url' => 'rawat-jalan/BaseMenu',
                'icon' => 'bi bi-exclamation-square',
                'colorClass' => 'pmc-green'
            ],
            'Kepegawaian' => [
                'label' => 'KEPEGAWAIAN',
                'url' => 'rawat-jalan/BaseMenu',
                'icon' => 'bi bi-person-badge',
                'colorClass' => 'pmc-blue'
            ],
            'Antrian' => [
                'label' => 'SISTEM INFORMASI EKSEKUTIF',
                'url' => 'eksekutif/default/index',
                'icon' => 'bi bi-graph-up-arrow',
                'colorClass' => 'pmc-green'
            ],
            'Keuangan' => [
                'label' => 'KEUANGAN',
                'url' => 'keuangan/default/index',
                'icon' => 'bi bi-wallet2',
                'colorClass' => 'pmc-blue'
            ],
            'Sistem Admin' => [
                'label' => 'SISTEM ADMIN',
                'url' => 'rawat-jalan/BaseMenu',
                'icon' => 'bi bi-gear',
                'colorClass' => 'pmc-green'
            ],
            'Pendaftaran dan Penjadwalan' => [
                'label' => 'PENDAFTARAN & PENJADWALAN',
                'url' => 'rawat-jalan/BaseMenu',
                'icon' => 'bi bi-calendar-check',
                'colorClass' => 'pmc-blue'
            ],
            'Farmasi' => [
                'label' => 'FARMASI / APOTEK',
                'url' => 'farmasi/default/index',
                'icon' => 'bi bi-capsule',
                'colorClass' => 'pmc-green'
            ],
            'Laboratorium' => [
                'label' => 'LABORATORIUM',
                'url' => 'laboratorium/default/index',
                'icon' => 'bi bi-droplet-half',
                'colorClass' => 'pmc-blue'
            ]
        ];
    }

}
