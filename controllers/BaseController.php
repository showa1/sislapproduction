<?php

namespace app\controllers;

use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use Yii;

/**
 * BaseController untuk modul Keuangan.
 * Mengunci semua controller Keuangan di backend:
 * - Hanya user yang sudah login
 * - Hanya user yang telah lulus verifikasi PIN sesi ini (keuangan_verified = true)
 */
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
     * Mengecek:
     *   1. User sudah login
     *   2. Session verifikasi Keuangan sudah diberikan (keuangan_verified = true)
     *      dengan masa berlaku 8 jam sejak verifikasi terakhir.
     */
    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        \Yii::$app->view->params['menuItems'] = $this->getModuleMenu();

        // ============================================================
        // KEUANGAN MODULE BACKEND LOCK
        // Hanya berlaku jika controller ini adalah bagian dari modul keuangan
        // ============================================================
        $moduleId = \Yii::$app->controller->module->id ?? '';

        if ($moduleId === 'keuangan') {
            // 1. Pastikan user sudah login
            if (\Yii::$app->user->isGuest) {
                return \Yii::$app->response->redirect(\Yii::$app->user->loginUrl);
            }

            // 2. Cek session flag verifikasi PIN
            $verified   = \Yii::$app->session->get('keuangan_verified', false);
            $verifiedAt = \Yii::$app->session->get('keuangan_verified_at', 0);
            $expiry     = 8 * 60 * 60; // 8 jam

            if (!$verified || (time() - $verifiedAt) > $expiry) {
                // Hapus flag yang mungkin sudah expired
                \Yii::$app->session->remove('keuangan_verified');
                \Yii::$app->session->remove('keuangan_verified_at');

                // Simpan URL yang ingin dituju agar bisa redirect setelah verifikasi
                \Yii::$app->session->setFlash('keuangan_redirect', \Yii::$app->request->absoluteUrl);

                // Kembalikan ke halaman home dengan pesan flash
                \Yii::$app->session->setFlash('warning', 'Silakan verifikasi identitas Anda terlebih dahulu untuk mengakses modul Keuangan.');
                return \Yii::$app->response->redirect(['/site/home']);
            }
        }

        return true;
    }
}
