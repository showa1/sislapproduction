<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this        yii\web\View */
/* @var $tahun       int */
/* @var $caraBayar   string */
/* @var $optCaraBayar array */
/* @var $jumlahTT    int */
/* @var $monthly     array */
/* @var $summary     array */

$this->title = 'Laporan BOR Rawat Inap';
$this->params['breadcrumbs'][] = ['label' => 'Rawat Inap', 'url' => ['/rawatinap/dashboard/index']];
$this->params['breadcrumbs'][] = $this->title;

$bulanLabels = [
    1  => 'Jan', 2  => 'Feb', 3  => 'Mar',
    4  => 'Apr', 5  => 'Mei', 6  => 'Jun',
    7  => 'Jul', 8  => 'Agu', 9  => 'Sep',
    10 => 'Okt', 11 => 'Nov', 12 => 'Des',
];

$resetUrl  = Url::to(['/rawatinap/bor/index']);
$exportUrl = Url::to([
    '/rawatinap/bor/index',
    'tahun'      => $tahun,
    'cara_bayar' => $caraBayar,
    'export'     => '1',
]);

$this->registerCss("
    .summary-card {
        border-radius: 12px;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .summary-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0,45,114,0.15) !important;
    }

    /* Tabel gaya Gambar 1 */
    .tbl-laporan-bor thead th {
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
    .tbl-laporan-bor tbody td {
        vertical-align: middle;
        font-size: 13px;
        border: 1px solid #dee2e6;
        padding: 9px 10px;
    }
    .tbl-laporan-bor td.td-bulan {
        text-align: center;
    }
    .tbl-laporan-bor td.td-total {
        text-align: center;
        font-weight: 700;
        background-color: #e8f4fd;
        color: #002D72;
        white-space: nowrap;
    }
    .tbl-laporan-bor tfoot td {
        background-color: #002D72;
        color: #fff;
        font-weight: 700;
        text-align: center;
        font-size: 13px;
        border: 1px solid #0045a5;
        padding: 10px 8px;
        white-space: nowrap;
    }
    .tbl-laporan-bor tfoot td.td-label {
        text-align: left;
        padding-left: 12px;
    }
    .badge-count {
        display: inline-block;
        min-width: 34px;
        padding: 3px 8px;
        border-radius: 10px;
        font-size: 12px;
        font-weight: 600;
    }
    .badge-count.zero    { background: #e9ecef; color: #6c757d; }
    .badge-count.nonzero { background: #d1ecf1; color: #0c5460; }
    .badge-count.static  { background: #f1f5f9; color: #334155; }
");
?>

<div class="row quick-action-toolbar laporan-bor-page">
    <style>
        .laporan-bor-page select.form-select,
        .laporan-bor-page input.form-control {
            color: #1e293b !important;
            font-weight: 500 !important;
            background-color: #ffffff !important;
            border: 1px solid #cbd5e1 !important;
            outline: none !important;
        }
        .laporan-bor-page select.form-select {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%231e293b' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e") !important;
            background-repeat: no-repeat !important;
            background-position: right 0.75rem center !important;
            background-size: 16px 12px !important;
            padding-right: 2.25rem !important;
        }
        .laporan-bor-page select.form-select option {
            color: #1e293b !important;
            background-color: #ffffff !important;
            font-weight: 500 !important;
        }
    </style>

    <div class="col-md-12">

        <h2 style="color: #002D72; font-weight: bold; margin-bottom: 20px;">
            <i class="bi bi-bar-chart-line me-2"></i>Laporan BOR (Bed Occupancy Rate) Rawat Inap
        </h2>

        <!-- ===== Summary Cards (Gaya Gambar 1) ===== -->
        <div class="row mb-4">
            <!-- Card 1: Kapasitas TT SIOP -->
            <div class="col-md-4 mb-3">
                <div class="card border-0 shadow-sm p-3 summary-card text-white"
                     style="background: linear-gradient(135deg, #002D72 0%, #0056b3 100%);">
                    <div class="d-flex align-items-center">
                        <div class="fs-1 me-3">
                            <i class="bi bi-hospital"></i>
                        </div>
                        <div>
                            <div class="fs-6 opacity-75">Kapasitas Tempat Tidur (SIOP)</div>
                            <div class="fs-3 fw-bold">
                                <?= number_format($jumlahTT) ?> <span class="fs-6 fw-normal">Tempat Tidur</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 2: Rata-rata BOR & Hari Perawatan -->
            <div class="col-md-4 mb-3">
                <div class="card border-0 shadow-sm p-3 summary-card text-white"
                     style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                    <div class="d-flex align-items-center">
                        <div class="fs-1 me-3"><i class="bi bi-percent"></i></div>
                        <div>
                            <div class="fs-6 opacity-75">Rata-Rata BOR Tahun <?= Html::encode($tahun) ?></div>
                            <div class="fs-3 fw-bold">
                                <?= number_format($summary['bor_bulan_aktif'], 2, ',', '.') ?>%
                                <span class="fs-6 fw-normal">
                                    (<?= number_format($summary['total_hari_perawatan'], 1, ',', '.') ?> HP)
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 3: Bulan Aktif -->
            <div class="col-md-4 mb-3">
                <div class="card border-0 shadow-sm p-3 summary-card text-white"
                     style="background: linear-gradient(135deg, #6f42c1 0%, #a855f7 100%);">
                    <div class="d-flex align-items-center">
                        <div class="fs-1 me-3"><i class="bi bi-calendar-check"></i></div>
                        <div>
                            <div class="fs-6 opacity-75">Bulan Aktif (Tahun <?= Html::encode($tahun) ?>)</div>
                            <div class="fs-3 fw-bold">
                                <?= (int)$summary['bulan_aktif'] ?> <span class="fs-6 fw-normal">Bulan (<?= number_format($summary['total_pasien'], 0, ',', '.') ?> Pasien)</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===== Filter Card + Tabel Data ===== -->
        <div class="card border-0 shadow-sm p-4">
            <div class="d-md-flex row m-0 quick-action-btns">

                <?= Html::beginForm(['/rawatinap/bor/index'], 'get', ['id' => 'filter-form']) ?>
                <div class="row g-3 align-items-end">
                    <div class="col-md-2">
                        <label class="form-label text-muted small fw-bold">
                            <i class="bi bi-calendar3 me-1"></i>Tahun Laporan
                        </label>
                        <?= Html::textInput('tahun', $tahun, [
                            'id'    => 'input-tahun',
                            'class' => 'form-control',
                            'type'  => 'number',
                            'min'   => '2000',
                            'max'   => date('Y') + 1,
                        ]) ?>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label text-muted small fw-bold">
                            <i class="bi bi-credit-card me-1"></i>Filter Cara Bayar
                        </label>
                        <select name="cara_bayar" class="form-select">
                            <option value="Semua" <?= ($caraBayar === 'Semua' || empty($caraBayar)) ? 'selected' : '' ?>>
                                Semua Cara Bayar (BOR Global RS)
                            </option>
                            <?php foreach ($optCaraBayar as $cb): ?>
                                <option value="<?= Html::encode($cb) ?>" <?= ($caraBayar === $cb) ? 'selected' : '' ?>>
                                    <?= Html::encode($cb) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="row mt-4">
                    <div class="col-12 d-flex justify-content-start flex-wrap align-items-center">
                        <?= Html::submitButton('<i class="bi bi-filter me-1"></i> Tampilkan Data', [
                            'class' => 'btn btn-primary me-2 mb-2',
                            'style' => 'background-color:#002D72; border-color:#002D72; padding:8px 20px; font-weight:600;',
                        ]) ?>
                        <?= Html::a('<i class="bi bi-arrow-clockwise me-1"></i> Reset Filter', $resetUrl, [
                            'class' => 'btn btn-outline-secondary me-2 mb-2',
                            'style' => 'padding:8px 16px;',
                        ]) ?>
                        <?= Html::a('<i class="bi bi-file-earmark-excel me-1"></i> Export Excel', $exportUrl, [
                            'class' => 'btn btn-success me-2 mb-2',
                            'style' => 'background-color:#28a745; border-color:#28a745; padding:8px 20px; font-weight:600;',
                            'target' => '_blank',
                        ]) ?>
                    </div>
                </div>
                <?= Html::endForm() ?>

                <!-- ===== Tabel Januari - Desember ===== -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped tbl-laporan-bor align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th style="width:40px;">No</th>
                                        <th style="min-width:250px; text-align:left;">Indikator / Komponen BOR</th>
                                        <?php foreach ($bulanLabels as $bLabel): ?>
                                            <th style="width:58px;"><?= $bLabel ?></th>
                                        <?php endforeach; ?>
                                        <th style="width:110px;">Total / Rata-rata</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- 1. Jumlah Pasien Pulang -->
                                    <tr>
                                        <td class="text-center">1</td>
                                        <td><strong>Jumlah Pasien Pulang (KRS)</strong></td>
                                        <?php for ($m = 1; $m <= 12; $m++): ?>
                                            <?php $v = (int)$monthly[$m]['jumlah_pasien']; ?>
                                            <td class="td-bulan">
                                                <?php if ($v > 0): ?>
                                                    <span class="badge-count nonzero"><?= number_format($v, 0, ',', '.') ?></span>
                                                <?php else: ?>
                                                    <span class="badge-count zero">-</span>
                                                <?php endif; ?>
                                            </td>
                                        <?php endfor; ?>
                                        <td class="td-total"><?= number_format($summary['total_pasien'], 0, ',', '.') ?></td>
                                    </tr>

                                    <!-- 2. Total Jam Perawatan -->
                                    <tr>
                                        <td class="text-center">2</td>
                                        <td><strong>Total Jam Perawatan (Jam)</strong></td>
                                        <?php for ($m = 1; $m <= 12; $m++): ?>
                                            <?php $v = (float)$monthly[$m]['total_jam']; ?>
                                            <td class="td-bulan">
                                                <?php if ($v > 0): ?>
                                                    <span class="badge-count nonzero"><?= number_format($v, 1, ',', '.') ?></span>
                                                <?php else: ?>
                                                    <span class="badge-count zero">-</span>
                                                <?php endif; ?>
                                            </td>
                                        <?php endfor; ?>
                                        <td class="td-total"><?= number_format($summary['total_jam'], 1, ',', '.') ?></td>
                                    </tr>

                                    <!-- 3. Total Hari Perawatan (HP = Total Jam / 24) -->
                                    <tr>
                                        <td class="text-center">3</td>
                                        <td><strong>Total Hari Perawatan / HP (Hari)</strong></td>
                                        <?php for ($m = 1; $m <= 12; $m++): ?>
                                            <?php $v = (float)$monthly[$m]['hari_perawatan']; ?>
                                            <td class="td-bulan">
                                                <?php if ($v > 0): ?>
                                                    <span class="badge-count nonzero"><?= number_format($v, 1, ',', '.') ?></span>
                                                <?php else: ?>
                                                    <span class="badge-count zero">-</span>
                                                <?php endif; ?>
                                            </td>
                                        <?php endfor; ?>
                                        <td class="td-total"><?= number_format($summary['total_hari_perawatan'], 1, ',', '.') ?></td>
                                    </tr>

                                    <!-- 4. Kapasitas Tempat Tidur (TT SIOP) -->
                                    <tr>
                                        <td class="text-center">4</td>
                                        <td><strong>Kapasitas Tempat Tidur SIOP (TT)</strong></td>
                                        <?php for ($m = 1; $m <= 12; $m++): ?>
                                            <td class="td-bulan">
                                                <span class="badge-count static"><?= (int)$monthly[$m]['jumlah_tt'] ?></span>
                                            </td>
                                        <?php endfor; ?>
                                        <td class="td-total"><?= (int)$summary['jumlah_tt'] ?> TT</td>
                                    </tr>

                                    <!-- 5. Jumlah Hari dalam Bulan -->
                                    <tr>
                                        <td class="text-center">5</td>
                                        <td><strong>Jumlah Hari dalam Bulan (Hari)</strong></td>
                                        <?php for ($m = 1; $m <= 12; $m++): ?>
                                            <td class="td-bulan">
                                                <span class="badge-count static"><?= (int)$monthly[$m]['jumlah_hari_bulan'] ?></span>
                                            </td>
                                        <?php endfor; ?>
                                        <td class="td-total"><?= (int)$summary['total_hari_kalender'] ?> Hari</td>
                                    </tr>

                                    <!-- 6. Kapasitas Hari Tempat Tidur (TT x Hari) -->
                                    <tr>
                                        <td class="text-center">6</td>
                                        <td><strong>Kapasitas Hari Tempat Tidur (TT &times; Hari)</strong></td>
                                        <?php for ($m = 1; $m <= 12; $m++): ?>
                                            <td class="td-bulan">
                                                <span class="badge-count static"><?= number_format($monthly[$m]['kapasitas_hari_tt'], 0, ',', '.') ?></span>
                                            </td>
                                        <?php endfor; ?>
                                        <td class="td-total"><?= number_format($summary['total_kapasitas_hari_tt'], 0, ',', '.') ?></td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="2" class="td-label">BOR (BED OCCUPANCY RATE) (%)</td>
                                        <?php for ($m = 1; $m <= 12; $m++): ?>
                                            <td><?= number_format($monthly[$m]['bor'], 2, ',', '.') ?>%</td>
                                        <?php endfor; ?>
                                        <td title="Rata-rata BOR pada bulan aktif (Setahun penuh: <?= number_format($summary['bor_tahunan'], 2, ',', '.') ?>%)">
                                            <?= number_format($summary['bor_bulan_aktif'], 2, ',', '.') ?>%
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div class="mt-2 text-muted small">
                            <i class="bi bi-info-circle me-1"></i>
                            Rumus BOR: <strong>(Total Hari Perawatan / (Kapasitas TT SIOP [<?= (int)$jumlahTT ?>] &times; Jumlah Hari dalam Bulan)) &times; 100%</strong>
                            — dimana Total Hari Perawatan dihitung dari <code>Total Jam Rawat / 24</code> pada tahun <strong><?= Html::encode($tahun) ?></strong>.
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

