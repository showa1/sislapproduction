<?php

use yii\helpers\Html;

$this->title = 'Dashboard Rawat Inap';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="rawatinap-dashboard">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body p-4 text-center">
                    <h3 class="mb-3"><i class="bi bi-heart-pulse text-primary me-2"></i> Selamat Datang di Dashboard Rawat Inap</h3>
                    <p class="text-muted">Modul ini dalam tahap pengembangan awal. Anda dapat mengakses menu <a href="<?= \yii\helpers\Url::to(['/rawatinap/informasi-hari-rawat/index']) ?>">Informasi Hari Rawat</a>.</p>
                </div>
            </div>
        </div>
    </div>
</div>
