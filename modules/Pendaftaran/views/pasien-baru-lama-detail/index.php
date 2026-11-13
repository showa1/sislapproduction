<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this         yii\web\View */
/* @var $dateFrom     string */
/* @var $dateTo       string */
/* @var $status       string */
/* @var $search       string */
/* @var $summary      array */
/* @var $dataProvider yii\data\SqlDataProvider */

$this->title = 'Pasien Baru / Lama Detail';
$this->params['breadcrumbs'][] = ['label' => 'Pendaftaran & Penjadwalan', 'url' => ['/pendaftaran/dashboard/index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerCss("
    .summary-card {
        border-radius: 12px;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .summary-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0,45,114,0.15) !important;
    }
    .visit-date-pill {
        display: inline-block;
        background-color: #f1f5f9;
        color: #1e293b;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        padding: 2px 7px;
        font-size: 11.5px;
        font-weight: 500;
        margin: 2px;
        white-space: nowrap;
    }
    .visit-date-pill.periode {
        background-color: #e0f2fe;
        color: #0369a1;
        border-color: #bae6fd;
    }
");
?>

<div class="pendaftaran-pasien-baru-lama-detail">
    <style>
        .pendaftaran-pasien-baru-lama-detail select.form-select,
        .pendaftaran-pasien-baru-lama-detail input.form-control {
            color: #1e293b !important;
            font-weight: 500 !important;
            background-color: #ffffff !important;
            border: 1px solid #cbd5e1 !important;
            outline: none !important;
        }
        .pendaftaran-pasien-baru-lama-detail select.form-select {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%231e293b' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e") !important;
            background-repeat: no-repeat !important;
            background-position: right 0.75rem center !important;
            background-size: 16px 12px !important;
            padding-right: 2.25rem !important;
        }
        .pendaftaran-pasien-baru-lama-detail select.form-select option {
            color: #1e293b !important;
            background-color: #ffffff !important;
            font-weight: 500 !important;
        }
    </style>

    <?php Pjax::begin(['id' => 'pjax-pasien-baru-lama-detail', 'timeout' => false]); ?>

    <h2 style="color: #002D72; font-weight: bold; margin-bottom: 20px;">
        <i class="bi bi-person-lines-fill me-2"></i>Laporan Pasien Baru / Lama Detail
    </h2>

    <!-- ===== 3 Summary Cards ===== -->
    <div class="row mb-4">
        <!-- Card 1: Total Pasien Baru -->
        <div class="col-md-4 mb-3">
            <a href="<?= Url::to(['index', 'date_from' => $dateFrom, 'date_to' => $dateTo, 'search' => $search, 'status' => 'Pasien Baru']) ?>" class="text-decoration-none">
                <div class="card border-0 shadow-sm p-3 summary-card text-white"
                     style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                    <div class="d-flex align-items-center">
                        <div class="fs-1 me-3"><i class="bi bi-person-plus-fill"></i></div>
                        <div>
                            <div class="fs-6 opacity-75">Total Pasien Baru</div>
                            <div class="fs-3 fw-bold">
                                <?= number_format($summary['total_baru'], 0, ',', '.') ?>
                                <span class="fs-6 fw-normal">Pasien (<?= $summary['pct_baru'] ?>%)</span>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Card 2: Total Pasien Lama -->
        <div class="col-md-4 mb-3">
            <a href="<?= Url::to(['index', 'date_from' => $dateFrom, 'date_to' => $dateTo, 'search' => $search, 'status' => 'Pasien Lama']) ?>" class="text-decoration-none">
                <div class="card border-0 shadow-sm p-3 summary-card text-white"
                     style="background: linear-gradient(135deg, #002D72 0%, #0056b3 100%);">
                    <div class="d-flex align-items-center">
                        <div class="fs-1 me-3"><i class="bi bi-person-check-fill"></i></div>
                        <div>
                            <div class="fs-6 opacity-75">Total Pasien Lama</div>
                            <div class="fs-3 fw-bold">
                                <?= number_format($summary['total_lama'], 0, ',', '.') ?>
                                <span class="fs-6 fw-normal">Pasien (<?= $summary['pct_lama'] ?>%)</span>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Card 3: Total Pasien Unik -->
        <div class="col-md-4 mb-3">
            <a href="<?= Url::to(['index', 'date_from' => $dateFrom, 'date_to' => $dateTo, 'search' => $search, 'status' => '']) ?>" class="text-decoration-none">
                <div class="card border-0 shadow-sm p-3 summary-card text-white"
                     style="background: linear-gradient(135deg, #6f42c1 0%, #a855f7 100%);">
                    <div class="d-flex align-items-center">
                        <div class="fs-1 me-3"><i class="bi bi-people-fill"></i></div>
                        <div>
                            <div class="fs-6 opacity-75">Total Pasien Unik (Periode Ini)</div>
                            <div class="fs-3 fw-bold">
                                <?= number_format($summary['total_semua'], 0, ',', '.') ?>
                                <span class="fs-6 fw-normal">Pasien</span>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- ===== Filter Toolbar ===== -->
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
                        <label class="form-label text-muted small fw-bold">Kategori Pasien</label>
                        <?= Html::dropDownList('status', $status, [
                            ''            => 'Semua Kategori (Baru & Lama)',
                            'Pasien Baru' => 'Pasien Baru',
                            'Pasien Lama' => 'Pasien Lama',
                        ], ['class' => 'form-select']) ?>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted small fw-bold">Pencarian (No. RM / Nama Pasien)</label>
                        <input type="text" class="form-control" name="search" placeholder="Masukkan kata kunci..." value="<?= Html::encode($search) ?>">
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary px-3 w-100" style="background-color:#002D72; border-color:#002D72;">
                            <i class="bi bi-filter"></i> Terapkan
                        </button>
                    </div>
                </div>
                <div class="row mt-3 align-items-center">
                    <div class="col-6">
                        <a href="<?= Url::to(['index', 'date_from' => $dateFrom, 'date_to' => $dateTo, 'status' => $status, 'search' => $search, 'export' => 1]) ?>"
                           class="btn btn-sm btn-success" data-pjax="0" target="_blank">
                            <i class="bi bi-file-earmark-excel"></i> Download Excel
                        </a>
                    </div>
                    <div class="col-6 text-end">
                        <a href="<?= Url::to(['index']) ?>" class="btn btn-sm btn-light border text-muted">
                            <i class="bi bi-arrow-clockwise"></i> Reset Filter
                        </a>
                    </div>
                </div>
            <?= Html::endForm() ?>
        </div>
    </div>

    <!-- ===== Tabel Detail Pasien & Histori Kunjungan ===== -->
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-0">
            <div class="px-4 pt-3 pb-2 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0" style="color: #002D72;">
                    <i class="bi bi-table me-1"></i>Detail Histori Kunjungan Pasien Baru / Lama (Periode: <?= Html::encode(date('d-m-Y', strtotime($dateFrom))) ?> s/d <?= Html::encode(date('d-m-Y', strtotime($dateTo))) ?>)
                </h6>
            </div>
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
                            'attribute' => 'pasien_id',
                            'label' => 'Pasien ID',
                            'contentOptions' => ['class' => 'text-muted small'],
                        ],
                        [
                            'attribute' => 'no_rekam_medik',
                            'label' => 'No. RM',
                            'format' => 'raw',
                            'value' => function ($model) {
                                return Html::tag('span', Html::encode($model['no_rekam_medik']), ['class' => 'fw-bold text-primary']);
                            },
                        ],
                        [
                            'attribute' => 'nama_pasien',
                            'label' => 'Nama Pasien',
                            'contentOptions' => ['style' => 'min-width: 150px; font-weight: 600;'],
                        ],
                        [
                            'attribute' => 'tanggal_create_rm',
                            'label' => 'Tanggal Create RM',
                            'contentOptions' => ['class' => 'text-nowrap'],
                        ],
                        [
                            'attribute' => 'tanggal_kunjungan_periode',
                            'label' => 'Tanggal Kunjungan Periode',
                            'format' => 'raw',
                            'contentOptions' => ['style' => 'max-width: 240px; white-space: normal;'],
                            'value' => function ($model) {
                                $raw = $model['tanggal_kunjungan_periode'] ?? '';
                                if ($raw === '') {
                                    return '-';
                                }
                                $dates = array_filter(array_map('trim', explode(',', $raw)));
                                $badges = array_map(function ($d) {
                                    return '<span class="visit-date-pill periode">' . Html::encode($d) . '</span>';
                                }, $dates);
                                return implode(' ', $badges);
                            },
                        ],
                        [
                            'attribute' => 'seluruh_histori_kunjungan',
                            'label' => 'Seluruh Histori Kunjungan',
                            'format' => 'raw',
                            'contentOptions' => ['style' => 'max-width: 340px; white-space: normal;'],
                            'value' => function ($model) {
                                $raw = $model['seluruh_histori_kunjungan'] ?? '';
                                if ($raw === '') {
                                    return '-';
                                }
                                $dates = array_filter(array_map('trim', explode(',', $raw)));
                                $badges = array_map(function ($d) {
                                    return '<span class="visit-date-pill">' . Html::encode($d) . '</span>';
                                }, $dates);
                                return implode(' ', $badges);
                            },
                        ],
                        [
                            'attribute' => 'kategori_pasien',
                            'label' => 'Kategori Pasien',
                            'format' => 'raw',
                            'contentOptions' => ['class' => 'text-center text-nowrap'],
                            'headerOptions' => ['class' => 'text-center'],
                            'value' => function ($model) {
                                if (($model['kategori_pasien'] ?? '') === 'Pasien Baru') {
                                    return '<span class="badge bg-success">Pasien Baru</span>';
                                }
                                return '<span class="badge bg-primary">Pasien Lama</span>';
                            },
                        ],
                    ],
                ]); ?>
            </div>
        </div>
    </div>

    <?php Pjax::end(); ?>
</div>

