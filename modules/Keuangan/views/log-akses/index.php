<?php
use yii\grid\GridView;
use yii\helpers\Html;

$this->title = 'Audit Log Akses Keuangan';
?>
<div class="card shadow-sm border-0 rounded-lg">
    <div class="card-header bg-white py-3">
        <div class="row align-items-center">
            <div class="col">
                <h5 class="mb-0 fw-bold text-navy" style="color: #0c2340;">
                    <i class="bi bi-shield-check me-2"></i> Audit Log Akses Keuangan
                </h5>
                <p class="text-muted small mb-0">Daftar pengguna yang telah melakukan verifikasi password khusus untuk masuk ke modul ini (Sistem Log File).</p>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'tableOptions' => ['class' => 'table table-hover align-middle mb-0'],
                'headerRowOptions' => ['style' => 'background-color: #f8fafc; border-bottom: 2px solid #e2e8f0;'],
                'summary' => '<div class="p-3 text-muted small">Menampilkan {begin} - {end} dari {totalCount} log akses</div>',
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],
                    [
                        'attribute' => 'nama_pemakai',
                        'label' => 'Nama Pengguna',
                        'contentOptions' => ['class' => 'fw-bold text-dark'],
                    ],
                    [
                        'attribute' => 'ip_address',
                        'label' => 'IP Address',
                    ],
                    [
                        'attribute' => 'waktu',
                        'label' => 'Waktu Akses',
                        'value' => function($model) {
                            return date('d M Y - H:i:s', strtotime($model['waktu']));
                        }
                    ],
                    [
                        'attribute' => 'keterangan',
                        'label' => 'Keterangan',
                    ],
                ],
            ]); ?>
        </div>
    </div>
</div>

<style>
    .text-navy { color: #0c2340; }
    .pagination { padding: 1rem; justify-content: center; }
    .page-item.active .page-link { background-color: #0c2340; border-color: #0c2340; }
</style>
