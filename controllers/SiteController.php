<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use app\models\LoginForm;
use app\models\User;

class SiteController extends Controller
{
    /**
     * Displays homepage by redirecting to home action.
     *
     * @return Response
     */
    public function actionIndex()
    {
        return $this->redirect(['site/home']);
    }

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
     * Verifikasi password user untuk mengakses modul Keuangan.
     * Dipanggil via AJAX dari modal di halaman home.
     */
    public function actionVerifyKeuangan()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Hanya terima POST request
        if (!Yii::$app->request->isPost) {
            return ['success' => false, 'message' => 'Metode tidak diizinkan.'];
        }

        // Hanya untuk user yang sudah login
        if (Yii::$app->user->isGuest) {
            return ['success' => false, 'message' => 'Session tidak valid. Silakan login ulang.'];
        }

        $password = Yii::$app->request->post('password');
        if (empty($password)) {
            return ['success' => false, 'message' => 'Password tidak boleh kosong.'];
        }

        // Rate limiting sederhana: maks 5 percobaan per sesi
        $attempts = Yii::$app->session->get('keuangan_verify_attempts', 0);
        if ($attempts >= 5) {
            return ['success' => false, 'message' => 'Terlalu banyak percobaan. Silakan logout dan login kembali.'];
        }

        // Verifikasi menggunakan password KHUSUS Keuangan dari params.php
        $hash = Yii::$app->params['keuangan_password_hash'] ?? null;
        
        if ($hash && password_verify($password, $hash)) {
            // Reset percobaan & set flag akses
            Yii::$app->session->set('keuangan_verify_attempts', 0);
            Yii::$app->session->set('keuangan_verified', true);
            Yii::$app->session->set('keuangan_verified_at', time());

            // LOGGING AKSES (File-based karena DB mungkin read-only)
            try {
                $logData = [
                    'waktu' => date('Y-m-d H:i:s'),
                    'id_pemakai' => Yii::$app->user->id,
                    'nama_pemakai' => Yii::$app->user->identity->nama_pemakai ?? 'Unknown',
                    'ip_address' => Yii::$app->request->userIP,
                    'keterangan' => 'Berhasil verifikasi password khusus keuangan'
                ];
                
                $logFile = Yii::getAlias('@runtime/keuangan_access.log');
                $logEntry = json_encode($logData) . PHP_EOL;
                file_put_contents($logFile, $logEntry, FILE_APPEND);
            } catch (\Exception $e) {
                // Abaikan jika gagal logging
            }

            return ['success' => true, 'message' => 'Verifikasi berhasil.'];
        }

        // Password salah, catat percobaan
        Yii::$app->session->set('keuangan_verify_attempts', $attempts + 1);
        $sisa = 5 - ($attempts + 1);
        return [
            'success' => false,
            'message' => "Password salah. Sisa percobaan: {$sisa}."
        ];
    }

    /**
     * Mencabut akses sesi ke Keuangan (untuk keamanan tambahan bila diperlukan).
     */
    public function actionRevokeKeuangan()
    {
        Yii::$app->session->remove('keuangan_verified');
        Yii::$app->session->remove('keuangan_verified_at');
        return $this->redirect(['site/home']);
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
                'colorClass' => 'pmc-blue',
                'comingSoon' => true,
            ],
            'Rawat Darurat' => [
                'label' => 'RAWAT DARURAT',
                'url' => 'rawat-jalan/BaseMenu',
                'icon' => 'bi bi-exclamation-square',
                'colorClass' => 'pmc-green',
                'comingSoon' => true,
            ],
            'Kepegawaian' => [
                'label' => 'KEPEGAWAIAN',
                'url' => 'rawat-jalan/BaseMenu',
                'icon' => 'bi bi-person-badge',
                'colorClass' => 'pmc-blue',
                'comingSoon' => true,
            ],
            'Antrian' => [
                'label' => 'SISTEM INFORMASI EKSEKUTIF',
                'url' => 'eksekutif/dashboard/index',
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
                'colorClass' => 'pmc-green',
                'comingSoon' => true,
            ],
            'Pendaftaran dan Penjadwalan' => [
                'label' => 'PENDAFTARAN & PENJADWALAN',
                'url' => 'rawat-jalan/BaseMenu',
                'icon' => 'bi bi-calendar-check',
                'colorClass' => 'pmc-blue',
                'comingSoon' => true,
            ],
            'Farmasi' => [
                'label' => 'FARMASI / APOTEK',
                'url' => 'farmasi/default/index',
                'icon' => 'bi bi-capsule',
                'colorClass' => 'pmc-green'
            ],
            'Laboratorium' => [
                'label' => 'LABORATORIUM',
                'url'   => 'laboratorium/dashboard/index',
                'icon'  => 'bi bi-droplet-half',
                'colorClass' => 'pmc-blue'
            ]
        ];
    }

}
