<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\Pjax;

$this->title = 'Cara Daftar Pasien';
$this->params['breadcrumbs'][] = ['label' => 'Pendaftaran', 'url' => ['/pendaftaran/dashboard/index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerCss("
    .pendaftaran-cara-daftar .form-select,
    .pendaftaran-cara-daftar .form-control {
        color: #1e293b !important;
        font-weight: 500;
        background-color: #ffffff !important;
    }
    .pendaftaran-cara-daftar .form-select option {
        color: #1e293b !important;
        background-color: #ffffff !important;
        font-weight: 500;
        padding: 8px;
    }
");
?>
<div class="pendaftaran-cara-daftar">
    <style>
        .pendaftaran-cara-daftar select.form-select,
        .pendaftaran-cara-daftar input.form-control {
            color: #1e293b !important;
            font-weight: 500 !important;
            background-color: #ffffff !important;
            border: 1px solid #cbd5e1 !important;
            outline: none !important;
        }
        .pendaftaran-cara-daftar select.form-select {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%231e293b' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e") !important;
            background-repeat: no-repeat !important;
            background-position: right 0.75rem center !important;
            background-size: 16px 12px !important;
            padding-right: 2.25rem !important;
        }
        .pendaftaran-cara-daftar select.form-select option {
            color: #1e293b !important;
            background-color: #ffffff !important;
            font-weight: 500 !important;
        }
    </style>
    <?php Pjax::begin(['id' => 'pjax-grid-cara-daftar', 'timeout' => false]); ?>

    <!-- Filter Toolbar -->
    <div class="card shadow-sm border-0 rounded-4 mb-4">
        <div class="card-body px-4 py-3">
            <?= Html::beginForm(['index'], 'get', ['id' => 'filterForm', 'data-pjax' => '1']) ?>
                <div class="row g-3 align-items-end">
                    <div class="col-md-2">
                        <label class="form-label text-muted small fw-bold">Dari Tanggal</label>
                        <input type="date" class="form-control" name="date_from" value="<?= Html::encode($dateFrom) ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label text-muted small fw-bold">Sampai Tanggal</label>
                        <input type="date" class="form-control" name="date_to" value="<?= Html::encode($dateTo) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted small fw-bold">Status Cara Daftar</label>
                        <?= Html::dropDownList('status', $status ?? '', [
                            '' => 'Semua Status',
                            'mjkn_no_checkin' => 'MJKN - Gagal Check In (Daftar Onsite)',
                            'mjkn_checkin' => 'MJKN - Berhasil Check In',
                            'mjkn_all' => 'Semua Pendaftar Mobile JKN',
                            'onsite' => 'Murni Onsite (Tanpa MJKN)',
                        ], [
                            'class' => 'form-select text-dark fw-medium',
                            'style' => 'color: #1e293b !important; font-weight: 500;',
                        ]) ?>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted small fw-bold">Pencarian (No. RM / Nama Pasien)</label>
                        <input type="text" class="form-control" name="search" placeholder="Masukkan kata kunci..." value="<?= Html::encode($search) ?>">
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary px-3 w-100"><i class="bi bi-filter"></i> Terapkan</button>
                    </div>
                </div>
                <div class="row mt-3 align-items-center">
                    <div class="col-6">
                        <a href="<?= Url::to(['index', 'date_from' => $dateFrom, 'date_to' => $dateTo, 'status' => $status ?? '', 'search' => $search, 'export' => 1]) ?>" class="btn btn-sm btn-success" data-pjax="0" target="_blank">
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

    <!-- KPI Summary Cards Analisa MJKN vs Onsite -->
    <?php if (!empty($summary)): ?>
    <div class="row g-3 mb-4">
        <!-- 1. Total Pasien Terdaftar -->
        <div class="col-md-3 col-sm-6">
            <a href="<?= Url::to(['index', 'date_from' => $dateFrom, 'date_to' => $dateTo, 'search' => $search, 'status' => '']) ?>" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-4 h-100 <?= ($status === '' ? 'border-start border-4 border-primary' : '') ?>">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-muted small fw-bold text-uppercase">Total Kunjungan</span>
                            <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-2 py-1"><i class="bi bi-people-fill"></i> Semua</span>
                        </div>
                        <h3 class="fw-bold mb-1" style="color: #002D72;"><?= number_format($summary['total_kunjungan'], 0, ',', '.') ?></h3>
                        <div class="small text-muted">
                            Murni Onsite: <strong class="text-dark"><?= number_format($summary['murni_onsite'], 0, ',', '.') ?></strong> (<?= $summary['pct_onsite_total'] ?>%)
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- 2. Total Pendaftar Mobile JKN -->
        <div class="col-md-3 col-sm-6">
            <a href="<?= Url::to(['index', 'date_from' => $dateFrom, 'date_to' => $dateTo, 'search' => $search, 'status' => 'mjkn_all']) ?>" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-4 h-100 <?= ($status === 'mjkn_all' ? 'border-start border-4 border-info' : '') ?>">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-muted small fw-bold text-uppercase">Total Daftar MJKN</span>
                            <span class="badge bg-info bg-opacity-10 text-info rounded-pill px-2 py-1"><?= $summary['pct_mjkn_total'] ?>% dari Total</span>
                        </div>
                        <h3 class="fw-bold mb-1" style="color: #0284c7;"><?= number_format($summary['total_mjkn'], 0, ',', '.') ?></h3>
                        <div class="small text-muted">
                            Berhasil + Gagal Check-In MJKN
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- 3. MJKN Berhasil Check-In -->
        <div class="col-md-3 col-sm-6">
            <a href="<?= Url::to(['index', 'date_from' => $dateFrom, 'date_to' => $dateTo, 'search' => $search, 'status' => 'mjkn_checkin']) ?>" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-4 h-100 <?= ($status === 'mjkn_checkin' ? 'border-start border-4 border-success' : '') ?>">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-muted small fw-bold text-uppercase">MJKN Berhasil Check-In</span>
                            <span class="badge bg-success text-white rounded-pill px-2 py-1"><?= $summary['pct_berhasil_dari_mjkn'] ?>% MJKN</span>
                        </div>
                        <h3 class="fw-bold text-success mb-1"><?= number_format($summary['mjkn_berhasil'], 0, ',', '.') ?></h3>
                        <div class="small text-muted">
                            <strong class="text-success"><?= $summary['pct_berhasil_total'] ?>%</strong> dari total seluruh pasien
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- 4. MJKN Gagal Check-In (Daftar Onsite) -->
        <div class="col-md-3 col-sm-6">
            <a href="<?= Url::to(['index', 'date_from' => $dateFrom, 'date_to' => $dateTo, 'search' => $search, 'status' => 'mjkn_no_checkin']) ?>" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-4 h-100 <?= ($status === 'mjkn_no_checkin' ? 'border-start border-4 border-danger' : '') ?>">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-muted small fw-bold text-uppercase">MJKN Gagal Check-In</span>
                            <span class="badge bg-danger text-white rounded-pill px-2 py-1"><?= $summary['pct_gagal_dari_mjkn'] ?>% MJKN</span>
                        </div>
                        <h3 class="fw-bold text-danger mb-1"><?= number_format($summary['mjkn_gagal'], 0, ',', '.') ?></h3>
                        <div class="small text-muted">
                            Dialihkan ke Onsite (<strong class="text-danger"><?= $summary['pct_gagal_total'] ?>%</strong> dari total)
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>
    <?php endif; ?>

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
                                if ($model['is_checkin']) {
                                    return '<span class="badge bg-success">Ya</span>';
                                }
                                if ($model['is_jkn']) {
                                    return '<span class="badge bg-danger" title="Sudah booking MJKN namun gagal check-in, sehingga didaftarkan Onsite">Tidak (Daftar Onsite)</span>';
                                }
                                return '<span class="badge bg-secondary">Tidak</span>';
                            }
                        ],
                    ],
                ]); ?>
            </div>
        </div>
    </div>

    <?php Pjax::end(); ?>
</div>
