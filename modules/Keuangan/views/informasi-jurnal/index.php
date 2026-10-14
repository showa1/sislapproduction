<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\LinkPager;
use kartik\date\DatePicker;

/* @var $this            yii\web\View */
/* @var $dataProvider    yii\data\SqlDataProvider */
/* @var $statusCari      bool */
/* @var $dateFrom        string */
/* @var $dateTo          string */
/* @var $kodeJurnal      string */
/* @var $urianJurnal     string */
/* @var $kdRekening5     string */
/* @var $jenisJurnalId   string */
/* @var $jenisJurnalList array */
/* @var $rekening5List   array */
/* @var $summary         array */

$this->title = 'Informasi Jurnal';
$this->params['breadcrumbs'][] = ['label' => 'Keuangan', 'url' => ['/keuangan']];
$this->params['breadcrumbs'][] = $this->title;

// Register Select2
$this->registerCssFile('@web/template/vendors/select2/select2.min.css');
$this->registerCssFile('@web/template/vendors/select2-bootstrap-theme/select2-bootstrap.min.css');
$this->registerJsFile('@web/template/vendors/select2/select2.min.js', ['depends' => [\yii\web\JqueryAsset::class]]);

$resetUrl   = Url::to(['/keuangan/informasi-jurnal/index']);
$exportUrl  = Url::to(['/keuangan/informasi-jurnal/export']);

$this->registerCss("
    .ij-header-title {
        color: #002D72;
        font-weight: 700;
        font-size: 1.6rem;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
    }
    .summary-card {
        border-radius: 12px;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .summary-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0,45,114,0.15) !important;
    }

    /* Filter Card */
    .filter-card {
        border: none;
        border-radius: 14px;
        box-shadow: 0 6px 18px rgba(0, 45, 114, 0.06);
        background: #ffffff;
        margin-bottom: 24px;
    }
    .filter-label {
        font-size: 0.82rem;
        font-weight: 600;
        color: #475569;
        margin-bottom: 5px;
        display: flex;
        align-items: center;
    }
    .filter-label i {
        color: #002D72;
        margin-right: 5px;
    }

    /* Select2 */
    .select2-container--bootstrap .select2-selection--single {
        height: 38px !important;
        border: 1px solid #ced4da !important;
        border-radius: 6px !important;
        padding: 5px 10px !important;
    }
    .select2-container--bootstrap .select2-selection--single .select2-selection__rendered {
        line-height: 26px !important;
        color: #333 !important;
        font-size: 13px !important;
    }
    .select2-container--bootstrap .select2-selection--single .select2-selection__arrow {
        height: 36px !important;
    }
    .select2-dropdown {
        border-radius: 6px !important;
        box-shadow: 0 8px 20px rgba(0,0,0,0.12) !important;
        border: 1px solid #ced4da !important;
        z-index: 1060 !important;
    }
    .select2-results__option--highlighted[aria-selected] {
        background-color: #002D72 !important;
        color: #fff !important;
    }

    /* Table */
    .tbl-informasi-jurnal thead th {
        background-color: #002D72;
        color: #fff;
        font-size: 12.5px;
        font-weight: 700;
        text-align: center;
        vertical-align: middle;
        border: 1px solid #0045a5;
        white-space: nowrap;
        padding: 10px 8px;
    }
    .tbl-informasi-jurnal tbody td {
        vertical-align: middle;
        font-size: 12.5px;
        border: 1px solid #dee2e6;
        padding: 8px;
    }
    .tbl-informasi-jurnal tfoot td {
        background-color: #f1f5f9;
        font-weight: 700;
        font-size: 13px;
        border: 1px solid #cbd5e1;
    }
    .badge-kode {
        background-color: #e0f2fe;
        color: #0369a1;
        font-weight: 600;
        padding: 4px 8px;
        border-radius: 6px;
        font-size: 12px;
        letter-spacing: 0.3px;
        display: inline-block;
    }
    .badge-rek {
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        color: #334155;
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 11px;
    }
    .pagination .page-item.active .page-link {
        background-color: #002D72;
        border-color: #002D72;
    }
");
?>

<div class="row quick-action-toolbar">
<div class="col-md-12">

    <div class="ij-header-title">
        <i class="bi bi-journal-bookmark me-2"></i>Informasi Jurnal
    </div>

    <!-- ===== Summary Cards ===== -->
    <?php
    $totalBaris  = (int)($summary['total_baris'] ?? 0);
    $totalJurnal = (int)($summary['total_jurnal'] ?? 0);
    $totalDebit  = (float)($summary['total_debit'] ?? 0);
    $totalKredit = (float)($summary['total_kredit'] ?? 0);
    $selisih     = (float)($summary['selisih'] ?? 0);
    ?>
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm p-3 summary-card text-white"
                 style="background: linear-gradient(135deg, #002D72 0%, #0056b3 100%);">
                <div class="d-flex align-items-center">
                    <div class="fs-1 me-3"><i class="bi bi-journal-check"></i></div>
                    <div>
                        <div class="fs-6 opacity-75">Jurnal / Baris Detail</div>
                        <div class="fs-4 fw-bold">
                            <?= number_format($totalJurnal) ?> <span class="fs-6 fw-normal">Jurnal (<?= number_format($totalBaris) ?> baris)</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm p-3 summary-card text-white"
                 style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                <div class="d-flex align-items-center">
                    <div class="fs-1 me-3"><i class="bi bi-arrow-down-circle"></i></div>
                    <div>
                        <div class="fs-6 opacity-75">Total Saldo Debit</div>
                        <div class="fs-4 fw-bold">Rp <?= number_format($totalDebit, 2, ',', '.') ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm p-3 summary-card text-white"
                 style="background: linear-gradient(135deg, #dc3545 0%, #f87171 100%);">
                <div class="d-flex align-items-center">
                    <div class="fs-1 me-3"><i class="bi bi-arrow-up-circle"></i></div>
                    <div>
                        <div class="fs-6 opacity-75">Total Saldo Kredit</div>
                        <div class="fs-4 fw-bold">Rp <?= number_format($totalKredit, 2, ',', '.') ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm p-3 summary-card text-white"
                 style="background: linear-gradient(135deg, <?= abs($selisih) < 0.01 ? '#6f42c1 0%, #a855f7 100%' : '#ea580c 0%, #f97316 100%' ?>);">
                <div class="d-flex align-items-center">
                    <div class="fs-1 me-3">
                        <i class="bi <?= abs($selisih) < 0.01 ? 'bi-check2-circle' : 'bi-exclamation-triangle' ?>"></i>
                    </div>
                    <div>
                        <div class="fs-6 opacity-75">Selisih (Balance)</div>
                        <div class="fs-4 fw-bold">Rp <?= number_format($selisih, 2, ',', '.') ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== Filter Card ===== -->
    <div class="card filter-card p-4">
        <?= Html::beginForm(['/keuangan/informasi-jurnal/index'], 'get', ['id' => 'filter-form']) ?>
        <?= Html::hiddenInput('cari', '1') ?>

        <div class="row g-3">
            <!-- Filter Tanggal Bukti -->
            <div class="col-md-4">
                <label class="filter-label">
                    <i class="bi bi-calendar3"></i>Periode Tanggal Bukti Jurnal
                </label>
                <?= DatePicker::widget([
                    'type'          => DatePicker::TYPE_RANGE,
                    'name'          => 'date_from',
                    'value'         => $dateFrom,
                    'name2'         => 'date_to',
                    'value2'        => $dateTo,
                    'separator'     => '<span class="px-2 text-muted fw-bold">s/d</span>',
                    'layout'        => '{input1}{separator}{input2}',
                    'options'       => [
                        'placeholder'  => 'Tgl Awal',
                        'class'        => 'form-control',
                        'autocomplete' => 'off',
                    ],
                    'options2'      => [
                        'placeholder'  => 'Tgl Akhir',
                        'class'        => 'form-control',
                        'autocomplete' => 'off',
                    ],
                    'pluginOptions' => [
                        'format'         => 'dd-mm-yyyy',
                        'autoclose'      => true,
                        'todayHighlight' => true,
                    ],
                ]); ?>
            </div>

            <!-- Filter Kode Jurnal (Permintaan Khusus User) -->
            <div class="col-md-3">
                <label class="filter-label">
                    <i class="bi bi-upc-scan"></i>Filter Kode Jurnal
                </label>
                <?= Html::textInput('kodejurnal', $kodeJurnal, [
                    'class'       => 'form-control',
                    'placeholder' => 'Misal: 003356732...',
                    'autocomplete'=> 'off',
                ]) ?>
            </div>

            <!-- Filter Jenis Jurnal -->
            <div class="col-md-5">
                <label class="filter-label">
                    <i class="bi bi-tags"></i>Jenis Jurnal
                </label>
                <?= Html::dropDownList('jenisjurnal_id', $jenisJurnalId, $jenisJurnalList, [
                    'class'  => 'form-select select2-basic',
                    'prompt' => '-- Semua Jenis Jurnal --',
                ]) ?>
            </div>

            <!-- Filter Kata Kunci / Uraian Jurnal -->
            <div class="col-md-6 mt-3">
                <label class="filter-label">
                    <i class="bi bi-search"></i>Cari Uraian / No Referensi / No Bukti
                </label>
                <?= Html::textInput('urianjurnal', $urianJurnal, [
                    'class'       => 'form-control',
                    'placeholder' => 'Ketik Uraian Jurnal atau No Referensi (Misal: RJ2602180001)...',
                    'autocomplete'=> 'off',
                ]) ?>
            </div>

            <!-- Filter Rekening 5 -->
            <div class="col-md-6 mt-3">
                <label class="filter-label">
                    <i class="bi bi-list-columns-reverse"></i>Pilih Akun Rekening 5 (kdrekening5)
                </label>
                <?= Html::dropDownList('kdrekening5', $kdRekening5, $rekening5List, [
                    'class'            => 'form-select select2-rekening',
                    'prompt'           => '-- Semua Rekening 5 --',
                    'data-placeholder' => 'Pilih atau cari kode/nama rekening 5...',
                ]) ?>
            </div>
        </div>

        <!-- Tombol Aksi -->
        <div class="row mt-4">
            <div class="col-12 d-flex justify-content-start flex-wrap align-items-center">
                <?= Html::submitButton('<i class="bi bi-search me-1"></i> Tampilkan Data', [
                    'class' => 'btn btn-primary me-2 mb-2',
                    'style' => 'background-color:#002D72; border-color:#002D72; padding:8px 22px; font-weight:600;',
                ]) ?>
                <?= Html::a('<i class="bi bi-arrow-clockwise me-1"></i> Reset Filter', $resetUrl, [
                    'class' => 'btn btn-outline-secondary me-2 mb-2',
                    'style' => 'padding:8px 18px;',
                ]) ?>
                <?= Html::button('<i class="bi bi-file-earmark-excel me-1"></i> Export Excel', [
                    'id'    => 'btn-export',
                    'class' => 'btn btn-success me-2 mb-2',
                    'style' => 'background-color:#28a745; border-color:#28a745; padding:8px 20px; font-weight:600;',
                ]) ?>
            </div>
        </div>
        <?= Html::endForm() ?>

        <!-- ===== Hasil Data / Empty State ===== -->
        <div class="row mt-4">
            <div class="col-12">
                <?php if (!$statusCari): ?>
                <!-- Empty State Awal -->
                <div class="text-center py-5 border rounded-3 bg-light my-2 shadow-sm">
                    <i class="bi bi-funnel d-block mb-3" style="font-size:3.2rem; color:#002D72;"></i>
                    <h5 class="fw-bold text-dark mb-2">Silakan Tentukan Filter Pencarian Jurnal</h5>
                    <p class="text-muted small mb-0">
                        Gunakan filter <strong>Kode Jurnal</strong>, <strong>Kata Kunci Uraian/No Referensi</strong>, atau <strong>Periode Tanggal</strong> di atas, lalu klik <strong>Tampilkan Data</strong>.
                    </p>
                </div>
                <?php elseif ($dataProvider->getTotalCount() == 0): ?>
                <!-- No Data State -->
                <div class="text-center py-5 border rounded-3 bg-light my-2 shadow-sm">
                    <i class="bi bi-inbox d-block mb-3" style="font-size:3.2rem; color:#6c757d;"></i>
                    <h5 class="fw-bold text-muted mb-2">Tidak Ditemukan Data Jurnal</h5>
                    <p class="text-muted small mb-0">
                        Tidak ada transaksi jurnal yang cocok dengan filter yang Anda tentukan. Silakan sesuaikan filter pencarian.
                    </p>
                </div>
                <?php else: ?>
                <!-- Data Table -->
                <?php $models = $dataProvider->getModels(); ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover tbl-informasi-jurnal align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width:38px;">No</th>
                                <th style="min-width:130px;">Tgl Bukti &amp; Kode</th>
                                <th style="min-width:130px;">No Bukti &amp; Jenis</th>
                                <th style="min-width:120px;">No Referensi</th>
                                <th style="min-width:220px; text-align:left;">Uraian Jurnal &amp; Transaksi</th>
                                <th style="min-width:180px; text-align:left;">Akun Rekening 5</th>
                                <th style="min-width:110px; text-align:right;">Saldo Debit</th>
                                <th style="min-width:110px; text-align:right;">Saldo Kredit</th>
                                <th style="width:70px;">Posting</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $pagination = $dataProvider->getPagination();
                            $offset     = $pagination ? $pagination->getOffset() : 0;
                            ?>
                            <?php foreach ($models as $idx => $row): ?>
                            <tr>
                                <td class="text-center"><?= $offset + $idx + 1 ?></td>
                                <td>
                                    <div class="fw-bold"><?= !empty($row['tglbuktijurnal']) ? date('d-m-Y H:i', strtotime($row['tglbuktijurnal'])) : '-' ?></div>
                                    <div class="mt-1"><span class="badge-kode"><?= Html::encode($row['kodejurnal']) ?></span></div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-primary"><?= Html::encode($row['nobuktijurnal']) ?></div>
                                    <small class="text-muted"><?= Html::encode($row['jenisjurnal_nama']) ?></small>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark"><?= Html::encode($row['noreferensi']) ?: '-' ?></div>
                                    <small class="text-muted"><?= !empty($row['tglreferensi']) ? date('d-m-Y', strtotime($row['tglreferensi'])) : '' ?></small>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark"><?= Html::encode($row['urianjurnal']) ?></div>
                                    <?php if (!empty($row['uraiantransaksi']) && $row['uraiantransaksi'] !== $row['urianjurnal']): ?>
                                    <div class="small text-muted mt-1"><i class="bi bi-arrow-return-right me-1"></i><?= Html::encode($row['uraiantransaksi']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div>
                                        <span class="badge-rek fw-bold"><?= Html::encode($row['kdrekening5']) ?></span>
                                        <span class="fw-semibold text-dark"><?= Html::encode($row['nmrekening5']) ?></span>
                                    </div>
                                    <div class="small text-muted mt-1">
                                        <?= Html::encode($row['kdrekening4']) ?> - <?= Html::encode($row['nmrekening4']) ?>
                                    </div>
                                </td>
                                <td class="text-end font-monospace">
                                    <?php $debit = (float)($row['saldodebit'] ?? 0); ?>
                                    <?php if ($debit > 0): ?>
                                    <span class="text-success fw-bold"><?= number_format($debit, 2, ',', '.') ?></span>
                                    <?php else: ?>
                                    <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end font-monospace">
                                    <?php $kredit = (float)($row['saldokredit'] ?? 0); ?>
                                    <?php if ($kredit > 0): ?>
                                    <span class="text-danger fw-bold"><?= number_format($kredit, 2, ',', '.') ?></span>
                                    <?php else: ?>
                                    <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if (!empty($row['tgljurnalpost'])): ?>
                                    <span class="badge bg-success" title="Diposting: <?= Html::encode($row['tgljurnalpost']) ?>">
                                        <i class="bi bi-check-lg"></i> Posted
                                    </span>
                                    <?php else: ?>
                                    <span class="badge bg-secondary">Draft</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="6" class="text-end fw-bold">TOTAL KESELURUHAN HASIL FILTER:</td>
                                <td class="text-end fw-bold text-success font-monospace">
                                    <?= number_format($totalDebit, 2, ',', '.') ?>
                                </td>
                                <td class="text-end fw-bold text-danger font-monospace">
                                    <?= number_format($totalKredit, 2, ',', '.') ?>
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Pagination Widget -->
                <div class="d-flex justify-content-between align-items-center flex-wrap mt-3">
                    <div class="text-muted small mb-2">
                        Menampilkan <strong><?= count($models) ?></strong> dari total <strong><?= number_format($dataProvider->getTotalCount()) ?></strong> baris transaksi.
                    </div>
                    <div>
                        <?= LinkPager::widget([
                            'pagination' => $dataProvider->getPagination(),
                            'options'    => ['class' => 'pagination pagination-sm m-0'],
                            'linkOptions'=> ['class' => 'page-link'],
                            'disabledPageCssClass' => 'disabled',
                            'activePageCssClass'   => 'active',
                        ]) ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

    </div>

</div>
</div>

<?php
$js = <<<JS
    // ── Deteksi browser refresh (F5) ─────────────────────────────────────────
    (function () {
        try {
            var navEntry = performance.getEntriesByType('navigation')[0];
            var isReload = navEntry
                ? navEntry.type === 'reload'
                : (window.performance && window.performance.navigation.type === 1);

            if (isReload) {
                var url = new URL(window.location.href);
                if (url.searchParams.has('cari')) {
                    window.location.replace('$resetUrl');
                }
            }
        } catch (e) { /* ignore */ }
    })();

    // ── Select2 ──────────────────────────────────────────────────────────────
    if ($.fn.select2) {
        $('.select2-basic').select2({
            theme: 'bootstrap',
            width: '100%',
            allowClear: true
        });
        $('.select2-rekening').select2({
            theme: 'bootstrap',
            width: '100%',
            allowClear: true,
            placeholder: 'Pilih atau cari kode / nama rekening 5...'
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
