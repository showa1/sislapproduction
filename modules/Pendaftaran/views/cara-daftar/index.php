<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\Pjax;

$this->title = 'Cara Daftar Pasien';
$this->params['breadcrumbs'][] = ['label' => 'Pendaftaran', 'url' => ['/pendaftaran/dashboard/index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="pendaftaran-cara-daftar">
    <?php Pjax::begin(['id' => 'pjax-grid-cara-daftar', 'timeout' => false]); ?>

    <!-- Filter Toolbar -->
    <div class="card shadow-sm border-0 rounded-4 mb-4">
        <div class="card-body px-4 py-3">
            <?= Html::beginForm(['index'], 'get', ['id' => 'filterForm', 'data-pjax' => '1']) ?>
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label text-muted small fw-bold">Dari Tanggal</label>
                        <input type="date" class="form-control" name="date_from" value="<?= Html::encode($dateFrom) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted small fw-bold">Sampai Tanggal</label>
                        <input type="date" class="form-control" name="date_to" value="<?= Html::encode($dateTo) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small fw-bold">Pencarian (No. RM / Nama Pasien)</label>
                        <input type="text" class="form-control" name="search" placeholder="Masukkan kata kunci..." value="<?= Html::encode($search) ?>">
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary px-3 w-100"><i class="bi bi-filter"></i> Terapkan</button>
                    </div>
                </div>
                <div class="row mt-3 align-items-center">
                    <div class="col-6">
                        <a href="<?= Url::to(['index', 'date_from' => $dateFrom, 'date_to' => $dateTo, 'search' => $search, 'export' => 1]) ?>" class="btn btn-sm btn-success" data-pjax="0" target="_blank">
                            <i class="bi bi-file-earmark-excel"></i> Download Excel
                        </a>
                    </div>
                    <div class="col-6 text-end">
                        <a href="<?= Url::to(['index']) ?>" class="btn btn-sm btn-light border text-muted"><i class="bi bi-arrow-clockwise"></i> Reset Filter</a>
                    </div>
                </div>
            <?= Html::endForm() ?>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'layout' => "{items}\n<div class='d-flex justify-content-between align-items-center p-3 border-top'>{summary}\n{pager}</div>",
                    'tableOptions' => ['class' => 'table table-hover table-striped align-middle mb-0'],
                    'headerRowOptions' => ['class' => 'table-light text-muted small text-uppercase'],
                    'pager' => [
                        'class' => \yii\bootstrap5\LinkPager::class,
                        'options' => ['class' => 'pagination mb-0'],
                        'linkOptions' => ['class' => 'page-link'],
                        'pageCssClass' => 'page-item',
                        'disabledListItemSubTagOptions' => ['tag' => 'span', 'class' => 'page-link'],
                    ],
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn', 'header' => 'No'],
                        [
                            'attribute' => 'tgl_pendaftaran',
                            'label' => 'Tgl Pendaftaran',
                            'format' => ['date', 'php:d M Y H:i']
                        ],
                        [
                            'attribute' => 'no_rekam_medik',
                            'label' => 'No. RM',
                            'format' => 'raw',
                            'value' => function ($model) {
                                return Html::tag('span', Html::encode($model['no_rekam_medik']), ['class' => 'fw-bold text-primary']);
                            }
                        ],
                        [
                            'attribute' => 'nama_pasien',
                            'label' => 'Nama Pasien',
                        ],
                        [
                            'attribute' => 'ruangan_nama',
                            'label' => 'Ruangan',
                        ],
                        [
                            'attribute' => 'carabayar_nama',
                            'label' => 'Cara Bayar',
                        ],
                        [
                            'attribute' => 'penjamin_nama',
                            'label' => 'Penjamin',
                        ],
                        [
                            'attribute' => 'is_jkn',
                            'label' => 'Mobile JKN',
                            'format' => 'raw',
                            'contentOptions' => ['class' => 'text-center'],
                            'headerOptions' => ['class' => 'text-center'],
                            'value' => function($model) {
                                return $model['is_jkn'] ? '<span class="badge bg-success">Ya</span>' : '<span class="badge bg-secondary">Tidak</span>';
                            }
                        ],
                        [
                            'attribute' => 'is_checkin',
                            'label' => 'Check In',
                            'format' => 'raw',
                            'contentOptions' => ['class' => 'text-center'],
                            'headerOptions' => ['class' => 'text-center'],
                            'value' => function($model) {
                                return $model['is_checkin'] ? '<span class="badge bg-success">Ya</span>' : '<span class="badge bg-secondary">Tidak</span>';
                            }
                        ],
                    ],
                ]); ?>
            </div>
        </div>
    </div>

    <?php Pjax::end(); ?>
</div>
