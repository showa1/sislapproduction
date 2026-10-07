<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;
use kartik\date\DatePicker;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\SqlDataProvider */
/* @var $statusCari bool */
/* @var $dateFrom string */
/* @var $dateTo string */
/* @var $penjaminKategori string */
/* @var $penjaminId string */
/* @var $tindakanIds array */
/* @var $search string */
/* @var $sortOrder string */
/* @var $penjaminList array */
/* @var $tindakanList array */
/* @var $totalPasien int */
/* @var $totalTindakan int */
/* @var $totalNominal float */

$this->title = 'Laporan Program Paket';
$this->params['breadcrumbs'][] = ['label' => 'Pendaftaran & Penjadwalan', 'url' => ['/pendaftaran']];
$this->params['breadcrumbs'][] = $this->title;

// Register Select2 Assets
$this->registerCssFile('@web/template/vendors/select2/select2.min.css');
$this->registerCssFile('@web/template/vendors/select2-bootstrap-theme/select2-bootstrap.min.css');
$this->registerJsFile('@web/template/vendors/select2/select2.min.js', ['depends' => [\yii\web\JqueryAsset::class]]);

$this->registerCss("
    .custom-gridview thead th {
        background-color: #002D72;
        color: white;
        font-size: 14px;
        text-align: center;
        font-weight: bold;
        border-bottom: 2px solid #dee2e6;
        vertical-align: middle;
    }
    .custom-gridview tbody td {
        vertical-align: middle;
        font-size: 14px;
    }
    .summary-card {
        border-radius: 12px;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .summary-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0, 45, 114, 0.15) !important;
    }
    
    /* Multi-Select Select2 Styling */
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
        box-shadow: 0 0 0 3px rgba(0, 45, 114, 0.12) !important;
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
        box-shadow: 0 2px 4px rgba(0, 45, 114, 0.2);
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
    .select2-search__field {
        font-size: 13px !important;
    }
");

$resetUrl = Url::to(['/pendaftaran/program-paket/index']);
$urlExport = Url::to(['/pendaftaran/program-paket/export']);
?>

<div class="row quick-action-toolbar">
    <div class="col-md-12">
        <h2 style="color: #002D72; font-weight: bold; margin-bottom: 20px;">
            <i class="bi bi-box-seam me-2"></i>Laporan Program Paket & Tindakan Pasien
        </h2>

        <!-- Summary Cards KPI -->
        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="card border-0 shadow-sm p-3 summary-card text-white" style="background: linear-gradient(135deg, #002D72 0%, #0056b3 100%);">
                    <div class="d-flex align-items-center">
                        <div class="fs-1 me-3"><i class="bi bi-people-fill"></i></div>
                        <div>
                            <div class="fs-6 opacity-75">Total Pasien</div>
                            <div class="fs-3 fw-bold"><?= number_format($totalPasien) ?> <span class="fs-6 fw-normal">Pasien</span></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card border-0 shadow-sm p-3 summary-card text-white" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                    <div class="d-flex align-items-center">
                        <div class="fs-1 me-3"><i class="bi bi-card-checklist"></i></div>
                        <div>
                            <div class="fs-6 opacity-75">Total Tindakan Diberikan</div>
                            <div class="fs-3 fw-bold"><?= number_format($totalTindakan) ?> <span class="fs-6 fw-normal">Tindakan</span></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card border-0 shadow-sm p-3 summary-card text-white" style="background: linear-gradient(135deg, #fd7e14 0%, #ffc107 100%);">
                    <div class="d-flex align-items-center">
                        <div class="fs-1 me-3"><i class="bi bi-cash-stack"></i></div>
                        <div>
                            <div class="fs-6 opacity-75">Total Nominal Tarif</div>
                            <div class="fs-3 fw-bold">Rp <?= number_format($totalNominal, 0, ',', '.') ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="card border-0 shadow-sm p-4">
            <div class="d-md-flex row m-0 quick-action-btns">
                
                <?= Html::beginForm(['/pendaftaran/program-paket/index'], 'get', ['id' => 'filter-form']) ?>
                <?= Html::hiddenInput('cari', '1') ?>
                
                <!-- Filter Row 1: Tanggal Range & Penjamin -->
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted small fw-bold"><i class="bi bi-calendar3 me-1"></i>Periode Tanggal Tindakan</label>
                        <?= DatePicker::widget([
                            'type' => DatePicker::TYPE_RANGE,
                            'name' => 'date_from',
                            'value' => $dateFrom,
                            'name2' => 'date_to',
                            'value2' => $dateTo, 
                            'separator' => 's/d',
                            'layout' => '{input1}<span class="input-group-text bg-light">{separator}</span>{input2}',
                            'options' => [
                                'placeholder' => 'Tanggal Awal (dd-mm-yyyy)',
                                'class' => 'form-control',
                                'autocomplete' => 'off',
                            ],
                            'options2' => [
                                'placeholder' => 'Tanggal Akhir (dd-mm-yyyy)',
                                'class' => 'form-control',
                                'autocomplete' => 'off',
                            ],
                            'pluginOptions' => [
                                'format' => 'dd-mm-yyyy',
                                'autoclose' => true,
                                'todayHighlight' => true,
                            ],
                        ]); ?>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label text-muted small fw-bold"><i class="bi bi-shield-check me-1"></i>Jenis Penjamin</label>
                        <?= Html::dropDownList('penjamin_kategori', $penjaminKategori, [
                            '' => '-- Semua Jenis Penjamin --',
                            'umum' => 'Khusus Penjamin UMUM',
                            'asuransi' => 'Khusus ASURANSI (Non-BPJS)',
                            'bpjs' => 'Khusus BPJS Kesehatan',
                        ], [
                            'class' => 'form-select',
                        ]) ?>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label text-muted small fw-bold"><i class="bi bi-building me-1"></i>Pilih Penjamin Spesifik</label>
                        <?= Html::dropDownList('penjamin_id', $penjaminId, $penjaminList, [
                            'prompt' => '-- Semua Nama Penjamin --',
                            'class' => 'form-select',
                        ]) ?>
                    </div>
                </div>

                <!-- Filter Row 2: Searchable Select2 Multi-Pilih Tindakan & Pencarian -->
                <div class="row g-3 mt-1">
                    <div class="col-md-6">
                        <label class="form-label text-muted small fw-bold"><i class="bi bi-check2-square me-1"></i>Pilih Tindakan yang Ditampilkan</label>
                        <?= Html::dropDownList('tindakan_ids[]', $tindakanIds, $tindakanList, [
                            'class' => 'form-select select2-tindakan',
                            'id' => 'select2-tindakan-ids',
                            'multiple' => true,
                            'data-placeholder' => 'Ketik atau pilih nama tindakan (misal: Anastomose, CITO, Scaling)...',
                        ]) ?>
                        <small class="text-muted" style="font-size: 11px;">Ketik kata kunci tindakan untuk mencari secara langsung. Kosongkan jika ingin menampilkan semua tindakan.</small>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label text-muted small fw-bold"><i class="bi bi-search me-1"></i>Cari (Pasien/RM/Pendaftaran)</label>
                        <?= Html::textInput('search', $search, [
                            'class' => 'form-control',
                            'placeholder' => 'Ketik Nama Pasien, No RM, atau No Pendaftaran...',
                        ]) ?>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label text-muted small fw-bold"><i class="bi bi-sort-down me-1"></i>Urutkan Data</label>
                        <?= Html::dropDownList('sort_order', $sortOrder, [
                            'tgl_desc'     => 'Tanggal Tindakan: Terbaru → Terlama',
                            'tgl_asc'      => 'Tanggal Tindakan: Terlama → Terbaru',
                            'nama_asc'     => 'Nama Pasien (A - Z)',
                            'nama_desc'    => 'Nama Pasien (Z - A)',
                            'tindakan_asc' => 'Nama Tindakan (A - Z)',
                            'tarif_desc'   => 'Total Tarif Terbesar',
                        ], [
                            'class' => 'form-select',
                        ]) ?>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="row mt-4">
                    <div class="col-12 d-flex justify-content-start flex-wrap align-items-center">
                        <?= Html::submitButton('<i class="bi bi-filter me-1"></i> Tampilkan Data', [
                            'class' => 'btn btn-primary me-2 mb-2',
                            'style' => 'background-color: #002D72; border-color: #002D72; padding: 8px 20px; font-weight: 600;'
                        ]) ?>
                        <?= Html::a('<i class="bi bi-arrow-clockwise me-1"></i> Reset Filter', $resetUrl, [
                            'class' => 'btn btn-outline-secondary me-2 mb-2',
                            'style' => 'padding: 8px 16px;'
                        ]) ?>
                        <?= Html::button('<i class="bi bi-file-earmark-excel me-1"></i> Export Excel', [
                            'id' => 'export-button',
                            'class' => 'btn btn-success me-2 mb-2',
                            'style' => 'background-color: #28a745; border-color: #28a745; padding: 8px 20px; font-weight: 600;'
                        ]) ?>
                    </div>
                </div>
                <?= Html::endForm() ?>

                <!-- Data Table Area -->
                <div class="row mt-4">
                    <div class="col-12">
                        <?php if (!$statusCari): ?>
                            <div class="text-center py-5 border rounded-3 bg-light my-2 shadow-sm">
                                <i class="bi bi-funnel text-primary mb-3 d-block" style="font-size: 3rem; color: #002D72 !important;"></i>
                                <h5 class="fw-bold text-dark mb-2">Silakan Tentukan Filter Laporan</h5>
                                <p class="text-muted small mb-0">Tentukan Periode Tanggal, Jenis Penjamin, atau Pilih Tindakan di atas, lalu klik tombol <strong><i class="bi bi-filter"></i> Tampilkan Data</strong> untuk memuat laporan.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <?= GridView::widget([
                                    'dataProvider' => $dataProvider,
                                    'tableOptions' => [
                                        'class' => 'table table-striped table-hover table-bordered custom-gridview align-middle',
                                    ],
                                    'columns' => [
                                        [
                                            'class' => 'yii\grid\SerialColumn',
                                            'header' => 'No',
                                            'headerOptions' => ['style' => 'width: 50px; text-align: center;'],
                                            'contentOptions' => ['style' => 'text-align: center;'],
                                        ],
                                        [
                                            'attribute' => 'tgl_tindakan',
                                            'label' => 'Tgl Tindakan',
                                            'headerOptions' => ['style' => 'width: 130px; text-align: center;'],
                                            'contentOptions' => ['style' => 'text-align: center;'],
                                            'value' => function($model) {
                                                return !empty($model['tgl_tindakan']) ? date('d/m/Y H:i', strtotime($model['tgl_tindakan'])) : '-';
                                            }
                                        ],
                                        [
                                            'attribute' => 'no_pendaftaran',
                                            'label' => 'No Pendaftaran',
                                            'headerOptions' => ['style' => 'width: 140px; text-align: center;'],
                                            'contentOptions' => ['style' => 'text-align: center; font-weight: 600; text-transform: uppercase;'],
                                        ],
                                        [
                                            'attribute' => 'no_rekam_medik',
                                            'label' => 'No RM',
                                            'headerOptions' => ['style' => 'width: 100px; text-align: center;'],
                                            'contentOptions' => ['style' => 'text-align: center; font-weight: 700; color: #002D72;'],
                                        ],
                                        [
                                            'attribute' => 'nama_pasien',
                                            'label' => 'Nama Pasien',
                                            'format' => 'raw',
                                            'value' => function($model) {
                                                $jk = !empty($model['jeniskelamin']) ? ' (' . substr($model['jeniskelamin'], 0, 1) . ')' : '';
                                                return Html::tag('strong', Html::encode($model['nama_pasien'] . $jk));
                                            }
                                        ],
                                        [
                                            'attribute' => 'penjamin_nama',
                                            'label' => 'Penjamin',
                                            'headerOptions' => ['style' => 'width: 140px; text-align: center;'],
                                            'contentOptions' => ['style' => 'text-align: center; font-weight: 500; color: #334155;'],
                                            'value' => function($model) {
                                                return $model['penjamin_nama'] ?? '-';
                                            }
                                        ],
                                        [
                                            'attribute' => 'daftartindakan_nama',
                                            'label' => 'Nama Tindakan',
                                            'format' => 'raw',
                                            'value' => function($model) {
                                                return Html::tag('span', Html::encode($model['daftartindakan_nama']), [
                                                    'class' => 'text-dark fw-bold'
                                                ]);
                                            }
                                        ],
                                        [
                                            'attribute' => 'qty_tindakan',
                                            'label' => 'Qty',
                                            'headerOptions' => ['style' => 'width: 60px; text-align: center;'],
                                            'contentOptions' => ['style' => 'text-align: center;'],
                                        ],
                                        [
                                            'attribute' => 'tarif_satuan',
                                            'label' => 'Tarif Satuan',
                                            'headerOptions' => ['style' => 'text-align: right; width: 120px;'],
                                            'contentOptions' => ['style' => 'text-align: right;'],
                                            'value' => function($model) {
                                                return 'Rp ' . number_format($model['tarif_satuan'] ?? 0, 0, ',', '.');
                                            }
                                        ],
                                        [
                                            'attribute' => 'total_tarif',
                                            'label' => 'Total Tarif',
                                            'headerOptions' => ['style' => 'text-align: right; width: 130px;'],
                                            'contentOptions' => ['style' => 'text-align: right; font-weight: 700; color: #28a745;'],
                                            'value' => function($model) {
                                                return 'Rp ' . number_format($model['total_tarif'] ?? 0, 0, ',', '.');
                                            }
                                        ],
                                        [
                                            'attribute' => 'ruangan_nama',
                                            'label' => 'Ruangan / Poli',
                                            'headerOptions' => ['style' => 'width: 140px; text-align: center;'],
                                            'contentOptions' => ['style' => 'text-align: center;'],
                                        ],
                                        [
                                            'attribute' => 'nama_dokter',
                                            'label' => 'Dokter Pemeriksa',
                                            'value' => function($model) {
                                                return $model['nama_dokter'] ?? '-';
                                            }
                                        ],
                                    ],
                                    'layout' => "{items}\n<div class='row mt-3 align-items-center'>
                                                <div class='col-md-6'>{pager}</div>
                                                <div class='col-md-6 text-end'>{summary}</div>
                                            </div>",
                                    'pager' => [
                                        'options' => ['class' => 'pagination mb-0'],
                                        'linkOptions' => ['class' => 'page-link'],
                                        'prevPageLabel' => '&laquo;', 
                                        'nextPageLabel' => '&raquo;',
                                    ],
                                    'summary' => 'Menampilkan {begin} - {end} dari {totalCount} data tindakan.',
                                ]); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php
$js = <<<JS
    // Inisialisasi Select2 Multi-Select dengan Autocomplete Search
    if ($.fn.select2) {
        $('.select2-tindakan').select2({
            placeholder: 'Ketik atau pilih nama tindakan (misal: Anastomose, CITO, Scaling)...',
            allowClear: true,
            width: '100%',
            theme: 'bootstrap'
        });
    }

    $('#export-button').on('click', function(e) {
        e.preventDefault();
        var formParams = $('#filter-form').serializeArray().filter(function(item) {
            return item.name !== 'r';
        });
        var formData = $.param(formParams);
        var separator = "$urlExport".indexOf('?') !== -1 ? "&" : "?";
        window.location.href = "$urlExport" + separator + formData;
    });
JS;
$this->registerJs($js);
?>
