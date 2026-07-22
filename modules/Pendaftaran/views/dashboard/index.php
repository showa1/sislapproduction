<?php

use yii\helpers\Html;

$this->title = 'Ringkasan Pendaftaran & Penjadwalan';
?>

<div class="row">
    <div class="col-md-12">
        <h2 style="color: #002D72; font-weight: bold; margin-bottom: 20px;">Dashboard Pendaftaran & Penjadwalan</h2>
        <p class="text-muted">Periode: Bulan <?= Html::encode($monthName) ?></p>

        <div class="row mt-4">
            <div class="col-md-4 stretch-card grid-margin">
                <div class="card card-img-holder text-white" style="background: linear-gradient(135deg, #002D72, #29ABE2); border-radius: 15px; border: none; box-shadow: 0 4px 15px rgba(0, 45, 114, 0.2);">
                    <div class="card-body">
                        <h4 class="font-weight-normal mb-3">Total Kunjungan <i class="bi bi-people mdi-24px float-right"></i></h4>
                        <h2 class="mb-5"><?= number_format($totalKunjungan, 0, ',', '.') ?></h2>
                        <h6 class="card-text">Pasien terdaftar bulan ini</h6>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 stretch-card grid-margin">
                <div class="card card-img-holder text-white" style="background: linear-gradient(135deg, #10B981, #6DC536); border-radius: 15px; border: none; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.2);">
                    <div class="card-body">
                        <h4 class="font-weight-normal mb-3">Total Pasien Unik <i class="bi bi-person-check mdi-24px float-right"></i></h4>
                        <h2 class="mb-5"><?= number_format($totalPasien, 0, ',', '.') ?></h2>
                        <h6 class="card-text">Pasien berbeda bulan ini</h6>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <div class="card shadow-sm border-0" style="border-radius: 15px;">
                    <div class="card-body">
                        <h4 class="card-title text-primary"><i class="bi bi-info-circle"></i> Info</h4>
                        <p>Silakan gunakan menu <strong>Laporan</strong> di sidebar sebelah kiri untuk melihat laporan-laporan detail terkait pendaftaran dan penjadwalan, seperti <strong>Data Pasien Meninggal</strong>.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
