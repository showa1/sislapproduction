<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this               yii\web\View */
/* @var $tahun              int */
/* @var $caraBayar          string */
/* @var $ruangan            string */
/* @var $optCaraBayar       array */
/* @var $optRuangan         array */
/* @var $monthly            array */
/* @var $summary            array */
/* @var $breakdownCaraBayar array */

$this->title = 'Laporan LOS Rawat Inap';
$this->params['breadcrumbs'][] = ['label' => 'Rawat Inap', 'url' => ['/rawatinap/dashboard/index']];
$this->params['breadcrumbs'][] = $this->title;

$bulanLabels = [
    1  => 'Jan', 2  => 'Feb', 3  => 'Mar',
    4  => 'Apr', 5  => 'Mei', 6  => 'Jun',
    7  => 'Jul', 8  => 'Agu', 9  => 'Sep',
    10 => 'Okt', 11 => 'Nov', 12 => 'Des',
];

$resetUrl  = Url::to(['/rawatinap/los/index']);
$exportUrl = Url::to([
    '/rawatinap/los/index',
    'tahun'        => $tahun,
    'cara_bayar'   => $caraBayar,
    'ruangan_nama' => $ruangan,
    'export'       => '1',
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
    .tbl-laporan-los thead th {
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
    .tbl-laporan-los tbody td {
        vertical-align: middle;
        font-size: 13px;
        border: 1px solid #dee2e6;
        padding: 9px 10px;
    }
    .tbl-laporan-los td.td-bulan {
        text-align: center;
    }
    .tbl-laporan-los td.td-total {
        text-align: center;
        font-weight: 700;
        background-color: #e8f4fd;
        color: #002D72;
        white-space: nowrap;
    }
    .tbl-laporan-los tfoot td {
        background-color: #002D72;
        color: #fff;
        font-weight: 700;
        text-align: center;
        font-size: 13px;
        border: 1px solid #0045a5;
        padding: 10px 8px;
        white-space: nowrap;
    }
    .tbl-laporan-los tfoot td.td-label {
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
");
?>

<div class="row quick-action-toolbar laporan-los-page">
    <style>
        .laporan-los-page select.form-select,
        .laporan-los-page input.form-control {
            color: #1e293b !important;
            font-weight: 500 !important;
            background-color: #ffffff !important;
            border: 1px solid #cbd5e1 !important;
            outline: none !important;
        }
        .laporan-los-page select.form-select {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%231e293b' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e") !important;
            background-repeat: no-repeat !important;
            background-position: right 0.75rem center !important;
            background-size: 16px 12px !important;
            padding-right: 2.25rem !important;
        }
        .laporan-los-page select.form-select option {
            color: #1e293b !important;
            background-color: #ffffff !important;
            font-weight: 500 !important;
        }
    </style>

    <div class="col-md-12">

        <h2 style="color: #002D72; font-weight: bold; margin-bottom: 20px;">
            <i class="bi bi-clock-history me-2"></i>Laporan LOS (Length of Stay) Rawat Inap
        </h2>

        <!-- ===== Summary Cards (Gaya Gambar 1) ===== -->
        <div class="row mb-4">
            <!-- Card 1: Rata-Rata LOS (Hari & Jam) -->
            <div class="col-md-4 mb-3">
                <div class="card border-0 shadow-sm p-3 summary-card text-white"
                     style="background: linear-gradient(135deg, #002D72 0%, #0056b3 100%);">
                    <div class="d-flex align-items-center">
                        <div class="fs-1 me-3">
                            <i class="bi bi-clock-history"></i>
                        </div>
                        <div>
                            <div class="fs-6 opacity-75">Rata-Rata LOS Tahun <?= Html::encode($tahun) ?></div>
                            <div class="fs-3 fw-bold">
                                <?= number_format($summary['rata_los_hari'], 1, ',', '.') ?> <span class="fs-6 fw-normal">Hari (<?= number_format($summary['rata_los_jam'], 1, ',', '.') ?> Jam)</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 2: Total Pasien Pulang & Total Hari Perawatan -->
            <div class="col-md-4 mb-3">
                <div class="card border-0 shadow-sm p-3 summary-card text-white"
                     style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                    <div class="d-flex align-items-center">
                        <div class="fs-1 me-3"><i class="bi bi-people-fill"></i></div>
                        <div>
                            <div class="fs-6 opacity-75">Total Pasien Pulang (KRS) <?= Html::encode($tahun) ?></div>
                            <div class="fs-3 fw-bold">
                                <?= number_format($summary['total_pasien'], 0, ',', '.') ?>
                                <span class="fs-6 fw-normal">
                                    Pasien (<?= number_format($summary['total_hari_perawatan'], 1, ',', '.') ?> HP)
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
                                <?= (int)$summary['bulan_aktif'] ?> <span class="fs-6 fw-normal">Bulan</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===== Filter Card + Tabel Data ===== -->
        <div class="card border-0 shadow-sm p-4">
            <div class="d-md-flex row m-0 quick-action-btns">

                <?= Html::beginForm(['/rawatinap/los/index'], 'get', ['id' => 'filter-form']) ?>
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
                                Semua Cara Bayar
                            </option>
                            <?php foreach ($optCaraBayar as $cb): ?>
                                <option value="<?= Html::encode($cb) ?>" <?= ($caraBayar === $cb) ? 'selected' : '' ?>>
                                    <?= Html::encode($cb) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label text-muted small fw-bold">
                            <i class="bi bi-door-open me-1"></i>Filter Ruangan / Bangsal
                        </label>
                        <select name="ruangan_nama" class="form-select">
                            <option value="Semua" <?= ($ruangan === 'Semua' || empty($ruangan)) ? 'selected' : '' ?>>
                                Semua Ruangan Rawat Inap
                            </option>
                            <?php foreach ($optRuangan as $rm): ?>
                                <option value="<?= Html::encode($rm) ?>" <?= ($ruangan === $rm) ? 'selected' : '' ?>>
                                    <?= Html::encode($rm) ?>
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

                <!-- ===== Tabel 1: Indikator & Komponen LOS (Januari - Desember) ===== -->
                <div class="row mt-4">
                    <div class="col-12">
                        <h6 class="fw-bold mb-2" style="color: #002D72;">
                            <i class="bi bi-table me-1"></i>Rekapitulasi Komponen &amp; Rata-Rata LOS Bulanan (Januari – Desember <?= Html::encode($tahun) ?>)
                        </h6>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped tbl-laporan-los align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th style="width:40px;">No</th>
                                        <th style="min-width:250px; text-align:left;">Indikator / Komponen LOS</th>
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
                                        <td><strong>Jumlah Pasien Pulang / KRS (Pasien)</strong></td>
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

                                    <!-- 3. Total Hari Perawatan / Lama Dirawat -->
                                    <tr>
                                        <td class="text-center">3</td>
                                        <td><strong>Total Hari Perawatan / Lama Dirawat (Hari)</strong></td>
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

                                    <!-- 4. Rata-rata LOS dalam Jam -->
                                    <tr>
                                        <td class="text-center">4</td>
                                        <td><strong>Rata-Rata LOS dalam Jam (Jam / Pasien)</strong></td>
                                        <?php for ($m = 1; $m <= 12; $m++): ?>
                                            <?php $v = (float)$monthly[$m]['los_jam']; ?>
                                            <td class="td-bulan">
                                                <?php if ($v > 0): ?>
                                                    <span class="badge-count nonzero"><?= number_format($v, 1, ',', '.') ?></span>
                                                <?php else: ?>
                                                    <span class="badge-count zero">-</span>
                                                <?php endif; ?>
                                            </td>
                                        <?php endfor; ?>
                                        <td class="td-total"><?= number_format($summary['rata_los_jam'], 1, ',', '.') ?> Jam</td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="2" class="td-label">RATA-RATA LOS / ALOS (HARI)</td>
                                        <?php for ($m = 1; $m <= 12; $m++): ?>
                                            <td><?= number_format($monthly[$m]['los_hari'], 1, ',', '.') ?> Hari</td>
                                        <?php endfor; ?>
                                        <td><?= number_format($summary['rata_los_hari'], 1, ',', '.') ?> Hari</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div class="mt-2 text-muted small">
                            <i class="bi bi-info-circle me-1"></i>
                            Rumus Rata-Rata LOS (Hari): <strong>Total Hari Perawatan (Total Jam / 24) &divide; Jumlah Pasien Pulang (KRS)</strong>
                            — tahun <strong><?= Html::encode($tahun) ?></strong>.
                        </div>
                    </div>
                </div>

                <!-- ===== Tabel 2: Breakdown Rata-Rata LOS (Hari) per Cara Bayar ===== -->
                <?php if (!empty($breakdownCaraBayar)): ?>
                <div class="row mt-4 pt-2">
                    <div class="col-12">
                        <h6 class="fw-bold mb-2" style="color: #002D72;">
                            <i class="bi bi-credit-card-2-front me-1"></i>Rincian Rata-Rata LOS (Hari) Berdasarkan Cara Bayar (Januari – Desember <?= Html::encode($tahun) ?>)
                        </h6>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped tbl-laporan-los align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th style="width:40px;">No</th>
                                        <th style="min-width:250px; text-align:left;">Cara Bayar</th>
                                        <?php foreach ($bulanLabels as $bLabel): ?>
                                            <th style="width:58px;"><?= $bLabel ?></th>
                                        <?php endforeach; ?>
                                        <th style="width:110px;">Rata-Rata LOS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($breakdownCaraBayar as $idx => $cbRow): ?>
                                    <tr>
                                        <td class="text-center"><?= $idx + 1 ?></td>
                                        <td>
                                            <strong><?= Html::encode($cbRow['nama']) ?></strong>
                                            <span class="text-muted small ms-1">(<?= number_format($cbRow['total_pasien'], 0, ',', '.') ?> Pasien)</span>
                                        </td>
                                        <?php for ($m = 1; $m <= 12; $m++): ?>
                                            <?php
                                            $losVal    = (float)($cbRow['bulan'][$m]['los_hari'] ?? 0);
                                            $pasienVal = (int)($cbRow['bulan'][$m]['pasien'] ?? 0);
                                            ?>
                                            <td class="td-bulan" title="<?= $pasienVal ?> Pasien">
                                                <?php if ($pasienVal > 0): ?>
                                                    <span class="badge-count nonzero"><?= number_format($losVal, 1, ',', '.') ?></span>
                                                <?php else: ?>
                                                    <span class="badge-count zero">-</span>
                                                <?php endif; ?>
                                            </td>
                                        <?php endfor; ?>
                                        <td class="td-total"><?= number_format($cbRow['rata_los'], 1, ',', '.') ?> Hari</td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="2" class="td-label">RATA-RATA LOS KESELURUHAN</td>
                                        <?php for ($m = 1; $m <= 12; $m++): ?>
                                            <td><?= number_format($monthly[$m]['los_hari'], 1, ',', '.') ?></td>
                                        <?php endfor; ?>
                                        <td><?= number_format($summary['rata_los_hari'], 1, ',', '.') ?> Hari</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

            </div>
        </div>

    </div>
</div>

