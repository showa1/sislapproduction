<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this         yii\web\View */
/* @var $dateFrom     string */
/* @var $dateTo       string */
/* @var $tahun        int */
/* @var $status       string */
/* @var $search       string */
/* @var $summary      array */
/* @var $monthly      array */
/* @var $dataProvider yii\data\SqlDataProvider */

$this->title = 'Pasien Baru / Lama';
$this->params['breadcrumbs'][] = ['label' => 'Pendaftaran & Penjadwalan', 'url' => ['/pendaftaran/dashboard/index']];
$this->params['breadcrumbs'][] = $this->title;

$bulanLabels = [
    1  => 'Jan', 2  => 'Feb', 3  => 'Mar',
    4  => 'Apr', 5  => 'Mei', 6  => 'Jun',
    7  => 'Jul', 8  => 'Agu', 9  => 'Sep',
    10 => 'Okt', 11 => 'Nov', 12 => 'Des',
];

$this->registerCss("
    .summary-card {
        border-radius: 12px;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .summary-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0,45,114,0.15) !important;
    }
    .tbl-rekap-pasien thead th {
        background-color: #002D72;
        color: #fff;
        text-align: center;
        vertical-align: middle;
        font-size: 13px;
        font-weight: 700;
        border: 1px solid #0045a5;
        white-space: nowrap;
        padding: 10px 8px;
    }
    .tbl-rekap-pasien tbody td {
        vertical-align: middle;
        font-size: 13px;
        border: 1px solid #dee2e6;
        padding: 8px 10px;
    }
    .tbl-rekap-pasien td.td-bulan { text-align: center; }
    .tbl-rekap-pasien td.td-total {
        text-align: center;
        font-weight: 700;
        background-color: #e8f4fd;
        color: #002D72;
    }
    .tbl-rekap-pasien tfoot td {
        background-color: #002D72;
        color: #fff;
        font-weight: 700;
        text-align: center;
        font-size: 13px;
        border: 1px solid #0045a5;
        padding: 10px 8px;
    }
    .tbl-rekap-pasien tfoot td.td-label { text-align: left; padding-left: 12px; }
    .badge-count {
        display: inline-block;
        min-width: 32px;
        padding: 2px 7px;
        border-radius: 10px;
        font-size: 12px;
        font-weight: 600;
    }
    .badge-count.zero    { background: #e9ecef; color: #6c757d; }
    .badge-count.nonzero { background: #d1ecf1; color: #0c5460; }
");
?>

<div class="pendaftaran-pasien-baru-lama">
    <style>
        .pendaftaran-pasien-baru-lama select.form-select,
        .pendaftaran-pasien-baru-lama input.form-control {
            color: #1e293b !important;
            font-weight: 500 !important;
            background-color: #ffffff !important;
            border: 1px solid #cbd5e1 !important;
            outline: none !important;
        }
        .pendaftaran-pasien-baru-lama select.form-select {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%231e293b' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e") !important;
            background-repeat: no-repeat !important;
            background-position: right 0.75rem center !important;
            background-size: 16px 12px !important;
            padding-right: 2.25rem !important;
        }
        .pendaftaran-pasien-baru-lama select.form-select option {
            color: #1e293b !important;
            background-color: #ffffff !important;
            font-weight: 500 !important;
        }
    </style>

    <?php Pjax::begin(['id' => 'pjax-pasien-baru-lama', 'timeout' => false]); ?>

    <h2 style="color: #002D72; font-weight: bold; margin-bottom: 20px;">
        <i class="bi bi-person-plus me-2"></i>Laporan Pasien Baru / Lama
    </h2>

    <!-- ===== 3 Summary Cards (Sesuai Rumus Query) ===== -->
    <div class="row mb-4">
        <!-- Card 1: Total Pasien Baru -->
        <div class="col-md-4 mb-3">
            <a href="<?= Url::to(['index', 'date_from' => $dateFrom, 'date_to' => $dateTo, 'search' => $search, 'status' => 'baru']) ?>" class="text-decoration-none">
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
            <a href="<?= Url::to(['index', 'date_from' => $dateFrom, 'date_to' => $dateTo, 'search' => $search, 'status' => 'lama']) ?>" class="text-decoration-none">
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
                            ''     => 'Semua Kategori (Baru & Lama)',
                            'baru' => 'Pasien Baru',
                            'lama' => 'Pasien Lama',
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

    <!-- ===== Tabel Rekapitulasi Bulanan (Jan - Des) ===== -->
    <?php
    $sumBaruTahun  = array_sum(array_column($monthly, 'baru'));
    $sumLamaTahun  = array_sum(array_column($monthly, 'lama'));
    $sumTotalTahun = array_sum(array_column($monthly, 'total'));
    ?>
    <div class="card shadow-sm border-0 rounded-4 mb-4">
        <div class="card-body p-4">
            <h6 class="fw-bold mb-3" style="color: #002D72;">
                <i class="bi bi-bar-chart-steps me-1"></i>Rekapitulasi Bulanan Pasien Baru vs Pasien Lama — Tahun <?= Html::encode($tahun) ?>
            </h6>
            <div class="table-responsive">
                <table class="table table-bordered table-striped tbl-rekap-pasien align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width:40px;">No</th>
                            <th style="min-width:200px; text-align:left;">Kategori Pasien</th>
                            <?php foreach ($bulanLabels as $bLabel): ?>
                                <th style="width:55px;"><?= $bLabel ?></th>
                            <?php endforeach; ?>
                            <th style="width:90px;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- 1. Pasien Baru -->
                        <tr>
                            <td class="text-center">1</td>
                            <td><strong>Pasien Baru</strong></td>
                            <?php for ($m = 1; $m <= 12; $m++): ?>
                                <?php $v = (int)$monthly[$m]['baru']; ?>
                                <td class="td-bulan">
                                    <?php if ($v > 0): ?>
                                        <span class="badge-count nonzero"><?= number_format($v, 0, ',', '.') ?></span>
                                    <?php else: ?>
                                        <span class="badge-count zero">-</span>
                                    <?php endif; ?>
                                </td>
                            <?php endfor; ?>
                            <td class="td-total"><?= number_format($sumBaruTahun, 0, ',', '.') ?></td>
                        </tr>
                        <!-- 2. Pasien Lama -->
                        <tr>
                            <td class="text-center">2</td>
                            <td><strong>Pasien Lama</strong></td>
                            <?php for ($m = 1; $m <= 12; $m++): ?>
                                <?php $v = (int)$monthly[$m]['lama']; ?>
                                <td class="td-bulan">
                                    <?php if ($v > 0): ?>
                                        <span class="badge-count nonzero"><?= number_format($v, 0, ',', '.') ?></span>
                                    <?php else: ?>
                                        <span class="badge-count zero">-</span>
                                    <?php endif; ?>
                                </td>
                            <?php endfor; ?>
                            <td class="td-total"><?= number_format($sumLamaTahun, 0, ',', '.') ?></td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="2" class="td-label">TOTAL PASIEN UNIK PER BULAN</td>
                            <?php for ($m = 1; $m <= 12; $m++): ?>
                                <td><?= number_format((int)$monthly[$m]['total'], 0, ',', '.') ?></td>
                            <?php endfor; ?>
                            <td><?= number_format($sumTotalTahun, 0, ',', '.') ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- ===== Tabel Detail Pasien ===== -->
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-0">
            <div class="px-4 pt-3 pb-2 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0" style="color: #002D72;">
                    <i class="bi bi-list-ul me-1"></i>Daftar Pasien Unik (Periode: <?= Html::encode(date('d M Y', strtotime($dateFrom))) ?> s/d <?= Html::encode(date('d M Y', strtotime($dateTo))) ?>)
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
                            'attribute' => 'tgl_pendaftaran',
                            'label' => 'Tgl Pendaftaran',
                            'format' => ['date', 'php:d M Y H:i'],
                        ],
                        [
                            'attribute' => 'no_pendaftaran',
                            'label' => 'No. Pendaftaran',
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
                        ],
                        [
                            'attribute' => 'tgl_rekam_medik',
                            'label' => 'Tgl Buat RM (create_time)',
                            'format' => ['date', 'php:d M Y'],
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
                            'attribute' => 'kategori_pasien',
                            'label' => 'Status Pasien',
                            'format' => 'raw',
                            'contentOptions' => ['class' => 'text-center'],
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

