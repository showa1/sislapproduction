<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this        yii\web\View */
/* @var $activeTab   string ('mcu' | 'program') */
/* @var $data        array */
/* @var $statusCari  bool */
/* @var $tahun       int */
/* @var $selectedIds int[] */
/* @var $itemList    array [{id, nama}] */
/* @var $grandTotal  array */

$this->title = 'Laporan Paket MCU & Program Paket';
$this->params['breadcrumbs'][] = ['label' => 'Pendaftaran & Penjadwalan', 'url' => ['/pendaftaran']];
$this->params['breadcrumbs'][] = $this->title;

// Register Select2 (menggunakan vendor lokal)
$this->registerCssFile('@web/template/vendors/select2/select2.min.css');
$this->registerCssFile('@web/template/vendors/select2-bootstrap-theme/select2-bootstrap.min.css');
$this->registerJsFile('@web/template/vendors/select2/select2.min.js', ['depends' => [\yii\web\JqueryAsset::class]]);

$bulanLabels = [
    'bln_01' => 'Jan', 'bln_02' => 'Feb', 'bln_03' => 'Mar',
    'bln_04' => 'Apr', 'bln_05' => 'Mei', 'bln_06' => 'Jun',
    'bln_07' => 'Jul', 'bln_08' => 'Agu', 'bln_09' => 'Sep',
    'bln_10' => 'Okt', 'bln_11' => 'Nov', 'bln_12' => 'Des',
];

$resetUrl    = Url::to(['/pendaftaran/laporan-paket-mcu/index', 'tab' => $activeTab]);
$cleanTabUrl = Url::to(['/pendaftaran/laporan-paket-mcu/index', 'tab' => $activeTab]);
$urlExport   = Url::to(['/pendaftaran/laporan-paket-mcu/export']);

$isProgramTab = ($activeTab === 'program');

$this->registerCss("
    .custom-gridview thead th {
        background-color: #002D72;
        color: white;
        font-size: 13px;
        text-align: center;
        font-weight: bold;
        border-bottom: 2px solid #dee2e6;
        vertical-align: middle;
        white-space: nowrap;
    }
    .custom-gridview tbody td { vertical-align: middle; font-size: 13px; }
    
    /* Nav Tabs */
    .paket-nav-tabs {
        border-bottom: 2px solid #dee2e6;
        margin-bottom: 22px;
        gap: 8px;
    }
    .paket-nav-tabs .nav-link {
        color: #64748b;
        font-weight: 600;
        font-size: 15px;
        border: none;
        border-bottom: 3px solid transparent;
        padding: 11px 22px;
        background: transparent;
        transition: all 0.2s ease-in-out;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
    }
    .paket-nav-tabs .nav-link:hover {
        color: #002D72;
        border-bottom-color: #cbd5e1;
    }
    .paket-nav-tabs .nav-link.active {
        color: #002D72;
        border-bottom-color: #002D72;
        background: transparent;
        font-weight: 700;
    }

    .summary-card {
        border-radius: 12px;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .summary-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0,45,114,0.15) !important;
    }

    /* Select2 */
    .select2-container--bootstrap .select2-selection--multiple {
        min-height: 42px !important;
        border: 1px solid #ced4da !important;
        border-radius: 8px !important;
        padding: 4px 8px !important;
        background-color: #ffffff !important;
        box-shadow: none !important;
    }
    .select2-container--bootstrap.select2-container--focus .select2-selection--multiple {
        border-color: #002D72 !important;
        box-shadow: 0 0 0 3px rgba(0,45,114,0.12) !important;
    }
    .select2-container--bootstrap .select2-selection--multiple .select2-selection__choice {
        background-color: #002D72 !important;
        border: none !important;
        color: #ffffff !important;
        border-radius: 20px !important;
        padding: 4px 12px !important;
        font-size: 13px !important;
        font-weight: 500 !important;
        margin-right: 6px !important;
        margin-top: 3px !important;
        display: inline-flex !important;
        align-items: center !important;
        box-shadow: 0 2px 4px rgba(0,45,114,0.2);
    }
    .select2-container--bootstrap .select2-selection--multiple .select2-selection__choice__remove {
        color: #ffffff !important;
        margin-right: 6px !important;
        font-weight: bold;
        cursor: pointer;
        opacity: 0.8;
    }
    .select2-container--bootstrap .select2-selection--multiple .select2-selection__choice__remove:hover {
        opacity: 1;
        color: #ffc107 !important;
    }
    .select2-dropdown {
        border-radius: 8px !important;
        box-shadow: 0 8px 24px rgba(0,0,0,0.12) !important;
        border: 1px solid #ced4da !important;
        z-index: 1060 !important;
    }
    .select2-results__option--highlighted[aria-selected] {
        background-color: #002D72 !important;
        color: #ffffff !important;
    }
    .select2-search__field { font-size: 13px !important; }

    /* Table */
    .tbl-laporan-paket thead th {
        background-color: #002D72;
        color: #fff;
        text-align: center;
        vertical-align: middle;
        font-size: 13px;
        font-weight: 700;
        border: 1px solid #0045a5;
        white-space: nowrap;
    }
    .tbl-laporan-paket tbody td {
        vertical-align: middle;
        font-size: 13px;
        border: 1px solid #dee2e6;
    }
    .tbl-laporan-paket td.td-bulan { text-align: center; }
    .tbl-laporan-paket td.td-total {
        text-align: center;
        font-weight: 700;
        background-color: #e8f4fd;
        color: #002D72;
    }
    .tbl-laporan-paket tfoot td {
        background-color: #002D72;
        color: #fff;
        font-weight: 700;
        text-align: center;
        font-size: 13px;
        border: 1px solid #0045a5;
    }
    .tbl-laporan-paket tfoot td.td-label { text-align: left; }
    .badge-count { display:inline-block; min-width:30px; padding:2px 7px; border-radius:10px; font-size:12px; font-weight:600; }
    .badge-count.zero    { background:#e9ecef; color:#6c757d; }
    .badge-count.nonzero { background:#d1ecf1; color:#0c5460; }
");
?>

<div class="row quick-action-toolbar">
<div class="col-md-12">

    <h2 style="color: #002D72; font-weight: bold; margin-bottom: 20px;">
        <i class="bi bi-bar-chart-line me-2"></i>Laporan Paket MCU &amp; Program Paket
    </h2>

    <!-- ===== Nav Tabs ===== -->
    <ul class="nav paket-nav-tabs" id="tabLaporanPaket">
        <li class="nav-item">
            <a class="nav-link <?= !$isProgramTab ? 'active' : '' ?>" 
               href="<?= Url::to(['/pendaftaran/laporan-paket-mcu/index', 'tab' => 'mcu']) ?>">
                <i class="bi bi-heart-pulse me-2"></i>Paket MCU
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $isProgramTab ? 'active' : '' ?>" 
               href="<?= Url::to(['/pendaftaran/laporan-paket-mcu/index', 'tab' => 'program']) ?>">
                <i class="bi bi-journal-medical me-2"></i>Program Paket / Tindakan
            </a>
        </li>
    </ul>

    <!-- ===== Summary Cards ===== -->
    <?php
    $totalPasien = (int)($grandTotal['total'] ?? 0);
    $jumlahItem = count($data);
    $totalAktifBln = $statusCari
        ? count(array_filter(array_keys($bulanLabels), fn($k) => ($grandTotal[$k] ?? 0) > 0))
        : 0;
    ?>
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm p-3 summary-card text-white"
                 style="background: linear-gradient(135deg, #002D72 0%, #0056b3 100%);">
                <div class="d-flex align-items-center">
                    <div class="fs-1 me-3">
                        <i class="<?= $isProgramTab ? 'bi bi-card-checklist' : 'bi bi-box-seam' ?>"></i>
                    </div>
                    <div>
                        <div class="fs-6 opacity-75">
                            <?= $isProgramTab ? 'Tindakan Ditampilkan' : 'Paket Ditampilkan' ?>
                        </div>
                        <div class="fs-3 fw-bold">
                            <?= $jumlahItem ?> <span class="fs-6 fw-normal"><?= $isProgramTab ? 'Tindakan' : 'Paket' ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm p-3 summary-card text-white"
                 style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                <div class="d-flex align-items-center">
                    <div class="fs-1 me-3"><i class="bi bi-people-fill"></i></div>
                    <div>
                        <div class="fs-6 opacity-75">Total Pasien Tahun <?= $tahun ?></div>
                        <div class="fs-3 fw-bold"><?= number_format($totalPasien) ?> <span class="fs-6 fw-normal">Pasien</span></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm p-3 summary-card text-white"
                 style="background: linear-gradient(135deg, #6f42c1 0%, #a855f7 100%);">
                <div class="d-flex align-items-center">
                    <div class="fs-1 me-3"><i class="bi bi-calendar-check"></i></div>
                    <div>
                        <div class="fs-6 opacity-75">Bulan Aktif</div>
                        <div class="fs-3 fw-bold"><?= $totalAktifBln ?> <span class="fs-6 fw-normal">Bulan</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== Filter Card ===== -->
    <div class="card border-0 shadow-sm p-4">
        <div class="d-md-flex row m-0 quick-action-btns">

            <?= Html::beginForm(['/pendaftaran/laporan-paket-mcu/index'], 'get', ['id' => 'filter-form']) ?>
            <?= Html::hiddenInput('tab', $activeTab) ?>
            <?= Html::hiddenInput('cari', '1') ?>

            <!-- Baris: Tahun + Pilih Item (Tindakan / Paket) -->
            <div class="row g-3">
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

                <div class="col-md-6">
                    <label class="form-label text-muted small fw-bold">
                        <i class="bi bi-check2-square me-1"></i>
                        <?= $isProgramTab ? 'Pilih Tindakan yang Ditampilkan' : 'Pilih Paket yang Ditampilkan' ?>
                    </label>
                    <?php
                    // Buat options array untuk dropdown
                    $itemOptions = [];
                    foreach ($itemList as $it) {
                        $itemOptions[(int)$it['id']] = $it['nama'];
                    }
                    $selectedValues = $statusCari ? $selectedIds : [];
                    $fieldName = $isProgramTab ? 'tindakan_ids[]' : 'paket_ids[]';
                    $placeholder = $isProgramTab 
                        ? 'Ketik atau pilih nama tindakan (misal: Anastomose, CITO, Scaling)...'
                        : 'Ketik atau pilih nama paket MCU...';
                    ?>
                    <?= Html::dropDownList($fieldName, $selectedValues, $itemOptions, [
                        'class'            => 'form-select select2-filter-item',
                        'id'               => 'select2-filter-item',
                        'multiple'         => true,
                        'data-placeholder' => $placeholder,
                    ]) ?>
                    <small class="text-muted" style="font-size:11px;">
                        <?= $isProgramTab
                            ? 'Kosongkan untuk menggunakan tindakan default (atau ketik &amp; pilih tindakan di atas).'
                            : 'Kosongkan untuk menggunakan paket default (ID 58, 65, 75).'
                        ?>
                    </small>
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
                    <?= Html::button('<i class="bi bi-file-earmark-excel me-1"></i> Export Excel', [
                        'id'    => 'btn-export',
                        'class' => 'btn btn-success me-2 mb-2',
                        'style' => 'background-color:#28a745; border-color:#28a745; padding:8px 20px; font-weight:600;',
                    ]) ?>
                </div>
            </div>
            <?= Html::endForm() ?>

            <!-- ===== Data / Empty State ===== -->
            <div class="row mt-4">
                <div class="col-12">
                    <?php if (!$statusCari): ?>
                    <!-- Empty state -->
                    <div class="text-center py-5 border rounded-3 bg-light my-2 shadow-sm">
                        <i class="bi bi-funnel d-block mb-3" style="font-size:3rem; color:#002D72;"></i>
                        <h5 class="fw-bold text-dark mb-2">Silakan Tentukan Filter Laporan</h5>
                        <p class="text-muted small mb-0">
                            Tentukan Tahun, pilih <?= $isProgramTab ? 'Tindakan' : 'Paket' ?> yang ingin ditampilkan, lalu klik tombol
                            <strong><i class="bi bi-filter"></i> Tampilkan Data</strong> untuk memuat laporan 
                            <strong><?= $isProgramTab ? 'Tindakan (Program Paket)' : 'Paket MCU' ?></strong>.
                        </p>
                    </div>
                    <?php elseif (empty($data)): ?>
                    <!-- No data -->
                    <div class="text-center py-5 border rounded-3 bg-light my-2 shadow-sm">
                        <i class="bi bi-inbox d-block mb-3" style="font-size:3rem; color:#6c757d;"></i>
                        <h5 class="fw-bold text-muted mb-2">Tidak Ada Data</h5>
                        <p class="text-muted small mb-0">
                            Tidak ditemukan data untuk <?= $isProgramTab ? 'tindakan' : 'paket' ?> &amp; tahun yang dipilih.
                        </p>
                    </div>
                    <?php else: ?>
                    <!-- Tabel data -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped tbl-laporan-paket align-middle mb-0">
                            <thead>
                                <tr>
                                    <th style="width:40px;">No</th>
                                    <th style="min-width:240px; text-align:left;">
                                        <?= $isProgramTab ? 'Nama Tindakan' : 'Nama Paket' ?>
                                    </th>
                                    <?php foreach ($bulanLabels as $bLabel): ?>
                                    <th style="width:52px;"><?= $bLabel ?></th>
                                    <?php endforeach; ?>
                                    <th style="width:64px;">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data as $i => $row): ?>
                                <tr>
                                    <td class="text-center"><?= $i + 1 ?></td>
                                    <td><strong><?= Html::encode($row['nama']) ?></strong></td>
                                    <?php foreach (array_keys($bulanLabels) as $bKey): ?>
                                    <?php $val = (int)($row[$bKey] ?? 0); ?>
                                    <td class="td-bulan">
                                        <?php if ($val > 0): ?>
                                        <span class="badge-count nonzero"><?= $val ?></span>
                                        <?php else: ?>
                                        <span class="badge-count zero">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <?php endforeach; ?>
                                    <td class="td-total"><?= number_format((int)($row['total'] ?? 0)) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="2" class="td-label">TOTAL</td>
                                    <?php foreach (array_keys($bulanLabels) as $bKey): ?>
                                    <td><?= (int)($grandTotal[$bKey] ?? 0) ?></td>
                                    <?php endforeach; ?>
                                    <td><?= number_format((int)($grandTotal['total'] ?? 0)) ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div class="mt-2 text-muted small">
                        <i class="bi bi-info-circle me-1"></i>
                        Jumlah <strong>pendaftaran unik</strong> per <?= $isProgramTab ? 'tindakan' : 'paket' ?> per bulan — 
                        tahun <strong><?= $tahun ?></strong>.
                    </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>

</div>
</div>

<?php
$exportUrl = Url::to(['/pendaftaran/laporan-paket-mcu/export']);
$js = <<<JS
    // ── Deteksi browser refresh (F5) ─────────────────────────────────────────
    // Jika user me-refresh halaman saat data sudah tampil (URL mengandung ?cari),
    // arahkan ke URL bersih sesuai tab aktif sehingga data dikosongkan.
    (function () {
        try {
            var navEntry = performance.getEntriesByType('navigation')[0];
            var isReload = navEntry
                ? navEntry.type === 'reload'
                : (window.performance && window.performance.navigation.type === 1);

            if (isReload) {
                var url     = new URL(window.location.href);
                var hasCari = url.searchParams.has('cari');
                if (hasCari) {
                    window.location.replace('$cleanTabUrl');
                }
            }
        } catch (e) { /* browser lama – abaikan */ }
    })();

    // ── Select2 ──────────────────────────────────────────────────────────────
    if ($.fn.select2) {
        $('.select2-filter-item').select2({
            placeholder: '$placeholder',
            allowClear: true,
            width: '100%',
            theme: 'bootstrap'
        });
    }

    // ── Export Excel ─────────────────────────────────────────────────────────
    $('#btn-export').on('click', function(e) {
        e.preventDefault();
        var formParams = $('#filter-form').serializeArray().filter(function(item) {
            return item.name !== 'r';
        });
        var formData  = $.param(formParams);
        var separator = "$exportUrl".indexOf('?') !== -1 ? '&' : '?';
        window.location.href = "$exportUrl" + separator + formData;
    });
JS;
$this->registerJs($js);
?>
